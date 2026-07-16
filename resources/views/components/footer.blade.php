<footer class="foot">
  <div class="container foot__inner">
    <div>
      <div class="nav__logo">
        <span class="logo-badge">س</span>
        <span class="logo-text">سين جيم <em>الإمارات</em></span>
      </div>
      <p class="foot__tag">لعبة معلومات جماعية بهوية إماراتية أصيلة</p>
    </div>
    <div>
      <h4>روابط</h4>
      <a href="{{ route('categories.index') }}">الفئات</a>
      <a href="{{ route('subscription.index') }}">الاشتراكات</a>
      <a href="{{ route('home') }}#how">كيف تلعب</a>
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
      <a href="mailto:info@seenjeem.ae">info@seenjeem.ae</a>
    </div>
  </div>
  <div class="foot__bottom">© {{ now()->year }} سين جيم الإمارات — جميع الحقوق محفوظة</div>
</footer>
