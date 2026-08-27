<x-layouts.app title="تجهيز اللعبة — سوالف">
<div
  class="setup-page-wrapper"
>
  <!-- Blurred Category Show Background -->
  <div class="setup-page-bg">
    <section class="page-hero category-show-hero">
      <div class="container category-show-wrap">
        <div class="cat-circle cat-circle--lg" style="background: linear-gradient(135deg, #0F6B4C, #084A34)">
          <div class="cat-circle__ring">
            @if($category->imageUrl())
              <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}" data-no-sw-img>
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
    <form
      class="setup-modal-card"
      method="POST"
      action="{{ route('game.start') }}"
      @if(!empty($aboutToClaimFree))
        data-free-start-confirm="1"
        data-free-start-message="هذي فئتك المجانية الوحيدة. بعد ما تبدأ ما تقدر تلعب فئة ثانية إلا بالاشتراك. متأكد تبي تبدأ؟"
      @endif
    >
      @csrf
      <input type="hidden" name="category_id" value="{{ $category->id }}">
      <input type="hidden" name="name" value="تحدي {{ $category->name_ar }}">

      <a href="{{ route('categories.show', $category) }}" class="setup-modal-close" title="إغلاق">✕</a>

      <x-back-button :href="route('categories.show', $category)" label="رجوع للفئة" />
      <h1 class="setup-modal-title">{{ $category->name_ar }}</h1>
      <p class="setup-modal-sub">حدد معلومات الفرق</p>

      <div class="setup-modal-cols">
        <!-- Team 1 -->
        <div class="setup-modal-col">
          <h3>الفريق الأول</h3>
          <input name="team_one" class="setup-modal-input" placeholder="اكتب اسم الفريق الأول…" value="{{ old('team_one') }}" required maxlength="50" autocomplete="off">
          @error('team_one')<small class="error" style="display:block;margin-top:6px;color:#FF1744;font-weight:700">{{ $message }}</small>@enderror
          <div class="setup-modal-counter">
            <button type="button" class="counter-btn minus">—</button>
            <span class="counter-val">1</span>
            <button type="button" class="counter-btn plus">+</button>
          </div>

          <div style="margin-top:14px;text-align:right">
            <div class="helpers-sub-title" style="margin-bottom:8px;font-weight:800;font-size:.85rem;color:var(--theme-muted,#6C7799)">اختر شخصية الفريق 1 <span style="color:#FF1744">*</span></div>
            <input type="hidden" name="team_one_character_id" id="setupTeamOneCharId" value="{{ old('team_one_character_id') }}" required>
            <div class="cg-char-grid" data-char-team="one" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(72px,1fr));gap:8px">
              @foreach(($characters ?? collect()) as $char)
                <button type="button" class="cg-char-btn {{ (string) old('team_one_character_id') === (string) $char->id ? 'is-active' : '' }}"
                  data-char-id="{{ $char->id }}" data-char-for="one"
                  style="border:2px solid {{ (string) old('team_one_character_id') === (string) $char->id ? '#FF6D00' : 'rgba(11,18,32,.1)' }};border-radius:14px;padding:8px 4px;background:#fff;cursor:pointer;font-weight:800;font-size:.75rem;color:inherit">
                  @if($char->imageUrl())
                    <img src="{{ $char->imageUrl() }}" alt="{{ $char->name_ar }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;display:block;margin:0 auto 4px" width="40" height="40">
                  @else
                    <span style="display:block;font-size:1.4rem;margin-bottom:2px;line-height:1.2">{{ $char->icon ?: '🧑' }}</span>
                  @endif
                  {{ $char->name_ar }}
                </button>
              @endforeach
            </div>
            @error('team_one_character_id')<small class="error" style="display:block;margin-top:6px;color:#FF1744;font-weight:700">{{ $message }}</small>@enderror
          </div>
        </div>

        <!-- Team 2 -->
        <div class="setup-modal-col">
          <h3>الفريق الثاني</h3>
          <input name="team_two" class="setup-modal-input" placeholder="اكتب اسم الفريق الثاني…" value="{{ old('team_two') }}" required maxlength="50" autocomplete="off">
          @error('team_two')<small class="error" style="display:block;margin-top:6px;color:#FF1744;font-weight:700">{{ $message }}</small>@enderror
          <div class="setup-modal-counter">
            <button type="button" class="counter-btn minus">—</button>
            <span class="counter-val">1</span>
            <button type="button" class="counter-btn plus">+</button>
          </div>

          <div style="margin-top:14px;text-align:right">
            <div class="helpers-sub-title" style="margin-bottom:8px;font-weight:800;font-size:.85rem;color:var(--theme-muted,#6C7799)">اختر شخصية الفريق 2 <span style="color:#FF1744">*</span></div>
            <input type="hidden" name="team_two_character_id" id="setupTeamTwoCharId" value="{{ old('team_two_character_id') }}" required>
            <div class="cg-char-grid" data-char-team="two" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(72px,1fr));gap:8px">
              @foreach(($characters ?? collect()) as $char)
                <button type="button" class="cg-char-btn {{ (string) old('team_two_character_id') === (string) $char->id ? 'is-active' : '' }}"
                  data-char-id="{{ $char->id }}" data-char-for="two"
                  style="border:2px solid {{ (string) old('team_two_character_id') === (string) $char->id ? '#FF6D00' : 'rgba(11,18,32,.1)' }};border-radius:14px;padding:8px 4px;background:#fff;cursor:pointer;font-weight:800;font-size:.75rem;color:inherit">
                  @if($char->imageUrl())
                    <img src="{{ $char->imageUrl() }}" alt="{{ $char->name_ar }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;display:block;margin:0 auto 4px" width="40" height="40">
                  @else
                    <span style="display:block;font-size:1.4rem;margin-bottom:2px;line-height:1.2">{{ $char->icon ?: '🧑' }}</span>
                  @endif
                  {{ $char->name_ar }}
                </button>
              @endforeach
            </div>
            @error('team_two_character_id')<small class="error" style="display:block;margin-top:6px;color:#FF1744;font-weight:700">{{ $message }}</small>@enderror
          </div>
        </div>
      </div>

      <button class="setup-modal-submit" type="submit">ابدأ اللعب</button>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const setupForm = document.querySelector('.setup-modal-card');
    const playMeta = @json($categoryPlayMeta ?? ['total' => 0, 'remaining' => 0]);

    document.querySelectorAll('.cg-char-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const forTeam = btn.dataset.charFor;
        const id = btn.dataset.charId;
        const input = forTeam === 'one'
          ? document.getElementById('setupTeamOneCharId')
          : document.getElementById('setupTeamTwoCharId');
        if (input) input.value = id;

        document.querySelectorAll('.cg-char-btn[data-char-for="' + forTeam + '"]').forEach((b) => {
          b.classList.remove('is-active');
          b.style.borderColor = 'rgba(11,18,32,.1)';
        });
        btn.classList.add('is-active');
        btn.style.borderColor = '#FF6D00';
        if (typeof window.playSound === 'function') window.playSound('select');
      });
    });

    if (setupForm) {
      setupForm.addEventListener('submit', async (e) => {
        const total = parseInt(playMeta.total, 10);
        const t1 = (setupForm.querySelector('input[name="team_one"]')?.value || '').trim();
        const t2 = (setupForm.querySelector('input[name="team_two"]')?.value || '').trim();
        const c1 = document.getElementById('setupTeamOneCharId')?.value;
        const c2 = document.getElementById('setupTeamTwoCharId')?.value;

        const popup = async (msg) => {
          if (typeof window.showPopup === 'function') {
            await window.showPopup(msg, 'error');
          } else {
            alert(msg.replace(/<br\s*\/?>/gi, '\n'));
          }
        };

        if (!t1 || !t2) {
          e.preventDefault();
          await popup('اكتب اسم الفريق الأول والثاني قبل البدء 👥');
          return;
        }

        if (t1 === t2) {
          e.preventDefault();
          await popup('اسم الفريقين لازم يكون مختلف');
          return;
        }

        if (!c1 || !c2) {
          e.preventDefault();
          await popup('اختر شخصية لكل فريق 🎭');
          return;
        }

        if (c1 === c2) {
          e.preventDefault();
          await popup('كل فريق لازم يختار شخصية مختلفة');
          return;
        }

        if (Number.isFinite(total) && total <= 0) {
          e.preventDefault();
          await popup('هالفئة فاضية الحين 🎯<br>بنضيف لها أسئلة قريب — ارجع لها بعدين وتقدر تلعب!');
          return;
        }
      });
    }

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
