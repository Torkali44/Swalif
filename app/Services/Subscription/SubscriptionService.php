<?php

namespace App\Services\Subscription;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionService
{
    public function activate(User $user, Plan $plan, ?int $paymentId = null): Subscription
    {
        Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'payment_id' => $paymentId,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_days),
            'status' => 'active',
        ]);
    }

    public function hasActive(User $user): bool
    {
        return $user->hasActiveSubscription();
    }
}
