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

      <div class="hp-head hp-head--center">
        <div>
          <h2>اختر باقتك</h2>
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
