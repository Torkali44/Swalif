<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SubscriptionActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_checkout_activates_subscription_with_correct_dates(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['is_admin' => false]);
        $plan = Plan::create([
            'name' => 'شهري',
            'type' => 'monthly',
            'price' => 25,
            'currency' => 'AED',
            'duration_days' => 30,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $before = now()->subSecond();

        $this->actingAs($user)
            ->post(route('subscription.checkout', $plan))
            ->assertRedirect(route('subscription.index'));

        $this->assertTrue($user->fresh()->hasActiveSubscription());

        $payment = Payment::query()->where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertSame('paid', $payment->status);

        $subscription = Subscription::query()->where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($subscription);
        $this->assertSame('active', $subscription->status);
        $this->assertSame((int) $payment->id, (int) $subscription->payment_id);
        $this->assertTrue($subscription->starts_at->greaterThanOrEqualTo($before));
        $this->assertTrue(
            $subscription->ends_at->between(
                $subscription->starts_at->copy()->addDays(30)->subMinute(),
                $subscription->starts_at->copy()->addDays(30)->addMinute()
            )
        );
    }

    public function test_unpaid_payment_cannot_activate_subscription(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $plan = Plan::create([
            'name' => 'أسبوعي',
            'type' => 'weekly',
            'price' => 10,
            'currency' => 'AED',
            'duration_days' => 7,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'gateway' => 'fake',
            'gateway_reference' => 'pending_test',
            'amount' => 10,
            'currency' => 'AED',
            'status' => 'pending',
            'meta' => ['plan_id' => $plan->id],
        ]);

        $service = app(SubscriptionService::class);

        try {
            $service->activateFromPaidPayment($user, $plan, $payment);
            $this->fail('Expected activation to fail for unpaid payment.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('تأكيد الدفع', $e->getMessage());
        }

        $this->assertFalse($user->fresh()->hasActiveSubscription());
    }

    public function test_stripe_link_creates_pending_payment_without_activating(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $plan = Plan::create([
            'name' => 'Monthly',
            'type' => 'monthly',
            'price' => 25,
            'currency' => 'AED',
            'duration_days' => 30,
            'sort_order' => 1,
            'is_active' => true,
            'stripe_checkout_url' => 'https://buy.stripe.com/test_live_link',
        ]);

        $this->actingAs($user)
            ->post(route('subscription.checkout', $plan))
            ->assertRedirect('https://buy.stripe.com/test_live_link');

        $payment = Payment::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);
        $this->assertFalse($user->fresh()->hasActiveSubscription());
    }

    public function test_admin_can_confirm_pending_payment_and_unlock_play(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $plan = Plan::create([
            'name' => 'سنوي',
            'type' => 'yearly',
            'price' => 199,
            'currency' => 'AED',
            'duration_days' => 365,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $categories = collect([
            Category::create([
                'name_ar' => 'فئة أ',
                'name_en' => 'A',
                'slug' => 'cat-a',
                'group' => 'general',
                'is_active' => true,
                'sort_order' => 1,
            ]),
            Category::create([
                'name_ar' => 'فئة ب',
                'name_en' => 'B',
                'slug' => 'cat-b',
                'group' => 'general',
                'is_active' => true,
                'sort_order' => 2,
            ]),
        ]);

        $user->forceFill(['free_category_id' => $categories[0]->id])->save();

        $payment = Payment::create([
            'user_id' => $user->id,
            'gateway' => 'stripe_link',
            'gateway_reference' => 'pending_admin',
            'amount' => 199,
            'currency' => 'AED',
            'status' => 'pending',
            'meta' => [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'duration_days' => 365,
            ],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payments.confirm', $payment))
            ->assertRedirect();

        $this->assertTrue($user->fresh()->hasActiveSubscription());
        $subscription = $user->fresh()->activeSubscription();
        $this->assertTrue(
            $subscription->ends_at->equalTo($subscription->starts_at->copy()->addDays(365))
        );

        $this->actingAs($user)
            ->get(route('game.setup', $categories[1]))
            ->assertOk();
    }

    public function test_expired_subscription_blocks_other_categories(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['is_admin' => false]);
        $plan = Plan::create([
            'name' => 'أسبوعي',
            'type' => 'weekly',
            'price' => 10,
            'currency' => 'AED',
            'duration_days' => 7,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $first = Category::create([
            'name_ar' => 'فئة 1',
            'name_en' => 'One',
            'slug' => 'one',
            'group' => 'general',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $second = Category::create([
            'name_ar' => 'فئة 2',
            'name_en' => 'Two',
            'slug' => 'two',
            'group' => 'general',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $user->forceFill(['free_category_id' => $first->id])->save();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
            'status' => 'active',
        ]);

        $this->assertFalse($user->fresh()->hasActiveSubscription());

        $this->actingAs($user)
            ->get(route('game.setup', $second))
            ->assertRedirect(route('subscription.index'));
    }

    public function test_expire_command_marks_ended_subscriptions(): void
    {
        $this->withoutVite();

        $user = User::factory()->create(['play_blocked' => false]);
        $plan = Plan::create([
            'name' => 'أسبوعي',
            'type' => 'weekly',
            'price' => 10,
            'currency' => 'AED',
            'duration_days' => 7,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subMinute(),
            'status' => 'active',
        ]);

        $category = Category::create([
            'name_ar' => 'فئة منتهية',
            'name_en' => 'Expired',
            'slug' => 'expired-cat',
            'group' => 'general',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame('expired', $subscription->fresh()->status);
        $this->assertTrue((bool) $user->fresh()->play_blocked);

        $this->actingAs($user->fresh())
            ->get(route('game.setup', $category))
            ->assertRedirect(route('subscription.index'));
    }

    public function test_admin_can_lock_and_unlock_play(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false, 'play_blocked' => false]);
        $category = Category::create([
            'name_ar' => 'فئة قفل',
            'name_en' => 'Lock',
            'slug' => 'lock-cat',
            'group' => 'general',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.togglePlay', $user))
            ->assertRedirect();

        $this->assertTrue((bool) $user->fresh()->play_blocked);

        $this->actingAs($user->fresh())
            ->get(route('game.setup', $category))
            ->assertRedirect(route('subscription.index'));

        $this->actingAs($admin)
            ->patch(route('admin.users.togglePlay', $user->fresh()))
            ->assertRedirect();

        $this->assertFalse((bool) $user->fresh()->play_blocked);

        $this->actingAs($user->fresh())
            ->get(route('game.setup', $category))
            ->assertOk();
    }
}
