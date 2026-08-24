<x-layouts.admin>
  <x-slot:heading>الباقات والاشتراكات</x-slot:heading>
  <x-slot:subheading>إدارة باقات الاشتراك وسعرها ومدة صلاحيتها</x-slot:subheading>

  <div class="toolbar toolbar--tight">
    <a class="btn btn-primary" href="{{ route('admin.plans.create') }}">+ إضافة باقة</a>
  </div>

  <div class="plans-admin-grid">
    @forelse($plans as $plan)
      <article class="plan-admin-card">
        <span class="status-dot {{ $plan->is_active ? '' : 'off' }}"></span>
        <h3>{{ $plan->name }}</h3>
        <div class="price">{{ number_format($plan->price) }} درهم</div>
        <p class="meta">
          {{ $plan->duration_days }} يوم
          · {{ $plan->is_active ? 'مفعّلة' : 'موقوفة' }}
          @if($plan->is_recommended) · مميزة @endif
        </p>
        @if(!empty($plan->features))
          <ul class="features">
            @foreach($plan->features as $feature)
              <li>✓ {{ $feature }}</li>
            @endforeach
          </ul>
        @endif
        <a class="btn" href="{{ route('admin.plans.edit', $plan) }}">تعديل الباقة</a>
      </article>
    @empty
      <p class="muted">لا توجد باقات بعد.</p>
    @endforelse
  </div>
</x-layouts.admin>
