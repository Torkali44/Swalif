<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    // ─── Webhook Entry Point ──────────────────────────────────────────────────

    /**
     * POST /stripe/webhook
     *
     * Security model:
     *   1. Stripe-Signature verified with STRIPE_WEBHOOK_SECRET via Webhook::constructEvent().
     *   2. No business logic runs unless signature is valid.
     *   3. Always returns 2xx so Stripe stops retrying events we intentionally ignore.
     */
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $secret    = config('services.stripe.webhook_secret');

        // ── 1. Cryptographic signature verification ────────────────────────────
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('[Stripe Webhook] ❌ Invalid signature — rejected', [
                'error'      => $e->getMessage(),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\UnexpectedValueException $e) {
            Log::error('[Stripe Webhook] ❌ Unparseable payload', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info('[Stripe Webhook] ✅ Event received', [
            'event_id' => $event->id,
            'type'     => $event->type,
            'livemode' => $event->livemode,
        ]);

        // ── 2. Route to handler ────────────────────────────────────────────────
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted(
                                                $event->data->object,
                                                (string) $event->id          // pass Stripe event.id
                                            ),
            default => Log::info('[Stripe Webhook] Unhandled event type — skipping', [
                            'event_id' => $event->id,
                            'type'     => $event->type,
                        ]),
        };

        return response()->json(['status' => 'received']);
    }

    // ─── checkout.session.completed ──────────────────────────────────────────

    /**
     * Activates the subscription tied to this Stripe Checkout Session.
     *
     * Safety guarantees:
     *   A. Payment is looked up exclusively by client_reference_id (PAY-XXXXXXXX) —
     *      a cryptographically random reference we generated and sent to Stripe.
     *   B. Idempotency is enforced at TWO levels:
     *      - Fast path: check payment.status + subscription_id before entering TX.
     *      - DB-level: SELECT … FOR UPDATE inside the transaction prevents races.
     *      - Stripe event.id is stored in payment.meta so repeated deliveries of the
     *        exact same event are detected and skipped immediately.
     *   C. All database writes happen inside a single atomic DB::transaction.
     *   D. Any failure is logged with event_id, payment_id, and full stack trace.
     */
    private function handleCheckoutCompleted(object $session, string $stripeEventId): void
    {
        $sessionId = (string) ($session->id ?? 'unknown');
        $ref       = (string) ($session->client_reference_id ?? '');

        Log::info('[Stripe Webhook] Processing checkout.session.completed', [
            'event_id'       => $stripeEventId,
            'session_id'     => $sessionId,
            'payment_status' => $session->payment_status ?? null,
            'ref'            => $ref,
            'amount_total'   => $session->amount_total ?? null,
            'currency'       => $session->currency ?? null,
        ]);

        // ── Guard A: Only process fully-paid sessions ──────────────────────────
        // payment_status can be 'unpaid' for async payment methods (OXXO, etc.)
        if (($session->payment_status ?? '') !== 'paid') {
            Log::info('[Stripe Webhook] Session payment_status is not "paid" — skipping', [
                'event_id'       => $stripeEventId,
                'session_id'     => $sessionId,
                'payment_status' => $session->payment_status ?? 'unknown',
            ]);
            return;
        }

        // ── Guard B: client_reference_id is required ───────────────────────────
        if ($ref === '') {
            Log::warning('[Stripe Webhook] No client_reference_id — cannot link to Payment record', [
                'event_id'   => $stripeEventId,
                'session_id' => $sessionId,
            ]);
            return;
        }

        // ── Lookup Payment by trusted client_reference_id ──────────────────────
        // We NEVER search by amount, user email, or phone — only by the opaque
        // reference we generated and passed to Stripe as ?client_reference_id=
        $payment = Payment::where('payment_reference', $ref)->first();

        if (! $payment) {
            Log::warning('[Stripe Webhook] No Payment record matches client_reference_id', [
                'event_id'   => $stripeEventId,
                'session_id' => $sessionId,
                'ref'        => $ref,
            ]);
            return;
        }

        // ── Idempotency fast-path: check if this exact Stripe event was already processed ──
        $processedEventId = $payment->meta['stripe_event_id'] ?? null;
        if ($processedEventId === $stripeEventId) {
            Log::info('[Stripe Webhook] Idempotency hit — same Stripe event.id already processed', [
                'event_id'   => $stripeEventId,
                'payment_id' => $payment->id,
                'ref'        => $ref,
            ]);
            return;
        }

        // ── Idempotency fast-path: check if payment is already fully activated ──
        if ($payment->isPaid() && $payment->subscription_id) {
            Log::info('[Stripe Webhook] Idempotency hit — payment already activated (different event delivery)', [
                'event_id'        => $stripeEventId,
                'payment_id'      => $payment->id,
                'subscription_id' => $payment->subscription_id,
                'ref'             => $ref,
            ]);
            return;
        }

        // ── Secondary verification via Stripe API ──────────────────────────────
        // The webhook payload is already signature-verified, but we retrieve the
        // session from the API to confirm amount + currency haven't been tampered.
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripeSession = \Stripe\Checkout\Session::retrieve($sessionId);

            $expectedCents = (int) round((float) $payment->amount * 100);
            $actualCents   = (int) ($stripeSession->amount_total ?? 0);

            if ($actualCents !== $expectedCents) {
                Log::error('[Stripe Webhook] ⚠ Amount mismatch — logged for manual review, proceeding with activation', [
                    'event_id'       => $stripeEventId,
                    'ref'            => $ref,
                    'expected_cents' => $expectedCents,
                    'actual_cents'   => $actualCents,
                ]);
            }

            $expectedCurrency = strtoupper($payment->currency ?? 'AED');
            $actualCurrency   = strtoupper($stripeSession->currency ?? '');
            if ($actualCurrency !== '' && $actualCurrency !== $expectedCurrency) {
                Log::warning('[Stripe Webhook] ⚠ Currency mismatch', [
                    'event_id' => $stripeEventId,
                    'ref'      => $ref,
                    'expected' => $expectedCurrency,
                    'actual'   => $actualCurrency,
                ]);
            }

        } catch (\Exception $e) {
            // API call failed — proceed; webhook signature already proved authenticity.
            Log::warning('[Stripe Webhook] Could not retrieve session from Stripe API — proceeding on signed webhook data', [
                'event_id'   => $stripeEventId,
                'session_id' => $sessionId,
                'ref'        => $ref,
                'error'      => $e->getMessage(),
            ]);
        }

        // ── Activate inside a single DB transaction ────────────────────────────
        // markPaymentPaidAndActivate internally uses SELECT … FOR UPDATE to prevent
        // concurrent double-activation even if two webhook deliveries race each other.
        try {
            $subscription = $this->subscriptions->markPaymentPaidAndActivate(
                $payment,
                $sessionId,       // stored as gateway_reference (Stripe cs_ ID)
                [
                    'stripe_event_id'       => $stripeEventId,   // idempotency key
                    'stripe_session_id'     => $sessionId,
                    'stripe_payment_status' => $session->payment_status ?? 'paid',
                    'stripe_amount_total'   => $session->amount_total ?? null,
                    'stripe_currency'       => $session->currency ?? null,
                    'activated_via'         => 'stripe_webhook',
                ]
            );

            Log::info('[Stripe Webhook] ✅ Subscription activated', [
                'event_id'        => $stripeEventId,
                'ref'             => $ref,
                'payment_id'      => $payment->id,
                'user_id'         => $payment->user_id,
                'subscription_id' => $subscription->id,
                'ends_at'         => optional($subscription->ends_at)->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error('[Stripe Webhook] ❌ Activation failed', [
                'event_id'   => $stripeEventId,
                'ref'        => $ref,
                'payment_id' => $payment->id,
                'user_id'    => $payment->user_id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            // Do NOT re-throw — we must return 200 so Stripe doesn't retry indefinitely.
            // The error is logged with full trace for manual investigation.
        }
    }
}
