<x-layouts.admin>
  <x-slot:heading>{{ $plan->exists ? 'تعديل الباقة: '.$plan->name : 'إضافة باقة جديدة' }}</x-slot:heading>
  <x-slot:subheading>تحديث معلومات باقة الاشتراك والأسعار والمميزات</x-slot:subheading>

  <x-back-button :href="route('admin.plans.index')" label="رجوع للباقات" />

  <form class="admin-form" method="POST" action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}">
    @csrf
    @if($plan->exists)
      @method('PUT')
    @endif

    <label>
      اسم الباقة
      <input name="name" value="{{ old('name', $plan->name) }}" required>
    </label>

    <label>
      نوع الباقة
      <input name="type" value="{{ old('type', $plan->type) }}" placeholder="monthly" required>
    </label>

    <label class="wide">
      رابط دفع Stripe (Payment Link)
      <input type="url" name="stripe_checkout_url" value="{{ old('stripe_checkout_url', $plan->stripe_checkout_url) }}" placeholder="https://buy.stripe.com/...">
      <small class="muted" style="display:block;margin-top:6px">يُستخدم تلقائيًا في زر الشراء بصفحة الاشتراكات.</small>
    </label>

    <label>
      الأيقونة
      <input name="icon" value="{{ old('icon', $plan->icon) }}" placeholder="💎">
    </label>

    <label>
      السعر (بالدرهم)
      <input type="number" step="0.01" min="1" name="price" value="{{ old('price', $plan->price) }}" required>
    </label>

    <label>
      السعر قبل الخصم
      <input type="number" step="0.01" min="1" name="old_price" value="{{ old('old_price', $plan->old_price) }}">
    </label>

    <label>
      العملة
      <input name="currency" value="{{ old('currency', $plan->currency ?? 'AED') }}" maxlength="3" required>
    </label>

    <label class="wide">
      المدة بالأيام
      <input type="number" min="1" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required>
    </label>

    <label>
      ترتيب الظهور
      <input type="number" min="1" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 1) }}" required>
    </label>

    @php
      $featureValues = old('features', $plan->features ?: ['']);
      $featureValues = is_array($featureValues) ? array_values($featureValues) : [$featureValues];
      $featureValues = count($featureValues) ? $featureValues : [''];
    @endphp

    <div class="wide form-group">
      المميزات
      <div class="feature-inputs" data-feature-list>
        @foreach($featureValues as $feature)
          <div class="feature-input-row">
            <input name="features[]" value="{{ $feature }}" placeholder="ميزة الاشتراك">
            <button class="btn btn-sm btn-outline" type="button" data-remove-feature>حذف</button>
          </div>
        @endforeach
      </div>
      <button class="btn btn-sm btn-outline" type="button" data-add-feature>+ إضافة ميزة</button>
    </div>

    <div class="wide" style="display:flex;gap:20px;margin:12px 0;flex-wrap:wrap">
      <label class="check">
        <input type="checkbox" name="is_active" @checked(old('is_active', $plan->is_active))>
        مفعّلة
      </label>

      <label class="check">
        <input type="checkbox" name="is_recommended" @checked(old('is_recommended', $plan->is_recommended))>
        موصى بها (باقة مميزة)
      </label>
    </div>

    @if($errors->any())
      <p class="error wide">{{ $errors->first() }}</p>
    @endif

    <button class="btn btn-primary" type="submit">حفظ الباقة</button>
  </form>

  <script>
    document.addEventListener('click', function (event) {
      const addButton = event.target.closest('[data-add-feature]');
      const removeButton = event.target.closest('[data-remove-feature]');

      if (addButton) {
        const list = document.querySelector('[data-feature-list]');
        const row = document.createElement('div');
        row.className = 'feature-input-row';
        row.innerHTML = '<input name="features[]" placeholder="ميزة الاشتراك"><button class="btn btn-sm btn-outline" type="button" data-remove-feature>حذف</button>';
        list.appendChild(row);
      }

      if (removeButton) {
        const list = removeButton.closest('[data-feature-list]');
        const row = removeButton.closest('.feature-input-row');

        if (list.children.length === 1) {
          row.querySelector('input').value = '';
          return;
        }

        row.remove();
      }
    });
  </script>
</x-layouts.admin>
