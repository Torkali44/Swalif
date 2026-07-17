<x-layouts.admin>
  <x-slot:heading>الباقات والاشتراكات</x-slot:heading>
  <x-slot:subheading>إدارة باقات الاشتراك وسعرها ومدة صلاحيتها</x-slot:subheading>

  <div class="admin-cards">
    @foreach($plans as $plan)
      <article style="position: relative">
        <span class="status-dot {{ $plan->is_active ? '' : 'off' }}"></span>
        <h3>{{ $plan->name }}</h3>
        <strong style="display:block;font-size:24px;color:var(--primary);margin:8px 0">{{ $plan->price }} درهم</strong>
        <p>{{ $plan->duration_days }} يوم · {{ $plan->is_active ? 'مفعّلة' : 'موقوفة' }}</p>
        
        <div style="margin-top:16px">
          <a class="btn btn-outline" style="font-size:13px;padding:8px 16px;color:var(--text);border-color:var(--border)" href="{{ route('admin.plans.edit', $plan) }}">تعديل الباقة</a>
        </div>
      </article>
    @endforeach
  </div>
</x-layouts.admin>
