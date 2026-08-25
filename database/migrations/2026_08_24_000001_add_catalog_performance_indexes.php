<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'categories_active_sort_idx');
            $table->index(['classification_id', 'is_active'], 'categories_class_active_idx');
        });

        Schema::table('classifications', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'classifications_active_sort_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->index(['category_id', 'is_active', 'level'], 'questions_cat_active_level_idx');
            $table->index(['is_active'], 'questions_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_active_sort_idx');
            $table->dropIndex('categories_class_active_idx');
        });

        Schema::table('classifications', function (Blueprint $table) {
            $table->dropIndex('classifications_active_sort_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('questions_cat_active_level_idx');
            $table->dropIndex('questions_active_idx');
        });
    }
};
