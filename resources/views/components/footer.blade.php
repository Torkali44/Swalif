<footer class="foot">
  <div class="container foot__inner">
    <div class="foot__brand">
      <a href="{{ route('home') }}" class="nav__logo">
        <img src="{{ asset('images/logo.jpg') }}" alt="سوالف" class="logo-img">
        <span class="logo-text">سوالف</span>
      </a>
      <p class="foot__tag">ألعاب • ضحك • فرفشة — بهوية إماراتية</p>
    </div>
    <div>
      <h4>روابط</h4>
      <a href="{{ route('categories.index') }}">الفئات</a>
      <a href="{{ route('subscription.index') }}">الاشتراكات</a>
      <a href="{{ route('home') }}#how">كيف تلعب</a>
      <a href="{{ route('home') }}#faq">الأسئلة الشائعة</a>
    </div>
    <div>
      <h4>الحساب</h4>
      @auth
        <a href="{{ route('profile') }}">الملف الشخصي</a>
        <a href="{{ route('history') }}">سجل الألعاب</a>
      @else
        <a href="{{ route('login') }}">تسجيل الدخول</a>
        <a href="{{ route('register') }}">إنشاء حساب</a>
      @endauth
    </div>
    <div>
      <h4>تواصل</h4>
      <a href="mailto:info@swalif.ae">info@swalif.ae</a>
    </div>
  </div>
  <div class="foot__bottom">© {{ now()->year }} سوالف — جميع الحقوق محفوظة</div>
</footer>
