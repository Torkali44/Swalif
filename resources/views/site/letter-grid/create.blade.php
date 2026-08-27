<x-layouts.app title="تجهيز شبكة الحروف — سوالف">
<div class="letter-grid-create-page">

  <!-- Curved Hero Header -->
  <section class="hex-setup-hero">
    <div class="hex-setup-hero__inner">
      <span class="hex-setup-badge">⬡ شبكة الحروف</span>
      <h1 class="hex-setup-title">جهّز الفرق وابدأ التحدي</h1>
      <p class="hex-setup-subtitle">
        تنافس ذكاء وسرعة بين فريقين — كل إجابة صحيحة تمنح فريقك الحرف لتلوين الشبكة!
      </p>
    </div>
  </section>

  <main class="hex-setup-container">
    @if($grids->isEmpty())
      <div class="hex-setup-empty">
        <div class="hex-setup-empty__icon">⬡</div>
        <h3>لا توجد شبكات حروف جاهزة حالياً</h3>
        <p>سيتم إضافة شبكات وتحديات جديدة قريباً، يمكنك تجربة باقي الألعاب المميزة الآن.</p>
        <a href="{{ route('categories.index') }}" class="btn btn--primary">🎮 تصفح الألعاب</a>
      </div>
    @else
      <form method="POST" action="{{ route('letter-grid.store') }}" class="hex-setup-form" id="letterGridSetupForm">
        @csrf

        {{-- Section 1: Choose Grid --}}
        <section class="hex-setup-section">
          <div class="hex-section-head">
            <span class="hex-step-num">1</span>
            <div>
              <h2 class="hex-section-title">اختر شبكة الحروف</h2>
              <p class="hex-section-desc">اختر التخصص والشبكة التي ترغب في التنافس عليها</p>
            </div>
          </div>

          <div class="hex-game-name-field" style="margin-bottom:18px;display:none" hidden>
            <input type="hidden" id="letter_grid_game_name" name="name" value="">
          </div>

          <div class="hex-grid-cards">
            @foreach($grids as $index => $grid)
              @php
                $preselect = old('letter_grid_id', request('grid', $grids->first()->id));
                $isSelected = (string) $preselect === (string) $grid->id;
              @endphp
              <label class="hex-grid-card {{ $isSelected ? 'is-selected' : '' }}">
                <input type="radio" name="letter_grid_id" value="{{ $grid->id }}" @checked($isSelected) required>
                <div class="hex-grid-card__inner">
                  <div class="hex-grid-card__media">
                    @if($grid->imageUrl())
                      <img src="{{ $grid->imageUrl() }}" alt="{{ $grid->name_ar }}" class="hex-grid-card__img" loading="lazy" decoding="async">
                    @else
                      <div class="hex-grid-card__fallback">⬡</div>
                    @endif
                    <span class="hex-count-badge hex-count-badge--overlay">{{ $grid->playable_cells_count }} حرف</span>
                  </div>
                  <div class="hex-grid-card__body">
                    <span class="hex-topic-badge">⬡ شبكة</span>
                    <h3 class="hex-grid-card__name">{{ $grid->name_ar }}</h3>
                    @if($grid->description)
                      <p class="hex-grid-card__desc">{{ \Illuminate\Support\Str::limit($grid->description, 80) }}</p>
                    @else
                      <p class="hex-grid-card__desc">تحدي حروف عربي سداسي ممتع وشيق</p>
                    @endif
                  </div>
                </div>
              </label>
            @endforeach
          </div>
          @error('letter_grid_id')
            <p class="hex-form-error">{{ $message }}</p>
          @enderror
        </section>

        {{-- Section 2: Setup Teams & Characters --}}
        <section class="hex-setup-section">
          <div class="hex-section-head">
            <span class="hex-step-num">2</span>
            <div>
              <h2 class="hex-section-title">تجهيز الفرق والشخصيات</h2>
              <p class="hex-section-desc">حدد اسم كل فريق واختر الشخصية (الأفاتار) الممثلة له</p>
            </div>
          </div>

          <div class="hex-teams-grid">
            {{-- Team 1 Card (Red / Fire) --}}
            <div class="hex-team-box hex-team-box--one">
              <div class="hex-team-box__header">
                <span class="hex-team-box__tag hex-team-box__tag--red">🔥 الفريق الأول</span>
              </div>

              <input type="hidden" name="team_one" id="team_one_name" value="{{ old('team_one') }}">
              <input type="hidden" name="team_one_character_id" id="team_one_character_id" value="{{ old('team_one_character_id') }}" required>

              <div class="hex-team-preview" id="teamOnePreview" style="padding:10px 14px;border-radius:14px;background:rgba(255,23,68,.08);border:1.5px dashed #FF1744;text-align:center;font-weight:900;color:#FF1744">
                <span id="teamOneSelectedName">{{ old('team_one') ?: 'اختر شخصية الفريق الأول' }}</span>
              </div>
              @error('team_one')
                <small class="hex-field-error">{{ $message }}</small>
              @enderror

              {{-- Character Selector Team 1 --}}
              <div class="hex-char-picker">
                <label class="hex-input-label">اختر شخصية الفريق الأول <span style="color:#FF1744">*</span></label>

                <div class="hex-char-grid" role="radiogroup" aria-label="شخصية الفريق الأول">
                  @foreach($characters as $cIndex => $char)
                    @php
                      $cSelected = (string) old('team_one_character_id') === (string) $char->id;
                    @endphp
                    <button
                      type="button"
                      class="hex-char-item {{ $cSelected ? 'is-active' : '' }}"
                      data-char-btn="one"
                      data-char-id="{{ $char->id }}"
                      data-char-name="{{ $char->name_ar }}"
                      title="{{ $char->name_ar }}"
                      aria-label="{{ $char->name_ar }}"
                    >
                      <span class="hex-char-avatar" style="background: {{ $char->accentGradient() }}">
                        @if($char->imageUrl())
                          <img src="{{ $char->imageUrl() }}" alt="{{ $char->name_ar }}" loading="lazy" decoding="async">
                        @else
                          <span>{{ $char->icon ?: '🧑' }}</span>
                        @endif
                      </span>
                      <span class="hex-char-name">{{ $char->name_ar }}</span>
                    </button>
                  @endforeach
                </div>
                @error('team_one_character_id')
                  <small class="hex-field-error">{{ $message }}</small>
                @enderror
              </div>
            </div>

            {{-- Team 2 Card (Gold / Yellow) --}}
            <div class="hex-team-box hex-team-box--two">
              <div class="hex-team-box__header">
                <span class="hex-team-box__tag hex-team-box__tag--gold">⭐ الفريق الثاني</span>
              </div>

              <input type="hidden" name="team_two" id="team_two_name" value="{{ old('team_two') }}">
              <input type="hidden" name="team_two_character_id" id="team_two_character_id" value="{{ old('team_two_character_id') }}" required>

              <div class="hex-team-preview" id="teamTwoPreview" style="padding:10px 14px;border-radius:14px;background:rgba(255,179,0,.12);border:1.5px dashed #FFB300;text-align:center;font-weight:900;color:#E65100">
                <span id="teamTwoSelectedName">{{ old('team_two') ?: 'اختر شخصية الفريق الثاني' }}</span>
              </div>
              @error('team_two')
                <small class="hex-field-error">{{ $message }}</small>
              @enderror

              {{-- Character Selector Team 2 --}}
              <div class="hex-char-picker">
                <label class="hex-input-label">اختر شخصية الفريق الثاني <span style="color:#FF1744">*</span></label>

                <div class="hex-char-grid" role="radiogroup" aria-label="شخصية الفريق الثاني">
                  @foreach($characters as $cIndex => $char)
                    @php
                      $cSelected = (string) old('team_two_character_id') === (string) $char->id;
                    @endphp
                    <button
                      type="button"
                      class="hex-char-item {{ $cSelected ? 'is-active' : '' }}"
                      data-char-btn="two"
                      data-char-id="{{ $char->id }}"
                      data-char-name="{{ $char->name_ar }}"
                      title="{{ $char->name_ar }}"
                      aria-label="{{ $char->name_ar }}"
                    >
                      <span class="hex-char-avatar" style="background: {{ $char->accentGradient() }}">
                        @if($char->imageUrl())
                          <img src="{{ $char->imageUrl() }}" alt="{{ $char->name_ar }}" loading="lazy" decoding="async">
                        @else
                          <span>{{ $char->icon ?: '🧑' }}</span>
                        @endif
                      </span>
                      <span class="hex-char-name">{{ $char->name_ar }}</span>
                    </button>
                  @endforeach
                </div>
                @error('team_two_character_id')
                  <small class="hex-field-error">{{ $message }}</small>
                @enderror
              </div>
            </div>
          </div>
        </section>

        {{-- Submit Button --}}
        <div class="hex-submit-wrap">
          <button type="submit" class="hex-start-btn">
            <span>🚀</span>
            <span>ابدأ التحدي الآن</span>
          </button>
          <p class="hex-submit-hint">كل إجابة صحيحة تمنح فريقك السيطرة على الحرف</p>
        </div>
      </form>
    @endif
  </main>
