<x-layouts.game>
@php
  $teams = $game->teams->values();
  $teamA = $teams->get(0);
  $teamB = $teams->get(1);
@endphp

<div class="cg-board-wrapper">
  <!-- Header Bar (RTL: Start is RIGHT, End is LEFT) -->
  <header class="cg-header-bar">
    <!-- Right side (in RTL): Logo & Turn Badge -->
    <div class="cg-header-start">
      <a href="{{ route('home') }}" class="cg-logo-link" title="سوالف">
        <img src="{{ asset('images/mainLogo.jpg') }}" alt="سوالف" class="cg-header-logo">
      </a>
      @if($activeTeam)
        <div class="cg-turn-badge">دور فريق : {{ $activeTeam->name }}</div>
      @endif
    </div>

    <!-- Center: Game Name / Title -->
    <div class="cg-header-center">
      <h1 class="cg-game-title">{{ $game->name }}</h1>
    </div>

    <!-- Left side (in RTL): Action Buttons & Theme Toggle -->
    <div class="cg-header-end">
      <button type="button" id="themeToggle" class="cg-theme-btn" title="تبديل المظهر" aria-label="تبديل المظهر">🌙</button>
      
      <a class="cg-nav-btn cg-nav-btn--finish" href="{{ route('custom-game.result', $game) }}" title="إنهاء اللعبة">
        <span>🏁</span>
        <span>انتهاء اللعبة</span>
      </a>

      <a href="{{ route('categories.index') }}" class="cg-nav-btn" title="الرجوع للوحة">
        <span>▦</span>
        <span>الرجوع للوحة</span>
      </a>

      <a href="{{ route('home') }}" class="cg-nav-btn cg-nav-btn--exit" title="خروج">
        <span>⏻</span>
        <span>الخروج</span>
      </a>
    </div>
  </header>

  <div class="cg-board-body">
    @if(session('success'))
      <div class="cg-alert is-success">{{ session('success') }}</div>
    @elseif(session('error'))
      <div class="cg-alert is-error">{{ session('error') }}</div>
    @endif

    <!-- All Categories Grid (4, 5, or 6 Categories Displayed Together) -->
    <div class="cg-categories-grid">
      @foreach($categoriesData as $catData)
        @php
          $cat = $catData['category'];

          $easyCells = collect($catData['easyCells'])->filter(fn($c) => !empty($c['question']))->values();
          $medCells  = collect($catData['mediumCells'])->filter(fn($c) => !empty($c['question']))->values();
          $hardCells = collect($catData['hardCells'])->filter(fn($c) => !empty($c['question']))->values();

          // Left tiles (Q1 of each difficulty)
          $leftEasy  = $easyCells->get(0);
          $leftMed   = $medCells->get(0);
          $leftHard  = $hardCells->get(0);

          // Right tiles (Q2 of each difficulty)
          $rightEasy = $easyCells->get(1);
          $rightMed  = $medCells->get(1);
          $rightHard = $hardCells->get(1);

          $leftTiles  = [$leftEasy, $leftMed, $leftHard];
          $rightTiles = [$rightEasy, $rightMed, $rightHard];
          $points     = [200, 400, 600];
        @endphp

        <div class="cg-cat-card">
          <!-- Left Column Scores (200, 400, 600) -->
          <div class="cg-col-tiles">
            @foreach($leftTiles as $idx => $cell)
              @php $pts = $points[$idx]; @endphp
              @if($cell)
                @if($cell['used'])
                  <div class="cg-tile is-used" aria-disabled="true" title="تم الإجابة">✓</div>
                @else
                  <a href="{{ route('custom-game.question', [$game, $cell['question']]) }}" class="cg-tile">
                    {{ $pts }}
                  </a>
                @endif
              @else
                <div class="cg-tile is-empty"></div>
              @endif
            @endforeach
          </div>

          <!-- Center Category Image + Title Banner -->
          <div class="cg-col-center">
            <div class="cg-cat-img-box">
              @if($cat->imageUrl())
                <img src="{{ $cat->imageUrl() }}" alt="{{ $cat->name_ar }}" class="cg-cat-img">
              @else
                <div class="cg-cat-fallback">
                  <span class="cg-cat-emoji">{{ $cat->icon ?: '🎯' }}</span>
                </div>
              @endif
              <div class="cg-cat-title-banner">{{ $cat->name_ar }}</div>
            </div>
          </div>

          <!-- Right Column Scores (200, 400, 600) -->
          <div class="cg-col-tiles">
            @foreach($rightTiles as $idx => $cell)
              @php $pts = $points[$idx]; @endphp
              @if($cell)
                @if($cell['used'])
                  <div class="cg-tile is-used" aria-disabled="true" title="تم الإجابة">✓</div>
                @else
                  <a href="{{ route('custom-game.question', [$game, $cell['question']]) }}" class="cg-tile">
                    {{ $pts }}
                  </a>
                @endif
              @else
                <div class="cg-tile is-empty"></div>
              @endif
            @endforeach
          </div>
        </div>
      @endforeach

      @foreach(($letterGridsData ?? collect()) as $lgData)
        @php
          $grid = $lgData['grid'];
          $finished = $lgData['finished'] ?? false;
          $session = $lgData['session'] ?? null;
          $replayMsg = $lgData['replay_message'] ?? null;
        @endphp
        <div class="cg-cat-card cg-letter-grid-card">
          <div class="cg-col-tiles">
            <div class="cg-tile is-empty"></div>
            <div class="cg-tile is-empty"></div>
            <div class="cg-tile is-empty"></div>
          </div>
          <div class="cg-col-center">
            @if($finished)
              <button type="button"
                class="cg-cat-img-box cg-letter-grid-link cg-letter-grid-done"
                data-replay-popup="{{ e($replayMsg ?: 'تم لعب هذه الشبكة مسبقاً.') }}">
                @if($grid->imageUrl())
                  <img src="{{ $grid->imageUrl() }}" alt="{{ $grid->name_ar }}" class="cg-cat-img">
                @else
                  <div class="cg-cat-fallback" style="background:linear-gradient(145deg,#FFB300,#FF6D00)">
                    <span class="cg-cat-emoji">🏆</span>
                  </div>
                @endif
                <div class="cg-cat-title-banner">{{ $grid->name_ar }} — انتهت</div>
              </button>
            @else
              <a href="{{ route('custom-game.letter-grid', [$game, $grid]) }}" class="cg-cat-img-box cg-letter-grid-link">
                @if($grid->imageUrl())
                  <img src="{{ $grid->imageUrl() }}" alt="{{ $grid->name_ar }}" class="cg-cat-img">
                @else
                  <div class="cg-cat-fallback" style="background:linear-gradient(145deg,#FFB300,#FF6D00)">
                    <span class="cg-cat-emoji">⬡</span>
                  </div>
                @endif
                <div class="cg-cat-title-banner">{{ $grid->name_ar }}</div>
              </a>
            @endif
          </div>
          <div class="cg-col-tiles">
            <div class="cg-tile is-empty"></div>
            <div class="cg-tile is-empty"></div>
            <div class="cg-tile is-empty"></div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Bottom Section: Teams Score & Helper Tools -->
    <div class="cg-teams-row">
      @foreach([[$teamA, '1'], [$teamB, '2']] as [$team, $teamNum])
        @continue(!$team)
        <div class="cg-team-card {{ $activeTeam?->id === $team->id ? 'is-turn' : '' }}">
          <div class="cg-team-badge">{{ $team->name }}</div>
          
          <div class="cg-team-controls">
            <div class="cg-team-score-box" data-team-id="{{ $team->id }}" data-game-id="{{ $game->id }}" data-game-type="custom">
              <button type="button" class="cg-score-btn minus" data-amount="-100">−</button>
              <span class="cg-score-val score-val">{{ $team->score }}</span>
              <button type="button" class="cg-score-btn plus" data-amount="100">+</button>
            </div>

            <div class="cg-team-helpers" data-team-id="{{ $team->id }}" data-game-id="{{ $game->id }}" data-game-type="custom">
              <span class="cg-helpers-label">وسائل المساعدة</span>
              <div class="cg-helpers-group">
                <button type="button" class="cg-helper-btn {{ ($team->helpers_left['swap'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="swap" title="تبديل السؤال">🔄</button>
                <button type="button" class="cg-helper-btn {{ ($team->helpers_left['phone_friend'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="phone_friend" title="اتصال بصديق">📞</button>
                <button type="button" class="cg-helper-btn {{ ($team->helpers_left['two_answers'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="two_answers" title="إجابتين">✌️</button>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

