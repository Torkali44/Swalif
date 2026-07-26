@props(['title' => null])
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'سوالف' }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="سوالف — ألعاب • ضحك • فرفشة. فرق، فئات متنوعة، مؤقت، نقاط ومنافسة حماسية بهوية إماراتية.">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;900&family=Tajawal:wght@700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;900&family=Tajawal:wght@700;800&display=swap" rel="stylesheet"></noscript>
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
  <main>{{ $slot }}</main>
  <x-footer />
  <x-toast />
</body>
</html>
