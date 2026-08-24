@php
  $totalQuestions = $categories->sum('questions_count');
  $usedClassificationIds = $categories->pluck('classification_id')->filter()->unique()->all();
  $filterClassifications = collect($classifications ?? [])
      ->filter(fn ($c) => in_array($c->id, $usedClassificationIds, true))
      ->values();

  // تجميع الفئات تحت تصنيفاتها
  $groupedCategories = $categories->groupBy(function ($cat) {
      return $cat->classification_id ?: 0;
  });

  // ترتيب التصنيفات حسب sort_order
  $orderedClassifications = $filterClassifications->sortBy('sort_order')->values();

  // الفئات التي ليس لها تصنيف أو تصنيفها غير موجود في جدول التصنيفات
  $validClassificationIds = $orderedClassifications->pluck('id')->all();
  $uncategorized = $categories->filter(function ($cat) use ($validClassificationIds) {
      return empty($cat->classification_id) || !in_array($cat->classification_id, $validClassificationIds);
  });
@endphp

<x-layouts.app title="اختر الفئات — سوالف">
<div class="swalif-categories-page">

  <!-- Curved Header Banner -->
  <section class="hero-curved-strip">
    <div class="container hero-curved-strip__inner">
      <h1 class="hero-curved-strip__title">الألعاب</h1>
      <p class="hero-curved-strip__sub">استكشف مختلف الفئات والتصنيفات واختبر معلوماتك مع أصحابك<br>آلاف الأسئلة الممتعة والمستويات بانتظارك</p>

      <!-- Horizontal Pills Strip -->
      <div class="top-nav-pills-row" id="topPillsRow">
        <button type="button" class="nav-pill-btn nav-pill-btn--blue is-active-pill" data-top-filter="all">الألعاب</button>
        <button type="button" class="nav-pill-btn nav-pill-btn--grey" data-top-filter="favorites">المفضلة <span class="fav-pill-count" id="topFavCount">(0)</span></button>
        <button type="button" class="nav-pill-btn nav-pill-btn--green" data-top-filter="فكر">🎲 فكروابدأ</button>
        <a href="{{ route('custom-game.create') }}" class="nav-pill-btn nav-pill-btn--red">🚀 إنشاء لعبة</a>
        <button type="button" class="nav-pill-btn nav-pill-btn--orange" data-top-filter="صممت لك">✨ صممت لك</button>
        <button type="button" class="nav-pill-btn nav-pill-btn--purple" data-top-filter="إمارات">🇦🇪 إمارات</button>
      </div>
    </div>
  </section>

  <main class="container page-content-wrap">

    <div class="section-heading-bar">
      <h2>اختر الفئات</h2>
      <p>فئات متنوعة • {{ $categories->count() }} فئة متاحة • أسئلة حصرية وممتعة</p>
    </div>

    <!-- Controls Row -->
    <section class="controls-bar">
      <div class="search-box">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="categorySearch" placeholder="ابحث عن فئة… مثلاً: تاريخ، رياضة، سياحة" />
      </div>

      <div class="filter-actions-cluster">
        <div class="filters-pills-group" id="categoryFilters">
          <button type="button" class="pill-btn active" data-filter="all">الكل</button>
          <button type="button" class="pill-btn" data-filter="favorites" id="favFilterBtn">❤️ المفضلة (<span id="favCount">0</span>)</button>
          @foreach($filterClassifications as $classification)
            <button type="button" class="pill-btn" data-filter="c{{ $classification->id }}">
              {{ $classification->icon ? $classification->icon.' ' : '' }}{{ $classification->name_ar }}
            </button>
          @endforeach
        </div>
      </div>
    </section>

    @if(!empty($playBlocked))
      <div class="free-lock-banner">
        {{ $subscribeMessage }}
        <a href="{{ route('subscription.index') }}">صفحة الاشتراك</a>.
      </div>
    @elseif(!empty($freeLocked))
      <div class="free-lock-banner">
        انتهت تجربتك المجانية (فئة واحدة). عشان تلعب فئة ثانية
        <a href="{{ route('subscription.index') }}">اشترك الحين</a>.
      </div>
    @endif

    {{-- زر إنشاء لعبة خاصة --}}
    @auth
      <div class="custom-game-cta-banner" id="customGameBanner">
        <div class="cta-banner-inner">
          <div class="cta-banner-text">
            <span class="cta-banner-icon">🎮</span>
            <div>
              <strong>أنشئ لعبتك الخاصة!</strong>
              <p>اختر من 4 إلى 6 فئات وانشئ تحديًا مخصصًا لأصحابك</p>
            </div>
          </div>
          <a href="{{ route('custom-game.create') }}" class="btn btn--primary cta-banner-btn" id="customGameBtn">
            🚀 أنشئ لعبتك الخاصة
          </a>
        </div>
      </div>
    @endauth

    {{-- حالة لا يوجد نتائج --}}
    <div id="categoryEmpty" class="category-empty-state" hidden>
      <div class="empty-icon">🔍</div>
      <h3>لم نجد أي فئة تطابق بحثك</h3>
      <p>جرب البحث بكلمات أخرى أو تصفح باقي الفئات المميزة</p>
      <button type="button" class="btn btn--primary" id="resetSearchBtn">إعادة عرض كل الفئات</button>
    </div>

    {{-- عرض الفئات بنظام Accordion --}}
    <div class="accordion-categories" id="accordionCategories">

      @foreach($orderedClassifications as $classification)
        @php
          $classCats = $groupedCategories->get($classification->id, collect());
          if ($classCats->isEmpty()) continue;
        @endphp

        <div class="accordion-section" data-classification-id="{{ $classification->id }}" data-classification-name="{{ $classification->name_ar }}">
          <!-- Accordion Header Pill -->
          <button
            type="button"
            class="accordion-header is-open"
            data-target="acc-{{ $classification->id }}"
            aria-expanded="true"
          >
            <span class="accordion-toggle-circle">−</span>
            <div class="accordion-header-title-wrap">
              @if($classification->icon)
                <span class="accordion-icon">{{ $classification->icon }}</span>
              @endif
              <span class="accordion-title">{{ $classification->name_ar }}</span>
              <span class="accordion-count">({{ $classCats->count() }})</span>
            </div>
          </button>

          <!-- Accordion Body: Open by default -->
          <div
            class="accordion-body is-open"
            id="acc-{{ $classification->id }}"
          >
            <div class="grid accordion-grid" data-acc-grid>
              @foreach($classCats as $category)
                @php
                  $filterKey = $category->classification_id ? 'c'.$category->classification_id : 'general';
                  $isLocked = !empty($playBlocked) || (!empty($freeLocked) && (int) $allowedCategoryId !== (int) $category->id);
                  $playUrl = $isLocked ? route('subscription.index') : route('categories.show', $category);
                @endphp
                <div class="card-item-box {{ $isLocked ? 'is-locked' : '' }}"
                     data-card-url="{{ $playUrl }}"
                     @if($isLocked)
                       data-subscribe-lock
                       data-subscribe-message="{{ $subscribeMessage }}"
                     @endif
                     data-filter="{{ $filterKey }}"
                     data-group="{{ $filterKey }}"
                     data-name="{{ $category->name_ar }}"
                     data-category-id="{{ $category->id }}"
                     data-questions="{{ $category->questions_count }}">

                  <!-- Favorite Direct Heart Button -->
                  <button type="button" class="card-item-fav-direct" data-fav-card-btn data-category-id="{{ $category->id }}" data-category-name="{{ $category->name_ar }}" title="إضافة للمفضلة">
                    <span class="fav-heart-icon">🤍</span>
                  </button>

                  <!-- Image Section -->
                  <div class="card-item-image-wrap">
                    <span class="card-item-badge">
                      {{ $isLocked ? '🔒 مقفول' : ($category->questions_count ? '📝 '.$category->questions_count.' سؤال' : 'لعبة ممتعة') }}
                    </span>
                    @if($category->imageUrl())
                      <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}" loading="lazy" decoding="async">
                    @else
                      <div class="card-item-fallback-icon">{{ $category->icon ?: '🎯' }}</div>
                    @endif
                  </div>

                  <!-- Bottom Solid Orange Name Bar -->
                  <div class="card-item-footer-bar">
                    <span class="card-item-name">{{ $category->name_ar }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endforeach

      {{-- فئات بدون تصنيف --}}
      @if($uncategorized->isNotEmpty())
        <div class="accordion-section" data-classification-id="0" data-classification-name="فئات متنوعة">
          <button
            type="button"
            class="accordion-header is-open"
            data-target="acc-0"
            aria-expanded="true"
          >
            <span class="accordion-toggle-circle">−</span>
            <div class="accordion-header-title-wrap">
              <span class="accordion-icon">🎯</span>
              <span class="accordion-title">فئات متنوعة</span>
              <span class="accordion-count">({{ $uncategorized->count() }})</span>
            </div>
          </button>

          <div class="accordion-body is-open" id="acc-0">
            <div class="grid accordion-grid" data-acc-grid>
              @foreach($uncategorized as $category)
                @php
                  $isLocked = !empty($playBlocked) || (!empty($freeLocked) && (int) $allowedCategoryId !== (int) $category->id);
                  $playUrl = $isLocked ? route('subscription.index') : route('categories.show', $category);
                @endphp
                <div class="card-item-box {{ $isLocked ? 'is-locked' : '' }}"
                     data-card-url="{{ $playUrl }}"
                     @if($isLocked)
                       data-subscribe-lock
                       data-subscribe-message="{{ $subscribeMessage }}"
                     @endif
                     data-filter="general"
                     data-group="general"
                     data-name="{{ $category->name_ar }}"
                     data-category-id="{{ $category->id }}"
                     data-questions="{{ $category->questions_count }}">

                  <button type="button" class="card-item-fav-direct" data-fav-card-btn data-category-id="{{ $category->id }}" data-category-name="{{ $category->name_ar }}" title="إضافة للمفضلة">
                    <span class="fav-heart-icon">🤍</span>
                  </button>

                  <button type="button" class="card-item-info" data-info-toggle title="معلومات الفئة">i</button>

                  <div class="card-info-popover" data-info-popover hidden>
                    <div class="popover-arrow"></div>
                    <p class="popover-desc">
                      {{ $category->description ?: 'اختبر معلوماتك في فئة '.$category->name_ar.' واسمح لإجاباتك بالتألق مع أصحابك!' }}
                    </p>
                    <div class="popover-actions">
                      <a href="{{ $playUrl }}" class="popover-btn popover-btn--primary">
                        تجربة الفئة
                      </a>
                    </div>
                  </div>

                  <div class="card-item-image-wrap">
                    <span class="card-item-badge">
                      {{ $isLocked ? '🔒 مقفول' : ($category->questions_count ? '📝 '.$category->questions_count.' سؤال' : 'لعبة ممتعة') }}
                    </span>
                    @if($category->imageUrl())
                      <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}" loading="lazy" decoding="async">
                    @else
                      <div class="card-item-fallback-icon">{{ $category->icon ?: '🎯' }}</div>
                    @endif
                  </div>

                  <div class="card-item-footer-bar">
                    <span class="card-item-name">{{ $category->name_ar }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif

    </div>

  </main>
</div>

<!-- Modal: Random Category Spinner (فكر وابدأ) -->
<div class="random-picker-modal" id="randomPickerModal" hidden>
  <div class="random-picker-backdrop" id="randomPickerBackdrop"></div>
  <div class="random-picker-dialog">
    <button type="button" class="random-picker-close" id="closeRandomModal">&times;</button>
    <div class="random-picker-body">
      <div class="random-spinner-wrap" id="randomSpinnerWrap">
        <div class="spinner-dice-anim">🎲</div>
        <h3 class="spinner-title">جاري اختيار فئة عشوائية…</h3>
        <p class="spinner-sub">استعد للتحـدي والمعرفة!</p>
      </div>
      <div class="random-result-wrap" id="randomResultWrap" style="display:none">
        <div class="result-badge-icon" id="randomResultIcon">🎯</div>
        <h2 class="result-title" id="randomResultTitle">اسم الفئة</h2>
        <p class="result-desc" id="randomResultDesc">فئة حماسية لتحدي أصحابك!</p>
        <div class="result-actions">
          <a href="#" class="btn btn--fire btn--lg" id="randomPlayLink">🚀 ابدأ اللعب بهذه الفئة</a>
          <button type="button" class="btn btn--ghost" id="spinAgainBtn">🔄 فكر غيره (اختر فئة أخرى)</button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════
   Categories Page Main Layout & Dark Mode Styles
══════════════════════════════════════════════════════════ */
.swalif-categories-page {
  direction: rtl;
  min-height: 100vh;
  padding-bottom: 60px;
  background: var(--bg-main, #F8FAFC);
  color: var(--text-main, #0F172A);
}

body.dark .swalif-categories-page,
html.dark .swalif-categories-page {
  background: #0B1020 !important;
  color: #F8FAFC !important;
}

.hero-curved-strip {
  background: linear-gradient(135deg, #FF1744 0%, #FF6D00 60%, #E64A19 100%);
  padding: 38px 16px 54px;
  text-align: center;
  color: #fff;
  border-bottom-left-radius: 50% 26px;
  border-bottom-right-radius: 50% 26px;
  box-shadow: 0 10px 30px rgba(255,23,68,.25);
  margin-bottom: 28px;
}

body.dark .hero-curved-strip,
html.dark .hero-curved-strip {
  background: linear-gradient(135deg, #D50000 0%, #FF6D00 50%, #7C3AED 100%);
  box-shadow: 0 12px 36px rgba(0,0,0,.5);
}

.hero-curved-strip__title {
  font-size: clamp(2rem, 4.2vw, 2.8rem);
  font-weight: 900;
  margin-bottom: 8px;
  color: #fff !important;
  text-shadow: 0 3px 12px rgba(0,0,0,.2);
}

.hero-curved-strip__sub {
  font-size: 1rem;
  opacity: .95;
  color: #fff !important;
  max-width: 600px;
  margin: 0 auto 24px;
  line-height: 1.55;
}

/* Nav Pills Row */
.top-nav-pills-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
  max-width: 880px;
  margin: 0 auto;
}

@media (max-width: 768px) {
  .top-nav-pills-row {
    flex-wrap: nowrap !important;
    overflow-x: auto !important;
    justify-content: flex-start !important;
    padding: 6px 12px 14px !important;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
  }
  .top-nav-pills-row::-webkit-scrollbar { display: none; }
}

.nav-pill-btn {
  padding: 10px 22px;
  border-radius: 50px;
  font-size: .95rem;
  font-weight: 800;
  color: #fff !important;
  text-decoration: none;
  border: 2px solid rgba(255,255,255,.35);
  cursor: pointer;
  font-family: inherit;
  box-shadow: 0 4px 14px rgba(0,0,0,.18);
  transition: transform .2s, box-shadow .2s, border-color .2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  white-space: nowrap;
  flex-shrink: 0;
}

.nav-pill-btn:hover {
  transform: translateY(-2px) scale(1.03);
  box-shadow: 0 6px 20px rgba(0,0,0,.28);
  border-color: #fff;
}

.nav-pill-btn--blue { background: #00BCD4; }
.nav-pill-btn--grey { background: #546E7A; }
.nav-pill-btn--green { background: #00C853; }
.nav-pill-btn--red { background: #FF1744; }
.nav-pill-btn--orange { background: #FF6D00; }
.nav-pill-btn--purple { background: #7C3AED; }

.is-active-pill {
  border-color: #fff !important;
  box-shadow: 0 0 0 3px rgba(255,255,255,.6), 0 6px 20px rgba(0,0,0,.3) !important;
  transform: scale(1.05);
}

.fav-pill-count {
  font-size: .85rem;
  opacity: .9;
  background: rgba(0,0,0,.2);
  padding: 2px 8px;
  border-radius: 20px;
}

/* Section Heading & Controls */
.section-heading-bar { text-align: center; margin-bottom: 24px; }
.section-heading-bar h2 { font-size: 1.8rem; font-weight: 900; color: inherit; margin-bottom: 6px; }
.section-heading-bar p { color: #64748B; font-size: .95rem; }
body.dark .section-heading-bar p, html.dark .section-heading-bar p { color: #94A3B8; }

.controls-bar {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 30px;
  align-items: center;
  width: 100%;
}

.search-box { width: 100%; max-width: 580px; position: relative; }
.search-box input {
  width: 100%;
  padding: 14px 44px 14px 18px;
  border-radius: 50px;
  border: 2px solid #FF6D00;
  font-family: inherit;
  font-size: .95rem;
  outline: none;
  background: #FFFFFF;
  color: #0F172A;
  box-shadow: 0 4px 16px rgba(255,109,0,.1);
  box-sizing: border-box;
}

body.dark .search-box input, html.dark .search-box input {
  background: rgba(255,255,255,.07);
  border-color: #FF6D00;
  color: #FFFFFF;
  box-shadow: 0 4px 20px rgba(0,0,0,.3);
}

.search-box svg { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #FF6D00; }

.filter-actions-cluster { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: center; width: 100%; }
.filters-pills-group { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }

.pill-btn {
  padding: 8px 18px;
  border-radius: 50px;
  border: 1.5px solid #CBD5E1;
  background: #FFFFFF;
  color: #334155;
  font-size: .88rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .2s;
}

body.dark .pill-btn, html.dark .pill-btn {
  background: rgba(255,255,255,.06);
  border-color: rgba(255,255,255,.14);
  color: #E2E8F0;
}

.pill-btn.active, .pill-btn:hover { background: #FF6D00 !important; color: #fff !important; border-color: #FF6D00 !important; }

/* Banners */
.free-lock-banner {
  margin: 0 0 20px;
  padding: 14px 20px;
  border-radius: 16px;
  background: rgba(255,23,68,.1);
  border: 1px solid rgba(255,23,68,.3);
  font-weight: 700;
  color: #D50000;
}
body.dark .free-lock-banner, html.dark .free-lock-banner { color: #FF5252; background: rgba(255,23,68,.15); }
.free-lock-banner a { color: #FF1744; font-weight: 900; text-decoration: underline; }

.custom-game-cta-banner {
  margin-bottom: 28px;
  border-radius: 24px;
  background: linear-gradient(135deg, #FF6D00 0%, #FF1744 100%);
  padding: 20px 24px;
  box-shadow: 0 8px 32px rgba(255,109,0,.3);
}
.cta-banner-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.cta-banner-text { display: flex; align-items: center; gap: 14px; color: #fff; }
.cta-banner-icon { font-size: 2.2rem; flex-shrink: 0; }
.cta-banner-text strong { display: block; font-size: 1.1rem; font-weight: 800; color: #fff; }
.cta-banner-text p { margin: 2px 0 0; font-size: .88rem; opacity: .9; color: #fff; }
.cta-banner-btn { background: #fff !important; color: #FF6D00 !important; font-weight: 900 !important; border-radius: 14px !important; padding: 12px 22px !important; font-size: .95rem !important; flex-shrink: 0; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(0,0,0,.15); }

/* Accordions */
.accordion-categories { display: flex; flex-direction: column; gap: 20px; margin-bottom: 40px; }
.accordion-section { border-radius: 24px; overflow: hidden; background: transparent; }
.accordion-header {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 22px;
  background: linear-gradient(135deg, #FF6D00 0%, #FF8F00 100%);
  color: #fff;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  font-family: inherit;
  box-shadow: 0 4px 16px rgba(255,109,0,.25);
  transition: transform .2s, box-shadow .2s;
  margin-bottom: 16px;
}
body.dark .accordion-header, html.dark .accordion-header {
  background: linear-gradient(135deg, #E65100 0%, #F57C00 100%);
  box-shadow: 0 6px 20px rgba(0,0,0,.4);
}
.accordion-header:hover { transform: scale(1.01); box-shadow: 0 6px 20px rgba(255,109,0,.35); }
.accordion-header-title-wrap { display: flex; align-items: center; gap: 10px; font-size: 1.2rem; font-weight: 800; }
.accordion-count { font-size: .95rem; opacity: .9; font-weight: 700; }
.accordion-toggle-circle { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,.25); color: #fff; font-size: 18px; font-weight: 900; display: flex; align-items: center; justify-content: center; }
.accordion-header.is-open .accordion-toggle-circle { background: rgba(0,0,0,.18); }
.accordion-body { display: none; padding: 4px 0 16px; }
.accordion-body.is-open { display: block; animation: accFadeIn .25s ease; }
@keyframes accFadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

/* Cards Grid */
.accordion-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important;
  gap: 20px !important;
  padding: 12px 0 28px !important;
}

.card-item-box {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  background: #FFFFFF;
  box-shadow: 0 6px 20px rgba(0,0,0,.08);
  border: 3px solid transparent;
  transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  user-select: none;
}

body.dark .card-item-box, html.dark .card-item-box {
  background: rgba(255,255,255,.05);
  box-shadow: 0 6px 24px rgba(0,0,0,.4);
  border-color: rgba(255,255,255,.08);
}

.card-item-box:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 32px rgba(255,109,0,.3);
  border-color: #FF6D00;
}

.card-item-fav-direct {
  position: absolute;
  top: 10px;
  left: 10px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255,255,255,.92);
  color: #FF1744;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  cursor: pointer;
  z-index: 25;
  box-shadow: 0 3px 10px rgba(0,0,0,.2);
  transition: transform .2s, background .2s;
  outline: none;
}

.card-item-fav-direct:hover { transform: scale(1.18); background: #ffffff; }
.card-item-fav-direct.is-fav { background: #FF1744; color: #ffffff; box-shadow: 0 4px 14px rgba(255,23,68,.4); }

.card-item-image-wrap {
  width: 100%;
  height: 180px;
  background: linear-gradient(180deg, #F0F4F8 0%, #E2E8F0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

body.dark .card-item-image-wrap, html.dark .card-item-image-wrap {
  background: rgba(255,255,255,.03);
}

.card-item-image-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
.card-item-box:hover .card-item-image-wrap img { transform: scale(1.06); }
.card-item-fallback-icon { font-size: 3.8rem; line-height: 1; }

.card-item-footer-bar {
  background: linear-gradient(135deg, #FF6D00 0%, #FF5722 100%);
  color: #ffffff;
  padding: 12px 10px;
  text-align: center;
  font-weight: 900;
  font-size: 1.05rem;
  line-height: 1.25;
  border-bottom-left-radius: 17px;
  border-bottom-right-radius: 17px;
  min-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-item-name {
  color: #ffffff !important;
  font-weight: 900;
  font-size: 1.05rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

.card-item-info {
  position: absolute;
  top: 8px;
  left: 44px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #0288D1;
  color: #ffffff;
  font-family: serif;
  font-weight: bold;
  font-style: italic;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 20;
  box-shadow: 0 3px 8px rgba(0,0,0,.3);
  border: none;
  cursor: pointer;
  outline: none;
  transition: transform .18s, background .18s;
}

.card-item-badge {
  position: absolute;
  bottom: 8px;
  right: 8px;
  top: auto !important;
  left: auto !important;
  background: linear-gradient(135deg, #FF1744, #D50000);
  color: #ffffff;
  font-size: .75rem;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 50px;
  z-index: 15;
  box-shadow: 0 3px 10px rgba(213,0,0,.4);
  pointer-events: none;
}

.card-info-popover {
  position: absolute;
  top: 46px;
  right: 8px;
  left: 8px;
  background: #181E2B;
  color: #ffffff;
  border-radius: 16px;
  padding: 16px 14px;
  z-index: 80;
  box-shadow: 0 12px 32px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.1);
  text-align: center;
  direction: rtl;
}

.popover-arrow {
  position: absolute;
  top: -8px;
  left: 18px;
  width: 0; height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 8px solid #181E2B;
}

.popover-desc { font-size: .88rem; font-weight: 700; line-height: 1.45; color: #E2E8F0; margin: 0 0 14px; }
.popover-actions { display: flex; flex-direction: column; gap: 8px; }
.popover-btn { display: flex; align-items: center; justify-content: center; width: 100%; padding: 10px 14px; border-radius: 50px; font-family: inherit; font-size: .88rem; font-weight: 800; border: none; cursor: pointer; text-decoration: none; transition: transform .18s; box-sizing: border-box; }
.popover-btn--primary { background: linear-gradient(135deg, #FF6D00, #FF3D00); color: #fff !important; }
.popover-btn--fav { background: linear-gradient(135deg, #FF1744, #D50000); color: #fff !important; }

/* Empty state */
.category-empty-state { text-align: center; padding: 48px 16px; background: #FFFFFF; border-radius: 24px; box-shadow: 0 6px 20px rgba(0,0,0,.06); margin-bottom: 30px; }
body.dark .category-empty-state, html.dark .category-empty-state { background: rgba(255,255,255,.05); }
.empty-icon { font-size: 3.5rem; margin-bottom: 12px; }
.category-empty-state h3 { font-size: 1.4rem; font-weight: 900; margin-bottom: 6px; }
.category-empty-state p { color: #64748B; margin-bottom: 18px; }
body.dark .category-empty-state p, html.dark .category-empty-state p { color: #94A3B8; }

/* Random Picker Modal */
.random-picker-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px; }
.random-picker-modal[hidden] { display: none !important; }
.random-picker-backdrop { position: absolute; inset: 0; background: rgba(15,17,23,.75); backdrop-filter: blur(8px); }
.random-picker-dialog { position: relative; z-index: 10; width: 100%; max-width: 440px; background: #181E2B; color: #fff; border-radius: 28px; padding: 32px 24px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,.5), 0 0 0 2px rgba(255,255,255,.1); animation: modalPop .3s ease; }
@keyframes modalPop { from { opacity: 0; transform: scale(.9); } to { opacity: 1; transform: scale(1); } }
.random-picker-close { position: absolute; top: 16px; left: 16px; width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.1); color: #fff; border: none; font-size: 20px; cursor: pointer; }
.spinner-dice-anim { font-size: 4rem; animation: diceSpin 1s infinite linear; margin-bottom: 14px; }
@keyframes diceSpin { 0% { transform: rotate(0deg) scale(1); } 50% { transform: rotate(180deg) scale(1.15); } 100% { transform: rotate(360deg) scale(1); } }
.spinner-title { font-size: 1.4rem; font-weight: 900; margin-bottom: 4px; color: #fff; }
.spinner-sub { color: #94A3B8; font-size: .95rem; }
.result-badge-icon { font-size: 4.2rem; margin-bottom: 8px; animation: bounceIn .4s ease; }
@keyframes bounceIn { 0% { transform: scale(0); } 60% { transform: scale(1.2); } 100% { transform: scale(1); } }
.result-title { font-size: 1.7rem; font-weight: 900; color: #FF6D00; margin-bottom: 6px; }
.result-desc { color: #CBD5E1; font-size: .95rem; margin-bottom: 24px; }
.result-actions { display: flex; flex-direction: column; gap: 12px; }

/* Responsive Grid */
@media (max-width: 600px) {
  .accordion-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 14px !important; }
  .card-item-image-wrap { height: 145px !important; }
  .card-item-footer-bar { padding: 10px 6px !important; font-size: .92rem !important; min-height: 44px !important; }
  .card-item-name { font-size: .92rem !important; }
  .card-item-fav-direct { width: 28px !important; height: 28px !important; font-size: 14px !important; top: 6px !important; left: 6px !important; }
  .card-item-info { width: 28px !important; height: 28px !important; font-size: 14px !important; top: 6px !important; left: 38px !important; }
  .card-item-badge { bottom: 6px !important; right: 6px !important; font-size: .7rem !important; padding: 2px 8px !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── Audio Chime Helper (Intact & Preserved) ─────────────────── */
  function playSound(type) {
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var osc = ctx.createOscillator();
      var gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      if (type === 'fav') {
        osc.frequency.setValueAtTime(523.25, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(659.25, ctx.currentTime + 0.15);
        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
        osc.start(); osc.stop(ctx.currentTime + 0.25);
      } else if (type === 'pick') {
        osc.frequency.setValueAtTime(440, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.3);
        gain.gain.setValueAtTime(0.25, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
        osc.start(); osc.stop(ctx.currentTime + 0.35);
      }
    } catch(e) {}
  }

  /* ── Favorites Manager (LocalStorage) ───────────────────────── */
  function getFavorites() {
    try {
      return JSON.parse(localStorage.getItem('swalif_favorites') || '[]');
    } catch(e) { return []; }
  }

  function saveFavorites(favs) {
    try {
      localStorage.setItem('swalif_favorites', JSON.stringify(favs));
    } catch(e) {}
    updateFavUI();
  }

  function toggleFavorite(catId, catName) {
    catId = String(catId);
    var favs = getFavorites();
    var idx = favs.indexOf(catId);
    if (idx >= 0) {
      favs.splice(idx, 1);
      showToast('تمت إزالة ' + catName + ' من المفضلة', 'info');
    } else {
      favs.push(catId);
      playSound('fav');
      showToast('تمت إضافة ' + catName + ' إلى المفضلة ❤️', 'success');
    }
    saveFavorites(favs);
  }

  function updateFavUI() {
    var favs = getFavorites();
    var countEl = document.getElementById('favCount');
    var topCountEl = document.getElementById('topFavCount');
    if (countEl) countEl.textContent = favs.length;
    if (topCountEl) topCountEl.textContent = '(' + favs.length + ')';

    document.querySelectorAll('[data-fav-card-btn]').forEach(function (btn) {
      var catId = String(btn.dataset.categoryId || '');
      var isFav = favs.includes(catId);
      btn.classList.toggle('is-fav', isFav);
      btn.querySelector('.fav-heart-icon').textContent = isFav ? '❤️' : '🤍';
    });

    document.querySelectorAll('[data-fav-btn]').forEach(function (btn) {
      var catId = String(btn.dataset.categoryId || '');
      var isFav = favs.includes(catId);
      btn.classList.toggle('is-fav', isFav);
      btn.innerHTML = isFav ? 'في المفضلة ❤️' : 'أضف إلى المفضلة 🤍';
    });
  }

  updateFavUI();

  // Heart button direct click
  document.querySelectorAll('[data-fav-card-btn]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      e.preventDefault();
      var catId = btn.dataset.categoryId;
      var catName = btn.dataset.categoryName || 'الفئة';
      if (catId) toggleFavorite(catId, catName);
    });
  });

  // Heart button popover click
  document.querySelectorAll('[data-fav-btn]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      e.preventDefault();
      var catId = btn.dataset.categoryId;
      var catName = btn.dataset.categoryName || 'الفئة';
      if (catId) toggleFavorite(catId, catName);
      var popover = btn.closest('[data-info-popover]');
      if (popover) popover.hidden = true;
    });
  });

  /* ── Filter Functionality ────────────────────────── */
  function applyFilter(filterVal) {
    document.querySelectorAll('.pill-btn').forEach(function (p) {
      p.classList.toggle('active', p.dataset.filter === filterVal);
    });

    document.querySelectorAll('[data-top-filter]').forEach(function (p) {
      p.classList.toggle('is-active-pill', p.dataset.topFilter === filterVal);
    });

    var emptyState = document.getElementById('categoryEmpty');
    var visibleTotal = 0;

    if (filterVal === 'all') {
      document.querySelectorAll('.card-item-box').forEach(function (c) { c.style.display = ''; visibleTotal++; });
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        sec.style.display = '';
        var hdr = sec.querySelector('.accordion-header');
        var bdy = sec.querySelector('.accordion-body');
        var tog = sec.querySelector('.accordion-toggle-circle');
        hdr.classList.add('is-open');
        bdy.classList.add('is-open');
        if (tog) tog.textContent = '−';
      });
      if (emptyState) emptyState.hidden = true;
      return;
    }

    if (filterVal === 'favorites' || filterVal === 'المفضلة') {
      var favs = getFavorites();
      if (favs.length === 0) {
        showToast('لم تُضف أي فئة للمفضلة بعد. اضغط ❤️ على أي فئة لإضافتها!', 'info');
      }
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        var hasVisible = false;
        sec.querySelectorAll('.card-item-box').forEach(function (card) {
          var cid = String(card.dataset.categoryId || '');
          var matches = favs.includes(cid);
          card.style.display = matches ? '' : 'none';
          if (matches) { hasVisible = true; visibleTotal++; }
        });
        sec.style.display = hasVisible ? '' : 'none';
      });
      if (emptyState) emptyState.hidden = visibleTotal > 0;
      return;
    }

    if (filterVal === 'صممت لك') {
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        var hasVisible = false;
        sec.querySelectorAll('.card-item-box').forEach(function (card) {
          var qCount = parseInt(card.dataset.questions || '0', 10);
          var matches = qCount >= 10 || card.dataset.name.includes('أشعار') || card.dataset.name.includes('أمثال') || card.dataset.name.includes('ألعاب');
          card.style.display = matches ? '' : 'none';
          if (matches) { hasVisible = true; visibleTotal++; }
        });
        sec.style.display = hasVisible ? '' : 'none';
      });
      if (emptyState) emptyState.hidden = visibleTotal > 0;
      return;
    }

    if (filterVal === 'إمارات') {
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        var secName = (sec.dataset.classificationName || '').toLowerCase();
        var hasVisible = false;
        sec.querySelectorAll('.card-item-box').forEach(function (card) {
          var name = (card.dataset.name || '').toLowerCase();
          var matches = name.includes('إمارات') || name.includes('امارات') || name.includes('دبي') || name.includes('أبوظبي') || name.includes('تاريخ') || name.includes('ثقافة') || secName.includes('إمارات');
          card.style.display = matches ? '' : 'none';
          if (matches) { hasVisible = true; visibleTotal++; }
        });
        sec.style.display = hasVisible ? '' : 'none';
      });
      if (emptyState) emptyState.hidden = visibleTotal > 0;
      return;
    }

    document.querySelectorAll('.accordion-section').forEach(function (sec) {
      var secName = (sec.dataset.classificationName || '').toLowerCase();
      var hasVisible = false;

      sec.querySelectorAll('.card-item-box').forEach(function (card) {
        var cardFilter = card.dataset.filter || '';
        var cardGroup  = card.dataset.group || '';
        var cardName   = (card.dataset.name || '').toLowerCase();

        var matches = (cardFilter === filterVal || cardGroup === filterVal || cardName.includes(filterVal.toLowerCase()) || secName.includes(filterVal.toLowerCase()));
        card.style.display = matches ? '' : 'none';
        if (matches) { hasVisible = true; visibleTotal++; }
      });

      sec.style.display = hasVisible ? '' : 'none';
    });

    if (emptyState) emptyState.hidden = visibleTotal > 0;
  }

  /* ── Top Nav Pills Clicks ─────────────────── */
  document.querySelectorAll('[data-top-filter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var filterVal = btn.dataset.topFilter;
      if (filterVal === 'فكر') {
        openRandomPickerModal();
        return;
      }
      applyFilter(filterVal);
    });
  });

  /* ── Filter Pills Clicks ─────────────────── */
  document.querySelectorAll('.pill-btn').forEach(function (pill) {
    pill.addEventListener('click', function () {
      applyFilter(pill.dataset.filter);
    });
  });

  /* ── Search Input ────────────────────────── */
  var searchInput = document.getElementById('categorySearch');
  var resetSearchBtn = document.getElementById('resetSearchBtn');

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.trim().toLowerCase();
      if (!q) {
        applyFilter('all');
        return;
      }
      var visibleCount = 0;
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        var hasVis = false;
        sec.querySelectorAll('.card-item-box').forEach(function (card) {
          var name = (card.dataset.name || '').toLowerCase();
          var show = name.includes(q);
          card.style.display = show ? '' : 'none';
          if (show) { hasVis = true; visibleCount++; }
        });
        sec.style.display = hasVis ? '' : 'none';
      });
      var emptyState = document.getElementById('categoryEmpty');
      if (emptyState) emptyState.hidden = visibleCount > 0;
    });
  }

  if (resetSearchBtn) {
    resetSearchBtn.addEventListener('click', function () {
      if (searchInput) searchInput.value = '';
      applyFilter('all');
    });
  }

  /* ── Card Click vs Info Popover ────────────────────── */
  document.querySelectorAll('.card-item-box').forEach(function (card) {
    card.addEventListener('click', function (e) {
      if (e.target.closest('[data-info-toggle]') || e.target.closest('[data-info-popover]') || e.target.closest('[data-fav-card-btn]')) {
        return;
      }
      var lockMsg = card.dataset.subscribeMessage;
      if (card.classList.contains('is-locked') && lockMsg) {
        showToast(lockMsg, 'error');
        return;
      }
      var url = card.dataset.cardUrl;
      if (url) window.location.href = url;
    });
  });

  /* ── Info Popover Toggle ───────────────── */
  document.querySelectorAll('[data-info-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      e.preventDefault();
      var card = btn.closest('.card-item-box');
      var popover = card.querySelector('[data-info-popover]');
      var isHidden = popover.hidden;
      document.querySelectorAll('[data-info-popover]').forEach(function (p) { p.hidden = true; });
      if (isHidden) popover.hidden = false;
    });
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-info-popover]') && !e.target.closest('[data-info-toggle]')) {
      document.querySelectorAll('[data-info-popover]').forEach(function (p) { p.hidden = true; });
    }
  });

  /* ── Accordion toggle ───────────────────────────── */
  document.querySelectorAll('.accordion-header').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var targetId = btn.dataset.target;
      var body     = document.getElementById(targetId);
      var toggle   = btn.querySelector('.accordion-toggle-circle');
      var isOpen   = btn.classList.contains('is-open');

      btn.classList.toggle('is-open', !isOpen);
      btn.setAttribute('aria-expanded', String(!isOpen));
      body.classList.toggle('is-open', !isOpen);
      if (toggle) toggle.textContent = isOpen ? '+' : '−';
    });
  });

  /* ── Random Picker Modal (فكر وابدأ) ────────────────────────── */
  var randomModal = document.getElementById('randomPickerModal');
  var closeRandomBtn = document.getElementById('closeRandomModal');
  var backdrop = document.getElementById('randomPickerBackdrop');
  var spinnerWrap = document.getElementById('randomSpinnerWrap');
  var resultWrap = document.getElementById('randomResultWrap');
  var spinAgainBtn = document.getElementById('spinAgainBtn');

  function openRandomPickerModal() {
    var cards = Array.from(document.querySelectorAll('.card-item-box:not(.is-locked)'));
    if (cards.length === 0) cards = Array.from(document.querySelectorAll('.card-item-box'));
    if (cards.length === 0) return;

    randomModal.hidden = false;
    spinnerWrap.style.display = 'block';
    resultWrap.style.display = 'none';

    playSound('pick');

    setTimeout(function () {
      var randomCard = cards[Math.floor(Math.random() * cards.length)];
      var name = randomCard.dataset.name || 'فئة عشوائية';
      var url  = randomCard.dataset.cardUrl || '#';

      document.getElementById('randomResultTitle').textContent = name;
      document.getElementById('randomPlayLink').href = url;

      spinnerWrap.style.display = 'none';
      resultWrap.style.display = 'block';
    }, 1100);
  }

  if (closeRandomBtn) closeRandomBtn.addEventListener('click', function() { randomModal.hidden = true; });
  if (backdrop) backdrop.addEventListener('click', function() { randomModal.hidden = true; });
  if (spinAgainBtn) spinAgainBtn.addEventListener('click', openRandomPickerModal);

  function showToast(msg, type) {
    var stack = document.getElementById('toastStack');
    if (!stack) {
      stack = document.createElement('div');
      stack.id = 'toastStack';
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    var toast = document.createElement('div');
    toast.className = 'toast toast--' + (type || 'error');
    toast.innerHTML = '<span class="toast__icon">' + (type === 'success' ? '✅' : '⚠️') + '</span><span class="toast__msg">' + msg + '</span><button type="button" class="toast__close">&times;</button>';
    stack.appendChild(toast);
    setTimeout(function () { toast.classList.add('is-visible'); }, 60);
    setTimeout(function () { toast.remove(); }, 4200);
  }
});
</script>

</x-layouts.app>
