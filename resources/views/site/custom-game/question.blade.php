@php
  $teams = $customGame->teams->values();
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

  // فئة هذا السؤال
  $questionCategory = $cgq->category;
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
        <small>{{ $customGame->name }}</small>
        <b>{{ $questionCategory?->name_ar }}</b>
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

  <div class="meta-row">
    <div class="q-index">
      السؤال <b>{{ $currentIndex }}</b> <span>من</span> <b>{{ $totalQuestions }}</b>
      <div class="q-progress"><i style="width:{{ $progress }}%"></i></div>
    </div>

    <div
      class="timer"
      id="playTimer"
      data-timer-ring="{{ $timeLimit }}"
      data-answer-url="{{ route('custom-game.answer', [$customGame, $cgq]) }}"
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
          <div><b>ركّز، الفرصة مرة وحدة فقط</b></div>
        </div>
        <div class="question-card__media question-card__media--video">
          <div class="question-media__header">
            <span>🎬 فيديو السؤال</span>
            <small>شغّل الفيديو ثم تابع</small>
          </div>
          <video
            id="questionVideo"
            class="question-media question-media--video"
            src="{{ $question->mediaUrl() }}"
            controls
            playsinline
            preload="metadata"
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
          <a class="btn btn--fire btn--lg" href="{{ route('custom-game.answer', [$customGame, $cgq]) }}">✔ عرض الإجابة</a>
        </div>
      </div>
    @else
      <span class="question-card__label">السؤال</span>
      <h1 class="question-card__text">{{ $question->question_text }}</h1>

      @if($question->isAudio() && $question->mediaUrl())
        <div class="question-card__media question-card__media--audio">
          <div class="question-media__header">
            <span>🎧 صوت السؤال</span>
            <small>اضغط على المشغل للاستماع</small>
          </div>
          <audio class="question-media question-media--audio" src="{{ $question->mediaUrl() }}" controls preload="metadata" controlsList="nodownload"></audio>
        </div>
      @elseif($question->imageUrl())
        <div class="question-card__media">
          <img src="{{ $question->imageUrl() }}" alt="صورة السؤال" loading="eager" decoding="async" fetchpriority="high">
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
          <a class="btn btn--fire btn--lg" href="{{ route('custom-game.answer', [$customGame, $cgq]) }}">✔ عرض الإجابة</a>
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
          <a class="btn btn--fire btn--lg" href="{{ route('custom-game.answer', [$customGame, $cgq]) }}">✔ عرض الإجابة</a>
        </div>
      @elseif($question->hasChoices())
        <form method="POST" action="{{ route('custom-game.answer.store', [$customGame, $cgq]) }}" id="revealAnswerForm" data-choice-form>
          @csrf
          <input type="hidden" name="selected_option_id" id="selectedOptionId" value="{{ $cgq->selected_option_id }}">
          <div class="answers" data-answers>
            @foreach($question->options as $i => $option)
              @continue(!filled($option->option_text))
              <button
                type="button"
                class="answer {{ (int) $cgq->selected_option_id === (int) $option->id ? 'selected' : '' }}"
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
        <form method="POST" action="{{ route('custom-game.answer.store', [$customGame, $cgq]) }}" id="revealAnswerForm">
          @csrf
          <label class="player-answer-field">
            <span>إجابتك (اختياري)</span>
            <input type="text" name="player_answer" value="{{ old('player_answer', $cgq->player_answer) }}" placeholder="اكتب إجابتك هنا…">
          </label>
          <div class="action-bar">
            <button class="btn btn--fire btn--lg" type="submit">✔ عرض الإجابة</button>
          </div>
        </form>
      @else
        <div class="action-bar">
          <a class="btn btn--fire btn--lg" href="{{ route('custom-game.answer', [$customGame, $cgq]) }}">✔ عرض الإجابة</a>
        </div>
      @endif
    @endif
  </main>
</div>

<!-- Custom Lifeline Confirmation Modal -->
<div class="lifeline-confirm-modal" id="lifelineConfirmModal" hidden>
  <div class="lifeline-confirm-backdrop" id="lifelineConfirmBackdrop"></div>
  <div class="lifeline-confirm-card">
    <div class="confirm-question-icon">❓</div>
    <h3 id="lifelineConfirmTitle">متأكد تبي تستخدم وسيلة المساعدة؟</h3>
    <div class="confirm-actions">
      <button type="button" class="btn btn--fire" id="lifelineConfirmYesBtn">نعم</button>
      <button type="button" class="btn btn--ghost" id="lifelineConfirmNoBtn">إلغاء</button>
    </div>
  </div>
