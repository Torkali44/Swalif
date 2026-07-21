<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Classification;
use App\Models\Question;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => [
                'categories' => Category::count(),
                'classifications' => Classification::count(),
                'questions' => Question::count(),
                'users' => User::where('is_admin', false)->count(),
                'admins' => User::where('is_admin', true)->count(),
                'plans' => Plan::count(),
                'recommended_plans' => Plan::where('is_recommended', true)->count(),
                'subscribers' => Subscription::where('status', 'active')->where('ends_at', '>', now())->count(),
                'expiring_soon' => Subscription::where('status', 'active')
                    ->whereBetween('ends_at', [now(), now()->addDays(7)])
                    ->count(),
            ],
            'recent' => Question::with('category')->latest()->take(8)->get(),
            'activePlans' => Plan::where('is_active', true)->orderBy('sort_order')->take(6)->get(),
        ]);
    }
}
