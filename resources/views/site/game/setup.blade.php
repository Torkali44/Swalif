<x-layouts.app>
<section class="section">
  <form class="game-setup" method="POST" action="{{ route('game.start') }}">
    @csrf
    <input type="hidden" name="category_id" value="{{ $category->id }}">
    <span class="chip" style="background:rgba(255,59,78,.12);color:var(--uae-red);border:1px solid rgba(255,59,78,.25)">
      {{ $category->icon }} {{ $category->name_ar }}
    </span>
    <h1>جهّزوا الجولة</h1>
    <label>اسم اللعبة
      <input name="name" value="تحدي {{ $category->name_ar }}" required>
    </label>
    <div class="two-col">
      <label>الفريق الأول
        <input name="team_one" placeholder="مثال: فريق الصقور" required>
      </label>
      <label>الفريق الثاني
        <input name="team_two" placeholder="مثال: فريق النجوم" required>
      </label>
    </div>
    <div class="helpers">
      <b>لكل فريق وسائل مساعدة:</b>
      <span>🔄 تبديل السؤال</span>
      <span>📞 اتصال بصديق</span>
      <span>✌️ إجابتان</span>
    </div>
    <button class="btn btn--primary btn--lg" type="submit">ابدأ اللعب</button>
  </form>
</section>
</x-layouts.app>
