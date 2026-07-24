<x-layouts.app>
<section class="auth-wrap">
  <form class="auth-card" method="POST" action="{{ route('login.admin.store') }}">
    @csrf
    <h1>دخول المدراء</h1>
    <p>تسجيل الدخول بالبريد وكلمة المرور.</p>
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
    <button class="btn btn--primary btn--block" type="submit">دخول لوحة التحكم</button>
    <p style="margin-top:14px"><a href="{{ route('login') }}" style="color:var(--uae-red);font-weight:800">العودة لدخول الموبايل</a></p>
  </form>
</section>
</x-layouts.app>
