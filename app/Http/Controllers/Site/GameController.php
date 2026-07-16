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
            ->orderBy('points')
            ->orderBy('id')
            ->get();

        $cells = $questions->map(function (Question $question) use ($game) {
            $gq = $game->gameQuestions->firstWhere('question_id', $question->id);

            return [
                'question' => $question,
                'points' => $question->points,
                'used' => $gq && $gq->answered_at,
            ];
        });

        $mid = (int) ceil($cells->count() / 2);
        $leftCells = $cells->take($mid)->values();
        $rightCells = $cells->slice($mid)->values();

        return view('site.game.board', compact('game', 'leftCells', 'rightCells'));
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
        $timeLimit = $this->timer->limitFor($question);

        return view('site.game.question', compact('game', 'question', 'gq', 'timeLimit'));
    }

    public function answer(Game $game, GameQuestion $gameQuestion, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        abort_unless($gameQuestion->game_id === $game->id, 404);
        $gameQuestion->load(['question.options', 'team']);
        $game->load('teams');

        return view('site.game.answer', compact('game', 'gameQuestion'));
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
        $game->load('teams');
        $winner = $this->winners->determine($game);

        $game->update([
            'status' => GameStatus::Finished,
            'winner_team_id' => $winner?->id,
            'ended_at' => now(),
        ]);

        return view('site.game.result', compact('game', 'winner'));
    }
}
