<x-layouts.admin>
  <x-slot:heading>المشتركين</x-slot:heading>
  <x-slot:subheading>اشتراكات المستخدمين وحالتها</x-slot:subheading>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>المستخدم</th>
          <th>البريد</th>
          <th>الباقة</th>
          <th>الحالة</th>
          <th>ينتهي في</th>
        </tr>
      </thead>
      <tbody>
        @forelse($subscribers as $subscription)
          <tr>
            <td>{{ $subscription->user?->name }}</td>
            <td>{{ $subscription->user?->email }}</td>
            <td>{{ $subscription->plan?->name }}</td>
            <td>{{ $subscription->status }}</td>
            <td>{{ optional($subscription->ends_at)->format('Y-m-d') }}</td>
          </tr>
        @empty
          <tr><td colspan="5">لا يوجد مشتركون بعد.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">{{ $subscribers->links() }}</div>
</x-layouts.admin>
