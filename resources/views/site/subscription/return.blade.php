<x-layouts.app title="استلام طلب الدفع — سوالف">
<div class="home subscribe-page">
  <section class="hp-hero subscribe-hero">
    <div class="hp-hero__blob hp-hero__blob--1"></div>
    <div class="hp-hero__blob hp-hero__blob--2"></div>
    <div class="container" style="max-width: 640px; margin: 0 auto;">
      <x-back-button :href="route('subscription.index')" label="العودة للباقات" />

      <div class="panel" style="margin-top: 24px; padding: 36px 28px; text-align: center; border-radius: 24px; background: rgba(255,255,255,0.97); box-shadow: 0 20px 40px rgba(0,0,0,0.08);">

        @if(session('info'))
          <div style="margin-bottom: 20px; padding: 14px 16px; border-radius: 14px; background: rgba(0,229,255,.1); border: 1px solid rgba(0,229,255,.3); font-weight: 700; color: #00838F">
            {{ session('info') }}
          </div>
        @endif

        {{-- Spinning clock animation --}}
        <div style="font-size: 56px; margin-bottom: 18px; animation: spin 4s linear infinite;">⏳</div>

        <h1 style="font-size: 24px; font-weight: 900; margin-bottom: 12px; color: #111827;">
          شكراً! تم استلام طلب الدفع
        </h1>

        <p style="font-size: 16px; color: #4b5563; line-height: 1.7; margin-bottom: 24px;">
          إذا تم الدفع بنجاح عبر Stripe، سيتم تفعيل اشتراكك <strong>تلقائياً خلال ثوانٍ</strong> عبر نظام Stripe الآمن.
          <br><br>
          يمكنك إعادة تحميل هذه الصفحة بعد لحظات للتحقق من حالة الاشتراك.
        </p>

        @if($payment)
          <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 16px; padding: 18px; margin-bottom: 24px; text-align: right;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: center;">
              <span style="color: #6b7280; font-size: 14px;">رقم الطلب:</span>
              <strong dir="ltr" style="font-family: monospace; font-size: 14px; color: #111827; background: #f3f4f6; padding: 3px 10px; border-radius: 8px;">
                {{ $payment->payment_reference ?? 'PAY-'.$payment->id }}
              </strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: center;">
              <span style="color: #6b7280; font-size: 14px;">الباقة:</span>
              <strong style="color: #111827;">{{ $payment->meta['plan_name'] ?? 'باقة الاشتراك' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: center;">
              <span style="color: #6b7280; font-size: 14px;">المبلغ:</span>
              <strong dir="ltr" style="color: #111827;">
                {{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}
              </strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="color: #6b7280; font-size: 14px;">الحالة:</span>
              @if($payment->isPaid())
                <span style="background: #d1fae5; color: #065f46; padding: 3px 12px; border-radius: 20px; font-size: 13px; font-weight: 700;">✅ مدفوع ومفعّل</span>
              @else
                <span style="background: #fef3c7; color: #92400e; padding: 3px 12px; border-radius: 20px; font-size: 13px; font-weight: 700;">⏳ قيد المعالجة</span>
              @endif
            </div>
          </div>
        @endif

        {{-- Auto-refresh after 10 seconds to check if webhook fired --}}
        <div style="margin-bottom: 20px; padding: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; font-size: 14px; color: #14532d;">
          🔄 ستنتقل الصفحة تلقائياً بعد لحظات للتحقق من حالة اشتراكك…
        </div>

        <a href="{{ route('subscription.return') }}" class="btn btn--soft" style="display: block; margin-bottom: 12px; font-weight: 700;">
          🔄 تحقق من الحالة الآن
        </a>
        <a href="{{ route('home') }}" class="btn" style="display: block; color: #6b7280; background: transparent; border: 1px solid #e5e7eb;">
          العودة للرئيسية
        </a>
      </div>
    </div>
  </section>
</div>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

{{-- Auto-redirect to return page after 12 seconds to re-check webhook status --}}
<script>
setTimeout(function () {
  window.location.href = "{{ route('subscription.return') }}";
}, 12000);
</script>
</x-layouts.app>
