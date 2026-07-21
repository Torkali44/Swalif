@props([
    'category',
    'index' => 0,
    'filter' => null,
    'group' => null,
])

@php
    $palettes = [
        ['#0F6B4C', '#084A34'],
        ['#1E3A5F', '#0F2440'],
        ['#0E7490', '#155E75'],
        ['#B45309', '#92400E'],
        ['#7C2D12', '#9A3412'],
        ['#6D28D9', '#5B21B6'],
        ['#BE185D', '#9D174D'],
        ['#0369A1', '#075985'],
        ['#15803D', '#166534'],
        ['#C2410C', '#9A3412'],
        ['#334155', '#1E293B'],
        ['#A16207', '#854D0E'],
        ['#7F1D1D', '#991B1B'],
        ['#4338CA', '#3730A3'],
    ];
    $palette = $palettes[$index % count($palettes)];
    $number = $index + 1;
@endphp

<a href="{{ route('categories.show', $category) }}"
   {{ $attributes->class(['cat-circle'])->merge([
       'data-cat' => $filter,
       'data-group' => $group ?? $category->classificationName(),
       'style' => '--c1:'.$palette[0].';--c2:'.$palette[1],
   ]) }}>
  <div class="cat-circle__ring">
    @if($category->imageUrl())
      <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}">
    @else
      <span class="cat-circle__emoji">{{ $category->icon ?: '🎯' }}</span>
    @endif
  </div>
  <div class="cat-circle__label">
    <span class="cat-circle__num">{{ $number }}</span>
    <span class="cat-circle__name">{{ $category->name_ar }}</span>
  </div>
</a>
