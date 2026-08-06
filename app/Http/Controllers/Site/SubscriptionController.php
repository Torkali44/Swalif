<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
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
    ) {}

    // ─── Subscription Plans Page ──────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();

        return view('site.subscription.plans', [
            'plans'              => $this->plans->activePlans(),
            'activeSubscription' => $user ? $this->subscriptions->activeSubscription($user) : null,
        ]);
    }

    // ─── Checkout: Regular Plan via Stripe Payment Link ───────────────────────

    public function checkout(Plan $plan, Request $request)
    {
        abort_unless($plan->is_active, 404);

        $user = $request->user();

        // ── Path A: External Stripe Payment Link ──────────────────────────────
        if (filled($plan->stripe_checkout_url)) {
            $ref = Payment::generateReference();

            $payment = Payment::create([
                'user_id'           => $user->id,
                'gateway'           => 'stripe_link',
                'gateway_reference' => 'pending_' . Str::lower(Str::random(16)),
                'payment_reference' => $ref,
                'amount'            => $plan->price,
                'currency'          => $plan->currency ?? 'AED',
                'status'            => 'pending',
                'meta'              => [
                    'plan_id'       => $plan->id,
                    'plan_name'     => $plan->name,
                    'duration_days' => $plan->duration_days,
                    'created_via'   => 'stripe_payment_link',
                ],
            ]);

            // Remember in session so the return page can look up this payment
            $request->session()->put('pending_payment_id', $payment->id);

            // Stripe Payment Links accept ?client_reference_id= as a query param.
            // The webhook receives it back in checkout.session.completed.
            $stripeUrl  = $plan->stripe_checkout_url;
            $separator  = str_contains($stripeUrl, '?') ? '&' : '?';
            $stripeUrl .= $separator . 'client_reference_id=' . $ref;

            return redirect()->away($stripeUrl);
        }

        // ── Path B: Direct gateway charge (FakeGateway fallback) ──────────────
        try {
            $result = DB::transaction(function () use ($user, $plan) {
                $ref    = Payment::generateReference();
                $charge = app(\App\Services\Payment\PaymentGatewayInterface::class)
                              ->charge($user, $plan, (float) $plan->price);
                $status = (string) ($charge['status'] ?? 'pending');

                $payment = Payment::create([
                    'user_id'           => $user->id,
                    'gateway'           => $charge['meta']['gateway'] ?? 'fake',
                    'gateway_reference' => $charge['reference'] ?? ('pay_' . Str::lower(Str::random(12))),
                    'payment_reference' => $ref,
                    'amount'            => $plan->price,
                    'currency'          => $plan->currency ?? 'AED',
                    'status'            => $status,
                    'meta'              => array_merge($charge['meta'] ?? [], [
                        'plan_id'       => $plan->id,
                        'plan_name'     => $plan->name,
                        'duration_days' => $plan->duration_days,
                    ]),
                ]);

                if ($payment->status !== 'paid') {
                    return ['ok' => false, 'message' => 'لم يتم تأكيد الدفع بعد. سيُفعَّل الاشتراك خلال لحظات.'];
                }

                $subscription = $this->subscriptions->activateFromPaidPayment($user, $plan, $payment);

                return [
                    'ok'      => true,
                    'message' => 'تم تأكيد الدفع وتفعيل اشتراكك حتى ' . $subscription->ends_at->format('Y-m-d H:i'),
                ];
            });
        } catch (RuntimeException $e) {
            return redirect()->route('subscription.index')->with('error', $e->getMessage());
        }

        return redirect()
            ->route('subscription.index')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    // ─── Checkout: Opening Offer (hardcoded Stripe Payment Link) ─────────────

    public function openingOfferCheckout(Request $request)
    {
        $user = $request->user();
        $ref  = Payment::generateReference();

        $stripeUrl = 'https://buy.stripe.com/eVq3cx1Nn4aH8pn2KU57W0o';

        // Retrieve or create opening offer plan so plan_id is always stored in payment meta
        $plan = Plan::where('stripe_checkout_url', $stripeUrl)->first()
            ?? Plan::where('type', 'daily')->first()
            ?? Plan::where('price', 5.00)->first();

        if (! $plan) {
            $plan = Plan::create([
                'name'                => 'عرض الافتتاح',
                'type'                => 'daily',
                'price'               => 5.00,
                'currency'            => 'AED',
                'duration_days'       => 1,
                'stripe_checkout_url' => $stripeUrl,
                'is_active'           => true,
                'is_recommended'      => true,
                'sort_order'          => 1,
            ]);
        }

        $payment = Payment::create([
            'user_id'           => $user->id,
            'gateway'           => 'stripe_link',
            'gateway_reference' => 'opening_offer_pending_' . Str::lower(Str::random(16)),
            'payment_reference' => $ref,
            'amount'            => $plan->price,
            'currency'          => $plan->currency ?? 'AED',
            'status'            => 'pending',
            'meta'              => [
                'plan_id'       => $plan->id,
                'plan_name'     => $plan->name,
                'duration_days' => $plan->duration_days,
                'offer_type'    => 'opening_offer',
                'created_via'   => 'opening_offer_checkout',
            ],
        ]);

        $request->session()->put('pending_payment_id', $payment->id);

        $stripeUrlWithRef = $stripeUrl . (str_contains($stripeUrl, '?') ? '&' : '?') . 'client_reference_id=' . $ref;

        return redirect()->away($stripeUrlWithRef);
    }

    // ─── Return URL (Stripe redirects here after payment) ─────────────────────

    /**
     * Stripe redirects here after the customer completes (or closes) the payment page.
     *
     * This page is PURELY informational. It does NOT activate subscriptions.
     * Activation is handled exclusively by the Stripe Webhook (checkout.session.completed).
     *
     * We check if the webhook has already fired and activated the subscription before
     * the user arrives here — if so, we redirect to the success page immediately.
     */
    public function returnFromPayment(Request $request)
    {
        $user      = $request->user();
        $paymentId = (int) $request->session()->get('pending_payment_id');

        $payment = $paymentId
            ? Payment::query()->where('user_id', $user->id)->find($paymentId)
            : null;

        // Webhook may have fired already — if subscription is active, go to success
        if ($payment && $payment->isPaid() && $payment->subscription_id) {
            return redirect()->route('subscription.success');
        }

        // Also check if user already has an active subscription from a previous session
        if ($this->subscriptions->activeSubscription($user)) {
            return redirect()->route('subscription.success');
        }

        return view('site.subscription.return', [
            'payment' => $payment,
        ]);
    }

    // ─── Success Page (display-only — no activation) ──────────────────────────

    /**
     * Shown after Stripe redirects back AND the webhook has activated the subscription.
     * This page only DISPLAYS the active subscription — it performs no activation.
     * Only accessible when the user actually has an active subscription.
     */
    public function success(Request $request)
    {
        $user         = $request->user();
        $subscription = $this->subscriptions->activeSubscription($user);

        if (! $subscription) {
            $hasPending = $request->session()->has('pending_payment_id');
            return redirect()
                ->route($hasPending ? 'subscription.return' : 'subscription.index')
                ->with('info', 'جاري معالجة دفعتك. ستُفعَّل الألعاب خلال لحظات بعد تأكيد Stripe.');
        }

        // Clear session reference after confirming subscription is active
        $request->session()->forget('pending_payment_id');

        return view('site.subscription.success', [
            'subscription' => $subscription,
        ]);
    }
}
