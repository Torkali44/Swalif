<x-layouts.admin>
  <x-slot:heading>الأسئلة</x-slot:heading>
  <x-slot:subheading>أضف وراجع أسئلة كل فئة</x-slot:subheading>

  <form class="toolbar" method="GET" action="{{ route('admin.questions.index') }}">
    <input class="search-inp" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="بحث في نص السؤال…">
    <select class="select" name="category_id">
      <option value="">كل الفئات</option>
      @foreach($categories as $category)
        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
          {{ $category->icon }} {{ $category->name_ar }}
        </option>
      @endforeach
    </select>
    <select class="select" name="level">
      <option value="">كل المستويات</option>
      <option value="easy" @selected(($filters['level'] ?? '') === 'easy')>سهل</option>
      <option value="medium" @selected(($filters['level'] ?? '') === 'medium')>متوسط</option>
      <option value="hard" @selected(($filters['level'] ?? '') === 'hard')>صعب</option>
    </select>
    <select class="select" name="points">
      <option value="">كل النقاط</option>
      @foreach([200, 400, 600] as $points)
        <option value="{{ $points }}" @selected((string) ($filters['points'] ?? '') === (string) $points)>{{ $points }}</option>
      @endforeach
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

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>الفئة</th>
          <th>السؤال</th>
          <th>المستوى</th>
          <th>النقاط</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @forelse($questions as $question)
          <tr>
            <td>{{ $question->category->icon }} {{ $question->category->name_ar }}</td>
            <td>{{ $question->question_text }}</td>
            <td>
              <span class="badge-level lvl-{{ $question->points }}">{{ $question->level->label() }}</span>
            </td>
            <td>{{ $question->points }}</td>
            <td>
              <a href="{{ route('admin.questions.edit', $question) }}">تعديل</a>
              <form class="inline" method="POST" action="{{ route('admin.questions.destroy', $question) }}">
                @csrf
                @method('DELETE')
                <button type="submit">حذف</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5">لا توجد أسئلة مطابقة للفلتر.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $questions->links() }}
</x-layouts.admin>
