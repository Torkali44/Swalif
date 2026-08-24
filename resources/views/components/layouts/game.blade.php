@props(['showNav' => false])
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>سوالف — اللعب</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800;900&family=Tajawal:wght@500;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800;900&family=Tajawal:wght@500;700;800&display=swap" rel="stylesheet"></noscript>
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
  <script>
    window.SwalifAudio = {
      ctx: null,
      getCtx: function() {
        if (!this.ctx) {
          this.ctx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (this.ctx.state === 'suspended') {
          this.ctx.resume();
        }
        return this.ctx;
      },
      play: function(type) {
        try {
          var ctx = this.getCtx();
          var now = ctx.currentTime;
          if (type === 'click' || type === 'select') {
            var osc = ctx.createOscillator(); var gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(440, now);
            osc.frequency.exponentialRampToValueAtTime(880, now + 0.12);
            gain.gain.setValueAtTime(0.15, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.12);
            osc.start(now); osc.stop(now + 0.12);
          } else if (type === 'fav') {
            var osc = ctx.createOscillator(); var gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(523.25, now);
            osc.frequency.exponentialRampToValueAtTime(659.25, now + 0.15);
            gain.gain.setValueAtTime(0.2, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
            osc.start(now); osc.stop(now + 0.25);
          } else if (type === 'plus') {
            var osc = ctx.createOscillator(); var gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(587.33, now);
            osc.frequency.exponentialRampToValueAtTime(880, now + 0.2);
            gain.gain.setValueAtTime(0.25, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.2);
            osc.start(now); osc.stop(now + 0.2);
          } else if (type === 'minus') {
            var osc = ctx.createOscillator(); var gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(349.23, now);
            osc.frequency.exponentialRampToValueAtTime(220, now + 0.2);
            gain.gain.setValueAtTime(0.25, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.2);
            osc.start(now); osc.stop(now + 0.2);
          } else if (type === 'phone' || type === 'helper' || type === 'spin') {
            var osc = ctx.createOscillator(); var gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(700, now);
            osc.frequency.setValueAtTime(900, now + 0.12);
            gain.gain.setValueAtTime(0.2, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
            osc.start(now); osc.stop(now + 0.25);
          } else if (type === 'correct' || type === 'fanfare') {
            [523.25, 659.25, 783.99, 1046.50].forEach(function(freq, i) {
              var o = ctx.createOscillator(); var g = ctx.createGain();
              o.connect(g); g.connect(ctx.destination);
              o.frequency.setValueAtTime(freq, now + i * 0.08);
              g.gain.setValueAtTime(0.2, now + i * 0.08);
              g.gain.exponentialRampToValueAtTime(0.01, now + i * 0.08 + 0.2);
              o.start(now + i * 0.08); o.stop(now + i * 0.08 + 0.2);
            });
          } else if (type === 'wrong') {
            [220, 196, 174].forEach(function(freq, i) {
              var o = ctx.createOscillator(); var g = ctx.createGain();
              o.type = 'sawtooth';
              o.connect(g); g.connect(ctx.destination);
              o.frequency.setValueAtTime(freq, now + i * 0.1);
              g.gain.setValueAtTime(0.25, now + i * 0.1);
              g.gain.exponentialRampToValueAtTime(0.01, now + i * 0.1 + 0.25);
              o.start(now + i * 0.1); o.stop(now + i * 0.1 + 0.25);
            });
          }
        } catch(e) {}
      }
    };

    document.addEventListener('click', function(e) {
      var btn = e.target.closest('button, a.btn, .cg-tile, .pill-btn, .nav-pill-btn, .header-pill');
      if (btn) {
        if (btn.classList.contains('plus')) window.SwalifAudio.play('plus');
        else if (btn.classList.contains('minus')) window.SwalifAudio.play('minus');
        else if (btn.classList.contains('cg-helper-btn') || btn.classList.contains('helper-btn')) window.SwalifAudio.play('helper');
        else window.SwalifAudio.play('click');
      }
    }, { passive: true });
  </script>
</body>
</html>
