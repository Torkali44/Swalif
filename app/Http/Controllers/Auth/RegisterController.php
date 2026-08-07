<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $identifier = trim((string) $request->input('identifier', ''));

        // Detect whether identifier is email or phone
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        // Build dynamic validation rules
        $identifierRules = $isEmail
            ? ['required', 'email', 'max:255', 'unique:users,email']
            : ['required', 'string', 'min:6', 'max:20'];

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'identifier' => $identifierRules,
            'password'   => ['required', Password::defaults()],
            'terms'      => ['accepted'],
        ], [
            'name.required'       => 'اكتب اسمك.',
            'identifier.required' => 'اكتب بريدك الإلكتروني أو رقم جوالك.',
            'identifier.email'    => 'البريد الإلكتروني غير صحيح.',
            'identifier.unique'   => 'هذا البريد مسجّل بالفعل.',
            'identifier.min'      => 'الرقم قصير جداً.',
            'password.required'   => 'اكتب كلمة المرور.',
            'terms.accepted'      => 'يجب الموافقة على الشروط والأحكام أولاً.',
        ]);

        if ($isEmail) {
            // Email registration — phone is optional placeholder
            $email = strtolower($identifier);
            $phone = null;

            if (User::where('email', $email)->exists()) {
                return back()
                    ->withErrors(['identifier' => 'هذا البريد مسجّل بالفعل.'])
                    ->withInput();
            }
        } else {
            // Phone registration
            $email = null;
            $phone = $this->normalizePhone($identifier);

            if (strlen($phone) < 6) {
                return back()
                    ->withErrors(['identifier' => 'رقم الجوال غير صحيح.'])
                    ->withInput();
            }

            if (User::where('phone', $phone)->exists()) {
                return back()
                    ->withErrors(['identifier' => 'هذا الرقم مسجّل بالفعل.'])
                    ->withInput();
            }

            // Generate a unique placeholder email so the DB unique constraint is satisfied
            $email = 'phone_' . $phone . '@swalif.local';
        }

        $user = new User([
            'name'       => $data['name'],
            'email'      => $email,
            'password'   => $data['password'],
            'phone'      => $phone,
            'phone_code' => $request->input('phone_code', '+971'),
        ]);
        $user->forceFill([
            'is_admin'  => false,
            'is_active' => true,
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        \Illuminate\Support\Facades\Log::info('[Auth] New user registered', [
            'user_id'    => $user->id,
            'identifier' => $isEmail ? $email : ('phone:' . $phone),
            'ip'         => $request->ip(),
        ]);

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
