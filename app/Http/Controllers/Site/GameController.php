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
        $game->load(['category', 'teams', 'gameQuestions']);

        $questions = $game->category->questions()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $mapCell = function (Question $question) use ($game) {
            $gq = $game->gameQuestions->firstWhere('question_id', $question->id);

            return [
                'question' => $question,
                'points' => $question->points,
                'used' => $gq && $gq->answered_at,
            ];
        };

        $easyQuestions = $questions->where('points', 200)->values();
        $mediumQuestions = $questions->where('points', 400)->values();
        $hardQuestions = $questions->where('points', 600)->values();

        $easyCells = $easyQuestions->map($mapCell)->pad(6, null);
        $mediumCells = $mediumQuestions->map($mapCell)->pad(6, null);
        $hardCells = $hardQuestions->map($mapCell)->pad(6, null);

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

        $gq = GameQuestion::firstOrCreate([
            'game_id' => $game->id,
            'question_id' => $question->id,
        ]);

        if ($gq->answered_at) {
            return redirect()
                ->route('game.board', $game)
                ->with('error', 'تم احتساب هذا السؤال مسبقاً. اختر سؤالاً آخر.');
        }

        $question->load('options');
        $game->load(['category', 'teams', 'gameQuestions']);
        $timeLimit = $this->timer->limitFor($question);

        $totalQuestions = $game->category->questions()->where('is_active', true)->count();
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

        return redirect()
            ->route('game.board', $game)
            ->with('success', $message);
    }

    public function result(Game $game, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        $game->load(['teams', 'gameQuestions', 'category']);
        $winner = $this->winners->determine($game);

        $game->update([
            'status' => GameStatus::Finished,
            'winner_team_id' => $winner?->id,
            'ended_at' => now(),
        ]);

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

        $rankedTeams = $game->teams->sortByDesc('score')->values()->map(function ($team, $index) use ($answered) {
            $teamAnswers = $answered->where('assigned_team_id', $team->id);

            return [
                'team' => $team,
                'rank' => $index + 1,
                'correct' => $teamAnswers->count(),
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
