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
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        width: 100% !important;
        gap: 6px !important;
      }
      .admin-circle-card .cat-actions .btn,
      .admin-circle-card .cat-actions a.btn,
      .admin-circle-card .cat-actions form {
        width: auto !important;
        flex: 1 1 0 !important;
        min-width: 0 !important;
      }
      .admin-circle-card .cat-actions form .btn {
        width: 100% !important;
        font-size: 11px !important;
        padding: 6px 4px !important;
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
        <img src="{{ asset('images/mainLogo.jpg') }}" alt="سوالف" class="brand-logo" width="52" height="52" decoding="async">
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
      <a href="{{ route('admin.characters.index') }}" class="nav-link {{ request()->routeIs('admin.characters.*') ? 'active' : '' }}">
        <span class="ico">🧑‍🎤</span> الشخصيات
      </a>
      <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <span class="ico">🗂️</span> الفئات
      </a>
      <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
        <span class="ico">❓</span> أنواع الأسئلة
      </a>
      <a href="{{ route('admin.letter-grids.index') }}" class="nav-link {{ request()->routeIs('admin.letter-grids.*') ? 'active' : '' }}">
        <span class="ico">⬡</span> شبكة الحروف
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
<style>
  .upload-status {
    margin-top: 8px;
    font-weight: 700;
    font-size: .92rem;
    color: var(--muted, #6C7799);
  }
  .upload-status.is-progress { color: #c45c00; }
  .upload-status.is-done { color: #1a7f37; }
  .upload-status.is-error { color: #C8102E; }
  .upload-status__bar {
    margin-top: 6px;
    height: 6px;
    border-radius: 999px;
    background: rgba(0,0,0,.08);
    overflow: hidden;
  }
  .upload-status__bar > span {
    display: block;
    height: 100%;
    width: 0;
    background: #ff6d00;
    transition: width .2s linear;
  }
  /* Async image/video preview */
  .async-preview-wrap {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .async-preview-wrap img,
  .async-preview-wrap video {
    max-width: 100%;
    max-height: 220px;
    border-radius: 10px;
    object-fit: contain;
    background: rgba(0,0,0,.04);
    display: block;
  }
  .async-preview-wrap audio {
    width: 100%;
    margin-top: 4px;
  }
  .btn-remove-preview {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 8px;
    border: 1.5px solid #C8102E;
    background: rgba(200,16,46,.08);
    color: #C8102E;
    font-weight: 700;
    font-size: .88rem;
    cursor: pointer;
    transition: background .15s, transform .1s;
    align-self: flex-start;
  }
  .btn-remove-preview:hover {
    background: rgba(200,16,46,.18);
    transform: scale(1.03);
  }
  .btn-remove-preview:active { transform: scale(.97); }
</style>
<script>
/* Async pre-upload: instant preview, remove button, strict double-submit guard */
(() => {
  const MAX = 1200;
  const QUALITY = 0.72;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  /* ── Image compression ── */
  async function compressFile(file) {
    if (!file || !file.type.startsWith('image/') || file.type === 'image/gif' || file.type === 'image/svg+xml') return file;
    if (file.size < 250 * 1024) return file;
    const bitmap = await createImageBitmap(file);
    let w = bitmap.width, h = bitmap.height;
    if (w > MAX) { h = Math.round(h * (MAX / w)); w = MAX; }
    const canvas = document.createElement('canvas');
    canvas.width = w; canvas.height = h;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, w, h);
    ctx.drawImage(bitmap, 0, 0, w, h);
    bitmap.close?.();
    const blob = await new Promise((res) => canvas.toBlob(res, 'image/jpeg', QUALITY));
    if (!blob || blob.size >= file.size) return file;
    return new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
  }

  /* ── Status bar ── */
  function statusEl(input) {
    return input.closest('label')?.querySelector('[data-upload-status]') || null;
  }
  function setStatus(input, text, state, percent) {
    const el = statusEl(input);
    if (!el) return;
    el.hidden = !text;
    el.className = 'upload-status' + (state ? ' is-' + state : '');
    const bar = typeof percent === 'number' ? Math.max(0, Math.min(100, percent)) : null;
    el.innerHTML = bar === null ? text : `${text}<div class="upload-status__bar"><span style="width:${bar}%"></span></div>`;
  }

  /* ── XHR upload ── */
  function uploadFile(url, file, kind, onProgress, folder) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', url);
      xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.upload.onprogress = (e) => {
        if (e.lengthComputable && typeof onProgress === 'function') onProgress(Math.round((e.loaded / e.total) * 100));
      };
      xhr.onload = () => {
        let data = null;
        try { data = JSON.parse(xhr.responseText); } catch (_) {}
        if (xhr.status >= 200 && xhr.status < 300 && data?.path) { resolve(data); return; }
        let msg = data?.errors?.file?.[0] || data?.message || 'فشل رفع الملف';
        if (typeof msg === 'string' && (msg === 'validation.mimes' || msg.startsWith('validation.'))) {
          msg = kind === 'audio'
            ? 'صيغة الصوت غير مدعومة. استخدم mp3 أو wav أو ogg أو m4a أو aac.'
            : 'صيغة الملف غير مدعومة. للفيديو استخدم mp4 أو webm أو mov.';
        }
        reject(new Error(msg));
      };
      xhr.onerror = () => reject(new Error('تعذر الاتصال أثناء الرفع'));
      const body = new FormData();
      body.append('file', file);
      body.append('kind', kind);
      if (folder) body.append('folder', folder);
      xhr.send(body);
    });
  }

  /* ── Instant local preview (before upload finishes) ── */
  function showPreview(input, file, resultUrl) {
    const label = input.closest('label');
    if (!label) return;
    // Remove any existing preview we injected
    label.querySelector('.async-preview-wrap')?.remove();

    const wrap = document.createElement('div');
    wrap.className = 'async-preview-wrap';

    const kind = input.dataset.uploadKind || 'image';
    let media = null;
    if (kind === 'video') {
      media = document.createElement('video');
      media.src = resultUrl;
      media.controls = true;
    } else if (kind === 'audio') {
      media = document.createElement('audio');
      media.src = resultUrl;
      media.controls = true;
    } else {
      media = document.createElement('img');
      media.src = resultUrl;
      media.alt = 'معاينة';
    }
    wrap.appendChild(media);
    label.appendChild(wrap);
  }

  /* ── Remove-button shown after successful upload ── */
  function showRemoveBtn(input, pathInput, objectUrl, oldPreview) {
    const label = input.closest('label');
    if (!label) return;
    label.querySelector('.btn-remove-preview')?.remove();

    const kind = input.dataset.uploadKind || 'image';
    const kindLabel = kind === 'video' ? 'الفيديو' : kind === 'audio' ? 'الملف الصوتي' : 'الصورة';

    const wrap = label.querySelector('.async-preview-wrap');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-remove-preview';
    btn.innerHTML = `🗑️ إزالة ${kindLabel}`;
    btn.addEventListener('click', () => {
      if (pathInput) pathInput.value = '';
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      wrap?.remove();
      btn.remove();
      setStatus(input, '', '');
      input.value = '';
      // Restore old server-side preview if it exists
      if (oldPreview) { oldPreview.hidden = false; oldPreview.style.opacity = ''; }
    });

    if (wrap) wrap.appendChild(btn);
    else label.appendChild(btn);
  }

  /* ── Bind async form ── */
  function bindAsyncForm(form) {
    const uploadUrl = form.dataset.uploadUrl;
    if (!uploadUrl) return;

    const pending = new Set();
    form._asyncUploads = pending;

    form.querySelectorAll('[data-async-file]').forEach((input) => {
      input.addEventListener('change', async () => {
        const file = input.files?.[0];
        const pathInput = document.getElementById(input.dataset.pathInput || '');

        // Clear previous state
        const labelEl = input.closest('label');
        labelEl?.querySelector('.async-preview-wrap')?.remove();
        labelEl?.querySelector('.btn-remove-preview')?.remove();
        // Hide existing server-side media preview while new one is being uploaded
        const oldPreview = labelEl?.querySelector('.media-preview');
        if (oldPreview) oldPreview.style.opacity = '0.3';

        if (!file) {
          if (pathInput) pathInput.value = '';
          setStatus(input, '', '');
          if (oldPreview) oldPreview.style.opacity = '';
          return;
        }

        const kind = input.dataset.uploadKind || 'image';

        // Guard audio size/format early (shared hosting post_max ~40MB)
        if (kind === 'audio') {
          const maxBytes = 40 * 1024 * 1024;
          const name = (file.name || '').toLowerCase();
          const okExt = /\.(mp3|wav|ogg|oga|m4a|aac|opus|webm|mp4|mpeg|mpga)$/i.test(name);
          const okMime = !file.type || /^audio\//i.test(file.type) || file.type === 'video/webm';
          if (!okExt && !okMime) {
            setStatus(input, 'صيغة الصوت غير مدعومة. استخدم mp3 / wav / ogg / m4a / aac.', 'error');
            input.value = '';
            if (oldPreview) oldPreview.style.opacity = '';
            return;
          }
          if (file.size > maxBytes) {
            setStatus(input, 'الملف الصوتي كبير جدًا (الحد 40 ميجابايت).', 'error');
            input.value = '';
            if (oldPreview) oldPreview.style.opacity = '';
            return;
          }
        }

        // Show instant preview from local file
        let objectUrl = null;
        if (kind === 'image' || kind === 'answer_image' || kind === 'video' || kind === 'audio') {
          objectUrl = URL.createObjectURL(file);
          showPreview(input, file, objectUrl);
        }

        const token = Symbol('upload');
        pending.add(token);
        input.dataset.uploading = '1';

        try {
          setStatus(input, 'جاري تجهيز الملف...', 'progress', 0);
          const ready = await compressFile(file);
          setStatus(input, 'جاري رفع الملف... 0%', 'progress', 0);

          const result = await uploadFile(uploadUrl, ready, kind, (pct) => {
            setStatus(input, `جاري رفع الملف... ${pct}%`, 'progress', pct);
          }, input.dataset.uploadFolder || '');

          if (pathInput) pathInput.value = result.path;
          input.value = ''; // don't re-send the file on final save

          // Update preview to server URL and hide the old server-side preview
          const label = input.closest('label');
          const prevImg = label?.querySelector('.async-preview-wrap img, .async-preview-wrap video, .async-preview-wrap audio');
          if (prevImg) prevImg.src = result.url;
          if (objectUrl) URL.revokeObjectURL(objectUrl);
          if (oldPreview) oldPreview.hidden = true;

          setStatus(input, 'تم رفع الملف بنجاح ✓', 'done');
          showRemoveBtn(input, pathInput, null, oldPreview);
        } catch (err) {
          console.error(err);
          if (pathInput) pathInput.value = '';
          if (objectUrl) URL.revokeObjectURL(objectUrl);
          input.closest('label')?.querySelector('.async-preview-wrap')?.remove();
          if (oldPreview) { oldPreview.style.opacity = ''; oldPreview.hidden = false; }
          setStatus(input, err.message || 'فشل رفع الملف', 'error');
        } finally {
          pending.delete(token);
          delete input.dataset.uploading;
        }
      });
    });

    /* ── Submit guard: prevent double-submit ── */
    form.addEventListener('submit', async (e) => {
      // Already cleared for final submit
      if (form.dataset.asyncReady === '1') {
        form.dataset.asyncReady = '';
        return;
      }

      // Already submitting — block completely
      if (form.dataset.submitting === '1') {
        e.preventDefault();
        return;
      }

      // Still uploading — wait
      if (pending.size > 0 || form.querySelector('[data-async-file][data-uploading="1"]')) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (btn) {
          btn.disabled = true;
          btn.dataset.oldText = btn.dataset.oldText || btn.textContent;
          btn.textContent = 'جاري إكمال الرفع...';
        }
        // Wait for all uploads to finish
        await new Promise((r) => {
          const t = setInterval(() => {
            if (pending.size === 0 && !form.querySelector('[data-async-file][data-uploading="1"]')) {
              clearInterval(t); r();
            }
          }, 100);
        });
        if (btn) { btn.disabled = false; btn.textContent = btn.dataset.oldText || 'حفظ السؤال'; }
        form.dataset.asyncReady = '1';
        form.dataset.submitting = '1';
        if (typeof form.requestSubmit === 'function') form.requestSubmit();
        else form.submit();
        return;
      }

      // Normal submit — lock immediately to prevent double click
      form.dataset.submitting = '1';
      const btn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (btn) { btn.disabled = true; btn.textContent = 'جاري الحفظ...'; }

      // Clear leftover file inputs
      form.querySelectorAll('[data-async-file]').forEach((input) => {
        const pathInput = document.getElementById(input.dataset.pathInput || '');
        if (pathInput?.value) input.value = '';
      });
    });
  }

  document.querySelectorAll('form[data-async-upload]').forEach(bindAsyncForm);

  /* ── Fallback: compress images in regular forms before submit ── */
  document.querySelectorAll('form:not([data-async-upload])').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      if (form.dataset.compressDone === '1') return;
      if (form.dataset.submitting === '1') { e.preventDefault(); return; }
      const inputs = [...form.querySelectorAll('input[type="file"]')].filter((i) => i.files?.length);
      if (!inputs.length) {
        form.dataset.submitting = '1';
        const btn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (btn) { btn.disabled = true; btn.textContent = 'جاري الحفظ...'; }
        return;
      }
      e.preventDefault();
      form.dataset.submitting = '1';
      const btn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (btn) { btn.disabled = true; btn.dataset.oldText = btn.textContent; btn.textContent = 'جاري رفع الملف...'; }
      try {
        for (const input of inputs) {
          const dt = new DataTransfer();
          for (const file of input.files) dt.items.add(await compressFile(file));
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
<script>
(() => {
  /* Auto-apply admin toolbar filters (no need to click تصفية) */
  document.querySelectorAll('form.toolbar[method="get"], form.toolbar[method="GET"]').forEach((form) => {
    const submitBtn = [...form.querySelectorAll('button[type="submit"]')]
      .find((b) => /تصفية|فلتر/i.test((b.textContent || '').trim()));
    if (submitBtn) {
      submitBtn.hidden = true;
      submitBtn.disabled = true;
    }

    let searchTimer = null;
    const submitNow = () => {
      if (typeof form.requestSubmit === 'function') form.requestSubmit();
      else form.submit();
    };

    form.querySelectorAll('select').forEach((el) => {
      el.addEventListener('change', submitNow);
    });

    form.querySelectorAll('input[type="search"], input[name="q"]').forEach((el) => {
      if (el.hasAttribute('data-category-finder') || el.id === 'categoryFinder') return;
      el.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(submitNow, 450);
      });
      el.addEventListener('search', submitNow);
    });
  });
})();
</script>
</body>
</html>
