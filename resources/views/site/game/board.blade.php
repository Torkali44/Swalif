<x-layouts.game>
<div class="game-shell board-shell">
  <header class="game-top">
    <div class="board-header-right">
      <a href="{{ route('home') }}" class="nav__logo">
        <img src="{{ asset('images/logo.jpg') }}" alt="سوالف" class="logo-img">
      </a>
      @if($activeTeam)
        <span class="turn-badge">دور فريق: {{ $activeTeam->name }}</span>
      @endif
    </div>
    <div>
      <b style="font-family:'Cairo';font-size:20px">{{ $game->category->name_ar }}</b>
      <small style="color:rgba(255,255,255,0.85)">{{ $game->name }}</small>
    </div>
    <div class="board-header-left">
      <a class="btn btn--ghost" href="{{ route('game.result', $game) }}">إنهاء اللعبة</a>
      <a class="btn btn-danger" href="{{ route('home') }}">خروج</a>
    </div>
  </header>

  <div class="board-arena">
    <!-- Row 1 Left (3 Easy Cells) -->
    @foreach($easyCells->slice(0, 3) as $cell)
      @if($cell)
        <a class="board-cell {{ $cell['used'] ? 'used' : '' }}"
           @if(!$cell['used']) href="{{ route('game.question', [$game, $cell['question']]) }}" @endif>
          {{ $cell['points'] }}
        </a>
      @else
        <div class="board-cell board-cell--empty"></div>
      @endif
    @endforeach

    <!-- Center Image (Column 4, Spans 3 Rows) -->
    <div class="board-center">
      @if($game->category->imageUrl())
        <img src="{{ $game->category->imageUrl() }}" alt="{{ $game->category->name_ar }}">
      @else
        <div class="board-center__fallback">
          <span>{{ $game->category->icon ?: '🎯' }}</span>
          <strong>{{ $game->category->name_ar }}</strong>
        </div>
      @endif
    </div>

    <!-- Row 1 Right (3 Easy Cells) -->
    @foreach($easyCells->slice(3, 3) as $cell)
      @if($cell)
        <a class="board-cell {{ $cell['used'] ? 'used' : '' }}"
           @if(!$cell['used']) href="{{ route('game.question', [$game, $cell['question']]) }}" @endif>
          {{ $cell['points'] }}
        </a>
      @else
        <div class="board-cell board-cell--empty"></div>
      @endif
    @endforeach

    <!-- Row 2 Left (3 Medium Cells) -->
    @foreach($mediumCells->slice(0, 3) as $cell)
      @if($cell)
        <a class="board-cell {{ $cell['used'] ? 'used' : '' }}"
           @if(!$cell['used']) href="{{ route('game.question', [$game, $cell['question']]) }}" @endif>
          {{ $cell['points'] }}
        </a>
      @else
        <div class="board-cell board-cell--empty"></div>
      @endif
    @endforeach

    <!-- Row 2 Right (3 Medium Cells) -->
    @foreach($mediumCells->slice(3, 3) as $cell)
      @if($cell)
        <a class="board-cell {{ $cell['used'] ? 'used' : '' }}"
           @if(!$cell['used']) href="{{ route('game.question', [$game, $cell['question']]) }}" @endif>
          {{ $cell['points'] }}
        </a>
      @else
        <div class="board-cell board-cell--empty"></div>
      @endif
    @endforeach

    <!-- Row 3 Left (3 Hard Cells) -->
    @foreach($hardCells->slice(0, 3) as $cell)
      @if($cell)
        <a class="board-cell {{ $cell['used'] ? 'used' : '' }}"
           @if(!$cell['used']) href="{{ route('game.question', [$game, $cell['question']]) }}" @endif>
          {{ $cell['points'] }}
        </a>
      @else
        <div class="board-cell board-cell--empty"></div>
      @endif
    @endforeach

    <!-- Row 3 Right (3 Hard Cells) -->
    @foreach($hardCells->slice(3, 3) as $cell)
      @if($cell)
        <a class="board-cell {{ $cell['used'] ? 'used' : '' }}"
           @if(!$cell['used']) href="{{ route('game.question', [$game, $cell['question']]) }}" @endif>
          {{ $cell['points'] }}
        </a>
      @else
        <div class="board-cell board-cell--empty"></div>
      @endif
    @endforeach
  </div>

  <div class="scoreboard board-scoreboard">
    @php
      $teams = $game->teams->values();
      $teamA = $teams->get(0);
      $teamB = $teams->get(1);
    @endphp

    @if($teamA)
      <!-- Team A (Right side in Arabic dir, or Left in HTML order) -->
      <div class="board-team-panel team--a">
        <span class="board-team-name">{{ $teamA->name }}</span>
        
        <div class="board-team-row">
          <!-- Adjust Score -->
          <div class="board-score-control" data-team-id="{{ $teamA->id }}" data-game-id="{{ $game->id }}">
            <button class="score-btn minus" data-amount="-100">—</button>
            <span class="score-val">{{ $teamA->score }}</span>
            <button class="score-btn plus" data-amount="100">+</button>
          </div>

          <!-- Lifelines -->
          <div class="board-lifelines-control" data-team-id="{{ $teamA->id }}" data-game-id="{{ $game->id }}">
            <span class="lifeline-title">وسائل المساعدة</span>
            <div class="lifeline-buttons">
              <button class="lifeline-btn {{ ($teamA->helpers_left['swap'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="swap" title="تبديل السؤال">🔄</button>
              <button class="lifeline-btn {{ ($teamA->helpers_left['phone_friend'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="phone_friend" title="اتصال بصديق">📞</button>
              <button class="lifeline-btn {{ ($teamA->helpers_left['two_answers'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="two_answers" title="إجابتين">✌️</button>
            </div>
          </div>
        </div>
      </div>
    @endif

    @if($teamB)
      <!-- Team B -->
      <div class="board-team-panel team--b">
        <span class="board-team-name">{{ $teamB->name }}</span>
        
        <div class="board-team-row">
          <!-- Lifelines -->
          <div class="board-lifelines-control" data-team-id="{{ $teamB->id }}" data-game-id="{{ $game->id }}">
            <span class="lifeline-title">وسائل المساعدة</span>
            <div class="lifeline-buttons">
              <button class="lifeline-btn {{ ($teamB->helpers_left['swap'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="swap" title="تبديل السؤال">🔄</button>
              <button class="lifeline-btn {{ ($teamB->helpers_left['phone_friend'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="phone_friend" title="اتصال بصديق">📞</button>
              <button class="lifeline-btn {{ ($teamB->helpers_left['two_answers'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="two_answers" title="إجابتين">✌️</button>
            </div>
          </div>

          <!-- Adjust Score -->
          <div class="board-score-control" data-team-id="{{ $teamB->id }}" data-game-id="{{ $game->id }}">
            <button class="score-btn minus" data-amount="-100">—</button>
            <span class="score-val">{{ $teamB->score }}</span>
            <button class="score-btn plus" data-amount="100">+</button>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
</x-layouts.game>
