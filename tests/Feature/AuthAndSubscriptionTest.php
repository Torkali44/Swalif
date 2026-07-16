<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_admin_redirects_to_dashboard(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->post(route('login.store'), [
            'email' => 'admin@seenjeem.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_player_can_login_to_home(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->post(route('login.store'), [
            'email' => 'player@seenjeem.test',
            'password' => 'password',
        ])->assertRedirect(route('home'));
    }

    public function test_player_can_subscribe_to_a_plan(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@seenjeem.test')->firstOrFail();
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
}
