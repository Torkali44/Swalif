@php
  $palettes = [
    ['#FF3B4E', '#C8102E'],
    ['#FF6B35', '#E8501A'],
    ['#8B5A2B', '#5C3317'],
    ['#D4AF37', '#B8860B'],
    ['#00A86B', '#00843D'],
    ['#A67B5B', '#6F4E37'],
    ['#0F9D58', '#0B6E3D'],
    ['#00B4D8', '#0077B6'],
    ['#7C3AED', '#5B21B6'],
    ['#F59E0B', '#D97706'],
    ['#EC4899', '#BE185D'],
    ['#EF4444', '#B91C1C'],
    ['#06B6D4', '#0E7490'],
    ['#F97316', '#C2410C'],
    ['#8B5CF6', '#6D28D9'],
  ];

  $filterOf = function ($category) {
      $slug = strtolower($category->slug ?? '');
      $name = $category->name_ar ?? '';
      if ($category->group === 'uae') return 'uae';
      if (str_contains($slug, 'quran') || str_contains($slug, 'verse') || str_contains($name, 'آية') || str_contains($name, 'سيرة') || str_contains($name, 'إسلام')) return 'religion';
      if (str_contains($slug, 'football') || str_contains($name, 'كرة') || str_contains($name, 'رياض')) return 'sports';
      if (str_contains($slug, 'guess') || str_contains($name, 'خمّن') || str_contains($name, 'خمن') || str_contains($name, 'ألغاز')) return 'guess';
      return 'fun';
  };
@endphp

<x-layouts.app>
{{-- HERO --}}
<section class="hero">
  <div class="hero__bg">
    <div class="hero__glow hero__glow--1"></div>
    <div class="hero__glow hero__glow--2"></div>
    <svg class="hero__skyline" viewBox="0 0 1440 220" preserveAspectRatio="none" aria-hidden="true">
      <path d="M0,220 L0,140 L60,140 L60,110 L100,110 L100,150 L150,150 L150,90 L180,70 L210,90 L210,150 L260,150 L260,60 L285,40 L310,60 L310,150 L360,150 L360,120 L420,120 L420,80 L470,80 L470,150 L520,150 L520,100 L580,100 L580,140 L640,140 L640,50 L670,30 L700,50 L700,150 L760,150 L760,110 L820,110 L820,130 L880,130 L880,90 L940,90 L940,150 L1000,150 L1000,70 L1030,50 L1060,70 L1060,150 L1120,150 L1120,120 L1180,120 L1180,150 L1240,150 L1240,100 L1300,100 L1300,140 L1360,140 L1360,120 L1440,120 L1440,220 Z" fill="rgba(255,255,255,0.08)"/>
    </svg>
  </div>

  <div class="hero__content">
    <span class="chip chip--pulse">🇦🇪 لعبة معلومات إماراتية</span>
    <h1 class="hero__title">
      <span class="title-line">العب سين جيم</span>
      <span class="title-line title-line--accent">بروح الإمارات</span>
    </h1>
    <p class="hero__subtitle">
      لعبة جماعية حماسية بين فريقين — فئات مستوحاة من الإمارات، مؤقت، وسائل مساعدة، ونقاط تحسم الفائز.
    </p>
    <div class="hero__cta">
      <a href="{{ route('categories.index') }}" class="btn btn--primary btn--lg">
        <span>ابدأ اللعب الآن</span>
        <span class="btn-arrow">←</span>
      </a>
      <a href="#how" class="btn btn--outline btn--lg">شاهد كيف تلعب</a>
    </div>
    <div class="hero__stats">
      <div class="stat"><b>{{ $categories->count() }}+</b><span>فئة متنوعة</span></div>
      <div class="stat stat--divider"><b>3</b><span>مستويات صعوبة</span></div>
      <div class="stat"><b>∞</b><span>ألعاب للمشتركين</span></div>
    </div>
  </div>

  <div class="hero__cards">
    <div class="float-card float-card--1">🏛️<span>معالم</span></div>
    <div class="float-card float-card--2">☕<span>كوفيهات</span></div>
    <div class="float-card float-card--3">🕌<span>مساجد</span></div>
    <div class="float-card float-card--4">🌴<span>تراث</span></div>
  </div>
