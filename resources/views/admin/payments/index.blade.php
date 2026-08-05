<x-layouts.admin>
  <x-slot:heading>إدارة المدفوعات</x-slot:heading>
  <x-slot:subheading>مراجعة عمليات الدفع وتأكيد أو إلغاء اشتراكات العملاء</x-slot:subheading>

  <x-back-button :href="route('admin.dashboard')" label="رجوع" />

  {{-- Quick Status Filter Tabs --}}
  <div style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
    <a class="btn {{ empty($filters['status']) ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status' => ''])) }}">
      الكل
    </a>
    <a class="btn {{ ($filters['status'] ?? '') === 'waiting_review' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status' => 'waiting_review'])) }}" style="{{ ($counts->waiting_review ?? 0) > 0 ? 'border-color: #f59e0b; color: #d97706;' : '' }}">
      🔍 بانتظار المراجعة ({{ $counts->waiting_review ?? 0 }})
    </a>
    <a class="btn {{ ($filters['status'] ?? '') === 'pending' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status' => 'pending'])) }}">
      ⏳ معلق ({{ $counts->pending ?? 0 }})
    </a>
    <a class="btn {{ ($filters['status'] ?? '') === 'paid' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status' => 'paid'])) }}">
      ✅ مدفوع ومفعل ({{ $counts->paid ?? 0 }})
    </a>
    <a class="btn {{ ($filters['status'] ?? '') === 'cancelled' ? 'btn-primary' : 'btn-outline' }}" href="{{ route('admin.payments.index', array_merge($filters, ['status' => 'cancelled'])) }}">
      ❌ ملغي ({{ $counts->cancelled ?? 0 }})
    </a>
  </div>

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.payments.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث بالاسم، البريد أو الجوال…">
    @if(!empty($filters['status']))
      <input type="hidden" name="status" value="{{ $filters['status'] }}">
    @endif
    <button class="btn btn-outline" type="submit">بحث</button>
    <a class="btn btn-ghost" href="{{ route('admin.payments.index') }}">إعادة ضبط</a>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>المستخدم</th>
          <th>الباقة</th>
          <th>المبلغ</th>
          <th>الحالة</th>
          <th>التاريخ</th>
          <th>إجراءات الإدارة</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
          @php
            $planName = $payment->meta['plan_name'] ?? ($payment->subscription?->plan?->name ?? '—');
            $ref = $payment->payment_reference ?? ('PAY-'.$payment->id);
          @endphp
          <tr style="{{ $payment->isWaitingReview() ? 'background: #fffbe6;' : '' }}">
            <td dir="ltr" style="font-family: monospace; font-weight: 700; font-size: 13px;">
              {{ $ref }}
            </td>
            <td>
              <b>{{ $payment->user?->name ?? '—' }}</b>
              <div class="muted" style="font-size:12px">{{ $payment->user?->email ?? $payment->user?->phone }}</div>
            </td>
            <td><b>{{ $planName }}</b></td>
            <td dir="ltr" style="font-weight:700;">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
            <td>
              @if($payment->isPaid())
                <span class="chip chip--ok">مدفوع ومفعّل</span>
              @elseif($payment->isWaitingReview())
                <span class="chip chip--warn" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">🔍 طلب مراجعة</span>
              @elseif($payment->isPending())
                <span class="chip" style="background: #f3f4f6; color: #6b7280;">⏳ معلق</span>
              @elseif($payment->isCancelled())
                <span class="chip" style="background: #fee2e2; color: #991b1b;">❌ ملغي</span>
              @else
                <span class="chip">{{ $payment->status }}</span>
              @endif
            </td>
            <td style="font-size: 13px; color: #6b7280;">
              <div>{{ $payment->created_at?->format('Y-m-d H:i') }}</div>
              <div style="font-size: 11px;">{{ $payment->created_at?->diffForHumans() }}</div>
            </td>
            <td>
              <div style="display: flex; gap: 6px; align-items: center;">
                @if($payment->canBeConfirmed())
                  <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" onsubmit="return confirm('تأكيد الدفع وتفعيل الاشتراك للمستخدم؟')">
                    @csrf
                    <button class="btn btn-sm btn-primary" type="submit" style="background: #10b981; border-color: #10b981; color: white;">
                      ✓ Activate (تأكيد وتفعيل)
                    </button>
                  </form>
                  <form method="POST" action="{{ route('admin.payments.cancel', $payment) }}" onsubmit="return confirm('إلغاء عملية الدفع هذه؟')">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-sm btn-outline" type="submit" style="color: #ef4444; border-color: #fca5a5;">
                      ✕ Cancel
                    </button>
                  </form>
                @elseif($payment->isPaid() && $payment->subscription)
                  <a class="btn btn-sm btn-outline" href="{{ route('admin.subscribers.edit', $payment->subscription) }}">
                    عرض الاشتراك
                  </a>
                @else
                  <span class="muted" style="font-size: 12px;">—</span>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="muted" style="text-align: center; padding: 24px;">لا توجد مدفوعات مطابقة للفلتر.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:16px">{{ $payments->links() }}</div>
</x-layouts.admin>
