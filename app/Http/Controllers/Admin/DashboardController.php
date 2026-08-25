<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Classification;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $expiringSubscriptions = Subscription::query()
            ->with(['user', 'plan'])
            ->where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->orderBy('ends_at')
            ->take(12)
            ->get();

        $pendingPayments = Payment::query()
            ->with(['user'])
            ->whereIn('status', ['pending', 'waiting_review'])
            ->latest()
            ->take(10)
            ->get();

        $playBlockedCount = User::query()
            ->where('is_admin', false)
            ->where('play_blocked', true)
            ->count();

        // Batch all simple counts into a single SELECT to reduce round-trips
        $counts = \Illuminate\Support\Facades\DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM categories)                                                              AS categories,
                (SELECT COUNT(*) FROM classifications)                                                        AS classifications,
                (SELECT COUNT(*) FROM questions)                                                               AS questions,
                (SELECT COUNT(*) FROM users WHERE is_admin = 0)                                               AS users,
                (SELECT COUNT(*) FROM users WHERE is_admin = 1)                                               AS admins,
                (SELECT COUNT(*) FROM plans)                                                                   AS plans,
                (SELECT COUNT(*) FROM plans WHERE is_recommended = 1)                                         AS recommended_plans,
                (SELECT COUNT(*) FROM subscriptions WHERE status = 'active' AND ends_at > datetime('now'))   AS subscribers,
                (SELECT COUNT(*) FROM payments WHERE status = 'waiting_review')                               AS waiting_payments,
                (SELECT COUNT(*) FROM payments WHERE status = 'pending')                                      AS pending_payments
        ");

        return view('admin.dashboard', [
            'stats' => [
                'categories'        => (int) $counts->categories,
                'classifications'   => (int) $counts->classifications,
                'questions'         => (int) $counts->questions,
                'users'             => (int) $counts->users,
                'admins'            => (int) $counts->admins,
                'plans'             => (int) $counts->plans,
                'recommended_plans' => (int) $counts->recommended_plans,
                'subscribers'       => (int) $counts->subscribers,
                // Reuse already-fetched collection for expiring_soon (no extra query)
                'expiring_soon'     => $expiringSubscriptions->count(),
                'play_blocked'      => $playBlockedCount,
                'waiting_payments'  => (int) $counts->waiting_payments,
                'pending_payments'  => (int) $counts->pending_payments,
            ],
            'expiringSubscriptions' => $expiringSubscriptions,
            'pendingPayments'       => $pendingPayments,
            'recent'                => Question::with('category')->latest()->take(8)->get(),
            'activePlans'           => Plan::where('is_active', true)->orderBy('sort_order')->take(6)->get(),
        ]);
    }

}
