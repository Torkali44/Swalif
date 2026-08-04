@php
  $periodOf = fn ($type) => match ($type) {
      'weekly' => 'أسبوع',
      'monthly' => 'شهر',
      'yearly' => 'سنة',
      default => 'مدة',
  };
@endphp

<x-layouts.app title="الاشتراكات — سوالف">
<div class="home subscribe-page">
  <section class="hp-hero subscribe-hero">
    <div class="hp-hero__blob hp-hero__blob--1"></div>
    <div class="hp-hero__blob hp-hero__blob--2"></div>
    <div class="container">
      <x-back-button :href="route('home')" />
      <div class="subscribe-hero__text">
        <span class="chip chip--soft">💎 باقات الاشتراك</span>
        <h1 class="hp-hero__title">افتح كل التحديات<br>مع <span>سوالف</span></h1>
        <p class="hp-hero__sub">جرّب فئة واحدة مجانًا، ثم اشترك وافتح كل التحديات بلا حدود.</p>
      </div>
    </div>
  </section>

  <section class="hp-section" id="plans">
    <div class="container">
      @if(session('success'))
        <div class="free-lock-banner" style="margin:0 0 18px;padding:14px 16px;border-radius:16px;background:rgba(0,200,83,.12);border:1px solid rgba(0,200,83,.28);font-weight:700;color:#00843D">
          {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div class="free-lock-banner" style="margin:0 0 18px;padding:14px 16px;border-radius:16px;background:rgba(255,23,68,.1);border:1px solid rgba(255,23,68,.25);font-weight:700">
          {{ session('error') }}
        </div>
      @endif

      @if(!empty($activeSubscription))
        <div style="margin:0 0 22px;padding:18px 20px;border-radius:18px;background:rgba(0,229,255,.08);border:1px solid rgba(0,229,255,.25)">
          <b style="display:block;font-size:18px;margin-bottom:6px">اشتراكك الحالي نشط ✅</b>
          <div style="font-weight:700;opacity:.9">
            الباقة: {{ $activeSubscription->plan?->name ?? '—' }}
            · يبدأ: <span dir="ltr">{{ optional($activeSubscription->starts_at)->format('Y-m-d H:i') }}</span>
            · ينتهي: <span dir="ltr">{{ optional($activeSubscription->ends_at)->format('Y-m-d H:i') }}</span>
          </div>
          <p style="margin:8px 0 0;font-weight:700">كل الفئات مفتوحة لحد ما الاشتراك يخلص.</p>
        </div>
      @endif

      {{-- ============ OPENING OFFER BANNER ============ --}}
      {{-- Critical CSS inline — displays correctly on server regardless of Vite build --}}
      <style>
        .launch-offer{max-width:1140px;margin:20px auto;display:grid;grid-template-columns:240px 1fr 320px;gap:28px;align-items:center;background:linear-gradient(135deg,#FFFDF8 0%,#FFF5EA 50%,#FFEFE0 100%);border:2px solid #FFE0B2;border-radius:28px;padding:32px 36px;box-shadow:0 14px 40px rgba(255,107,44,.09),inset 0 2px 0 rgba(255,255,255,.9);position:relative;overflow:hidden;direction:rtl}
        .launch-offer::before{content:"";position:absolute;width:280px;height:280px;background:radial-gradient(circle,rgba(255,140,0,.12) 0%,transparent 70%);top:-80px;right:-80px;border-radius:50%;pointer-events:none}
        .launch-offer::after{content:"";position:absolute;width:240px;height:240px;background:radial-gradient(circle,rgba(255,61,0,.08) 0%,transparent 70%);bottom:-80px;left:-60px;border-radius:50%;pointer-events:none}
        .offer-left{position:relative;display:flex;align-items:center;justify-content:center;z-index:2}
        .offer-left-wrap{position:relative;width:190px;height:190px;display:flex;align-items:center;justify-content:center}
        .offer-left-img{width:100%;height:100%;object-fit:contain;filter:drop-shadow(0 14px 24px rgba(124,58,237,.25));animation:giftFloat 3.5s ease-in-out infinite alternate}
        @keyframes giftFloat{0%{transform:translateY(0) rotate(0deg)}100%{transform:translateY(-8px) rotate(-2deg)}}
        .offer-star-badge{position:absolute;bottom:4px;left:12px;width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#FFB300,#FF6D00);color:#fff;border:3px solid #FFF;box-shadow:0 6px 16px rgba(255,140,0,.4);display:flex;align-items:center;justify-content:center;font-size:22px;z-index:3;animation:starPulse 2.5s ease-in-out infinite alternate}
        @keyframes starPulse{0%{transform:scale(1)}100%{transform:scale(1.12)}}
        .offer-content{position:relative;z-index:2;text-align:center;display:flex;flex-direction:column;align-items:center;gap:8px}
        .offer-badge{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#FF5722,#FF9800);color:#fff;padding:6px 22px;border-radius:50px;font-weight:800;font-size:14px;box-shadow:0 4px 14px rgba(255,87,34,.35);margin-bottom:4px}
        .offer-content h2{font-size:34px;font-weight:900;color:#111827;margin:0;line-height:1.25}
        .offer-content h2 span{background:linear-gradient(135deg,#FF3D00,#FF9100);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .offer-subtitle{font-size:16px;font-weight:700;color:#374151;margin:0}
        .offer-subtext{font-size:14px;font-weight:600;color:#6B7280;margin:0 0 8px}
        .offer-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:14px 44px;background:linear-gradient(135deg,#FF3D00 0%,#FF9100 100%);color:#fff!important;font-size:18px;font-weight:900;text-decoration:none;border-radius:50px;box-shadow:0 10px 28px rgba(255,61,0,.38);transition:all .25s ease;border:1px solid rgba(255,255,255,.2)}
        .offer-btn:hover{transform:translateY(-3px) scale(1.03);box-shadow:0 16px 36px rgba(255,61,0,.5);color:#fff!important}
        .offer-features{display:flex;flex-direction:column;gap:14px;z-index:2}
        .feature{display:flex;align-items:center;gap:14px;background:rgba(255,255,255,.9);backdrop-filter:blur(8px);padding:12px 18px;border-radius:18px;border:1px solid rgba(255,224,178,.6);box-shadow:0 4px 12px rgba(0,0,0,.03);transition:transform .2s ease}
        .feature:hover{transform:translateX(-4px)}
        .feature .icon{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
        .feature .icon--confetti{background:#FFF3E0}
        .feature .icon--clock{background:#F3E8FF}
        .feature .icon--gem{background:#FCE7F3}
        .feature h4{font-size:15px;font-weight:800;color:#111827;margin:0 0 2px}
        .feature p{font-size:13px;font-weight:600;color:#6B7280;margin:0}
        @media(max-width:992px){
          .launch-offer{grid-template-columns:1fr;gap:22px;padding:26px 20px;text-align:center}
          .offer-left{justify-content:center}
          .offer-btn{width:100%;max-width:320px}
        }
      </style>

      <div class="launch-offer" style="margin-bottom:38px;">
        <div class="offer-left">
          <div class="offer-left-wrap">
            <img
              src="{{ asset('images/gift-box.webp') }}"
              onerror="this.onerror=null;this.src='{{ asset('images/gift-box.png') }}'"
              alt="عرض الافتتاح"
              class="offer-left-img"
              loading="eager"
              decoding="async"
            >
            <div class="offer-star-badge" title="عرض مميز">★</div>
          </div>
        </div>

        <div class="offer-content">
          <span class="offer-badge">🎉 عرض الافتتاح المميز</span>
          <h2>أهلاً بك في <span>سوالف!</span> 🔥</h2>
          <p class="offer-subtitle">إذا تحب التحدي والفراسة, فهذا مكانك! 😍</p>
          <p class="offer-subtext">أسئلة وألعاب تناسب كل الفئات بسعر 5 دراهم فقط.</p>
          @auth
            {{-- المستخدم مسجل → نمرر الدفع عبر الـ controller لتسجيل الـ payment أولاً --}}
            <form method="POST" action="{{ route('subscription.opening_offer') }}" style="width:100%;display:flex;justify-content:center;">
              @csrf
              <button type="submit" class="offer-btn" style="border:none;cursor:pointer;">
                <span class="arrow">&lt;</span>
                <span>ادفع 5 دراهم واشترك الآن</span>
                <span>🚀</span>
              </button>
            </form>
          @else
            {{-- الزائر غير مسجل → حوّله لصفحة إنشاء الحساب أولاً --}}
            <div style="display:flex;flex-direction:column;align-items:center;gap:10px;width:100%">
              <a href="{{ route('register') }}" class="offer-btn">
                <span>📝</span>
                <span>أنشئ حسابك واستفد من العرض</span>
                <span>🚀</span>
              </a>
              <p style="font-size:13px;font-weight:600;color:#6B7280;margin:0">لازم تكون عامل حساب عشان نقدر نفعّل اشتراكك بعد الدفع</p>
            </div>
          @endauth
        </div>

        <div class="offer-features">
          <div class="feature">
            <div class="icon icon--confetti">🎊</div>
            <div>
              <h4>أسئلة وألعاب متنوعة</h4>
              <p>تناسب جميع الأعمار والاهتمامات</p>
            </div>
          </div>

          <div class="feature">
            <div class="icon icon--clock">⏰</div>
            <div>
              <h4>لفترة محدودة</h4>
              <p>العرض متاح الآن فقط!</p>
            </div>
          </div>

          <div class="feature">
            <div class="icon icon--gem">💎</div>
            <div>
              <h4>اشتراك لمدة 24 ساعة فقط</h4>
              <p>فرصة ذهبية لا تفوتك!</p>
            </div>
          </div>
        </div>
      </div>

      <div class="hp-head hp-head--center">
        <div>
          <h2>جميع الباقات</h2>
          <p>الاشتراك يتفعّل بعد تأكيد الدفع فقط، ويظل مفتوح للفترة المحددة</p>
        </div>
      </div>

      <div class="subscribe-grid">
        @forelse($plans as $plan)
          @php
            $period = $periodOf($plan->type);
          @endphp
          <article class="hp-plan {{ $plan->is_recommended ? 'is-featured' : '' }}">
            @if($plan->is_recommended)
              <span class="hp-plan__badge">الأكثر شعبية</span>
            @endif
            <span class="hp-plan__icon">{{ $plan->icon ?: '💎' }}</span>
            <h3>{{ $plan->name }}</h3>
            <div class="hp-plan__price">
              @if($plan->old_price)
                <del class="hp-plan__old">{{ number_format((float) $plan->old_price) }}</del>
              @endif
              <b>{{ number_format((float) $plan->price) }}</b>
              <span>{{ $plan->currency ?: 'AED' }} / {{ $period }} · {{ $plan->duration_days }} يومًا</span>
            </div>
            <ul>
              @forelse($plan->features ?? [] as $feature)
                <li>{{ $feature }}</li>
              @empty
                <li>فتح جميع الفئات</li>
                <li>لعب غير محدود أثناء الاشتراك</li>
              @endforelse
            </ul>

            @auth
              <form method="POST" action="{{ route('subscription.checkout', $plan) }}">
                @csrf
                <button class="btn {{ $plan->is_recommended ? 'btn--primary' : 'btn--soft' }} btn--block" type="submit">
                  {{ filled($plan->stripe_checkout_url) ? 'ادفع الآن' : 'اشترك الآن' }}
                </button>
              </form>
            @else
              <a class="btn btn--primary btn--block" href="{{ route('login') }}">سجّل دخولك أولًا</a>
            @endauth
          </article>
        @empty
          <p class="muted" style="grid-column:1/-1;text-align:center">لا توجد باقات متاحة حاليًا.</p>
        @endforelse
      </div>
    </div>
  </section>
</div>
</x-layouts.app>
