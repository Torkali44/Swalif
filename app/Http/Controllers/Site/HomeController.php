<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Category\CategoryService;
use App\Services\Subscription\PlanService;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(
        private CategoryService $categories,
        private PlanService $plans,
    ) {}

    public function index()
    {
        return view('site.home', [
            'categories' => Cache::remember('home.active_categories', 120, fn () => $this->categories->activeOrdered()),
            'plans' => Cache::remember('home.active_plans', 120, fn () => $this->plans->activePlans()),
        ]);
    }
}
