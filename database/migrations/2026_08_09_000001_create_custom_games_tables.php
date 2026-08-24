<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // جدول الألعاب الخاصة — مستقل تماماً عن جدول games الحالي
        Schema::create('custom_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('status')->default('playing'); // playing | finished
            $table->unsignedBigInteger('winner_team_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // Pivot: ربط اللعبة الخاصة بالفئات (4-6 فئات)
        Schema::create('custom_game_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_game_id')
                ->constrained('custom_games')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['custom_game_id', 'category_id']);
        });

        // جدول أسئلة اللعبة الخاصة — مشابه لـ game_questions لكن مستقل
        Schema::create('custom_game_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_game_id')
                ->constrained('custom_games')
                ->cascadeOnDelete();
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->foreignId('assigned_team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();
            $table->foreignId('turn_team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();
            $table->foreignId('selected_option_id')
                ->nullable()
                ->constrained('question_options')
                ->nullOnDelete();
            $table->text('player_answer')->nullable();
            $table->unsignedSmallInteger('points_awarded')->default(0);
            $table->boolean('answered_correctly')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->unique(['custom_game_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_game_questions');
        Schema::dropIfExists('custom_game_categories');
        Schema::dropIfExists('custom_games');
    }
};
