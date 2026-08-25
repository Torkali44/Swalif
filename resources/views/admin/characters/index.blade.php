@php
  $palettes = [
    ['#0F6B4C', '#084A34'], ['#1E3A5F', '#0F2440'], ['#0E7490', '#155E75'],
    ['#B45309', '#92400E'], ['#7C2D12', '#9A3412'], ['#6D28D9', '#5B21B6'],
    ['#BE185D', '#9D174D'], ['#0369A1', '#075985'], ['#15803D', '#166534'],
    ['#C2410C', '#9A3412'], ['#334155', '#1E293B'], ['#A16207', '#854D0E'],
  ];
@endphp

<x-layouts.admin>
  <x-slot:heading>الشخصيات</x-slot:heading>
  <x-slot:subheading>شخصيات اللاعبين — يختار منها كل مستخدم في حسابه</x-slot:subheading>

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.characters.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث باسم الشخصية…">
    <select class="select" name="status">
      <option value="">كل الحالات</option>
      <option value="active" @selected(($filters['status'] ?? '') === 'active')>مفعّل</option>
      <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>موقوف</option>
    </select>
    <button class="btn btn-outline" type="submit">تصفية</button>
    <a class="btn btn-ghost" href="{{ route('admin.characters.index') }}">إعادة</a>
    <div class="spacer"></div>
    <a class="btn btn-primary" href="{{ route('admin.characters.create') }}">+ شخصية جديدة</a>
  </form>

  <div class="admin-circle-grid admin-circle-grid--compact">
    @forelse($characters as $index => $character)
      @php $palette = $palettes[$index % count($palettes)]; @endphp
      <article class="admin-circle-card" style="--c1:{{ $palette[0] }};--c2:{{ $palette[1] }}">
        <span class="status-dot {{ $character->is_active ? '' : 'off' }}"></span>

        <div class="cat-circle cat-circle--admin">
          <div class="cat-circle__ring">
            @if($character->imageUrl())
              <img src="{{ $character->imageUrl() }}" alt="{{ $character->name_ar }}" width="136" height="136" decoding="async" loading="lazy">
            @else
              <span class="cat-circle__emoji" style="background:{{ $character->accentGradient() }}">{{ $character->icon ?: '🧑' }}</span>
            @endif
          </div>
          <div class="cat-circle__label">
            <span class="cat-circle__num">{{ (int) ($characters->firstItem() ?? 1) + $index }}</span>
            <span class="cat-circle__name">{{ $character->name_ar }}</span>
          </div>
        </div>

        <p class="admin-circle-meta">
          {{ $character->users_count }} لاعب
        </p>

        <div class="cat-actions">
          <a class="btn btn-sm btn-outline" href="{{ route('admin.characters.edit', $character) }}">تعديل</a>
          <form method="POST" action="{{ route('admin.characters.toggle', $character) }}" class="inline">
            @csrf
            @method('PATCH')
            <button class="btn btn-sm btn-ghost" type="submit">{{ $character->is_active ? 'إيقاف' : 'تفعيل' }}</button>
          </form>
          <form method="POST" action="{{ route('admin.characters.destroy', $character) }}" class="inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('حذف الشخصية؟')">حذف</button>
          </form>
        </div>
      </article>
    @empty
      <p class="muted">لا توجد شخصيات مطابقة للفلتر.</p>
    @endforelse
  </div>

  @if($characters->hasPages())
    <div class="admin-pagination">{{ $characters->links() }}</div>
  @endif
</x-layouts.admin>
