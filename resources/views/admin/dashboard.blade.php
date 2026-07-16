<x-layouts.admin>
  <x-slot:heading>نظرة عامة</x-slot:heading>
  <x-slot:subheading>ملخص سريع عن اللعبة والمحتوى</x-slot:subheading>

  <div class="stat-grid">
    <div class="stat-card grad-fire">
      <div class="stat-label">إجمالي الفئات</div>
      <div class="stat-value">{{ $stats['categories'] }}</div>
      <div class="stat-trend">↑ محتوى نشط</div>
    </div>
    <div class="stat-card grad-cool">
      <div class="stat-label">إجمالي الأسئلة</div>
      <div class="stat-value">{{ $stats['questions'] }}</div>
      <div class="stat-trend">↑ عبر كل المستويات</div>
    </div>
    <div class="stat-card grad-gold">
      <div class="stat-label">مشتركون فعّالون</div>
      <div class="stat-value">{{ $stats['subscribers'] }}</div>
      <div class="stat-trend">↑ اشتراكات حالية</div>
    </div>
    <div class="stat-card grad-emerald">
      <div class="stat-label">المستخدمون</div>
      <div class="stat-value">{{ $stats['users'] }}</div>
      <div class="stat-trend">↑ لاعبون مسجّلون</div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h3>أحدث الأسئلة المضافة</h3>
      <a href="{{ route('admin.questions.index') }}" class="link-more">عرض الكل ←</a>
    </div>
    <div class="mini-list">
      @forelse($recent as $question)
        <div class="mini-item">
          <span class="q">{{ $question->question_text }}</span>
          <span class="meta">
            <span>{{ $question->category->icon }} {{ $question->category->name_ar }}</span>
            <span>{{ $question->points }} نقطة</span>
          </span>
        </div>
      @empty
        <p class="muted">لا توجد أسئلة بعد.</p>
      @endforelse
    </div>
  </div>
</x-layouts.admin>
