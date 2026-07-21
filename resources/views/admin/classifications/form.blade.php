<x-layouts.admin>
  <x-slot:heading>{{ $classification->exists ? 'تعديل التصنيف' : 'تصنيف جديد' }}</x-slot:heading>
  <x-slot:subheading>{{ $classification->exists ? 'تحديث بيانات التصنيف وصورته' : 'إضافة تصنيف جديد تختاره لاحقًا داخل الفئات' }}</x-slot:subheading>

  <x-back-button :href="route('admin.classifications.index')" label="رجوع للتصنيفات" />

  <form class="admin-form" method="POST" action="{{ $classification->exists ? route('admin.classifications.update', $classification) : route('admin.classifications.store') }}" enctype="multipart/form-data">
    @csrf
    @if($classification->exists) @method('PUT') @endif

    <label>الاسم العربي
      <input name="name_ar" value="{{ old('name_ar', $classification->name_ar) }}" required>
    </label>
    <label>الاسم الإنجليزي
      <input name="name_en" value="{{ old('name_en', $classification->name_en) }}">
    </label>
    <label>الأيقونة (إيموجي)
      <input name="icon" value="{{ old('icon', $classification->icon) }}">
    </label>
    <label>ترتيب الظهور
      <input type="number" name="sort_order" min="1" value="{{ old('sort_order', $classification->sort_order ?: 1) }}" required>
    </label>
    <label class="wide">الوصف
      <textarea name="description">{{ old('description', $classification->description) }}</textarea>
    </label>

    <label class="wide">
      صورة التصنيف
      <input type="file" name="image" accept="image/*">
      @if($classification->imageUrl())
        <div class="media-preview">
          <img src="{{ $classification->imageUrl() }}" alt="صورة التصنيف">
          <label class="check"><input type="checkbox" name="remove_image" value="1"> حذف الصورة الحالية</label>
        </div>
      @endif
    </label>

    <label class="check">
      <input type="checkbox" name="is_active" @checked(old('is_active', $classification->is_active ?? true))>
      مفعّل
    </label>

    @if($errors->any())
      <p class="error wide">{{ $errors->first() }}</p>
    @endif

    <button class="btn btn-primary" type="submit">حفظ التصنيف</button>
  </form>
</x-layouts.admin>