</div>

<style>
/* ══════════════════════════════════════════════════════════
   Letter Grid Create Setup Page - Clean Responsive Design
══════════════════════════════════════════════════════════ */
.letter-grid-create-page {
  direction: rtl;
  min-height: 100vh;
  padding-bottom: 90px;
  background: var(--bg-alt, #FFF8EF);
  color: var(--ink, #0B1220);
  overflow-x: hidden;
}

body.dark .letter-grid-create-page,
html.dark .letter-grid-create-page {
  background: #0B1020 !important;
  color: #F8FAFC !important;
}

/* Curved Hero Banner */
.hex-setup-hero {
  background: linear-gradient(135deg, #FF6D00 0%, #FF1744 55%, #7C3AED 100%);
  padding: 40px 16px 56px;
  text-align: center;
  color: #fff;
  border-bottom-left-radius: 50% 26px;
  border-bottom-right-radius: 50% 26px;
  box-shadow: 0 10px 30px rgba(255, 109, 0, 0.22);
  margin-bottom: 30px;
}

body.dark .hex-setup-hero,
html.dark .hex-setup-hero {
  background: linear-gradient(135deg, #E65100 0%, #C2185B 50%, #4C1D95 100%);
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45);
}

.hex-setup-hero__inner {
  max-width: 780px;
  margin: 0 auto;
}

.hex-setup-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 18px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.22);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.35);
  font-weight: 900;
  font-size: 0.88rem;
  margin-bottom: 10px;
}

