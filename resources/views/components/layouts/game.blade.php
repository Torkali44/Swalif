<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>سين جيم — اللعب</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800;900&family=Tajawal:wght@500;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="game-body">
  @if(session('success'))
    <div class="flash game-flash">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="flash flash-error game-flash">{{ session('error') }}</div>
  @endif
  <main>{{ $slot }}</main>
</body>
</html>
