@php
  $avatars = [
    'linear-gradient(135deg,#FF1744,#7C3AED)',
    'linear-gradient(135deg,#00E5FF,#00843D)',
  ];
  $winnerRow = $winner ? $rankedTeams->firstWhere('team.id', $winner->id) : null;
  $winnerCorrect = $winnerRow ? $winnerRow['correct'] : 0;
  $winnerWrong = $winnerRow ? $winnerRow['wrong'] : 0;
@endphp

<x-layouts.game :show-nav="true">
<canvas id="confetti"></canvas>

<div class="result-stage">
  <x-back-button :href="route('game.board', $game)" label="رجوع للوحة" />

  <section class="winner">
    <div class="winner__crown">
      <svg viewBox="0 0 100 60" width="120" height="72">
        <defs>
          <linearGradient id="crownGrad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#F4C842"/>
            <stop offset="100%" stop-color="#8A6D1B"/>
          </linearGradient>
        </defs>
        <path d="M10 55 L20 20 L35 40 L50 10 L65 40 L80 20 L90 55 Z" fill="url(#crownGrad)" stroke="#8A6D1B" stroke-width="1.5"/>
        <circle cx="50" cy="10" r="4" fill="#FF1744"/>
        <circle cx="20" cy="20" r="3" fill="#00E5FF"/>
        <circle cx="80" cy="20" r="3" fill="#00C853"/>
      </svg>
    </div>

    <p class="winner__label">{{ $winner ? 'الفريق الفائز' : 'النتيجة' }}</p>
    <h1 class="winner__name">{{ $winner?->name ?? 'تعادل!' }}</h1>
    <div class="winner__trophy">🏆</div>

    @if($winner)
      <div class="winner__score">
        <span>مجموع النقاط</span>
        <b>{{ number_format($winner->score) }}</b>
      </div>
      <div class="winner__badges">
        <span class="badge">🔥 {{ $winnerCorrect }} صحيحة</span>
        <span class="badge">✕ {{ $winnerWrong }} خاطئة</span>
        <span class="badge">🎯 دقة {{ $accuracy }}%</span>
      </div>
    @endif
  </section>

  <section class="scoreboard">
    @foreach($rankedTeams as $row)
      @php
        $team = $row['team'];
        $isWinner = $winner && $winner->id === $team->id;
      @endphp
      <div class="team-card {{ $isWinner ? 'team-card--winner' : '' }}">
        <div class="team-card__rank">{{ $row['rank'] }}</div>
        <div class="team-card__avatar" style="background:{{ $avatars[($row['rank'] - 1) % 2] }}">
          {{ mb_substr($team->name, 0, 1) }}
        </div>
        <div class="team-card__info">
          <b>{{ $team->name }}</b>
          <div class="team-card__stats">
            <span class="stat stat--correct">✔ {{ $row['correct'] }} صحيحة</span>
            <span class="stat stat--wrong">✕ {{ $row['wrong'] }} خاطئة</span>
          </div>
        </div>
        <div class="team-card__score">
          <b>{{ number_format($team->score) }}</b>
          <small>نقطة</small>
        </div>
      </div>
    @endforeach
  </section>

  <section class="summary">
    <div class="summary__card">
      <div class="summary__icon" style="background:linear-gradient(135deg,#00C853,#1B5E20)">✔</div>
      <div>
        <small>إجابات صحيحة</small>
        <b>{{ $correctCount }}</b>
      </div>
    </div>
    <div class="summary__card">
      <div class="summary__icon" style="background:linear-gradient(135deg,#FF1744,#B00020)">✕</div>
      <div>
        <small>إجابات خاطئة</small>
        <b>{{ $wrongCount }}</b>
      </div>
    </div>
    <div class="summary__card">
      <div class="summary__icon" style="background:linear-gradient(135deg,#00E5FF,#0064B7)">⏱</div>
      <div>
        <small>مدة اللعبة</small>
        <b>{{ $duration ?? '—' }}</b>
      </div>
    </div>
    <div class="summary__card">
      <div class="summary__icon" style="background:linear-gradient(135deg,#F4C842,#8A6D1B)">🎯</div>
      <div>
        <small>دقة عامة</small>
        <b>{{ $accuracy }}%</b>
      </div>
    </div>
  </section>

  <section class="actions">
    <a class="btn btn--fire btn--lg" href="{{ route('categories.index') }}">🔁 لعبة جديدة</a>
    <a class="btn btn--ghost" href="{{ route('game.board', $game) }}">📊 العودة للوحة</a>
    <a class="btn btn--ghost" href="{{ route('home') }}">🏠 الرئيسية</a>
  </section>
</div>
</x-layouts.game>
