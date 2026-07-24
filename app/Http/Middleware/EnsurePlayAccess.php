<?php

namespace App\Http\Middleware;

use App\Services\Subscription\PlayAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlayAccess
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

        return $next($request);
    }
}
