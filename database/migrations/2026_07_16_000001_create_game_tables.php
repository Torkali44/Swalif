<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name_ar'); $table->string('name_en')->nullable(); $table->string('slug')->unique();
            $table->string('group')->default('general'); $table->string('icon')->nullable(); $table->text('description')->nullable();
            $table->boolean('is_active')->default(true); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id(); $table->foreignId('category_id')->constrained()->cascadeOnDelete(); $table->text('question_text');
            $table->string('level'); $table->unsignedSmallInteger('points'); $table->unsignedSmallInteger('time_limit')->default(60);
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('question_options', function (Blueprint $table) {
            $table->id(); $table->foreignId('question_id')->constrained()->cascadeOnDelete(); $table->string('option_text'); $table->boolean('is_correct')->default(false);
        });
        Schema::create('games', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('status')->default('playing'); $table->unsignedBigInteger('winner_team_id')->nullable();
            $table->timestamp('started_at')->nullable(); $table->timestamp('ended_at')->nullable(); $table->timestamps();
        });
        Schema::create('teams', function (Blueprint $table) {
            $table->id(); $table->foreignId('game_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->integer('score')->default(0); $table->json('helpers_left')->nullable(); $table->timestamps();
        });
        Schema::create('game_questions', function (Blueprint $table) {
            $table->id(); $table->foreignId('game_id')->constrained()->cascadeOnDelete(); $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_team_id')->nullable()->constrained('teams')->nullOnDelete(); $table->unsignedSmallInteger('points_awarded')->default(0);
            $table->boolean('answered_correctly')->default(false); $table->timestamp('answered_at')->nullable(); $table->timestamps();
            $table->unique(['game_id', 'question_id']);
        });
        Schema::create('plans', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('type'); $table->decimal('price', 8, 2); $table->string('currency', 3)->default('AED');
            $table->unsignedSmallInteger('duration_days'); $table->json('features')->nullable(); $table->boolean('is_active')->default(true); $table->boolean('is_recommended')->default(false); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at'); $table->timestamp('ends_at'); $table->string('status')->default('active'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('subscriptions'); Schema::dropIfExists('plans'); Schema::dropIfExists('game_questions'); Schema::dropIfExists('teams'); Schema::dropIfExists('games'); Schema::dropIfExists('question_options'); Schema::dropIfExists('questions'); Schema::dropIfExists('categories'); }
};
