<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\QuestionType;
use App\Http\Requests\StoreQuestionRequest;
use App\Models\Category;
use App\Models\Classification;
use App\Models\Question;
use App\Support\MediaStore;
use App\Support\PublicMedia;
use App\Support\UploadedMediaPath;
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

        if ($request->filled('classification_id')) {
            $categoriesQuery->where('classification_id', $request->integer('classification_id'));
        }

        $categories = $categoriesQuery
            ->with('classification:id,name_ar,icon')
            ->with(['questions' => function ($query) use ($request) {
                $query->select([
                    'id', 'category_id', 'type', 'question_text', 'answer_text', 'meta',
                    'level', 'points', 'time_limit', 'is_active', 'image', 'created_at',
                ])
                    ->with('options:id,question_id,option_text,is_correct')
                    ->orderBy('level')
                    ->orderBy('points')
                    ->orderBy('id');

                if ($request->filled('level')) {
                    $query->where('level', $request->string('level'));
                }   

                if ($request->filled('type')) {
                    $query->where('type', $request->string('type'));
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

        if ($request->filled('q') || $request->filled('level') || $request->filled('type') || $request->filled('points') || $request->filled('status')) {
            $categories = $categories->filter(fn (Category $category) => $category->questions->isNotEmpty())->values();
        }

        return view('admin.questions.index', [
            'groupedCategories' => $categories,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name_ar', 'icon', 'group', 'classification_id']),
            'classifications' => Classification::orderBy('sort_order')->get(['id', 'name_ar', 'icon']),
            'questionTypes' => QuestionType::options(),
            'filters' => $request->only(['category_id', 'classification_id', 'level', 'type', 'points', 'status', 'q']),
        ]);
    }

    public function create()
    {
        return view('admin.questions.form', [
            'question' => new Question,
            'categories' => $this->formCategories(),
            'questionTypes' => QuestionType::options(),
            'maxQuestionsPerCategory' => (int) config('game.max_questions_per_category', 18),
            'maxPerLevel' => (int) config('game.questions_per_level', 6),
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
            'categories' => $this->formCategories(),
            'questionTypes' => QuestionType::options(),
            'maxQuestionsPerCategory' => (int) config('game.max_questions_per_category', 18),
            'maxPerLevel' => (int) config('game.questions_per_level', 6),
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

        // Upload files outside the DB transaction (much faster on shared hosting)
        $newImagePath = null;
        $newAnswerImagePath = null;
        $oldImage = $question->image;
        $oldAnswerImage = $question->answer_image;

        if ($request->hasFile('image')) {
            $folder = match ($data['type']) {
                'video' => 'questions/videos',
                'audio' => 'questions/audio',
                default => 'questions',
            };
            $maxWidth = $data['type'] === 'image_guess' || $data['type'] === 'puzzle' ? 1400 : 1200;
            $newImagePath = MediaStore::store($request->file('image'), $folder, $maxWidth);
        } else {
            $mediaKind = match ($data['type']) {
                'video' => 'video',
                'audio' => 'audio',
                default => 'image',
            };
            $preloaded = (string) ($data['image_path'] ?? '');
            if (UploadedMediaPath::isValid($preloaded, $mediaKind)) {
                $newImagePath = $preloaded;
            }
        }

        if ($request->hasFile('answer_image')) {
            $newAnswerImagePath = MediaStore::store($request->file('answer_image'), 'questions', 1200);
        } else {
            $preloadedAnswer = (string) ($data['answer_image_path'] ?? '');
            if (UploadedMediaPath::isValid($preloadedAnswer, 'image')) {
                $newAnswerImagePath = $preloadedAnswer;
            }
        }

        DB::transaction(function () use ($question, $data, $newImagePath, $newAnswerImagePath) {
            $payload = [
                'category_id' => $data['category_id'],
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'answer_text' => $data['answer_text'] ?? null,
                'meta' => $this->buildMeta($data),
                'level' => $data['level'],
                'points' => $data['points'],
                'time_limit' => $data['time_limit'] ?? 60,
                'is_active' => $data['is_active'] ?? true,
            ];

            if (! empty($data['remove_image'])) {
                $payload['image'] = null;
            }

            if (! empty($data['remove_answer_image'])) {
                $payload['answer_image'] = null;
            }

            if ($newImagePath !== null) {
                $payload['image'] = $newImagePath;
            }

            if ($newAnswerImagePath !== null) {
                $payload['answer_image'] = $newAnswerImagePath;
            }

            $question->fill($payload)->save();

            $question->options()->delete();

            if ($data['type'] === 'standard') {
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
            }
        });

        if ($newImagePath !== null || ! empty($data['remove_image'])) {
            $this->deleteImage($oldImage);
        }

        if ($newAnswerImagePath !== null || ! empty($data['remove_answer_image'])) {
            $this->deleteImage($oldAnswerImage);
        }
    }

    private function formCategories()
    {
        return Category::query()
            ->select(['id', 'name_ar', 'icon', 'classification_id', 'sort_order', 'is_active'])
            ->with(['classification:id,name_ar,icon'])
            ->withCount('questions')
            ->withCount([
                'questions as easy_count' => fn ($q) => $q->where('level', 'easy'),
                'questions as medium_count' => fn ($q) => $q->where('level', 'medium'),
                'questions as hard_count' => fn ($q) => $q->where('level', 'hard'),
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    private function buildMeta(array $data): ?array
    {
        return match ($data['type']) {
            'order' => [
                'order_items' => collect($data['order_items'] ?? [])
                    ->map(fn ($item) => trim((string) $item))
                    ->filter()
                    ->values()
                    ->all(),
            ],
            'match' => [
                'match_pairs' => collect($data['match_pairs'] ?? [])
                    ->map(function ($pair) {
                        return [
                            'left' => trim((string) data_get($pair, 'left', '')),
                            'right' => trim((string) data_get($pair, 'right', '')),
                        ];
                    })
                    ->filter(fn ($pair) => filled($pair['left']) && filled($pair['right']))
                    ->values()
                    ->all(),
            ],
            default => null,
        };
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk(PublicMedia::DISK)->exists($path)) {
            Storage::disk(PublicMedia::DISK)->delete($path);
        }
    }
}
