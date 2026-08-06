<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\PlayAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndUserJourneyTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_e2e_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => $this->webhookSecret]);
        config(['services.stripe.secret' => 'sk_test_e2e_fake']);
    }

    public function test_complete_end_to_end_user_journey(): void
    {
        $this->withoutVite();

        // ── 1. Create a new user via registration ────────────────────────────
        $registerResponse = $this->post(route('register.store'), [
            'name'       => 'سارة الأحمد',
            'email'      => 'sara@example.com',
            'phone'      => '0501234567',
            'password'   => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'terms'      => '1',
        ]);

        $registerResponse->assertRedirect(route('home'));
        $user = User::where('email', 'sara@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        // Create categories for testing access
        $cat1 = Category::create([
            'name_ar' => 'فئة مجانية', 'name_en' => 'Free', 'slug' => 'free-cat',
            'group' => 'general', 'is_active' => true, 'sort_order' => 1,
        ]);
        $cat2 = Category::create([
            'name_ar' => 'فئة مدفوعة', 'name_en' => 'Paid', 'slug' => 'paid-cat',
            'group' => 'general', 'is_active' => true, 'sort_order' => 2,
        ]);

        // Set free category
        $user->forceFill(['free_category_id' => $cat1->id])->save();

        // ── 2. User initiates subscription checkout (Stripe Payment Link) ─────
        $plan = Plan::create([
            'name' => 'الباقة الشهريّة',
            'type' => 'monthly',
            'price' => 25,
            'currency' => 'AED',
            'duration_days' => 30,
            'sort_order' => 1,
            'is_active' => true,
            'stripe_checkout_url' => 'https://buy.stripe.com/test_e2e_link',
        ]);

        $checkoutResponse = $this->actingAs($user)
            ->post(route('subscription.checkout', $plan));

        $checkoutResponse->assertRedirect();
        $this->assertTrue(str_starts_with($checkoutResponse->headers->get('Location'), 'https://buy.stripe.com/test_e2e_link'));

        // Payment record created in pending state
        $payment = Payment::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertNotNull($payment->payment_reference);

        // ── 3. Webhook arrives (checkout.session.completed) ────────────────────
        $payload = [
            'id'       => 'evt_e2e_' . uniqid(),
            'type'     => 'checkout.session.completed',
            'livemode' => false,
            'data'     => [
                'object' => [
                    'id'                  => 'cs_e2e_' . uniqid(),
                    'object'              => 'checkout.session',
                    'client_reference_id' => $payment->payment_reference,
                    'payment_status'      => 'paid',
                    'amount_total'        => 2500,
                    'currency'            => 'aed',
                ],
            ],
        ];

        $json = json_encode($payload);
        $ts   = time();
        $sig  = 't=' . $ts . ',v1=' . hash_hmac('sha256', "{$ts}.{$json}", $this->webhookSecret);

        $webhookResponse = $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sig,
            'CONTENT_TYPE'          => 'application/json',
        ], $json);

        $webhookResponse->assertStatus(200);

        // ── 4. Subscription is activated ─────────────────────────────────────
        $user = $user->fresh();
        $this->assertTrue($user->hasActiveSubscription());
        $this->assertSame('paid', $payment->fresh()->status);

        // ── 5. Games unlocked ────────────────────────────────────────────────
        $this->actingAs($user)
            ->get(route('game.setup', $cat2))
            ->assertOk();

        // ── 6. Subscription expires ──────────────────────────────────────────
        $activeSub = $user->activeSubscription();
        $activeSub->update(['ends_at' => now()->subMinute()]);

        // Sync expiration
        app(PlayAccessService::class)->syncExpiredSubscriptions($user->fresh());

        $this->assertFalse($user->fresh()->hasActiveSubscription());

        // ── 7. Access to paid game is blocked ────────────────────────────────
        $this->actingAs($user->fresh())
            ->get(route('game.setup', $cat2))
            ->assertRedirect(route('subscription.index'));

        // ── 8. Re-subscribe ──────────────────────────────────────────────────
        $ref2 = Payment::generateReference();
        $payment2 = Payment::create([
            'user_id'           => $user->id,
            'gateway'           => 'stripe_link',
            'gateway_reference' => 'cs_e2e_resub',
            'payment_reference' => $ref2,
            'amount'            => 25,
            'currency'          => 'AED',
            'status'            => 'pending',
            'meta'              => ['plan_id' => $plan->id, 'plan_name' => $plan->name, 'duration_days' => 30],
        ]);

        $payload2 = [
            'id'       => 'evt_e2e_resub_' . uniqid(),
            'type'     => 'checkout.session.completed',
            'livemode' => false,
            'data'     => [
                'object' => [
                    'id'                  => 'cs_e2e_resub_' . uniqid(),
                    'object'              => 'checkout.session',
                    'client_reference_id' => $ref2,
                    'payment_status'      => 'paid',
                    'amount_total'        => 2500,
                    'currency'            => 'aed',
                ],
            ],
        ];

        $json2 = json_encode($payload2);
        $ts2   = time();
        $sig2  = 't=' . $ts2 . ',v1=' . hash_hmac('sha256', "{$ts2}.{$json2}", $this->webhookSecret);

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sig2,
            'CONTENT_TYPE'          => 'application/json',
        ], $json2)->assertStatus(200);

        $this->assertTrue($user->fresh()->hasActiveSubscription());

        // ── 9. Cancel subscription from Admin Panel ──────────────────────────
        $admin    = User::factory()->create(['is_admin' => true]);
        $latestSub = $user->fresh()->activeSubscription();

        $this->actingAs($admin)
            ->patch(route('admin.subscribers.cancel', $latestSub))
            ->assertRedirect();

        // ── 10. Confirm game access is blocked ───────────────────────────────
        $this->assertFalse($user->fresh()->hasActiveSubscription());

        $this->actingAs($user->fresh())
            ->get(route('game.setup', $cat2))
            ->assertRedirect(route('subscription.index'));
    }
}
