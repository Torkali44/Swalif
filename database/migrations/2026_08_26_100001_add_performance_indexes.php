<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_grid_games', function (Blueprint $table) {
            $table->index('user_id', 'lgg_user_idx');
            $table->index('letter_grid_id', 'lgg_grid_idx');
            $table->index('status', 'lgg_status_idx');
            $table->index('custom_game_id', 'lgg_custom_game_idx');
        });

        Schema::table('letter_grid_game_cells', function (Blueprint $table) {
            $table->index('letter_grid_game_id', 'lggc_game_idx');
            $table->index('claimed_team_id', 'lggc_claimed_team_idx');
        });

        Schema::table('letter_grid_cells', function (Blueprint $table) {
            $table->index(['letter_grid_id', 'is_active'], 'lgc_grid_active_idx');
        });

        Schema::table('letter_grids', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'lg_active_sort_idx');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->index('letter_grid_game_id', 'teams_lgg_idx');
            $table->index('custom_game_id', 'teams_custom_game_idx');
        });

        Schema::table('games', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'games_user_status_idx');
            $table->index('category_id', 'games_category_idx');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'ends_at'], 'subs_user_status_ends_idx');
        });

        Schema::table('game_questions', function (Blueprint $table) {
            $table->index('assigned_team_id', 'gq_assigned_team_idx');
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'chars_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::table('letter_grid_games', function (Blueprint $table) {
            $table->dropIndex('lgg_user_idx');
            $table->dropIndex('lgg_grid_idx');
            $table->dropIndex('lgg_status_idx');
            $table->dropIndex('lgg_custom_game_idx');
        });

        Schema::table('letter_grid_game_cells', function (Blueprint $table) {
            $table->dropIndex('lggc_game_idx');
            $table->dropIndex('lggc_claimed_team_idx');
        });

        Schema::table('letter_grid_cells', function (Blueprint $table) {
            $table->dropIndex('lgc_grid_active_idx');
        });

        Schema::table('letter_grids', function (Blueprint $table) {
            $table->dropIndex('lg_active_sort_idx');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex('teams_lgg_idx');
            $table->dropIndex('teams_custom_game_idx');
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex('games_user_status_idx');
            $table->dropIndex('games_category_idx');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subs_user_status_ends_idx');
        });

        Schema::table('game_questions', function (Blueprint $table) {
            $table->dropIndex('gq_assigned_team_idx');
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropIndex('chars_active_sort_idx');
        });
    }
};
