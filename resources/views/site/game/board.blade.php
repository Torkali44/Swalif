<x-layouts.game>
<div class="game-shell board-shell">
  <header class="game-top">
    <a href="{{ route('home') }}" class="nav__logo">
      <span class="logo-badge">س</span>
    </a>
    <div>
      <b>{{ $game->category->name_ar }}</b>
      <small>{{ $game->name }}</small>
    </div>
    <a class="btn btn--ghost" href="{{ route('game.result', $game) }}">إنهاء اللعبة</a>
  </header>

  <div class="board-arena">
    <div class="board-side">
      @foreach($leftCells as $cell)
        <a class="board-cell {{ $cell['used'] ? 'used' : '' }}"
           @if(!$cell['used']) href="{{ route('game.question', [$game, $cell['question']]) }}" @endif>
          {{ $cell['used'] ? '✓' : $cell['points'] }}
        </a>
      @endforeach
    </div>

    <div class="board-center">
      @if($game->category->imageUrl())
        <img src="{{ $game->category->imageUrl() }}" alt="{{ $game->category->name_ar }}">
      @else
        <div class="board-center__fallback">
          <span>{{ $game->category->icon }}</span>
          <strong>{{ $game->category->name_ar }}</strong>
        </div>
      @endif
    </div>

    <div class="board-side">
      @foreach($rightCells as $cell)
        <a class="board-cell {{ $cell['used'] ? 'used' : '' }}"
           @if(!$cell['used']) href="{{ route('game.question', [$game, $cell['question']]) }}" @endif>
          {{ $cell['used'] ? '✓' : $cell['points'] }}
        </a>
      @endforeach
    </div>
  </div>

  <div class="scoreboard board-scoreboard">
    @foreach($game->teams as $team)
      <div>
        <small>{{ $team->name }}</small>
        <b>{{ $team->score }}</b>
      </div>
    @endforeach
  </div>
</div>
</x-layouts.game>
