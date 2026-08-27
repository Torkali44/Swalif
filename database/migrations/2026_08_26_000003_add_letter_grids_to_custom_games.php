<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('custom_game_letter_grids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_game_id')
                ->constrained('custom_games')
                ->cascadeOnDelete();
            $table->foreignId('letter_grid_id')
                ->constrained('letter_grids')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['custom_game_id', 'letter_grid_id']);
        });

        Schema::table('letter_grid_games', function (Blueprint $table) {
            $table->foreignId('custom_game_id')
                ->nullable()
                ->after('letter_grid_id')
                ->constrained('custom_games')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('letter_grid_games', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_game_id');
        });

        Schema::dropIfExists('custom_game_letter_grids');
    }
};
