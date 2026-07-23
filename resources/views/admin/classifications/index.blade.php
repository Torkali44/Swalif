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
  <x-slot:heading>التصنيفات</x-slot:heading>
  <x-slot:subheading>إدارة التصنيفات التي يتم اختيارها داخل الفئات</x-slot:subheading>

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.classifications.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث باسم التصنيف…">
    <select class="select" name="status">
      <option value="">كل الحالات</option>
      <option value="active" @selected(($filters['status'] ?? '') === 'active')>مفعّل</option>
      <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>موقوف</option>
    </select>
    <button class="btn btn-outline" type="submit">تصفية</button>
    <a class="btn btn-ghost" href="{{ route('admin.classifications.index') }}">إعادة</a>
    <div class="spacer"></div>
    <a class="btn btn-primary" href="{{ route('admin.classifications.create') }}">+ تصنيف جديد</a>
  </form>

  <div class="admin-circle-grid admin-circle-grid--compact">
    @forelse($classifications as $index => $classification)
      @php $palette = $palettes[$index % count($palettes)]; @endphp
      <article class="admin-circle-card" style="--c1:{{ $palette[0] }};--c2:{{ $palette[1] }}">
        <span class="status-dot {{ $classification->is_active ? '' : 'off' }}"></span>

        <div class="cat-circle cat-circle--admin">
          <div class="cat-circle__ring">
            @if($classification->imageUrl())
              <img src="{{ $classification->imageUrl() }}" alt="{{ $classification->name_ar }}">
            @else
              <span class="cat-circle__emoji">{{ $classification->icon ?: '🏷️' }}</span>
            @endif
          </div>
          <div class="cat-circle__label">
            <span class="cat-circle__num">{{ $index + 1 }}</span>
            <span class="cat-circle__name">{{ $classification->name_ar }}</span>
          </div>
        </div>

        <p class="admin-circle-meta">
          {{ $classification->categories_count }} فئة
        </p>

        <div class="cat-actions">
          <a class="btn btn-sm btn-outline" href="{{ route('admin.classifications.edit', $classification) }}">تعديل</a>
          <form method="POST" action="{{ route('admin.classifications.toggle', $classification) }}" class="inline">
            @csrf
            @method('PATCH')
            <button class="btn btn-sm btn-ghost" type="submit">{{ $classification->is_active ? 'إيقاف' : 'تفعيل' }}</button>
          </form>
          <form method="POST" action="{{ route('admin.classifications.destroy', $classification) }}" class="inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('حذف التصنيف؟')">حذف</button>
          </form>
        </div>
      </article>
    @empty
      <p class="muted">لا توجد تصنيفات مطابقة للفلتر.</p>
    @endforelse
  </div>
</x-layouts.admin>
