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
use App\Services\Subscription\FreeTrialService;
use App\Services\Subscription\PlayAccessService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        private GameSessionService $sessions,
        private ScoringService $scoring,
        private WinnerCalculator $winners,
        private TimerService $timer,
        private FreeTrialService $freeTrial,
        private PlayAccessService $playAccess,
    ) {}

    public function setup(Category $category)
    {
        abort_unless($category->is_active, 404);

        $user = request()->user();
        if ($user && ! $this->playAccess->canPlayCategory($user, (int) $category->id)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        return view('site.game.setup', [
            'category' => $category,
            'freeLeaveWarn' => $user && $this->freeTrial->shouldWarnOnLeave($user),
            'leaveWarningMessage' => $this->freeTrial->leaveWarningMessage(),
            'aboutToClaimFree' => $user
                && $this->freeTrial->isLimitedFreeUser($user)
                && ! $this->freeTrial->hasConsumedFreeCategory($user),
        ]);
    }

    public function start(StoreGameRequest $request)
    {
        $user = $request->user();
        $categoryId = (int) $request->validated('category_id');

        if (! $this->playAccess->canPlayCategory($user, $categoryId)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        $this->freeTrial->claimFreeCategory($user, $categoryId);
        $game = $this->sessions->start($user, $request->validated());

        return redirect()->route('game.board', $game);
    }

    public function board(Game $game, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        $game->load(['category', 'teams', 'gameQuestions.question']);
        $user = $request->user();
        $freeLeaveWarn = $user && $this->freeTrial->shouldWarnOnLeave($user);
        $leaveWarningMessage = $this->freeTrial->leaveWarningMessage();

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
            $level = $question->level instanceof \BackedEnum
                ? $question->level
                : \App\Enums\Difficulty::tryFrom((string) $question->level);

            return [
                'question' => $question,
                // النقاط من المستوى فقط (مش القيمة المخزّنة لو كانت غلط)
                'points' => $level?->points() ?? (int) $question->points,
                'used' => $gq && $gq->answered_at,
            ];
        };

        $placedIds = collect();
        $byLevel = function (string $level, int $points) use ($boardQuestions, $mapCell, $perLevel, &$placedIds) {
            $items = $boardQuestions
                ->filter(function ($q) use ($level, $placedIds) {
                    if ($placedIds->contains($q->id)) {
                        return false;
                    }
                    $qLevel = $q->level instanceof \BackedEnum ? $q->level->value : (string) $q->level;

                    return $qLevel === $level;
                })
                ->unique('id')
                ->take($perLevel)
                ->values();

            // لو ناقص: كمّل بأسئلة نقاطها مطابقة للمستوى وما اتاخدتش
            if ($items->count() < $perLevel) {
                $fill = $boardQuestions
                    ->filter(function ($q) use ($points, $placedIds, $items) {
                        if ($placedIds->contains($q->id) || $items->contains(fn ($item) => (int) $item->id === (int) $q->id)) {
                            return false;
                        }

                        return (int) $q->points === $points;
                    })
                    ->unique('id')
                    ->take($perLevel - $items->count())
                    ->values();

                $items = $items->concat($fill)->unique('id')->take($perLevel)->values();
            }

            $placedIds = $placedIds->merge($items->pluck('id'))->unique()->values();

            return $items->map($mapCell)->pad($perLevel, null);
        };

        $easyCells = $byLevel('easy', 200);
        $mediumCells = $byLevel('medium', 400);
        $hardCells = $byLevel('hard', 600);

        $answeredQuestions = $game->gameQuestions->whereNotNull('answered_at')->count();
        $teams = $game->teams->values();
        $activeTeam = $teams->count() > 0
            ? (($answeredQuestions % 2 === 0) ? $teams->get(0) : $teams->get(1))
            : null;

        return view('site.game.board', compact(
            'game',
            'easyCells',
            'mediumCells',
            'hardCells',
            'activeTeam',
            'freeLeaveWarn',
            'leaveWarningMessage',
        ));
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
        $user = $request->user();
        $freeLeaveWarn = $user && $this->freeTrial->shouldWarnOnLeave($user);
        $leaveWarningMessage = $this->freeTrial->leaveWarningMessage();

        return view('site.game.question', compact(
            'game',
            'question',
            'gq',
            'timeLimit',
            'totalQuestions',
            'answeredQuestions',
            'freeLeaveWarn',
            'leaveWarningMessage',
        ));
    }

    public function answer(Game $game, GameQuestion $gameQuestion, Request $request)
    {
        $this->sessions->ensureOwned($game, $request->user());
        abort_unless($gameQuestion->game_id === $game->id, 404);

        $gameQuestion->loadMissing(['question.options', 'selectedOption']);

        if ($request->isMethod('post') && ! $gameQuestion->answered_at) {
            $data = $request->validate([
                'selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
                'player_answer' => ['nullable', 'string', 'max:2000'],
            ]);

            if (! empty($data['selected_option_id'])) {
                $belongs = $gameQuestion->question->options
                    ->contains(fn ($opt) => (int) $opt->id === (int) $data['selected_option_id']);
                abort_unless($belongs, 422);
            }

            $gameQuestion->update([
                'selected_option_id' => $data['selected_option_id'] ?? $gameQuestion->selected_option_id,
                'player_answer' => array_key_exists('player_answer', $data)
                    ? ($data['player_answer'] ?: null)
                    : $gameQuestion->player_answer,
            ]);
            $gameQuestion->refresh()->load(['question.options', 'selectedOption']);
        }

        $game->load(['category', 'teams', 'gameQuestions']);
        $answeredQuestions = $game->gameQuestions->whereNotNull('answered_at')->count();
        $playerCorrect = $gameQuestion->playerChoseCorrectly();
        $user = $request->user();
        $freeLeaveWarn = $user && $this->freeTrial->shouldWarnOnLeave($user);
        $leaveWarningMessage = $this->freeTrial->leaveWarningMessage();

        return view('site.game.answer', compact(
            'game',
            'gameQuestion',
            'answeredQuestions',
            'playerCorrect',
            'freeLeaveWarn',
            'leaveWarningMessage',
        ));
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
            ? 'تم إضافة '.$gameQuestion->question->displayPoints()." نقطة لفريق {$team->name}"
            : 'تم تسجيل السؤال بدون نقاط (إجابة خاطئة)';

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

        // حدّث الفائز دائمًا حسب أعلى نقاط (حتى لو اللعبة خلصت بدري)
        $game->update([
            'status' => GameStatus::Finished,
            'winner_team_id' => $winner?->id,
            'ended_at' => $game->ended_at ?? now(),
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

        $teamsByOrder = $game->teams->sortBy('id')->values();
        $teamStats = $teamsByOrder->mapWithKeys(fn ($t) => [
            $t->id => ['correct' => 0, 'wrong' => 0],
        ])->all();

        /*
         * منطق النتيجة:
         * - الفريق اللي خد النقط → صحيحة
         * - لو كان دور فريق ومخدش النقط (غلط / ولا فريق / الفريق التاني) → خاطئة لدوره
         */
        $answered->sortBy('answered_at')->values()->each(function ($gq, $i) use ($teamsByOrder, &$teamStats) {
            $turnTeam = $gq->turn_team_id
                ? $teamsByOrder->firstWhere('id', (int) $gq->turn_team_id)
                : $teamsByOrder->get($i % max(1, $teamsByOrder->count()));

            if (! $turnTeam) {
                return;
            }

            $turnId = (int) $turnTeam->id;
            $assignedId = $gq->assigned_team_id ? (int) $gq->assigned_team_id : null;

            if ($gq->answered_correctly && $assignedId) {
                $teamStats[$assignedId]['correct'] = ($teamStats[$assignedId]['correct'] ?? 0) + 1;

                // دور الفريق ومخدش النقط → خاطئة عليه
                if ($assignedId !== $turnId) {
                    $teamStats[$turnId]['wrong'] = ($teamStats[$turnId]['wrong'] ?? 0) + 1;
                }

                return;
            }

            // إجابة غلط / بدون نقاط → خاطئة على صاحب الدور
            $teamStats[$turnId]['wrong'] = ($teamStats[$turnId]['wrong'] ?? 0) + 1;
        });

        $rankedTeams = $game->teams
            ->sortByDesc(fn ($team) => (int) $team->score)
            ->values()
            ->map(function ($team, $index) use ($teamStats) {
                $stats = $teamStats[$team->id] ?? ['correct' => 0, 'wrong' => 0];

                return [
                    'team' => $team,
                    'rank' => $index + 1,
                    'correct' => (int) $stats['correct'],
                    'wrong' => (int) $stats['wrong'],
                ];
            });

        // الفائز المعروض = صاحب المركز الأول في الترتيب (أعلى نقاط)
        if ($rankedTeams->isNotEmpty()) {
            $first = $rankedTeams->first();
            $second = $rankedTeams->get(1);
            $isTie = $second && (int) $first['team']->score === (int) $second['team']->score;
            $winner = $isTie ? null : $first['team'];

            if ((int) ($game->winner_team_id ?? 0) !== (int) ($winner?->id ?? 0)) {
                $game->update(['winner_team_id' => $winner?->id]);
            }
        }
        $user = $request->user();
        $needsSubscribe = $user && $this->freeTrial->hasConsumedFreeCategory($user);
        $subscribeMessage = $this->freeTrial->subscribeRequiredMessage();

        return view('site.game.result', compact(
            'game',
            'winner',
            'correctCount',
            'wrongCount',
            'accuracy',
            'duration',
            'rankedTeams',
            'needsSubscribe',
            'subscribeMessage',
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
