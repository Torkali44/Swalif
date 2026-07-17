<x-layouts.app title="سجل الألعاب — سوالف">
<section class="history-page section">
  <div class="container">
    <x-back-button :href="route('home')" />
    <header class="section-head">
      <h2>سجل <span class="grad-text">الألعاب</span></h2>
      <p>كل الجولات التي أنشأتها — اضغط للمتابعة أو المراجعة.</p>
    </header>

    <div class="history-card">
      @forelse($games as $game)
        @php
          $status = $game->status->value ?? $game->status;
          $isFinished = $status === 'finished';
        @endphp
        <a class="history-item" href="{{ route('game.board', $game) }}">
          <div class="history-item__main">
            <div class="history-item__icon">{{ $game->category->icon ?: '🎯' }}</div>
            <div>
              <div class="history-item__title">{{ $game->name }}</div>
              <div class="history-item__meta">
                {{ $game->category->name_ar ?? 'فئة' }}
                · {{ $game->created_at->format('Y/m/d') }}
              </div>
            </div>
          </div>
          <span class="history-badge {{ $isFinished ? 'is-finished' : 'is-playing' }}">
            {{ $isFinished ? 'منتهية' : 'قيد اللعب' }}
          </span>
        </a>
      @empty
        <p class="muted" style="padding:28px;text-align:center;margin:0">لم تبدأ أي لعبة بعد.</p>
      @endforelse
    </div>

    @if($games->hasPages())
      <div class="pagination" style="margin-top:18px">{{ $games->links() }}</div>
    @endif
  </div>
</section>
</x-layouts.app>
