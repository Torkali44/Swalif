<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Subscription\PlayAccessService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private PlayAccessService $playAccess) {}

    public function index(Request $request)
    {
        $query = User::query()
            ->with(['subscriptions' => fn ($q) => $q->latest()->with('plan')])
            ->latest();

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('is_admin', $request->string('role') === 'admin');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        if ($request->filled('play')) {
            if ($request->string('play') === 'blocked') {
                $query->where('play_blocked', true);
            } elseif ($request->string('play') === 'open') {
                $query->where('play_blocked', false);
            }
        }

        if ($request->filled('subscription')) {
            if ($request->string('subscription') === 'active') {
                $query->whereHas('subscriptions', fn ($q) => $q->where('status', 'active')->where('ends_at', '>', now()));
            } elseif ($request->string('subscription') === 'none') {
                $query->whereDoesntHave('subscriptions', fn ($q) => $q->where('status', 'active')->where('ends_at', '>', now()));
            } elseif ($request->string('subscription') === 'expiring') {
                $query->whereHas('subscriptions', fn ($q) => $q
                    ->where('status', 'active')
                    ->whereBetween('ends_at', [now(), now()->addDays(7)]));
            }
        }

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'role', 'subscription', 'status', 'play']),
        ]);
    }

    public function toggleActive(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'لا يمكنك تعطيل حسابك الحالي.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'تم تفعيل المستخدم.' : 'تم إيقاف المستخدم.');
    }

    public function togglePlayBlock(User $user)
    {
        abort_if($user->is_admin, 403, 'لا يمكن قفل لعب حساب مدير.');
        abort_if($user->id === auth()->id(), 403, 'لا يمكنك قفل حسابك الحالي.');

        if ($user->play_blocked) {
            $this->playAccess->unblock($user);
            $message = 'تم فتح اللعب لهذا اللاعب.';
        } else {
            $this->playAccess->block($user, 'تم إيقاف اللعب من الإدارة. اشترك أو تواصل مع الإدارة.');
            $message = 'تم قفل اللعب لهذا اللاعب.';
        }

        return back()->with('success', $message);
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'لا يمكنك حذف حسابك الحالي.');
        abort_if($user->is_admin, 403, 'لا يمكن حذف حساب مدير.');

        $user->delete();

        return back()->with('success', 'تم حذف المستخدم بنجاح.');
    }
}
