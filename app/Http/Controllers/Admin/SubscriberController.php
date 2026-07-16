<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;

class SubscriberController extends Controller
{
    public function index()
    {
        $subscribers = Subscription::with(['user', 'plan'])
            ->latest()
            ->paginate(20);

        return view('admin.subscribers.index', compact('subscribers'));
    }
}
