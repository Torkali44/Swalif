<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['subscriptions' => fn ($q) => $q->latest()->with('plan')]);

        return view('user.profile', compact('user'));
    }
}
