<?php

namespace App\Services\Subscription;

use App\Models\GameQuestion;
use App\Models\User;

class FreeTrialService
{
    public function freeQuestionsUsed(User $user): int
    {
        return GameQuestion::query()
            ->whereHas('game', fn ($q) => $q->where('user_id', $user->id))
            ->whereNotNull('answered_at')
            ->count();
    }

    public function canOpenQuestion(User $user): bool
    {
        if ($user->is_admin || $user->hasActiveSubscription()) {
            return true;
        }

        return $this->freeQuestionsUsed($user) < (int) config('game.free_trial_limit', 5);
    }

    public function remaining(User $user): int
    {
        if ($user->is_admin || $user->hasActiveSubscription()) {
            return PHP_INT_MAX;
        }

        return max(0, (int) config('game.free_trial_limit', 5) - $this->freeQuestionsUsed($user));
    }
}
