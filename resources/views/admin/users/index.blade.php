<x-layouts.admin>
  <x-slot:heading>المستخدمون</x-slot:heading>
  <x-slot:subheading>إدارة المسجلين: تفعيل، إيقاف، وحذف الحسابات</x-slot:subheading>

  <x-back-button :href="route('admin.dashboard')" label="رجوع" />

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.users.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث بالاسم أو البريد…">
    <select class="select" name="role">
      <option value="">كل الأدوار</option>
      <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>مدراء</option>
      <option value="user" @selected(($filters['role'] ?? '') === 'user')>لاعبون</option>
    </select>
    <select class="select" name="status">
      <option value="">كل الحالات</option>
      <option value="active" @selected(($filters['status'] ?? '') === 'active')>مفعّل</option>
      <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>موقوف</option>
    </select>
    <select class="select" name="subscription">
      <option value="">كل الاشتراكات</option>
      <option value="active" @selected(($filters['subscription'] ?? '') === 'active')>لديهم اشتراك نشط</option>
      <option value="none" @selected(($filters['subscription'] ?? '') === 'none')>بدون اشتراك</option>
    </select>
    <button class="btn btn-outline" type="submit">تصفية</button>
    <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">إعادة</a>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>الاسم</th>
          <th>البريد</th>
          <th>الجوال</th>
          <th>الدور</th>
          <th>الحالة</th>
          <th>الاشتراك</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          @php
            $activeSub = $user->subscriptions->first(function ($sub) {
              return $sub->status === 'active' && $sub->ends_at && $sub->ends_at->isFuture();
            });
          @endphp
          <tr class="{{ $user->is_active ? '' : 'is-muted-row' }}">
            <td style="font-weight:700">{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td dir="ltr">{{ $user->phone_code }} {{ $user->phone ?: '—' }}</td>
            <td>
              <span class="status-pill {{ $user->is_admin ? 'admin' : 'on' }}">
                {{ $user->is_admin ? 'مدير' : 'لاعب' }}
              </span>
            </td>
            <td>
              <span class="status-pill {{ $user->is_active ? 'on' : 'off' }}">
                {{ $user->is_active ? 'مفعّل' : 'موقوف' }}
              </span>
            </td>
            <td>
              @if($activeSub)
                <span class="status-pill on">{{ $activeSub->plan?->name }} · {{ $activeSub->ends_at->format('Y-m-d') }}</span>
              @else
                <span class="muted">لا يوجد اشتراك نشط</span>
              @endif
            </td>
            <td class="row-actions">
              @if($user->id !== auth()->id())
                <form class="inline" method="POST" action="{{ route('admin.users.toggleActive', $user) }}">
                  @csrf
                  @method('PATCH')
                  <button class="btn btn-sm {{ $user->is_active ? 'btn-ghost' : 'btn-primary' }}" type="submit">
                    {{ $user->is_active ? 'إيقاف' : 'تفعيل' }}
                  </button>
                </form>
                <form class="inline" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('حذف المستخدم نهائيًا؟')">حذف</button>
                </form>
              @else
                <span class="muted">أنت</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7">لا يوجد مستخدمون.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">{{ $users->links() }}</div>
</x-layouts.admin>
