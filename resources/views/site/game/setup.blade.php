<x-layouts.app title="تجهيز اللعبة — سوالف">
<div class="setup-page-wrapper">
  <!-- Blurred Category Show Background -->
  <div class="setup-page-bg">
    <section class="page-hero category-show-hero">
      <div class="container category-show-wrap">
        <div class="cat-circle cat-circle--lg" style="background: linear-gradient(135deg, #0F6B4C, #084A34)">
          <div class="cat-circle__ring">
            @if($category->imageUrl())
              <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}">
            @else
              <span class="cat-circle__emoji">{{ $category->icon ?: '🎯' }}</span>
            @endif
          </div>
          <div class="cat-circle__label">
            <span class="cat-circle__name">{{ $category->name_ar }}</span>
          </div>
        </div>
        <p class="category-show-desc" style="display:none">{{ $category->description }}</p>
      </div>
    </section>
  </div>

  <!-- Modal Dialog Overlay -->
  <div class="setup-modal-overlay">
    <form class="setup-modal-card" method="POST" action="{{ route('game.start') }}">
      @csrf
      <input type="hidden" name="category_id" value="{{ $category->id }}">
      <input type="hidden" name="name" value="تحدي {{ $category->name_ar }}">

      <a href="{{ route('categories.show', $category) }}" class="setup-modal-close" title="إغلاق">✕</a>

      <h1 class="setup-modal-title">{{ $category->name_ar }}</h1>
      <p class="setup-modal-sub">حدد معلومات الفرق</p>

      <div class="setup-modal-cols">
        <!-- Team 1 -->
        <div class="setup-modal-col">
          <h3>الفريق الأول</h3>
          <input name="team_one" class="setup-modal-input" placeholder="اسم الفريق" value="فريق الصقور" required>
          <div class="setup-modal-counter">
            <button type="button" class="counter-btn minus">—</button>
            <span class="counter-val">1</span>
            <button type="button" class="counter-btn plus">+</button>
          </div>
        </div>

        <!-- Team 2 -->
        <div class="setup-modal-col">
          <h3>الفريق الثاني</h3>
          <input name="team_two" class="setup-modal-input" placeholder="اسم الفريق" value="فريق النجوم" required>
          <div class="setup-modal-counter">
            <button type="button" class="counter-btn minus">—</button>
            <span class="counter-val">1</span>
            <button type="button" class="counter-btn plus">+</button>
          </div>
        </div>
      </div>

      <button class="setup-modal-submit" type="submit">ابدأ اللعب</button>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.setup-modal-counter').forEach(counter => {
      const minus = counter.querySelector('.minus');
      const plus = counter.querySelector('.plus');
      const val = counter.querySelector('.counter-val');
      minus.addEventListener('click', () => {
        let current = parseInt(val.textContent) || 1;
        if (current > 1) val.textContent = current - 1;
      });
      plus.addEventListener('click', () => {
        let current = parseInt(val.textContent) || 1;
        val.textContent = current + 1;
      });
    });
  });
</script>
</x-layouts.app>
