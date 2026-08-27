@php
  $rankedTeams = $game->teams->sortByDesc('score')->values();
  $cellsByRow = $game->cells->groupBy('row')->sortKeys();
@endphp

<x-layouts.game :show-nav="true">
  <canvas id="confetti"></canvas>
  <audio id="winSound" src="{{ asset('audio/game-win.wav') }}" preload="auto"></audio>

  <div class="result-stage hex-result-stage" data-result-page>
    <header class="result-top">
      <a href="{{ route('home') }}" class="result-top__brand" title="سوالف">
        <img src="{{ asset('images/mainLogo.jpg') }}" alt="سوالف" width="54" height="54" decoding="async">
        <span>سوالف</span>
      </a>
      <div class="result-top__actions">
        @if($game->custom_game_id)
          <a class="result-top__link" href="{{ route('custom-game.board', $game->custom_game_id) }}">كمّل اللعبة</a>
        @else
          <a class="result-top__link" href="{{ route('letter-grid.create') }}">لعبة جديدة</a>
        @endif
      </div>
    </header>

    <section class="winner">
      <div class="winner__crown">
        <svg viewBox="0 0 100 60" width="100" height="60">
          <path d="M10 55 L20 20 L35 40 L50 10 L65 40 L80 20 L90 55 Z" fill="url(#crownGrad)" stroke="#8A6D1B"
            stroke-width="1.5" />
          <defs>
            <linearGradient id="crownGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#F4C842" />
              <stop offset="100%" stop-color="#8A6D1B" />
            </linearGradient>
          </defs>
        </svg>
      </div>
      <p class="winner__label">شبكة الحروف — {{ $game->grid?->name_ar }}</p>
      <h1 class="winner__name">
        @if($isTie)
          تعادل!
        @elseif($winner)
          {{ $winner->name }}
        @else
          انتهت اللعبة
        @endif
      </h1>
      <div class="winner__trophy">🏆</div>
      @if($winner)
        <div class="winner__score">
          <span>حروف محققة</span>
          <b>{{ $winner->score }}</b>
        </div>
      @endif
    </section>

    <section class="scoreboard">
      @foreach($rankedTeams as $index => $team)
        <article class="team-card {{ $winner && (int) $winner->id === (int) $team->id ? 'team-card--winner' : '' }}">
          <div class="team-card__main">
            <div class="team-card__avatar"
              style="border-radius:50%;overflow:hidden;background: {{ $team->character?->accentGradient() ?: ($index === 0 ? 'linear-gradient(135deg,#FF1744,#FF6D00)' : 'linear-gradient(135deg,#FFB300,#F4C842)') }}">
              @if($team->character && $team->character->imageUrl())
                <img src="{{ $team->character->imageUrl() }}" alt="{{ $team->character->name_ar }}"
                  width="56" height="56" style="width:56px;height:56px;object-fit:cover;border-radius:50%;display:block">
              @elseif($team->character && $team->character->icon)
                <span>{{ $team->character->icon }}</span>
              @else
                {{ mb_substr($team->name, 0, 1) }}
              @endif
            </div>
            <div class="team-card__info">
              <b>{{ $team->name }}</b>
            </div>
            <div class="team-card__score">
              <b>{{ $team->score }}</b>
              <small>حرف</small>
            </div>
          </div>
        </article>
      @endforeach
    </section>

    <section class="hex-result-grid">
      <h3>ملخص الشبكة</h3>
      <div class="hex-result-grid__board">
        @foreach($cellsByRow as $rowCells)
          <div class="hex-row hex-row--mini">
            @foreach($rowCells->sortBy('col') as $cell)
              @php
                $ti = $game->teams->search(fn($t) => (int) $t->id === (int) $cell->claimed_team_id);
              @endphp
              <span class="hex-cell hex-cell--mini
                  {{ $cell->isClaimed() ? 'is-claimed is-team-' . max(0, $ti) : '' }}
                  {{ $cell->isMissed() ? 'is-missed' : '' }}">
                <span class="hex-cell__inner"><span class="hex-cell__letter">{{ $cell->letter }}</span></span>
              </span>
            @endforeach
          </div>
        @endforeach
      </div>
      <div class="hex-result-grid__legend">
        @foreach($game->teams->values() as $tIdx => $t)
          <span><i class="dot dot--{{ $tIdx === 0 ? 'a' : 'b' }}"></i> {{ $t->name }}</span>
        @endforeach
        <span><i class="dot dot--miss"></i> بدون إجابة / خاطئ</span>
      </div>
    </section>

    <div class="result-actions">
      @if($game->custom_game_id)
        <a href="{{ route('custom-game.board', $game->custom_game_id) }}" class="btn btn--fire btn--lg" style="width:100%;max-width:420px">
          ▦ كمّل باقي فئات لعبتك الخاصة
        </a>
      @else
        <a href="{{ route('letter-grid.create') }}" class="btn btn--fire btn--lg">🔁 لعبة جديدة</a>
        <a href="{{ route('categories.index') }}" class="btn btn--ghost btn--lg">🎮 الألعاب</a>
        <a href="{{ route('home') }}" class="btn btn--ghost btn--lg">🏠 الرئيسية</a>
      @endif
    </div>
  </div>

  <style>
  .hex-result-stage .team-card__avatar {
    width: 56px !important;
    height: 56px !important;
    min-width: 56px !important;
    border-radius: 50% !important;
  }
  .hex-result-grid__board {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    max-width: 100%;
  }
  @media (max-width: 640px) {
    .hex-result-stage .result-top { padding: 10px 12px; gap: 8px; flex-wrap: wrap; }
    .hex-result-stage .winner__name { font-size: 1.6rem; }
    .hex-result-grid__board { transform: scale(0.92); transform-origin: top center; }
    .hex-result-stage .result-actions { flex-direction: column; }
    .hex-result-stage .result-actions .btn { width: 100%; }
  }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const audio = document.getElementById('winSound');
      if (audio) {
        audio.currentTime = 0;
        audio.play().catch(() => {});
      }
      try { window.SwalifAudio?.play('correct'); } catch (_) {}
    });
  </script>
</x-layouts.game>
