<x-layouts.app>
<section class="auth-wrap">
  <form class="auth-card" method="POST" action="{{ route('login.store') }}">
    @csrf
    <h1>مرحبًا بعودتك</h1>
    <p>سجّل دخولك للعب ومتابعة تحدياتك.</p>
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
    <button class="btn btn--primary btn--block" type="submit">تسجيل الدخول</button>
    <p style="margin-top:14px">ليس لديك حساب؟ <a href="{{ route('register') }}" style="color:var(--uae-red);font-weight:800">أنشئ حسابًا</a></p>
    @if(app()->isLocal())
      <p class="demo">تجريبي: player@swalif.test / password<br>مدير: admin@swalif.test / password</p>
    @endif
  </form>
</section>
</x-layouts.app>
