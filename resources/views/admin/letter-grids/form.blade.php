<x-layouts.admin>
  <x-slot:heading>{{ $grid->exists ? 'تعديل شبكة الحروف' : 'شبكة حروف جديدة' }}</x-slot:heading>
  <x-slot:subheading>أضف أو عدّل حروف الشبكة — كل حرف له سؤال وإجابة تبدأ بنفس الحرف</x-slot:subheading>

  <x-back-button :href="route('admin.letter-grids.index')" label="رجوع للشبكات" />

  @php
    $storedCells = old('cells', $cells);
  @endphp

  <form class="admin-form admin-letter-grid-form" method="POST" id="letterGridForm"
    action="{{ $grid->exists ? route('admin.letter-grids.update', $grid) : route('admin.letter-grids.store') }}"
    data-async-upload
    data-upload-url="{{ route('admin.media.store') }}">
    @csrf
    @if($grid->exists) @method('PUT') @endif

    {{-- Basic Settings --}}
    <label>
      <span>اسم الشبكة <b style="color:#FF1744">*</b></span>
      <input type="text" name="name_ar" value="{{ old('name_ar', $grid->name_ar) }}" required maxlength="120"
        placeholder="مثال: حروف عامة، حروف إسلامية...">
      @error('name_ar')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label>
      <span>الاسم اللطيف (Slug)</span>
      <input type="text" name="slug" value="{{ old('slug', $grid->slug) }}" maxlength="140"
        placeholder="اتركه فارغاً للتوليد التلقائي">
      @error('slug')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="wide">
      <span>الوصف (اختياري)</span>
      <textarea name="description" rows="2"
        placeholder="نبذة تعريفية بمحتوى هذه الشبكة وموضوع أسئلتها">{{ old('description', $grid->description) }}</textarea>
      @error('description')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="wide">
      صورة الشبكة (تظهر في الإنشاء واللوحة مثل الفئات)
      <input
        type="file"
        accept="image/*"
        data-async-file
        data-upload-kind="image"
        data-upload-folder="letter_grids"
        data-path-input="letter_grid_image_path"
      >
      <span data-upload-status hidden></span>
      <input type="hidden" name="image_path" id="letter_grid_image_path"
             value="{{ old('image_path', $grid->image) }}">
      @if($grid->imageUrl())
        <div class="media-preview" style="margin-top:10px">
          <img src="{{ $grid->imageUrl() }}" alt="صورة الشبكة" style="max-width:180px;border-radius:14px">
          <label class="check"><input type="checkbox" name="remove_image" value="1"> حذف الصورة الحالية</label>
        </div>
      @endif
    </label>

    <label class="check">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $grid->is_active ?? true))>
      <span>مفعّلة وظاهرة للاعبين في الموقع</span>
    </label>

    <label>
      <span>ترتيب العرض</span>
      <input type="number" name="sort_order" value="{{ old('sort_order', $grid->sort_order ?? 0) }}" min="0" max="9999"
        placeholder="0">
    </label>

    {{-- Letters & Questions Section --}}
    <div class="wide admin-cells-container">
      <div class="admin-cells-header">
        <div>
          <h3 class="admin-cells-title">حروف وأسئلة الشبكة</h3>
          <p class="admin-cells-subtitle">
            قاعدة اللعبة: إجابة كل حرف <b>يجب أن تبدأ بنفس الحرف</b> (مثال: حرف «ض» ⬅ السؤال: «وصف للشيء الضخم» ⬅
            الإجابة: «ضخم»).
          </p>
        </div>

        <div class="admin-cells-stats">
          <span class="cells-counter-badge">عدد الحروف: <b id="cellsTotalCount">{{ count($storedCells) }}</b></span>
        </div>
      </div>

      {{-- Action Toolbar & Search --}}
      <div class="admin-cells-toolbar">
        <div class="admin-cells-search">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7" />
            <path d="m21 21-4.3-4.3" />
          </svg>
          <input type="search" id="cellSearchInput" placeholder="تصفية الحروف… (اكتب حرفاً للوصول إليه بسرعة)"
            autocomplete="off">
        </div>

        <div class="admin-cells-actions-group">
          <button type="button" class="btn btn-sm btn-outline" id="addLetterGridCell">
            <span>+</span> إضافة حرف يدوي
          </button>
          <button type="button" class="btn btn-sm btn-primary" id="fillArabicAlphabet">
            <span>✨</span> تعبئة الحروف العربية الـ 28
          </button>
        </div>
      </div>

      @error('cells')
        <div class="alert alert-danger"
          style="margin: 12px 0; padding: 12px 16px; border-radius: 12px; background: rgba(255,23,68,0.1); border: 1.5px solid rgba(255,23,68,0.3); color: #FF1744; font-weight: 800;">
          {{ $message }}
        </div>
      @enderror

      {{-- Cells Stack --}}
      <div class="admin-cells-list" id="letterGridCells">
        @foreach($storedCells as $index => $cell)
          <div class="admin-cell-card" data-cell-row data-index="{{ $index }}" data-letter="{{ $cell['letter'] ?? '' }}">
            <input type="hidden" name="cells[{{ $index }}][row]" value="{{ $cell['row'] ?? 0 }}" data-cell-row-input>
            <input type="hidden" name="cells[{{ $index }}][col]" value="{{ $cell['col'] ?? 0 }}" data-cell-col-input>
            <input type="hidden" name="cells[{{ $index }}][is_active]" value="1">

            <div class="admin-cell-card__badge-wrap">
              <span class="admin-cell-num" data-cell-num>{{ $index + 1 }}</span>
              <div class="admin-cell-hex-icon">⬡</div>
            </div>

            <div class="admin-cell-card__fields">
              {{-- Letter Input --}}
              <div class="admin-cell-field admin-cell-field--letter">
                <span class="admin-cell-label">الحرف</span>
                <input type="text" name="cells[{{ $index }}][letter]" class="admin-cell-input admin-cell-input--letter"
                  value="{{ $cell['letter'] ?? '' }}" maxlength="3" dir="rtl" required placeholder="أ" data-letter-input>
              </div>

              {{-- Question Input --}}
              <div class="admin-cell-field admin-cell-field--question">
                <span class="admin-cell-label">نص السؤال</span>
                <input type="text" name="cells[{{ $index }}][question_text]" class="admin-cell-input"
                  value="{{ $cell['question_text'] ?? '' }}" required dir="rtl" placeholder="اكتب نص السؤال هنا…">
              </div>

              {{-- Answer Input --}}
              <div class="admin-cell-field admin-cell-field--answer">
                <span class="admin-cell-label">الإجابة (تبدأ بالحرف)</span>
                <input type="text" name="cells[{{ $index }}][answer_text]" class="admin-cell-input"
                  value="{{ $cell['answer_text'] ?? '' }}" required dir="rtl" placeholder="الإجابة الصحيحة">
              </div>

              {{-- Delete Button --}}
              <div class="admin-cell-card__actions">
                <button type="button" class="admin-cell-remove-btn" data-remove-cell title="حذف هذا الحرف">
                  ✕
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    @if($errors->any())
      <div class="wide alert alert-danger"
        style="margin-top: 10px; padding: 14px 18px; border-radius: 14px; background: rgba(255,23,68,0.1); border: 1.5px solid rgba(255,23,68,0.3); color: #FF1744; font-weight: 800;">
        {{ $errors->first() }}
      </div>
    @endif

    <div class="wide admin-form-footer">
      <button class="btn btn-primary btn-lg" type="submit">
        <span>💾</span> حفظ شبكة الحروف
      </button>
      <a href="{{ route('admin.letter-grids.index') }}" class="btn btn-ghost">إلغاء</a>
    </div>
  </form>

  <style>
    /* ══════════════════════════════════════════════════════════
     Admin Letter Grid Form Custom Styles
  ══════════════════════════════════════════════════════════ */
    .admin-letter-grid-form {
      margin-top: 18px;
    }

    .admin-cells-container {
      background: var(--surface, #fff);
      border: 1.5px solid var(--border, #E8E4DC);
      border-radius: 20px;
      padding: 24px 20px;
      margin-top: 14px;
      box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
    }

    body.dark.admin-body .admin-cells-container {
      background: #151B32;
      border-color: rgba(255, 255, 255, 0.1);
    }

    .admin-cells-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border, #E8E4DC);
      flex-wrap: wrap;
    }

    body.dark.admin-body .admin-cells-header {
      border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    .admin-cells-title {
      font-family: var(--font-display, 'Cairo');
      font-size: 1.25rem;
      font-weight: 900;
      margin: 0 0 4px;
      color: var(--ink, #0B1220);
    }

    body.dark.admin-body .admin-cells-title {
      color: #F8FAFC;
    }

    .admin-cells-subtitle {
      font-size: 0.88rem;
      color: var(--muted, #6C7799);
      margin: 0;
      line-height: 1.5;
    }

    .cells-counter-badge {
      padding: 6px 16px;
      border-radius: 999px;
      background: rgba(255, 109, 0, 0.12);
      color: #FF6D00;
      font-weight: 800;
      font-size: 0.9rem;
    }

    /* Toolbar */
    .admin-cells-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 14px;
      margin: 18px 0;
      flex-wrap: wrap;
    }

    .admin-cells-search {
      position: relative;
      flex: 1;
      min-width: 260px;
      max-width: 480px;
    }

    .admin-cells-search svg {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted, #6C7799);
      pointer-events: none;
    }

    .admin-cells-search input {
      width: 100%;
      padding: 10px 42px 10px 14px;
      border-radius: 12px;
      border: 1.5px solid var(--border, #E8E4DC);
      background: var(--bg-alt, #FFF8EF);
      font-family: inherit;
      font-size: 0.92rem;
      font-weight: 700;
      color: var(--ink, #0B1220);
      outline: none;
      transition: all 0.2s ease;
    }

    body.dark.admin-body .admin-cells-search input {
      background: #0B1020;
      border-color: rgba(255, 255, 255, 0.12);
      color: #F8FAFC;
    }

    .admin-cells-search input:focus {
      border-color: #FF6D00;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(255, 109, 0, 0.15);
    }

    body.dark.admin-body .admin-cells-search input:focus {
      background: #10162B;
    }

    .admin-cells-actions-group {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    /* Cells Stack List */
    .admin-cells-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .admin-cell-card {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 16px;
      border-radius: 16px;
      background: rgba(11, 18, 32, 0.02);
      border: 1.5px solid rgba(11, 18, 32, 0.08);
      transition: all 0.2s ease;
    }

    body.dark.admin-body .admin-cell-card {
      background: rgba(255, 255, 255, 0.03);
      border-color: rgba(255, 255, 255, 0.08);
    }

    .admin-cell-card:hover {
      border-color: rgba(255, 109, 0, 0.4);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
    }

    .admin-cell-card[hidden] {
      display: none !important;
    }

    .admin-cell-card__badge-wrap {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }

    .admin-cell-num {
      width: 32px;
      height: 32px;
      border-radius: 10px;
      background: rgba(11, 18, 32, 0.06);
      color: var(--ink, #0B1220);
      font-family: var(--font-display, 'Cairo');
      font-weight: 900;
      font-size: 0.88rem;
      display: grid;
      place-items: center;
    }

    body.dark.admin-body .admin-cell-num {
      background: rgba(255, 255, 255, 0.1);
      color: #F8FAFC;
    }

    .admin-cell-hex-icon {
      font-size: 1.25rem;
      color: #FF6D00;
    }

    .admin-cell-card__fields {
      display: grid;
      grid-template-columns: 80px 1fr 240px auto;
      gap: 12px;
      align-items: center;
      flex: 1;
      min-width: 0;
    }

    .admin-cell-field {
      display: flex;
      flex-direction: column;
      gap: 4px;
      min-width: 0;
    }

    .admin-cell-label {
      font-size: 0.76rem;
      font-weight: 800;
      color: var(--muted, #6C7799);
    }

    .admin-cell-input {
      width: 100% !important;
      padding: 10px 14px !important;
      border-radius: 10px !important;
      border: 1.5px solid var(--border, #E8E4DC) !important;
      background: #fff !important;
      font-family: inherit !important;
      font-size: 0.95rem !important;
      font-weight: 700 !important;
      color: var(--ink, #0B1220) !important;
      outline: none !important;
      box-sizing: border-box !important;
      transition: all 0.2s ease !important;
    }

    body.dark.admin-body .admin-cell-input {
      background: #0B1020 !important;
      border-color: rgba(255, 255, 255, 0.14) !important;
      color: #F8FAFC !important;
    }

    .admin-cell-input:focus {
      border-color: #FF6D00 !important;
      box-shadow: 0 0 0 3px rgba(255, 109, 0, 0.15) !important;
    }

    .admin-cell-input--letter {
      text-align: center;
      font-size: 1.15rem !important;
      font-weight: 900 !important;
      color: #FF6D00 !important;
    }

    body.dark.admin-body .admin-cell-input--letter {
      color: #FFB300 !important;
    }

    .admin-cell-card__actions {
      display: flex;
      align-items: flex-end;
      padding-top: 18px;
      flex-shrink: 0;
    }

    .admin-cell-remove-btn {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      border: 1.5px solid rgba(255, 23, 68, 0.3);
      background: rgba(255, 23, 68, 0.08);
      color: #FF1744;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 900;
      display: grid;
      place-items: center;
      transition: all 0.18s ease;
    }

    .admin-cell-remove-btn:hover {
      background: #FF1744;
      color: #fff;
      transform: scale(1.06);
    }

    .admin-form-footer {
      display: flex;
      gap: 14px;
      align-items: center;
      margin-top: 14px;
      padding-top: 18px;
      border-top: 1px solid var(--border, #E8E4DC);
    }

    @media (max-width: 900px) {
      .admin-cell-card__fields {
        grid-template-columns: 70px 1fr 180px auto;
      }
    }

    @media (max-width: 768px) {
      .admin-cell-card {
        flex-direction: column;
        align-items: stretch;
      }

      .admin-cell-card__fields {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .admin-cell-card__actions {
        padding-top: 4px;
        justify-content: flex-end;
      }
    }
  </style>

  <script>
    (function () {
      const container = document.getElementById('letterGridCells');
      const addBtn = document.getElementById('addLetterGridCell');
      const fillBtn = document.getElementById('fillArabicAlphabet');
      const searchInput = document.getElementById('cellSearchInput');
      const counterEl = document.getElementById('cellsTotalCount');

      const arabicLetters = ['أ', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'ه', 'و', 'ي'];

      const honeycombPos = (index) => {
        const preset = @json(config('letter_grid.default_layout', []));
        if (preset[index]) return preset[index];
        const overflow = index - preset.length;
        const row = Math.floor(preset.length / 5) + Math.floor(overflow / 5);
        return { row: Math.max(row, 0), col: overflow % 5 };
      };

      const updateCount = () => {
        const total = container.querySelectorAll('[data-cell-row]').length;
        if (counterEl) counterEl.textContent = String(total);
      };

      const reindex = () => {
        container.querySelectorAll('[data-cell-row]').forEach((row, index) => {
          row.dataset.index = index;
          const numEl = row.querySelector('[data-cell-num]');
          if (numEl) numEl.textContent = index + 1;

          row.querySelectorAll('input[name]').forEach((input) => {
            input.name = input.name.replace(/cells\[\d+\]/, `cells[${index}]`);
          });

          const pos = honeycombPos(index);
          const rowInp = row.querySelector('[data-cell-row-input]');
          const colInp = row.querySelector('[data-cell-col-input]');
          if (rowInp) rowInp.value = pos.row;
          if (colInp) colInp.value = pos.col;
        });
        updateCount();
      };

      const addRow = (letter = '', question = '', answer = '') => {
        const index = container.querySelectorAll('[data-cell-row]').length;
        const pos = honeycombPos(index);
        const row = document.createElement('div');
        row.className = 'admin-cell-card';
        row.dataset.cellRow = '1';
        row.dataset.index = index;
        row.dataset.letter = letter;

        row.innerHTML = `
        <input type="hidden" name="cells[${index}][row]" value="${pos.row}" data-cell-row-input>
        <input type="hidden" name="cells[${index}][col]" value="${pos.col}" data-cell-col-input>
        <input type="hidden" name="cells[${index}][is_active]" value="1">

        <div class="admin-cell-card__badge-wrap">
          <span class="admin-cell-num" data-cell-num>${index + 1}</span>
          <div class="admin-cell-hex-icon">⬡</div>
        </div>

        <div class="admin-cell-card__fields">
          <div class="admin-cell-field admin-cell-field--letter">
            <span class="admin-cell-label">الحرف</span>
            <input
              type="text"
              name="cells[${index}][letter]"
              class="admin-cell-input admin-cell-input--letter"
              value="${letter}"
              maxlength="3"
              dir="rtl"
              required
              placeholder="أ"
              data-letter-input
            >
          </div>

          <div class="admin-cell-field admin-cell-field--question">
            <span class="admin-cell-label">نص السؤال</span>
            <input
              type="text"
              name="cells[${index}][question_text]"
              class="admin-cell-input"
              value="${question}"
              required
              dir="rtl"
              placeholder="اكتب نص السؤال هنا…"
            >
          </div>

          <div class="admin-cell-field admin-cell-field--answer">
            <span class="admin-cell-label">الإجابة (تبدأ بالحرف)</span>
            <input
              type="text"
              name="cells[${index}][answer_text]"
              class="admin-cell-input"
              value="${answer}"
              required
              dir="rtl"
              placeholder="الإجابة الصحيحة"
            >
          </div>

          <div class="admin-cell-card__actions">
            <button type="button" class="admin-cell-remove-btn" data-remove-cell title="حذف هذا الحرف">
              ✕
            </button>
          </div>
        </div>
      `;

        container.appendChild(row);
      };

      // Remove letter row
      container?.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('[data-remove-cell]');
        if (removeBtn) {
          const row = removeBtn.closest('[data-cell-row]');
          if (container.querySelectorAll('[data-cell-row]').length <= 2) {
            alert('يجب أن تحتوي شبكة الحروف على حرفين على الأقل.');
            return;
          }
          row?.remove();
          reindex();
        }
      });

      // Sync letter data attribute on input
      container?.addEventListener('input', (e) => {
        if (e.target.matches('[data-letter-input]')) {
          const row = e.target.closest('[data-cell-row]');
          if (row) {
            row.dataset.letter = e.target.value.trim();
          }
        }
      });

      // Add letter button
      addBtn?.addEventListener('click', () => {
        addRow();
        reindex();
        const lastRow = container.querySelector('[data-cell-row]:last-child');
        lastRow?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        lastRow?.querySelector('[data-letter-input]')?.focus();
      });

      // Fill 28 Arabic letters
      fillBtn?.addEventListener('click', () => {
        if (!confirm('سيتم استبدال الحروف الحالية بالحروف العربية الـ 28. هل تود المتابعة؟')) return;
        container.innerHTML = '';
        arabicLetters.forEach((l) => addRow(l, '', ''));
        reindex();
        if (searchInput) searchInput.value = '';
      });

      // Fast search filter
      searchInput?.addEventListener('input', (e) => {
        const q = e.target.value.trim().toLowerCase();
        container.querySelectorAll('[data-cell-row]').forEach((row) => {
          const letter = (row.dataset.letter || '').toLowerCase();
          const qText = (row.querySelector('input[name*="question_text"]')?.value || '').toLowerCase();
          const aText = (row.querySelector('input[name*="answer_text"]')?.value || '').toLowerCase();

          if (!q || letter.includes(q) || qText.includes(q) || aText.includes(q)) {
            row.hidden = false;
          } else {
            row.hidden = true;
          }
        });
      });
    })();
  </script>
</x-layouts.admin>