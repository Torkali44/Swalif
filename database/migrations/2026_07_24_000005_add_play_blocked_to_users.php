<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('play_blocked')->default(false)->after('is_active');
            $table->timestamp('play_blocked_at')->nullable()->after('play_blocked');
            $table->string('play_blocked_reason')->nullable()->after('play_blocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['play_blocked', 'play_blocked_at', 'play_blocked_reason']);
        });
    }
};
