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
    $maxQuestions = $maxQuestionsPerCategory ?? 18;
    $maxPerLevel = $maxPerLevel ?? 6;
    $pointsMap = ['easy' => 200, 'medium' => 400, 'hard' => 600];
  @endphp

  @unless($question->exists)
    <div class="alert alert-info" style="margin-bottom:16px;padding:12px 14px;border-radius:12px;background:rgba(255,109,0,.1);border:1px solid rgba(255,109,0,.25);font-weight:700">
      كل فئة: <b>6 سهل (200)</b> + <b>6 متوسط (400)</b> + <b>6 صعب (600)</b> = {{ $maxQuestions }} سؤال.
      لو مستوى اكتمل هيظهر تنبيه، ولو الفئة خلصت: أنشئ فئة جديدة.
    </div>
  @endunless

  <form class="admin-form" method="POST" action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}" enctype="multipart/form-data">
    @csrf
    @if($question->exists) @method('PUT') @endif

    <label>
      الفئة
      <select name="category_id" id="questionCategorySelect">
        @foreach($categories as $category)
          @php
            $count = (int) ($category->questions_count ?? 0);
            $easy = (int) ($category->easy_count ?? 0);
            $medium = (int) ($category->medium_count ?? 0);
            $hard = (int) ($category->hard_count ?? 0);
          @endphp
          <option
            value="{{ $category->id }}"
            data-count="{{ $count }}"
            data-easy="{{ $easy }}"
            data-medium="{{ $medium }}"
            data-hard="{{ $hard }}"
            data-full="{{ $count >= $maxQuestions ? '1' : '0' }}"
            @selected(old('category_id', $question->category_id ?: request('category_id')) == $category->id)
            @disabled(! $question->exists && $count >= $maxQuestions)
          >
            {{ $category->classificationName() }} — {{ $category->name_ar }}
            ({{ $count }}/{{ $maxQuestions }} · سهل {{ $easy }}/{{ $maxPerLevel }} · متوسط {{ $medium }}/{{ $maxPerLevel }} · صعب {{ $hard }}/{{ $maxPerLevel }})
            @if($count >= $maxQuestions) — مكتملة @endif
          </option>
        @endforeach
      </select>
    </label>
    @error('category_id')<small class="error">{{ $message }}</small>@enderror

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
      <select name="level" id="questionLevelSelect">
        @foreach(['easy' => 'سهل (200 نقطة)', 'medium' => 'متوسط (400 نقطة)', 'hard' => 'صعب (600 نقطة)'] as $key => $label)
          <option value="{{ $key }}" @selected(old('level', $question->level?->value) === $key)>{{ $label }}</option>
        @endforeach
      </select>
      <small id="levelCapacityHint" style="display:block;margin-top:6px;font-weight:700;color:var(--muted,#6C7799)"></small>
    </label>
    @error('level')<small class="error">{{ $message }}</small>@enderror

    <label>
      النقاط (تلقائي حسب المستوى)
      <input type="text" id="pointsPreview" value="{{ old('points', $question->points ?: 200) }}" readonly>
      <input type="hidden" name="points" id="pointsHidden" value="{{ old('points', $question->points ?: 200) }}">
    </label>

    <label>
      المؤقت (ثانية)
      <input type="number" name="time_limit" value="{{ old('time_limit', $question->time_limit ?? 60) }}" min="10" max="300">
    </label>

    <label class="wide">
      نص السؤال
      <textarea name="question_text" required>{{ old('question_text', $question->question_text) }}</textarea>
    </label>

    <div class="wide question-section" data-question-section data-types="image_guess,puzzle,complete,video,audio" hidden>
      <label class="wide">
        نص الإجابة
        <textarea name="answer_text" placeholder="مثال: نص الحديث كاملاً…">{{ old('answer_text', $question->answer_text) }}</textarea>
      </label>
      <small class="muted">هذا الحقل يظهر مع: خمن الصورة، لغز، أكمل الناقص، فيديو، صوتي.</small>
    </div>

    <div class="wide question-section" data-question-section data-types="video" hidden>
      <label class="wide">
        فيديو السؤال
        <input type="file" name="image" accept="video/mp4,video/webm,video/quicktime">
        @if($question->exists && $question->isVideo() && $question->mediaUrl())
          <div class="media-preview">
            <video src="{{ $question->mediaUrl() }}" controls style="max-width:100%;border-radius:12px;max-height:240px"></video>
            <label class="check"><input type="checkbox" name="remove_image" value="1"> حذف الفيديو الحالي</label>
          </div>
        @endif
        <small class="muted">يُسمح بالتشغيل مرة واحدة فقط أثناء اللعب. الصيغ: mp4 / webm</small>
      </label>
    </div>

    <div class="wide question-section" data-question-section data-types="audio" hidden>
      <label class="wide">
        الملف الصوتي
        <input type="file" name="image" accept="audio/mpeg,audio/wav,audio/ogg,audio/mp4,.mp3,.wav">
        @if($question->exists && $question->isAudio() && $question->mediaUrl())
          <div class="media-preview">
            <audio src="{{ $question->mediaUrl() }}" controls style="width:100%"></audio>
            <label class="check"><input type="checkbox" name="remove_image" value="1"> حذف الصوت الحالي</label>
          </div>
        @endif
        <small class="muted">الصيغ المدعومة: mp3 / wav / ogg</small>
      </label>
    </div>

    <div class="wide question-section" data-question-section data-types="standard,image_guess,puzzle,complete,order,match" hidden>
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
    </div>

    <div class="wide question-section" data-question-section data-types="standard,image_guess,puzzle,complete,order,match" hidden>
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
    </div>

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
            const show = allowed.includes(type);
            section.hidden = !show;
            section.querySelectorAll('input, textarea, select').forEach((el) => {
              if (el.type === 'file' || el.name === 'image' || el.name === 'answer_image' || el.name === 'answer_text' || el.name?.startsWith('options') || el.name?.startsWith('order_items') || el.name?.startsWith('match_pairs') || el.name === 'correct_option' || el.name === 'remove_image' || el.name === 'remove_answer_image') {
                el.disabled = !show;
              }
            });
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

      (() => {
        const categorySelect = document.getElementById('questionCategorySelect');
        const levelSelect = document.getElementById('questionLevelSelect');
        const pointsPreview = document.getElementById('pointsPreview');
        const pointsHidden = document.getElementById('pointsHidden');
        const hint = document.getElementById('levelCapacityHint');
        const maxPerLevel = {{ (int) ($maxPerLevel ?? 6) }};
        const pointsMap = { easy: 200, medium: 400, hard: 600 };
        const labels = { easy: 'السهل', medium: 'المتوسط', hard: 'الصعب' };
        const isEdit = {{ $question->exists ? 'true' : 'false' }};

        const sync = () => {
          const level = levelSelect?.value || 'easy';
          const points = pointsMap[level] || 200;
          if (pointsPreview) pointsPreview.value = points;
          if (pointsHidden) pointsHidden.value = points;

          const opt = categorySelect?.selectedOptions?.[0];
          if (!opt || !hint) return;

          const count = Number(opt.dataset[level] || 0);
          const total = Number(opt.dataset.count || 0);
          if (total >= {{ (int) ($maxQuestions ?? 18) }} && !isEdit) {
            hint.textContent = 'الفئة مكتملة وكل المستويات مكتملة. قم بإنشاء فئة جديدة.';
            hint.style.color = '#C8102E';
            return;
          }

          hint.textContent = `أسئلة ${labels[level] || level}: ${count}/${maxPerLevel} — النقاط ${points} تلقائيًا`;
          if (!isEdit && count >= maxPerLevel) {
            hint.textContent = `أسئلة المستوى ${labels[level]} مكتملة (${maxPerLevel}/${maxPerLevel}). أكمل باقي المستويات.`;
            hint.style.color = '#C8102E';
          } else {
            hint.style.color = '';
          }
        };

        categorySelect?.addEventListener('change', sync);
        levelSelect?.addEventListener('change', sync);
        sync();
      })();
    </script>

    @if($errors->any())
      <p class="error wide">{{ $errors->first() }}</p>
    @endif

    <button class="btn btn-primary" type="submit">حفظ السؤال</button>
  </form>
</x-layouts.admin>
