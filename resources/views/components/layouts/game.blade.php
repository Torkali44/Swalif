<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>سوالف — اللعب</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800;900&family=Tajawal:wght@500;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="game-body">
  <script>
    if (localStorage.getItem('theme') === 'dark') {
      document.body.classList.add('dark');
    }
  </script>
  <button id="themeToggle" class="theme-toggle-btn" title="تبديل المظهر">🌙</button>

  @if(session('success'))
    <div class="flash game-flash">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="flash flash-error game-flash">{{ session('error') }}</div>
  @endif
  <main>{{ $slot }}</main>
</body>
</html>
