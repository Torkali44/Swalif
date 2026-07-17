<x-layouts.admin>
  <x-slot:heading>الأسئلة</x-slot:heading>
  <x-slot:subheading>كل فئة وأسئلةها تحتها — سهولة الوصول والمراجعة</x-slot:subheading>

  <form class="toolbar toolbar--tight" method="GET" action="{{ route('admin.questions.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث في نص السؤال…">
    <select class="select" name="category_id">
      <option value="">كل الفئات</option>
      @foreach($categories as $category)
        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
          {{ $category->icon }} {{ $category->name_ar }}
        </option>
      @endforeach
    </select>
    <select class="select" name="group">
      <option value="">كل التصنيفات</option>
      <option value="uae" @selected(($filters['group'] ?? '') === 'uae')>إمارات</option>
      <option value="general" @selected(($filters['group'] ?? '') === 'general')>عامة</option>
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
    <button class="btn btn-outline" type="submit">تصفية</button>
    <a class="btn btn-ghost" href="{{ route('admin.questions.index') }}">إعادة</a>
    <div class="spacer"></div>
    <a class="btn btn-primary" href="{{ route('admin.questions.create') }}">+ سؤال جديد</a>
  </form>

  <div class="q-groups">
    @forelse($groupedCategories as $category)
      <details class="q-group" @if(($filters['category_id'] ?? null) || $loop->first) open @endif>
        <summary class="q-group__head">
          <div class="q-group__title">
            <span class="q-group__icon">{{ $category->icon ?: '🎯' }}</span>
            <div>
              <b>{{ $category->name_ar }}</b>
              <small>{{ $category->group === 'uae' ? 'إمارات' : 'عامة' }} · {{ $category->questions->count() }} سؤال معروض · {{ $category->questions_count }} إجمالي</small>
            </div>
          </div>
          <div class="q-group__actions">
            <a class="btn btn-sm btn-primary" href="{{ route('admin.questions.create', ['category_id' => $category->id]) }}">+ إضافة</a>
            <span class="q-group__chevron">▾</span>
          </div>
        </summary>

        <div class="q-group__body">
          @if($category->questions->isEmpty())
            <p class="muted">لا توجد أسئلة مطابقة داخل هذه الفئة.</p>
          @else
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>السؤال</th>
                    <th>المستوى</th>
                    <th>النقاط</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($category->questions as $question)
                    <tr>
                      <td class="q-text">{{ $question->question_text }}</td>
                      <td>
                        <span class="badge-level lvl-{{ $question->points }}">{{ $question->level->label() }}</span>
                      </td>
                      <td>{{ $question->points }}</td>
                      <td>
                        <span class="status-pill {{ $question->is_active ? 'on' : 'off' }}">
                          {{ $question->is_active ? 'مفعّل' : 'موقوف' }}
                        </span>
                      </td>
                      <td class="row-actions">
                        <a class="btn btn-sm btn-outline" href="{{ route('admin.questions.edit', $question) }}">تعديل</a>
                        <form class="inline" method="POST" action="{{ route('admin.questions.toggle', $question) }}">
                          @csrf
                          @method('PATCH')
                          <button class="btn btn-sm btn-ghost" type="submit">{{ $question->is_active ? 'إيقاف' : 'تفعيل' }}</button>
                        </form>
                        <form class="inline" method="POST" action="{{ route('admin.questions.destroy', $question) }}">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('حذف السؤال؟')">حذف</button>
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
</x-layouts.admin>
