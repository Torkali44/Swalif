<x-layouts.admin>
  <x-slot:heading>المستخدمون</x-slot:heading>
  <x-slot:subheading>إدارة المسجلين: تفعيل الحساب، قفل/فتح اللعب، وحذف اللاعبين</x-slot:subheading>

  <x-back-button :href="route('admin.dashboard')" label="رجوع" />

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.users.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث بالاسم أو البريد أو الجوال…">
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
    <select class="select" name="play">
      <option value="">كل حالات اللعب</option>
      <option value="blocked" @selected(($filters['play'] ?? '') === 'blocked')>لعب مقفول</option>
      <option value="open" @selected(($filters['play'] ?? '') === 'open')>لعب مفتوح</option>
    </select>
    <select class="select" name="subscription">
      <option value="">كل الاشتراكات</option>
      <option value="active" @selected(($filters['subscription'] ?? '') === 'active')>لديهم اشتراك نشط</option>
      <option value="expiring" @selected(($filters['subscription'] ?? '') === 'expiring')>ينتهي خلال 7 أيام</option>
      <option value="none" @selected(($filters['subscription'] ?? '') === 'none')>بدون اشتراك نشط</option>
    </select>
    <button class="btn btn-outline" type="submit">تصفية</button>
    <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">إعادة</a>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>الاسم</th>
          <th>البريد / الجوال</th>
          <th>الدور</th>
          <th>الحساب</th>
          <th>اللعب</th>
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
            $expiring = $activeSub && $activeSub->ends_at->lte(now()->addDays(7));
          @endphp
          <tr class="{{ $user->is_active ? '' : 'is-muted-row' }}">
            <td style="font-weight:700">{{ $user->name }}</td>
            <td>
              <div>{{ $user->email }}</div>
              <div class="muted" dir="ltr" style="font-size:12px">{{ $user->phone_code }} {{ $user->phone ?: '—' }}</div>
            </td>
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
              @if($user->is_admin)
                <span class="muted">—</span>
              @elseif($user->play_blocked && ! $activeSub)
                <span class="status-pill off">مقفول</span>
              @else
                <span class="status-pill on">مفتوح</span>
              @endif
            </td>
            <td>
              @if($activeSub)
                <span class="status-pill {{ $expiring ? 'admin' : 'on' }}">
                  {{ $activeSub->plan?->name }} · {{ $activeSub->ends_at->format('Y-m-d') }}
                  @if($expiring) ⚠ @endif
                </span>
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
                    {{ $user->is_active ? 'إيقاف حساب' : 'تفعيل حساب' }}
                  </button>
                </form>
                @unless($user->is_admin)
                  <form class="inline" method="POST" action="{{ route('admin.users.togglePlay', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-sm {{ $user->play_blocked ? 'btn-primary' : 'btn-ghost' }}" type="submit"
                      onclick="return confirm('{{ $user->play_blocked ? 'فتح اللعب لهذا اللاعب؟' : 'قفل اللعب؟ مش هيقدر يلعب غير لما تفتحله أو يشترك.' }}')">
                      {{ $user->play_blocked ? 'فتح اللعب' : 'قفل اللعب' }}
                    </button>
                  </form>
                  <form class="inline" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm" style="color: #ef4444; background: transparent; border: 1px solid #ef4444;" type="submit"
                      onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم بشكل نهائي؟')">
                      حذف
                    </button>
                  </form>
                @endunless
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
