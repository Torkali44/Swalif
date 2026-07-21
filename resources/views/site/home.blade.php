@php
  $catTiles = [
    ['#EDE7FF', '#7C3AED'], // purple
    ['#DCFBEF', '#0E9F6E'], // green
    ['#FFEAD9', '#FF6B2C'], // orange
    ['#FFE1EF', '#EC4899'], // pink
    ['#FFE1E1', '#EF4444'], // red
    ['#FFF3D1', '#E0A500'], // yellow
  ];
  $homeCats = $categories->take(6);
  $secondsLeft = now()->endOfDay()->diffInSeconds(now());

  $leaders = [
    ['name' => 'أحمد',  'score' => 15250, 'c' => '#FF6B2C'],
    ['name' => 'سارة',  'score' => 12840, 'c' => '#7C3AED'],
    ['name' => 'محمد',  'score' => 11600, 'c' => '#0E9F6E'],
    ['name' => 'خالد',  'score' => 10230, 'c' => '#EC4899'],
    ['name' => 'نور',   'score' => 9870,  'c' => '#00B4D8'],
  ];

  $nowPlaying = [
    ['name' => 'Ahmed',  'cat' => 'تحدي إماراتي', 'c' => '#FF6B2C'],
    ['name' => 'Aya',    'cat' => 'الجغرافيا',    'c' => '#0E9F6E'],
    ['name' => 'Omar',   'cat' => 'الرياضة',      'c' => '#7C3AED'],
    ['name' => 'Sara',   'cat' => 'العلوم',        'c' => '#00B4D8'],
    ['name' => 'Khaled', 'cat' => 'التاريخ',      'c' => '#EC4899'],
  ];
@endphp

