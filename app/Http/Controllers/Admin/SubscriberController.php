<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->integer('plan_id'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->whereHas('user', function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return view('admin.subscribers.index', [
            'subscribers' => $query->paginate(20)->withQueryString(),
            'plans' => Plan::orderBy('sort_order')->get(['id', 'name']),
            'filters' => $request->only(['status', 'plan_id', 'q']),
        ]);
    }

    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'ends_at' => now(),
        ]);

        return back()->with('success', 'تم إلغاء الاشتراك.');
    }

    public function activate(Subscription $subscription)
    {
        $days = max(1, (int) ($subscription->plan?->duration_days ?? 30));

        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays($days),
        ]);

        return back()->with('success', 'تم تفعيل الاشتراك.');
    }

    public function extend(Subscription $subscription, Request $request)
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $base = $subscription->ends_at && $subscription->ends_at->isFuture()
            ? $subscription->ends_at
            : now();

        $subscription->update([
            'status' => 'active',
            'ends_at' => $base->copy()->addDays((int) $data['days']),
        ]);

        return back()->with('success', "تم تمديد الاشتراك {$data['days']} يوم.");
    }
}
