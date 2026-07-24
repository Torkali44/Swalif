<?php

namespace App\Http\Middleware;

use App\Models\Game;
use App\Services\Subscription\PlayAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FreeTrialLimit
{
    public function __construct(private PlayAccessService $playAccess) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_admin) {
            return $next($request);
        }

        if ($this->playAccess->isBlocked($user)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        if ($user->hasActiveSubscription()) {
            return $next($request);
        }

        /** @var Game|null $game */
        $game = $request->route('game');
        $categoryId = $game?->category_id;

        if ($categoryId && ! $this->playAccess->canPlayCategory($user, (int) $categoryId)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        return $next($request);
    }
}
