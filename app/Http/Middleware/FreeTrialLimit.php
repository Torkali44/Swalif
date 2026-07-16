<?php

namespace App\Http\Middleware;

use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\Question;
use App\Services\Subscription\FreeTrialService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FreeTrialLimit
{
    public function __construct(private FreeTrialService $freeTrial) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_admin || $user->hasActiveSubscription()) {
            return $next($request);
        }

        /** @var Game|null $game */
        $game = $request->route('game');
        /** @var Question|null $question */
        $question = $request->route('question');

        if ($game && $question) {
            $alreadyOpened = GameQuestion::query()
                ->where('game_id', $game->id)
                ->where('question_id', $question->id)
                ->exists();

            if ($alreadyOpened) {
                return $next($request);
            }
        }

        if (! $this->freeTrial->canOpenQuestion($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', 'انتهت الأسئلة التجريبية المجانية. اشترك للمتابعة.');
        }

        return $next($request);
    }
}
