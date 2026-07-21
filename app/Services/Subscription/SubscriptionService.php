<?php

namespace App\Services\Subscription;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function saveForAdmin(
        User $user,
        Plan $plan,
        Carbon $startsAt,
        Carbon $endsAt,
        string $status,
        ?Subscription $subscription = null
    ): Subscription {
        return DB::transaction(function () use ($user, $plan, $startsAt, $endsAt, $status, $subscription) {
            if ($status === 'active') {
                Subscription::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->when($subscription, fn ($query) => $query->whereKeyNot($subscription->getKey()))
                    ->update([
                        'status' => 'cancelled',
                        'ends_at' => now(),
                    ]);
            }

            $attributes = [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => $status,
            ];

            if ($subscription) {
                $subscription->update($attributes);

                return $subscription->refresh();
            }

            return Subscription::create($attributes);
        });
    }
}
