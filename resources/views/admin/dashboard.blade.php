<x-layouts.admin>
  <x-slot:heading>نظرة عامة</x-slot:heading>
  <x-slot:subheading>ملخص سريع عن اللعبة والمحتوى والاشتراكات والمدفوعات</x-slot:subheading>

  @if($pendingPayments->isNotEmpty())
    <div class="panel" style="border: 2px solid #f59e0b; background: #fffbe6; margin-bottom: 20px;">
      <div class="panel-head">
        <h3 style="color: #b45309;">🔍 مدفوعات تنتظر المراجعة والتأكيد ({{ $stats['waiting_payments'] + $stats['pending_payments'] }})</h3>
        <a href="{{ route('admin.payments.index', ['status' => 'waiting_review']) }}" class="link-more">عرض قائمة المدفوعات ←</a>
      </div>
      <div class="mini-list">
        @foreach($pendingPayments as $p)
          <div class="mini-item">
            <span class="q">
              <strong>{{ $p->user?->name ?? 'مستخدم' }}</strong> 
              <span class="muted">({{ $p->payment_reference ?? 'PAY-'.$p->id }})</span>
              — {{ $p->meta['plan_name'] ?? 'باقة' }}
            </span>
            <span class="meta">
              <span dir="ltr">{{ number_format((float)$p->amount, 2) }} {{ $p->currency }}</span>
              <span class="status-pill {{ $p->isWaitingReview() ? 'admin' : 'off' }}">
                {{ $p->isWaitingReview() ? '🔍 طلب مراجعة' : '⏳ معلق' }}
              </span>
              <a href="{{ route('admin.payments.index', ['status' => $p->status]) }}" class="btn btn-sm btn-primary" style="padding: 2px 8px; font-size: 11px;">مراجعة</a>
            </span>
          </div>
        @endforeach
      </div>
    </div>
  @endif



  <div class="stat-grid">
    <div class="stat-card grad-gold" style="border: 2px solid #f59e0b;">
      <div class="stat-label">بانتظار المراجعة</div>
      <div class="stat-value">{{ $stats['waiting_payments'] }}</div>
      <div class="stat-trend">🔍 أكد الدفع الآن</div>
    </div>
    <div class="stat-card grad-fire">
      <div class="stat-label">مدفوعات معلقة</div>
      <div class="stat-value">{{ $stats['pending_payments'] }}</div>
      <div class="stat-trend">⏳ في انتظار النقر/الدفع</div>
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
      <div class="stat-label">لاعبون مقفول لعبهم</div>
      <div class="stat-value">{{ $stats['play_blocked'] }}</div>
      <div class="stat-trend">↑ يحتاجون فتح أو اشتراك</div>
    </div>
    <div class="stat-card grad-cool">
      <div class="stat-label">إجمالي الفئات</div>
      <div class="stat-value">{{ $stats['categories'] }}</div>
      <div class="stat-trend">↑ محتوى نشط</div>
    </div>
    <div class="stat-card grad-cool">
      <div class="stat-label">إجمالي الأسئلة</div>
      <div class="stat-value">{{ $stats['questions'] }}</div>
      <div class="stat-trend">↑ عبر كل المستويات</div>
    </div>
    <div class="stat-card grad-gold">
      <div class="stat-label">الباقات الفعالة</div>
      <div class="stat-value">{{ $stats['plans'] }}</div>
      <div class="stat-trend">↑ باقات جاهزة للبيع</div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h3>اختصارات الإدارة</h3>
      <span class="link-more">أهم المهام اليومية</span>
    </div>
    <div class="admin-quick-grid">
      <a class="quick-card" href="{{ route('admin.payments.index', ['status' => 'waiting_review']) }}" style="border-color: #f59e0b;">
        <span>🔍</span>
        <b>مراجعة المدفوعات</b>
        <small>تأكيد وتفعيل طلبات الدفع</small>
      </a>
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
</x-layouts.admin>
