@php
  $teams = $game->teams->values();
  $teamA = $teams->get(0);
  $teamB = $teams->get(1);
  $question = $gameQuestion->question;
  $points = $question->displayPoints();
  $activeTeam = ($answeredQuestions % 2 === 0) ? 'a' : 'b';
  $turnTeam = $teams->count() > 0
    ? $teams->get($answeredQuestions % $teams->count())
    : null;
  $suggestTurnTeam = $playerCorrect === true && $turnTeam;
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
      <span class="category-badge__icon">{{ $game->category->icon ?: '🎯' }}</span>
      <div>
        <small>الإجابة</small>
        <b>{{ $game->category->name_ar }}</b>
      </div>
    </div>
    <span class="level-chip">
      <em>{{ $points }} نقطة</em>
    </span>
    <a class="icon-btn" href="{{ route('game.board', $game) }}" title="اللوحة">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 6l12 12M6 18L18 6"/></svg>
    </a>
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

  <section class="assign-panel">
    <h3>الإجابة الصحيحة</h3>
    <p style="color:#C8CFE7;font-weight:700;margin-bottom:16px">نوع السؤال: {{ $question->typeLabel() }}</p>

    @if($question->hasChoices() && $gameQuestion->selected_option_id)
      @php $choseCorrect = $playerCorrect === true; @endphp
      <div class="player-verdict {{ $choseCorrect ? 'is-correct' : 'is-wrong' }}">
        @if($choseCorrect)
          ✔ إجابتك صحيحة
          @if($turnTeam)
            <small>دور فريق {{ $turnTeam->name }} — اختارهم عشان تتحسبلهم صح</small>
          @endif
        @else
          ✕ إجابتك مش صحيحة
          @if($turnTeam)
            <small>تتحسب خاطئة على {{ $turnTeam->name }} — والنقاط للفريق اللي أجاب صح</small>
          @endif
        @endif
        @if($gameQuestion->selectedOption)
          <small>اختيارك: {{ $gameQuestion->selectedOption->option_text }}</small>
        @endif
      </div>
    @elseif(filled($gameQuestion->player_answer))
      <div class="player-verdict is-neutral">
        إجابتك: <b>{{ $gameQuestion->player_answer }}</b>
      </div>
    @elseif($turnTeam)
      <div class="player-verdict is-neutral">
        دور فريق <b>{{ $turnTeam->name }}</b> — اختار مين أجاب صح (أو ولا فريق لو غلطوا)
      </div>
    @endif

    @if($question->answerImageUrl())
      <div class="question-card__media" style="margin:0 auto 20px;position:relative">
        <img src="{{ $question->answerImageUrl() }}" alt="صورة الإجابة" loading="lazy">
      </div>
    @elseif($question->imageUrl() && ! $question->hasChoices())
      <div class="question-card__media" style="margin:0 auto 20px;position:relative">
        <img src="{{ $question->imageUrl() }}" alt="صورة السؤال" loading="lazy">
      </div>
    @elseif(($question->isVideo() || $question->isAudio()) && $question->mediaUrl())
      <div class="question-card__media" style="margin:0 auto 20px">
        @if($question->isVideo())
          <video src="{{ $question->mediaUrl() }}" controls playsinline style="max-width:100%;border-radius:16px;max-height:280px"></video>
        @else
          <audio src="{{ $question->mediaUrl() }}" controls style="width:100%"></audio>
        @endif
      </div>
    @endif

    @if($question->type === 'order' && $question->orderItems())
      <ol class="correct-answer correct-answer--list">
        @foreach($question->orderItems() as $item)
          <li>{{ $item }}</li>
        @endforeach
      </ol>
    @elseif($question->type === 'match' && $question->matchPairs())
      <div class="correct-answer correct-answer--pairs">
        @foreach($question->matchPairs() as $pair)
          <div class="correct-answer__pair">
            <b>{{ $pair['left'] }}</b>
            <span>↔</span>
            <b>{{ $pair['right'] }}</b>
          </div>
        @endforeach
      </div>
    @elseif($question->correctAnswerText())
      <div class="correct-answer">
        <span class="correct-answer__label">نص الإجابة</span>
        {{ $question->correctAnswerText() }}
      </div>
    @endif

    @if($gameQuestion->answered_at)
      <p style="color:#C8CFE7;font-weight:700;margin-bottom:20px">
        تم احتساب السؤال مسبقاً
        @if($gameQuestion->team)
          — النقاط لفريق <b>{{ $gameQuestion->team->name }}</b> ({{ $gameQuestion->points_awarded }})
        @else
          — بدون نقاط
        @endif
      </p>
      <div class="action-bar" style="justify-content:center">
        <a class="btn btn--fire btn--lg" href="{{ route('game.board', $game) }}">العودة للوحة</a>
      </div>
    @else
      <div class="action-bar" style="justify-content:center;margin-bottom:18px">
        <a class="btn btn--ghost" href="{{ route('game.question', [$game, $question]) }}">← الرجوع للسؤال</a>
      </div>
      <h3>من الفريق اللي أجاب صح؟</h3>
      @if($suggestTurnTeam)
        <p style="color:#7CFFB2;font-weight:700;margin-bottom:12px">
          مقترح: {{ $turnTeam->name }} (دورهم + إجابة صحيحة)
        </p>
      @endif
      <form method="POST" action="{{ route('game.assign', [$game, $gameQuestion]) }}" id="assignForm">
        @csrf
        <input type="hidden" name="team_id" id="assignTeamId" value="{{ $suggestTurnTeam ? $turnTeam->id : '' }}">
        <div class="assign-grid">
          @foreach($teams as $index => $team)
            <button
              class="assign-btn {{ $index === 0 ? 'assign-btn--a' : 'assign-btn--b' }} {{ $suggestTurnTeam && (int) $turnTeam->id === (int) $team->id ? 'is-suggested' : '' }}"
              type="button"
              data-team-id="{{ $team->id }}">
              <b>{{ $team->name }}</b>
              <span>+{{ $points }} نقطة</span>
            </button>
          @endforeach
          <button class="assign-btn assign-btn--none" type="button" data-team-id="">
            <b>ولا فريق</b>
            <span>الإجابة خاطئة</span>
          </button>
        </div>
      </form>
    @endif
  </section>
</div>
</x-layouts.game>
