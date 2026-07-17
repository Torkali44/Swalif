<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $categoriesQuery = Category::query()
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('category_id')) {
            $categoriesQuery->whereKey($request->integer('category_id'));
        }

        if ($request->filled('group')) {
            $categoriesQuery->where('group', $request->string('group'));
        }

        $categories = $categoriesQuery
            ->with(['questions' => function ($query) use ($request) {
                $query->orderBy('level')->orderBy('points')->orderBy('id');

                if ($request->filled('level')) {
                    $query->where('level', $request->string('level'));
                }

                if ($request->filled('points')) {
                    $query->where('points', $request->integer('points'));
                }

                if ($request->filled('status')) {
                    $query->where('is_active', $request->string('status') === 'active');
                }

                if ($request->filled('q')) {
                    $q = $request->string('q');
                    $query->where('question_text', 'like', "%{$q}%");
                }
            }])
            ->withCount('questions')
            ->get();

        if ($request->filled('q') || $request->filled('level') || $request->filled('points') || $request->filled('status')) {
            $categories = $categories->filter(fn (Category $category) => $category->questions->isNotEmpty())->values();
        }

        return view('admin.questions.index', [
            'groupedCategories' => $categories,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name_ar', 'icon', 'group']),
            'filters' => $request->only(['category_id', 'group', 'level', 'points', 'status', 'q']),
        ]);
    }

    public function create()
    {
        return view('admin.questions.form', [
            'question' => new Question,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreQuestionRequest $request)
    {
        $this->save($request, new Question);

        return redirect()->route('admin.questions.index')->with('success', 'تمت إضافة السؤال.');
    }

    public function edit(Question $question)
    {
        $question->load('options');

        return view('admin.questions.form', [
            'question' => $question,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(StoreQuestionRequest $request, Question $question)
    {
        $this->save($request, $question);

        return redirect()->route('admin.questions.index')->with('success', 'تم حفظ السؤال.');
    }

    public function destroy(Question $question)
    {
        $this->deleteImage($question->image);
        $this->deleteImage($question->answer_image);
        $question->delete();

        return back()->with('success', 'تم حذف السؤال.');
    }

    public function toggle(Question $question)
    {
        $question->update(['is_active' => ! $question->is_active]);

        return back()->with('success', 'تم تحديث حالة السؤال.');
    }

    private function save(StoreQuestionRequest $request, Question $question): void
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $question, $data) {
            $payload = [
                'category_id' => $data['category_id'],
                'question_text' => $data['question_text'],
                'answer_text' => $data['answer_text'] ?? null,
                'level' => $data['level'],
                'points' => $data['points'],
                'time_limit' => $data['time_limit'] ?? 60,
                'is_active' => $data['is_active'] ?? true,
            ];

            if (! empty($data['remove_image']) && $question->image) {
                $this->deleteImage($question->image);
                $payload['image'] = null;
            }

            if (! empty($data['remove_answer_image']) && $question->answer_image) {
                $this->deleteImage($question->answer_image);
                $payload['answer_image'] = null;
            }

            if ($request->hasFile('image')) {
                $this->deleteImage($question->image);
                $payload['image'] = $request->file('image')->store('questions', 'public');
            }

            if ($request->hasFile('answer_image')) {
                $this->deleteImage($question->answer_image);
                $payload['answer_image'] = $request->file('answer_image')->store('questions', 'public');
            }

            $question->fill($payload)->save();

            $question->options()->delete();

            $options = collect($data['options'] ?? [])
                ->map(fn ($text) => trim((string) $text))
                ->filter()
                ->values();

            foreach ($options as $index => $optionText) {
                $question->options()->create([
                    'option_text' => $optionText,
                    'is_correct' => (int) $index === (int) ($data['correct_option'] ?? -1),
                ]);
            }
        });
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