<style>
.cg-board-wrapper {
  direction: rtl;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg-main, #F8FAFC);
  color: var(--fg, #1E293B);
  font-family: 'Cairo', sans-serif;
  transition: background-color .25s ease, color .25s ease;
}

/* ── Dark Mode Root Rules ─────────────────────────────── */
body.dark .cg-board-wrapper,
html.dark .cg-board-wrapper {
  background: #0F172A;
  color: #F8FAFC;
}

/* ── Header ─────────────────────────────────────────── */
.cg-header-bar {
  background: linear-gradient(135deg, #FF3B30 0%, #FF6B00 100%);
  color: #fff;
  padding: 14px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 4px 24px rgba(255, 59, 48, 0.28);
  flex-wrap: wrap;
  gap: 16px;
}

.cg-header-start {
  display: flex;
  align-items: center;
  gap: 14px;
}

.cg-header-end {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cg-header-logo {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  object-fit: cover;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.cg-turn-badge {
  background: #B71C1C;
  color: #fff;
  padding: 6px 20px;
  border-radius: 50px;
  font-weight: 800;
  font-size: 15px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25);
  white-space: nowrap;
}

.cg-header-center {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.cg-game-title {
  margin: 0;
  font-size: 26px;
  font-weight: 900;
  color: #fff;
  letter-spacing: -0.5px;
}

.cg-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #fff;
  text-decoration: none;
  font-weight: 800;
  font-size: 14px;
  background: rgba(255, 255, 255, 0.2);
  padding: 8px 18px;
  border-radius: 50px;
  transition: all .2s ease;
  border: 1px solid rgba(255, 255, 255, 0.3);
  white-space: nowrap;
}

.cg-nav-btn:hover {
  background: rgba(255, 255, 255, 0.38);
  color: #fff;
  transform: translateY(-1px);
}

.cg-theme-btn {
  background: rgba(255, 255, 255, 0.22);
  border: 1px solid rgba(255, 255, 255, 0.35);
  color: #fff;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  transition: all .2s ease;
}

.cg-theme-btn:hover {
  background: rgba(255, 255, 255, 0.4);
  transform: scale(1.08);
}

/* ── Body Layout ────────────────────────────────────── */
.cg-board-body {
  width: 100%;
  max-width: 1600px;
  margin: 0 auto;
  padding: 24px 32px 32px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 24px;
}

.cg-alert {
  padding: 12px 20px;
  border-radius: 16px;
  margin-bottom: 20px;
  font-weight: 700;
}
.cg-alert.is-success { background: #D1FAE5; color: #065F46; }
.cg-alert.is-error { background: #FEE2E2; color: #991B1B; }

body.dark .cg-alert.is-success { background: #064E3B; color: #A7F3D0; }
body.dark .cg-alert.is-error { background: #7F1D1D; color: #FCA5A5; }

/* ── Categories Grid ────────────────────────────────── */
.cg-categories-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 24px;
  width: 100%;
}

.cg-cat-card {
  flex: 1 1 calc(33.333% - 24px);
  max-width: calc(33.333% - 16px);
  min-width: 340px;
  background: #E5E7EB;
  border-radius: 24px;
  padding: 14px 16px;
  display: grid;
  grid-template-columns: 60px 1fr 60px;
  gap: 12px;
  align-items: center;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
  border: 1.5px solid rgba(0, 0, 0, 0.08);
  transition: all .25s ease;
}

body.dark .cg-cat-card,
html.dark .cg-cat-card {
  background: #1E293B;
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.cg-col-tiles {
  display: flex;
  flex-direction: column;
  gap: 10px;
  justify-content: center;
}

.cg-tile {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 50px;
  background: #fff;
  color: #B71C1C;
  font-weight: 900;
  font-size: 17px;
  border-radius: 50px;
  text-decoration: none;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
  transition: transform .15s ease, background-color .15s ease, color .15s ease;
  border: 1.5px solid #D1D5DB;
}

body.dark .cg-tile,
html.dark .cg-tile {
  background: #334155;
  color: #FF6B6B;
  border-color: #475569;
}

.cg-tile:hover {
  transform: scale(1.06);
  background: #FEF2F2;
  color: #991B1B;
  border-color: #FCA5A5;
}

body.dark .cg-tile:hover,
html.dark .cg-tile:hover {
  background: #475569;
  color: #FF8787;
  border-color: #64748B;
}

.cg-tile.is-used {
  background: #D1D5DB;
  color: #6B7280;
  box-shadow: none;
  border-color: transparent;
  pointer-events: none;
}

body.dark .cg-tile.is-used,
html.dark .cg-tile.is-used {
  background: #1E293B;
  color: #475569;
  border-color: transparent;
}

.cg-tile.is-empty {
  background: transparent;
  border: none;
  box-shadow: none;
}

.cg-col-center {
  height: 100%;
}

.cg-cat-img-box {
  position: relative;
  width: 100%;
  height: 180px;
  border-radius: 18px;
  overflow: hidden;
  background: #D0E2F7;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
}

a.cg-letter-grid-link {
  text-decoration: none;
  color: inherit;
  display: flex;
  transition: transform .18s ease, box-shadow .18s ease;
}
button.cg-letter-grid-link {
  border: 0;
  padding: 0;
  cursor: pointer;
  font: inherit;
  width: 100%;
  text-align: inherit;
}
a.cg-letter-grid-link:hover,
button.cg-letter-grid-link:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 24px rgba(255, 109, 0, 0.25);
}

body.dark .cg-cat-img-box,
html.dark .cg-cat-img-box {
  background: #0F172A;
}

.cg-cat-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cg-cat-fallback {
  text-align: center;
}

.cg-cat-emoji {
  font-size: 52px;
}

.cg-cat-title-banner {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(135deg, #FF5722 0%, #FF7043 100%);
  color: #fff;
  text-align: center;
  padding: 7px 10px;
  font-weight: 800;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  box-shadow: 0 -3px 10px rgba(0, 0, 0, 0.2);
}

/* ── Teams Score Row ────────────────────────────────── */
.cg-teams-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
  margin-top: auto;
  padding-top: 12px;
}

@media (max-width: 768px) {
  .cg-teams-row {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}

.cg-team-card {
  background: #E5E7EB;
  border-radius: 28px;
  padding: 18px 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  border: 2.5px solid transparent;
  transition: all .2s ease;
  box-shadow: 0 4px 16px rgba(0,0,0,0.04);
}

body.dark .cg-team-card,
html.dark .cg-team-card {
  background: #1E293B;
  border-color: rgba(255, 255, 255, 0.08);
}

.cg-team-card.is-turn {
  border-color: #FF5722;
  box-shadow: 0 6px 24px rgba(255, 87, 34, 0.25);
}

.cg-team-badge {
  background: #B71C1C;
  color: #fff;
  padding: 8px 24px;
  border-radius: 50px;
  font-weight: 900;
  font-size: 18px;
  text-align: center;
  display: inline-block;
  align-self: center;
  min-width: 140px;
  box-shadow: 0 3px 10px rgba(183, 28, 28, 0.3);
}

.cg-team-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.cg-team-score-box {
  display: inline-flex;
  align-items: center;
  background: #fff;
  border-radius: 50px;
  padding: 6px 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  border: 1.5px solid #D1D5DB;
  gap: 16px;
}

body.dark .cg-team-score-box,
html.dark .cg-team-score-box {
  background: #334155;
  border-color: #475569;
}

.cg-score-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: #B71C1C;
  color: #fff;
  font-size: 20px;
  font-weight: 900;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform .15s;
}

.cg-score-btn:hover {
  transform: scale(1.12);
}

.cg-score-val {
  font-size: 22px;
  font-weight: 900;
  color: #1E293B;
  min-width: 44px;
  text-align: center;
}

body.dark .cg-score-val,
html.dark .cg-score-val {
  color: #F8FAFC;
}

.cg-team-helpers {
  display: flex;
  align-items: center;
  gap: 10px;
}

.cg-helpers-label {
  font-weight: 700;
  font-size: 14px;
  color: #4B5563;
}

body.dark .cg-helpers-label,
html.dark .cg-helpers-label {
  color: #94A3B8;
}

.cg-helpers-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.cg-helper-btn {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 1.5px solid #D1D5DB;
  background: #fff;
  font-size: 18px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all .15s ease;
}

body.dark .cg-helper-btn,
html.dark .cg-helper-btn {
  background: #334155;
  border-color: #475569;
  color: #F8FAFC;
}

.cg-helper-btn:hover {
  transform: scale(1.12);
  border-color: #FF5722;
}

.cg-helper-btn.is-used {
  opacity: 0.35;
  pointer-events: none;
  background: #E5E7EB;
}

body.dark .cg-helper-btn.is-used,
html.dark .cg-helper-btn.is-used {
  background: #1E293B;
  border-color: transparent;
}

/* ── Responsive adjustments for tablet & mobile ───────── */
@media (max-width: 1100px) {
  .cg-cat-card {
    flex: 1 1 calc(50% - 18px);
    max-width: calc(50% - 12px);
    min-width: 280px;
  }
  .cg-board-body {
    padding: 20px 20px 24px;
    gap: 20px;
  }
}

@media (max-width: 600px) {
  .cg-board-body {
    padding: 12px 10px 16px;
    gap: 16px;
  }

  .cg-header-bar {
    padding: 10px 14px;
    gap: 10px;
  }

  .cg-header-logo {
    width: 42px;
    height: 42px;
    border-radius: 10px;
  }

  .cg-turn-badge {
    font-size: 12px;
    padding: 4px 12px;
  }

  .cg-game-title {
    font-size: 18px;
  }

  .cg-nav-btn {
    padding: 6px 10px;
    font-size: 12px;
  }

  .cg-theme-btn {
    width: 34px;
    height: 34px;
    font-size: 15px;
  }

  .cg-categories-grid {
    gap: 10px;
  }

  .cg-cat-card {
    flex: 1 1 calc(50% - 6px);
    max-width: calc(50% - 4px);
    min-width: 140px;
    grid-template-columns: 40px 1fr 40px;
    padding: 6px 8px;
    gap: 6px;
    border-radius: 16px;
  }

  .cg-col-tiles {
    gap: 6px;
  }

  .cg-tile {
    height: 34px;
    font-size: 13px;
    font-weight: 800;
  }

  .cg-cat-img-box {
    height: 125px;
    border-radius: 12px;
  }

  .cg-cat-title-banner {
    font-size: 11px;
    padding: 4px 4px;
  }

  .cg-cat-emoji {
    font-size: 36px;
  }

  .cg-teams-row {
    grid-template-columns: 1fr;
    gap: 12px;
    margin-top: auto;
  }

  .cg-team-card {
    padding: 12px 16px;
    border-radius: 20px;
    gap: 8px;
  }

  .cg-team-badge {
    font-size: 15px;
    padding: 5px 16px;
    min-width: 110px;
  }

  .cg-team-controls {
    justify-content: center;
    gap: 10px;
  }

  .cg-team-score-box {
    padding: 4px 12px;
    gap: 10px;
  }

  .cg-score-btn {
    width: 32px;
    height: 32px;
    font-size: 18px;
  }

  .cg-score-val {
    font-size: 18px;
    min-width: 36px;
  }

  .cg-helper-btn {
    width: 36px;
    height: 36px;
    font-size: 15px;
  }
}

@media (max-width: 420px) {
  .cg-nav-btn span:last-child {
    display: none;
  }
  .cg-nav-btn {
    padding: 6px 8px;
    border-radius: 50%;
  }

  .cg-cat-card {
    grid-template-columns: 34px 1fr 34px;
    padding: 5px 4px;
    gap: 4px;
  }

  .cg-tile {
    height: 34px;
    font-size: 11px;
  }

  .cg-cat-img-box {
    height: 110px;
  }

  .cg-cat-title-banner {
    font-size: 10px;
    padding: 3px 2px;
  }
}
</style>

<!-- Board Helper Prompt Modal -->
<div class="board-helper-prompt-modal" id="boardHelperPromptModal" hidden>
  <div class="board-helper-prompt-backdrop" id="boardHelperPromptBackdrop"></div>
  <div class="board-helper-prompt-card">
    <div class="prompt-icon">🎯</div>
    <h3>استخدام وسائل المساعدة</h3>
    <p>يلا ابدأ اللعب وافتح سؤالاً لتستطيع استخدام وسائل المساعدة!</p>
    <button type="button" class="btn btn--fire" id="closeBoardHelperPromptBtn">فهمت، اختر سؤالاً</button>
  </div>
</div>

<style>
.board-helper-prompt-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px; }
.board-helper-prompt-modal[hidden] { display: none !important; }
.board-helper-prompt-backdrop { position: absolute; inset: 0; background: rgba(15,17,23,.75); backdrop-filter: blur(8px); }
.board-helper-prompt-card { position: relative; z-index: 10; width: 100%; max-width: 420px; background: #181E2B; color: #fff; border-radius: 28px; padding: 32px 24px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,.5); animation: modalPop .3s ease; }
.prompt-icon { font-size: 3.8rem; margin-bottom: 12px; }
.board-helper-prompt-card h3 { font-size: 1.5rem; font-weight: 900; color: #FF6D00; margin-bottom: 8px; }
.board-helper-prompt-card p { font-size: 1rem; color: #CBD5E1; margin-bottom: 24px; line-height: 1.5; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var promptModal = document.getElementById('boardHelperPromptModal');
  var closePromptBtn = document.getElementById('closeBoardHelperPromptBtn');
  var promptBackdrop = document.getElementById('boardHelperPromptBackdrop');

  function openHelperPromptModal() {
    if (promptModal) promptModal.hidden = false;
  }

  if (closePromptBtn) closePromptBtn.addEventListener('click', function() { if (promptModal) promptModal.hidden = true; });
  if (promptBackdrop) promptBackdrop.addEventListener('click', function() { if (promptModal) promptModal.hidden = true; });

  /* ── Helper buttons (custom game board prompt) ───────── */
  document.querySelectorAll('.cg-team-helpers').forEach(function (tools) {
    tools.querySelectorAll('.cg-helper-btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openHelperPromptModal();
      });
    });
  });

  /* ── Score adjustment (custom game) ─────────────────── */
  document.querySelectorAll('.cg-team-score-box[data-game-type="custom"]').forEach(function (scoreBox) {
    var gameId   = scoreBox.dataset.gameId;
    var teamId   = scoreBox.dataset.teamId;
    var scoreVal = scoreBox.querySelector('.score-val');

    scoreBox.querySelectorAll('.cg-score-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var amount = parseInt(btn.dataset.amount) || 0;
        var url    = '/custom-game/' + gameId + '/team/' + teamId + '/adjust-score';

        fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ amount: amount }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success && scoreVal) {
            scoreVal.textContent = data.score;
          }
        });
      });
    });
  });

  document.querySelectorAll('[data-replay-popup]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var msg = btn.getAttribute('data-replay-popup') || 'تم لعب هذه الشبكة مسبقاً.';
      if (typeof window.showPopup === 'function') {
        window.showPopup(msg, 'error');
      } else {
        alert(msg);
      }
    });
  });

  @if(session('letter_grid_replay_popup'))
    if (typeof window.showPopup === 'function') {
      window.showPopup(@json(session('letter_grid_replay_popup')), 'error');
    }
  @endif
});
</script>

</x-layouts.game>
