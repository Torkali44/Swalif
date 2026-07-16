<x-layouts.game>
<div class="game-shell question-screen">
  <div class="question-topbar">
    <a class="back-link" href="{{ route('game.board', $game) }}">← الرجوع للوحة</a>
    <div class="timer" data-timer="{{ $question->time_limit }}">{{ $question->time_limit }}</div>
    <span class="chip points-chip">{{ $question->points }} نقطة</span>
  </div>

  <article class="question-card">
    <span class="question-cat">{{ $game->category->name_ar }}</span>
    <h1>{{ $question->question_text }}</h1>

    @if($question->imageUrl())
      <figure class="question-media">
        <img src="{{ $question->imageUrl() }}" alt="صورة السؤال" loading="lazy">
      </figure>
    @endif

    @if($question->hasChoices())
      <div class="options">
        @foreach($question->options as $option)
          @continue(!filled($option->option_text))
          <div>{{ $option->option_text }}</div>
        @endforeach
      </div>
    @endif

    <a class="btn btn--answer btn--lg" href="{{ route('game.answer', [$game, $gq]) }}">الإجابة</a>
  </article>
</div>
</x-layouts.game>
