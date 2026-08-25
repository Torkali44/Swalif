<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCharacterRequest;
use App\Models\Character;
use App\Support\MediaStore;
use App\Support\PublicMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CharacterController extends Controller
{
    public function index(Request $request)
    {
        $query = Character::query()->withCount('users')->orderBy('sort_order');

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('name_ar', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%");
            });
        }

        return view('admin.characters.index', [
            'characters' => $query->paginate(60)->withQueryString(),
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function create()
    {
        return view('admin.characters.form', ['character' => new Character]);
    }

    public function store(StoreCharacterRequest $request)
    {
        $data = $request->safe()->except(['image', 'image_path', 'remove_image']);
        $data['slug'] = Str::slug($data['name_en'] ?: $data['name_ar']).'-'.Str::random(4);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->filled('image_path')) {
            $data['image'] = $request->input('image_path');
        } elseif ($request->hasFile('image')) {
            $data['image'] = MediaStore::store($request->file('image'), 'characters', 480);
        }

        $character = Character::create($data);

        $desired = (int) $request->input('sort_order', 0);
        $this->applyOrdering($character, $desired >= 1 ? $desired : PHP_INT_MAX);

        return redirect()->route('admin.characters.index')->with('success', 'تمت إضافة الشخصية بنجاح.');
    }

    public function edit(Character $character)
    {
        return view('admin.characters.form', compact('character'));
    }

    public function update(StoreCharacterRequest $request, Character $character)
    {
        $data = $request->safe()->except(['image', 'image_path', 'remove_image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_image') && $character->image) {
            $this->deleteImage($character->image);
            $data['image'] = null;
        }

        if ($request->filled('image_path')) {
            $newPath = $request->input('image_path');
            if ($newPath !== $character->image) {
                $this->deleteImage($character->image);
            }
            $data['image'] = $newPath;
        } elseif ($request->hasFile('image')) {
            $this->deleteImage($character->image);
            $data['image'] = MediaStore::store($request->file('image'), 'characters', 480);
        }

        $character->update($data);

        $desired = (int) $request->input('sort_order', 0);
        $this->applyOrdering($character, $desired >= 1 ? $desired : PHP_INT_MAX);

        return redirect()->route('admin.characters.index')->with('success', 'تم حفظ التعديلات بنجاح.');
    }

    public function destroy(Character $character)
    {
        if ($character->users()->exists()) {
            return back()->with('error', 'لا يمكن حذف الشخصية لأن لاعبين يستخدمونها حالياً.');
        }

        $this->deleteImage($character->image);
        $character->delete();

        return back()->with('success', 'تم حذف الشخصية.');
    }

    public function toggle(Character $character)
    {
        $character->update(['is_active' => ! $character->is_active]);

        return back()->with('success', 'تم تحديث حالة الشخصية.');
    }

    private function applyOrdering(Character $saved, int $desired): void
    {
        $others = Character::where('id', '!=', $saved->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $position = max(1, min($desired, $others->count() + 1));

        $ordered = $others->values();
        $ordered->splice($position - 1, 0, [$saved]);

        foreach ($ordered->values() as $i => $character) {
            $newOrder = $i + 1;
            if ((int) $character->sort_order !== $newOrder) {
                $character->sort_order = $newOrder;
                $character->saveQuietly();
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
