<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => [
                'categories' => Category::count(),
                'questions' => Question::count(),
                'users' => User::where('is_admin', false)->count(),
                'subscribers' => Subscription::where('status', 'active')->where('ends_at', '>', now())->count(),
            ],
            'recent' => Question::with('category')->latest()->take(8)->get(),
        ]);
    }
}
