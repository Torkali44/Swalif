<x-layouts.admin>
  <x-slot:heading>{{ $question->exists ? 'تعديل السؤال' : 'سؤال جديد' }}</x-slot:heading>
  <x-slot:subheading>{{ $question->exists ? 'تحديث بيانات السؤال ونوعه وصوره' : 'إضافة سؤال جديد مع نوعه وتصنيفه' }}</x-slot:subheading>

  <x-back-button :href="route('admin.questions.index')" label="رجوع للأسئلة" />

  @php
    $selectedType = old('type', $question->type ?? 'standard');
    $selectedCorrectOption = old('correct_option', $question->options->search(fn ($option) => $option->is_correct));
    $storedOptions = old('options', $question->exists ? $question->options->pluck('option_text')->all() : []);
    $optionValues = collect($storedOptions)->pad(4, '')->take(4)->values();
    $storedOrderItems = old('order_items', $question->orderItems());
    $orderItems = collect($storedOrderItems)->whenEmpty(fn () => collect(['', '', '', '']))->pad(4, '')->values();
    $storedMatchPairs = old('match_pairs', $question->matchPairs());
    $matchPairs = collect($storedMatchPairs);
    if ($matchPairs->isEmpty()) {
      $matchPairs = collect(array_fill(0, 4, ['left' => '', 'right' => '']));
    } else {
      $matchPairs = $matchPairs->pad(4, ['left' => '', 'right' => ''])->values();
    }
  @endphp

  <form class="admin-form" method="POST" action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}" enctype="multipart/form-data">
    @csrf
    @if($question->exists) @method('PUT') @endif

    <label>
      الفئة
      <select name="category_id">
        @foreach($categories as $category)
          <option value="{{ $category->id }}" @selected(old('category_id', $question->category_id ?: request('category_id')) == $category->id)>
            {{ $category->classificationName() }} — {{ $category->name_ar }}
          </option>
        @endforeach
      </select>
    </label>

    <label>
      نوع السؤال
      <select name="type" required>
        @foreach($questionTypes as $questionType)
          <option value="{{ $questionType['value'] }}" @selected($selectedType === $questionType['value'])>{{ $questionType['label'] }}</option>
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
      <input type="number" name="time_limit" value="{{ old('time_limit', $question->time_limit ?? 60) }}" min="10" max="300">
    </label>

    <label class="wide">
      نص السؤال
      <textarea name="question_text" required>{{ old('question_text', $question->question_text) }}</textarea>
    </label>

    <div class="wide question-section" data-question-section data-types="image_guess,puzzle,complete" hidden>
      <label class="wide">
        نص الإجابة
        <textarea name="answer_text" placeholder="مثال: نص الحديث كاملاً…">{{ old('answer_text', $question->answer_text) }}</textarea>
      </label>
      <small class="muted">هذا الحقل يظهر فقط مع: خمن الصورة، لغز، أكمل الناقص.</small>
    </div>

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

    <div class="wide question-section" data-question-section data-types="order" hidden>
      <fieldset class="wide">
        <legend>عناصر الترتيب</legend>
        <div class="repeat-stack" id="orderItems">
          @foreach($orderItems as $index => $value)
            <label class="option">
              <span class="option__badge">{{ $index + 1 }}</span>
              <input name="order_items[]" value="{{ $value }}" placeholder="العنصر {{ $index + 1 }}">
            </label>
          @endforeach
        </div>
        <button class="btn btn-outline btn-sm" type="button" id="addOrderItem">+ إضافة عنصر</button>
        <small class="muted">اكتب العناصر بالترتيب الصحيح الذي تريد ظهوره للمستخدم.</small>
      </fieldset>
    </div>

    <div class="wide question-section" data-question-section data-types="match" hidden>
      <fieldset class="wide">
        <legend>أزواج التوصيل</legend>
        <div class="repeat-stack" id="matchPairs">
          @foreach($matchPairs as $index => $pair)
            <div class="match-row" data-match-row>
              <input type="hidden" data-match-index value="{{ $index }}">
              <input name="match_pairs[{{ $index }}][left]" value="{{ $pair['left'] ?? '' }}" placeholder="الطرف الأول">
              <span class="match-row__sep">↔</span>
              <input name="match_pairs[{{ $index }}][right]" value="{{ $pair['right'] ?? '' }}" placeholder="الطرف الثاني">
            </div>
          @endforeach
        </div>
        <button class="btn btn-outline btn-sm" type="button" id="addMatchPair">+ إضافة زوج</button>
        <small class="muted">كل زوج لازم يكون فيه طرفين مكتملين، زي كلمة ومعناها أو صورة ووصفها.</small>
      </fieldset>
    </div>

    <div class="wide question-section" data-question-section data-types="standard" hidden>
      <fieldset class="wide">
        <legend>الاختيارات</legend>
        @for($i = 0; $i < 4; $i++)
          <label class="option">
            <input type="radio" name="correct_option" value="{{ $i }}" @checked((string) $selectedCorrectOption === (string) $i)>
            <input name="options[]" value="{{ $optionValues[$i] ?? '' }}" placeholder="اختيار {{ $i + 1 }}">
          </label>
        @endfor
        <small class="muted">لازم تضيف خيارين على الأقل، وتحدد الاختيار الصحيح.</small>
      </fieldset>
    </div>

    <label class="check">
      <input type="checkbox" name="is_active" @checked(old('is_active', $question->is_active ?? true))>
      مفعّل
    </label>

    <script>
      (function () {
        const typeSelect = document.querySelector('select[name="type"]');
        const sections = Array.from(document.querySelectorAll('[data-question-section]'));
        const orderContainer = document.getElementById('orderItems');
        const matchContainer = document.getElementById('matchPairs');
        const addOrderBtn = document.getElementById('addOrderItem');
        const addMatchBtn = document.getElementById('addMatchPair');

        const escapeHtml = (value) => String(value ?? '')
          .replaceAll('&', '&amp;')
          .replaceAll('<', '&lt;')
          .replaceAll('>', '&gt;')
          .replaceAll('"', '&quot;')
          .replaceAll("'", '&#39;');

        const updateSections = () => {
          const type = typeSelect?.value || @json($selectedType);
          sections.forEach((section) => {
            const allowed = (section.dataset.types || '')
              .split(',')
              .map((item) => item.trim())
              .filter(Boolean);
            section.hidden = !allowed.includes(type);
          });
        };

        const addOrderRow = (value = '') => {
          if (!orderContainer) return;
          const index = orderContainer.querySelectorAll('input[name="order_items[]"]').length + 1;
          const row = document.createElement('label');
          row.className = 'option';
          row.innerHTML = `
            <span class="option__badge">${index}</span>
            <input name="order_items[]" value="${escapeHtml(value)}" placeholder="العنصر ${index}">
          `;
          orderContainer.appendChild(row);
        };

        const addMatchRow = (left = '', right = '') => {
          if (!matchContainer) return;
          const index = matchContainer.querySelectorAll('[data-match-row]').length;
          const row = document.createElement('div');
          row.className = 'match-row';
          row.dataset.matchRow = '1';
          row.innerHTML = `
            <input type="hidden" data-match-index value="${index}">
            <input name="match_pairs[${index}][left]" value="${escapeHtml(left)}" placeholder="الطرف الأول">
            <span class="match-row__sep">↔</span>
            <input name="match_pairs[${index}][right]" value="${escapeHtml(right)}" placeholder="الطرف الثاني">
          `;
          matchContainer.appendChild(row);
        };

        typeSelect?.addEventListener('change', updateSections);
        addOrderBtn?.addEventListener('click', () => addOrderRow());
        addMatchBtn?.addEventListener('click', () => addMatchRow());

        updateSections();
      })();
    </script>

    @if($errors->any())
      <p class="error wide">{{ $errors->first() }}</p>
    @endif

    <button class="btn btn-primary" type="submit">حفظ السؤال</button>
  </form>
</x-layouts.admin>
