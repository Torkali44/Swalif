<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Services\Category\CategoryPlayPoolService;
use App\Services\Category\CategoryService;
use App\Services\Subscription\PlanService;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(
        private CategoryService $categories,
        private CategoryPlayPoolService $playPool,
        private PlanService $plans,
    ) {}

    public function index()
    {
        $categories = Cache::remember('home.active_categories', 120, fn () => $this->categories->activeOrdered());
        $categories = $this->playPool->decorateCategories($categories, request()->user());

        $heroCharacters = Cache::remember('home.hero_characters', 300, function () {
            return Character::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(4)
                ->get();
        });

        return view('site.home', [
            'categories' => $categories,
            'plans' => Cache::remember('home.active_plans', 120, fn () => $this->plans->activePlans()),
            'heroCharacters' => $heroCharacters,
        ]);
    }
}