<x-layouts.app title="سوالف — العب، تعلّم، واربح">
<div class="home">

  {{-- ============ HERO ============ --}}
  <section class="hp-hero">
    <div class="hp-hero__blob hp-hero__blob--1"></div>
    <div class="hp-hero__blob hp-hero__blob--2"></div>
    <div class="container hp-hero__inner">

      <div class="hp-hero__text">
        <h1 class="hp-hero__title">
          العب، تعلّم،<br>
          واربح مع <span>سوالف</span>
        </h1>
        <p class="hp-hero__sub">منصة ألعاب معلوماتية ممتعة وتفاعلية تجمع بين التحدي والمعرفة. آلاف الأسئلة في مختلف المجالات بانتظارك.</p>

        <div class="hp-hero__cta">
          <a href="{{ route('categories.index') }}" class="btn btn--primary btn--lg">🎮 ابدأ اللعب الآن</a>
          <a href="{{ route('categories.index') }}" class="btn btn--soft btn--lg">استكشف الألعاب</a>
        </div>

        <div class="hp-hero__players">
          <div class="hp-avatars">
            <span style="background:linear-gradient(135deg,#FF6B2C,#EC4899)">A</span>
            <span style="background:linear-gradient(135deg,#7C3AED,#00B4D8)">S</span>
            <span style="background:linear-gradient(135deg,#0E9F6E,#F5C542)">M</span>
            <span style="background:linear-gradient(135deg,#EC4899,#7C3AED)">N</span>
          </div>
          <div class="hp-hero__live"><i></i> +25,430 لاعب نشط الآن</div>
        </div>
      </div>

      <div class="hp-hero__art">
        <img src="{{ asset('images/hero-character.png') }}" alt="العب سوالف" loading="eager">
      </div>
    </div>
  </section>

  {{-- ============ STATS ============ --}}
  <section class="hp-stats">
    <div class="container hp-stats__grid">
      <div class="hp-stat">
        <span class="hp-stat__ic hp-stat__ic--orange">🏆</span>
        <div><b>2.5M+</b><span>إجابة صحيحة</span></div>
      </div>
      <div class="hp-stat">
        <span class="hp-stat__ic hp-stat__ic--green">👥</span>
        <div><b>350K+</b><span>لاعب مسجّل</span></div>
      </div>
      <div class="hp-stat">
        <span class="hp-stat__ic hp-stat__ic--red">🔥</span>
        <div><b>45K+</b><span>لعبة اليوم</span></div>
      </div>
      <div class="hp-stat">
        <span class="hp-stat__ic hp-stat__ic--blue">🛡️</span>
        <div><b>98%</b><span>رضا المستخدمين</span></div>
      </div>
    </div>
  </section>

  {{-- ============ CATEGORIES ============ --}}
  <section class="hp-section" id="categories">
    <div class="container">
      <div class="hp-head hp-head--center">
        <div>
          <h2>اختر فئتك المفضلة</h2>
          <p>تصفّح أكثر الفئات شعبية وابدأ التحدي فورًا</p>
        </div>
      </div>
      <div style="text-align:center;margin-bottom:12px;"><a href="{{ route('categories.index') }}" class="hp-head__link">عرض الكل ←</a></div>

      <div class="hp-cats">
        @foreach($homeCats as $i => $category)
          @php $tile = $catTiles[$i % count($catTiles)]; @endphp
          <article class="hp-cat">
            <div class="hp-cat__icon" style="background:{{ $tile[0] }};color:{{ $tile[1] }}">
              @if($category->imageUrl())
                <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}">
              @else
                {{ $category->icon ?: '🎯' }}
              @endif
            </div>
            <h3>{{ $category->name_ar }}</h3>
            <p>{{ number_format($category->questions_count) }} سؤال</p>
            <a href="{{ route('categories.show', $category) }}"
               class="hp-cat__btn"
               style="--tc:{{ $tile[1] }}">ابدأ اللعب</a>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ PANELS: leaderboard / daily / now playing ============ --}}
  <section class="hp-section hp-section--soft" id="leaderboard">
    <div class="container hp-panels">

      {{-- Leaderboard --}}
      <div class="hp-panel">
        <div class="hp-panel__head">
          <h3>🏅 المتصدرون هذا الأسبوع</h3>
        </div>
        <ul class="hp-lead">
          @foreach($leaders as $i => $l)
            <li>
              <span class="hp-lead__rank hp-lead__rank--{{ $i + 1 }}">{{ $i + 1 }}</span>
              <span class="hp-lead__ava" style="background:{{ $l['c'] }}">{{ mb_substr($l['name'], 0, 1) }}</span>
              <span class="hp-lead__name">{{ $l['name'] }}</span>
              <span class="hp-lead__score">{{ number_format($l['score']) }}</span>
            </li>
          @endforeach
        </ul>
        <a href="{{ route('categories.index') }}" class="btn btn--soft btn--block">عرض الترتيب الكامل</a>
      </div>

      {{-- Daily challenge --}}
      <div class="hp-panel hp-panel--challenge">
        <div class="hp-panel__head hp-panel__head--center">
          <h3>⚡ تحدّي اليوم</h3>
        </div>
        <div class="hp-challenge">
          <div class="hp-challenge__trophy">🏆</div>
          <p>جاوب على سؤال صعب على مدار اليوم واكسب نقاطًا مضاعفة!</p>
          <div class="hp-countdown" data-countdown="{{ $secondsLeft }}">
            <div><b data-cd="h">00</b><small>ساعة</small></div>
            <span>:</span>
            <div><b data-cd="m">00</b><small>دقيقة</small></div>
            <span>:</span>
            <div><b data-cd="s">00</b><small>ثانية</small></div>
          </div>
          <a href="{{ route('categories.index') }}" class="btn btn--primary btn--block">شارك الآن</a>
        </div>
      </div>

      {{-- Now playing --}}
      <div class="hp-panel">
        <div class="hp-panel__head">
          <h3>🎮 الآن يلعب</h3>
        </div>
        <ul class="hp-live">
          @foreach($nowPlaying as $p)
            <li>
              <span class="hp-live__ava" style="background:{{ $p['c'] }}">{{ mb_substr($p['name'], 0, 1) }}</span>
              <span class="hp-live__name">{{ $p['name'] }}</span>
              <span class="hp-live__tag" style="color:{{ $p['c'] }};background:{{ $p['c'] }}1a">{{ $p['cat'] }}</span>
            </li>
          @endforeach
        </ul>
        <a href="{{ route('categories.index') }}" class="btn btn--soft btn--block">عرض جميع اللاعبين</a>
      </div>
    </div>
  </section>

  {{-- ============ PLANS ============ --}}
  <section class="hp-section" id="plans">
    <div class="container">
      <div class="hp-head hp-head--center">
        <div>
          <h2>باقات الاشتراك</h2>
          <p>اختر الخطة التي تناسبك وابدأ رحلتك نحو القمة</p>
        </div>
      </div>

      @php
        $hpPlans = [
          ['name' => 'يومي',   'icon' => '🎯', 'old' => 3,   'new' => 1,   'period' => 'يوم',   'featured' => false],
          ['name' => 'أسبوعي', 'icon' => '⭐', 'old' => 10,  'new' => 5,   'period' => 'أسبوع', 'featured' => false],
          ['name' => 'شهري',   'icon' => '💎', 'old' => 29,  'new' => 15,  'period' => 'شهر',   'featured' => true],
          ['name' => 'سنوي',   'icon' => '👑', 'old' => 149, 'new' => 99,  'period' => 'سنة',   'featured' => false],
        ];
      @endphp

      @php
        if ($plans->isNotEmpty()) {
          $hpPlans = $plans->map(function ($plan) {
            $period = match ((int) $plan->duration_days) {
              1 => 'يوم',
              7 => 'أسبوع',
              30 => 'شهر',
              365 => 'سنة',
              default => $plan->duration_days.' يوم',
            };

            return [
              'name' => $plan->name,
              'icon' => $plan->icon ?: '💎',
              'old' => $plan->old_price,
              'new' => $plan->price,
              'period' => $period,
              'currency' => $plan->currency === 'AED' ? 'درهم' : $plan->currency,
              'featured' => $plan->is_recommended,
              'features' => $plan->features,
            ];
          })->values()->all();
        }
      @endphp

      <div class="hp-plans hp-plans--4">
        @foreach($hpPlans as $hp)
          <article class="hp-plan {{ $hp['featured'] ? 'is-featured' : '' }}">
            @if($hp['featured'])
              <span class="hp-plan__badge">الأكثر شعبية</span>
            @endif
            <span class="hp-plan__icon">{{ $hp['icon'] }}</span>
            <h3>{{ $hp['name'] }}</h3>
            <div class="hp-plan__price">
              <b>{{ $hp['new'] }}</b>
              @if(!empty($hp['old']) && (float) $hp['old'] > (float) $hp['new'])
                <s class="hp-plan__old">{{ $hp['old'] }}</s>
              @endif
              <span>{{ $hp['currency'] ?? 'درهم' }} / {{ $hp['period'] }}</span>
            </div>
            <ul>
              @if(!empty($hp['features']))
                @foreach($hp['features'] as $feature)
                  <li>{{ $feature }}</li>
                @endforeach
              @else
              <li>فتح جميع الفئات</li>
              <li>لعب غير محدود</li>
              <li>جميع المستويات</li>
              <li>تحديثات مستمرة</li>
              @endif
            </ul>
            <a href="{{ route('subscription.index') }}"
               class="btn {{ $hp['featured'] ? 'btn--primary' : 'btn--soft' }} btn--block">
              اختر الخطة
            </a>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ FAQ ============ --}}
  <section class="hp-section hp-section--soft" id="faq">
    <div class="container hp-faq">
      <div class="hp-faq__art">
        <img src="{{ asset('images/faq-bubbles.png') }}" alt="الأسئلة الشائعة" loading="lazy">
        <h2>الأسئلة الشائعة</h2>
        <p>كل ما تريد معرفته عن سوالف في مكان واحد</p>
      </div>

      <div class="hp-faq__list">
        @foreach([
          ['كيف أبدأ اللعب؟',
           'اختر فئة من صفحة التصنيفات، كوّن فريقين، ثم اختر خانات النقاط من اللوحة. كل خانة تفتح سؤالًا حسب المستوى (سهل / متوسط / صعب).'],
          ['هل يمكنني اللعب مع أصدقائي؟',
           'نعم! سوالف لعبة جماعية بين فريقين، اجمع أصدقاءك وتحدّوا بعضكم على النقاط والفوز.'],
          ['كيف يتم احتساب النقاط؟',
           'كل سؤال له نقاط حسب صعوبته: سهل 200، متوسط 400، وصعب 600. الفريق صاحب أعلى مجموع يفوز.'],
          ['هل هناك تجربة مجانية؟',
           'نعم — تقدر تجرّب أول '.config('game.free_trial_limit').' أسئلة مجانًا، وبعدها تختار الباقة التي تناسبك.'],
          ['هل يمكنني اللعب على الجوال؟',
           'بالتأكيد، الموقع متجاوب بالكامل مع الجوال والتابلت ويعمل على أي متصفح حديث.'],
        ] as [$q, $a])
          <details class="hp-faq__item">
            <summary><span>{{ $q }}</span><i aria-hidden="true">+</i></summary>
            <p>{{ $a }}</p>
          </details>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ CTA ============ --}}
  <section class="hp-cta">
    <div class="container hp-cta__inner">
      <div class="hp-cta__text">
        <h2>هل أنت مستعد للتحدّي؟</h2>
        <p>انضم إلى آلاف اللاعبين وابدأ الآن — مجانًا بالكامل.</p>
        <a href="{{ route('categories.index') }}" class="btn btn--white btn--lg">🚀 ابدأ مجانًا</a>
      </div>
      <div class="hp-cta__art">
        <img src="{{ asset('images/game-controller.png') }}" alt="ابدأ التحدي" loading="lazy">
      </div>
    </div>
  </section>

</div>
</x-layouts.app>
