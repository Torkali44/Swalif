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
            return back()
                ->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])
                ->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'تم إيقاف هذا الحساب.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
