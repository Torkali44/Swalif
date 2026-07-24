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
  $questionType = $question->type ?? 'standard';
  $orderItems = collect($question->orderItems())
    ->map(fn ($text, $index) => ['key' => (string) $index, 'text' => $text])
    ->shuffle()
    ->values();
  $matchPairs = collect($question->matchPairs());
  $matchLeftItems = $matchPairs
    ->map(fn ($pair, $index) => ['key' => (string) $index, 'text' => $pair['left']])
    ->shuffle()
    ->values();
  $matchRightItems = $matchPairs
    ->map(fn ($pair, $index) => ['key' => (string) $index, 'text' => $pair['right']])
    ->shuffle()
    ->values();
@endphp

<x-layouts.game :show-nav="true">
<div class="play-stage"
  @if(!empty($freeLeaveWarn))
    data-free-leave-guard="1"
    data-free-leave-message="{{ $leaveWarningMessage }}"
  @endif
>
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

    <div>
      @if(session('success'))
        <span class="header-notice-badge success">{{ session('success') }}</span>
      @elseif(session('error'))
        <span class="header-notice-badge error">{{ session('error') }}</span>
      @endif
    </div>

    <div class="level-chip level-chip--{{ $levelClass }}">
      <span></span><span></span><span></span>
      <em>{{ $levelLabel }} • {{ $question->displayPoints() }} نقطة</em>
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

    <div
      class="timer"
      id="playTimer"
      data-timer-ring="{{ $timeLimit }}"
      data-answer-url="{{ route('game.answer', [$game, $gq]) }}"
      @if($question->isVideo() && $question->mediaUrl()) data-timer-wait-video="true" @endif
    >
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
      <b>{{ $question->displayPoints() }}</b>
      <small>نقطة</small>
    </div>
  </div>

  <main class="question-card">
    <div class="question-card__glow"></div>

    @if($question->isVideo() && $question->mediaUrl())
      <div class="video-gate" data-video-gate>
        <div class="video-gate__alert">
          <span class="video-gate__icon">🎬</span>
          <div>
            <b>ركّز في الفيديو كويس</b>
            <p>الفيديو هيتعرض <em>مرة واحدة فقط</em>، وبعده هيظهر السؤال. لو طفّيته مش هيتشاف تاني.</p>
          </div>
        </div>
        <div class="question-card__media question-card__media--video">
          <video
            id="questionVideo"
            class="question-media question-media--video"
            src="{{ $question->mediaUrl() }}"
            controls
            playsinline
            controlslist="nodownload noplaybackrate"
            data-play-once="true"
            data-gate-video="true"
          ></video>
          <p class="question-media__hint" data-video-hint>اضغط تشغيل وركّز… لن يُعاد العرض</p>
        </div>
      </div>

      <div class="video-reveal" data-video-reveal hidden>
        <span class="question-card__label">السؤال</span>
        <h1 class="question-card__text">{{ $question->question_text }}</h1>
        <div class="action-bar">
          <a class="btn btn--fire btn--lg" href="{{ route('game.answer', [$game, $gq]) }}">✔ عرض الإجابة</a>
        </div>
      </div>
    @else
      <span class="question-card__label">السؤال</span>
      <h1 class="question-card__text">{{ $question->question_text }}</h1>

      @if($question->isAudio() && $question->mediaUrl())
        <div class="question-card__media question-card__media--audio">
          <audio class="question-media question-media--audio" src="{{ $question->mediaUrl() }}" controls controlsList="nodownload"></audio>
        </div>
      @elseif($question->imageUrl())
        <div class="question-card__media">
          <img src="{{ $question->imageUrl() }}" alt="صورة السؤال" loading="lazy">
        </div>
      @endif

      @if($questionType === 'order' && $orderItems->isNotEmpty())
        <section class="interactive-answer interactive-answer--order" data-order-game>
          <div class="interactive-answer__head">
            <b>رتّب الجمل بالترتيب الصحيح</b>
            <span>اسحب العنصر أو استخدم الأسهم</span>
          </div>
          <div class="order-list" data-order-list>
            @foreach($orderItems as $item)
              <div class="order-item" draggable="true" data-order-key="{{ $item['key'] }}">
                <span class="order-item__handle">↕</span>
                <span class="order-item__text">{{ $item['text'] }}</span>
                <span class="order-item__tools">
                  <button type="button" data-order-up title="رفع">↑</button>
                  <button type="button" data-order-down title="نزول">↓</button>
                </span>
              </div>
            @endforeach
          </div>
          <div class="interactive-answer__actions">
            <button class="btn btn--fire" type="button" data-check-order>تحقق من الترتيب</button>
            <span class="interactive-answer__result" data-order-result></span>
          </div>
        </section>
        <div class="action-bar">
          <a class="btn btn--fire btn--lg" href="{{ route('game.answer', [$game, $gq]) }}">✔ عرض الإجابة</a>
        </div>
      @elseif($questionType === 'match' && $matchPairs->isNotEmpty())
        <section class="interactive-answer interactive-answer--match" data-match-game>
          <div class="interactive-answer__head">
            <b>وصّل كل عنصر بما يناسبه</b>
            <span>اختر من العمود الأول ثم اختر المقابل من العمود الثاني</span>
          </div>
          <div class="match-board">
            <div class="match-column">
              @foreach($matchLeftItems as $item)
                <button class="match-choice" type="button" data-match-left data-match-key="{{ $item['key'] }}">
                  <span class="match-choice__mark"></span>
                  <span>{{ $item['text'] }}</span>
                </button>
              @endforeach
            </div>
            <div class="match-column">
              @foreach($matchRightItems as $item)
                <button class="match-choice" type="button" data-match-right data-match-key="{{ $item['key'] }}">
                  <span class="match-choice__mark"></span>
                  <span>{{ $item['text'] }}</span>
                </button>
              @endforeach
            </div>
          </div>
          <div class="interactive-answer__actions">
            <button class="btn btn--fire" type="button" data-check-match>تحقق من التوصيل</button>
            <button class="btn btn--ghost" type="button" data-reset-match>إعادة التوصيل</button>
            <span class="interactive-answer__result" data-match-result></span>
          </div>
        </section>
        <div class="action-bar">
          <a class="btn btn--fire btn--lg" href="{{ route('game.answer', [$game, $gq]) }}">✔ عرض الإجابة</a>
        </div>
      @elseif($question->hasChoices())
        <form method="POST" action="{{ route('game.answer.store', [$game, $gq]) }}" id="revealAnswerForm" data-choice-form>
          @csrf
          <input type="hidden" name="selected_option_id" id="selectedOptionId" value="{{ $gq->selected_option_id }}">
          <div class="answers" data-answers>
            @foreach($question->options as $i => $option)
              @continue(!filled($option->option_text))
              <button
                type="button"
                class="answer {{ (int) $gq->selected_option_id === (int) $option->id ? 'selected' : '' }}"
                data-option-id="{{ $option->id }}"
              >
                <span class="answer__key">{{ $keys[$i] ?? ($i + 1) }}</span>
                <span class="answer__text">{{ $option->option_text }}</span>
                <span class="answer__mark"></span>
              </button>
            @endforeach
          </div>
          <p class="choice-hint" data-choice-hint>اختار إجابة وبعدين اضغط عرض الإجابة</p>
          <div class="action-bar">
            <button class="btn btn--fire btn--lg" type="submit">✔ عرض الإجابة</button>
          </div>
        </form>
      @elseif(in_array($questionType, ['complete', 'puzzle', 'image_guess'], true))
        <form method="POST" action="{{ route('game.answer.store', [$game, $gq]) }}" id="revealAnswerForm">
          @csrf
          <label class="player-answer-field">
            <span>إجابتك (اختياري)</span>
            <input type="text" name="player_answer" value="{{ old('player_answer', $gq->player_answer) }}" placeholder="اكتب إجابتك هنا…">
          </label>
          <div class="action-bar">
            <button class="btn btn--fire btn--lg" type="submit">✔ عرض الإجابة</button>
          </div>
        </form>
      @else
        <div class="action-bar">
          <a class="btn btn--fire btn--lg" href="{{ route('game.answer', [$game, $gq]) }}">✔ عرض الإجابة</a>
        </div>
      @endif
    @endif
  </main>
</div>
</x-layouts.game>
