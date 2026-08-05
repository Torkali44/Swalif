<x-layouts.app title="استلام طلب الدفع — سوالف">
<div class="home subscribe-page">
  <section class="hp-hero subscribe-hero">
    <div class="hp-hero__blob hp-hero__blob--1"></div>
    <div class="hp-hero__blob hp-hero__blob--2"></div>
    <div class="container" style="max-width: 640px; margin: 0 auto;">
      <x-back-button :href="route('subscription.index')" label="العودة للباقات" />

      <div class="panel" style="margin-top: 24px; padding: 32px 24px; text-align: center; border-radius: 24px; background: rgba(255,255,255,0.95); box-shadow: 0 20px 40px rgba(0,0,0,0.08);">
        @if(session('success'))
          <div style="margin-bottom: 20px; padding: 14px 16px; border-radius: 16px; background: rgba(0,200,83,.12); border: 1px solid rgba(0,200,83,.28); font-weight: 700; color: #00843D">
            {{ session('success') }}
          </div>
        @endif

        @if(session('info'))
          <div style="margin-bottom: 20px; padding: 14px 16px; border-radius: 16px; background: rgba(0,229,255,.12); border: 1px solid rgba(0,229,255,.28); font-weight: 700; color: #00838F">
            {{ session('info') }}
          </div>
        @endif

        @if(session('error'))
          <div style="margin-bottom: 20px; padding: 14px 16px; border-radius: 16px; background: rgba(255,23,68,.1); border: 1px solid rgba(255,23,68,.25); font-weight: 700; color: #d32f2f">
            {{ session('error') }}
          </div>
        @endif

        <div style="font-size: 54px; margin-bottom: 16px;">⏳</div>
        <h1 style="font-size: 26px; font-weight: 900; margin-bottom: 12px; color: #111827;">شكراً لك! تم استلام طلب الدفع</h1>
        
        <p style="font-size: 16px; color: #4b5563; line-height: 1.6; margin-bottom: 24px;">
          إذا تم الدفع بنجاح عبر Stripe، فسيتم مراجعة العملية وتفعيل اشتراكك خلال دقائق بواسطة الإدارة.
        </p>

        @if($payment)
          <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 16px; padding: 18px; margin-bottom: 24px; text-align: right;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
              <span style="color: #6b7280; font-size: 14px;">رقم العملية (Reference):</span>
              <strong dir="ltr" style="color: #111827; font-family: monospace; font-size: 15px;">{{ $payment->payment_reference ?? 'PAY-'.$payment->id }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
              <span style="color: #6b7280; font-size: 14px;">الباقة:</span>
              <strong style="color: #111827;">{{ $payment->meta['plan_name'] ?? 'باقة الاشتراك' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
              <span style="color: #6b7280; font-size: 14px;">المبلغ:</span>
              <strong dir="ltr" style="color: #111827;">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span style="color: #6b7280; font-size: 14px;">حالة الطلب:</span>
              <span>
                @if($payment->isWaitingReview())
                  <span class="chip chip--warn" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">قيد المراجعة 🔍</span>
                @elseif($payment->isPending())
                  <span class="chip" style="background: #f3f4f6; color: #4b5563;">بانتظار التأكيد ⏳</span>
                @else
                  <span class="chip">{{ $payment->status }}</span>
                @endif
              </span>
            </div>
          </div>
        @endif

        @if($payment && $payment->isPending())
          <form method="POST" action="{{ route('subscription.claim') }}" style="margin-bottom: 16px;">
            @csrf
            <button type="submit" class="btn btn--primary btn--block" style="padding: 14px 24px; font-size: 16px; font-weight: 800;">
              ✅ لقد قمت بالدفع (إرسال للمراجعة)
            </button>
          </form>
        @elseif($payment && $payment->isWaitingReview())
          <div style="padding: 12px; background: #fff7ed; border-radius: 12px; color: #c2410c; font-size: 14px; font-weight: 700; margin-bottom: 16px;">
            تم إرسال إشعارك مسبقاً! الإدارة تراجع العملية حالياً وسيتفعل حسابك فور التأكيد.
          </div>
        @endif

        <a href="{{ route('home') }}" class="btn btn--soft" style="display: inline-block; width: 100%;">
          العودة للرئيسية
        </a>
      </div>
    </div>
  </section>
</div>
</x-layouts.app>
