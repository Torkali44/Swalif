@php
  $teams = $game->teams->values();
  $teamA = $teams->get(0);
  $teamB = $teams->get(1);
  $level = $question->level;
  $levelClass = match ($level?->value ?? 'medium') {
    'easy' => 'easy',
    'hard' => 'hard',
    default => 'medium',
  };
  $levelLabel = $level?->label() ?? 'متوسط';
  $keys = ['أ', 'ب', 'ج', 'د', 'هـ', 'و'];
  $progress = $totalQuestions > 0
    ? min(100, (int) round((($answeredQuestions + 1) / $totalQuestions) * 100))
    : 0;
  $currentIndex = min($answeredQuestions + 1, max($totalQuestions, 1));
  $activeTeam = ($answeredQuestions % 2 === 0) ? 'a' : 'b';
@endphp

<x-layouts.game>
<div class="play-stage">
  <header class="topbar">
    <div class="category-badge">
      <span class="category-badge__icon">
        @if($game->category->imageUrl())
          <img src="{{ $game->category->imageUrl() }}" alt="">
        @else
          {{ $game->category->icon ?: '🎯' }}
        @endif
      </span>
      <div>
        <small>الفئة</small>
        <b>{{ $game->category->name_ar }}</b>
      </div>
    </div>

    <div class="level-chip level-chip--{{ $levelClass }}">
      <span></span><span></span><span></span>
      <em>{{ $levelLabel }} • {{ $question->points }} نقطة</em>
    </div>
  </header>

  @if($teamA && $teamB)
    <section class="teams">
      <div class="team team--a {{ $activeTeam === 'a' ? 'active' : '' }}" data-team-card="a">
        <div class="team__avatar" style="background:linear-gradient(135deg,#FF1744,#7C3AED)">{{ mb_substr($teamA->name, 0, 1) }}</div>
        <div>
          <b>{{ $teamA->name }}</b>
          <div class="team__score">{{ number_format($teamA->score) }} <em>نقطة</em></div>
        </div>
        <div class="team__lifelines" data-team-id="{{ $teamA->id }}" data-game-id="{{ $game->id }}">
          <button type="button" class="helper-btn {{ ($teamA->helpers_left['swap'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="swap" title="تبديل السؤال">🔄</button>
          <button type="button" class="helper-btn {{ ($teamA->helpers_left['phone_friend'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="phone_friend" title="اتصال بصديق">📞</button>
          <button type="button" class="helper-btn {{ ($teamA->helpers_left['two_answers'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="two_answers" title="إجابتين">✌️</button>
        </div>
        <div class="team__turn" style="display: {{ $activeTeam === 'a' ? 'block' : 'none' }}">🎯 دورهم</div>
      </div>

      <div class="vs">VS</div>

      <div class="team team--b {{ $activeTeam === 'b' ? 'active' : '' }}" data-team-card="b">
        <div class="team__avatar" style="background:linear-gradient(135deg,#00E5FF,#00843D)">{{ mb_substr($teamB->name, 0, 1) }}</div>
        <div>
          <b>{{ $teamB->name }}</b>
          <div class="team__score">{{ number_format($teamB->score) }} <em>نقطة</em></div>
        </div>
        <div class="team__lifelines" data-team-id="{{ $teamB->id }}" data-game-id="{{ $game->id }}">
          <button type="button" class="helper-btn {{ ($teamB->helpers_left['swap'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="swap" title="تبديل السؤال">🔄</button>
          <button type="button" class="helper-btn {{ ($teamB->helpers_left['phone_friend'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="phone_friend" title="اتصال بصديق">📞</button>
          <button type="button" class="helper-btn {{ ($teamB->helpers_left['two_answers'] ?? 1) <= 0 ? 'used' : '' }}" data-helper="two_answers" title="إجابتين">✌️</button>
        </div>
        <div class="team__turn" style="display: {{ $activeTeam === 'b' ? 'block' : 'none' }}">🎯 دورهم</div>
      </div>
    </section>
  @endif

  <div class="meta-row">
    <div class="q-index">
      السؤال <b>{{ $currentIndex }}</b> <span>من</span> <b>{{ $totalQuestions }}</b>
      <div class="q-progress"><i style="width:{{ $progress }}%"></i></div>
    </div>

    <div class="timer" id="playTimer" data-timer-ring="{{ $timeLimit }}">
      <svg viewBox="0 0 120 120" class="timer__ring">
        <defs>
          <linearGradient id="fireGrad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#FFB300"/>
            <stop offset="50%" stop-color="#FF6D00"/>
            <stop offset="100%" stop-color="#FF1744"/>
          </linearGradient>
        </defs>
        <circle cx="60" cy="60" r="52" class="timer__track"/>
        <circle cx="60" cy="60" r="52" class="timer__bar" id="timerBar" style="stroke:url(#fireGrad)"/>
      </svg>
      <div class="timer__value"><b id="timerValue">{{ $timeLimit }}</b><small>ثانية</small></div>
    </div>

    <div class="q-points">
      <span>مكافأة</span>
      <b>{{ $question->points }}</b>
      <small>نقطة</small>
    </div>
  </div>

  <main class="question-card">
    <div class="question-card__glow"></div>
    <span class="question-card__label">السؤال</span>
    <h1 class="question-card__text">{{ $question->question_text }}</h1>

    @if($question->imageUrl())
      <div class="question-card__media">
        <img src="{{ $question->imageUrl() }}" alt="صورة السؤال" loading="lazy">
      </div>
    @endif

    @if($question->hasChoices())
      <div class="answers">
        @foreach($question->options as $i => $option)
          @continue(!filled($option->option_text))
          <div class="answer">
            <span class="answer__key">{{ $keys[$i] ?? ($i + 1) }}</span>
            <span class="answer__text">{{ $option->option_text }}</span>
          </div>
        @endforeach
      </div>
    @endif

    <div class="action-bar">
      <a class="btn btn--fire btn--lg" href="{{ route('game.answer', [$game, $gq]) }}">✔ عرض الإجابة</a>
    </div>
  </main>
</div>
</x-layouts.game>
