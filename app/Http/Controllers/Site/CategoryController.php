<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Category\CategoryService;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categories) {}

    public function index()
    {
        return view('site.categories.index', [
            'categories' => $this->categories->activeOrdered(),
        ]);
    }

    public function show(Category $category)
    {
        abort_unless($category->is_active, 404);
        $category->loadCount('questions');

        return view('site.categories.show', compact('category'));
    }
}
