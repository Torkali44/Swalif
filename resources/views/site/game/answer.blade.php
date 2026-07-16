<x-layouts.game>
<div class="game-shell question-screen">
  <div class="question-topbar">
    @unless($gameQuestion->answered_at)
      <a class="back-link" href="{{ route('game.question', [$game, $gameQuestion->question]) }}">← الرجوع للسؤال</a>
    @endunless
    <a class="back-link" href="{{ route('game.board', $game) }}">اللوحة</a>
    <span class="chip points-chip">{{ $gameQuestion->question->points }} نقطة</span>
  </div>

  <article class="question-card answer-card">
    <span class="chip">الإجابة الصحيحة</span>

    @if($gameQuestion->question->answerImageUrl())
      <figure class="question-media">
        <img src="{{ $gameQuestion->question->answerImageUrl() }}" alt="صورة الإجابة" loading="lazy">
      </figure>
    @elseif($gameQuestion->question->imageUrl())
      <figure class="question-media">
        <img src="{{ $gameQuestion->question->imageUrl() }}" alt="صورة السؤال" loading="lazy">
      </figure>
    @endif

    @if($gameQuestion->question->correctAnswerText())
      <div class="correct-answer">{{ $gameQuestion->question->correctAnswerText() }}</div>
    @endif

    @if($gameQuestion->answered_at)
      <p class="answered-note">
        تم احتساب السؤال مسبقاً
        @if($gameQuestion->team)
          — النقاط لفريق <b>{{ $gameQuestion->team->name }}</b> ({{ $gameQuestion->points_awarded }})
        @else
          — بدون نقاط
        @endif
      </p>
      <a class="btn btn--primary btn--lg" href="{{ route('game.board', $game) }}">العودة للوحة</a>
    @else
      <div class="answer-actions">
        <a class="btn btn--outline" href="{{ route('game.question', [$game, $gameQuestion->question]) }}">الرجوع للسؤال</a>
      </div>

      <form method="POST" action="{{ route('game.assign', [$game, $gameQuestion]) }}" id="assignForm">
        @csrf
        <input type="hidden" name="team_id" id="assignTeamId" value="">
        <h2>أي فريق أجاب بشكل صحيح؟</h2>
        <div class="team-buttons">
          @foreach($game->teams as $team)
            <button class="btn btn-primary assign-btn" type="button" data-team-id="{{ $team->id }}">{{ $team->name }}</button>
          @endforeach
          <button class="btn btn-outline assign-btn" type="button" data-team-id="">لا أحد</button>
        </div>
      </form>
    @endif
  </article>
</div>
</x-layouts.game>
