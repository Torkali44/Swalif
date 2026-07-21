<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SubscriberController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions)
    {
    }

    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'plan'])->latest();

        if ($request->filled('status')) {
            match ((string) $request->string('status')) {
                'active' => $query->where('status', 'active')->where('ends_at', '>', now()),
                'expired' => $query->where(function ($builder) {
                    $builder->where('status', 'expired')
                        ->orWhere(function ($inner) {
                            $inner->where('status', 'active')->where('ends_at', '<=', now());
                        });
                }),
                default => $query->where('status', (string) $request->string('status')),
            };
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

    public function create()
    {
        return view('admin.subscribers.form', [
            'subscription' => new Subscription([
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
                'status' => 'active',
            ]),
            'users' => $this->usersForSelect(),
            'plans' => $this->plansForSelect(),
        ]);
    }

    public function store(Request $request)
    {
        $this->saveSubscription($request);

        return redirect()->route('admin.subscribers.index')->with('success', 'تم منح الاشتراك.');
    }

    public function edit(Subscription $subscription)
    {
        return view('admin.subscribers.form', [
            'subscription' => $subscription->load(['user', 'plan']),
            'users' => $this->usersForSelect(),
            'plans' => $this->plansForSelect(),
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $this->saveSubscription($request, $subscription);

        return redirect()->route('admin.subscribers.index')->with('success', 'تم تحديث الاشتراك.');
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

        $this->subscriptions->saveForAdmin(
            $subscription->user,
            $subscription->plan,
            now(),
            now()->addDays($days),
            'active',
            $subscription
        );

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

        $this->subscriptions->saveForAdmin(
            $subscription->user,
            $subscription->plan,
            $subscription->starts_at ?? now(),
            $base->copy()->addDays((int) $data['days']),
            'active',
            $subscription
        );

        return back()->with('success', "تم تمديد الاشتراك {$data['days']} يوم.");
    }

    private function saveSubscription(Request $request, ?Subscription $subscription = null): Subscription
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['required', Rule::in(['active', 'cancelled', 'expired'])],
        ]);

        return $this->subscriptions->saveForAdmin(
            User::findOrFail($data['user_id']),
            Plan::findOrFail($data['plan_id']),
            Carbon::parse($data['starts_at']),
            Carbon::parse($data['ends_at']),
            $data['status'],
            $subscription
        );
    }

    private function usersForSelect()
    {
        return User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function plansForSelect()
    {
        return Plan::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
