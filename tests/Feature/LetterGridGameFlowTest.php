<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\LetterGrid;
use App\Models\LetterGridGame;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterGridGameFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_start_claim_and_cannot_double_claim(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $characters = Character::query()->where('is_active', true)->orderBy('id')->take(2)->get();
        $this->assertGreaterThanOrEqual(2, $characters->count());

        $grid = LetterGrid::create([
            'name_ar' => 'شبكة اختبار',
            'slug' => 'test-grid-flow',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        foreach ([
            ['letter' => 'أ', 'row' => 0, 'col' => 0],
            ['letter' => 'ب', 'row' => 0, 'col' => 1],
            ['letter' => 'ت', 'row' => 1, 'col' => 0],
        ] as $cell) {
            $grid->cells()->create([
                'letter' => $cell['letter'],
                'row' => $cell['row'],
                'col' => $cell['col'],
                'question_text' => 'سؤال '.$cell['letter'],
                'answer_text' => 'جواب '.$cell['letter'],
                'is_active' => true,
            ]);
        }

        $this->actingAs($player)->post(route('letter-grid.store'), [
            'letter_grid_id' => $grid->id,
            'team_one' => 'فريق أ',
            'team_two' => 'فريق ب',
            'team_one_character_id' => $characters[0]->id,
            'team_two_character_id' => $characters[1]->id,
        ])->assertRedirect();

        $game = LetterGridGame::where('user_id', $player->id)->latest('id')->firstOrFail();
        $this->get(route('letter-grid.play', $game))->assertOk();

        $cell = $game->cells()->firstOrFail();
        $team = $game->teams()->firstOrFail();

        $claim = $this->postJson(route('letter-grid.claim', [$game, $cell]), [
            'team_id' => $team->id,
            'correct' => true,
        ]);

        $claim->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('cell.id', $cell->id)
            ->assertJsonPath('resolved', 1);

        $this->assertDatabaseHas('letter_grid_game_cells', [
            'id' => $cell->id,
            'claimed_team_id' => $team->id,
            'answered_correctly' => true,
        ]);

        $this->assertSame(1, (int) $team->fresh()->score);

        // Double claim must fail
        $this->postJson(route('letter-grid.claim', [$game, $cell]), [
            'team_id' => $team->id,
            'correct' => true,
        ])->assertStatus(422);
    }

    public function test_other_user_cannot_access_letter_grid_game(): void
    {
        $this->withoutVite();
        $this->seed();

        $owner = User::where('email', 'player@swalif.test')->firstOrFail();
        $other = User::factory()->create([
            'email' => 'other@swalif.test',
            'is_admin' => false,
            'is_active' => true,
        ]);
        $characters = Character::query()->where('is_active', true)->orderBy('id')->take(2)->get();

        $grid = LetterGrid::create([
            'name_ar' => 'شبكة ملكية',
            'slug' => 'ownership-grid',
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $grid->cells()->create([
            'letter' => 'م',
            'row' => 0,
            'col' => 0,
            'question_text' => 'سؤال',
            'answer_text' => 'جواب',
            'is_active' => true,
        ]);

        $this->actingAs($owner)->post(route('letter-grid.store'), [
            'letter_grid_id' => $grid->id,
            'team_one' => 'أ',
            'team_two' => 'ب',
            'team_one_character_id' => $characters[0]->id,
            'team_two_character_id' => $characters[1]->id,
        ])->assertRedirect();

        $game = LetterGridGame::where('user_id', $owner->id)->latest('id')->firstOrFail();

        $this->actingAs($other)->get(route('letter-grid.play', $game))->assertForbidden();
    }

    public function test_letter_grid_can_start_with_automatic_team_names_from_characters(): void
    {
        $this->withoutVite();
        $this->seed();

        $player = User::where('email', 'player@swalif.test')->firstOrFail();
        $characters = Character::query()->where('is_active', true)->orderBy('id')->take(2)->get();

        $grid = LetterGrid::create([
            'name_ar' => 'شبكة أسماء تلقائية',
            'slug' => 'auto-names-grid',
            'is_active' => true,
            'sort_order' => 3,
        ]);
        $grid->cells()->create([
            'letter' => 'س',
            'row' => 0,
            'col' => 0,
            'question_text' => 'سؤال',
            'answer_text' => 'جواب',
            'is_active' => true,
        ]);

        $response = $this->actingAs($player)->post(route('letter-grid.store'), [
            'letter_grid_id' => $grid->id,
            'team_one_character_id' => $characters[0]->id,
            'team_two_character_id' => $characters[1]->id,
        ]);

        $response->assertRedirect();
        $game = LetterGridGame::where('user_id', $player->id)->latest('id')->firstOrFail();
        $teams = $game->teams()->orderBy('id')->get();

        $this->assertCount(2, $teams);
        $this->assertSame($characters[0]->name_ar, $teams[0]->name);
        $this->assertSame($characters[1]->name_ar, $teams[1]->name);
    }
}
