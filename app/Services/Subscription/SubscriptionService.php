<?php

namespace App\Services\Subscription;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SubscriptionService
{
    /**
     * Activate subscription ONLY after a confirmed paid payment.
     */
    public function activateFromPaidPayment(User $user, Plan $plan, Payment $payment): Subscription
    {
        if ((int) $payment->user_id !== (int) $user->id) {
            throw new RuntimeException('عملية الدفع غير مرتبطة بهذا الحساب.');
        }

        if ($payment->status !== 'paid') {
            throw new RuntimeException('لا يمكن تفعيل الاشتراك قبل تأكيد الدفع.');
        }

        if ($payment->subscription_id) {
            $existing = Subscription::query()->find($payment->subscription_id);
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($user, $plan, $payment) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== 'paid') {
                throw new RuntimeException('لا يمكن تفعيل الاشتراك قبل تأكيد الدفع.');
            }

            if ($payment->subscription_id) {
                return Subscription::query()->findOrFail($payment->subscription_id);
            }

            $this->cancelActiveSubscriptions($user);

            $startsAt = now();
            $endsAt = $startsAt->copy()->addDays($this->durationDays($plan));

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_id' => $payment->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'active',
            ]);

            $payment->update(['subscription_id' => $subscription->id]);

            app(PlayAccessService::class)->unlockForActiveSubscription($user);

            return $subscription->fresh(['plan']);
        });
    }

    /**
     * @deprecated Prefer activateFromPaidPayment
     */
    public function activate(User $user, Plan $plan, ?int $paymentId = null): Subscription
    {
        if (! $paymentId) {
            throw new RuntimeException('تفعيل الاشتراك يتطلب عملية دفع مؤكدة.');
        }

        $payment = Payment::query()->findOrFail($paymentId);

        return $this->activateFromPaidPayment($user, $plan, $payment);
    }

    public function markPaymentPaidAndActivate(Payment $payment, ?string $gatewayReference = null, array $meta = []): Subscription
    {
        return DB::transaction(function () use ($payment, $gatewayReference, $meta) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            $planId = (int) ($payment->meta['plan_id'] ?? 0);
            if ($planId <= 0) {
                throw new RuntimeException('الدفع لا يحتوي على الباقة.');
            }

            $plan = Plan::query()->findOrFail($planId);
            $user = User::query()->findOrFail($payment->user_id);

            if ($payment->status !== 'paid') {
                $payment->update([
                    'status' => 'paid',
                    'gateway_reference' => $gatewayReference ?: $payment->gateway_reference,
                    'meta' => array_merge($payment->meta ?? [], $meta, [
                        'paid_at' => now()->toIso8601String(),
                    ]),
                ]);
                $payment->refresh();
            }

            return $this->activateFromPaidPayment($user, $plan, $payment);
        });
    }

    public function hasActive(User $user): bool
    {
        return $user->hasActiveSubscription();
    }

    public function activeSubscription(User $user): ?Subscription
    {
        return $user->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->latest('ends_at')
            ->first();
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
                $this->cancelActiveSubscriptions($user, $subscription?->getKey());
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
                if ($status === 'active') {
                    app(PlayAccessService::class)->unlockForActiveSubscription($user);
                }

                return $subscription->refresh();
            }

            $created = Subscription::create($attributes);
            if ($status === 'active') {
                app(PlayAccessService::class)->unlockForActiveSubscription($user);
            }

            return $created;
        });
    }

    public function cancelActiveSubscriptions(User $user, ?int $exceptId = null): void
    {
        Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->update([
                'status' => 'cancelled',
                'ends_at' => now(),
            ]);
    }

    public function durationDays(Plan $plan): int
    {
        return max(1, (int) $plan->duration_days);
    }
}
