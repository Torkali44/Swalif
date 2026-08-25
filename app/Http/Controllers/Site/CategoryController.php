<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Classification;
use App\Services\Category\CategoryPlayPoolService;
use App\Services\Category\CategoryService;
use App\Services\Subscription\FreeTrialService;
use App\Services\Subscription\PlayAccessService;

use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categories,
        private CategoryPlayPoolService $playPool,
        private FreeTrialService $freeTrial,
        private PlayAccessService $playAccess,
    ) {}

    public function index()
    {
        $user = request()->user();
        $playBlocked = $user && $this->playAccess->isBlocked($user);
        $freeLocked = $user && ! $playBlocked && $this->freeTrial->hasConsumedFreeCategory($user);
        $allowedCategoryId = $user && ! $playBlocked ? $this->freeTrial->freeCategoryId($user) : null;

        $categories = Cache::remember('categories.active_ordered', 120, fn () => $this->categories->activeOrdered());
        $categories = $this->playPool->decorateCategories($categories, $user);

        return view('site.categories.index', [
            'categories' => $categories,
            'classifications' => Cache::remember('categories.active_classifications', 120, fn () => Classification::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name_ar', 'icon', 'slug'])),
            'playBlocked' => $playBlocked,
            'freeLocked' => $freeLocked || $playBlocked,
            'allowedCategoryId' => $allowedCategoryId,
            'subscribeMessage' => $playBlocked
                ? $this->playAccess->blockMessage($user)
                : $this->freeTrial->subscribeRequiredMessage(),
        ]);
    }

    public function show(Category $category)
    {
        abort_unless($category->is_active, 404);
        $category->loadCount(['questions' => fn ($q) => $q->where('is_active', true)]);
        $category->load('classification');

        $user = request()->user();
        if ($user) {
            $category = $this->playPool->decorateCategories(collect([$category]), $user)->first();
        }

        if ($user && ! $this->playAccess->canPlayCategory($user, (int) $category->id)) {
            return redirect()
                ->route('subscription.index')
                ->with('error', $this->playAccess->blockMessage($user));
        }

        return view('site.categories.show', [
            'category'         => $category,
            'categoryPlayMeta' => [
                'total'     => (int) ($category->questions_count ?? 0),
                'remaining' => (int) ($category->remaining_questions ?? $category->questions_count ?? 0),
            ],
        ]);
    }
}
