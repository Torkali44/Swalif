<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $games = $user
            ->games()
            ->with(['category', 'teams'])
            ->latest()
            ->paginate(10, ['*'], 'page');

        $customGames = $user
            ->customGames()
            ->with(['categories', 'teams'])
            ->latest()
            ->paginate(10, ['*'], 'custom_page');

        return view('user.history', compact('games', 'customGames'));
    }
}
