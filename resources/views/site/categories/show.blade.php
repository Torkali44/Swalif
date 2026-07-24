@php
  $palettes = [
    ['#0F6B4C', '#084A34'], ['#1E3A5F', '#0F2440'], ['#0E7490', '#155E75'],
    ['#B45309', '#92400E'], ['#7C2D12', '#9A3412'], ['#6D28D9', '#5B21B6'],
    ['#BE185D', '#9D174D'], ['#0369A1', '#075985'], ['#15803D', '#166534'],
    ['#C2410C', '#9A3412'], ['#334155', '#1E293B'], ['#A16207', '#854D0E'],
  ];
  $palette = $palettes[($category->sort_order ?: $category->id) % count($palettes)];
@endphp

<x-layouts.app>
<section class="page-hero category-show-hero">
  <div class="container category-show-wrap">
    <x-back-button :href="route('categories.index')" />
    <div class="cat-circle cat-circle--lg" style="--c1:{{ $palette[0] }};--c2:{{ $palette[1] }}">
      <div class="cat-circle__ring">
        @if($category->imageUrl())
          <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}">
        @else
          <span class="cat-circle__emoji">{{ $category->icon ?: '🎯' }}</span>
        @endif
      </div>
      <div class="cat-circle__label">
        @if($category->classification && $category->classification->imageUrl())
          <img src="{{ $category->classification->imageUrl() }}" alt="{{ $category->classificationName() }}" class="cat-circle__class-img" style="width:32px;height:32px;border-radius:8px;object-fit:cover;margin:0 auto 4px">
        @elseif($category->classification && $category->classification->icon)
          <span class="cat-circle__num">{{ $category->classification->icon }}</span>
        @else
          <span class="cat-circle__num">{{ $category->classificationName() }}</span>
        @endif
        <span class="cat-circle__name">{{ $category->name_ar }}</span>
      </div>
    </div>

    <p class="category-show-desc">{{ $category->description }}</p>

    <div class="category-show-actions">
      @auth
        <a class="btn btn--primary btn--lg" href="{{ route('game.setup', $category) }}">ابدأ لعبة جديدة</a>
      @else
        <a class="btn btn--primary btn--lg" href="{{ route('login') }}">سجّل دخولك للعب</a>
      @endauth
      <a class="btn btn--outline btn--lg" href="{{ route('categories.index') }}">كل الفئات</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container center">
    <header class="section-head">
      <h2>نظام <span class="grad-text">النقاط</span></h2>
      <p>كل مستوى يعطيك نقاطًا مختلفة حسب الصعوبة</p>
    </header>
    <div class="level-cards">
      <div><b>200</b><span>سهل</span></div>
      <div><b>400</b><span>متوسط</span></div>
      <div><b>600</b><span>صعب</span></div>
    </div>
  </div>
</section>
</x-layouts.app>
