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
        <p class="hp-hero__sub">جرّب أول {{ config('game.free_trial_limit') }} أسئلة مجانًا، ثم اختر الباقة المناسبة وابدأ اللعب بلا حدود.</p>
      </div>
    </div>
  </section>

  <section class="hp-section" id="plans">
    <div class="container">
      <div class="hp-head hp-head--center">
        <div>
          <h2>اختر باقتك</h2>
          <p>ادفع عبر Stripe بأمان — الرابط يُضبط من لوحة التحكم</p>
        </div>
      </div>

      <div class="subscribe-grid">
        @forelse($plans as $plan)
          @php
            $period = $periodOf($plan->type);
            $hasStripe = filled($plan->stripe_checkout_url);
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
                <li>لعب غير محدود</li>
              @endforelse
            </ul>

            @auth
              @if($hasStripe)
                <a class="btn {{ $plan->is_recommended ? 'btn--primary' : 'btn--soft' }} btn--block"
                   href="{{ $plan->stripe_checkout_url }}"
                   target="_blank"
                   rel="noopener noreferrer">
                  شراء الآن
                </a>
              @else
                <form method="POST" action="{{ route('subscription.checkout', $plan) }}">
                  @csrf
                  <button class="btn {{ $plan->is_recommended ? 'btn--primary' : 'btn--soft' }} btn--block" type="submit">
                    تفعيل الباقة
                  </button>
                </form>
              @endif
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
