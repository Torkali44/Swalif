<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()->withCount('questions')->orderBy('sort_order');

        if ($request->filled('group')) {
            $query->where('group', $request->string('group'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('name_ar', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%");
            });
        }

        return view('admin.categories.index', [
            'categories' => $query->get(),
            'filters' => $request->only(['group', 'status', 'q']),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $data['slug'] = Str::slug($data['name_en'] ?: $data['name_ar']).'-'.Str::random(4);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'تمت إضافة الفئة.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->boolean('remove_image') && $category->image) {
            $this->deleteImage($category->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'تم حفظ التعديلات.');
    }

    public function destroy(Category $category)
    {
        $this->deleteImage($category->image);
        $category->delete();

        return back()->with('success', 'تم حذف الفئة.');
    }

    public function toggle(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', 'تم تحديث حالة الفئة.');
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
