<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id')->nullable()->change();
            // يسمح بربط الفريق بلعبة خاصة (nullable — لا يؤثر على الفرق العادية)
            $table->unsignedBigInteger('custom_game_id')->nullable()->after('game_id');
            $table->foreign('custom_game_id')
                  ->references('id')
                  ->on('custom_games')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['custom_game_id']);
            $table->dropColumn('custom_game_id');
            $table->unsignedBigInteger('game_id')->nullable(false)->change();
        });
    }
};
