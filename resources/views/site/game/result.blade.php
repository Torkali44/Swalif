@php
  $avatars = [
    'linear-gradient(135deg,#FF1744,#7C3AED)',
    'linear-gradient(135deg,#00E5FF,#00843D)',
  ];
  $winnerRow = $winner
    ? $rankedTeams->first(fn ($row) => (int) $row['team']->id === (int) $winner->id)
    : null;
  $winnerCorrect = $winnerRow ? $winnerRow['correct'] : 0;
  $winnerWrong = $winnerRow ? $winnerRow['wrong'] : 0;
  $winnerAttempts = $winnerCorrect + $winnerWrong;
  $winnerAccuracy = $winnerAttempts > 0 ? (int) round(($winnerCorrect / $winnerAttempts) * 100) : 0;
  $justEnded = session('game_just_ended');
@endphp

<x-layouts.game>
<canvas id="confetti"></canvas>
<audio id="winSound" src="{{ asset('audio/game-win.wav') }}" preload="auto"></audio>

<div
  class="result-stage"
  data-result-page
  @if($justEnded) data-game-just-ended="1" @endif
  @if(!empty($needsSubscribe))
    data-subscribe-guard="1"
    data-subscribe-message="{{ $subscribeMessage }}"
    data-subscribe-url="{{ route('subscription.index') }}"
  @endif
>
  <header class="result-top">
    <a href="{{ route('home') }}" class="result-top__brand" title="سوالف">
      <img src="{{ asset('images/logo.png') }}" alt="سوالف">
      <span>سوالف</span>
    </a>
    <div class="result-top__actions">
      <button type="button" id="themeToggle" class="result-top__icon" title="تبديل المظهر" aria-label="تبديل المظهر">🌙</button>
      <a class="result-top__link" href="{{ !empty($needsSubscribe) ? route('subscription.index') : route('categories.index') }}" @if(!empty($needsSubscribe)) data-subscribe-lock data-subscribe-message="{{ $subscribeMessage }}" @endif>لعبة جديدة</a>
    </div>
  </header>

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
        <span class="badge">🎯 دقة {{ $winnerAccuracy }}%</span>
      </div>
    @endif
  </section>

  <section class="scoreboard">
    @foreach($rankedTeams as $row)
      @php
        $team = $row['team'];
        $isWinner = $winner && $winner->id === $team->id;
      @endphp
      <article class="team-card {{ $isWinner ? 'team-card--winner' : '' }}">
        <div class="team-card__main">
          <div class="team-card__avatar" style="background:{{ $avatars[($row['rank'] - 1) % 2] }}">
            {{ $row['rank'] }}
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
      </article>
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
    <a class="btn btn--fire btn--lg" href="{{ !empty($needsSubscribe) ? route('subscription.index') : route('categories.index') }}" @if(!empty($needsSubscribe)) data-subscribe-lock data-subscribe-message="{{ $subscribeMessage }}" @endif>🔁 لعبة جديدة</a>
    <a class="btn btn--ghost" href="{{ route('home') }}">🏠 الرئيسية</a>
  </section>
</div>
</x-layouts.game>
