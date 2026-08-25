<x-layouts.admin>
  <x-slot:heading>{{ $character->exists ? 'تعديل الشخصية' : 'شخصية جديدة' }}</x-slot:heading>
  <x-slot:subheading>{{ $character->exists ? 'تحديث صورة الشخصية وبياناتها' : 'إضافة شخصية جديدة يختارها اللاعبون' }}</x-slot:subheading>

  <x-back-button :href="route('admin.characters.index')" label="رجوع للشخصيات" />

  <form
    class="admin-form"
    method="POST"
    action="{{ $character->exists ? route('admin.characters.update', $character) : route('admin.characters.store') }}"
    data-async-upload
    data-upload-url="{{ route('admin.media.store') }}"
  >
    @csrf
    @if($character->exists) @method('PUT') @endif

    <label>الاسم العربي
      <input name="name_ar" value="{{ old('name_ar', $character->name_ar) }}" required>
    </label>
    <label>الاسم الإنجليزي
      <input name="name_en" value="{{ old('name_en', $character->name_en) }}">
    </label>
    <label>أيقونة احتياطية (إيموجي)
      <input name="icon" value="{{ old('icon', $character->icon) }}" placeholder="🧑">
      <small class="muted">تظهر لو ما رفعت صورة للشخصية.</small>
    </label>
    <label>لون الخلفية
      <input name="accent_color" value="{{ old('accent_color', $character->accent_color) }}" placeholder="#1E3A5F">
      <small class="muted">لون تدرّج خلف الإيموجي (مثل #FF1744).</small>
    </label>
    <label>ترتيب الظهور
      <input type="number" name="sort_order" min="1" value="{{ old('sort_order', $character->sort_order ?: 1) }}" required>
    </label>

    <label class="wide">
      صورة الشخصية
      <input
        type="file"
        accept="image/*"
        data-async-file
        data-upload-kind="image"
        data-path-input="character_image_path"
      >
      <span data-upload-status hidden></span>
      <input type="hidden" name="image_path" id="character_image_path"
             value="{{ old('image_path', $character->image) }}">
      @if($character->imageUrl())
        <div class="media-preview">
          <img src="{{ $character->imageUrl() }}" alt="صورة الشخصية">
          <label class="check"><input type="checkbox" name="remove_image" value="1"> حذف الصورة الحالية</label>
        </div>
      @endif
    </label>

    <label class="check">
      <input type="checkbox" name="is_active" @checked(old('is_active', $character->is_active ?? true))>
      مفعّل
    </label>

    @if($errors->any())
      <p class="error wide">{{ $errors->first() }}</p>
    @endif

    <button class="btn btn-primary" type="submit">حفظ الشخصية</button>
  </form>
</x-layouts.admin>
