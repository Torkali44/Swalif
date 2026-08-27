@php
  $totalCells = $game->cells->count();
  $resolvedCells = $resolvedCells ?? $game->resolvedCount();
@endphp

<x-layouts.game :show-nav="true">
  <div class="hex-game-page">
    <div class="hex-game" data-hex-game data-game-id="{{ $game->id }}"
      data-result-url="{{ route('letter-grid.result', $game) }}" data-time-limit="{{ $timeLimit ?? 30 }}"
      data-csrf="{{ csrf_token() }}">

      {{-- Mobile Top Bar Indicator (visible only on mobile) --}}
      <div class="hex-mobile-turn-bar">
        <div class="hex-mobile-turn-bar__item">
          <span class="hex-mobile-turn-bar__label">الجولة</span>
          <strong>{{ $roundLabel }}</strong>
        </div>
        <div class="hex-mobile-turn-bar__item hex-mobile-turn-bar__item--turn">
          <span class="hex-mobile-turn-bar__label">الدور الحالي</span>
          <strong data-hex-mobile-turn-name>{{ $turnTeam?->name ?? 'الفريق' }}</strong>
        </div>
        <div class="hex-mobile-turn-bar__item">
          <span class="hex-mobile-turn-bar__label">المحسوم</span>
          <strong><span data-hex-mobile-resolved>{{ $resolvedCells }}</span>/{{ $totalCells }}</strong>
        </div>
      </div>

      <div class="hex-game__layout">
        {{-- Game Board Section (Right in RTL) --}}
        <section class="hex-game__board-wrap">
          <div class="hex-game__board-bg"></div>
          <div class="hex-game__board-mesh"></div>
          <div class="hex-game__board">
            @foreach($cellsByRow as $rowIndex => $rowCells)
              <div class="hex-row" data-row="{{ $rowIndex }}" style="--row-offset: {{ ($rowIndex % 2) === 1 ? 1 : 0 }}">
                @foreach($rowCells->sortBy('col') as $cell)
                  @php
                    $teamIndex = null;
                    if ($cell->claimed_team_id) {
                      $teamIndex = $teams->search(fn($t) => (int) $t->id === (int) $cell->claimed_team_id);
                    }
                    $isActive = $activeCell && (int) $activeCell->id === (int) $cell->id;
                    $isResolved = $cell->isResolved();
                    $isMissed = $cell->isMissed();
                  @endphp
                  <button type="button" class="hex-cell
                        {{ $cell->isClaimed() ? 'is-claimed is-team-' . max(0, $teamIndex) : '' }}
                        {{ $isMissed ? 'is-missed' : '' }}
                        {{ $isActive ? 'is-active' : '' }}" data-hex-cell data-cell-id="{{ $cell->id }}"
                    data-letter="{{ $cell->letter }}" data-question="{{ e($cell->question_text) }}"
                    data-answer="{{ e($cell->answer_text) }}" data-resolved="{{ $isResolved ? '1' : '0' }}" {{ $isResolved ? 'disabled' : '' }}
                    aria-label="الحرف {{ $cell->letter }}">
                    <span class="hex-cell__inner">
                      <span class="hex-cell__letter">{{ $cell->letter }}</span>
                    </span>
                  </button>
                @endforeach
              </div>
            @endforeach
          </div>
        </section>

        {{-- Control & Question Panel (Left in RTL) --}}
        <aside class="hex-game__panel">
          {{-- Panel Header --}}
          <header class="hex-game__panel-head">
            <div class="hex-game__head-info">
              <span class="hex-game__grid-tag">⬡ {{ $game->grid?->name_ar ?? 'شبكة الحروف' }}</span>
              <h2 class="hex-game__round">{{ $roundLabel }}</h2>
            </div>
            <div class="hex-panel-turn-tag">
              <span class="hex-panel-turn-dot"></span>
              <span>الدور:</span>
              <strong data-hex-panel-turn-name>{{ $turnTeam?->name ?? 'الفريق' }}</strong>
            </div>
          </header>

          {{-- Timer Ring --}}
          <div class="hex-game__timer" data-hex-timer-wrap hidden>
            <div class="hex-game__timer-ring">
              <svg viewBox="0 0 80 80">
                <defs>
                  <linearGradient id="hexTimerGrad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#FFB300" />
                    <stop offset="100%" stop-color="#FF1744" />
                  </linearGradient>
                </defs>
                <circle cx="40" cy="40" r="34" class="hex-game__timer-track" />
                <circle cx="40" cy="40" r="34" class="hex-game__timer-bar" data-hex-timer-bar />
              </svg>
              <span data-hex-timer-value>{{ $timeLimit ?? 30 }}</span>
            </div>
            <div class="hex-game__timer-meta">
              <strong>الوقت المتبقي</strong>
              <small>ثانية للإجابة</small>
            </div>
          </div>

          {{-- Question Card --}}
          <div class="hex-game__question-card" data-hex-question-card>
            <div class="hex-game__question-letter" data-hex-active-letter>
              {{ $activeCell?->letter ?? '؟' }}
            </div>
            <p class="hex-game__question-text" data-hex-question-text>
              @if($activeCell)
                {{ $activeCell->question_text }}
              @else
                اختر حرفاً من الشبكة السداسية لبدء السؤال
              @endif
            </p>
            <div class="hex-game__answer-reveal" data-hex-answer-reveal hidden>
              <span class="hex-game__answer-label">الإجابة النموذجية:</span>
              <strong data-hex-answer-text>{{ $activeCell?->answer_text }}</strong>
            </div>
          </div>

          {{-- Action Buttons --}}
          <div class="hex-game__actions">
            <button type="button" class="hex-btn hex-btn--gold" data-hex-new-question {{ $activeCell ? '' : 'disabled' }}>
              🎲 حرف عشوائي
            </button>
            <button type="button" class="hex-btn hex-btn--fire" data-hex-show-answer {{ $activeCell ? '' : 'disabled' }}>
              💡 عرض الإجابة
            </button>
          </div>

          {{-- Claim Panel (Voting / Scoring Action Card) --}}
          <div class="hex-game__claim hex-claim-card" data-hex-claim-panel hidden>
            <div class="hex-claim-head">
              <span class="hex-claim-icon">🎯</span>
              <div class="hex-claim-title-wrap">
                <h4 class="hex-claim-title">من صاحب الإجابة الصحيحة؟</h4>
                <p class="hex-claim-subtitle">اختر الفريق لاحتساب الحرف أو اختر إجابة خاطئة</p>
              </div>
            </div>
            <div class="hex-game__claim-btns">
              @foreach($teams as $index => $team)
                <button type="button" class="hex-claim-btn hex-claim-btn--team-{{ $index }}" data-hex-claim-team
                  data-team-id="{{ $team->id }}" data-team-index="{{ $index }}">
                  <span class="hex-claim-avatar-circle"
                    style="width:38px;height:38px;min-width:38px;max-width:38px;border-radius:50%;overflow:hidden;flex-shrink:0;display:grid;place-items:center;background: {{ $team->character?->accentGradient() ?: ($index === 0 ? 'linear-gradient(135deg,#FF1744,#FF6D00)' : 'linear-gradient(135deg,#FFB300,#FF6D00)') }}">
                    @if($team->character && $team->character->imageUrl())
                      <img src="{{ $team->character->imageUrl() }}" alt="{{ $team->character->name_ar }}" class="hex-avatar-img" width="38" height="38" style="width:38px;height:38px;object-fit:cover;border-radius:50%;display:block">
                    @else
                      <span class="hex-avatar-emoji">{{ $team->character?->icon ?: ($index === 0 ? '🔥' : '⭐') }}</span>
                    @endif
                  </span>
                  <div class="hex-claim-team-info">
                    <span class="hex-claim-team-name">{{ $team->name }}</span>
                    <span class="hex-claim-action-label">منح الحرف لهذا الفريق</span>
                  </div>
                  <span class="hex-claim-badge">+1 حرف</span>
                </button>
              @endforeach
              <button type="button" class="hex-claim-btn hex-claim-btn--none" data-hex-claim-none>
                <span class="hex-claim-avatar-circle hex-claim-avatar-circle--none">❌</span>
                <div class="hex-claim-team-info">
                  <span class="hex-claim-team-name">لا أحد / إجابة خاطئة</span>
                  <span class="hex-claim-action-label">حسم الحرف دون نقاط</span>
                </div>
              </button>
            </div>
          </div>

          {{-- Distinct Scoreboard & Turn Section --}}
          <div class="hex-scoreboard-card">
            <div class="hex-scoreboard-head">
              <div class="hex-scoreboard-title">
                <span class="hex-scoreboard-icon">📊</span>
                <span>لوحة النتائج والدور</span>
              </div>
              <span class="hex-scoreboard-status-tag">
                الدور: <b data-hex-turn-indicator-text>{{ $turnTeam?->name ?? 'فريق 1' }}</b>
              </span>
            </div>

            {{-- Teams Score Bars --}}
            <div class="hex-game__teams">
              @foreach($teams as $index => $team)
                @php
                  $isTurn = $turnTeam && (int) $turnTeam->id === (int) $team->id;
                @endphp
                <div class="hex-team-bar hex-team-bar--{{ $index }} {{ $isTurn ? 'is-turn' : '' }}" data-hex-team-bar
                  data-team-index="{{ $index }}" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}">
                  <div class="hex-team-avatar-circle"
                    style="width:40px;height:40px;min-width:40px;max-width:40px;border-radius:50%;overflow:hidden;flex-shrink:0;display:grid;place-items:center;background: {{ $team->character?->accentGradient() ?: ($index === 0 ? 'linear-gradient(135deg,#FF1744,#FF6D00)' : 'linear-gradient(135deg,#FFB300,#FF6D00)') }}">
                    @if($team->character && $team->character->imageUrl())
                      <img src="{{ $team->character->imageUrl() }}" alt="{{ $team->character->name_ar }}" class="hex-avatar-img" width="40" height="40" style="width:40px;height:40px;object-fit:cover;border-radius:50%;display:block">
                    @else
                      <span class="hex-avatar-emoji">{{ $team->character?->icon ?: ($index === 0 ? '🔥' : '⭐') }}</span>
                    @endif
                  </div>
                  <div class="hex-team-bar__details">
                    <div class="hex-team-bar__name-row">
                      <span class="hex-team-bar__name">{{ $team->name }}</span>
                      <span class="hex-team-bar__turn-pill" data-hex-turn-pill {{ $isTurn ? '' : 'hidden' }}>⚡ الدور الحالي</span>
                    </div>
                    <span class="hex-team-bar__turn-desc" data-hex-turn-desc>
                      {{ $isTurn ? 'حان دوره لاختيار الحرف والإجابة' : 'ينتظر دوره' }}
                    </span>
                  </div>
                  <div class="hex-team-bar__score-badge">
                    <span class="hex-team-bar__score-title">النقاط</span>
                    <span class="hex-team-bar__score" data-hex-team-score="{{ $team->id }}">{{ $team->score }}</span>
                  </div>
                </div>
              @endforeach
            </div>

            {{-- Letters Progress --}}
            <div class="hex-game__progress">
              <span class="hex-progress-bar-wrap">
                <span class="hex-progress-bar-fill" data-hex-progress-bar
                  style="width: {{ $totalCells > 0 ? round(($resolvedCells / $totalCells) * 100) : 0 }}%"></span>
              </span>
              <div class="hex-progress-label">
                <span>الحروف المحسومة:</span>
                <b><span data-hex-progress>{{ $resolvedCells }}</span> من {{ $totalCells }} حرف</b>
              </div>
            </div>
          </div>

          {{-- End Game / Results Button --}}
          <div class="hex-game__footer-actions">
            <a href="{{ route('letter-grid.result', $game) }}" class="hex-btn-end-game" data-hex-end-game
              title="إنهاء اللعبة الحالية وعرض النتيجة">
              <span class="hex-end-icon">🏆</span>
              <span class="hex-end-text">إنهاء اللعبة لعرض النتيجة</span>
            </a>
          </div>
        </aside>
      </div>
    </div>
  </div>
</x-layouts.game>