</section>

{{-- FILTERS --}}
<section class="filters">
  <div class="container">
    <div class="filters__row">
      <button type="button" class="pill pill--all is-active" data-filter="all">كل الفئات</button>
      <button type="button" class="pill pill--red" data-filter="uae">إمارات 🇦🇪</button>
      <button type="button" class="pill pill--gold" data-filter="general">عامة</button>
      <button type="button" class="pill pill--green" data-filter="religion">إسلاميات</button>
      <button type="button" class="pill pill--blue" data-filter="sports">رياضة</button>
      <button type="button" class="pill pill--purple" data-filter="fun">ترفيه</button>
      <button type="button" class="pill pill--pink" data-filter="guess">خمّن</button>
    </div>
  </div>
</section>

{{-- CATEGORIES --}}
<section class="categories categories--circles" id="categories">
  <div class="container">
    <header class="section-head">
      <h2>اختر فئتك <span class="grad-text">المفضلة</span></h2>
      <p>فئات مستوحاة من الإمارات وفئات عامة — كل فئة بثلاث مستويات: سهل، متوسط، صعب</p>
    </header>

    <div class="cat-circle-grid">
      @foreach($categories as $index => $category)
        <x-category-circle
          :category="$category"
          :index="$index"
          :filter="$filterOf($category)"
          :group="$category->group"
        />
      @endforeach
    </div>

    <div class="center">
      <a class="btn btn--outline" href="{{ route('categories.index') }}">عرض كل الفئات</a>
    </div>
  </div>
</section>

{{-- HOW --}}
<section class="how" id="how">
  <div class="container">
    <header class="section-head section-head--light">
      <h2>كيف <span class="grad-text">تلعب؟</span></h2>
      <p>4 خطوات بسيطة لبدء أول لعبة</p>
    </header>
    <div class="steps">
      @foreach([
        ['1','اختر فئة','من الفئات الإماراتية أو العامة'],
        ['2','أنشئ فريقين','سمّ الفرق وحدد اللاعبين'],
        ['3','العب واستخدم الوسائل','مؤقت، خيارات، ووسائل مساعدة'],
        ['4','الفريق الفائز','يظهر مجموع النقاط والفائز 🏆'],
      ] as $step)
        <div class="step">
          <div class="step__num">{{ $step[0] }}</div>
          <h3>{{ $step[1] }}</h3>
          <p>{{ $step[2] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- PLANS --}}
<section class="plans" id="plans">
  <div class="container">
    <header class="section-head">
      <h2>باقات <span class="grad-text">الاشتراك</span></h2>
      <p>جرّب أول {{ config('game.free_trial_limit') }} أسئلة مجانًا، ثم اختر ما يناسبك</p>
    </header>

    <div class="plan-grid">
      @foreach($plans as $plan)
        @php
          $medal = match($plan->type) {
            'weekly' => '🥉',
            'monthly' => '🥈',
            'yearly' => '🥇',
            default => '💎',
          };
          $period = match($plan->type) {
            'weekly' => 'أسبوع',
            'monthly' => 'شهر',
            'yearly' => 'سنة',
            default => 'مدة الاشتراك',
          };
        @endphp
        <article class="plan {{ $plan->is_recommended ? 'plan--featured' : '' }}">
          @if($plan->is_recommended)
            <div class="plan__badge">⭐ الأكثر شعبية</div>
          @endif
          <div class="plan__medal">{{ $medal }}</div>
          <h3>{{ $plan->name }}</h3>
          <div class="plan__price">
            <b>{{ number_format($plan->price) }}</b>
            <span>درهم / {{ $period }}</span>
          </div>
          <ul>
            @foreach($plan->features ?? [] as $feature)
              <li>{{ $feature }}</li>
            @endforeach
          </ul>
          <a href="{{ route('subscription.index') }}"
             class="btn {{ $plan->is_recommended ? 'btn--primary' : 'btn--outline' }} btn--block">
            {{ $plan->is_recommended ? 'اشترك الآن' : 'اشترك' }}
          </a>
        </article>
      @endforeach
    </div>
  </div>
</section>
</x-layouts.app>
