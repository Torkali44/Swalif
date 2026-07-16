<?php

namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Support\Collection;

class QuestionPickerService
{
    public function forBoard(Category $category): Collection
    {
        return $category->questions()
            ->where('is_active', true)
            ->orderBy('points')
            ->orderBy('id')
            ->get()
            ->groupBy('level');
    }
}
