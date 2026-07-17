<x-layouts.admin>
  <x-slot:heading>المستخدمون</x-slot:heading>
  <x-slot:subheading>كل المسجلين في النظام وصلاحياتهم واشتراكاتهم</x-slot:subheading>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>الاسم</th>
          <th>البريد الإلكتروني</th>
          <th>الدور</th>
          <th>الاشتراك الحالي</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
          <tr>
            <td style="font-weight:700">{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
              <span class="badge-level" style="background: {{ $user->is_admin ? 'var(--uae-red)' : 'var(--muted)' }}">
                {{ $user->is_admin ? 'مدير النظام' : 'لاعب / مستخدم' }}
              </span>
            </td>
            <td>
              @php
                $activeSub = $user->subscriptions->firstWhere('status', 'active');
              @endphp
              @if($activeSub)
                <span class="badge-level" style="background:var(--uae-green)">{{ $activeSub->plan?->name }}</span>
              @else
                <span style="color:var(--muted)">لا يوجد اشتراك نشط</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="pagination">{{ $users->links() }}</div>
</x-layouts.admin>
