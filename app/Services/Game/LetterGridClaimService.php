<?php

namespace App\Services\Game;

use App\Models\LetterGridGame;
use App\Models\LetterGridGameCell;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LetterGridClaimService
{
    public function __construct(
        private LetterGridSessionService $sessions,
    ) {}

    /**
     * @return array{game: LetterGridGame, cell: LetterGridGameCell}
     */
    public function claim(LetterGridGame $game, LetterGridGameCell $cell, ?Team $team, bool $correct): array
    {
        if (! $game->isPlaying()) {
            throw ValidationException::withMessages(['team_id' => 'اللعبة منتهية.']);
        }

        if ($cell->isResolved()) {
            throw ValidationException::withMessages(['cell' => 'هذا الحرف تم احتسابه بالفعل.']);
        }

        return DB::transaction(function () use ($game, $cell, $team, $correct) {
            $game = LetterGridGame::query()->lockForUpdate()->findOrFail($game->id);
            $game->load('teams');
            $cell = LetterGridGameCell::query()->lockForUpdate()->findOrFail($cell->id);

            if ($cell->isResolved()) {
                throw ValidationException::withMessages(['cell' => 'هذا الحرف تم احتسابه بالفعل.']);
            }

            $turnTeam = $game->currentTurnTeam();

            $cell->update([
                'claimed_team_id' => $correct && $team ? $team->id : null,
                'turn_team_id' => $turnTeam?->id,
                'answered_correctly' => $correct && $team !== null,
                'answered_at' => now(),
            ]);

            if ($correct && $team) {
                $team->increment('score');
                $loadedTeam = $game->teams->firstWhere('id', $team->id);
                if ($loadedTeam) {
                    $loadedTeam->score = $team->score;
                }
            }

            $teamsCount = max($game->teams->count(), 1);
            $game->increment('answered_count');
            $game->update([
                'turn_index' => ((int) $game->turn_index + 1) % $teamsCount,
                'active_cell_id' => null,
            ]);

            // Use answered_count + one COUNT instead of reloading all cells
            $this->sessions->finishIfComplete($game);

            return [
                'game' => $game,
                'cell' => $cell,
            ];
        });
    }
}
