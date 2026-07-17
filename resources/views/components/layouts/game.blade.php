@props(['showNav' => false])
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>سوالف — اللعب</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800;900&family=Tajawal:wght@500;700;800&display=swap" rel="stylesheet">
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
<body @class(['game-body', 'game-body--nav' => $showNav])>
  <script>
    if (document.documentElement.classList.contains('dark')) {
      document.body.classList.add('dark');
    }
  </script>
  @if($showNav)
    <x-header />
  @endif
  <main>{{ $slot }}</main>
</body>
</html>
