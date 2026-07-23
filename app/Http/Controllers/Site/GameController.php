<?php

namespace App\Http\Controllers\Site;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGameRequest;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Question;
use App\Models\Team;
use App\Services\Game\GameSessionService;
use App\Services\Game\ScoringService;
use App\Services\Game\TimerService;
use App\Services\Game\WinnerCalculator;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        private GameSessionService $sessions,
        private ScoringService $scoring,
        private WinnerCalculator $winners,
        private TimerService $timer,
    ) {}

    public function setup(Category $category)
    {
        abort_unless($category->is_active, 404);

        return view('site.game.setup', compact('category'));
    }

    public function start(StoreGameRequest $request)
    {
        $game = $this->sessions->start($request->user(), $request->validated());

        return redirect()->route('game.board', $game);
    }

    public function board(Game $game, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        $game->load(['category', 'teams', 'gameQuestions.question']);

        $perLevel = (int) config('game.questions_per_level', 6);
        $expected = $perLevel * 3;

        // Prefer the fixed set locked when the game started (18 questions)
        $boardQuestions = collect();
        if ($game->gameQuestions->count() >= $expected) {
            $boardQuestions = $game->gameQuestions
                ->filter(fn ($gq) => $gq->question)
                ->map(fn ($gq) => $gq->question)
                ->values();
        }

        // Legacy / incomplete boards: use category pool (still capped at 6/level)
        if ($boardQuestions->count() < $expected) {
            $boardQuestions = $game->category->questions()
                ->where('is_active', true)
                ->orderBy('id')
                ->get();
        }

        $mapCell = function ($question) use ($game) {
            if (! $question) {
                return null;
            }

            $gq = $game->gameQuestions->firstWhere('question_id', $question->id);

            return [
                'question' => $question,
                'points' => $question->points,
                'used' => $gq && $gq->answered_at,
            ];
        };

        $byLevel = function (string $level, int $points) use ($boardQuestions, $mapCell, $perLevel) {
            $items = $boardQuestions
                ->filter(function ($q) use ($level, $points) {
                    $qLevel = $q->level instanceof \BackedEnum ? $q->level->value : (string) $q->level;

                    return $qLevel === $level || (int) $q->points === $points;
                })
                ->unique('id')
                ->take($perLevel)
                ->values()
                ->map($mapCell);

            return $items->pad($perLevel, null);
        };

        $easyCells = $byLevel('easy', 200);
        $mediumCells = $byLevel('medium', 400);
        $hardCells = $byLevel('hard', 600);

        $answeredQuestions = $game->gameQuestions->whereNotNull('answered_at')->count();
        $teams = $game->teams->values();
        $activeTeam = $teams->count() > 0
            ? (($answeredQuestions % 2 === 0) ? $teams->get(0) : $teams->get(1))
            : null;

        return view('site.game.board', compact('game', 'easyCells', 'mediumCells', 'hardCells', 'activeTeam'));
    }

    public function question(Game $game, Question $question, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        abort_unless($question->category_id === $game->category_id, 404);
        abort_unless($question->is_active, 404);

        try {
            $gq = GameQuestion::firstOrCreate([
                'game_id' => $game->id,
                'question_id' => $question->id,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $gq = GameQuestion::query()
                ->where('game_id', $game->id)
                ->where('question_id', $question->id)
                ->firstOrFail();
        }

        if ($gq->answered_at) {
            return redirect()
                ->route('game.board', $game)
                ->with('error', 'تم احتساب هذا السؤال مسبقاً. اختر سؤالاً آخر.');
        }

        $question->load('options');
        $game->load(['category', 'teams', 'gameQuestions']);
        $timeLimit = $this->timer->limitFor($question);

        $perLevel = (int) config('game.questions_per_level', 6);
        $expected = $perLevel * 3;
        $totalQuestions = $game->gameQuestions->count() >= $expected
            ? $expected
            : max($expected, $game->category->questions()->where('is_active', true)->count());
        $answeredQuestions = $game->gameQuestions->whereNotNull('answered_at')->count();

        return view('site.game.question', compact(
            'game',
            'question',
            'gq',
            'timeLimit',
            'totalQuestions',
            'answeredQuestions',
        ));
    }

    public function answer(Game $game, GameQuestion $gameQuestion, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        abort_unless($gameQuestion->game_id === $game->id, 404);
        $game->load(['category', 'teams', 'gameQuestions']);
        $answeredQuestions = $game->gameQuestions->whereNotNull('answered_at')->count();

        return view('site.game.answer', compact('game', 'gameQuestion', 'answeredQuestions'));
    }

    public function assign(Game $game, GameQuestion $gameQuestion, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        abort_unless($gameQuestion->game_id === $game->id, 404);

        $data = $request->validate([
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ]);

        $teamId = $data['team_id'] ?? null;

        $team = $teamId
            ? Team::query()->where('game_id', $game->id)->whereKey($teamId)->firstOrFail()
            : null;

        $assigned = $this->scoring->assignPoints($game, $gameQuestion, $team);

        if (! $assigned) {
            return redirect()
                ->route('game.board', $game)
                ->with('error', 'تم احتساب هذا السؤال مسبقاً.');
        }

        $message = $team
            ? "تم إضافة {$gameQuestion->question->points} نقطة لفريق {$team->name}"
            : 'تم تسجيل السؤال بدون نقاط';

        $game->load('gameQuestions');
        $total = $game->gameQuestions->count();
        $answered = $game->gameQuestions->whereNotNull('answered_at')->count();
        $gameComplete = $total > 0 && $answered >= $total;

        if ($gameComplete) {
            return redirect()
                ->route('game.result', $game)
                ->with('game_just_ended', true)
                ->with('success', $message);
        }

        return redirect()
            ->route('game.board', $game)
            ->with('success', $message);
    }

    public function result(Game $game, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        $game->load(['teams', 'gameQuestions', 'category']);
        $winner = $this->winners->determine($game);

        if ($game->status !== GameStatus::Finished) {
            $game->update([
                'status' => GameStatus::Finished,
                'winner_team_id' => $winner?->id,
                'ended_at' => now(),
            ]);
        }

        $answered = $game->gameQuestions->whereNotNull('answered_at');
        $correctCount = $answered->where('answered_correctly', true)->count();
        $wrongCount = $answered->where('answered_correctly', false)->count();
        $totalAnswered = $answered->count();
        $accuracy = $totalAnswered > 0 ? (int) round(($correctCount / $totalAnswered) * 100) : 0;

        $duration = null;
        if ($game->started_at && $game->ended_at) {
            $seconds = $game->started_at->diffInSeconds($game->ended_at);
            $duration = sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
        }

        $teamsByOrder = $game->teams->values();
        $teamStats = $teamsByOrder->mapWithKeys(fn ($t) => [
            $t->id => ['correct' => 0, 'wrong' => 0],
        ])->all();

        // Correct = فريق خد النقط. Wrong = دور الفريق ومخدش نقط (ولا فريق أو الفريق التاني).
        $answered->sortBy('answered_at')->values()->each(function ($gq, $i) use ($teamsByOrder, &$teamStats) {
            $turnTeam = $teamsByOrder->get($i % max(1, $teamsByOrder->count()));
            if (! $turnTeam) {
                return;
            }

            $assignedId = $gq->assigned_team_id ? (int) $gq->assigned_team_id : null;

            if ($gq->answered_correctly && $assignedId) {
                $teamStats[$assignedId]['correct'] = ($teamStats[$assignedId]['correct'] ?? 0) + 1;

                if ($assignedId !== (int) $turnTeam->id) {
                    $teamStats[$turnTeam->id]['wrong'] = ($teamStats[$turnTeam->id]['wrong'] ?? 0) + 1;
                }

                return;
            }

            $teamStats[$turnTeam->id]['wrong'] = ($teamStats[$turnTeam->id]['wrong'] ?? 0) + 1;
        });

        $rankedTeams = $game->teams->sortByDesc('score')->values()->map(function ($team, $index) use ($teamStats) {
            $stats = $teamStats[$team->id] ?? ['correct' => 0, 'wrong' => 0];

            return [
                'team' => $team,
                'rank' => $index + 1,
                'correct' => (int) $stats['correct'],
                'wrong' => (int) $stats['wrong'],
            ];
        });

        return view('site.game.result', compact(
            'game',
            'winner',
            'correctCount',
            'wrongCount',
            'accuracy',
            'duration',
            'rankedTeams',
        ));
    }

    public function useHelper(Game $game, Team $team, string $helper, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        abort_unless((int) $team->game_id === (int) $game->id, 404);

        $validHelpers = ['swap', 'phone_friend', 'two_answers'];
        abort_unless(in_array($helper, $validHelpers), 400);

        $helpers = $team->helpers_left ?? [];
        if (empty($helpers)) {
            $helpers = config('game.default_helpers');
        }

        if (($helpers[$helper] ?? 0) <= 0) {
            return response()->json(['success' => false, 'message' => 'لقد استخدمت هذه المساعدة بالفعل.'], 400);
        }

        $helpers[$helper] = max(0, $helpers[$helper] - 1);
        $team->update(['helpers_left' => $helpers]);

        return response()->json([
            'success' => true,
            'message' => 'تم استخدام وسيلة المساعدة بنجاح.',
            'helpers_left' => $helpers,
        ]);
    }

    public function adjustScore(Game $game, Team $team, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        abort_unless((int) $team->game_id === (int) $game->id, 404);

        $data = $request->validate([
            'amount' => ['required', 'integer'],
        ]);

        $newScore = max(0, $team->score + $data['amount']);
        $team->update(['score' => $newScore]);

        return response()->json([
            'success' => true,
            'score' => $newScore,
        ]);
    }
}
