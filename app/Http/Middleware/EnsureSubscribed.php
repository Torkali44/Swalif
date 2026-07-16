<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->is_admin || $user->hasActiveSubscription())) {
            return $next($request);
        }

        return redirect()
            ->route('subscription.index')
            ->with('error', 'يلزم اشتراك نشط للوصول إلى هذه الميزة.');
    }
}
