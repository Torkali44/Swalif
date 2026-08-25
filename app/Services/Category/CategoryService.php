<?php

namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryService
{
    public function activeOrdered()
    {
        return Category::query()
            ->select([
                'id', 'name_ar', 'name_en', 'slug', 'icon', 'image', 'group',
                'classification_id', 'description', 'is_active', 'sort_order',
            ])
            ->where('is_active', true)
            ->with(['classification:id,name_ar,name_en,icon,image,is_active,sort_order'])
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
