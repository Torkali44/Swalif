<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $games = $request->user()
            ->games()
            ->with(['category', 'teams'])
            ->latest()
            ->paginate(12);

        return view('user.history', compact('games'));
    }
}
