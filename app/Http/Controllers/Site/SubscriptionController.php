<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Subscription\PlanService;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SubscriptionController extends Controller
{
    public function __construct(
        private PlanService $plans,
        private SubscriptionService $subscriptions,
        private PaymentGatewayInterface $gateway,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        return view('site.subscription.plans', [
            'plans' => $this->plans->activePlans(),
            'activeSubscription' => $user ? $this->subscriptions->activeSubscription($user) : null,
        ]);
    }

    public function checkout(Plan $plan, Request $request)
    {
        abort_unless($plan->is_active, 404);

        $user = $request->user();

        // External Stripe Payment Link: create pending payment, activate only after paid confirmation
        if (filled($plan->stripe_checkout_url)) {
            $payment = Payment::create([
                'user_id' => $user->id,
                'gateway' => 'stripe_link',
                'gateway_reference' => 'pending_'.Str::lower(Str::random(16)),
                'amount' => $plan->price,
                'currency' => $plan->currency ?? 'AED',
                'status' => 'pending',
                'meta' => [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'duration_days' => $plan->duration_days,
                    'created_via' => 'stripe_checkout_url',
                ],
            ]);

            $request->session()->put('pending_payment_id', $payment->id);

            return redirect()->away($plan->stripe_checkout_url);
        }

        try {
            $result = DB::transaction(function () use ($user, $plan) {
                $charge = $this->gateway->charge($user, $plan, (float) $plan->price);
                $status = (string) ($charge['status'] ?? 'pending');

                $payment = Payment::create([
                    'user_id' => $user->id,
                    'gateway' => $charge['meta']['gateway'] ?? config('payment.default_gateway', 'fake'),
                    'gateway_reference' => $charge['reference'] ?? ('pay_'.Str::lower(Str::random(12))),
                    'amount' => $plan->price,
                    'currency' => $plan->currency ?? 'AED',
                    'status' => $status,
                    'meta' => array_merge($charge['meta'] ?? [], [
                        'plan_id' => $plan->id,
                        'plan_name' => $plan->name,
                        'duration_days' => $plan->duration_days,
                    ]),
                ]);

                if ($payment->status !== 'paid') {
                    return [
                        'ok' => false,
                        'payment' => $payment,
                        'message' => 'لم يتم تأكيد الدفع بعد. الاشتراك لن يُفعَّل إلا بعد نجاح الدفع.',
                    ];
                }

                $subscription = $this->subscriptions->activateFromPaidPayment($user, $plan, $payment);

                return [
                    'ok' => true,
                    'payment' => $payment,
                    'subscription' => $subscription,
                    'message' => 'تم تأكيد الدفع وتفعيل اشتراكك حتى '.$subscription->ends_at->format('Y-m-d H:i'),
                ];
            });
        } catch (RuntimeException $e) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('subscription.index')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Return page after external checkout (Stripe Payment Link success URL).
     * Does NOT activate by itself — activation requires paid confirmation (webhook/admin).
     */
    public function returnFromPayment(Request $request)
    {
        $paymentId = (int) $request->session()->get('pending_payment_id');
        $payment = $paymentId
            ? Payment::query()->where('user_id', $request->user()->id)->find($paymentId)
            : null;

        if ($payment && $payment->status === 'paid' && $payment->subscription_id) {
            return redirect()
                ->route('subscription.index')
                ->with('success', 'تم تفعيل اشتراكك بنجاح.');
        }

        return redirect()
            ->route('subscription.index')
            ->with(
                'error',
                'استلمنا طلب الدفع. الاشتراك يتفعّل تلقائيًا بعد تأكيد الدفع من بوابة الدفع، أو من لوحة الإدارة.'
            );
    }
}
