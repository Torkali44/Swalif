<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Category;
use App\Models\Classification;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminSubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_plan(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => 'Premium',
                'icon' => 'P',
                'type' => 'premium',
                'stripe_checkout_url' => 'https://buy.stripe.com/test_123',
                'price' => 25,
                'old_price' => 35,
                'currency' => 'AED',
                'duration_days' => 30,
                'features' => ['All categories', 'Unlimited play'],
                'sort_order' => 1,
                'is_active' => '1',
                'is_recommended' => '1',
            ])
            ->assertRedirect(route('admin.plans.index'));

        $plan = Plan::where('type', 'premium')->firstOrFail();

        $this->assertSame(['All categories', 'Unlimited play'], $plan->features);
        $this->assertSame('https://buy.stripe.com/test_123', $plan->stripe_checkout_url);
        $this->assertSame(1, (int) $plan->sort_order);
        $this->assertTrue($plan->is_active);
        $this->assertTrue($plan->is_recommended);
    }

    public function test_plan_sort_order_shifts_existing_plans(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);

        $first = Plan::create([
            'name' => 'Weekly',
            'type' => 'weekly',
            'price' => 10,
            'currency' => 'AED',
            'duration_days' => 7,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $second = Plan::create([
            'name' => 'Monthly',
            'type' => 'monthly',
            'price' => 25,
            'currency' => 'AED',
            'duration_days' => 30,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => 'Yearly',
                'type' => 'yearly',
                'price' => 199,
                'currency' => 'AED',
                'duration_days' => 365,
                'features' => ['All categories'],
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.plans.index'));

        $this->assertSame(1, (int) Plan::where('type', 'yearly')->value('sort_order'));
        $this->assertSame(2, (int) $first->fresh()->sort_order);
        $this->assertSame(3, (int) $second->fresh()->sort_order);
    }

    public function test_checkout_redirects_to_stripe_payment_link(): void
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

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'status' => 'pending',
            'gateway' => 'stripe_link',
        ]);
        $this->assertFalse($user->fresh()->hasActiveSubscription());
    }

    public function test_plan_numeric_fields_must_be_at_least_one(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => 'Invalid',
                'type' => 'invalid',
                'price' => 0,
                'old_price' => 0,
                'currency' => 'AED',
                'duration_days' => 0,
                'features' => ['Feature'],
                'sort_order' => 0,
            ])
            ->assertSessionHasErrors(['price', 'old_price', 'duration_days', 'sort_order']);
    }

    public function test_admin_can_create_question_with_type(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name_ar' => 'إمارات',
            'name_en' => 'UAE',
            'slug' => 'uae-test',
            'group' => 'uae',
            'icon' => '🏛️',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.questions.store'), [
                'category_id' => $category->id,
                'type' => 'image_guess',
                'question_text' => 'ما هذه الصورة؟',
                'answer_text' => 'الإجابة',
                'image' => UploadedFile::fake()->image('question.jpg'),
                'level' => 'easy',
                'points' => 200,
                'time_limit' => 60,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.questions.index'));

        $this->assertDatabaseHas('questions', [
            'category_id' => $category->id,
            'type' => 'image_guess',
            'question_text' => 'ما هذه الصورة؟',
        ]);
    }

    public function test_admin_can_create_category_with_custom_group_name(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);
        $classification = Classification::create([
            'name_ar' => 'السعودية',
            'name_en' => 'Saudi',
            'slug' => 'saudi',
            'icon' => '🇸🇦',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name_ar' => 'أفلام',
                'name_en' => 'Movies',
                'classification_id' => $classification->id,
                'icon' => '🎬',
                'description' => 'فئة جديدة',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name_ar' => 'أفلام',
            'classification_id' => $classification->id,
            'group' => 'السعودية',
        ]);
    }

    public function test_admin_can_create_classification(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.classifications.store'), [
                'name_ar' => 'منوعات',
                'name_en' => 'Mixed',
                'icon' => '🎲',
                'description' => 'تصنيف للتجارب المتنوعة',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.classifications.index'));

        $this->assertDatabaseHas('classifications', [
            'name_ar' => 'منوعات',
            'name_en' => 'Mixed',
        ]);
    }

    public function test_admin_can_grant_subscription_and_replace_existing_active_subscription(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $oldPlan = Plan::create([
            'name' => 'Old',
            'type' => 'old',
            'price' => 5,
            'currency' => 'AED',
            'duration_days' => 7,
            'sort_order' => 1,
        ]);
        $newPlan = Plan::create([
            'name' => 'New',
            'type' => 'new',
            'price' => 15,
            'currency' => 'AED',
            'duration_days' => 30,
            'sort_order' => 2,
        ]);
        $existing = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $oldPlan->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(6),
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.subscribers.store'), [
                'user_id' => $user->id,
                'plan_id' => $newPlan->id,
                'starts_at' => now()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.subscribers.index'));

        $this->assertDatabaseHas('subscriptions', [
            'id' => $existing->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $newPlan->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_create_video_question(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name_ar' => 'فيديو',
            'name_en' => 'Video',
            'slug' => 'video-cat',
            'group' => 'general',
            'icon' => '🎬',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.questions.store'), [
                'category_id' => $category->id,
                'type' => 'video',
                'question_text' => 'ماذا يظهر في الفيديو؟',
                'answer_text' => 'برج خليفة',
                'image' => UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4'),
                'level' => 'easy',
                'points' => 200,
                'time_limit' => 60,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.questions.index'));

        $this->assertDatabaseHas('questions', [
            'category_id' => $category->id,
            'type' => 'video',
            'answer_text' => 'برج خليفة',
        ]);
    }

    public function test_admin_can_create_audio_question(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name_ar' => 'صوت',
            'name_en' => 'Audio',
            'slug' => 'audio-cat',
            'group' => 'general',
            'icon' => '🎧',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.questions.store'), [
                'category_id' => $category->id,
                'type' => 'audio',
                'question_text' => 'ما هذه الأغنية؟',
                'answer_text' => 'النشيد الوطني',
                'image' => UploadedFile::fake()->create('sound.mp3', 512, 'audio/mpeg'),
                'level' => 'medium',
                'points' => 400,
                'time_limit' => 60,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.questions.index'));

        $this->assertDatabaseHas('questions', [
            'category_id' => $category->id,
            'type' => 'audio',
            'answer_text' => 'النشيد الوطني',
        ]);
    }
}
