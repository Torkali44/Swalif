<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_email_from_main_login(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->post(route('login.store'), [
            'email' => 'omjori_Swalif_Admin_009@gmail.com',
            'password' => 'Omjori@2026$Admin',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_player_can_login_with_email_and_password(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->post(route('login.store'), [
            'email' => 'player@swalif.test',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }

    public function test_new_user_can_register_with_email_and_phone(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->post(route('register.store'), [
            'name' => 'لاعب جديد',
            'email' => 'new.player@swalif.test',
            'phone' => '0501888777',
            'phone_code' => '+971',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'new.player@swalif.test',
            'phone' => '0501888777',
            'name' => 'لاعب جديد',
        ]);
    }

    public function test_register_requires_terms_acceptance(): void
    {
        $this->withoutVite();

        $this->post(route('register.store'), [
            'name' => 'لاعب',
            'email' => 'no.terms@swalif.test',
            'phone' => '0501777666',
            'phone_code' => '+971',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('terms');
    }

    public function test_register_page_is_available(): void
    {
        $this->withoutVite();

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('إنشاء حساب')
            ->assertSee('الشروط والأحكام');
    }

    public function test_player_can_subscribe_to_a_plan(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $plan = Plan::where('type', 'monthly')->firstOrFail();

        $this->actingAs($player)
            ->post(route('subscription.checkout', $plan))
            ->assertRedirect(route('subscription.index'));

        $this->assertTrue($player->fresh()->hasActiveSubscription());
    }

    public function test_guest_cannot_access_game_setup(): void
    {
        $this->withoutVite();
        $this->seed();

        $category = \App\Models\Category::firstOrFail();

        $this->get(route('game.setup', $category))->assertRedirect(route('login'));
    }

    public function test_free_user_can_play_only_one_category(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $categories = \App\Models\Category::where('is_active', true)->take(2)->get();
        $this->assertCount(2, $categories);

        $first = $categories[0];
        $second = $categories[1];

        $this->actingAs($player)
            ->post(route('game.start'), [
                'category_id' => $first->id,
                'name' => 'لعبة مجانية',
                'team_one' => 'أ',
                'team_two' => 'ب',
            ])
            ->assertRedirect();

        $this->assertSame((int) $first->id, (int) $player->fresh()->free_category_id);

        $this->actingAs($player)
            ->get(route('game.setup', $second))
            ->assertRedirect(route('subscription.index'));
    }
}
