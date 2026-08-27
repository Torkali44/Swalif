<?php

namespace App\Http\Controllers\Site;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLetterGridGameRequest;
use App\Models\Character;
use App\Models\LetterGrid;
use App\Models\LetterGridGame;
use App\Models\LetterGridGameCell;
use App\Models\Team;
use App\Services\Game\LetterGridClaimService;
use App\Services\Game\LetterGridSessionService;
use App\Services\Game\LetterGridWinnerCalculator;
use App\Services\Subscription\FreeTrialService;
use App\Services\Subscription\PlayAccessService;
use Illuminate\Http\Request;

class LetterGridGameController extends Controller
{
    public function __construct(
        private LetterGridSessionService $sessions,
        private LetterGridClaimService $claims,
        private PlayAccessService $playAccess,
        private FreeTrialService $freeTrial,
    ) {}

    public function create(Request $request)
    {
        $user = $request->user();

        if ($this->playAccess->isBlocked($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        if (! $this->freeTrial->canCreateLetterGridGame($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->freeTrial->letterGridSubscribeRequiredMessage());
        }

        $grids = LetterGrid::query()
            ->where('is_active', true)
            ->withCount(['cells as playable_cells_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->filter(fn ($grid) => $grid->playable_cells_count > 0);

        $characters = Character::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_ar', 'name_en', 'slug', 'image', 'icon', 'accent_color', 'is_active', 'sort_order']);

        return view('site.letter-grid.create', compact('grids', 'characters'));
    }

    public function store(StoreLetterGridGameRequest $request)
    {
        $user = $request->user();

        if ($this->playAccess->isBlocked($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        if (! $this->freeTrial->canCreateLetterGridGame($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->freeTrial->letterGridSubscribeRequiredMessage());
        }

        $game = $this->sessions->start($user, $request->validated());

        return redirect()->route('letter-grid.play', $game);
    }

    public function play(LetterGridGame $letterGridGame, Request $request)
    {
        $this->sessions->ensureOwned($letterGridGame, $request->user());

        if ($letterGridGame->isFinished()) {
            return redirect()->route('letter-grid.result', $letterGridGame);
        }

        $game = $this->sessions->loadForPlay($letterGridGame);
        $teams = $game->teams->values();
        $activeCell = $game->activeCell;

        if ($request->filled('cell')) {
            try {
                $activeCell = $this->sessions->selectCell($game, (int) $request->input('cell'));
                $game->setRelation('activeCell', $activeCell);
            } catch (\Throwable) {
                // ignore invalid cell selection
            }
        }

        $cellsByRow = $game->cells->groupBy('row')->sortKeys();
        $timeLimit = (int) ($game->grid?->time_limit ?? config('letter_grid.default_time_limit', 30));

        return view('site.letter-grid.play', [
            'game' => $game,
            'teams' => $teams,
            'teamA' => $teams->get(0),
            'teamB' => $teams->get(1),
            'activeCell' => $activeCell ?? $game->activeCell,
            'turnTeam' => $game->currentTurnTeam(),
            'cellsByRow' => $cellsByRow,
            'roundLabel' => $this->roundLabel($game),
            'timeLimit' => $timeLimit,
            'resolvedCells' => $game->resolvedCount(),
        ]);
    }

    public function claim(LetterGridGame $letterGridGame, LetterGridGameCell $cell, Request $request)
    {
        $this->sessions->ensureOwned($letterGridGame, $request->user());

        abort_unless((int) $cell->letter_grid_game_id === (int) $letterGridGame->id, 404);

        $validated = $request->validate([
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'correct' => ['required', 'boolean'],
        ]);

        $team = null;
        if ($validated['correct'] && filled($validated['team_id'])) {
            $team = Team::query()
                ->where('letter_grid_game_id', $letterGridGame->id)
                ->whereKey($validated['team_id'])
                ->firstOrFail();
        }

        $result = $this->claims->claim(
            $letterGridGame,
            $cell,
            $team,
            (bool) $validated['correct']
        );

        $game = $result['game'];
        $claimedCell = $result['cell'];

        if ($request->expectsJson()) {
            $teams = $game->teams->values();

            return response()->json([
                'success' => true,
                'finished' => $game->isFinished(),
                'redirect' => $game->isFinished()
                    ? route('letter-grid.result', $game)
                    : route('letter-grid.play', $game),
                'cell' => [
                    'id' => $claimedCell->id,
                    'letter' => $claimedCell->letter,
                    'team_index' => $this->teamIndex($teams, $claimedCell->claimed_team_id),
                    'answered_correctly' => $claimedCell->answered_correctly,
                    'missed' => $claimedCell->isMissed(),
                ],
                'teams' => $teams->map(fn ($t, $i) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'score' => (int) $t->score,
                    'index' => $i,
                ])->values(),
                'turn_index' => (int) $game->turn_index,
                'claimed' => (int) $game->claimedCount(),
                'resolved' => (int) $game->resolvedCount(),
                'total' => (int) $game->totalCells(),
            ]);
        }

        if ($game->isFinished()) {
            return redirect()->route('letter-grid.result', $game);
        }

        return redirect()
            ->route('letter-grid.play', $game)
            ->with('success', $team ? 'تم احتساب الحرف لفريق '.$team->name : 'تم تخطي الحرف.');
    }

    public function result(LetterGridGame $letterGridGame, Request $request)
    {
        $this->sessions->ensureOwned($letterGridGame, $request->user());

        $winner = null;

        if ($letterGridGame->isPlaying()) {
            $letterGridGame->load('teams');
            $winner = LetterGridWinnerCalculator::determine($letterGridGame);
            $letterGridGame->update([
                'status' => GameStatus::Finished->value,
                'winner_team_id' => $winner?->id,
                'ended_at' => now(),
                'active_cell_id' => null,
            ]);
            if ($letterGridGame->custom_game_id) {
                $letterGridGame->load('customGame.teams');
                $this->sessions->awardCustomGameBonus($letterGridGame);
            }
        }

        $game = $letterGridGame->load(['teams.character', 'cells.claimedTeam', 'grid', 'winnerTeam']);
        $teams = $game->teams->sortByDesc('score')->values();
        $winner ??= LetterGridWinnerCalculator::determine($game);

        $duration = ($game->started_at && $game->ended_at)
            ? $game->started_at->diffInSeconds($game->ended_at)
            : ($game->started_at ? $game->started_at->diffInSeconds(now()) : 0);

        return view('site.letter-grid.result', [
            'game' => $game,
            'teams' => $teams,
            'winner' => $winner,
            'duration' => $duration,
            'isTie' => $winner === null && $teams->count() >= 2,
        ]);
    }

    private function roundLabel(LetterGridGame $game): string
    {
        $claimed = $game->claimedCount();
        $total = max($game->totalCells(), 1);
        $round = intdiv($claimed, max($game->teams->count(), 1)) + 1;

        return 'الجولة '.max(1, min($round, $total));
    }

    private function teamIndex($teams, ?int $teamId): ?int
    {
        if (! $teamId) {
            return null;
        }

        foreach ($teams as $index => $team) {
            if ((int) $team->id === (int) $teamId) {
                return $index;
            }
        }

        return null;
    }
}
