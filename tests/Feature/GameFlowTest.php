<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Game;
use App\Models\Plan;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_create_and_play_a_game(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $category = Category::where('slug', 'uae-malls')->firstOrFail();
        $question = Question::where('category_id', $category->id)->firstOrFail();

        $response = $this->actingAs($player)->post(route('game.start'), [
            'category_id' => $category->id,
            'name' => 'اختبار اللعبة',
            'team_one' => 'الصقور',
            'team_two' => 'النجوم',
        ]);

        $response->assertRedirect();
        $game = Game::firstOrFail();

        $this->get(route('game.board', $game))->assertOk();
        $this->get(route('game.question', [$game, $question]))->assertOk();
    }

    public function test_admin_dashboard_is_protected_by_role(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $admin = User::where('email', 'admin@swalif.test')->firstOrFail();

        $this->actingAs($player)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_assigning_points_twice_is_blocked(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $category = Category::where('slug', 'uae-malls')->firstOrFail();
        $question = Question::where('category_id', $category->id)->firstOrFail();

        $this->actingAs($player)->post(route('game.start'), [
            'category_id' => $category->id,
            'name' => 'جولة نقاط',
            'team_one' => 'أ',
            'team_two' => 'ب',
        ]);

        $game = Game::firstOrFail();
        $this->get(route('game.question', [$game, $question]))->assertOk();

        $gq = $game->gameQuestions()->firstOrFail();
        $team = $game->teams()->firstOrFail();

        $this->post(route('game.assign', [$game, $gq]), ['team_id' => $team->id])
            ->assertRedirect(route('game.board', $game));

        $this->post(route('game.assign', [$game, $gq]), ['team_id' => $team->id])
            ->assertRedirect(route('game.board', $game));

        $this->assertSame((int) $question->points, (int) $team->fresh()->score);
    }

    public function test_player_can_use_lifeline(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $category = Category::where('slug', 'uae-malls')->firstOrFail();

        $this->actingAs($player)->post(route('game.start'), [
            'category_id' => $category->id,
            'name' => 'جولة مساعدة',
            'team_one' => 'أ',
            'team_two' => 'ب',
        ]);

        $game = Game::firstOrFail();
        $team = $game->fresh()->teams()->firstOrFail();

        // Initially helper is 1
        $this->assertSame(1, $team->helpers_left['swap'] ?? 0);

        // Call the useHelper endpoint
        $response = $this->post(route('game.useHelper', [$game, $team, 'swap']));
        $response->assertOk();
        $response->assertJsonPath('success', true);

        // Helper count should be 0
        $this->assertSame(0, $team->fresh()->helpers_left['swap'] ?? 1);

        // Calling it again should fail
        $response = $this->post(route('game.useHelper', [$game, $team, 'swap']));
        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
    }

    public function test_player_can_adjust_score(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $category = Category::where('slug', 'uae-malls')->firstOrFail();

        $this->actingAs($player)->post(route('game.start'), [
            'category_id' => $category->id,
            'name' => 'جولة نقاط',
            'team_one' => 'أ',
            'team_two' => 'ب',
        ]);

        $game = Game::firstOrFail();
        $team = $game->fresh()->teams()->firstOrFail();

        $this->assertSame(0, (int) $team->score);

        // Increase score by 100
        $response = $this->post(route('game.adjustScore', [$game, $team]), ['amount' => 100]);
        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('score', 100);
        $this->assertSame(100, (int) $team->fresh()->score);

        // Decrease score by 100
        $response = $this->post(route('game.adjustScore', [$game, $team]), ['amount' => -100]);
        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('score', 0);
        $this->assertSame(0, (int) $team->fresh()->score);
    }
}
