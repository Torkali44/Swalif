<x-layouts.app>
<section class="page-hero">
  <div class="container">
    <span class="chip">💎 اشتراكات</span>
    <h1>افتح كل التحديات</h1>
    <p>جرّب أول {{ config('game.free_trial_limit') }} أسئلة مجانًا، ثم اختر الباقة المناسبة.</p>
  </div>
</section>

<section class="plans">
  <div class="container">
    <div class="plan-grid">
      @foreach($plans as $plan)
        @php
          $medal = match($plan->type) { 'weekly' => '🥉', 'monthly' => '🥈', 'yearly' => '🥇', default => '💎' };
          $period = match($plan->type) { 'weekly' => 'أسبوع', 'monthly' => 'شهر', 'yearly' => 'سنة', default => 'مدة' };
        @endphp
        <article class="plan {{ $plan->is_recommended ? 'plan--featured' : '' }}">
          @if($plan->is_recommended)<div class="plan__badge">⭐ الأكثر شعبية</div>@endif
          <div class="plan__medal">{{ $medal }}</div>
          <h3>{{ $plan->name }}</h3>
          <div class="plan__price">
            <b>{{ number_format($plan->price) }}</b>
            <span>درهم / {{ $period }} · {{ $plan->duration_days }} يومًا</span>
          </div>
          <ul>
            @foreach($plan->features ?? [] as $feature)
              <li>{{ $feature }}</li>
            @endforeach
          </ul>
          @auth
            <form method="POST" action="{{ route('subscription.checkout', $plan) }}">
              @csrf
              <button class="btn {{ $plan->is_recommended ? 'btn--primary' : 'btn--outline' }} btn--block" type="submit">تفعيل الباقة</button>
            </form>
          @else
            <a class="btn btn--primary btn--block" href="{{ route('login') }}">سجّل دخولك أولاً</a>
          @endauth
        </article>
      @endforeach
    </div>
  </div>
</section>
</x-layouts.app>
