<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLetterGridRequest;
use App\Models\LetterGrid;
use App\Support\LetterGridHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LetterGridController extends Controller
{
    public function index()
    {
        $grids = LetterGrid::query()
            ->withCount('cells')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('admin.letter-grids.index', compact('grids'));
    }

    public function create()
    {
        return view('admin.letter-grids.form', [
            'grid' => new LetterGrid(['is_active' => true]),
            'cells' => LetterGridHelper::emptyStarterCells(8),
        ]);
    }

    public function store(StoreLetterGridRequest $request)
    {
        $this->save(new LetterGrid(), $request->validated());

        return redirect()
            ->route('admin.letter-grids.index')
            ->with('success', 'تم إنشاء شبكة الحروف.');
    }

    public function edit(LetterGrid $letterGrid)
    {
        $letterGrid->load('cells');

        $cells = $letterGrid->cells->map(fn ($cell) => [
            'letter' => $cell->letter,
            'row' => $cell->row,
            'col' => $cell->col,
            'question_text' => $cell->question_text,
            'answer_text' => $cell->answer_text,
            'is_active' => $cell->is_active,
        ])->values()->all();

        if ($cells === []) {
            $cells = LetterGridHelper::defaultCells();
        }

        return view('admin.letter-grids.form', [
            'grid' => $letterGrid,
            'cells' => $cells,
        ]);
    }

    public function update(StoreLetterGridRequest $request, LetterGrid $letterGrid)
    {
        $this->save($letterGrid, $request->validated());

        return redirect()
            ->route('admin.letter-grids.index')
            ->with('success', 'تم تحديث شبكة الحروف.');
    }

    public function destroy(LetterGrid $letterGrid)
    {
        if ($letterGrid->image) {
            $this->deleteImage($letterGrid->image);
        }
        $letterGrid->delete();

        return redirect()
            ->route('admin.letter-grids.index')
            ->with('success', 'تم حذف شبكة الحروف.');
    }

    public function toggle(LetterGrid $letterGrid)
    {
        $letterGrid->update(['is_active' => ! $letterGrid->is_active]);

        return back()->with('success', 'تم تحديث حالة الشبكة.');
    }

    private function save(LetterGrid $grid, array $data): LetterGrid
    {
        return DB::transaction(function () use ($grid, $data) {
            $image = $grid->image;

            if (! empty($data['remove_image']) && $image) {
                $this->deleteImage($image);
                $image = null;
            }

            if (! empty($data['image_path']) && $data['image_path'] !== $grid->image) {
                if ($grid->image) {
                    $this->deleteImage($grid->image);
                }
                $image = $data['image_path'];
            }

            $grid->fill([
                'name_ar' => $data['name_ar'],
                'slug' => filled($data['slug'] ?? null)
                    ? Str::slug($data['slug'])
                    : Str::slug($data['name_ar']),
                'description' => $data['description'] ?? null,
                'image' => $image,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ])->save();

            $grid->cells()->delete();

            foreach ($data['cells'] as $cell) {
                $grid->cells()->create([
                    'letter' => trim((string) $cell['letter']),
                    'row' => (int) $cell['row'],
                    'col' => (int) $cell['col'],
                    'question_text' => trim((string) ($cell['question_text'] ?? '')),
                    'answer_text' => trim((string) ($cell['answer_text'] ?? '')),
                    'is_active' => (bool) ($cell['is_active'] ?? true),
                ]);
            }

            return $grid->fresh('cells');
        });
    }

    private function deleteImage(?string $path): void
    {
        if ($path && \Illuminate\Support\Facades\Storage::disk(\App\Support\PublicMedia::DISK)->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk(\App\Support\PublicMedia::DISK)->delete($path);
        }
    }
}
