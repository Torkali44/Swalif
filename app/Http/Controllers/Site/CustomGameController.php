<?php

namespace App\Http\Controllers\Site;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomGameRequest;
use App\Models\Category;
use App\Models\CustomGame;
use App\Models\CustomGameQuestion;
use App\Models\Question;
use App\Models\Team;
use App\Services\Category\CategoryService;
use App\Services\Game\CustomGameSessionService;
use App\Services\Game\ScoringService;
use App\Services\Game\TimerService;
use App\Services\Game\WinnerCalculator;
use App\Services\Subscription\FreeTrialService;
use App\Services\Subscription\PlayAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomGameController extends Controller
{
    public function __construct(
        private CustomGameSessionService $sessions,
        private ScoringService $scoring,
        private WinnerCalculator $winners,
        private TimerService $timer,
        private FreeTrialService $freeTrial,
        private PlayAccessService $playAccess,
        private CategoryService $categories,
        private \App\Services\Category\CategoryPlayPoolService $playPool,
    ) {}

    // ── صفحة إنشاء اللعبة ──────────────────────────────────────

    public function create(Request $request)
    {
        $user = $request->user();

        // المستخدم المحجوب يُوجه لصفحة الاشتراك
        if ($user && $this->playAccess->isBlocked($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        // المستخدم المجاني يملك لعبة خاصة واحدة فقط مجاناً
        if ($user && ! $this->freeTrial->canCreateCustomGame($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->freeTrial->customGameSubscribeRequiredMessage());
        }

        $allCategories = Cache::remember('categories.active_ordered', 120, fn () => $this->categories->activeOrdered());
        $allCategories = $this->playPool->decorateCategories($allCategories, $user);
        $classifications = Cache::remember('classifications.active_ordered', 120, fn () => \App\Models\Classification::where('is_active', true)->orderBy('sort_order')->get());

        return view('site.custom-game.create', [
            'categories' => $allCategories,
            'classifications' => $classifications,
        ]);
    }

    // ── إنشاء اللعبة ───────────────────────────────────────────

    public function store(StoreCustomGameRequest $request)
    {
        $user = $request->user();

        if ($this->playAccess->isBlocked($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        if (! $this->freeTrial->canCreateCustomGame($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->freeTrial->customGameSubscribeRequiredMessage());
        }

        $game = $this->sessions->start($user, $request->validated());

        return redirect()->route('custom-game.board', $game);
    }

    // ── لوحة اللعب ──────────────────────────────────────────────

    public function board(CustomGame $customGame, Request $request)
    {
        $this->sessions->ensureOwned($customGame, $request->user());

        $customGame->load(['categories', 'teams', 'customGameQuestions.question']);

        // بناء بيانات Board لكل فئة
        $categoriesData = $customGame->categories->map(function (Category $category) use ($customGame) {
            $board = $this->sessions->buildBoardForCategory($customGame, $category);
            return [
                'category'   => $category,
                'easyCells'  => $board['easy'],
                'mediumCells'=> $board['medium'],
                'hardCells'  => $board['hard'],
            ];
        });

        $activeTeam = $this->sessions->activeTeam($customGame);

        return view('site.custom-game.board', [
            'game'           => $customGame,
            'categoriesData' => $categoriesData,
            'activeTeam'     => $activeTeam,
        ]);
    }

    // ── صفحة السؤال ─────────────────────────────────────────────

    public function question(CustomGame $customGame, Question $question, Request $request)
    {
        $this->sessions->ensureOwned($customGame, $request->user());

        // التحقق أن السؤال ينتمي لإحدى فئات اللعبة الخاصة
        $cgq = CustomGameQuestion::query()
            ->where('custom_game_id', $customGame->id)
            ->where('question_id', $question->id)
            ->firstOrFail();

        if ($cgq->answered_at) {
            return redirect()
                ->route('custom-game.board', $customGame)
                ->with('error', 'تم احتساب هذا السؤال مسبقاً. اختر سؤالاً آخر.');
        }

        $question->load('options');
        $customGame->load(['categories', 'teams', 'customGameQuestions']);

        $totalQuestions    = max(1, $customGame->customGameQuestions->count());
        $answeredQuestions = $customGame->customGameQuestions->whereNotNull('answered_at')->count();
        $timeLimit         = $this->timer->limitFor($question);

        return view('site.custom-game.question', compact(
            'customGame',
            'question',
            'cgq',
            'timeLimit',
            'totalQuestions',
            'answeredQuestions',
        ));
    }

    // ── تسجيل الإجابة ──────────────────────────────────────────

    public function answer(CustomGame $customGame, CustomGameQuestion $customGameQuestion, Request $request)
    {
        $this->sessions->ensureOwned($customGame, $request->user());
        abort_unless($customGameQuestion->custom_game_id === $customGame->id, 404);

        $customGameQuestion->loadMissing(['question.options', 'selectedOption']);

        if ($request->isMethod('post') && ! $customGameQuestion->answered_at) {
            $data = $request->validate([
                'selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
                'player_answer'      => ['nullable', 'string', 'max:2000'],
            ]);

            if (! empty($data['selected_option_id'])) {
                $belongs = $customGameQuestion->question->options
                    ->contains(fn ($opt) => (int) $opt->id === (int) $data['selected_option_id']);
                abort_unless($belongs, 422);
            }

            $customGameQuestion->update([
                'selected_option_id' => $data['selected_option_id'] ?? $customGameQuestion->selected_option_id,
                'player_answer'      => array_key_exists('player_answer', $data)
                    ? ($data['player_answer'] ?: null)
                    : $customGameQuestion->player_answer,
            ]);
            $customGameQuestion->refresh()->load(['question.options', 'selectedOption']);
        }

        $customGame->load(['categories', 'teams', 'customGameQuestions']);
        $answeredQuestions = $customGame->customGameQuestions->whereNotNull('answered_at')->count();
        $playerCorrect     = $customGameQuestion->playerChoseCorrectly();

        return view('site.custom-game.answer', compact(
            'customGame',
            'customGameQuestion',
            'answeredQuestions',
            'playerCorrect',
        ));
    }

    // ── تعيين النقاط ────────────────────────────────────────────

    public function assign(CustomGame $customGame, CustomGameQuestion $customGameQuestion, Request $request)
    {
        $this->sessions->ensureOwned($customGame, $request->user());
        abort_unless($customGameQuestion->custom_game_id === $customGame->id, 404);

        $data   = $request->validate(['team_id' => ['nullable', 'integer', 'exists:teams,id']]);
        $teamId = $data['team_id'] ?? null;

        $team = $teamId
            ? Team::query()->where('custom_game_id', $customGame->id)->whereKey($teamId)->firstOrFail()
            : null;

        // استخدام transaction + lock لمنع الحساب المزدوج عند التزامن
        $assigned = DB::transaction(function () use ($customGameQuestion, $team) {
            $cgq = CustomGameQuestion::query()
                ->whereKey($customGameQuestion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($cgq->answered_at !== null) {
                return false;
            }

            $cgq->loadMissing('question');
            $points = $team ? $cgq->question->displayPoints() : 0;

            $cgq->update([
                'assigned_team_id'   => $team?->id,
                'points_awarded'     => $points,
                'answered_correctly' => $team !== null,
                'answered_at'        => now(),
            ]);

            if ($team && $points > 0) {
                Team::query()->whereKey($team->id)->increment('score', $points);
            }

            return true;
        });

        if (! $assigned) {
            return redirect()
                ->route('custom-game.board', $customGame)
                ->with('error', 'تم احتساب هذا السؤال مسبقاً.');
        }

        $customGameQuestion->refresh()->loadMissing('question');
        $points  = $customGameQuestion->points_awarded ?? 0;
        $message = $team
            ? 'تم إضافة '.$points.' نقطة لفريق '.$team->name
            : 'تم تسجيل السؤال بدون نقاط (إجابة خاطئة)';

        $customGame->load('customGameQuestions');
        $total    = $customGame->customGameQuestions->count();
        $answered = $customGame->customGameQuestions->whereNotNull('answered_at')->count();

        if ($total > 0 && $answered >= $total) {
            return redirect()
                ->route('custom-game.result', $customGame)
                ->with('game_just_ended', true)
                ->with('success', $message);
        }

        return redirect()
            ->route('custom-game.board', $customGame)
            ->with('success', $message);
    }

    // ── صفحة النتيجة ────────────────────────────────────────────

    public function result(CustomGame $customGame, Request $request)
    {
        $this->sessions->ensureOwned($customGame, $request->user());
        $customGame->load(['teams', 'customGameQuestions', 'categories']);

        // حساب الفائز
        $teams   = $customGame->teams->sortByDesc(fn ($t) => (int) $t->score)->values();
        $winner  = null;
        $isTie   = false;

        if ($teams->count() >= 2) {
            $isTie  = (int) $teams->get(0)->score === (int) $teams->get(1)->score;
            $winner = $isTie ? null : $teams->get(0);
        } elseif ($teams->count() === 1) {
            $winner = $teams->get(0);
        }

        // تحديث اللعبة
        $customGame->update([
            'status'         => GameStatus::Finished,
            'winner_team_id' => $winner?->id,
            'ended_at'       => $customGame->ended_at ?? now(),
        ]);

        $answered    = $customGame->customGameQuestions->whereNotNull('answered_at');
        $correctCount = $answered->where('answered_correctly', true)->count();
        $wrongCount   = $answered->where('answered_correctly', false)->count();
        $totalAnswered = $answered->count();
        $accuracy     = $totalAnswered > 0 ? (int) round(($correctCount / $totalAnswered) * 100) : 0;

        $duration = null;
        if ($customGame->started_at && $customGame->ended_at) {
            $seconds  = $customGame->started_at->diffInSeconds($customGame->ended_at);
            $duration = sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
        }

        $teamsByOrder = $customGame->teams->values();
        $teamStats = $customGame->teams->mapWithKeys(fn ($t) => [
            (int) $t->id => ['correct' => 0, 'wrong' => 0],
        ])->all();

        $answered->sortBy('answered_at')->values()->each(function ($cgq, $i) use ($teamsByOrder, &$teamStats) {
            $turnTeam = $cgq->turn_team_id
                ? $teamsByOrder->firstWhere('id', (int) $cgq->turn_team_id)
                : $teamsByOrder->get($i % max(1, $teamsByOrder->count()));

            if (! $turnTeam) {
                return;
            }

            $turnId = (int) $turnTeam->id;
            $assignedId = $cgq->assigned_team_id ? (int) $cgq->assigned_team_id : null;

            if ($cgq->answered_correctly && $assignedId && isset($teamStats[$assignedId])) {
                $teamStats[$assignedId]['correct']++;

                if ($assignedId !== $turnId && isset($teamStats[$turnId])) {
                    $teamStats[$turnId]['wrong']++;
                }

                return;
            }

            if (isset($teamStats[$turnId])) {
                $teamStats[$turnId]['wrong']++;
            }
        });

        $rankedTeams = $teams->values()->map(function ($t, $i) use ($teamStats) {
            $stats = $teamStats[$t->id] ?? ['correct' => 0, 'wrong' => 0];

            return [
                'team'    => $t,
                'rank'    => $i + 1,
                'correct' => (int) $stats['correct'],
                'wrong'   => (int) $stats['wrong'],
            ];
        });

        return view('site.custom-game.result', compact(
            'customGame',
            'winner',
            'correctCount',
            'wrongCount',
            'accuracy',
            'duration',
            'rankedTeams',
            'isTie',
        ));
    }

    // ── وسائل المساعدة ──────────────────────────────────────────

    public function useHelper(CustomGame $customGame, Team $team, string $helper, Request $request)
    {
        $this->sessions->ensureOwned($customGame, $request->user());
        abort_unless((int) $team->custom_game_id === (int) $customGame->id, 404);

        $validHelpers = ['swap', 'phone_friend', 'two_answers'];
        abort_unless(in_array($helper, $validHelpers), 400);

        $helpers = $team->helpers_left ?? config('game.default_helpers');

        if (($helpers[$helper] ?? 0) <= 0) {
            return response()->json(['success' => false, 'message' => 'لقد استخدمت هذه المساعدة بالفعل.'], 400);
        }

        $cgqId = $request->input('cgq_id');
        $cgq   = $cgqId ? $customGame->customGameQuestions()->where('id', $cgqId)->first() : null;

        $removeOptionIds = [];

        if ($helper === 'swap' && $cgq) {
            $cgq->update([
                'assigned_team_id'   => null,
                'points_awarded'     => 0,
                'answered_correctly' => false,
                'answered_at'        => now(),
            ]);
        }

        if ($helper === 'two_answers' && $cgq) {
            $question = $cgq->question;
            $options  = $question->options->filter(fn ($o) => filled($o->option_text))->values();
            if ($options->count() !== 4) {
                return response()->json([
                    'success' => false,
                    'message' => 'وسيلة حذف إجابتين تتاح فقط في الأسئلة ذات الـ 4 اختيارات ✌️',
                ], 400);
            }
            $wrongOptions = $options->where('is_correct', false)->pluck('id')->all();
            shuffle($wrongOptions);
            $removeOptionIds = array_slice($wrongOptions, 0, 2);
        }

        $helpers[$helper] = max(0, $helpers[$helper] - 1);
        $team->update(['helpers_left' => $helpers]);

        return response()->json([
            'success'           => true,
            'message'           => 'تم استخدام وسيلة المساعدة بنجاح.',
            'helpers_left'      => $helpers,
            'remove_option_ids' => $removeOptionIds,
            'redirect_url'      => ($helper === 'swap' && $cgq) ? route('custom-game.board', $customGame) : null,
        ]);
    }

    // ── تعديل النقاط يدوياً ─────────────────────────────────────

    public function adjustScore(CustomGame $customGame, Team $team, Request $request)
    {
        $this->sessions->ensureOwned($customGame, $request->user());
        abort_unless((int) $team->custom_game_id === (int) $customGame->id, 404);

        $data     = $request->validate(['amount' => ['required', 'integer', 'min:-9999', 'max:9999']]);
        $newScore = max(0, min(99999, $team->score + $data['amount']));
        $team->update(['score' => $newScore]);

        return response()->json(['success' => true, 'score' => $newScore]);
    }
}
