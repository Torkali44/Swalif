<?php

namespace Database\Seeders;

use App\Models\LetterGrid;
use App\Support\LetterGridHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LetterGridSeeder extends Seeder
{
    public function run(): void
    {
        if (LetterGrid::query()->exists()) {
            return;
        }

        $grid = LetterGrid::create([
            'name_ar' => 'شبكة الحروف العربية',
            'slug' => 'arabic-letters-default',
            'description' => 'شبكة افتراضية بجميع حروف اللغة العربية — أضف الأسئلة من لوحة التحكم.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        foreach (LetterGridHelper::defaultCells() as $cell) {
            $grid->cells()->create([
                'letter' => $cell['letter'],
                'row' => $cell['row'],
                'col' => $cell['col'],
                'question_text' => 'سؤال يبدأ بحرف '.$cell['letter'].'…',
                'answer_text' => $cell['letter'].'…',
                'is_active' => true,
            ]);
        }
    }
}
