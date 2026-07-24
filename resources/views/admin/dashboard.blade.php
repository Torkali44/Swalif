<x-layouts.admin>
  <x-slot:heading>نظرة عامة</x-slot:heading>
  <x-slot:subheading>ملخص سريع عن اللعبة والمحتوى والاشتراكات</x-slot:subheading>

  @if($expiringSubscriptions->isNotEmpty())
    <div class="panel panel-warning">
      <div class="panel-head">
        <h3>⚠ اشتراكات تنتهي خلال 7 أيام ({{ $stats['expiring_soon'] }})</h3>
        <a href="{{ route('admin.users.index', ['subscription' => 'expiring']) }}" class="link-more">عرض اللاعبين ←</a>
      </div>
      <div class="mini-list">
        @foreach($expiringSubscriptions as $sub)
          <div class="mini-item">
            <span class="q">{{ $sub->user?->name ?? 'لاعب' }} — {{ $sub->plan?->name ?? 'باقة' }}</span>
            <span class="meta">
              <span dir="ltr">ينتهي {{ optional($sub->ends_at)->format('Y-m-d H:i') }}</span>
              <span>{{ optional($sub->ends_at)->diffForHumans() }}</span>
            </span>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  <div class="stat-grid">
    <div class="stat-card grad-fire">
      <div class="stat-label">إجمالي الفئات</div>
      <div class="stat-value">{{ $stats['categories'] }}</div>
      <div class="stat-trend">↑ محتوى نشط</div>
    </div>
    <div class="stat-card grad-cool">
      <div class="stat-label">إجمالي التصنيفات</div>
      <div class="stat-value">{{ $stats['classifications'] }}</div>
      <div class="stat-trend">↑ مجموعات الفئات</div>
    </div>
    <div class="stat-card grad-cool">
      <div class="stat-label">إجمالي الأسئلة</div>
      <div class="stat-value">{{ $stats['questions'] }}</div>
      <div class="stat-trend">↑ عبر كل المستويات</div>
    </div>
    <div class="stat-card grad-gold">
      <div class="stat-label">مشتركون فعّالون</div>
      <div class="stat-value">{{ $stats['subscribers'] }}</div>
      <div class="stat-trend">↑ اشتراكات حالية</div>
    </div>
    <div class="stat-card grad-emerald">
      <div class="stat-label">المستخدمون</div>
      <div class="stat-value">{{ $stats['users'] }}</div>
      <div class="stat-trend">↑ لاعبون مسجّلون</div>
    </div>
    <div class="stat-card grad-fire">
      <div class="stat-label">المديرون</div>
      <div class="stat-value">{{ $stats['admins'] }}</div>
      <div class="stat-trend">↑ حسابات إدارية</div>
    </div>
    <div class="stat-card grad-gold">
      <div class="stat-label">الباقات الفعالة</div>
      <div class="stat-value">{{ $stats['plans'] }}</div>
      <div class="stat-trend">↑ باقات جاهزة للبيع</div>
    </div>
    <div class="stat-card grad-cool">
      <div class="stat-label">باقات مميزة</div>
      <div class="stat-value">{{ $stats['recommended_plans'] }}</div>
      <div class="stat-trend">↑ تظهر كخيار مفضل</div>
    </div>
    <div class="stat-card grad-emerald">
      <div class="stat-label">اشتراكات تنتهي قريبًا</div>
      <div class="stat-value">{{ $stats['expiring_soon'] }}</div>
      <div class="stat-trend">↑ خلال 7 أيام</div>
    </div>
    <div class="stat-card grad-fire">
      <div class="stat-label">لاعبون مقفول لعبهم</div>
      <div class="stat-value">{{ $stats['play_blocked'] }}</div>
      <div class="stat-trend">↑ يحتاجون فتح أو اشتراك</div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h3>اختصارات الإدارة</h3>
      <span class="link-more">أهم المهام اليومية</span>
    </div>
    <div class="admin-quick-grid">
      <a class="quick-card" href="{{ route('admin.questions.create') }}">
        <span>＋</span>
        <b>إضافة سؤال</b>
        <small>أضف محتوى جديدًا بسرعة</small>
      </a>
      <a class="quick-card" href="{{ route('admin.categories.create') }}">
        <span>◌</span>
        <b>إضافة فئة</b>
        <small>نظّم الأسئلة داخل فئة جديدة</small>
      </a>
      <a class="quick-card" href="{{ route('admin.payments.index') }}">
        <span>🧾</span>
        <b>تأكيد مدفوعات</b>
        <small>فعّل الاشتراك بعد الدفع</small>
      </a>
      <a class="quick-card" href="{{ route('admin.subscribers.create') }}">
        <span>◎</span>
        <b>منح اشتراك</b>
        <small>ربط مستخدم بخطة مباشرة</small>
      </a>
      <a class="quick-card" href="{{ route('admin.users.index', ['play' => 'blocked']) }}">
        <span>🔒</span>
        <b>اللاعبون المقفولون</b>
        <small>افتح أو أبقِ القفل</small>
      </a>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h3>الباقات النشطة</h3>
      <a href="{{ route('admin.plans.index') }}" class="link-more">عرض الباقات ←</a>
    </div>
    <div class="mini-list">
      @forelse($activePlans as $plan)
        <div class="mini-item">
          <span class="q">{{ $plan->name }}</span>
          <span class="meta">
            <span>{{ number_format($plan->price) }} {{ $plan->currency === 'AED' ? 'درهم' : $plan->currency }}</span>
            <span>{{ $plan->duration_days }} يوم</span>
          </span>
        </div>
      @empty
        <p class="muted">لا توجد باقات مفعلة بعد.</p>
      @endforelse
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h3>أحدث الأسئلة المضافة</h3>
      <a href="{{ route('admin.questions.index') }}" class="link-more">عرض الكل ←</a>
    </div>
    <div class="mini-list">
      @forelse($recent as $question)
        <div class="mini-item">
          <span class="q">{{ $question->question_text }}</span>
          <span class="meta">
            <span>{{ $question->category->icon }} {{ $question->category->name_ar }}</span>
            <span>{{ $question->points }} نقطة</span>
          </span>
        </div>
      @empty
        <p class="muted">لا توجد أسئلة بعد.</p>
      @endforelse
    </div>
  </div>
</x-layouts.admin>
