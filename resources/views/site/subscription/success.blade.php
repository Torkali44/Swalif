<x-layouts.app title="تم تفعيل الاشتراك — سوالف">
<div class="home subscribe-page">
  <section class="hp-hero subscribe-hero">
    <div class="hp-hero__blob hp-hero__blob--1"></div>
    <div class="hp-hero__blob hp-hero__blob--2"></div>
    <div class="container" style="max-width: 640px; margin: 0 auto;">
      <div class="panel" style="margin-top: 36px; padding: 40px 28px; text-align: center; border-radius: 28px; background: rgba(255,255,255,0.98); box-shadow: 0 20px 50px rgba(0,200,83,0.15); border: 2px solid rgba(0,200,83,0.25);">
        
        <div style="font-size: 64px; margin-bottom: 16px;">🎉</div>
        <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 10px; color: #111827;">مبروك! تم تفعيل اشتراكك بنجاح</h1>
        
        <p style="font-size: 16px; color: #4b5563; line-height: 1.6; margin-bottom: 24px;">
          جميع الألعاب والتحديات أصبحت مفتوحة لك الآن بدون حدود!
        </p>

        <div style="background: rgba(0,200,83,0.06); border: 1px solid rgba(0,200,83,0.2); border-radius: 18px; padding: 20px; margin-bottom: 24px; text-align: right;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span style="color: #4b5563; font-size: 15px;">الباقة المفعّلة:</span>
            <strong style="color: #00843D; font-size: 16px;">{{ $subscription->plan?->name ?? 'باقة الاشتراك' }}</strong>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span style="color: #4b5563; font-size: 15px;">تاريخ التفعيل:</span>
            <strong dir="ltr" style="color: #111827;">{{ optional($subscription->starts_at)->format('Y-m-d H:i') }}</strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span style="color: #4b5563; font-size: 15px;">ينتهي في:</span>
            <strong dir="ltr" style="color: #111827;">{{ optional($subscription->ends_at)->format('Y-m-d H:i') }}</strong>
          </div>
        </div>

        <div style="margin-bottom: 20px; padding: 12px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; font-size: 14px; color: #14532d; font-weight: 700;">
          🚀 جاري تحويلك تلقائياً إلى صفحة الألعاب خلال 3 ثوانٍ…
        </div>

        <a href="{{ route('categories.index') }}" class="btn btn--primary btn--block" style="padding: 16px 28px; font-size: 18px; font-weight: 900; border-radius: 50px; text-decoration: none; box-shadow: 0 10px 25px rgba(0,200,83,0.3);">
          🎮 تصفّح الألعاب والبدء الآن
        </a>

      </div>
    </div>
  </section>
</div>

<script>
setTimeout(function() {
  window.location.href = "{{ route('categories.index') }}";
}, 3000);
</script>
</x-layouts.app>
