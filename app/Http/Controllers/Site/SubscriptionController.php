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

    // ─── Public: Subscription Plans Page ─────────────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();

        return view('site.subscription.plans', [
            'plans'               => $this->plans->activePlans(),
            'activeSubscription'  => $user ? $this->subscriptions->activeSubscription($user) : null,
        ]);
    }

    // ─── Checkout: Regular Plans (Stripe Payment Link) ────────────────────────

    public function checkout(Plan $plan, Request $request)
    {
        abort_unless($plan->is_active, 404);

        $user = $request->user();

        // Plans with an external Stripe Payment Link
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
                    'plan_id'      => $plan->id,
                    'plan_name'    => $plan->name,
                    'duration_days'=> $plan->duration_days,
                    'created_via'  => 'stripe_checkout_url',
                ],
            ]);

            // Store in session so return page can identify this user's payment
            $request->session()->put('pending_payment_id', $payment->id);

            // Append client_reference_id so Stripe webhooks (when added later) can link back
            $stripeUrl = $plan->stripe_checkout_url;
            $separator = str_contains($stripeUrl, '?') ? '&' : '?';
            $stripeUrl .= $separator . 'client_reference_id=' . $ref;

            return redirect()->away($stripeUrl);
        }

        // Fallback: direct charge via gateway (FakeGateway / future real Stripe)
        try {
            $result = DB::transaction(function () use ($user, $plan) {
                $ref    = Payment::generateReference();
                $charge = app(\App\Services\Payment\PaymentGatewayInterface::class)
                              ->charge($user, $plan, (float) $plan->price);
                $status = (string) ($charge['status'] ?? 'pending');

                $payment = Payment::create([
                    'user_id'           => $user->id,
                    'gateway'           => $charge['meta']['gateway'] ?? config('payment.default_gateway', 'fake'),
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
                    return ['ok' => false, 'payment' => $payment,
                            'message' => 'لم يتم تأكيد الدفع بعد. سيُفعَّل الاشتراك بعد المراجعة.'];
                }

                $subscription = $this->subscriptions->activateFromPaidPayment($user, $plan, $payment);

                return ['ok' => true, 'payment' => $payment, 'subscription' => $subscription,
                        'message' => 'تم تأكيد الدفع وتفعيل اشتراكك حتى ' . $subscription->ends_at->format('Y-m-d H:i')];
            });
        } catch (RuntimeException $e) {
            return redirect()->route('subscription.index')->with('error', $e->getMessage());
        }

        return redirect()
            ->route('subscription.index')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    // ─── Checkout: Opening Offer ──────────────────────────────────────────────

    public function openingOfferCheckout(Request $request)
    {
        $user = $request->user();

        $ref = Payment::generateReference();

        $payment = Payment::create([
            'user_id'           => $user->id,
            'gateway'           => 'stripe_link',
            'gateway_reference' => 'opening_offer_pending_' . Str::lower(Str::random(16)),
            'payment_reference' => $ref,
            'amount'            => 5.00,
            'currency'          => 'AED',
            'status'            => 'pending',
            'meta'              => [
                'offer_type'    => 'opening_offer',
                'plan_name'     => 'عرض الافتتاح',
                'duration_days' => 1,
                'created_via'   => 'opening_offer_checkout',
            ],
        ]);

        $request->session()->put('pending_payment_id', $payment->id);

        $stripeUrl  = 'https://buy.stripe.com/eVq3cx1Nn4aH8pn2KU57W0o';
        $stripeUrl .= '?client_reference_id=' . $ref;

        return redirect()->away($stripeUrl);
    }

    // ─── Return from Stripe (Success URL) ────────────────────────────────────

    /**
     * Stripe redirects here after a completed payment.
     * We do NOT activate the subscription automatically — we just show a
     * thank-you screen and let the user self-report ("I have paid").
     * Activation is done by the admin via the payments panel.
     *
     * SECURITY: We never trust the mere visit to this URL as proof of payment.
     */
    public function returnFromPayment(Request $request)
    {
        $paymentId = (int) $request->session()->get('pending_payment_id');
        $payment   = $paymentId
            ? Payment::query()
                ->where('user_id', $request->user()->id)
                ->find($paymentId)
            : null;

        // If the payment was already confirmed by admin before the user returned
        if ($payment && $payment->isPaid() && $payment->subscription_id) {
            return redirect()->route('subscription.success');
        }

        return view('site.subscription.return', [
            'payment' => $payment,
        ]);
    }

    // ─── "I Have Paid" Claim ──────────────────────────────────────────────────

    /**
     * User self-reports that they completed the payment.
     * Moves the payment from "pending" → "waiting_review" so the admin sees
     * it highlighted in the payments panel and can confirm quickly.
     *
     * Rules:
     *   - Only the owner of the payment can claim it.
     *   - Only works if status is still "pending".
     *   - Cannot be triggered more than once (idempotent).
     */
    public function claimPayment(Request $request)
    {
        $paymentId = (int) $request->session()->get('pending_payment_id');

        if (! $paymentId) {
            return redirect()->route('subscription.index')
                ->with('error', 'لم يتم العثور على عملية دفع مرتبطة بجلستك الحالية.');
        }

        $payment = Payment::query()
            ->where('user_id', $request->user()->id)
            ->find($paymentId);

        if (! $payment) {
            return redirect()->route('subscription.index')
                ->with('error', 'العملية غير موجودة أو لا تخصك.');
        }

        if ($payment->isPaid() && $payment->subscription_id) {
            // Already activated — send to success page
            return redirect()->route('subscription.success');
        }

        if (! $payment->canBeClaimed()) {
            // Already claimed or cancelled — just show the return page again
            return redirect()->route('subscription.return')
                ->with('info', 'تم إرسال طلبك للمراجعة مسبقاً. سنتواصل معك قريباً.');
        }

        // Advance status: pending → waiting_review
        $payment->update(['status' => 'waiting_review']);

        return redirect()->route('subscription.return')
            ->with('success', 'تم إرسال طلبك. سيقوم فريقنا بمراجعة الدفع وتفعيل اشتراكك خلال دقائق.');
    }

    // ─── Success Page (Active Subscribers Only) ───────────────────────────────

    /**
     * A "gate" success page — only accessible if the user has an active
     * subscription right now.  Cannot be opened by typing the URL manually
     * unless the subscription really is active.
     */
    public function success(Request $request)
    {
        $user         = $request->user();
        $subscription = $this->subscriptions->activeSubscription($user);

        if (! $subscription) {
            // Not yet active — redirect to return page (or plans if no pending payment)
            $hasPending = $request->session()->has('pending_payment_id');
            return redirect()->route($hasPending ? 'subscription.return' : 'subscription.index')
                ->with('error', 'لم يتم تفعيل الاشتراك بعد. انتظر مراجعة الفريق.');
        }

        return view('site.subscription.success', [
            'subscription' => $subscription,
        ]);
    }
}
