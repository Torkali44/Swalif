<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('classification_id')
                ->nullable()
                ->after('group')
                ->constrained('classifications')
                ->nullOnDelete();
        });

        $groups = DB::table('categories')
            ->select('group')
            ->whereNotNull('group')
            ->where('group', '!=', '')
            ->distinct()
            ->pluck('group');

        foreach ($groups->values() as $index => $group) {
            $name = match ($group) {
                'uae' => 'إمارات',
                'general' => 'عامة',
                default => $group,
            };

            $id = DB::table('classifications')->insertGetId([
                'name_ar' => $name,
                'name_en' => in_array($group, ['uae', 'general'], true) ? $group : null,
                'slug' => Str::slug($group ?: $name).'-'.Str::random(4),
                'icon' => $group === 'uae' ? '🇦🇪' : '🎯',
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('categories')->where('group', $group)->update([
                'classification_id' => $id,
                'group' => $name,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('classification_id');
        });

        Schema::dropIfExists('classifications');
    }
};
