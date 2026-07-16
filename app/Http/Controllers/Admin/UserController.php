<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with(['subscriptions' => fn ($q) => $q->where('status', 'active')->latest()])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }
}
