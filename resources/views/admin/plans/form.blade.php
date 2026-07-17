<x-layouts.admin>
  <x-slot:heading>تعديل الباقة: {{ $plan->name }}</x-slot:heading>
  <x-slot:subheading>تحديث معلومات باقة الاشتراك والأسعار</x-slot:subheading>

  <x-back-button :href="route('admin.plans.index')" label="رجوع للباقات" />

  <form class="admin-form" method="POST" action="{{ route('admin.plans.update', $plan) }}">
    @csrf
    @method('PUT')

    <label>
      اسم الباقة
      <input name="name" value="{{ old('name', $plan->name) }}" required>
    </label>

    <label>
      السعر (بالدرهم)
      <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" required>
    </label>

    <label class="wide">
      المدة بالأيام
      <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required>
    </label>

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
</x-layouts.admin>
