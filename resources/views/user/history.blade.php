<x-layouts.app>
<section class="section">
  <div class="container">
    <header class="section-head">
      <h2>سجل <span class="grad-text">الألعاب</span></h2>
      <p>كل الجولات التي أنشأتها.</p>
    </header>
    <div class="data-list">
      @forelse($games as $game)
        <a href="{{ route('game.board', $game) }}">
          <span>{{ $game->category->icon }} {{ $game->name }}</span>
          <small>
            {{ ($game->status->value ?? $game->status) === 'finished' ? 'منتهية' : 'قيد اللعب' }}
            · {{ $game->created_at->format('Y/m/d') }}
          </small>
        </a>
      @empty
        <p class="muted" style="padding:20px;text-align:center">لم تبدأ أي لعبة بعد.</p>
      @endforelse
    </div>
    <div class="pagination" style="margin-top:18px">{{ $games->links() }}</div>
  </div>
</section>
</x-layouts.app>
