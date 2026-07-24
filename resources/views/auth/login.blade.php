<x-layouts.app>
<section class="auth-wrap">
  <form class="auth-card" method="POST" action="{{ route('login.store') }}">
    @csrf
    <h1>تسجيل الدخول</h1>
    <p>ادخل بحسابك عشان تكمل اللعب.</p>

    <label>البريد الإلكتروني
      <input name="email" type="email" value="{{ old('email') }}" required autofocus>
    </label>
    <label>كلمة المرور
      <input name="password" type="password" required>
    </label>
    <label class="check">
      <input type="checkbox" name="remember"> تذكرني
    </label>
    @error('email')<small class="error">{{ $message }}</small>@enderror

    <button class="btn btn--primary btn--block" type="submit">دخول</button>

    <p style="margin-top:16px;text-align:center">
      مستخدم جديد؟
      <a href="{{ route('register') }}" style="color:var(--uae-red);font-weight:800">إنشاء حساب</a>
    </p>
  </form>
</section>
</x-layouts.app>
