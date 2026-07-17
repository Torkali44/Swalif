<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with(['subscriptions' => fn ($q) => $q->latest()->with('plan')])
            ->latest();

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('is_admin', $request->string('role') === 'admin');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        if ($request->filled('subscription')) {
            if ($request->string('subscription') === 'active') {
                $query->whereHas('subscriptions', fn ($q) => $q->where('status', 'active')->where('ends_at', '>', now()));
            } elseif ($request->string('subscription') === 'none') {
                $query->whereDoesntHave('subscriptions', fn ($q) => $q->where('status', 'active')->where('ends_at', '>', now()));
            }
        }

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'role', 'subscription', 'status']),
        ]);
    }

    public function toggleActive(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'لا يمكنك تعطيل حسابك الحالي.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'تم تفعيل المستخدم.' : 'تم إيقاف المستخدم.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'لا يمكنك حذف حسابك الحالي.');

        $user->delete();

        return back()->with('success', 'تم حذف المستخدم.');
    }
}
