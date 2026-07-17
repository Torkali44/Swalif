@php
  $palettes = [
    ['#FF1744', '#B00020'], ['#FF6D00', '#B00020'], ['#8D6E63', '#3E2723'],
    ['#E64A19', '#7B1F1F'], ['#D4AF37', '#8A6D1B'], ['#00BCD4', '#006064'],
    ['#00C853', '#004D40'], ['#7C3AED', '#3D1E75'], ['#5D4037', '#2E1810'],
    ['#A0522D', '#3E2723'], ['#FF6D00', '#BF360C'], ['#263238', '#00E5FF'],
    ['#2E7D32', '#0D3D1A'], ['#455A64', '#1C313A'], ['#FF2D95', '#7C3AED'],
    ['#F57C00', '#6D4C1B'], ['#00E5FF', '#0064B7'],
  ];

  $filterOf = function ($category) {
      $slug = strtolower($category->slug ?? '');
      $name = $category->name_ar ?? '';
      if ($category->group === 'uae') return 'uae';
      if (str_contains($slug, 'quran') || str_contains($slug, 'verse') || str_contains($name, 'آية') || str_contains($name, 'سيرة') || str_contains($name, 'إسلام')) return 'religion';
      if (str_contains($slug, 'football') || str_contains($name, 'كرة') || str_contains($name, 'رياض')) return 'sport';
      if (str_contains($slug, 'guess') || str_contains($name, 'خمّن') || str_contains($name, 'خمن') || str_contains($name, 'صورة') || str_contains($name, 'صوت')) return 'media';
      if (str_contains($slug, 'disney') || str_contains($name, 'ألغاز') || str_contains($name, 'ترفيه')) return 'fun';
      return 'general';
  };

  $typeLabel = [
    'uae' => 'إمارات',
    'general' => 'عامة',
    'religion' => 'دينية',
    'sport' => 'رياضة',
    'fun' => 'ترفيه',
    'media' => 'صور/صوت',
  ];

  $totalQuestions = $categories->sum('questions_count');
@endphp

<x-layouts.app title="الفئات — سوالف">
<div class="categories-design">
  <section class="hero-strip">
    <div class="hero-strip__glow"></div>
    <div class="container">
      <div class="crumb">الرئيسية <span>›</span> الفئات</div>
      <h1 class="hero-strip__title">
        <span>اختر <em>فئتك</em></span>
        <span class="hero-strip__title--gradient">وابدأ التحدي</span>
      </h1>
      <p class="hero-strip__sub">فئات متنوعة • ٣ مستويات • أسئلة حصرية عن الإمارات والمعرفة العامة</p>

      <div class="hero-stats">
        <div class="stat"><b>{{ $categories->count() }}</b><span>فئة</span></div>
        <div class="stat"><b>{{ $totalQuestions }}+</b><span>سؤال</span></div>
        <div class="stat"><b>3</b><span>مستويات</span></div>
        <div class="stat"><b>∞</b><span>متعة</span></div>
      </div>
    </div>
  </section>

  <section class="controls">
    <div class="container controls__inner">
      <div class="search">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="categorySearch" placeholder="ابحث عن فئة… مثلاً: كوفيهات، معالم، قرآن" />
      </div>

      <div class="filters" id="categoryFilters">
        <button type="button" class="pill active" data-filter="all">الكل</button>
        <button type="button" class="pill" data-filter="uae">🇦🇪 إمارات</button>
        <button type="button" class="pill" data-filter="general">🌍 عامة</button>
        <button type="button" class="pill" data-filter="religion">📖 دينية</button>
        <button type="button" class="pill" data-filter="sport">⚽ رياضة</button>
        <button type="button" class="pill" data-filter="fun">🎭 ترفيه</button>
        <button type="button" class="pill" data-filter="media">🎬 صور وصوت</button>
      </div>

      <div class="sort">
        <label>ترتيب:</label>
        <select id="categorySort">
          <option value="popular">الأكثر شعبية</option>
          <option value="new">الأحدث</option>
          <option value="az">أ — ي</option>
          <option value="questions">عدد الأسئلة</option>
        </select>
      </div>
    </div>
  </section>

  <main class="container">
    <div class="grid" id="categoryGrid">
      @foreach($categories as $index => $category)
        @php
          $filter = $filterOf($category);
          $palette = $palettes[$index % count($palettes)];
        @endphp
        <a href="{{ route('categories.show', $category) }}"
           class="card"
           data-filter="{{ $filter }}"
           data-group="{{ $category->group }}"
           data-name="{{ $category->name_ar }}"
           data-questions="{{ $category->questions_count }}"
           data-created="{{ $category->created_at ? $category->created_at->timestamp : 0 }}"
           data-order="{{ $category->sort_order ?? 0 }}"
           style="--c1:{{ $palette[0] }};--c2:{{ $palette[1] }}">
          <span class="card__tag">{{ $typeLabel[$filter] ?? 'عامة' }}</span>
          <div class="card__icon">
            @if($category->imageUrl())
              <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}">
            @else
              {{ $category->icon ?: '🎯' }}
            @endif
          </div>
          <div>
            <h3 class="card__title">{{ $category->name_ar }}</h3>
            <div class="card__meta">
              <span>📝 {{ $category->questions_count }} سؤال</span>
              <span class="card__levels" title="سهل / متوسط / صعب">
                <i class="on"></i><i class="on"></i><i class="on"></i>
              </span>
            </div>
          </div>
        </a>
      @endforeach
    </div>

    <div class="empty" id="categoryEmpty" hidden>
      <div class="empty__icon">🔎</div>
      <h3>لا توجد فئات مطابقة</h3>
      <p>جرّب كلمة بحث أخرى أو أزل الفلاتر</p>
    </div>
  </main>

  <section class="cta">
    <div class="container cta__inner">
      <div>
        <h2>جاهز تتحدى أصحابك؟</h2>
        <p>كوّن فريقين، اختر فئتك المفضلة، وخلي المتعة تبدأ.</p>
      </div>
      <a href="{{ route('categories.index') }}" class="btn btn--primary btn--lg">🔥 ابدأ من الفئات</a>
    </div>
  </section>
</div>
</x-layouts.app>
