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
        // Single DB::transaction — the lockForUpdate below is the concurrency guard.
        // We do NOT call activateFromPaidPayment() to avoid a nested transaction;
        // the activation logic is inlined here so there is exactly ONE atomic boundary.
        return DB::transaction(function () use ($payment, $gatewayReference, $meta) {

            // Re-fetch with an exclusive lock to prevent concurrent double-activation
            // (e.g. two Stripe webhook retries arriving within milliseconds of each other)
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            // ── Idempotency: already fully activated inside this transaction ───
            if ($payment->status === 'paid' && $payment->subscription_id) {
                return Subscription::query()->findOrFail($payment->subscription_id);
            }

            // ── Resolve Plan & User ───────────────────────────────────────────
            $planId = (int) ($payment->meta['plan_id'] ?? 0);

            if ($planId <= 0) {
                // Fail-safe recovery for legacy/edge-case payments missing plan_id
                $fallbackPlan = null;
                if (($payment->meta['offer_type'] ?? '') === 'opening_offer') {
                    $fallbackPlan = Plan::where('type', 'daily')->first()
                        ?? Plan::where('price', 5.00)->first();
                }
                if (! $fallbackPlan) {
                    $fallbackPlan = Plan::where('price', $payment->amount)->first()
                        ?? Plan::where('is_active', true)->first();
                }

                if ($fallbackPlan) {
                    $planId = $fallbackPlan->id;
                    \Illuminate\Support\Facades\Log::warning('[SubscriptionService] Missing plan_id in payment meta — recovered using fallback plan', [
                        'payment_id'        => $payment->id,
                        'payment_ref'       => $payment->payment_reference,
                        'recovered_plan_id' => $planId,
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::error('[SubscriptionService] Payment lacks plan_id and no fallback plan could be found', [
                        'payment_id'  => $payment->id,
                        'payment_ref' => $payment->payment_reference,
                    ]);
                    throw new RuntimeException("الدفع رقم {$payment->id} لا يحتوي على معرّف الباقة (plan_id).");
                }
            }

            $plan = Plan::query()->findOrFail($planId);
            $user = User::query()->findOrFail($payment->user_id);

            // ── Mark payment as paid ──────────────────────────────────────────
            $payment->update([
                'status'            => 'paid',
                'gateway_reference' => $gatewayReference ?: $payment->gateway_reference,
                'meta'              => array_merge($payment->meta ?? [], $meta, [
                    'paid_at' => now()->toIso8601String(),
                ]),
            ]);
            $payment->refresh();

            // ── Cancel any existing active subscriptions ──────────────────────
            $this->cancelActiveSubscriptions($user);

            // ── Create new subscription ───────────────────────────────────────
            $startsAt = now();
            $endsAt   = $startsAt->copy()->addDays($this->durationDays($plan));

            $subscription = Subscription::create([
                'user_id'    => $user->id,
                'plan_id'    => $plan->id,
                'payment_id' => $payment->id,
                'starts_at'  => $startsAt,
                'ends_at'    => $endsAt,
                'status'     => 'active',
            ]);

            // ── Link subscription back to payment ─────────────────────────────
            $payment->update(['subscription_id' => $subscription->id]);

            // ── Unblock game access ───────────────────────────────────────────
            app(PlayAccessService::class)->unlockForActiveSubscription($user);

            return $subscription->fresh(['plan']);
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
