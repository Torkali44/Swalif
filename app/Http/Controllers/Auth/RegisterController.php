<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'اكتب اسمك.',
            'email.required' => 'اكتب البريد الإلكتروني.',
            'email.email' => 'البريد الإلكتروني غير صحيح.',
            'email.unique' => 'هذا البريد مسجّل بالفعل.',
            'phone.required' => 'اكتب رقم الموبايل.',
            'password.required' => 'اكتب كلمة المرور.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'terms.accepted' => 'يجب الموافقة على الشروط والأحكام أولاً.',
        ]);

        $phone = $this->normalizePhone($data['phone']);
        if (strlen($phone) < 8) {
            return back()
                ->withErrors(['phone' => 'رقم الموبايل غير صحيح.'])
                ->withInput();
        }

        if (User::query()->where('phone', $phone)->exists()) {
            return back()
                ->withErrors(['phone' => 'هذا الرقم مسجّل بالفعل.'])
                ->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $phone,
            'phone_code' => $data['phone_code'] ?? '+971',
            'is_admin' => false,
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', 'تم إنشاء حسابك بنجاح. أهلًا بك!');
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }
}
