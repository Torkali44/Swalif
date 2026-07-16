<x-layouts.app>
<section class="auth-wrap">
  <form class="auth-card" method="POST" action="{{ route('register.store') }}">
    @csrf
    <h1>انضم إلى سين جيم</h1>
    <p>أنشئ حسابك وابدأ أول تحدٍ.</p>
    <label>الاسم<input name="name" value="{{ old('name') }}" required></label>
    <label>البريد الإلكتروني<input name="email" type="email" value="{{ old('email') }}" required></label>
    <label>كلمة المرور<input name="password" type="password" required></label>
    <label>تأكيد كلمة المرور<input name="password_confirmation" type="password" required></label>
    @if($errors->any())<small class="error">{{ $errors->first() }}</small>@endif
    <button class="btn btn--primary btn--block" type="submit">إنشاء الحساب</button>
    <p style="margin-top:14px">لديك حساب؟ <a href="{{ route('login') }}" style="color:var(--uae-red);font-weight:800">سجّل دخولك</a></p>
  </form>
</section>
</x-layouts.app>
