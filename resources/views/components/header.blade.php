<header class="nav">
  <div class="nav__inner">
    <a href="{{ route('home') }}" class="nav__logo">
      <img src="{{ asset('images/logo.jpg') }}" alt="سوالف" class="logo-img">
      <span class="logo-text">سوالف</span>
    </a>

    <nav class="nav__links" id="navLinks">
      <a href="{{ route('categories.index') }}" @class(['is-active' => request()->routeIs('categories.*')])>الفئات</a>
      <a href="{{ route('home') }}#how">كيف تلعب</a>
      <a href="{{ route('subscription.index') }}">الاشتراكات</a>
      <a href="{{ route('home') }}#faq">الأسئلة الشائعة</a>
      @auth
        <a href="{{ route('history') }}">ألعابي</a>
        @if(auth()->user()->is_admin)
          <a href="{{ route('admin.dashboard') }}">الإدارة</a>
        @endif
      @endauth
    </nav>

    <div class="nav__actions" style="display:flex;align-items:center;gap:8px">
      <button type="button" id="themeToggle" class="nav-theme-btn" title="تبديل المظهر" aria-label="تبديل المظهر">🌙</button>
      @auth
        <a href="{{ route('profile') }}" class="nav-user" title="حسابي">
          @if(auth()->user()->avatarUrl())
            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="nav-avatar">
          @else
            <span class="nav-avatar-fallback">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
          @endif
          <span class="nav-user__name" style="font-size:14px">{{ auth()->user()->firstName() ?: 'حسابي' }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button class="btn btn--outline" type="submit" style="padding:8px 12px">خروج</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn btn--ghost">دخول</a>
        <a href="{{ route('categories.index') }}" class="btn btn--primary">ابدأ اللعب</a>
      @endauth
      <button class="nav__toggle" type="button" aria-label="القائمة" id="navToggle">☰</button>
    </div>
  </div>
</header>