.hex-setup-title {
  font-family: 'Cairo', sans-serif;
  font-size: clamp(1.8rem, 4vw, 2.6rem);
  font-weight: 900;
  margin: 0 0 8px;
  color: #fff !important;
  text-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
}

.hex-setup-subtitle {
  font-size: 1rem;
  opacity: 0.95;
  color: #fff !important;
  line-height: 1.55;
  margin: 0 auto;
}

/* Container */
.hex-setup-container {
  max-width: 980px;
  margin: 0 auto;
  padding: 0 16px;
  box-sizing: border-box;
}

.hex-setup-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Sections */
.hex-setup-section {
  background: #fff;
  border: 1.5px solid rgba(11, 18, 32, 0.08);
  border-radius: 22px;
  padding: 24px 20px;
  box-shadow: 0 6px 24px rgba(15, 23, 42, 0.05);
  box-sizing: border-box;
  overflow: hidden;
}

body.dark .hex-setup-section,
html.dark .hex-setup-section {
  background: #151B32;
  border-color: rgba(255, 255, 255, 0.1);
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25);
}

.hex-section-head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(11, 18, 32, 0.06);
}

body.dark .hex-section-head,
html.dark .hex-section-head {
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

.hex-step-num {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: linear-gradient(135deg, #FF6D00, #FF1744);
  color: #fff;
  font-family: 'Cairo', sans-serif;
  font-size: 1.15rem;
  font-weight: 900;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.hex-section-title {
  font-family: 'Cairo', sans-serif;
  font-size: 1.2rem;
  font-weight: 900;
  margin: 0;
  color: var(--ink, #0B1220);
}

body.dark .hex-section-title,
html.dark .hex-section-title {
  color: #F8FAFC;
}

.hex-section-desc {
  font-size: 0.84rem;
  color: var(--muted, #6C7799);
  margin: 2px 0 0;
  font-weight: 600;
}

/* Grid Selection Cards */
.hex-grid-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 14px;
}

.hex-grid-card {
  position: relative;
  cursor: pointer;
}

.hex-grid-card input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.hex-grid-card__inner {
  display: flex;
  flex-direction: column;
  gap: 0;
  padding: 0;
  border-radius: 18px;
  background: #fff;
  border: 2px solid rgba(11, 18, 32, 0.08);
  transition: all 0.2s ease;
  height: 100%;
  box-sizing: border-box;
  overflow: hidden;
}

body.dark .hex-grid-card__inner,
html.dark .hex-grid-card__inner {
  background: #151B32;
  border-color: rgba(255, 255, 255, 0.08);
}

.hex-grid-card:hover .hex-grid-card__inner {
  transform: translateY(-2px);
  border-color: rgba(255, 109, 0, 0.4);
}

.hex-grid-card input:checked + .hex-grid-card__inner,
.hex-grid-card.is-selected .hex-grid-card__inner {
  border-color: #FF6D00;
  box-shadow: 0 8px 24px rgba(255, 109, 0, 0.18);
}

.hex-grid-card__media {
  position: relative;
  width: 100%;
  height: 160px;
  background: linear-gradient(135deg, #FFB300, #FF6D00);
  overflow: hidden;
}

.hex-grid-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.hex-grid-card__fallback {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
  font-size: 3rem;
  color: #fff;
}

.hex-count-badge--overlay {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(255, 255, 255, 0.92) !important;
  color: #0B1220 !important;
  box-shadow: 0 4px 12px rgba(0,0,0,.12);
}

.hex-grid-card__body {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 14px 16px 16px;
}

.hex-grid-card__badge-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.hex-topic-badge {
  font-size: 0.74rem;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 999px;
  background: rgba(255, 109, 0, 0.12);
  color: #FF6D00;
  width: fit-content;
}

.hex-count-badge {
  font-size: 0.76rem;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 999px;
  background: rgba(11, 18, 32, 0.06);
  color: var(--ink, #0B1220);
}

body.dark .hex-count-badge,
html.dark .hex-count-badge {
  background: rgba(255, 255, 255, 0.1);
  color: #F8FAFC;
}

.hex-grid-card__icon-wrap {
  display: none;
}

.hex-grid-card__name {
  font-family: 'Cairo', sans-serif;
  font-size: 1.05rem;
  font-weight: 900;
  margin: 0;
  color: var(--ink, #0B1220);
}

body.dark .hex-grid-card__name,
html.dark .hex-grid-card__name {
  color: #F8FAFC;
}

.hex-grid-card__desc {
  font-size: 0.82rem;
  color: var(--muted, #6C7799);
  line-height: 1.4;
  margin: 0;
}

/* Teams & Characters Grid */
.hex-teams-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
  width: 100%;
  box-sizing: border-box;
}

.hex-team-box {
  padding: 18px 16px;
  border-radius: 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  border: 1.5px solid transparent;
  box-sizing: border-box;
  overflow: hidden;
}

.hex-team-box--one {
  background: linear-gradient(180deg, rgba(255, 23, 68, 0.05) 0%, rgba(255, 109, 0, 0.02) 100%);
  border-color: rgba(255, 23, 68, 0.2);
}

.hex-team-box--two {
  background: linear-gradient(180deg, rgba(255, 179, 0, 0.08) 0%, rgba(244, 200, 66, 0.03) 100%);
  border-color: rgba(255, 179, 0, 0.3);
}

body.dark .hex-team-box--one,
html.dark .hex-team-box--one {
  background: linear-gradient(180deg, rgba(255, 23, 68, 0.12) 0%, rgba(255, 109, 0, 0.04) 100%);
  border-color: rgba(255, 23, 68, 0.35);
}

body.dark .hex-team-box--two,
html.dark .hex-team-box--two {
  background: linear-gradient(180deg, rgba(255, 179, 0, 0.15) 0%, rgba(244, 200, 66, 0.05) 100%);
  border-color: rgba(255, 179, 0, 0.4);
}

.hex-team-box__header {
  display: flex;
  align-items: center;
}

.hex-team-box__tag {
  font-family: 'Cairo', sans-serif;
  font-size: 0.92rem;
  font-weight: 900;
  padding: 4px 12px;
  border-radius: 999px;
}

.hex-team-box__tag--red {
  background: rgba(255, 23, 68, 0.12);
  color: #FF1744;
}

.hex-team-box__tag--gold {
  background: rgba(255, 179, 0, 0.16);
  color: #E65100;
}

body.dark .hex-team-box__tag--gold,
html.dark .hex-team-box__tag--gold {
  color: #FFB300;
}

.hex-team-input-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.hex-input-label {
  font-size: 0.84rem;
  font-weight: 800;
  color: var(--ink, #0B1220);
}

body.dark .hex-input-label,
html.dark .hex-input-label {
  color: #F8FAFC;
}

.hex-text-input {
  width: 100%;
  padding: 11px 14px;
  border-radius: 12px;
  border: 1.5px solid rgba(11, 18, 32, 0.12);
  background: #fff;
  font-family: 'Cairo', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--ink, #0B1220);
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}

body.dark .hex-text-input,
html.dark .hex-text-input {
  background: #0B1020;
  border-color: rgba(255, 255, 255, 0.15);
  color: #F8FAFC;
}

.hex-text-input:focus {
  border-color: #FF6D00;
  box-shadow: 0 0 0 3px rgba(255, 109, 0, 0.15);
}

/* Character Picker Grid inside Team Box */
.hex-char-picker {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.hex-char-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(54px, 1fr));
  gap: 8px;
  max-height: 180px;
  overflow-y: auto;
  padding: 4px;
  scrollbar-width: thin;
}

.hex-char-grid::-webkit-scrollbar {
  width: 5px;
}

.hex-char-grid::-webkit-scrollbar-thumb {
  background: rgba(11, 18, 32, 0.15);
  border-radius: 10px;
}

.hex-char-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px 2px;
  border-radius: 12px;
  transition: transform 0.15s ease;
  min-width: 0;
  outline: none;
}

.hex-char-item:hover {
  transform: translateY(-2px);
}

.hex-char-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 1.25rem;
  border: 2.5px solid transparent;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.hex-char-avatar img {
  width: 44px !important;
  height: 44px !important;
  max-width: 44px !important;
  max-height: 44px !important;
  object-fit: cover !important;
  display: block !important;
  border-radius: 50% !important;
}

.hex-char-name {
  font-size: 0.72rem;
  font-weight: 800;
  color: var(--muted, #6C7799);
  white-space: nowrap;
  max-width: 52px;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.1;
}

.hex-char-item.is-active .hex-char-avatar {
  border-color: #FF6D00;
  box-shadow: 0 0 0 3px rgba(255, 109, 0, 0.45);
  transform: scale(1.08);
}

.hex-char-item.is-active .hex-char-name {
  color: #FF6D00;
  font-weight: 900;
}

body.dark .hex-char-item.is-active .hex-char-name,
html.dark .hex-char-item.is-active .hex-char-name {
  color: #FFB300;
}

/* Submit Area */
.hex-submit-wrap {
  text-align: center;
  margin-top: 6px;
}

.hex-start-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 15px 44px;
  border-radius: 999px;
  background: linear-gradient(135deg, #FFB300 0%, #FF6D00 45%, #FF1744 100%);
  color: #fff;
  font-family: 'Cairo', sans-serif;
  font-size: 1.2rem;
  font-weight: 900;
  border: none;
  cursor: pointer;
  box-shadow: 0 10px 28px rgba(255, 23, 68, 0.35);
  transition: all 0.22s ease;
}

.hex-start-btn:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 14px 34px rgba(255, 23, 68, 0.5);
}

.hex-submit-hint {
  font-size: 0.84rem;
  color: var(--muted, #6C7799);
  font-weight: 700;
  margin-top: 10px;
}

.hex-form-error,
.hex-field-error {
  color: #FF1744;
  font-size: 0.8rem;
  font-weight: 800;
  margin-top: 4px;
}

/* Empty State */
.hex-setup-empty {
  text-align: center;
  padding: 50px 20px;
  background: #fff;
  border-radius: 20px;
}

body.dark .hex-setup-empty,
html.dark .hex-setup-empty {
  background: #151B32;
}

.hex-setup-empty__icon {
  font-size: 3.5rem;
  color: #FF6D00;
  margin-bottom: 10px;
}

@media (max-width: 768px) {
  .hex-setup-hero { padding: 28px 16px 36px; }
  .hex-setup-title { font-size: 1.45rem; }
  .hex-setup-subtitle { font-size: 0.9rem; }
  .hex-setup-container { padding: 16px 12px 40px; }
  .hex-setup-section { padding: 16px 14px; border-radius: 18px; }
  .hex-grid-cards { grid-template-columns: 1fr; gap: 12px; }
  .hex-teams-grid { grid-template-columns: 1fr; gap: 14px; }
  .hex-start-btn { width: 100%; padding: 14px 20px; font-size: 1.05rem; }
  .hex-char-grid { grid-template-columns: repeat(auto-fill, minmax(64px, 1fr)) !important; }
}

@media (max-width: 420px) {
  .hex-setup-badge { font-size: 0.78rem; }
  .hex-section-title { font-size: 1.05rem; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Grid card selection visual update
  const gridCards = document.querySelectorAll('.hex-grid-card');
  gridCards.forEach((card) => {
    const radio = card.querySelector('input[type="radio"]');
    if (!radio) return;

    radio.addEventListener('change', () => {
      gridCards.forEach((c) => c.classList.remove('is-selected'));
      if (radio.checked) {
        card.classList.add('is-selected');
      }
    });
  });

  // Character Picker for Team 1
  const teamOneInput = document.getElementById('team_one_character_id');
  const teamOneNameInput = document.getElementById('team_one_name');
  const teamOnePreview = document.getElementById('teamOneSelectedName');
  const teamOneBtns = document.querySelectorAll('[data-char-btn="one"]');

  teamOneBtns.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const charId = btn.dataset.charId;
      const charName = btn.dataset.charName;
      if (teamOneInput) teamOneInput.value = charId;
      if (teamOneNameInput) teamOneNameInput.value = charName;
      if (teamOnePreview) teamOnePreview.textContent = charName;
      teamOneBtns.forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');
    });
  });

  // Character Picker for Team 2
  const teamTwoInput = document.getElementById('team_two_character_id');
  const teamTwoNameInput = document.getElementById('team_two_name');
  const teamTwoPreview = document.getElementById('teamTwoSelectedName');
  const teamTwoBtns = document.querySelectorAll('[data-char-btn="two"]');

  teamTwoBtns.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const charId = btn.dataset.charId;
      const charName = btn.dataset.charName;
      if (teamTwoInput) teamTwoInput.value = charId;
      if (teamTwoNameInput) teamTwoNameInput.value = charName;
      if (teamTwoPreview) teamTwoPreview.textContent = charName;
      teamTwoBtns.forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');
    });
  });

  document.getElementById('letterGridSetupForm')?.addEventListener('submit', async (e) => {
    const c1 = teamOneInput?.value || '';
    const c2 = teamTwoInput?.value || '';

    const popup = (msg) => {
      if (typeof window.showPopup === 'function') return window.showPopup(msg, 'error');
      alert(msg);
    };

    if (!c1 || !c2) {
      e.preventDefault();
      await popup('اختر شخصية لكل فريق قبل البدء 🎭');
      return;
    }
    if (c1 === c2) {
      e.preventDefault();
      await popup('كل فريق لازم يختار شخصية مختلفة.');
      return;
    }
  });
});
</script>
</x-layouts.app>