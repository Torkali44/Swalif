<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>إدارة سين جيم</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a class="brand" href="{{ route('admin.dashboard') }}">
      <div class="brand-badge">س</div>
      <div>
        <div class="brand-title">سين جيم</div>
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
        <div class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
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
        <p class="page-sub">{{ $subheading ?? 'إدارة محتوى سين جيم' }}</p>
      </div>
      <div class="top-actions">
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
