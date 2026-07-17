<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>إدارة سوالف</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script>
    (function () {
      try {
        if (localStorage.getItem('theme') === 'dark') {
          document.documentElement.classList.add('dark');
        }
      } catch (e) {}
    })();
  </script>
</head>
<body class="admin-body">
<script>
  if (document.documentElement.classList.contains('dark')) {
    document.body.classList.add('dark');
  }
</script>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a class="brand" href="{{ route('admin.dashboard') }}">
      <img src="{{ asset('images/logo.jpg') }}" alt="سوالف" class="brand-logo">
      <div>
        <div class="brand-title">سوالف</div>
        <div class="brand-sub">لوحة التحكم</div>
      </div>
    </a>

    <nav class="admin-nav">
      <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span class="ico">📊</span> نظرة عامة
      </a>
      <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <span class="ico">🗂️</span> الفئات
      </a>
      <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
        <span class="ico">❓</span> الأسئلة
      </a>
      <a href="{{ route('admin.plans.index') }}" class="nav-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
        <span class="ico">💎</span> الاشتراكات
      </a>
      <a href="{{ route('admin.subscribers.index') }}" class="nav-link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}">
        <span class="ico">💳</span> المشتركين
      </a>
      <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <span class="ico">👥</span> المستخدمون
      </a>
    </nav>

    <div class="admin-footer">
      <div class="user-chip">
        @if(auth()->user()->avatarUrl())
          <img class="avatar avatar-img" src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}">
        @else
          <div class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
        @endif
        <div>
          <div class="u-name">{{ auth()->user()->name }}</div>
          <div class="u-role">مشرف عام</div>
        </div>
      </div>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-topbar">
      <div>
        <h1 class="page-title">{{ $heading ?? 'لوحة التحكم' }}</h1>
        <p class="page-sub">{{ $subheading ?? 'إدارة محتوى سوالف' }}</p>
      </div>
      <div class="top-actions">
        <button type="button" id="themeToggle" class="nav-theme-btn theme-toggle" title="تبديل المظهر" aria-label="تبديل المظهر">🌙</button>
        <a class="btn btn-outline" href="{{ route('home') }}">عرض الموقع</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button class="btn btn-primary" type="submit">خروج</button>
        </form>
      </div>
    </header>

    @if(session('success'))
      <div class="flash">{{ session('success') }}</div>
    @endif

    {{ $slot }}
  </main>
</div>
</body>
</html>
