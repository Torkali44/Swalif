<?php

namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryService
{
    public function activeOrdered()
    {
        return Category::query()
            ->where('is_active', true)
            ->with('classification')
            ->withCount(['questions' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function activeGrouped(): Collection
    {
        return $this->activeOrdered()->groupBy(fn (Category $category) => $category->classificationName());
    }
}
