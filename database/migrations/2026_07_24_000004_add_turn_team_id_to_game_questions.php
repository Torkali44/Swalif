<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_questions', function (Blueprint $table) {
            $table->foreignId('turn_team_id')
                ->nullable()
                ->after('selected_option_id')
                ->constrained('teams')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('game_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('turn_team_id');
        });
    }
};
