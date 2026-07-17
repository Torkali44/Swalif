@props(['title' => null])
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'سوالف' }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="سوالف — ألعاب • ضحك • فرفشة. فرق، فئات متنوعة، مؤقت، نقاط ومنافسة حماسية بهوية إماراتية.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@500;700;800;900&display=swap" rel="stylesheet">
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
<body>
  <script>
    if (document.documentElement.classList.contains('dark')) {
      document.body.classList.add('dark');
    }
  </script>
  <x-header />
  @if(session('success'))
    <div class="flash container">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="flash flash-error container">{{ session('error') }}</div>
  @endif
  <main>{{ $slot }}</main>
  <x-footer />
</body>
</html>
