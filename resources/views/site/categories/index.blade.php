<x-layouts.app>
<section class="page-hero categories-hero">
  <div class="container">
    <div class="categories-hero__title">
      <span class="flag-deco" aria-hidden="true">🇦🇪</span>
      <div>
        <h1>فئات الأسئلة</h1>
        <p>اختر فئتك وابدأ التحدي</p>
      </div>
      <span class="flag-deco" aria-hidden="true">🇦🇪</span>
    </div>
  </div>
</section>

<section class="categories categories--circles">
  <div class="container">
    @foreach($categories->groupBy('group') as $group => $items)
      <h2 class="cat-group-title">{{ $group === 'uae' ? '🇦🇪 فئات الإمارات' : '🎯 فئات عامة' }}</h2>
      <div class="cat-circle-grid">
        @foreach($items as $index => $category)
          <x-category-circle :category="$category" :index="$index" :group="$group" />
        @endforeach
      </div>
    @endforeach
  </div>
</section>
</x-layouts.app>
