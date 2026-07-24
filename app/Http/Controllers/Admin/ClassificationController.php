<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassificationRequest;
use App\Models\Classification;
use App\Support\PublicMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClassificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Classification::query()->withCount('categories')->orderBy('sort_order');

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

        return view('admin.classifications.index', [
            'classifications' => $query->get(),
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function create()
    {
        return view('admin.classifications.form', ['classification' => new Classification]);
    }

    public function store(StoreClassificationRequest $request)
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $data['slug'] = Str::slug($data['name_en'] ?: $data['name_ar']).'-'.Str::random(4);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('classifications', PublicMedia::DISK);
        }

        $classification = Classification::create($data);

        $desired = (int) $request->input('sort_order', 0);
        $this->applyOrdering($classification, $desired >= 1 ? $desired : PHP_INT_MAX);

        return redirect()->route('admin.classifications.index')->with('success', 'تمت إضافة التصنيف بنجاح.');
    }

    public function edit(Classification $classification)
    {
        return view('admin.classifications.form', compact('classification'));
    }

    public function update(StoreClassificationRequest $request, Classification $classification)
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->boolean('remove_image') && $classification->image) {
            $this->deleteImage($classification->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($classification->image);
            $data['image'] = $request->file('image')->store('classifications', PublicMedia::DISK);
        }

        $classification->update($data);
        $classification->categories()->update(['group' => $classification->name_ar]);

        $desired = (int) $request->input('sort_order', 0);
        $this->applyOrdering($classification, $desired >= 1 ? $desired : PHP_INT_MAX);

        return redirect()->route('admin.classifications.index')->with('success', 'تم حفظ التعديلات بنجاح.');
    }

    public function destroy(Classification $classification)
    {
        if ($classification->categories()->exists()) {
            return back()->with('error', 'لا يمكن حذف التصنيف لأنه مستخدم داخل فئات.');
        }

        $this->deleteImage($classification->image);
        $classification->delete();

        return back()->with('success', 'تم حذف التصنيف.');
    }

    public function toggle(Classification $classification)
    {
        $classification->update(['is_active' => ! $classification->is_active]);

        return back()->with('success', 'تم تحديث حالة التصنيف.');
    }

    private function applyOrdering(Classification $saved, int $desired): void
    {
        $others = Classification::where('id', '!=', $saved->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $position = max(1, min($desired, $others->count() + 1));

        $ordered = $others->values();
        $ordered->splice($position - 1, 0, [$saved]);

        foreach ($ordered->values() as $i => $classification) {
            $newOrder = $i + 1;
            if ((int) $classification->sort_order !== $newOrder) {
                $classification->sort_order = $newOrder;
                $classification->saveQuietly();
            }
        }
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk(PublicMedia::DISK)->exists($path)) {
            Storage::disk(PublicMedia::DISK)->delete($path);
        }
    }
}
