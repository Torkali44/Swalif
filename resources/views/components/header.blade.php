@php
  $onHome = request()->routeIs('home');
  $authUser = auth()->user();
  $isAdmin = $authUser ? (bool) ($authUser->is_admin ?? false) : false;
@endphp
<header class="nav">
  <style>
    .nav__actions {
      display: flex !important;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }
    .nav__actions-desktop {
      display: flex !important;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }
    .nav-auth-btns {
      display: inline-flex !important;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
      visibility: visible !important;
      opacity: 1 !important;
    }
    .nav-auth-btns .btn {
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      visibility: visible !important;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .nav__links {
      flex: 1 1 auto;
      min-width: 0;
      overflow-x: auto;
      scrollbar-width: none;
    }
    .nav__links::-webkit-scrollbar { display: none; }
    .nav__inner {
      overflow: visible !important;
    }

    @media (max-width: 900px) {
      .nav__links {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--surface);
        border-bottom: 1px solid var(--line);
        padding: 16px;
        box-shadow: 0 12px 24px rgba(0,0,0,.08);
        overflow: visible;
      }
      .nav__links.is-open { display: flex; }
      body.dark .nav__links { background: var(--bg); border-color: rgba(255,255,255,.06); }
      .nav-mobile-extra {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--line);
      }
      body.dark .nav-mobile-extra { border-color: rgba(255,255,255,.06); }
      .nav__actions-desktop,
      .nav-auth-btns { display: none !important; }
      .nav__toggle {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--line, #ECE6DE);
        background: #fff;
        font-size: 22px;
        line-height: 1;
        padding: 0;
        flex-shrink: 0;
      }
      .nav-icon-btn { width: 40px; height: 40px; flex-shrink: 0; }
    }
    @media (min-width: 901px) {
      .nav-mobile-extra { display: none; }
      .nav__toggle { display: none; }
      .nav__actions-desktop,
      .nav-auth-btns { display: flex !important; }
    }
  </style>

  <div class="nav__inner">
    <a href="{{ route('home') }}" class="nav__logo">
      <img
        src="{{ asset('images/mainLogo.jpg') }}"
        onerror="this.onerror=null;this.src='{{ asset('images/logo.jpg') }}'"
        alt="سوالف" class="logo-img" width="58" height="58" decoding="async" fetchpriority="high">
      <span class="logo-text">سوالف</span>
    </a>

    <nav class="nav__links" id="navLinks">
      <a href="{{ route('home') }}" @class(['is-active' => $onHome])>الرئيسية</a>
      <a href="{{ route('categories.index') }}" @class(['is-active' => request()->routeIs('categories.*')])>الألعاب</a>
      <a href="{{ route('custom-game.create') }}" @class(['is-active' => request()->routeIs('custom-game.*')])>أنشئ لعبتك الخاصة 🎮</a>
      <a href="{{ route('home') }}#plans">الاشتراكات</a>
      <a href="{{ route('home') }}#faq">المزيد</a>

      <div class="nav-mobile-extra">
        @auth
          <a href="{{ route('profile') }}" class="btn btn--ghost btn--sm">حسابي</a>
          @if($isAdmin)
            <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost btn--sm">الإدارة</a>
          @endif
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn--ghost btn--sm" type="submit" style="width: 100%; text-align: right;">خروج</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="btn btn--ghost btn--sm">تسجيل الدخول</a>
          <a href="{{ route('register') }}" class="btn btn--primary btn--sm">إنشاء حساب</a>
        @endauth
      </div>
    </nav>

    <div class="nav__actions">
      <a href="{{ route('categories.index') }}" class="nav-icon-btn" title="بحث" aria-label="بحث">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      </a>
      <button type="button" id="themeToggle" class="nav-icon-btn theme-toggle" title="تبديل المظهر" aria-label="تبديل المظهر">🌙</button>

      <div class="nav__actions-desktop">
        @auth
          <a href="{{ route('profile') }}" class="nav-user" title="حسابي">
            <x-user-avatar :user="$authUser" size="sm" class="nav-avatar" />
            <span class="nav-user__name">{{ $authUser->firstName() ?: 'حسابي' }}</span>
          </a>
          <div class="nav-auth-btns">
            @if($isAdmin)
              <a class="btn btn--ghost btn--sm" href="{{ route('admin.dashboard') }}">الإدارة</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="display:inline;margin:0">
              @csrf
              <button class="btn btn--ghost btn--sm" type="submit">خروج</button>
            </form>
          </div>
        @else
          <div class="nav-auth-btns">
            <a href="{{ route('login') }}" class="btn btn--ghost btn--sm">تسجيل الدخول</a>
            <a href="{{ route('register') }}" class="btn btn--primary btn--sm">إنشاء حساب</a>
          </div>
        @endauth
      </div>

      <button class="nav__toggle" type="button" aria-label="القائمة" id="navToggle">☰</button>
    </div>
  </div>
</header>
