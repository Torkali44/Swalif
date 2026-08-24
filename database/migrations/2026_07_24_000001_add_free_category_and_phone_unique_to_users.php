<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'free_category_id')) {
                $table->foreignId('free_category_id')
                    ->nullable()
                    ->constrained('categories')
                    ->nullOnDelete();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            if (Schema::hasColumn('users', 'free_category_id')) {
                $table->dropConstrainedForeignId('free_category_id');
            }
        });
    }
};
