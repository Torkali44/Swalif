@php
  $palettes = [
    ['#0F6B4C', '#084A34'], ['#1E3A5F', '#0F2440'], ['#0E7490', '#155E75'],
    ['#B45309', '#92400E'], ['#7C2D12', '#9A3412'], ['#6D28D9', '#5B21B6'],
    ['#BE185D', '#9D174D'], ['#0369A1', '#075985'], ['#15803D', '#166534'],
    ['#C2410C', '#9A3412'], ['#334155', '#1E293B'], ['#A16207', '#854D0E'],
    ['#7F1D1D', '#991B1B'], ['#4338CA', '#3730A3'],
  ];
@endphp

<x-layouts.admin>
  <x-slot:heading>الفئات</x-slot:heading>
  <x-slot:subheading>إدارة موضوعات اللعبة وتصنيفها</x-slot:subheading>

  <form class="toolbar" method="GET" action="{{ route('admin.categories.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث بالاسم…">
    <select class="select" name="group">
      <option value="">كل التصنيفات</option>
      <option value="uae" @selected(($filters['group'] ?? '') === 'uae')>إمارات</option>
      <option value="general" @selected(($filters['group'] ?? '') === 'general')>عامة</option>
    </select>
    <select class="select" name="status">
      <option value="">كل الحالات</option>
      <option value="active" @selected(($filters['status'] ?? '') === 'active')>مفعّلة</option>
      <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>موقوفة</option>
    </select>
    <button class="btn btn-outline" type="submit">تصفية</button>
    <a class="btn btn-ghost" href="{{ route('admin.categories.index') }}">إعادة</a>
    <div class="spacer"></div>
    <a class="btn btn-primary" href="{{ route('admin.categories.create') }}">+ فئة جديدة</a>
  </form>

  <div class="admin-circle-grid">
    @forelse($categories as $index => $category)
      @php $palette = $palettes[$index % count($palettes)]; @endphp
      <article class="admin-circle-card" style="--c1:{{ $palette[0] }};--c2:{{ $palette[1] }}">
        <span class="status-dot {{ $category->is_active ? '' : 'off' }}"></span>

        <div class="cat-circle cat-circle--admin">
          <div class="cat-circle__ring">
            @if($category->imageUrl())
              <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}">
            @else
              <span class="cat-circle__emoji">{{ $category->icon ?: '🎯' }}</span>
            @endif
          </div>
          <div class="cat-circle__label">
            <span class="cat-circle__num">{{ $index + 1 }}</span>
            <span class="cat-circle__name">{{ $category->name_ar }}</span>
          </div>
        </div>

        <p class="admin-circle-meta">
          {{ $category->group === 'uae' ? 'إمارات' : 'عامة' }}
          · {{ $category->questions_count }} سؤال
          · {{ $category->is_active ? 'مفعّلة' : 'موقوفة' }}
        </p>

        <div class="cat-actions">
          <a href="{{ route('admin.categories.edit', $category) }}">تعديل</a>
          <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('حذف الفئة؟')">حذف</button>
          </form>
        </div>
      </article>
    @empty
      <p class="muted">لا توجد فئات مطابقة للفلتر.</p>
    @endforelse
  </div>
</x-layouts.admin>
