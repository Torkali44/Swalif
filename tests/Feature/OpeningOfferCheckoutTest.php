<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpeningOfferCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_opening_offer_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => $this->webhookSecret]);
        config(['services.stripe.secret' => 'sk_test_fake']);
    }

    public function test_opening_offer_checkout_creates_pending_payment_with_plan_id_in_meta(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        // Create opening offer plan in DB
        $plan = Plan::create([
            'name'                => 'عرض الافتتاح',
            'type'                => 'daily',
            'price'               => 5.00,
            'currency'            => 'AED',
            'duration_days'       => 1,
            'stripe_checkout_url' => 'https://buy.stripe.com/eVq3cx1Nn4aH8pn2KU57W0o',
            'is_active'           => true,
            'sort_order'          => 1,
        ]);

        $response = $this->actingAs($user)
            ->post(route('subscription.opening_offer'));

        $response->assertRedirect();
        $this->assertTrue(str_starts_with($response->headers->get('Location'), 'https://buy.stripe.com/eVq3cx1Nn4aH8pn2KU57W0o'));

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);
        $this->assertNotNull($payment->payment_reference);

        // Crucial assertion requested by user: plan_id MUST be saved in meta
        $this->assertArrayHasKey('plan_id', $payment->meta);
        $this->assertSame($plan->id, (int) $payment->meta['plan_id']);
    }

    public function test_webhook_activates_opening_offer_payment_and_unlocks_games(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['is_admin' => false]);
        $plan = Plan::create([
            'name'          => 'عرض الافتتاح',
            'type'          => 'daily',
            'price'         => 5.00,
            'currency'      => 'AED',
            'duration_days' => 1,
            'is_active'     => true,
            'sort_order'    => 1,
        ]);

        $cat = Category::create([
            'name_ar' => 'فئة إضافية', 'name_en' => 'Extra', 'slug' => 'extra-cat',
            'group' => 'general', 'is_active' => true, 'sort_order' => 2,
        ]);

        $ref = Payment::generateReference();
        $payment = Payment::create([
            'user_id'           => $user->id,
            'gateway'           => 'stripe_link',
            'gateway_reference' => 'opening_offer_pending_test',
            'payment_reference' => $ref,
            'amount'            => 5.00,
            'currency'          => 'AED',
            'status'            => 'pending',
            'meta'              => [
                'plan_id'       => $plan->id,
                'plan_name'     => $plan->name,
                'duration_days' => 1,
                'offer_type'    => 'opening_offer',
            ],
        ]);

        $payload = [
            'id'       => 'evt_test_offer_' . uniqid(),
            'type'     => 'checkout.session.completed',
            'livemode' => false,
            'data'     => [
                'object' => [
                    'id'                  => 'cs_test_offer_' . uniqid(),
                    'object'              => 'checkout.session',
                    'client_reference_id' => $ref,
                    'payment_status'      => 'paid',
                    'amount_total'        => 500,
                    'currency'            => 'aed',
                ],
            ],
        ];

        $json = json_encode($payload);
        $ts   = time();
        $sig  = 't=' . $ts . ',v1=' . hash_hmac('sha256', "{$ts}.{$json}", $this->webhookSecret);

        $response = $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sig,
            'CONTENT_TYPE'          => 'application/json',
        ], $json);

        $response->assertStatus(200);

        // Payment status becomes paid
        $this->assertSame('paid', $payment->fresh()->status);

        // Subscription created and active
        $this->assertTrue($user->fresh()->hasActiveSubscription());
        $sub = $user->fresh()->activeSubscription();
        $this->assertNotNull($sub);
        $this->assertSame('active', $sub->status);

        // Games become accessible
        $this->actingAs($user->fresh())
            ->get(route('game.setup', $cat))
            ->assertOk();
    }

    public function test_payment_missing_plan_id_recovers_via_fallback_plan(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $plan = Plan::create([
            'name'          => 'عرض الافتتاح',
            'type'          => 'daily',
            'price'         => 5.00,
            'currency'      => 'AED',
            'duration_days' => 1,
            'is_active'     => true,
            'sort_order'    => 1,
        ]);

        $ref = Payment::generateReference();

        // Payment explicitly MISSING plan_id in meta (like payment #17 reported by user)
        $payment = Payment::create([
            'user_id'           => $user->id,
            'gateway'           => 'stripe_link',
            'gateway_reference' => 'opening_offer_legacy_no_plan_id',
            'payment_reference' => $ref,
            'amount'            => 5.00,
            'currency'          => 'AED',
            'status'            => 'pending',
            'meta'              => [
                'offer_type'    => 'opening_offer',
                'plan_name'     => 'عرض الافتتاح',
                'duration_days' => 1,
            ],
        ]);

        $service = app(SubscriptionService::class);
        $subscription = $service->markPaymentPaidAndActivate($payment);

        $this->assertNotNull($subscription);
        $this->assertSame('active', $subscription->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertTrue($user->fresh()->hasActiveSubscription());
    }
}
