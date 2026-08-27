<x-layouts.admin>
  <x-slot:heading>الأسئلة</x-slot:heading>
  <x-slot:subheading>كل فئة وأسئلةها تحتها — سهولة الوصول والمراجعة</x-slot:subheading>

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.questions.index') }}" data-auto-filter>
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث في نص السؤال…">
    <input class="search-inp" type="search" id="categoryFinder" placeholder="ابحث عن فئة بالاسم…" autocomplete="off" data-category-finder>
    <select class="select" name="category_id" id="filterCategorySelect">
      <option value="">كل الفئات (الأحدث أولاً)</option>
      @foreach($categories as $category)
        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
          {{ $category->icon }} {{ $category->name_ar }}
          @if(isset($category->questions_count)) ({{ $category->questions_count }}) @endif
        </option>
      @endforeach
    </select>
    <select class="select" name="classification_id">
      <option value="">كل التصنيفات</option>
      @foreach($classifications as $classification)
        <option value="{{ $classification->id }}" @selected((string) ($filters['classification_id'] ?? '') === (string) $classification->id)>
          {{ $classification->icon }} {{ $classification->name_ar }}
        </option>
      @endforeach
    </select>
    <select class="select" name="type">
      <option value="">كل الأنواع</option>
      @foreach($questionTypes as $questionType)
        <option value="{{ $questionType['value'] }}" @selected(($filters['type'] ?? '') === $questionType['value'])>
          {{ $questionType['label'] }}</option>
      @endforeach
    </select>
    <select class="select" name="level">
      <option value="">كل المستويات</option>
      <option value="easy" @selected(($filters['level'] ?? '') === 'easy')>سهل</option>
      <option value="medium" @selected(($filters['level'] ?? '') === 'medium')>متوسط</option>
      <option value="hard" @selected(($filters['level'] ?? '') === 'hard')>صعب</option>
    </select>
    <select class="select" name="status">
      <option value="">كل الحالات</option>
      <option value="active" @selected(($filters['status'] ?? '') === 'active')>مفعّل</option>
      <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>موقوف</option>
    </select>
    <a class="btn btn-ghost" href="{{ route('admin.questions.index') }}">إعادة</a>
    <div class="spacer"></div>
    <a class="btn btn-primary" href="{{ route('admin.questions.create', array_filter(['category_id' => $filters['category_id'] ?? null])) }}">+ سؤال جديد</a>
  </form>

  <div class="q-groups" id="qGroups">
    @forelse($groupedCategories as $category)
      @php
        $forceOpen = (string) ($filters['open'] ?? $filters['category_id'] ?? '') === (string) $category->id;
      @endphp
      <details class="q-group" data-category-id="{{ $category->id }}" data-category-name="{{ mb_strtolower($category->name_ar.' '.($category->name_en ?? '')) }}" @if($forceOpen) open @endif>
        <summary class="q-group__head">
          <div class="q-group__title">
            <span class="q-group__icon">{{ $category->icon ?: '🎯' }}</span>
            <div>
              <b>{{ $category->name_ar }}</b>
              <small>{{ $category->classificationName() }} · <span class="shown-count">{{ $category->questions->count() }}</span> سؤال معروض ·
                <span class="total-count">{{ $category->questions_count }}</span> إجمالي</small>
            </div>
          </div>
          <div class="q-group__actions">
            <a class="btn btn-sm btn-primary"
              href="{{ route('admin.questions.create', ['category_id' => $category->id]) }}">+ إضافة</a>
            <span class="q-group__chevron">▾</span>
          </div>
        </summary>

        <div class="q-group__body">
          @if($category->questions->isEmpty())
            <div class="empty-panel" style="margin:12px 0">
              لا توجد أسئلة داخل هذه الفئة بعد.
              <a class="btn btn-sm btn-primary" href="{{ route('admin.questions.create', ['category_id' => $category->id]) }}">أضف أول سؤال</a>
            </div>
          @else
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>السؤال</th>
                    <th>النوع</th>
                    <th>المستوى</th>
                    <th>النقاط</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($category->questions as $question)
                            <tr id="q-row-{{ $question->id }}">
                              <td class="q-text">{{ $question->question_text }}</td>
                              <td>
                                @php
                                  $typeLabel = match ($question->type ?? 'standard') {
                                    'image_guess' => 'خمن الصورة',
                                    'puzzle' => 'جواب واحد',
                                    'match' => 'توصيل',
                                    'complete' => 'أكمل الناقص',
                                    'order' => 'ترتيب',
                                    'word_build' => 'رتبها',
                                    'video' => 'فيديو',
                                    'audio' => 'صوتي',
                                    'standard' => 'اختياري',
                                    default => \App\Enums\QuestionType::tryFrom($question->type ?? '')?->label() ?? 'اختياري',
                                  };
                                  $typeIcon = match ($question->type ?? 'standard') {
                                    'video' => '🎬',
                                    'audio' => '🎧',
                                    'image_guess' => '🖼️',
                                    'puzzle' => '🧩',
                                    'match' => '🔗',
                                    'complete' => '✏️',
                                    'order' => '🔢',
                                    'word_build' => '🔤',
                                    default => '📝',
                                  };
                                  $typeColor = match ($question->type ?? 'standard') {
                                    'video' => 'background:rgba(124,58,237,.12);color:#7C3AED;border-color:rgba(124,58,237,.3)',
                                    'audio' => 'background:rgba(0,180,216,.12);color:#0077A8;border-color:rgba(0,180,216,.3)',
                                    'image_guess' => 'background:rgba(14,159,110,.1);color:#0E9F6E;border-color:rgba(14,159,110,.3)',
                                    'match' => 'background:rgba(236,72,153,.1);color:#BE185D;border-color:rgba(236,72,153,.3)',
                                    'order' => 'background:rgba(255,140,0,.1);color:#B45309;border-color:rgba(255,140,0,.3)',
                                    'word_build' => 'background:rgba(168,85,247,.12);color:#7E22CE;border-color:rgba(168,85,247,.3)',
                                    'puzzle' => 'background:rgba(239,68,68,.1);color:#B91C1C;border-color:rgba(239,68,68,.3)',
                                    'complete' => 'background:rgba(59,130,246,.1);color:#1D4ED8;border-color:rgba(59,130,246,.3)',
                                    default => '',
                                  };
                                @endphp
                                <span class="status-pill admin" style="{{ $typeColor }}">
                                  {{ $typeIcon }} {{ $typeLabel }}
                                </span>
                              </td>
                              <td>
                                <span class="badge-level lvl-{{ $question->points }}">{{ $question->level->label() }}</span>
                              </td>
                              <td>{{ $question->points }}</td>
                              <td>
                                <span class="status-pill {{ $question->is_active ? 'on' : 'off' }}" id="status-pill-{{ $question->id }}">
                                  {{ $question->is_active ? 'مفعّل' : 'موقوف' }}
                                </span>
                              </td>
                              <td class="row-actions">
                                <button class="btn btn-sm btn-primary" type="button"
                                  data-q="{{ json_encode([
                                    'id'         => $question->id,
                                    'text'       => $question->question_text,
                                    'type'       => $question->type ?? 'standard',
                                    'level'      => $question->level?->label() ?? 'متوسط',
                                    'levelClass' => match($question->level?->value ?? 'medium') { 'easy' => 'easy', 'hard' => 'hard', default => 'medium' },
                                    'points'     => $question->points,
                                    'answer'     => $question->correctAnswerText(),
                                    'imageUrl'   => $question->imageUrl() ?? null,
                                    'mediaUrl'   => $question->mediaUrl() ?? null,
                                    'isVideo'    => $question->isVideo(),
                                    'isAudio'    => $question->isAudio(),
                                    'options'    => $question->options->map(fn($o) => ['text' => $o->option_text, 'correct' => (bool)$o->is_correct])->values()->all(),
                                    'orderItems' => array_values($question->orderItems()),
                                    'matchPairs' => array_values($question->matchPairs()),
                                    'wordBuildLetters' => array_values($question->wordBuildLetters()),
                                    'validWords' => array_values($question->validWords()),
                                  ], JSON_UNESCAPED_UNICODE) }}"
                                  onclick="previewQuestionFromBtn(this)">عرض</button>
                                <a class="btn btn-sm btn-outline" href="{{ route('admin.questions.edit', $question) }}?return_category={{ $category->id }}">تعديل</a>
                                <form class="inline" method="POST" action="{{ route('admin.questions.toggle', $question) }}">
                                  @csrf
                                  @method('PATCH')
                                  <button class="btn btn-sm btn-ghost"
                                    type="submit" onclick="toggleQuestionStatus(event, this.form, {{ $question->id }})">{{ $question->is_active ? 'إيقاف' : 'تفعيل' }}</button>
                                </form>
                                <form class="inline" method="POST" action="{{ route('admin.questions.destroy', $question) }}">
                                  @csrf
                                  @method('DELETE')
                                  <button class="btn btn-sm btn-danger" type="submit"
                                    onclick="confirmDeleteQuestion(event, this.form, '{{ e(addslashes($question->question_text)) }}', {{ $question->id }}, {{ $category->id }})">حذف</button>
                                </form>
                              </td>
                            </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </details>
    @empty
      <div class="empty-panel">لا توجد فئات/أسئلة مطابقة للفلتر.</div>
    @endforelse
  </div>

  <script>
    /* Keep opened categories sticky until the admin closes them */
    (() => {
      const KEY = 'swalif.admin.qOpenCategories';
      const groups = [...document.querySelectorAll('details.q-group[data-category-id]')];
      if (!groups.length) return;

      const readOpen = () => {
        try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (_) { return []; }
      };
      const writeOpen = (ids) => {
        try { localStorage.setItem(KEY, JSON.stringify([...new Set(ids.map(String))])); } catch (_) {}
      };

      const forcedOpen = @json((string) ($filters['open'] ?? $filters['category_id'] ?? ''));
      const focusId = @json((string) ($filters['focus'] ?? ''));
      let openIds = readOpen();

      if (forcedOpen) {
        openIds = [...new Set([...openIds, String(forcedOpen)])];
        writeOpen(openIds);
      }

      groups.forEach((el) => {
        const id = String(el.dataset.categoryId || '');
        if (openIds.includes(id) || (forcedOpen && forcedOpen === id)) {
          el.open = true;
        }

        el.addEventListener('toggle', () => {
          const current = readOpen().filter((x) => x !== id);
          if (el.open) current.push(id);
          writeOpen(current);
        });
      });

      // Instant category name finder (client-side) for large catalogs
      const finder = document.querySelector('[data-category-finder]');
      if (finder) {
        finder.addEventListener('input', () => {
          const q = (finder.value || '').trim().toLowerCase();
          groups.forEach((el) => {
            const name = (el.dataset.categoryName || '').toLowerCase();
            const match = !q || name.includes(q);
            el.hidden = !match;
            if (match && q && q.length >= 2) el.open = true;
          });
        });
      }

      const scrollTarget = focusId
        ? document.getElementById('q-row-' + focusId)
        : (forcedOpen ? document.querySelector('details.q-group[data-category-id="' + forcedOpen + '"]') : null);

      if (scrollTarget) {
        setTimeout(() => {
          scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
          if (scrollTarget.tagName === 'TR') {
            scrollTarget.style.outline = '2px solid rgba(255,109,0,.55)';
            setTimeout(() => { scrollTarget.style.outline = ''; }, 2500);
          }
        }, 120);
      }
    })();
  </script>

  {{-- ===== Question Preview Modal ===== --}}
  <div id="qPreviewModal"
    style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);overflow-y:auto;"
    onclick="if(event.target===this)closePreview()">
    <div
      style="max-width:680px;margin:40px auto;background:var(--card,#1e2035);border-radius:20px;padding:0;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.6);">

      {{-- top bar --}}
      <div
        style="background:linear-gradient(135deg,#7C3AED,#00B4D8);padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
          <span id="qp-level-chip"
            style="padding:4px 12px;border-radius:99px;font-size:.78rem;font-weight:700;background:rgba(255,255,255,.2);color:#fff"></span>
          <span id="qp-points-chip"
            style="padding:4px 12px;border-radius:99px;font-size:.78rem;font-weight:700;background:rgba(255,255,255,.15);color:#fff"></span>
        </div>
        <button onclick="closePreview()"
          style="background:rgba(255,255,255,.2);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1.1rem;line-height:1">✕</button>
      </div>

      {{-- body --}}
      <div style="padding:28px 24px;">
        <p style="font-size:.8rem;color:var(--muted,#888);margin:0 0 8px">السؤال</p>
        <h2 id="qp-text" style="font-size:1.3rem;font-weight:800;line-height:1.6;color:var(--fg,#fff);margin:0 0 20px">
        </h2>

        <div id="qp-media-wrap" style="display:none;margin-bottom:20px;text-align:center;">
          <img id="qp-image" src="" alt=""
            style="display:none;max-width:100%;max-height:240px;border-radius:12px;object-fit:contain;margin:0 auto;">
          <video id="qp-video" controls playsinline
            style="display:none;max-width:100%;max-height:300px;border-radius:12px;width:100%;background:#000;margin:0 auto;"></video>
          <audio id="qp-audio" controls style="display:none;width:100%;margin-top:10px;"></audio>
        </div>

        {{-- options --}}
        <div id="qp-options" style="display:grid;gap:10px;margin-bottom:20px;"></div>

        {{-- order --}}
        <div id="qp-order" style="display:none;margin-bottom:20px;">
          <p style="font-size:.85rem;color:var(--muted,#888);margin:0 0 8px">الترتيب الصحيح:</p>
          <ol id="qp-order-list"
            style="padding-right:20px;display:flex;flex-direction:column;gap:8px;list-style:decimal;"></ol>
        </div>

        {{-- match --}}
        <div id="qp-match" style="display:none;margin-bottom:20px;">
          <p style="font-size:.85rem;color:var(--muted,#888);margin:0 0 8px">الربط الصحيح:</p>
          <div id="qp-match-list" style="display:flex;flex-direction:column;gap:8px;"></div>
        </div>

        {{-- word build --}}
        <div id="qp-word-build" style="display:none;margin-bottom:20px;">
          <p style="font-size:.85rem;color:var(--muted,#888);margin:0 0 8px">حروف رتبها:</p>
          <div id="qp-word-build-letters" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;"></div>
          <p style="font-size:.85rem;color:var(--muted,#888);margin:0 0 8px">الكلمات الصحيحة:</p>
          <div id="qp-word-build-words" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
        </div>

        {{-- answer --}}
        <div id="qp-answer-wrap"
          style="background:rgba(14,159,110,.12);border:1px solid rgba(14,159,110,.3);border-radius:12px;padding:14px 16px;">
          <p style="font-size:.8rem;color:#0E9F6E;margin:0 0 4px;font-weight:700">✅ الإجابة الصحيحة</p>
          <p id="qp-answer" style="margin:0;font-size:1rem;font-weight:600;color:var(--fg,#fff)"></p>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== Question Delete Confirm Modal ===== --}}
  <div id="deleteConfirmModal"
    style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);overflow-y:auto;"
    onclick="if(event.target===this)closeDeleteModal()">
    <div style="max-width:440px;margin:120px auto;background:var(--card,#1e2035);border-radius:24px;padding:32px 28px;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,.5);direction:rtl;border:1px solid rgba(255,255,255,.1);">
      <div style="width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,.15);color:#ef4444;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 16px;">🗑️</div>
      <h3 style="font-size:1.25rem;font-weight:900;margin:0 0 10px;color:var(--fg,#fff);">تأكيد حذف السؤال</h3>
      <p id="deleteConfirmText" style="font-size:.9rem;color:var(--muted,#9ca3af);line-height:1.6;margin:0 0 24px;word-break:break-word;"></p>
      <div style="display:flex;gap:12px;justify-content:center;">
        <button type="button" id="deleteConfirmSubmitBtn" style="background:#ef4444;color:#fff;border:none;padding:12px 24px;border-radius:50px;font-weight:800;font-size:.95rem;cursor:pointer;transition:all .2s;">حذف النهائي</button>
        <button type="button" onclick="closeDeleteModal()" style="background:rgba(255,255,255,.1);color:var(--fg,#fff);border:none;padding:12px 24px;border-radius:50px;font-weight:800;font-size:.95rem;cursor:pointer;transition:all .2s;">إلغاء</button>
      </div>
    </div>
  </div>

  <script>
    const keys = ['أ', 'ب', 'ج', 'د', 'هـ', 'و'];
    const levelLabels = { easy: 'سهل 🟢', medium: 'متوسط 🟡', hard: 'صعب 🔴' };

    // Called from onclick="previewQuestionFromBtn(this)"
    // Reads JSON safely from data-q attribute (avoids HTML quote-breaking issues)
    function previewQuestionFromBtn(btn) {
      try {
        const q = JSON.parse(btn.getAttribute('data-q'));
        previewQuestion(q);
      } catch (err) {
        console.error('فشل تحليل بيانات السؤال:', err, btn.getAttribute('data-q'));
      }
    }

    function previewQuestion(q) {
      document.getElementById('qp-text').textContent = q.text;

      // level & points chips
      document.getElementById('qp-level-chip').textContent = levelLabels[q.levelClass] || q.level;
      document.getElementById('qp-points-chip').textContent = q.points + ' نقطة';

      // media (image / video / audio)
      const mediaWrap = document.getElementById('qp-media-wrap');
      const img = document.getElementById('qp-image');
      const video = document.getElementById('qp-video');
      const audio = document.getElementById('qp-audio');

      video.pause(); video.removeAttribute('src'); video.load(); video.style.display = 'none';
      audio.pause(); audio.removeAttribute('src'); audio.load(); audio.style.display = 'none';
      img.src = ''; img.style.display = 'none';
      mediaWrap.style.display = 'none';

      if ((q.isVideo || q.type === 'video' || q.type === 'Video') && q.mediaUrl) {
        video.src = q.mediaUrl;
        video.style.display = 'block';
        mediaWrap.style.display = 'block';
      } else if ((q.isAudio || q.type === 'audio' || q.type === 'Audio') && q.mediaUrl) {
        audio.src = q.mediaUrl;
        audio.style.display = 'block';
        mediaWrap.style.display = 'block';
      } else if (q.imageUrl) {
        img.src = q.imageUrl;
        img.style.display = 'block';
        mediaWrap.style.display = 'block';
      }

      // reset sections
      ['qp-options', 'qp-order', 'qp-match'].forEach(id => { document.getElementById(id).innerHTML = ''; document.getElementById(id).style.display = 'none'; });
      ['qp-word-build-letters', 'qp-word-build-words'].forEach(id => { const el = document.getElementById(id); if (el) el.innerHTML = ''; });
      const wbWrap = document.getElementById('qp-word-build');
      if (wbWrap) wbWrap.style.display = 'none';

      // ✅ Types with multiple-choice options
      const mcqTypes = ['standard', 'image_guess', 'puzzle', 'complete'];
      if (mcqTypes.includes(q.type) && q.options && q.options.length) {
        const wrap = document.getElementById('qp-options');
        wrap.style.display = 'grid';
        q.options.forEach((opt, i) => {
          const btn = document.createElement('div');
          btn.style.cssText = 'display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:12px;border:2px solid ' + (opt.correct ? '#0E9F6E' : 'rgba(255,255,255,.12)') + ';background:' + (opt.correct ? 'rgba(14,159,110,.15)' : 'rgba(255,255,255,.04)') + ';color:var(--fg,#fff);';
          btn.innerHTML = '<span style="min-width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem">' + (keys[i] || i + 1) + '</span><span style="flex:1">' + opt.text + '</span>' + (opt.correct ? '<span style="color:#0E9F6E;font-size:1.1rem">✔</span>' : '');
          wrap.appendChild(btn);
        });

        // ✅ Order type — orderItems is a plain JS array ["item1", "item2", ...]
      } else if (q.type === 'order') {
        const items = Array.isArray(q.orderItems) ? q.orderItems : Object.values(q.orderItems || {});
        if (items.length) {
          const wrap = document.getElementById('qp-order');
          const list = document.getElementById('qp-order-list');
          wrap.style.display = 'block';
          items.forEach((text, idx) => {
            const li = document.createElement('li');
            li.style.cssText = 'padding:10px 14px;border-radius:10px;background:rgba(255,255,255,.06);color:var(--fg,#fff);display:flex;align-items:center;gap:10px;';
            li.innerHTML = '<span style="min-width:24px;height:24px;background:rgba(255,140,0,.2);color:#FF8C00;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0">' + (idx + 1) + '</span><span>' + text + '</span>';
            list.appendChild(li);
          });
        }

        // ✅ Match type — matchPairs is [{left, right}, ...]
      } else if (q.type === 'match') {
        const pairs = Array.isArray(q.matchPairs) ? q.matchPairs : [];
        if (pairs.length) {
          const wrap = document.getElementById('qp-match');
          const list = document.getElementById('qp-match-list');
          wrap.style.display = 'block';
          pairs.forEach(pair => {
            const row = document.createElement('div');
            row.style.cssText = 'display:grid;grid-template-columns:1fr 36px 1fr;gap:8px;align-items:center;padding:10px 14px;border-radius:10px;background:rgba(255,255,255,.06);color:var(--fg,#fff);';
            row.innerHTML = '<span style="text-align:right">' + pair.left + '</span><span style="text-align:center;color:#7C3AED;font-weight:900;font-size:1rem">↔</span><span style="text-align:left">' + pair.right + '</span>';
            list.appendChild(row);
          });
        }

        // ✅ Word build type
      } else if (q.type === 'word_build') {
        const letters = Array.isArray(q.wordBuildLetters) ? q.wordBuildLetters : [];
        const words = Array.isArray(q.validWords) ? q.validWords : [];
        if (letters.length || words.length) {
          const wrap = document.getElementById('qp-word-build');
          const lettersWrap = document.getElementById('qp-word-build-letters');
          const wordsWrap = document.getElementById('qp-word-build-words');
          wrap.style.display = 'block';
          letters.forEach(letter => {
            const tile = document.createElement('span');
            tile.style.cssText = 'min-width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;background:rgba(168,85,247,.15);color:#fff;font-weight:900;font-size:1.2rem;border:1px solid rgba(168,85,247,.3);';
            tile.textContent = letter;
            lettersWrap.appendChild(tile);
          });
          words.forEach(word => {
            const chip = document.createElement('span');
            chip.style.cssText = 'padding:8px 14px;border-radius:12px;background:rgba(236,72,153,.15);color:#fff;font-weight:800;border:1px solid rgba(236,72,153,.3);';
            chip.textContent = word;
            wordsWrap.appendChild(chip);
          });
        }
      }

      // answer text
      const ans = q.answer || (q.options ? (q.options.find(o => o.correct) || {}).text : '') || '—';
      document.getElementById('qp-answer').textContent = ans;

      document.getElementById('qPreviewModal').style.display = 'block';
      document.body.style.overflow = 'hidden';
    }

    function closePreview() {
      const video = document.getElementById('qp-video');
      const audio = document.getElementById('qp-audio');
      if (video) { video.pause(); video.removeAttribute('src'); video.load(); }
      if (audio) { audio.pause(); audio.removeAttribute('src'); audio.load(); }
      document.getElementById('qPreviewModal').style.display = 'none';
      document.body.style.overflow = '';
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        closePreview();
        closeDeleteModal();
      }
    });

    /* ── Accordion Open State Persistence ───────────── */
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('details.q-group').forEach(det => {
        const catId = det.dataset.categoryId;
        if (catId) {
          const isSavedOpen = localStorage.getItem('admin_cat_open_' + catId);
          if (isSavedOpen === 'true') det.open = true;

          det.addEventListener('toggle', () => {
            localStorage.setItem('admin_cat_open_' + catId, det.open ? 'true' : 'false');
          });
        }
      });
    });

    /* ── AJAX Question Deletion with Modal ──────────── */
    let pendingDeleteForm = null;
    let pendingQuestionRowId = null;
    let pendingCatDetailEl = null;

    function confirmDeleteQuestion(e, form, qText, questionId, categoryId) {
      e.preventDefault();
      e.stopPropagation();
      pendingDeleteForm = form;
      pendingQuestionRowId = 'q-row-' + questionId;
      pendingCatDetailEl = form.closest('details.q-group');

      document.getElementById('deleteConfirmText').textContent = 'هل أنت تأكد من رغبتك في حذف هذا السؤال؟ "' + qText + '"';
      document.getElementById('deleteConfirmModal').style.display = 'block';
      document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
      pendingDeleteForm = null;
      pendingQuestionRowId = null;
      pendingCatDetailEl = null;
      document.getElementById('deleteConfirmModal').style.display = 'none';
      document.body.style.overflow = '';
    }

    let deleteInFlight = false;

    document.getElementById('deleteConfirmSubmitBtn').addEventListener('click', function() {
      if (!pendingDeleteForm || deleteInFlight) return;

      const btn = this;
      const rowId = pendingQuestionRowId;
      const catDetail = pendingCatDetailEl;
      const url = pendingDeleteForm.action;
      const formData = new FormData(pendingDeleteForm);

      deleteInFlight = true;
      btn.disabled = true;
      btn.textContent = 'جاري الحذف...';

      fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(async (res) => {
        let data = {};
        try {
          data = await res.json();
        } catch (_) {}

        if (!res.ok) {
          if (res.status === 404 && rowId) {
            return { success: true };
          }
          throw new Error(data.message || 'تعذّر حذف السؤال.');
        }

        return data;
      })
      .then(data => {
        btn.disabled = false;
        btn.textContent = 'حذف النهائي';
        deleteInFlight = false;
        closeDeleteModal();

        if (data.success) {
          const row = rowId ? document.getElementById(rowId) : null;
          if (row) {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            setTimeout(() => {
              row.remove();
              if (catDetail) {
                const shownEl = catDetail.querySelector('.shown-count');
                if (shownEl) {
                  const currentCount = parseInt(shownEl.textContent) || 0;
                  if (currentCount > 0) shownEl.textContent = currentCount - 1;
                }
              }
            }, 300);
          }

          if (typeof showToast === 'function') {
            showToast('تم حذف السؤال بنجاح ✓', 'success');
          } else if (typeof toast === 'function') {
            toast('تم حذف السؤال بنجاح ✓');
          }
        } else {
          alert(data.message || 'حدث خطأ أثناء الحذف');
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.textContent = 'حذف النهائي';
        deleteInFlight = false;
        closeDeleteModal();
        alert(err.message || 'حدث خطأ بالاتصال. حاول مجدداً.');
      });
    });

    /* ── AJAX Question Status Toggle ──────────────────── */
    function toggleQuestionStatus(e, form, questionId) {
      e.preventDefault();
      e.stopPropagation();

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      const url = form.action;
      const formData = new FormData(form);

      fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (submitBtn) submitBtn.disabled = false;
        if (data.success) {
          if (submitBtn) submitBtn.textContent = data.is_active ? 'إيقاف' : 'تفعيل';
          const pill = document.getElementById('status-pill-' + questionId);
          if (pill) {
            pill.className = 'status-pill ' + (data.is_active ? 'on' : 'off');
            pill.textContent = data.is_active ? 'مفعّل' : 'موقوف';
          }
          if (typeof showToast === 'function') {
            showToast(data.message || 'تم تحديث حالة السؤال ✓', 'success');
          } else if (typeof toast === 'function') {
            toast('تم تحديث حالة السؤال ✓');
          }
        }
      })
      .catch(() => {
        if (submitBtn) submitBtn.disabled = false;
      });
    }
  </script>

</x-layouts.admin>