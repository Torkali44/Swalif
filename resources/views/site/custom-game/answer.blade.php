@php
  $teams = $customGame->teams->values();
  $teamA = $teams->get(0);
  $teamB = $teams->get(1);
  $question = $customGameQuestion->question;
  $points = $question->displayPoints();
  $activeTeam = ($answeredQuestions % 2 === 0) ? 'a' : 'b';
  $turnTeam = $teams->count() > 0
    ? $teams->get($answeredQuestions % $teams->count())
    : null;
  $suggestTurnTeam = $playerCorrect === true && $turnTeam;
  $questionCategory = $customGameQuestion->category;
  $remainingUnanswered = $customGame->customGameQuestions->whereNull('answered_at')->count();
  $isLastQuestionToAssign = ! $customGameQuestion->answered_at && $remainingUnanswered <= 1;
@endphp

<x-layouts.game :show-nav="true">
<div class="play-stage">
  <header class="topbar">
    <div class="category-badge">
      <span class="category-badge__icon">
        @if($questionCategory?->imageUrl())
          <img src="{{ $questionCategory->imageUrl() }}" alt="">
        @else
          {{ $questionCategory?->icon ?: '🎯' }}
        @endif
      </span>
      <div>
        <small>الإجابة • {{ $customGame->name }}</small>
        <b>{{ $questionCategory?->name_ar }}</b>
      </div>
    </div>
    <span class="level-chip">
      <em>{{ $points }} نقطة</em>
    </span>
    <a class="icon-btn" href="{{ route('custom-game.board', $customGame) }}" title="اللوحة">
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
        <div class="team__lifelines" data-team-id="{{ $teamA->id }}" data-game-id="{{ $customGame->id }}" data-game-type="custom">
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
        <div class="team__lifelines" data-team-id="{{ $teamB->id }}" data-game-id="{{ $customGame->id }}" data-game-type="custom">
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

    @if($question->hasChoices() && $customGameQuestion->selected_option_id)
      @php $choseCorrect = $playerCorrect === true; @endphp
      <div class="player-verdict {{ $choseCorrect ? 'is-correct' : 'is-wrong' }}">
        @if($choseCorrect)
          ✔ إجابتك صحيحة
          @if($turnTeam)
            <small>دور  {{ $turnTeam->name }} — اختَرهم عشان تتحسب لهم صح</small>
          @endif
        @else
          ✕ إجابتك مش صحيحة
          @if($turnTeam)
            <small>تتحسب خاطئة على {{ $turnTeam->name }} — والنقاط للفريق اللي أجاب صح</small>
          @endif
        @endif
        @if($customGameQuestion->selectedOption)
          <small>اختيارك: {{ $customGameQuestion->selectedOption->option_text }}</small>
        @endif
      </div>
    @elseif($question->type === 'word_build')
      @php $choseCorrect = $playerCorrect === true; @endphp
      <div class="player-verdict {{ $choseCorrect ? 'is-correct' : 'is-wrong' }}">
        @if($choseCorrect)
          ✔ إجابتك صحيحة — وجدت كل الكلمات
          @if($turnTeam)
            <small>دور {{ $turnTeam->name }} — اختَرهم عشان تتحسب لهم صح</small>
          @endif
        @else
          ✕ لم تكتمل كل الكلمات المطلوبة
          @if($turnTeam)
            <small>تتحسب خاطئة على {{ $turnTeam->name }} إلا لو فريق تاني جاوب صح</small>
          @endif
        @endif
      </div>
    @elseif(filled($customGameQuestion->player_answer))
      <div class="player-verdict is-neutral">
        إجابتك: <b>{{ $customGameQuestion->player_answer }}</b>
      </div>
    @elseif($turnTeam)
      <div class="player-verdict is-neutral">
        دور فريق <b>{{ $turnTeam->name }}</b> — 
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
    @elseif($question->type === 'word_build' && $question->validWords())
      <div class="correct-answer correct-answer--word-build">
        <span class="correct-answer__label">الجواب:</span>
        <div class="word-build-answers">
          @foreach($question->validWords() as $word)
            <span class="word-build-answer-chip">{{ $word }}</span>
          @endforeach
        </div>
      </div>
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

    @if($customGameQuestion->answered_at)
      <p style="color:#C8CFE7;font-weight:700;margin-bottom:20px">
        تم احتساب السؤال مسبقاً
        @if($customGameQuestion->team)
          — النقاط لفريق <b>{{ $customGameQuestion->team->name }}</b> ({{ $customGameQuestion->points_awarded }})
        @else
          — بدون نقاط
        @endif
      </p>
      <div class="action-bar" style="justify-content:center">
        <a class="btn btn--fire btn--lg" href="{{ route('custom-game.board', $customGame) }}">العودة للوحة</a>
      </div>
    @else
      <div class="action-bar" style="justify-content:center;margin-bottom:18px">
        <a class="btn btn--ghost" href="{{ route('custom-game.question', [$customGame, $question]) }}">← الرجوع للسؤال</a>
      </div>
      <h3>من الفريق اللي أجاب صح؟</h3>
      @if($suggestTurnTeam)
        <p style="color:#7CFFB2;font-weight:700;margin-bottom:12px">
         
        </p>
      @endif
      <form method="POST" action="{{ route('custom-game.assign', [$customGame, $customGameQuestion]) }}" id="assignForm" @if($isLastQuestionToAssign) data-last-question="1" @endif>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.team__lifelines').forEach(function (lifelines) {
    lifelines.querySelectorAll('.helper-btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var stack = document.getElementById('toastStack');
        if (!stack) {
          stack = document.createElement('div');
          stack.id = 'toastStack';
          stack.className = 'toast-stack';
          document.body.appendChild(stack);
        }
        var toast = document.createElement('div');
        toast.className = 'toast toast--error';
        toast.innerHTML = '<span class="toast__icon">🎯</span><span class="toast__msg">وسائل المساعدة تُستخدم فقط أثناء عرض السؤال!</span>';
        stack.appendChild(toast);
        setTimeout(function () { toast.classList.add('is-visible'); }, 60);
        setTimeout(function () { toast.remove(); }, 3500);
      });
    });
  });
});
</script>
</x-layouts.game>
