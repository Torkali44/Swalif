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
    ['name' => 'زايد',  'score' => 15250, 'c' => '#FF6B2C'],
    ['name' => 'شما',   'score' => 12840, 'c' => '#7C3AED'],
    ['name' => 'حمدان', 'score' => 11600, 'c' => '#0E9F6E'],
    ['name' => 'ميرة',  'score' => 10230, 'c' => '#EC4899'],
    ['name' => 'سلطان', 'score' => 9870,  'c' => '#00B4D8'],
  ];

  $nowPlaying = [
    ['name' => 'راشد',  'cat' => 'تحدي إماراتي', 'c' => '#FF6B2C'],
    ['name' => 'عفراء', 'cat' => 'الجغرافيا',    'c' => '#0E9F6E'],
    ['name' => 'منصور', 'cat' => 'الرياضة',      'c' => '#7C3AED'],
    ['name' => 'مهرة',  'cat' => 'العلوم',        'c' => '#00B4D8'],
    ['name' => 'هزاع',  'cat' => 'التاريخ',      'c' => '#EC4899'],
  ];
@endphp

<x-layouts.app title="سوالف — العب، تعلّم، واربح">
<div class="home">

  <style>
    .hp-hero__art { display: flex; justify-content: center; align-items: center; }
    .hero-main-img {
      width: 100%;
      max-width: 480px;
      height: auto;
      object-fit: contain;
      background: transparent !important;
      border-radius: 0 !important;
      filter: drop-shadow(0 24px 38px rgba(0,0,0,0.18));
      transition: transform 0.3s ease, filter 0.3s ease;
      animation: heroFloat 4s ease-in-out infinite alternate;
    }
    body.dark .hero-main-img {
      filter: drop-shadow(0 24px 44px rgba(0,0,0,0.65));
    }
    @keyframes heroFloat {
      0%   { transform: translateY(0); }
      100% { transform: translateY(-10px); }
    }
    @media (max-width: 920px) {
      .hp-hero__inner { grid-template-columns: 1fr !important; text-align: center; gap: 20px !important; }
      .hp-hero__text { align-items: center; gap: 16px !important; }
      .hp-hero__art { display: flex !important; justify-content: center; margin-top: 10px; }
      .hero-main-img { max-width: min(340px, 86vw) !important; }
      .hp-hero__title { font-size: clamp(24px, 5.5vw, 36px) !important; line-height: 1.3 !important; }
      .hp-hero__sub { font-size: 14px !important; max-width: 92% !important; margin: 0 auto; line-height: 1.6 !important; }
      .hp-hero__cta { justify-content: center !important; }
      .hp-hero__players { justify-content: center !important; }
      .hp-hero { padding: 36px 0 28px !important; min-height: auto !important; }
    }
  </style>

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
          <!-- <a href="{{ route('custom-game.create') }}" class="btn btn--lg" style="background:linear-gradient(135deg,#FF6D00,#FF1744);color:#fff;font-weight:800">🚀 أنشئ لعبتك الخاصة</a> -->
        </div>

        @if(isset($heroCharacters) && $heroCharacters->isNotEmpty())
          <div class="hp-hero__players">
            <div class="hp-avatars">
              @foreach($heroCharacters as $character)
                @if($character->imageUrl())
                  <img src="{{ $character->imageUrl() }}" alt="{{ $character->name_ar }}" loading="lazy" decoding="async">
                @else
                  <span style="background:{{ $character->accentGradient() }}">{{ $character->icon ?: mb_substr($character->name_ar, 0, 1) }}</span>
                @endif
              @endforeach
            </div>
          </div>
        @endif
      </div>

      <div class="hp-hero__art">
        <picture>
          <source srcset="{{ asset('images/mainPhoto.webp') }}" type="image/webp">
          <img
            src="{{ asset('images/mainPhoto.png') }}"
            alt="منصة ألعاب سوالف"
            width="800" height="600"
            decoding="async" fetchpriority="high"
            class="hero-main-img"
            data-no-sw-img>
        </picture>
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
            <div class="hp-cat__body">
              <div class="hp-cat__icon" style="background:{{ $tile[0] }};color:{{ $tile[1] }}">
                @if($category->imageUrl())
                  <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}" loading="lazy" decoding="async" width="160" height="160" data-no-sw-img>
                @else
                  {{ $category->icon ?: '🎯' }}
                @endif
              </div>
              <h3>{{ $category->name_ar }}</h3>
              <p>{{ $category->remaining_badge ?? (number_format($category->questions_count).' سؤال') }}</p>
            </div>
            <a href="{{ route('game.setup', $category) }}"
               class="hp-cat__btn"
               style="--tc:{{ $tile[1] }}"
               data-category-play
               data-play-url="{{ route('game.setup', $category) }}"
               data-total="{{ (int) $category->questions_count }}"
               data-remaining="{{ (int) ($category->remaining_questions ?? $category->questions_count) }}">ابدأ اللعب</a>
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
        <picture>
          <source srcset="{{ asset('images/faq-bubbles.webp') }}" type="image/webp">
          <img
            src="{{ asset('images/faq-bubbles.png') }}"
            alt="الأسئلة الشائعة" loading="lazy" decoding="async" width="640" height="480"
            data-no-sw-img>
        </picture>
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
        <picture>
          <source srcset="{{ asset('images/game-controller.webp') }}" type="image/webp">
          <img
            src="{{ asset('images/game-controller.png') }}"
            alt="ابدأ التحدي" loading="lazy" decoding="async" width="480" height="360"
            data-no-sw-img>
        </picture>
      </div>
    </div>
  </section>

</div>
</x-layouts.app>
