<x-layouts.app title="سجل الألعاب — سوالف">
<section class="history-page section">
  <div class="container">
    <x-back-button :href="route('home')" />
    <header class="section-head">
      <h2>سجل <span class="grad-text">الألعاب</span></h2>
      <p>كل الجولات التي أنشأتها — اضغط للمتابعة أو المراجعة.</p>
    </header>

    <div class="history-tabs" style="display:flex;gap:12px;margin-bottom:20px;justify-content:center;flex-wrap:wrap">
      <button type="button" class="btn btn--fire btn--sm history-tab-btn is-active" data-hist-tab="standard" style="border-radius:50px;padding:8px 20px">🎮 ألعاب الفئات ({{ $games->total() }})</button>
      <button type="button" class="btn btn--ghost btn--sm history-tab-btn" data-hist-tab="custom" style="border-radius:50px;padding:8px 20px">✨ الألعاب الخاصة ({{ $customGames->total() }})</button>
    </div>

    <!-- Standard Games List -->
    <div id="hist-standard" class="history-card">
      @forelse($games as $game)
        @php
          $status = $game->status instanceof \BackedEnum ? $game->status->value : (string) $game->status;
          $isFinished = $status === 'finished';
        @endphp
        <a class="history-item" href="{{ $isFinished ? route('game.result', $game) : route('game.board', $game) }}">
          <div class="history-item__main">
            <div class="history-item__icon">{{ $game->category?->icon ?: '🎯' }}</div>
            <div>
              <div class="history-item__title">{{ $game->name }}</div>
              <div class="history-item__meta">
                {{ $game->category?->name_ar ?? 'فئة' }}
                · {{ $game->created_at->format('Y/m/d') }}
              </div>
            </div>
          </div>
          <span class="history-badge {{ $isFinished ? 'is-finished' : 'is-playing' }}">
            {{ $isFinished ? 'منتهية' : 'قيد اللعب' }}
          </span>
        </a>
      @empty
        <p class="muted" style="padding:28px;text-align:center;margin:0">لم تبدأ أي لعبة فئات بعد.</p>
      @endforelse

      @if($games->hasPages())
        <div class="pagination" style="margin-top:18px">{{ $games->links() }}</div>
      @endif
    </div>

    <!-- Custom Games List -->
    <div id="hist-custom" class="history-card" hidden>
      @forelse($customGames as $cGame)
        @php
          $cStatus = $cGame->status instanceof \BackedEnum ? $cGame->status->value : (string) $cGame->status;
          $cIsFinished = $cStatus === 'finished';
          $catsCount = $cGame->categories->count();
        @endphp
        <a class="history-item" href="{{ $cIsFinished ? route('custom-game.result', $cGame) : route('custom-game.board', $cGame) }}">
          <div class="history-item__main">
            <div class="history-item__icon">🎲</div>
            <div>
              <div class="history-item__title">{{ $cGame->name }}</div>
              <div class="history-item__meta">
                {{ $catsCount }} فئات
                · {{ $cGame->created_at->format('Y/m/d') }}
              </div>
            </div>
          </div>
          <span class="history-badge {{ $cIsFinished ? 'is-finished' : 'is-playing' }}">
            {{ $cIsFinished ? 'منتهية' : 'قيد اللعب' }}
          </span>
        </a>
      @empty
        <p class="muted" style="padding:28px;text-align:center;margin:0">لم تنشئ أي لعبة خاصة بعد.</p>
      @endforelse

      @if($customGames->hasPages())
        <div class="pagination" style="margin-top:18px">{{ $customGames->links() }}</div>
      @endif
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var tabBtns = document.querySelectorAll('.history-tab-btn');
  var panelStandard = document.getElementById('hist-standard');
  var panelCustom = document.getElementById('hist-custom');

  tabBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      tabBtns.forEach(function(b) {
        b.classList.remove('btn--fire', 'is-active');
        b.classList.add('btn--ghost');
      });
      this.classList.remove('btn--ghost');
      this.classList.add('btn--fire', 'is-active');

      var isCustom = this.dataset.histTab === 'custom';
      if (panelStandard) panelStandard.hidden = isCustom;
      if (panelCustom) panelCustom.hidden = !isCustom;
    });
  });

  if (window.location.search.includes('custom_page')) {
    var customTabBtn = document.querySelector('[data-hist-tab="custom"]');
    if (customTabBtn) customTabBtn.click();
  }
});
</script>
</x-layouts.app>
