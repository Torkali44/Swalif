<x-layouts.admin>
  <x-slot:heading>{{ $question->exists ? 'تعديل السؤال' : 'سؤال جديد' }}</x-slot:heading>
  <x-slot:subheading>{{ $question->exists ? 'تحديث نص السؤال والخيارات' : 'إضافة سؤال جديد' }}</x-slot:subheading>

  <x-back-button :href="route('admin.questions.index')" label="رجوع للأسئلة" />

  <form class="admin-form" method="POST" action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}" enctype="multipart/form-data">
    @csrf
    @if($question->exists) @method('PUT') @endif

    <label>
      الفئة
      <select name="category_id">
        @foreach($categories as $category)
          <option value="{{ $category->id }}" @selected(old('category_id', $question->category_id ?: request('category_id')) == $category->id)>{{ $category->name_ar }}</option>
        @endforeach
      </select>
    </label>

    <label>
      المستوى
      <select name="level">
        @foreach(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب'] as $key => $label)
          <option value="{{ $key }}" @selected(old('level', $question->level?->value) === $key)>{{ $label }}</option>
        @endforeach
      </select>
    </label>

    <label>
      النقاط
      <select name="points">
        @foreach([200, 400, 600] as $points)
          <option value="{{ $points }}" @selected((int) old('points', $question->points) === $points)>{{ $points }}</option>
        @endforeach
      </select>
    </label>

    <label>
      المؤقت (ثانية)
      <input type="number" name="time_limit" value="{{ old('time_limit', $question->time_limit ?? 60) }}">
    </label>

    <label class="wide">
      نص السؤال
      <textarea name="question_text" required>{{ old('question_text', $question->question_text) }}</textarea>
    </label>

    <label class="wide">
      نص الإجابة (للأسئلة بدون اختيارات)
      <textarea name="answer_text" placeholder="مثال: نص الحديث كاملاً…">{{ old('answer_text', $question->answer_text) }}</textarea>
    </label>

    <label class="wide">
      صورة السؤال (اختياري)
      <input type="file" name="image" accept="image/*">
      @if($question->imageUrl())
        <div class="media-preview">
          <img src="{{ $question->imageUrl() }}" alt="صورة السؤال">
          <label class="check"><input type="checkbox" name="remove_image" value="1"> حذف الصورة الحالية</label>
        </div>
      @endif
    </label>

    <label class="wide">
      صورة الإجابة (اختياري)
      <input type="file" name="answer_image" accept="image/*">
      @if($question->answerImageUrl())
        <div class="media-preview">
          <img src="{{ $question->answerImageUrl() }}" alt="صورة الإجابة">
          <label class="check"><input type="checkbox" name="remove_answer_image" value="1"> حذف صورة الإجابة</label>
        </div>
      @endif
    </label>

    <fieldset class="wide">
      <legend>الاختيارات (اختياري — اتركها فارغة لو السؤال مفتوح)</legend>
      @for($i = 0; $i < 4; $i++)
        <label class="option">
          <input type="radio" name="correct_option" value="{{ $i }}" @checked((int) old('correct_option', optional($question->options)->search(fn ($option) => $option->is_correct) ?? -1) === $i)>
          <input name="options[]" value="{{ old('options.'.$i, $question->options[$i]->option_text ?? '') }}" placeholder="اختيار {{ $i + 1 }}">
        </label>
      @endfor
      <small class="muted">لو هتستخدم اختيارات، حدّد الصحيحة بزر الراديو. لو مش محتاج اختيارات، اكتب نص الإجابة فوق.</small>
    </fieldset>

    <label class="check">
      <input type="checkbox" name="is_active" @checked(old('is_active', $question->is_active ?? true))>
      مفعّل
    </label>

    @if($errors->any())
      <p class="error wide">{{ $errors->first() }}</p>
    @endif

    <button class="btn btn-primary" type="submit">حفظ السؤال</button>
  </form>
</x-layouts.admin>
