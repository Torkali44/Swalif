<x-layouts.app>
<section class="auth-wrap">
  <form class="auth-card" method="POST" action="{{ route('register.store') }}" id="registerForm">
    @csrf
    <h1>إنشاء حساب</h1>
    <p>سجّل بالإيميل ورقم الموبايل عشان تبدأ اللعب.</p>

    <label>الاسم
      <input name="name" value="{{ old('name') }}" placeholder="اسمك" required autofocus>
    </label>

    <label>البريد الإلكتروني
      <input name="email" type="email" value="{{ old('email') }}" placeholder="you@email.com" required dir="ltr">
    </label>

    <label>رمز الدولة
      <select name="phone_code">
        <option value="+971" @selected(old('phone_code', '+971') === '+971')>+971 الإمارات</option>
        <option value="+966" @selected(old('phone_code') === '+966')>+966 السعودية</option>
        <option value="+20" @selected(old('phone_code') === '+20')>+20 مصر</option>
      </select>
    </label>

    <label>رقم الموبايل
      <input name="phone" type="tel" inputmode="numeric" value="{{ old('phone') }}" placeholder="5XXXXXXXX" required dir="ltr">
    </label>

    <label>كلمة المرور
      <input name="password" type="password" required>
    </label>

    <label>تأكيد كلمة المرور
      <input name="password_confirmation" type="password" required>
    </label>

    <label class="check terms-check">
      <input type="checkbox" name="terms" value="1" id="termsCheck" @checked(old('terms')) required>
      <span>
        أوافق على
        <button type="button" class="terms-link" id="openTerms">الشروط والأحكام</button>
      </span>
    </label>

    @error('name')<small class="error">{{ $message }}</small>@enderror
    @error('email')<small class="error">{{ $message }}</small>@enderror
    @error('phone')<small class="error">{{ $message }}</small>@enderror
    @error('password')<small class="error">{{ $message }}</small>@enderror
    @error('terms')<small class="error">{{ $message }}</small>@enderror

    <button class="btn btn--primary btn--block" type="submit">إنشاء الحساب</button>
    <p style="margin-top:14px;text-align:center">
      لديك حساب؟
      <a href="{{ route('login') }}" style="color:var(--uae-red);font-weight:800">سجّل دخولك</a>
    </p>
  </form>
</section>

<div class="terms-modal" id="termsModal" hidden>
  <div class="terms-modal__backdrop" data-close-terms></div>
  <div class="terms-modal__panel" role="dialog" aria-modal="true" aria-labelledby="termsTitle">
    <div class="terms-modal__head">
      <h2 id="termsTitle">الشروط والأحكام</h2>
      <button type="button" class="terms-modal__close" data-close-terms aria-label="إغلاق">×</button>
    </div>
    <div class="terms-modal__body">
      <p>مرحباً بك في منصة <strong>سوالف</strong>. باستخدامك للمنصة فإنك توافق على الشروط التالية:</p>
      <ol>
        <li>الحساب شخصي، ومسؤوليتك الحفاظ على سرية بيانات الدخول.</li>
        <li>يُمنع استخدام المنصة لأي غرض مخالف للقوانين أو مسيء للمستخدمين الآخرين.</li>
        <li>المحتوى والأسئلة والتصاميم ملك للمنصة، ولا يجوز نسخها أو إعادة نشرها دون إذن.</li>
        <li>الاشتراكات تُفعَّل بعد تأكيد الدفع، وتنتهي في التاريخ المحدد في حسابك.</li>
        <li>الفئة المجانية متاحة مرة واحدة للمستخدم الجديد وفق سياسة المنصة.</li>
        <li>يحق للإدارة إيقاف أو حذف أي حساب يخالف الشروط أو يسيء استخدام اللعب.</li>
        <li>قد نحدّث هذه الشروط من وقت لآخر، ويستمر استخدامك للمنصة بعد التحديث موافقةً عليها.</li>
      </ol>
      <p>للاستفسارات تواصل مع فريق سوالف عبر قنوات الدعم الرسمية.</p>
    </div>
    <div class="terms-modal__foot">
      <button type="button" class="btn btn--primary" id="acceptTerms">موافق</button>
    </div>
  </div>
</div>

<style>
.terms-check{align-items:flex-start !important;gap:10px}
.terms-link{
  background:none;border:0;padding:0;margin:0;color:var(--uae-red,#C8102E);
  font:inherit;font-weight:800;cursor:pointer;text-decoration:underline;
}
.terms-modal{
  position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:18px;
}
.terms-modal[hidden]{display:none !important}
.terms-modal__backdrop{position:absolute;inset:0;background:rgba(10,14,28,.55)}
.terms-modal__panel{
  position:relative;width:min(560px,100%);max-height:min(80vh,720px);
  background:#fff;border-radius:20px;box-shadow:0 24px 60px rgba(0,0,0,.28);
  display:flex;flex-direction:column;overflow:hidden;
}
body.dark .terms-modal__panel{background:#151B32;color:#F5F7FF}
.terms-modal__head,.terms-modal__foot{
  display:flex;align-items:center;justify-content:space-between;gap:12px;
  padding:16px 18px;border-bottom:1px solid rgba(0,0,0,.08);
}
.terms-modal__foot{border-bottom:0;border-top:1px solid rgba(0,0,0,.08);justify-content:flex-end}
body.dark .terms-modal__head,body.dark .terms-modal__foot{border-color:rgba(255,255,255,.1)}
.terms-modal__head h2{margin:0;font-size:20px;font-weight:900}
.terms-modal__close{
  width:36px;height:36px;border:0;border-radius:10px;background:rgba(0,0,0,.06);
  font-size:22px;cursor:pointer;line-height:1;color:inherit;
}
body.dark .terms-modal__close{background:rgba(255,255,255,.08)}
.terms-modal__body{padding:16px 18px;overflow:auto;line-height:1.8;font-size:14px}
.terms-modal__body ol{padding-inline-start:22px;margin:12px 0}
.terms-modal__body li{margin-bottom:8px}
</style>

<script>
(() => {
  const modal = document.getElementById('termsModal');
  const openBtn = document.getElementById('openTerms');
  const acceptBtn = document.getElementById('acceptTerms');
  const check = document.getElementById('termsCheck');
  if (!modal || !openBtn || !acceptBtn || !check) return;

  const open = () => { modal.hidden = false; document.body.style.overflow = 'hidden'; };
  const close = () => { modal.hidden = true; document.body.style.overflow = ''; };

  openBtn.addEventListener('click', open);
  acceptBtn.addEventListener('click', () => { check.checked = true; close(); });
  modal.querySelectorAll('[data-close-terms]').forEach((el) => el.addEventListener('click', close));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.hidden) close(); });
})();
</script>
</x-layouts.app>
