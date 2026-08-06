<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'اكتب البريد الإلكتروني.',
            'password.required' => 'اكتب كلمة المرور.',
        ]);

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            \Illuminate\Support\Facades\Log::warning('[Auth] Failed login attempt', [
                'email' => $data['email'],
                'ip'    => $request->ip(),
            ]);

            return back()
                ->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])
                ->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            \Illuminate\Support\Facades\Log::warning('[Auth] Disabled user attempted login', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'تم إيقاف هذا الحساب.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        \Illuminate\Support\Facades\Log::info('[Auth] User logged in successfully', [
            'user_id'  => Auth::id(),
            'is_admin' => Auth::user()->is_admin,
            'ip'       => $request->ip(),
        ]);

        return redirect()->intended(
            Auth::user()->is_admin ? route('admin.dashboard') : route('home')
        );
    }

    /** @deprecated use login form */
    public function adminCreate()
    {
        return redirect()->route('login');
    }

    /** @deprecated use login.store */
    public function adminStore(Request $request)
    {
        return $this->store($request);
    }

    public function destroy(Request $request)
    {
        $userId = Auth::id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        \Illuminate\Support\Facades\Log::info('[Auth] User logged out', [
            'user_id' => $userId,
            'ip'      => $request->ip(),
        ]);

        return redirect()->route('home');
    }
}
