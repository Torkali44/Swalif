<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()->with('classification')->withCount('questions')->orderBy('sort_order');

        if ($request->filled('classification_id')) {
            $query->where('classification_id', $request->integer('classification_id'));
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
            'classifications' => Classification::orderBy('sort_order')->get(['id', 'name_ar', 'icon']),
            'filters' => $request->only(['classification_id', 'status', 'q']),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new Category,
            'classifications' => Classification::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $data['group'] = Classification::findOrFail($data['classification_id'])->name_ar;
        $data['slug'] = Str::slug($data['name_en'] ?: $data['name_ar']).'-'.Str::random(4);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        $desired = (int) $request->input('sort_order', 0);
        $this->applyOrdering($category, $desired >= 1 ? $desired : PHP_INT_MAX);

        return redirect()->route('admin.categories.index')->with('success', 'تمت إضافة الفئة بنجاح.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'classifications' => Classification::orderBy('sort_order')->get(),
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $data['group'] = Classification::findOrFail($data['classification_id'])->name_ar;

        if ($request->boolean('remove_image') && $category->image) {
            $this->deleteImage($category->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        $desired = (int) $request->input('sort_order', 0);
        $this->applyOrdering($category, $desired >= 1 ? $desired : PHP_INT_MAX);

        return redirect()->route('admin.categories.index')->with('success', 'تم حفظ التعديلات بنجاح.');
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

    /**
     * Place the given category at the desired 1-based position and
     * re-sequence every category so each one holds a unique order (1..N).
     */
    private function applyOrdering(Category $saved, int $desired): void
    {
        $others = Category::where('id', '!=', $saved->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $position = max(1, min($desired, $others->count() + 1));

        $ordered = $others->values();
        $ordered->splice($position - 1, 0, [$saved]);

        foreach ($ordered->values() as $i => $category) {
            $newOrder = $i + 1;
            if ((int) $category->sort_order !== $newOrder) {
                $category->sort_order = $newOrder;
                $category->saveQuietly();
            }
        }
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
