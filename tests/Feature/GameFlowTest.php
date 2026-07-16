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

        $player = User::where('email', 'player@seenjeem.test')->firstOrFail();
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

        $player = User::where('email', 'player@seenjeem.test')->firstOrFail();
        $admin = User::where('email', 'admin@seenjeem.test')->firstOrFail();

        $this->actingAs($player)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_assigning_points_twice_is_blocked(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@seenjeem.test')->firstOrFail();
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
}
