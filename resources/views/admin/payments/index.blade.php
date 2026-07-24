<x-layouts.admin>
  <x-slot:heading>المدفوعات</x-slot:heading>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>المستخدم</th>
          <th>المبلغ</th>
          <th>البوابة</th>
          <th>الحالة</th>
          <th>الباقة</th>
          <th>التاريخ</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
          @php
            $planName = $payment->meta['plan_name'] ?? ($payment->subscription?->plan?->name ?? '—');
          @endphp
          <tr>
            <td>{{ $payment->id }}</td>
            <td>
              <b>{{ $payment->user?->name ?? '—' }}</b>
              <div class="muted" style="font-size:12px">{{ $payment->user?->email ?? $payment->user?->phone }}</div>
            </td>
            <td dir="ltr">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
            <td>{{ $payment->gateway }}</td>
            <td>
              @if($payment->status === 'paid')
                <span class="chip chip--ok">مدفوع</span>
              @elseif($payment->status === 'pending')
                <span class="chip chip--warn">بانتظار التأكيد</span>
              @else
                <span class="chip">{{ $payment->status }}</span>
              @endif
            </td>
            <td>{{ $planName }}</td>
            <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
            <td>
              @if($payment->status !== 'paid')
                <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" onsubmit="return confirm('تأكيد الدفع وتفعيل الاشتراك؟')">
                  @csrf
                  <button class="btn btn-sm btn-primary" type="submit">تأكيد وتفعيل</button>
                </form>
              @elseif($payment->subscription)
                <a class="btn btn-sm btn-outline" href="{{ route('admin.subscribers.edit', $payment->subscription) }}">الاشتراك</a>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="muted">لا توجد مدفوعات بعد.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:16px">{{ $payments->links() }}</div>
</x-layouts.admin>
