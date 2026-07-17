<x-layouts.admin>
  <x-slot:heading>المشتركين</x-slot:heading>
  <x-slot:subheading>إدارة اشتراكات المستخدمين: تفعيل، إلغاء، وتمديد</x-slot:subheading>

  <x-back-button :href="route('admin.dashboard')" label="رجوع" />

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.subscribers.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث بالاسم أو البريد…">
    <select class="select" name="plan_id">
      <option value="">كل الباقات</option>
      @foreach($plans as $plan)
        <option value="{{ $plan->id }}" @selected((string) ($filters['plan_id'] ?? '') === (string) $plan->id)>{{ $plan->name }}</option>
      @endforeach
    </select>
    <select class="select" name="status">
      <option value="">كل الحالات</option>
      <option value="active" @selected(($filters['status'] ?? '') === 'active')>نشط</option>
      <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>ملغي</option>
      <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>منتهي</option>
    </select>
    <button class="btn btn-outline" type="submit">تصفية</button>
    <a class="btn btn-ghost" href="{{ route('admin.subscribers.index') }}">إعادة</a>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>المستخدم</th>
          <th>البريد</th>
          <th>الباقة</th>
          <th>الحالة</th>
          <th>يبدأ</th>
          <th>ينتهي</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @forelse($subscribers as $subscription)
          @php
            $isActive = $subscription->status === 'active' && $subscription->ends_at && $subscription->ends_at->isFuture();
          @endphp
          <tr>
            <td style="font-weight:700">{{ $subscription->user?->name ?? '—' }}</td>
            <td>{{ $subscription->user?->email ?? '—' }}</td>
            <td>{{ $subscription->plan?->name ?? '—' }}</td>
            <td>
              <span class="status-pill {{ $isActive ? 'on' : 'off' }}">
                {{ $isActive ? 'نشط' : ($subscription->status === 'cancelled' ? 'ملغي' : 'منتهي') }}
              </span>
            </td>
            <td>{{ optional($subscription->starts_at)->format('Y-m-d') }}</td>
            <td>{{ optional($subscription->ends_at)->format('Y-m-d') }}</td>
            <td class="row-actions">
              @if($isActive)
                <form class="inline" method="POST" action="{{ route('admin.subscribers.cancel', $subscription) }}">
                  @csrf
                  @method('PATCH')
                  <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('إلغاء الاشتراك؟')">إلغاء</button>
                </form>
              @else
                <form class="inline" method="POST" action="{{ route('admin.subscribers.activate', $subscription) }}">
                  @csrf
                  @method('PATCH')
                  <button class="btn btn-sm btn-primary" type="submit">تفعيل</button>
                </form>
              @endif
              <form class="inline extend-form" method="POST" action="{{ route('admin.subscribers.extend', $subscription) }}">
                @csrf
                @method('PATCH')
                <input type="number" name="days" value="30" min="1" max="365" class="days-inp" title="أيام التمديد" aria-label="أيام التمديد">
                <button class="btn btn-sm btn-outline" type="submit">تمديد</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="empty-cell">لا يوجد مشتركون مطابقون للفلتر.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">{{ $subscribers->links() }}</div>
</x-layouts.admin>
