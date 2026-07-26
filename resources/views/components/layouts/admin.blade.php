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
  <style>
    /* Critical mobile fix for admin cards (before/without cache) */
    @media (max-width: 900px) {
      .admin-circle-grid,
      .admin-circle-grid--compact {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 14px !important;
        width: 100% !important;
      }
      .admin-circle-card {
        width: 100% !important;
        max-width: 100% !important;
        min-height: 0 !important;
      }
      .admin-circle-card .cat-actions {
        flex-direction: column !important;
        width: 100% !important;
      }
      .admin-circle-card .cat-actions .btn,
      .admin-circle-card .cat-actions a.btn,
      .admin-circle-card .cat-actions form {
        width: 100% !important;
      }
      .admin-circle-card .cat-circle__name {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: unset !important;
      }
    }
  </style>
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
<div class="admin-overlay" id="adminOverlay" hidden></div>
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-head">
      <a class="brand" href="{{ route('admin.dashboard') }}">
        <img src="{{ asset(file_exists(public_path('images/logo-nav.jpg')) ? 'images/logo-nav.jpg' : 'images/logo.jpg') }}" alt="سوالف" class="brand-logo" width="48" height="48" decoding="async">
        <div>
          <div class="brand-title">سوالف</div>
          <div class="brand-sub">لوحة التحكم</div>
        </div>
      </a>
      <button type="button" class="admin-sidebar__close" id="adminSidebarClose" aria-label="إغلاق القائمة">×</button>
    </div>

    <nav class="admin-nav">
      <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span class="ico">📊</span> نظرة عامة
      </a>
      <a href="{{ route('admin.classifications.index') }}" class="nav-link {{ request()->routeIs('admin.classifications.*') ? 'active' : '' }}">
        <span class="ico">🏷️</span> التصنيفات
      </a>
      <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <span class="ico">🗂️</span> الفئات
      </a>
      <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
        <span class="ico">❓</span> أنواع الأسئلة
      </a>
      <a href="{{ route('admin.plans.index') }}" class="nav-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
        <span class="ico">💎</span> الاشتراكات
      </a>
      <a href="{{ route('admin.subscribers.index') }}" class="nav-link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}">
        <span class="ico">💳</span> المشتركين
      </a>
      <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
        <span class="ico">🧾</span> المدفوعات
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
      <button type="button" class="admin-menu-btn" id="adminMenuBtn" aria-label="فتح القائمة" aria-controls="adminSidebar">☰</button>
      <div class="admin-topbar__title">
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

    {{ $slot }}
  </main>
</div>
<x-toast />
<script>
/* Compress images in the browser before upload — faster admin saves on shared hosting */
(() => {
  const MAX = 1400;
  const QUALITY = 0.78;

  async function compressFile(file) {
    if (!file || !file.type.startsWith('image/') || file.type === 'image/gif' || file.type === 'image/svg+xml') {
      return file;
    }
    if (file.size < 350 * 1024) return file;

    const bitmap = await createImageBitmap(file);
    let w = bitmap.width;
    let h = bitmap.height;
    if (w > MAX) {
      h = Math.round(h * (MAX / w));
      w = MAX;
    }
    const canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, w, h);
    ctx.drawImage(bitmap, 0, 0, w, h);
    bitmap.close?.();

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', QUALITY));
    if (!blob || blob.size >= file.size) return file;

    const name = file.name.replace(/\.\w+$/, '') + '.jpg';
    return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
  }

  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      if (form.dataset.compressDone === '1') return;
      const inputs = [...form.querySelectorAll('input[type="file"]')].filter((i) => i.files?.length);
      if (!inputs.length) return;

      e.preventDefault();
      const btn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.dataset.oldText = btn.textContent;
        btn.textContent = 'جاري رفع الملف...';
      }

      try {
        for (const input of inputs) {
          const dt = new DataTransfer();
          for (const file of input.files) {
            dt.items.add(await compressFile(file));
          }
          input.files = dt.files;
        }
        form.dataset.compressDone = '1';
        if (typeof form.requestSubmit === 'function') form.requestSubmit();
        else form.submit();
      } catch (err) {
        console.error(err);
        form.dataset.compressDone = '1';
        form.submit();
      }
    });
  });
})();
</script>
</body>
</html>
