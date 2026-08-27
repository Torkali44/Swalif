<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('letter_grids', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
        });

        Schema::table('letter_grid_games', function (Blueprint $table) {
            $table->boolean('custom_bonus_awarded')->default(false)->after('answered_count');
        });

        Schema::table('custom_game_letter_grids', function (Blueprint $table) {
            $table->foreignId('winner_team_id')
                ->nullable()
                ->after('sort_order')
                ->constrained('teams')
                ->nullOnDelete();
            $table->unsignedSmallInteger('points_awarded')->default(0)->after('winner_team_id');
        });
    }

    public function down(): void
    {
        Schema::table('custom_game_letter_grids', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_team_id');
            $table->dropColumn('points_awarded');
        });

        Schema::table('letter_grid_games', function (Blueprint $table) {
            $table->dropColumn('custom_bonus_awarded');
        });

        Schema::table('letter_grids', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
