<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Category\CategoryService;
use App\Services\Subscription\PlanService;

class HomeController extends Controller
{
    public function __construct(
        private CategoryService $categories,
        private PlanService $plans,
    ) {}

    public function index()
    {
        return view('site.home', [
            'categories' => $this->categories->activeOrdered(),
            'plans' => $this->plans->activePlans(),
        ]);
    }
}
