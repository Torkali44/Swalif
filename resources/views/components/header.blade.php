@php
  $onHome = request()->routeIs('home');
@endphp
<header class="nav">
  <div class="nav__inner">
    <a href="{{ route('home') }}" class="nav__logo">
      <img src="{{ asset('images/logo.png') }}" alt="سوالف" class="logo-img">
      <span class="logo-text">سوالف</span>
    </a>

    <nav class="nav__links" id="navLinks">
      <a href="{{ route('home') }}" @class(['is-active' => $onHome])>الرئيسية</a>
      <a href="{{ route('categories.index') }}" @class(['is-active' => request()->routeIs('categories.*')])>الألعاب</a>
      <a href="{{ route('home') }}#leaderboard">الترتيب</a>
      <a href="{{ route('home') }}#plans">الاشتراكات</a>
      <a href="{{ route('home') }}#faq">المزيد</a>
    </nav>

    <div class="nav__actions">
      <a href="{{ route('categories.index') }}" class="nav-icon-btn" title="بحث" aria-label="بحث">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      </a>
      <button type="button" id="themeToggle" class="nav-icon-btn theme-toggle" title="تبديل المظهر" aria-label="تبديل المظهر">🌙</button>

      @auth
        <a href="{{ route('profile') }}" class="nav-user" title="حسابي">
          @if(auth()->user()->avatarUrl())
            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="nav-avatar">
          @else
            <span class="nav-avatar-fallback">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
          @endif
          <span class="nav-user__name">{{ auth()->user()->firstName() ?: 'حسابي' }}</span>
        </a>
        @if(auth()->user()->is_admin)
          <a class="btn btn--ghost btn--sm" href="{{ route('admin.dashboard') }}">الإدارة</a>
        @endif
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button class="btn btn--ghost btn--sm" type="submit">خروج</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn btn--ghost btn--sm">تسجيل الدخول</a>
        <a href="{{ route('register') }}" class="btn btn--primary btn--sm">إنشاء حساب</a>
      @endauth

      <button class="nav__toggle" type="button" aria-label="القائمة" id="navToggle">☰</button>
    </div>
  </div>
</header>