</div>

<!-- Floating Phone a Friend 60s Widget (Non-blocking, top of screen so question text is readable) -->
<div class="phone-friend-floating-banner" id="phoneFriendFloatingBanner" hidden>
  <div class="phone-banner-inner">
    <span class="phone-banner-icon">📞</span>
    <div class="phone-banner-text">
      <strong>اتصال بصديق</strong>
      <small>اقرأ السؤال لصديقك! المتبقي:</small>
    </div>
    <div class="phone-banner-timer" id="phoneTimerValue">60</div>
    <button type="button" class="phone-banner-close" id="closePhoneBanner">&times;</button>
  </div>
</div>

<style>
/* Confirmation Modal */
.lifeline-confirm-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px; }
.lifeline-confirm-modal[hidden] { display: none !important; }
.lifeline-confirm-backdrop { position: absolute; inset: 0; background: rgba(15,17,23,.7); backdrop-filter: blur(6px); }
.lifeline-confirm-card { position: relative; z-index: 10; width: 100%; max-width: 380px; background: #181E2B; color: #fff; border-radius: 28px; padding: 28px 22px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,.5); animation: modalPop .25s ease; }
.confirm-question-icon { font-size: 3rem; margin-bottom: 10px; }
.lifeline-confirm-card h3 { font-size: 1.25rem; font-weight: 800; color: #fff; margin-bottom: 20px; line-height: 1.45; }
.confirm-actions { display: flex; align-items: center; justify-content: center; gap: 12px; }
.confirm-actions .btn { min-width: 110px; padding: 10px 20px; border-radius: 50px; font-weight: 800; font-size: .95rem; }

/* Non-blocking Floating Phone Timer Banner (60s) */
.phone-friend-floating-banner {
  position: fixed;
  top: 16px;
  right: 16px;
  z-index: 9990;
  background: linear-gradient(135deg, #181E2B 0%, #0F172A 100%);
  color: #fff;
  border-radius: 20px;
  padding: 12px 20px;
  box-shadow: 0 12px 36px rgba(0,0,0,.5), 0 0 0 2px rgba(255,109,0,.4);
  border: 1px solid rgba(255,255,255,.15);
  animation: bannerSlideIn .3s ease;
}
.phone-friend-floating-banner[hidden] { display: none !important; }
@keyframes bannerSlideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
.phone-banner-inner { display: flex; align-items: center; gap: 14px; }
.phone-banner-icon { font-size: 2rem; animation: phoneRing 1s infinite alternate; }
@keyframes phoneRing { from { transform: scale(1); } to { transform: scale(1.15) rotate(15deg); } }
.phone-banner-text strong { display: block; font-size: .95rem; color: #fff; }
.phone-banner-text small { color: #94A3B8; font-size: .8rem; }
.phone-banner-timer { font-size: 2rem; font-weight: 900; color: #FF6D00; min-width: 48px; text-align: center; }
.phone-banner-close { background: rgba(255,255,255,.12); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var confirmModal    = document.getElementById('lifelineConfirmModal');
  var confirmTitle    = document.getElementById('lifelineConfirmTitle');
  var confirmYesBtn   = document.getElementById('lifelineConfirmYesBtn');
  var confirmNoBtn    = document.getElementById('lifelineConfirmNoBtn');
  var confirmBackdrop = document.getElementById('lifelineConfirmBackdrop');

  var phoneBanner     = document.getElementById('phoneFriendFloatingBanner');
  var closePhoneBtn   = document.getElementById('closePhoneBanner');
  var phoneTimerEl    = document.getElementById('phoneTimerValue');
  var phoneTimerInt   = null;

  var pendingAction   = null;

  function showConfirm(titleText, onConfirm) {
    confirmTitle.textContent = titleText;
    pendingAction = onConfirm;
    confirmModal.hidden = false;
  }

  function hideConfirm() {
    confirmModal.hidden = true;
    pendingAction = null;
  }

  if (confirmYesBtn) {
    confirmYesBtn.addEventListener('click', function() {
      var act = pendingAction;
      hideConfirm(); // Immediately close confirm modal FIRST
      if (act) act();
    });
  }

  if (confirmNoBtn) confirmNoBtn.addEventListener('click', hideConfirm);
  if (confirmBackdrop) confirmBackdrop.addEventListener('click', hideConfirm);

  function finishPhoneCall() {
    phoneBanner.hidden = true;
    if (phoneTimerInt) {
      clearInterval(phoneTimerInt);
      phoneTimerInt = null;
    }
    if (window.swalifQuestionTimer && typeof window.swalifQuestionTimer.resetAndResume === 'function') {
      window.swalifQuestionTimer.resetAndResume(30);
    }
  }

  function startPhoneTimer() {
    if (window.swalifQuestionTimer && typeof window.swalifQuestionTimer.pause === 'function') {
      window.swalifQuestionTimer.pause();
    }
    phoneBanner.hidden = false;
    var count = 60;
    phoneTimerEl.textContent = count;
    if (phoneTimerInt) clearInterval(phoneTimerInt);
    phoneTimerInt = setInterval(function() {
      count--;
      phoneTimerEl.textContent = count;
      if (count <= 0) {
        finishPhoneCall();
      }
    }, 1000);
  }

  if (closePhoneBtn) {
    closePhoneBtn.addEventListener('click', function() {
      finishPhoneCall();
    });
  }

  /* Helper buttons for custom game */
  document.querySelectorAll('.team__lifelines[data-game-type="custom"]').forEach(function (lifelines) {
    var gameId = lifelines.dataset.gameId;
    var teamId = lifelines.dataset.teamId;
    var cgqId  = '{{ $cgq->id }}';

    lifelines.querySelectorAll('.helper-btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (btn.classList.contains('used')) return;

        var helper = btn.dataset.helper;
        var helperName = helper === 'two_answers' ? 'إجابتين' : (helper === 'swap' ? 'تبديل السؤال' : 'اتصال بصديق');

        // Check 4-choice rule BEFORE confirmation dialog
        if (helper === 'two_answers') {
          var availOptions = document.querySelectorAll('[data-answers] [data-option-id]');
          if (!availOptions || availOptions.length !== 4) {
            showToast('وسيلة حذف إجابتين تتاح فقط في الأسئلة ذات الـ 4 اختيارات ✌️', 'error');
            return;
          }
        }

        var promptText = helper === 'swap'
          ? 'متأكد تبي تستخدم وسيلة المساعدة "تبديل السؤال"؟ (سيتم إلغاء السؤال والانتقال للوحة)'
          : 'متأكد تبي تستخدم وسيلة المساعدة "' + helperName + '"؟';

        showConfirm(promptText, function() {
          var url = '/custom-game/' + gameId + '/team/' + teamId + '/use-helper/' + helper;

          fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cgq_id: cgqId })
          })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) {
              btn.classList.add('used');
              if (helper === 'swap' && data.redirect_url) {
                if (window.SwalifAudio) window.SwalifAudio.play('spin');
                window.location.href = data.redirect_url;
              } else if (helper === 'two_answers' && data.remove_option_ids) {
                if (window.SwalifAudio) window.SwalifAudio.play('select');
                data.remove_option_ids.forEach(function(optId) {
                  var optBtn = document.querySelector('[data-option-id="' + optId + '"]');
                  if (optBtn) {
                    optBtn.style.opacity = '0.35';
                    optBtn.style.pointerEvents = 'none';
                    optBtn.style.textDecoration = 'line-through';
                    optBtn.style.filter = 'grayscale(1)';
                    optBtn.insertAdjacentHTML('beforeend', '<span style="color:#FF1744;font-weight:900;margin-left:auto">❌ ملغاة</span>');
                  }
                });
              } else if (helper === 'phone_friend') {
                if (window.SwalifAudio) window.SwalifAudio.play('phone');
                startPhoneTimer();
              }
            } else {
              showToast(data.message || 'لقد استخدمت هذه المساعدة بالفعل.', 'error');
            }
          })
          .catch(function () {
            showToast('حدث خطأ أثناء تفعيل المساعدة.', 'error');
          });
        });
      });
    });
  });

  function showToast(msg, type) {
    var stack = document.getElementById('toastStack');
    if (!stack) {
      stack = document.createElement('div');
      stack.id = 'toastStack';
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    var toast = document.createElement('div');
    toast.className = 'toast toast--' + (type || 'error');
    toast.innerHTML = '<span class="toast__icon">' + (type === 'success' ? '✅' : '⚠️') + '</span><span class="toast__msg">' + msg + '</span><button type="button" class="toast__close">&times;</button>';
    stack.appendChild(toast);
    setTimeout(function () { toast.classList.add('is-visible'); }, 60);
    setTimeout(function () { toast.remove(); }, 4200);
  }
});
</script>
</x-layouts.game>
