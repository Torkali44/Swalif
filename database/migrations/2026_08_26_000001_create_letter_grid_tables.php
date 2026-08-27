<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('letter_grids', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('letter_grid_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_grid_id')->constrained('letter_grids')->cascadeOnDelete();
            $table->string('letter', 10);
            $table->unsignedSmallInteger('row')->default(0);
            $table->unsignedSmallInteger('col')->default(0);
            $table->text('question_text');
            $table->string('answer_text', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['letter_grid_id', 'letter']);
        });

        Schema::create('letter_grid_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('letter_grid_id')->constrained('letter_grids')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('status')->default('playing');
            $table->unsignedBigInteger('winner_team_id')->nullable();
            $table->unsignedBigInteger('active_cell_id')->nullable();
            $table->unsignedTinyInteger('turn_index')->default(0);
            $table->unsignedSmallInteger('answered_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('letter_grid_game_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_grid_game_id')->constrained('letter_grid_games')->cascadeOnDelete();
            $table->string('letter', 10);
            $table->unsignedSmallInteger('row')->default(0);
            $table->unsignedSmallInteger('col')->default(0);
            $table->text('question_text');
            $table->string('answer_text', 255);
            $table->foreignId('claimed_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('turn_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->boolean('answered_correctly')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->unique(['letter_grid_game_id', 'letter']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('letter_grid_game_id')
                ->nullable()
                ->after('custom_game_id')
                ->constrained('letter_grid_games')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('letter_grid_game_id');
        });

        Schema::dropIfExists('letter_grid_game_cells');
        Schema::dropIfExists('letter_grid_games');
        Schema::dropIfExists('letter_grid_cells');
        Schema::dropIfExists('letter_grids');
    }
};
