<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_questions', function (Blueprint $table) {
            $table->foreignId('selected_option_id')->nullable()->after('question_id')->constrained('question_options')->nullOnDelete();
            $table->text('player_answer')->nullable()->after('selected_option_id');
        });
    }

    public function down(): void
    {
        Schema::table('game_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('selected_option_id');
            $table->dropColumn('player_answer');
        });
    }
};
