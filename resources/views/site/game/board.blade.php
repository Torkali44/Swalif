<x-layouts.game>
@php
  $rows = [
    ['cells' => $easyCells, 'points' => 200],
    ['cells' => $mediumCells, 'points' => 400],
    ['cells' => $hardCells, 'points' => 600],
  ];
  $teams = $game->teams->values();
  $teamA = $teams->get(0);
  $teamB = $teams->get(1);
@endphp

<div
  class="board-page"
  @if(!empty($freeLeaveWarn))
    data-free-leave-guard="1"
    data-free-leave-message="{{ $leaveWarningMessage }}"
  @endif
>
  <header class="board-bar">
    <div class="board-bar__start">
      <a href="{{ route('home') }}" class="board-bar__logo" title="سوالف" data-free-leave-link>
        <img src="{{ asset('images/mainLogo.jpg') }}" alt="سوالف" width="58" height="58" decoding="async">
      </a>
      @if($activeTeam)
        <span class="board-bar__turn">دور فريق: {{ $activeTeam->name }}</span>
      @endif
    </div>

    <div class="board-bar__center">
      @if(session('success'))
        <span class="board-bar__notice is-success">{{ session('success') }}</span>
      @elseif(session('error'))
        <span class="board-bar__notice is-error">{{ session('error') }}</span>
      @else
        <strong class="board-bar__title">{{ $game->category->name_ar }}</strong>
      @endif
    </div>

    <div class="board-bar__actions">
      <button type="button" id="themeToggle" class="board-bar__icon-btn" title="تبديل المظهر" aria-label="تبديل المظهر">🌙</button>
      <a class="board-bar__action" href="{{ route('game.result', $game) }}" title="إنهاء اللعبة">
        <span class="board-bar__action-ico">🏁</span>
        <span>إنهاء اللعبة</span>
      </a>
      <a class="board-bar__action board-bar__action--exit" href="{{ route('home') }}" title="خروج" data-free-leave-link>
        <span class="board-bar__action-ico">⏻</span>
        <span>خروج</span>
      </a>
    </div>
  </header>

  <div class="board-page__body">
    <div class="board-grid" aria-label="لوحة اللعب">
      <div class="board-grid__center">
        @if($game->category->imageUrl())
          <img src="{{ $game->category->imageUrl() }}" alt="{{ $game->category->name_ar }}">
        @else
          <div class="board-grid__fallback">
            <span>{{ $game->category->icon ?: '🎯' }}</span>
            <strong>{{ $game->category->name_ar }}</strong>
          </div>
        @endif
      </div>

      @foreach($rows as $rowIndex => $row)
        @php
          $validCells = collect($row['cells'])->filter(fn($c) => !empty($c['question']))->values();
          $count = $validCells->count();

          $rightCount = (int) ceil($count / 2);
          $leftCount  = (int) floor($count / 2);

          $rightCols = [3, 2, 1];
          $leftCols  = [5, 6, 7];

          $placedTiles = [];

          for ($i = 0; $i < $rightCount; $i++) {
              $placedTiles[] = [
                  'cell' => $validCells->get($i),
                  'col'  => $rightCols[$i],
                  'row'  => $rowIndex + 1,
              ];
          }

          for ($j = 0; $j < $leftCount; $j++) {
              $placedTiles[] = [
                  'cell' => $validCells->get($rightCount + $j),
                  'col'  => $leftCols[$j],
                  'row'  => $rowIndex + 1,
              ];
          }
        @endphp

        @foreach($placedTiles as $tile)
          @php
            $cell = $tile['cell'];
            $gridCol = $tile['col'];
            $gridRow = $tile['row'];
          @endphp
          @if($cell['used'])
            <div class="board-tile is-used"
                 style="grid-column:{{ $gridCol }};grid-row:{{ $gridRow }}"
                 aria-disabled="true"
                 title="تم الإجابة">✓</div>
          @else
            <a class="board-tile"
               style="grid-column:{{ $gridCol }};grid-row:{{ $gridRow }}"
               href="{{ route('game.question', [$game, $cell['question']]) }}">
              {{ $row['points'] }}
            </a>
          @endif
        @endforeach
      @endforeach
    </div>

    <div class="board-teams">
      @foreach([[$teamA, 'a'], [$teamB, 'b']] as [$team, $side])
        @continue(!$team)
        <section class="board-team board-team--{{ $side }} {{ $activeTeam?->id === $team->id ? 'is-turn' : '' }}">
          <div class="board-team__meta">
            <div class="board-team__avatar">{{ mb_substr($team->name, 0, 1) }}</div>
            <div>
              <h3 class="board-team__name">{{ $team->name }}</h3>
              @if($activeTeam?->id === $team->id)
                <span class="board-team__badge">دورهم الآن</span>
              @endif
            </div>
          </div>

          <div class="board-team__tools" data-team-id="{{ $team->id }}" data-game-id="{{ $game->id }}">
            <span class="board-team__tools-label">وسائل المساعدة</span>
            <div class="board-team__helpers">
              <button type="button" class="board-helper {{ ($team->helpers_left['swap'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="swap" title="تبديل السؤال">🔄</button>
              <button type="button" class="board-helper {{ ($team->helpers_left['phone_friend'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="phone_friend" title="اتصال بصديق">📞</button>
              <button type="button" class="board-helper {{ ($team->helpers_left['two_answers'] ?? 1) <= 0 ? 'is-used' : '' }}" data-helper="two_answers" title="إجابتين">✌️</button>
            </div>
          </div>

          <div class="board-team__score" data-team-id="{{ $team->id }}" data-game-id="{{ $game->id }}">
            <button type="button" class="score-btn minus" data-amount="-100">−</button>
            <span class="score-val">{{ $team->score }}</span>
            <button type="button" class="score-btn plus" data-amount="100">+</button>
          </div>
        </section>
      @endforeach
    </div>
  </div>
</div>

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

  document.querySelectorAll('.board-team__helpers').forEach(function (tools) {
    tools.querySelectorAll('.board-helper').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openHelperPromptModal();
      });
    });
  });
});
</script>
</x-layouts.game>
