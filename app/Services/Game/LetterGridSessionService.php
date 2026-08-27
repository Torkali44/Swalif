<?php

namespace App\Services\Game;

use App\Enums\GameStatus;
use App\Models\LetterGrid;
use App\Models\LetterGridGame;
use App\Models\LetterGridGameCell;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LetterGridSessionService
{
    public const CUSTOM_WIN_BONUS = 600;

    public function start(User $user, array $data): LetterGridGame
    {
        $grid = LetterGrid::query()
            ->where('is_active', true)
            ->with('activeCells')
            ->findOrFail($data['letter_grid_id']);

        if ($grid->activeCells->isEmpty()) {
            throw ValidationException::withMessages([
                'letter_grid_id' => 'هذه الشبكة لا تحتوي على حروف جاهزة للعب.',
            ]);
        }

        return DB::transaction(function () use ($user, $grid, $data) {
            $game = LetterGridGame::create([
                'user_id' => $user->id,
                'letter_grid_id' => $grid->id,
                'custom_game_id' => $data['custom_game_id'] ?? null,
                'name' => $data['name'] ?? ($grid->name_ar ?: 'شبكة الحروف'),
                'status' => GameStatus::Playing->value,
                'turn_index' => 0,
                'answered_count' => 0,
                'started_at' => now(),
            ]);

            $helpers = config('game.default_helpers', [
                'swap' => 0,
                'phone_friend' => 0,
                'two_answers' => 0,
            ]);

            $game->teams()->create([
                'letter_grid_game_id' => $game->id,
                'name' => $data['team_one'],
                'character_id' => $data['team_one_character_id'] ?? null,
                'score' => 0,
                'helpers_left' => $helpers,
            ]);

            $game->teams()->create([
                'letter_grid_game_id' => $game->id,
                'name' => $data['team_two'],
                'character_id' => $data['team_two_character_id'] ?? null,
                'score' => 0,
                'helpers_left' => $helpers,
            ]);

            $now = now();
            $game->cells()->insert(
                $grid->activeCells->map(fn ($cell) => [
                    'letter_grid_game_id' => $game->id,
                    'letter' => $cell->letter,
                    'row' => $cell->row,
                    'col' => $cell->col,
                    'question_text' => $cell->question_text,
                    'answer_text' => $cell->answer_text,
                    'answered_correctly' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );

            $firstCell = $game->cells()->orderBy('row')->orderBy('col')->first();
            if ($firstCell) {
                $game->update(['active_cell_id' => $firstCell->id]);
            }

            return $game->fresh(['teams.character', 'cells', 'grid']);
        });
    }

    public function ensureOwned(LetterGridGame $game, User $user): void
    {
        abort_unless((int) $game->user_id === (int) $user->id, 403);
    }

    public function loadForPlay(LetterGridGame $game): LetterGridGame
    {
        return $game->load([
            'teams.character',
            'cells.claimedTeam',
            'grid',
            'activeCell',
        ]);
    }

    public function selectCell(LetterGridGame $game, int $cellId): LetterGridGameCell
    {
        $cell = $game->relationLoaded('cells')
            ? $game->cells->firstWhere('id', $cellId)
            : null;

        if (! $cell) {
            $cell = $game->cells()->whereKey($cellId)->firstOrFail();
        }

        abort_if($cell->isResolved(), 422, 'هذا الحرف تم احتسابه بالفعل.');
        abort_if(! $game->isPlaying(), 422, 'اللعبة منتهية.');

        $game->update(['active_cell_id' => $cell->id]);

        return $cell;
    }

    public function finishIfComplete(LetterGridGame $game): bool
    {
        if (! $game->isComplete()) {
            return false;
        }

        $game->loadMissing('teams');
        $winner = LetterGridWinnerCalculator::determine($game);

        $game->update([
            'status' => GameStatus::Finished->value,
            'winner_team_id' => $winner?->id,
            'ended_at' => now(),
            'active_cell_id' => null,
        ]);

        if ($game->custom_game_id) {
            $game->loadMissing('customGame.teams');
            $this->awardCustomGameBonus($game);
        }

        return true;
    }

    /**
     * عند انتهاء شبكة داخل لعبة مخصصة: 600 نقطة لفريق الفائز مرة واحدة.
     */
    public function awardCustomGameBonus(LetterGridGame $game): void
    {
        if (! $game->custom_game_id || $game->custom_bonus_awarded) {
            return;
        }

        $winner = LetterGridWinnerCalculator::determine($game);
        $points = 0;
        $customWinnerId = null;

        if ($winner) {
            $customTeams = $game->customGame?->teams
                ?? Team::query()->where('custom_game_id', $game->custom_game_id)->orderBy('id')->get();

            $matched = $customTeams->first(function (Team $t) use ($winner) {
                if ($winner->character_id && $t->character_id) {
                    return (int) $t->character_id === (int) $winner->character_id;
                }

                return trim((string) $t->name) === trim((string) $winner->name);
            }) ?? $customTeams->values()->get(
                $game->teams->values()->search(fn (Team $t) => (int) $t->id === (int) $winner->id)
            );

            if ($matched) {
                $points = self::CUSTOM_WIN_BONUS;
                $customWinnerId = $matched->id;
                Team::query()->whereKey($matched->id)->increment('score', $points);
            }
        }

        $game->update(['custom_bonus_awarded' => true]);

        DB::table('custom_game_letter_grids')
            ->where('custom_game_id', $game->custom_game_id)
            ->where('letter_grid_id', $game->letter_grid_id)
            ->update([
                'winner_team_id' => $customWinnerId,
                'points_awarded' => $points,
                'updated_at' => now(),
            ]);
    }

    public function finishedReplayMessage(LetterGridGame $session): string
    {
        $gridName = $session->grid?->name_ar ?? 'هذه الشبكة';

        $pivot = DB::table('custom_game_letter_grids')
            ->where('custom_game_id', $session->custom_game_id)
            ->where('letter_grid_id', $session->letter_grid_id)
            ->select(['points_awarded', 'winner_team_id'])
            ->first();

        $points = (int) ($pivot->points_awarded ?? 0);
        $winnerTeamId = $pivot->winner_team_id ?? null;

        $winnerName = $winnerTeamId
            ? Team::query()->whereKey($winnerTeamId)->value('name')
            : null;

        if ($winnerName && $points > 0) {
            return 'لعبوا «'.$gridName.'» وفاز بها فريق «'.$winnerName.'» وتم احتساب '.$points.' نقطة لصالحه.';
        }

        return 'لعبوا «'.$gridName.'» مسبقاً وانتهت — لا يمكن لعبها مرة أخرى.';
    }
}
