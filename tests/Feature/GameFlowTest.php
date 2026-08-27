<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Character;
use App\Models\Game;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameFlowTest extends TestCase
{
    use RefreshDatabase;

    private function characterPair(): array
    {
        $ids = Character::query()->orderBy('id')->pluck('id')->take(2)->all();

        return [
            'team_one_character_id' => $ids[0],
            'team_two_character_id' => $ids[1],
        ];
    }

    public function test_player_can_create_and_play_a_game(): void
    {
        $this->withoutVite();
        $this->seed();

        $player   = User::where('email', 'player@swalif.test')->firstOrFail();
        $category = Category::where('slug', 'uae-malls')->firstOrFail();

        $response = $this->actingAs($player)->post(route('game.start'), [
            'category_id' => $category->id,
            'name'        => 'اختبار اللعبة',
            'team_one'    => 'الصقور',
            'team_two'    => 'النجوم',
            ...$this->characterPair(),
        ]);

        $response->assertRedirect();
        $game = Game::where('user_id', $player->id)->latest('id')->firstOrFail();

        $this->get(route('game.board', $game))->assertOk();

        // Use a question that is actually locked to this game (not just any category question)
        $lockedQuestion = $game->gameQuestions()->with('question')->firstOrFail()->question;
        $this->get(route('game.question', [$game, $lockedQuestion]))->assertOk();
    }


    public function test_admin_dashboard_is_protected_by_role(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $admin = User::where('email', 'omjori_Swalif_Admin_009@gmail.com')->firstOrFail();

        $this->actingAs($player)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_assigning_points_twice_is_blocked(): void
    {
        $this->withoutVite();
        $this->seed();

        $player   = User::where('email', 'player@swalif.test')->firstOrFail();
        $category = Category::where('slug', 'uae-malls')->firstOrFail();

        $this->actingAs($player)->post(route('game.start'), [
            'category_id' => $category->id,
            'name'        => 'جولة نقاط',
            'team_one'    => 'أ',
            'team_two'    => 'ب',
            ...$this->characterPair(),
        ]);

        $game = Game::firstOrFail();
        // Use a GameQuestion that is locked to THIS game
        $gq   = $game->gameQuestions()->with('question')->firstOrFail();
        $team = $game->teams()->firstOrFail();

        // Visit the question page via the locked GameQuestion
        $this->get(route('game.question', [$game, $gq->question]))->assertOk();

        // First assign — should succeed and redirect to board
        $this->post(route('game.assign', [$game, $gq]), ['team_id' => $team->id])
            ->assertRedirect();

        // Second assign — should be blocked and still redirect (with error flash)
        $this->post(route('game.assign', [$game, $gq]), ['team_id' => $team->id])
            ->assertRedirect();

        // Score must equal exactly one assignment (not doubled)
        $this->assertSame((int) $gq->question->points, (int) $team->fresh()->score);
    }


    public function test_finishing_all_questions_redirects_to_result_with_team_stats(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $category = Category::where('slug', 'uae-malls')->firstOrFail();

        $this->actingAs($player)->post(route('game.start'), [
            'category_id' => $category->id,
            'name' => 'نهاية اللعبة',
            'team_one' => 'الأسود',
            'team_two' => 'النمور',
            ...$this->characterPair(),
        ]);

        $game = Game::query()->latest('id')->firstOrFail();
        $teams = $game->teams()->orderBy('id')->get();
        $questions = $game->gameQuestions()->with('question')->get();

        $this->assertGreaterThan(0, $questions->count());

        foreach ($questions as $index => $gq) {
            $this->get(route('game.question', [$game, $gq->question]))->assertOk();

            $teamId = $index === 0 ? $teams[0]->id : null;
            $response = $this->post(route('game.assign', [$game, $gq]), [
                'team_id' => $teamId,
            ]);

            if ($index === $questions->count() - 1) {
                $response->assertRedirect(route('game.result', $game));
                $response->assertSessionHas('game_just_ended', true);
            } else {
                $response->assertRedirect(route('game.board', $game));
            }
        }

        $result = $this->get(route('game.result', $game));
        $result->assertOk();
        $result->assertSee('الأسود');
        $result->assertSee('النمور');
        $result->assertSee('صحيحة');
        $result->assertSee('خاطئة');
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
            ...$this->characterPair(),
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
            ...$this->characterPair(),
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
