<header class="nav">
  <div class="nav__inner">
    <a href="{{ route('home') }}" class="nav__logo">
      <span class="logo-badge">س</span>
      <span class="logo-text">سين جيم <em>الإمارات</em></span>
    </a>

    <nav class="nav__links" id="navLinks">
      <a href="{{ route('categories.index') }}">الفئات</a>
      <a href="{{ route('home') }}#how">كيف تلعب</a>
      <a href="{{ route('subscription.index') }}">الاشتراكات</a>
      @auth
        <a href="{{ route('history') }}">ألعابي</a>
        @if(auth()->user()->is_admin)
          <a href="{{ route('admin.dashboard') }}">الإدارة</a>
        @endif
        <a href="{{ route('profile') }}" class="btn btn--ghost">حسابي</a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button class="btn btn--outline" type="submit">خروج</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn btn--ghost">دخول</a>
        <a href="{{ route('categories.index') }}" class="btn btn--primary">ابدأ اللعب</a>
      @endauth
    </nav>

    <button class="nav__toggle" type="button" aria-label="القائمة" id="navToggle">☰</button>
  </div>
</header>
