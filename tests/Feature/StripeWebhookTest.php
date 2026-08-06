<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_test_secret_for_testing_only';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => $this->webhookSecret]);
        config(['services.stripe.secret' => 'sk_test_fake']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Build a fake Stripe webhook payload and sign it with our test secret.
     */
    private function makeSignedWebhook(array $payload): array
    {
        $json      = json_encode($payload);
        $timestamp = time();
        $signature = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $json, 'test_secret_for_testing_only');

        return ['payload' => $json, 'signature' => $signature];
    }

    /**
     * Build a valid signed event using Stripe SDK helper (no real keys needed).
     */
    private function signedPayload(array $data): array
    {
        $json      = json_encode($data);
        $timestamp = time();
        $sig       = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', "{$timestamp}.{$json}", $this->webhookSecret);

        return ['json' => $json, 'sig' => $sig];
    }

    private function checkoutCompletedPayload(string $ref, string $status = 'paid'): array
    {
        return [
            'id'   => 'evt_test_' . uniqid(),
            'type' => 'checkout.session.completed',
            'livemode' => false,
            'data' => [
                'object' => [
                    'id'                   => 'cs_test_' . uniqid(),
                    'object'               => 'checkout.session',
                    'client_reference_id'  => $ref,
                    'payment_status'       => $status,
                    'amount_total'         => 2500,
                    'currency'             => 'aed',
                ],
            ],
        ];
    }

    // ─── Tests ────────────────────────────────────────────────────────────────

    public function test_webhook_with_invalid_signature_returns_400(): void
    {
        $this->withoutVite();

        $response = $this->postJson('/stripe/webhook', ['foo' => 'bar'], [
            'Stripe-Signature' => 't=1234,v1=badsignature',
            'Content-Type'     => 'application/json',
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_activates_subscription_on_valid_checkout_completed(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $plan = Plan::create([
            'name'              => 'شهري',
            'type'              => 'monthly',
            'price'             => 25,
            'currency'          => 'AED',
            'duration_days'     => 30,
            'sort_order'        => 1,
            'is_active'         => true,
            'stripe_checkout_url' => 'https://buy.stripe.com/test',
        ]);

        $ref = Payment::generateReference();
        $payment = Payment::create([
            'user_id'           => $user->id,
            'gateway'           => 'stripe_link',
            'gateway_reference' => 'pending_test',
            'payment_reference' => $ref,
            'amount'            => 25.00,
            'currency'          => 'AED',
            'status'            => 'pending',
            'meta'              => [
                'plan_id'       => $plan->id,
                'plan_name'     => $plan->name,
                'duration_days' => 30,
            ],
        ]);

        $payload = $this->checkoutCompletedPayload($ref);
        $json    = json_encode($payload);
        $ts      = time();
        $sig     = 't=' . $ts . ',v1=' . hash_hmac('sha256', "{$ts}.{$json}", $this->webhookSecret);

        // Mock the Stripe Checkout\Session::retrieve to avoid real API call
        $this->mock(\Stripe\Service\Checkout\CheckoutServiceFactory::class);

        $response = $this->call(
            method:  'POST',
            uri:     '/stripe/webhook',
            parameters: [],
            cookies: [],
            files:   [],
            server:  [
                'HTTP_STRIPE_SIGNATURE' => $sig,
                'CONTENT_TYPE'          => 'application/json',
            ],
            content: $json,
        );

        // Should return 200
        $response->assertStatus(200);

        // Subscription should be active
        $this->assertTrue($user->fresh()->hasActiveSubscription());

        // Payment should be paid
        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_webhook_is_idempotent_on_duplicate_events(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $plan = Plan::create([
            'name'          => 'أسبوعي',
            'type'          => 'weekly',
            'price'         => 10,
            'currency'      => 'AED',
            'duration_days' => 7,
            'sort_order'    => 1,
            'is_active'     => true,
        ]);

        $ref     = Payment::generateReference();
        $payment = Payment::create([
            'user_id'           => $user->id,
            'gateway'           => 'stripe_link',
            'gateway_reference' => 'cs_test_already',
            'payment_reference' => $ref,
            'amount'            => 10.00,
            'currency'          => 'AED',
            'status'            => 'pending',
            'meta'              => ['plan_id' => $plan->id, 'plan_name' => $plan->name, 'duration_days' => 7],
        ]);

        $payload = $this->checkoutCompletedPayload($ref);
        $json    = json_encode($payload);
        $ts      = time();
        $sig     = 't=' . $ts . ',v1=' . hash_hmac('sha256', "{$ts}.{$json}", $this->webhookSecret);

        // First webhook
        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sig,
            'CONTENT_TYPE'          => 'application/json',
        ], $json)->assertStatus(200);

        $subscriptionCountAfterFirst = \App\Models\Subscription::where('user_id', $user->id)->count();

        // Send exact same webhook again (retry simulation)
        $ts2  = time() + 1;
        $sig2 = 't=' . $ts2 . ',v1=' . hash_hmac('sha256', "{$ts2}.{$json}", $this->webhookSecret);

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sig2,
            'CONTENT_TYPE'          => 'application/json',
        ], $json)->assertStatus(200);

        $subscriptionCountAfterSecond = \App\Models\Subscription::where('user_id', $user->id)->count();

        // Must NOT create a duplicate subscription
        $this->assertSame($subscriptionCountAfterFirst, $subscriptionCountAfterSecond);
        $this->assertSame(1, $subscriptionCountAfterFirst);
    }

    public function test_webhook_with_unknown_client_reference_is_handled_gracefully(): void
    {
        $this->withoutVite();

        $payload = $this->checkoutCompletedPayload('PAY-DOESNOTEXIST');
        $json    = json_encode($payload);
        $ts      = time();
        $sig     = 't=' . $ts . ',v1=' . hash_hmac('sha256', "{$ts}.{$json}", $this->webhookSecret);

        $response = $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sig,
            'CONTENT_TYPE'          => 'application/json',
        ], $json);

        // Should still return 200 (so Stripe stops retrying)
        $response->assertStatus(200);
    }

    public function test_webhook_with_unpaid_session_does_not_activate(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $ref  = Payment::generateReference();
        Payment::create([
            'user_id'           => $user->id,
            'gateway'           => 'stripe_link',
            'gateway_reference' => 'pending_test',
            'payment_reference' => $ref,
            'amount'            => 25.00,
            'currency'          => 'AED',
            'status'            => 'pending',
            'meta'              => ['plan_id' => 1, 'plan_name' => 'Test', 'duration_days' => 30],
        ]);

        // payment_status = 'unpaid' — async payment not yet completed
        $payload = $this->checkoutCompletedPayload($ref, 'unpaid');
        $json    = json_encode($payload);
        $ts      = time();
        $sig     = 't=' . $ts . ',v1=' . hash_hmac('sha256', "{$ts}.{$json}", $this->webhookSecret);

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sig,
            'CONTENT_TYPE'          => 'application/json',
        ], $json)->assertStatus(200);

        $this->assertFalse($user->fresh()->hasActiveSubscription());
    }
}
