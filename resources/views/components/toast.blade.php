@php
  $toasts = [];
  if (session('success')) { $toasts[] = ['type' => 'success', 'msg' => session('success')]; }
  if (session('error'))   { $toasts[] = ['type' => 'error',   'msg' => session('error')]; }
@endphp

@if(count($toasts))
  <div class="toast-stack" id="toastStack" aria-live="polite" aria-atomic="true">
    @foreach($toasts as $t)
      <div class="toast toast--{{ $t['type'] }}" role="status">
        <span class="toast__icon">{{ $t['type'] === 'success' ? '✅' : '⚠️' }}</span>
        <span class="toast__msg">{{ $t['msg'] }}</span>
        <button type="button" class="toast__close" aria-label="إغلاق">&times;</button>
        <span class="toast__bar"></span>
      </div>
    @endforeach
  </div>

  <script>
    (function () {
      var stack = document.getElementById('toastStack');
      if (!stack) return;
      var life = 4200;
      stack.querySelectorAll('.toast').forEach(function (toast, i) {
        var bar = toast.querySelector('.toast__bar');
        if (bar) { bar.style.animationDuration = life + 'ms'; }

        setTimeout(function () { toast.classList.add('is-visible'); }, 60 + i * 140);

        var hide = function () {
          toast.classList.remove('is-visible');
          toast.classList.add('is-leaving');
          setTimeout(function () { toast.remove(); }, 420);
        };

        var closeBtn = toast.querySelector('.toast__close');
        if (closeBtn) { closeBtn.addEventListener('click', hide); }

        setTimeout(hide, life + i * 140);
      });
    })();
  </script>
@endif
