<x-layouts.game>
@php
  $rows = [
    ['cells' => $easyCells, 'points' => 200],
    ['cells' => $mediumCells, 'points' => 400],
    ['cells' => $hardCells, 'points' => 600],
  ];
  $teams = $game->teams->values();
  $teamA = $teams->get(0);
  $teamB = $teams->get(1);
@endphp

<div class="board-page">
  <header class="board-bar">
    <div class="board-bar__start">
      <a href="{{ route('home') }}" class="board-bar__logo" title="سوالف">
        <img src="{{ asset('images/logo.png') }}" alt="سوالف">
      </a>
      @if($activeTeam)
        <span class="board-bar__turn">دور فريق: {{ $activeTeam->name }}</span>
      @endif
    </div>

    <div class="board-bar__center">
      @if(session('success'))
        <span class="board-bar__notice is-success">{{ session('success') }}</span>
      @elseif(session('error'))
        <span class="board-bar__notice is-error">{{ session('error') }}</span>
      @else
        <strong class="board-bar__title">{{ $game->category->name_ar }}</strong>
      @endif
    </div>

    <div class="board-bar__actions">
      <button type="button" id="themeToggle" class="board-bar__icon-btn" title="تبديل المظهر" aria-label="تبديل المظهر">🌙</button>
      <a class="board-bar__action" href="{{ route('game.result', $game) }}" title="إنهاء اللعبة">
        <span class="board-bar__action-ico">🏁</span>
        <span>إنهاء اللعبة</span>
      </a>
      <a class="board-bar__action board-bar__action--exit" href="{{ route('home') }}" title="خروج">
        <span class="board-bar__action-ico">⏻</span>
        <span>خروج</span>
      </a>
    </div>
  </header>

  <div class="board-page__body">
    <div class="board-grid" aria-label="لوحة اللعب">
      <div class="board-grid__center">
        @if($game->category->imageUrl())
          <img src="{{ $game->category->imageUrl() }}" alt="{{ $game->category->name_ar }}">
        @else
          <div class="board-grid__fallback">
            <span>{{ $game->category->icon ?: '🎯' }}</span>
            <strong>{{ $game->category->name_ar }}</strong>
          </div>
        @endif
      </div>

      @foreach($rows as $rowIndex => $row)
        @foreach($row['cells']->take(6) as $colIndex => $cell)
          @php
            $pos = $colIndex < 3 ? $colIndex + 1 : $colIndex - 2;
            $gridCol = $colIndex < 3 ? $pos : $pos + 4;
            $gridRow = $rowIndex + 1;
          @endphp
          @if($cell)
            @if($cell['used'])
              <div class="board-tile is-used"
                   style="grid-column:{{ $gridCol }};grid-row:{{ $gridRow }}"
                   aria-disabled="true"
                   title="تم الإجابة">✓</div>
            @else
              <a class="board-tile"
                 style="grid-column:{{ $gridCol }};grid-row:{{ $gridRow }}"
                 href="{{ route('game.question', [$game, $cell['question']]) }}">
                {{ $cell['points'] }}
              </a>
            @endif
          @else
            <div class="board-tile is-empty"
                 style="grid-column:{{ $gridCol }};grid-row:{{ $gridRow }}"></div>
          @endif
        @endforeach
      @endforeach
    </div>

    <div class="board-teams">
      @foreach([[$teamA, 'a'], [$teamB, 'b']] as [$team, $side])
        @continue(!$team)
        <section class="board-team board-team--{{ $side }} {{ $activeTeam?->id === $team->id ? 'is-turn' : '' }}">
          <div class="board-team__meta">
            <div class="board-team__avatar">{{ mb_substr($team->name, 0, 1) }}</div>
            <div>
              <h3 class="board-team__name">{{ $team->name }}</h3>
              @if($activeTeam?->id === $team->id)
                <span class="board-team__badge">دورهم الآن</span>
              @endif
            </div>
          </div>

          <div class="board-team__tools" data-team-id="{{ $team->id }}" data-game-id="{{ $game->id }}">
            <span class="board-team__tools-label">وسائل المساعدة</span>
            <div class="board-team__helpers">
              <button type="button" class="board-helper {{ ($team->helpers_left['swap'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="swap" title="تبديل السؤال">🔄</button>
              <button type="button" class="board-helper {{ ($team->helpers_left['phone_friend'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="phone_friend" title="اتصال بصديق">📞</button>
              <button type="button" class="board-helper {{ ($team->helpers_left['two_answers'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="two_answers" title="إجابتين">✌️</button>
            </div>
          </div>

          <div class="board-team__score" data-team-id="{{ $team->id }}" data-game-id="{{ $game->id }}">
            <button type="button" class="score-btn minus" data-amount="-100">−</button>
            <span class="score-val">{{ $team->score }}</span>
            <button type="button" class="score-btn plus" data-amount="100">+</button>
          </div>
        </section>
      @endforeach
    </div>
  </div>
</div>
</x-layouts.game>
