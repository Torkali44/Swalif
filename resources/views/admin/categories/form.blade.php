<x-layouts.admin>
  <x-slot:heading>{{ $category->exists ? 'تعديل الفئة' : 'فئة جديدة' }}</x-slot:heading>

  <form class="admin-form" method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
    @csrf
    @if($category->exists) @method('PUT') @endif

    <label>الاسم العربي
      <input name="name_ar" value="{{ old('name_ar', $category->name_ar) }}" required>
    </label>
    <label>الاسم الإنجليزي
      <input name="name_en" value="{{ old('name_en', $category->name_en) }}">
    </label>
    <label>التصنيف
      <select name="group">
        <option value="uae" @selected(old('group', $category->group) === 'uae')>إمارات</option>
        <option value="general" @selected(old('group', $category->group) === 'general')>عام</option>
      </select>
    </label>
    <label>الأيقونة (إيموجي)
      <input name="icon" value="{{ old('icon', $category->icon) }}">
    </label>
    <label>ترتيب الظهور
      <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
    </label>
    <label class="wide">الوصف
      <textarea name="description">{{ old('description', $category->description) }}</textarea>
    </label>

    <label class="wide">
      صورة الفئة (تظهر في اللوحة وبطاقة الفئة)
      <input type="file" name="image" accept="image/*">
      @if($category->imageUrl())
        <div class="media-preview">
          <img src="{{ $category->imageUrl() }}" alt="صورة الفئة">
          <label class="check"><input type="checkbox" name="remove_image" value="1"> حذف الصورة الحالية</label>
        </div>
      @endif
    </label>

    <label class="check">
      <input type="checkbox" name="is_active" @checked(old('is_active', $category->is_active ?? true))>
      مفعّلة
    </label>

    @if($errors->any())
      <p class="error wide">{{ $errors->first() }}</p>
    @endif

    <button class="btn btn-primary" type="submit">حفظ الفئة</button>
  </form>
</x-layouts.admin>
