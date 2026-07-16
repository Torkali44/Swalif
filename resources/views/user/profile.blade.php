<x-layouts.app>
<section class="section">
  <div class="container" style="display:grid;place-items:center">
    <div class="auth-card" style="text-align:center">
      <div class="avatar" style="width:72px;height:72px;border-radius:50%;background:var(--grad-fire);color:#fff;display:grid;place-items:center;font:900 28px Cairo;margin:0 auto 16px">
        {{ mb_substr($user->name, 0, 1) }}
      </div>
      <h1>{{ $user->name }}</h1>
      <p>{{ $user->email }}</p>
      <p style="margin:12px 0;font-weight:700">
        الاشتراك:
        <span style="color:{{ $user->hasActiveSubscription() ? 'var(--uae-green)' : 'var(--uae-red)' }}">
          {{ $user->hasActiveSubscription() ? 'فعّال' : 'لا يوجد اشتراك فعّال' }}
        </span>
      </p>
      <a class="btn btn--primary" href="{{ route('subscription.index') }}">إدارة الاشتراك</a>
      <a class="btn btn--outline" href="{{ route('history') }}" style="margin-top:10px">سجل الألعاب</a>
    </div>
  </div>
</section>
</x-layouts.app>
