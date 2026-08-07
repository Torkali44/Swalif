<x-layouts.admin>
  <x-slot:heading>المدفوعات</x-slot:heading>
  <x-slot:subheading>مراجعة عمليات الدفع — التفعيل يتم تلقائياً عبر Stripe Webhook. الأزرار هنا للطوارئ فقط.</x-slot:subheading>

  <x-back-button :href="route('admin.dashboard')" label="رجوع" />

  {{-- Webhook explanation banner --}}
  <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 14px; padding: 14px 18px; margin-bottom: 16px; font-size: 14px; color: #1d4ed8; display: flex; align-items: center; gap: 10px;">
    <span style="font-size: 20px;">🤖</span>
    <div>
      <strong>التفعيل التلقائي عبر Stripe Webhook</strong> — عند دفع العميل، يُفعَّل الاشتراك تلقائياً خلال ثوانٍ.
      <br>أزرار <em>Activate Manual</em> هي للطوارئ فقط في حالات استثنائية.
    </div>
  </div>

  {{-- Status Filter Tabs --}}
  <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
    @php $s = $filters['status'] ?? ''; @endphp
    <a class="btn {{ $s === '' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status'=>''])) }}">
      الكل <span style="opacity:.7;">({{ ($counts->pending ?? 0) + ($counts->waiting_review ?? 0) + ($counts->paid ?? 0) + ($counts->cancelled ?? 0) }})</span>
    </a>
    <a class="btn {{ $s === 'pending' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status'=>'pending'])) }}">
      ⏳ معلق ({{ $counts->pending ?? 0 }})
    </a>
    <a class="btn {{ $s === 'waiting_review' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status'=>'waiting_review'])) }}" style="{{ ($counts->waiting_review ?? 0) > 0 ? 'border-color:#f59e0b;color:#b45309;' : '' }}">
      🔍 مراجعة يدوية ({{ $counts->waiting_review ?? 0 }})
    </a>
    <a class="btn {{ $s === 'paid' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status'=>'paid'])) }}">
      ✅ مدفوع ومفعّل ({{ $counts->paid ?? 0 }})
    </a>
    <a class="btn {{ $s === 'cancelled' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status'=>'cancelled'])) }}">
      ❌ ملغي ({{ $counts->cancelled ?? 0 }})
    </a>
  </div>

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.payments.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث بالاسم أو البريد أو الجوال…">
    @if(!empty($filters['status']))
      <input type="hidden" name="status" value="{{ $filters['status'] }}">
    @endif
    <button class="btn btn-outline" type="submit">بحث</button>
    <a class="btn btn-ghost" href="{{ route('admin.payments.index') }}">مسح</a>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>المستخدم</th>
          <th>الباقة / المبلغ</th>
          <th>الحالة</th>
          <th>التاريخ</th>
          <th>Webhook / Stripe ID</th>
          <th style="white-space:nowrap;">إجراءات الطوارئ</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
          @php
            $planName = $payment->meta['plan_name'] ?? ($payment->subscription?->plan?->name ?? '—');
            $ref      = $payment->payment_reference ?? ('PAY-'.$payment->id);
            $stripeId = $payment->meta['stripe_session_id'] ?? $payment->gateway_reference ?? null;
          @endphp
          <tr style="{{ $payment->isPending() ? 'background:#fffbeb;' : '' }}">
            <td dir="ltr" style="font-family: monospace; font-weight: 700; font-size: 13px;">{{ $ref }}</td>
            <td>
              <b>{{ $payment->user?->name ?? '—' }}</b>
              <div class="muted" style="font-size:12px;">{{ $payment->user?->email ?? $payment->user?->phone ?? '—' }}</div>
            </td>
            <td>
              <div style="font-weight:700;">{{ $planName }}</div>
              <div dir="ltr" style="font-size:13px;color:#6b7280;">{{ number_format((float)$payment->amount, 2) }} {{ $payment->currency }}</div>
            </td>
            <td>
              @if($payment->isPaid())
                <span class="chip chip--ok">✅ مدفوع (Webhook)</span>
              @elseif($payment->isWaitingReview())
                <span class="chip chip--warn" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;">🔍 مراجعة يدوية</span>
              @elseif($payment->isPending())
                <span class="chip" style="background:#fffbeb;color:#92400e;border:1px solid #fcd34d;">⏳ في الانتظار</span>
              @elseif($payment->isCancelled())
                <span class="chip" style="background:#fee2e2;color:#991b1b;">❌ ملغي</span>
              @else
                <span class="chip">{{ $payment->status }}</span>
              @endif
            </td>
            <td style="font-size:13px;color:#6b7280;">
              <div>{{ $payment->created_at?->format('Y-m-d H:i') }}</div>
              <div style="font-size:11px;">{{ $payment->created_at?->diffForHumans() }}</div>
            </td>
            <td style="font-size:12px;color:#6b7280;font-family:monospace;">
              @if($stripeId && str_starts_with($stripeId, 'cs_'))
                <span title="{{ $stripeId }}" style="display:inline-block;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $stripeId }}</span>
              @else
                <span class="muted">—</span>
              @endif
            </td>
            <td>
              <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                @if($payment->canBeConfirmed())
                  {{-- Emergency manual activation --}}
                  <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" onsubmit="return confirm('⚠ تفعيل يدوي للطوارئ فقط. هل أنت متأكد؟')">
                    @csrf
                    <button class="btn btn-sm" type="submit" style="background:#f59e0b;border-color:#f59e0b;color:#fff;white-space:nowrap;">
                      ⚡ Activate (طوارئ)
                    </button>
                  </form>
                  <form method="POST" action="{{ route('admin.payments.cancel', $payment) }}" onsubmit="return confirm('إلغاء عملية الدفع؟')">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-sm btn-outline" type="submit" style="color:#ef4444;border-color:#fca5a5;">
                      ✕ إلغاء
                    </button>
                  </form>
                @elseif($payment->isPaid() && $payment->subscription)
                  <a class="btn btn-sm btn-outline" href="{{ route('admin.subscribers.edit', $payment->subscription) }}">
                    عرض الاشتراك
                  </a>
                @endif

                {{-- Delete action for admin --}}
                <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" onsubmit="return confirm('حذف سجل السداد هذا نهائياً؟ لا يمكن التراجع!')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm" type="submit" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;">
                    🗑 حذف
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="muted" style="text-align:center;padding:28px;">لا توجد مدفوعات.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:16px;">{{ $payments->links() }}</div>
</x-layouts.admin>
