import './bootstrap';

/* Timer (simple text) */
document.querySelectorAll('[data-timer]').forEach((timer) => {
  let remaining = Number(timer.dataset.timer);
  const interval = window.setInterval(() => {
    remaining -= 1;
    timer.textContent = Math.max(remaining, 0);
    if (remaining <= 0) {
      window.clearInterval(interval);
      timer.classList.add('expired');
    }
  }, 1000);
});

/* Timer ring (game play design) */
document.querySelectorAll('[data-timer-ring]').forEach((timerEl) => {
  const total = Number(timerEl.dataset.timerRing) || 30;
  const circum = 2 * Math.PI * 52;
  const bar = timerEl.querySelector('.timer__bar');
  const val = timerEl.querySelector('.timer__value b, #timerValue');
  if (!bar || !val) return;

  bar.style.strokeDasharray = String(circum);
  let remaining = total;

  const interval = window.setInterval(() => {
    remaining -= 1;
    val.textContent = String(Math.max(remaining, 0));
    bar.style.strokeDashoffset = String(circum * (1 - Math.max(remaining, 0) / total));
    if (remaining <= 5) timerEl.classList.add('warn');
    if (remaining <= 0) {
      window.clearInterval(interval);
      timerEl.classList.add('expired');
      window.showPopup('انتهى وقت الإجابة!', 'error');
    }
  }, 1000);
});

/* Mobile nav */
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');
if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    navLinks.classList.toggle('is-open');
  });
}

/* Home category filter pills */
(() => {
  const homeRoot = document.querySelector('.filters__row') || document.querySelector('section.filters');
  if (!homeRoot || homeRoot.closest('.categories-design')) return;

  const pills = homeRoot.querySelectorAll('.pill[data-filter]');
  const cats = document.querySelectorAll('.cat-circle[data-cat], .cat-circle[data-group], .cat[data-cat], .cat[data-group]');

  pills.forEach((pill) => {
    pill.addEventListener('click', () => {
      pills.forEach((p) => p.classList.remove('is-active', 'active'));
      pill.classList.add('is-active');
      const filter = pill.dataset.filter || 'all';

      cats.forEach((cat) => {
        const group = cat.dataset.group || '';
        const tag = cat.dataset.cat || '';
        let match = filter === 'all';
        if (filter === 'uae') match = group === 'uae' || tag === 'uae';
        else if (filter === 'general') match = group === 'general' || tag === 'general';
        else if (filter === 'sports' || filter === 'sport') match = tag === 'sports' || tag === 'sport';
        else match = tag === filter;
        cat.classList.toggle('is-hidden', !match);
        if (match) {
          cat.style.opacity = '1';
          cat.style.transform = 'translateY(0)';
        }
      });
    });
  });
})();

/* Normalize Arabic text so search ignores diacritics & letter variants */
const normalizeAr = (value) => (value || '')
  .toString()
  .toLowerCase()
  .replace(/[\u064B-\u065F\u0670]/g, '') // tashkeel/diacritics
  .replace(/\u0640/g, '')                 // tatweel
  .replace(/[\u0622\u0623\u0625\u0671]/g, '\u0627') // آ أ إ ٱ -> ا
  .replace(/\u0649/g, '\u064A')           // ى -> ي
  .replace(/\u0629/g, '\u0647')           // ة -> ه
  .replace(/\u0624/g, '\u0648')           // ؤ -> و
  .replace(/\u0626/g, '\u064A')           // ئ -> ي
  .replace(/\s+/g, ' ')
  .trim();

/* Categories page — search + filters */
(() => {
  const root = document.querySelector('.categories-design');
  if (!root) return;

  const cards = [...root.querySelectorAll('.card')];
  const grid = root.querySelector('#categoryGrid');
  const empty = root.querySelector('#categoryEmpty');
  const search = root.querySelector('#categorySearch');
  const filters = root.querySelector('#categoryFilters');
  const sortSelect = root.querySelector('#categorySort');
  let activeFilter = 'all';
  let activeSort = 'popular';
  let term = '';

  const apply = () => {
    let visible = 0;
    cards.forEach((card) => {
      const filter = card.dataset.filter;
      const group = card.dataset.group;
      const name = normalizeAr(card.dataset.name);
      let match = activeFilter === 'all'
        || filter === activeFilter
        || group === activeFilter;
      if (term && !name.includes(term)) match = false;
      card.classList.toggle('is-hidden', !match);
      if (match) {
        visible += 1;
        card.style.opacity = '1';
        card.style.transform = 'none';
      }
    });

    const sorted = [...cards].sort((a, b) => {
      if (activeSort === 'questions') {
        return Number(b.dataset.questions || 0) - Number(a.dataset.questions || 0);
      }
      if (activeSort === 'new') {
        return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
      }
      if (activeSort === 'az') {
        const nameA = a.dataset.name || '';
        const nameB = b.dataset.name || '';
        return nameA.localeCompare(nameB, 'ar');
      }
      // 'popular' (by sort_order)
      return Number(a.dataset.order || 0) - Number(b.dataset.order || 0);
    });

    if (grid) {
      sorted.forEach(card => grid.appendChild(card));
    }

    if (empty) empty.hidden = visible > 0;
  };

  filters?.addEventListener('click', (e) => {
    const btn = e.target.closest('.pill');
    if (!btn) return;
    filters.querySelectorAll('.pill').forEach((p) => p.classList.remove('active', 'is-active'));
    btn.classList.add('active');
    activeFilter = btn.dataset.filter || 'all';
    apply();
  });

  search?.addEventListener('input', (e) => {
    term = normalizeAr(e.target.value);
    apply();
  });

  sortSelect?.addEventListener('change', (e) => {
    activeSort = e.target.value;
    apply();
  });
})();

/* Smooth scroll */
document.querySelectorAll('a[href^="#"]').forEach((a) => {
  a.addEventListener('click', (e) => {
    const id = a.getAttribute('href');
    if (id && id.length > 1) {
      const el = document.querySelector(id);
      if (el) {
        e.preventDefault();
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        navLinks?.classList.remove('is-open');
      }
    }
  });
});

/* Home — daily challenge countdown */
(() => {
  const el = document.querySelector('.hp-countdown[data-countdown]');
  if (!el) return;
  const h = el.querySelector('[data-cd="h"]');
  const m = el.querySelector('[data-cd="m"]');
  const s = el.querySelector('[data-cd="s"]');
  let remaining = Math.max(0, parseInt(el.dataset.countdown, 10) || 0);
  const pad = (n) => String(n).padStart(2, '0');

  const tick = () => {
    const hrs = Math.floor(remaining / 3600);
    const mins = Math.floor((remaining % 3600) / 60);
    const secs = remaining % 60;
    if (h) h.textContent = pad(hrs);
    if (m) m.textContent = pad(mins);
    if (s) s.textContent = pad(secs);
    if (remaining > 0) remaining -= 1;
  };

  tick();
  setInterval(tick, 1000);
})();

/* Assign points — set team_id then submit once */
const assignForm = document.getElementById('assignForm');
if (assignForm) {
  const teamInput = document.getElementById('assignTeamId');
  let submitting = false;

  assignForm.querySelectorAll('.assign-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (submitting) return;
      submitting = true;
      teamInput.value = btn.dataset.teamId ?? '';
      assignForm.querySelectorAll('.assign-btn').forEach((b) => {
        b.disabled = true;
      });
      assignForm.submit();
    });
  });
}

/* Result confetti */
(() => {
  const canvas = document.getElementById('confetti');
  if (!canvas || !canvas.getContext) return;

  const ctx = canvas.getContext('2d');
  let W;
  let H;
  let particles = [];
  const COLORS = ['#FF1744', '#F4C842', '#00E5FF', '#00C853', '#FF2D95', '#7C3AED', '#FFB300'];

  const resize = () => {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
  };
  resize();
  window.addEventListener('resize', resize);

  const spawn = (n) => {
    for (let i = 0; i < n; i += 1) {
      particles.push({
        x: Math.random() * W,
        y: -20 - Math.random() * H * 0.5,
        vx: (Math.random() - 0.5) * 3,
        vy: 2 + Math.random() * 4,
        size: 6 + Math.random() * 8,
        rot: Math.random() * Math.PI * 2,
        vr: (Math.random() - 0.5) * 0.2,
        color: COLORS[Math.floor(Math.random() * COLORS.length)],
        shape: Math.random() > 0.5 ? 'rect' : 'circle',
      });
    }
  };
  spawn(160);
  window.setInterval(() => spawn(6), 400);

  const loop = () => {
    ctx.clearRect(0, 0, W, H);
    particles = particles.filter((p) => p.y < H + 40);
    particles.forEach((p) => {
      p.x += p.vx;
      p.y += p.vy;
      p.rot += p.vr;
      p.vy += 0.03;
      ctx.save();
      ctx.translate(p.x, p.y);
      ctx.rotate(p.rot);
      ctx.fillStyle = p.color;
      if (p.shape === 'rect') {
        ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2);
      } else {
        ctx.beginPath();
        ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();
    });
    requestAnimationFrame(loop);
  };
  loop();
})();

/* Reveal on scroll */
if ('IntersectionObserver' in window) {
  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.10 });

  document.querySelectorAll('.cat, .cat-circle, .cat-card, .step, .plan').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = `opacity .55s ease ${i * 0.03}s, transform .55s ease ${i * 0.03}s`;
    io.observe(el);
  });

  /* Home page — hp-reveal elements */
  document.querySelectorAll('.hp-reveal').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(28px)';
    el.style.transition = `opacity .6s cubic-bezier(.16,1,.3,1) ${i * 0.08}s, transform .6s cubic-bezier(.16,1,.3,1) ${i * 0.08}s`;
    io.observe(el);
  });
}


/* Account tabs + avatar preview */
(() => {
  const tabs = document.querySelectorAll('.account-tab[data-tab]');
  if (!tabs.length) return;

  const panels = {
    profile: document.getElementById('tab-profile'),
    password: document.getElementById('tab-password'),
  };

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      tabs.forEach((t) => t.classList.remove('is-active'));
      tab.classList.add('is-active');
      Object.entries(panels).forEach(([key, panel]) => {
        if (!panel) return;
        panel.hidden = key !== tab.dataset.tab;
      });
    });
  });

  const input = document.getElementById('avatarInput');
  const preview = document.getElementById('avatarPreview');
  const placeholder = document.getElementById('avatarPlaceholder');
  input?.addEventListener('change', () => {
    const file = input.files?.[0];
    if (!file || !preview) return;
    const url = URL.createObjectURL(file);
    preview.src = url;
    preview.hidden = false;
    if (placeholder) placeholder.hidden = true;
  });
})();

/* ==========================================
   Game Play Interactivity & AJAX helpers
   ========================================== */
document.addEventListener('DOMContentLoaded', () => {
  /* Turn toggling by clicking on team cards */
  document.querySelectorAll('.play-stage .team[data-team-card]').forEach((teamCard) => {
    teamCard.addEventListener('click', (e) => {
      if (e.target.closest('.helper-btn')) return;

      document.querySelectorAll('.play-stage .team[data-team-card]').forEach((card) => {
        card.classList.remove('active');
        const turnEl = card.querySelector('.team__turn');
        if (turnEl) turnEl.style.display = 'none';
      });

      teamCard.classList.add('active');
      const turnEl = teamCard.querySelector('.team__turn');
      if (turnEl) turnEl.style.display = 'block';
    });
  });

  /* Answer option selection highlight */
  document.querySelectorAll('.play-stage .answers .answer').forEach((answer) => {
    answer.style.cursor = 'pointer';
    answer.addEventListener('click', () => {
      document.querySelectorAll('.play-stage .answers .answer').forEach((a) => {
        a.classList.remove('selected');
      });
      answer.classList.add('selected');
    });
  });

  /* Interactive order questions */
  document.querySelectorAll('[data-order-game]').forEach((game) => {
    const list = game.querySelector('[data-order-list]');
    const result = game.querySelector('[data-order-result]');
    if (!list) return;

    const clearState = () => {
      list.querySelectorAll('[data-order-key]').forEach((item) => {
        item.classList.remove('is-correct', 'is-wrong');
      });
      result?.classList.remove('is-correct', 'is-wrong');
      if (result) result.textContent = '';
    };

    const moveItem = (item, direction) => {
      clearState();
      if (direction < 0 && item.previousElementSibling) {
        list.insertBefore(item, item.previousElementSibling);
      }
      if (direction > 0 && item.nextElementSibling) {
        list.insertBefore(item.nextElementSibling, item);
      }
    };

    let dragged = null;
    list.querySelectorAll('[data-order-key]').forEach((item) => {
      item.addEventListener('dragstart', () => {
        dragged = item;
        item.classList.add('is-dragging');
      });
      item.addEventListener('dragend', () => {
        item.classList.remove('is-dragging');
        dragged = null;
      });
      item.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (!dragged || dragged === item) return;
        clearState();
        const rect = item.getBoundingClientRect();
        const before = event.clientY < rect.top + rect.height / 2;
        list.insertBefore(dragged, before ? item : item.nextSibling);
      });
      item.querySelector('[data-order-up]')?.addEventListener('click', () => moveItem(item, -1));
      item.querySelector('[data-order-down]')?.addEventListener('click', () => moveItem(item, 1));
    });

    game.querySelector('[data-check-order]')?.addEventListener('click', () => {
      let correct = 0;
      const items = [...list.querySelectorAll('[data-order-key]')];
      items.forEach((item, index) => {
        const isCorrect = item.dataset.orderKey === String(index);
        item.classList.toggle('is-correct', isCorrect);
        item.classList.toggle('is-wrong', !isCorrect);
        if (isCorrect) correct += 1;
      });

      if (!result) return;
      const allCorrect = correct === items.length;
      result.textContent = allCorrect
        ? 'الترتيب صحيح بالكامل'
        : `في ${items.length - correct} عنصر محتاج يتراجع`;
      result.classList.toggle('is-correct', allCorrect);
      result.classList.toggle('is-wrong', !allCorrect);
    });
  });

  /* Interactive matching questions */
  document.querySelectorAll('[data-match-game]').forEach((game) => {
    const leftItems = [...game.querySelectorAll('[data-match-left]')];
    const rightItems = [...game.querySelectorAll('[data-match-right]')];
    const result = game.querySelector('[data-match-result]');
    const pairs = new Map();
    let selectedLeft = null;

    const resetResult = () => {
      result?.classList.remove('is-correct', 'is-wrong');
      if (result) result.textContent = '';
      [...leftItems, ...rightItems].forEach((item) => {
        item.classList.remove('is-correct', 'is-wrong');
      });
    };

    const refreshMarks = () => {
      leftItems.forEach((left) => {
        const pairNumber = [...pairs.keys()].indexOf(left) + 1;
        left.classList.toggle('is-paired', pairs.has(left));
        left.querySelector('.match-choice__mark').textContent = pairNumber > 0 ? pairNumber : '';
      });

      rightItems.forEach((right) => {
        const pairNumber = [...pairs.values()].indexOf(right) + 1;
        right.classList.toggle('is-paired', pairNumber > 0);
        right.querySelector('.match-choice__mark').textContent = pairNumber > 0 ? pairNumber : '';
      });
    };

    const clearPairs = () => {
      pairs.clear();
      selectedLeft = null;
      [...leftItems, ...rightItems].forEach((item) => {
        item.classList.remove('is-selected', 'is-paired', 'is-correct', 'is-wrong');
        item.querySelector('.match-choice__mark').textContent = '';
      });
      resetResult();
    };

    leftItems.forEach((left) => {
      left.addEventListener('click', () => {
        resetResult();
        selectedLeft = left;
        leftItems.forEach((item) => item.classList.toggle('is-selected', item === left));
      });
    });

    rightItems.forEach((right) => {
      right.addEventListener('click', () => {
        if (!selectedLeft) {
          if (result) {
            result.textContent = 'اختار عنصر من العمود الأول';
            result.classList.add('is-wrong');
          }
          return;
        }

        resetResult();
        for (const [left, pairedRight] of pairs.entries()) {
          if (pairedRight === right || left === selectedLeft) {
            pairs.delete(left);
          }
        }
        pairs.set(selectedLeft, right);
        selectedLeft.classList.remove('is-selected');
        selectedLeft = null;
        refreshMarks();
      });
    });

    game.querySelector('[data-reset-match]')?.addEventListener('click', clearPairs);

    game.querySelector('[data-check-match]')?.addEventListener('click', () => {
      if (pairs.size < leftItems.length) {
        if (result) {
          result.textContent = `كمل التوصيل: باقي ${leftItems.length - pairs.size}`;
          result.classList.add('is-wrong');
        }
        return;
      }

      let correct = 0;
      for (const [left, right] of pairs.entries()) {
        const isCorrect = left.dataset.matchKey === right.dataset.matchKey;
        left.classList.toggle('is-correct', isCorrect);
        right.classList.toggle('is-correct', isCorrect);
        left.classList.toggle('is-wrong', !isCorrect);
        right.classList.toggle('is-wrong', !isCorrect);
        if (isCorrect) correct += 1;
      }

      if (!result) return;
      const allCorrect = correct === leftItems.length;
      result.textContent = allCorrect
        ? 'كل التوصيلات صحيحة'
        : `في ${leftItems.length - correct} توصيلة غلط`;
      result.classList.toggle('is-correct', allCorrect);
      result.classList.toggle('is-wrong', !allCorrect);
    });
  });

  /* Lifeline (Helper) usage with confirmation & AJAX */
  document.querySelectorAll('.play-stage .helper-btn').forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      e.stopPropagation(); // Prevent turn toggle click
      if (btn.classList.contains('used')) return;

      const helper = btn.dataset.helper;
      const teamContainer = btn.closest('.team__lifelines');
      if (!teamContainer) return;

      const teamId = teamContainer.dataset.teamId;
      const gameId = teamContainer.dataset.gameId;

      const helperNames = {
        swap: 'تبديل السؤال',
        phone_friend: 'اتصال بصديق',
        two_answers: 'إجابتين'
      };
      const helperName = helperNames[helper] || helper;

      const confirmed = await window.showConfirm(`هل أنت متأكد من رغبتك في استخدام وسيلة المساعدة "${helperName}"؟`);
      if (!confirmed) return;

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

      fetch(`/game/${gameId}/team/${teamId}/use-helper/${helper}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({})
      })
      .then(response => response.json())
      .then(async (data) => {
        if (data.success) {
          btn.classList.add('used');
          btn.disabled = true;
          btn.title = `${helperName}: مستخدم`;
          
          // Disable helper button on all cards for this team
          document.querySelectorAll(`.team__lifelines[data-team-id="${teamId}"] .helper-btn[data-helper="${helper}"]`).forEach(b => {
            b.classList.add('used');
            b.disabled = true;
            b.title = `${helperName}: مستخدم`;
          });
          
          await window.showPopup(data.message || 'تم استخدام وسيلة المساعدة.', 'success');
        } else {
          await window.showPopup(data.message || 'فشل استخدام وسيلة المساعدة.', 'error');
        }
      })
      .catch(async (error) => {
        console.error('Error using helper:', error);
        await window.showPopup('حدث خطأ أثناء استخدام وسيلة المساعدة. يرجى المحاولة لاحقاً.', 'error');
      });
    });
  });
});

/* ==========================================
   Theme Toggling & Custom Dialog Modals
   ========================================== */
window.showPopup = function(message, type = 'success') {
  document.querySelectorAll('.custom-modal-overlay').forEach(el => el.remove());

  const overlay = document.createElement('div');
  overlay.className = 'custom-modal-overlay';
  
  const modal = document.createElement('div');
  modal.className = `custom-modal custom-modal--${type}`;
  
  const icon = type === 'success' ? '✔' : '✖';
  const iconClass = type === 'success' ? 'success' : 'error';
  
  modal.innerHTML = `
    <div class="custom-modal__icon custom-modal__icon--${iconClass}">${icon}</div>
    <div class="custom-modal__message">${message}</div>
    <button class="custom-modal__btn" id="modalOkBtn">موافق</button>
  `;
  
  overlay.appendChild(modal);
  document.body.appendChild(overlay);
  
  setTimeout(() => overlay.classList.add('is-active'), 10);

  return new Promise((resolve) => {
    document.getElementById('modalOkBtn').addEventListener('click', () => {
      overlay.classList.remove('is-active');
      setTimeout(() => {
        overlay.remove();
        resolve();
      }, 300);
    });
  });
};

window.showConfirm = function(message) {
  document.querySelectorAll('.custom-modal-overlay').forEach(el => el.remove());

  const overlay = document.createElement('div');
  overlay.className = 'custom-modal-overlay';
  
  const modal = document.createElement('div');
  modal.className = 'custom-modal custom-modal--confirm';
  
  modal.innerHTML = `
    <div class="custom-modal__icon custom-modal__icon--confirm">❓</div>
    <div class="custom-modal__message">${message}</div>
    <div class="custom-modal__actions">
      <button class="custom-modal__btn custom-modal__btn--yes" id="modalYesBtn">نعم</button>
      <button class="custom-modal__btn custom-modal__btn--no" id="modalNoBtn">إلغاء</button>
    </div>
  `;
  
  overlay.appendChild(modal);
  document.body.appendChild(overlay);
  
  setTimeout(() => overlay.classList.add('is-active'), 10);

  return new Promise((resolve) => {
    document.getElementById('modalYesBtn').addEventListener('click', () => {
      overlay.classList.remove('is-active');
      setTimeout(() => {
        overlay.remove();
        resolve(true);
      }, 300);
    });
    
    document.getElementById('modalNoBtn').addEventListener('click', () => {
      overlay.classList.remove('is-active');
      setTimeout(() => {
        overlay.remove();
        resolve(false);
      }, 300);
    });
  });
};

/* Init Theme — sync across site + admin */
(() => {
  const applyTheme = (dark) => {
    document.body.classList.toggle('dark', dark);
    document.documentElement.classList.toggle('dark', dark);
    document.querySelectorAll('#themeToggle, .theme-toggle').forEach((btn) => {
      btn.textContent = dark ? '☀️' : '🌙';
      btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
    });
  };

  const storedDark = localStorage.getItem('theme') === 'dark';
  applyTheme(storedDark);

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#themeToggle, .theme-toggle');
    if (!btn) return;
    const nextDark = !document.body.classList.contains('dark');
    localStorage.setItem('theme', nextDark ? 'dark' : 'light');
    applyTheme(nextDark);
  });
})();

document.addEventListener('DOMContentLoaded', () => {

  /* Board manual score adjustment */
  document.querySelectorAll('.board-score-control, .board-team__score').forEach(container => {
    const teamId = container.dataset.teamId;
    const gameId = container.dataset.gameId;
    const scoreVal = container.querySelector('.score-val');

    container.querySelectorAll('.score-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const amount = parseInt(btn.dataset.amount) || 100;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        fetch(`/game/${gameId}/team/${teamId}/adjust-score`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
          body: JSON.stringify({ amount })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            scoreVal.textContent = data.score;
          } else {
            window.showPopup(data.message || 'فشل تحديث النتيجة.', 'error');
          }
        })
        .catch(err => {
          console.error('Error adjusting score:', err);
        });
      });
    });
  });

  /* Board lifeline helper usage */
  document.querySelectorAll('.board-lifelines-control .lifeline-btn, .board-team__tools .board-helper').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      if (btn.classList.contains('used') || btn.classList.contains('is-used')) return;

      const helper = btn.dataset.helper;
      const container = btn.closest('.board-lifelines-control, .board-team__tools');
      if (!container) return;

      const teamId = container.dataset.teamId;
      const gameId = container.dataset.gameId;

      const helperNames = {
        swap: 'تبديل السؤال',
        phone_friend: 'اتصال بصديق',
        two_answers: 'إجابتين'
      };
      const helperName = helperNames[helper] || helper;

      const confirmed = await window.showConfirm(`هل أنت متأكد من رغبتك في استخدام وسيلة المساعدة "${helperName}"؟`);
      if (!confirmed) return;

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

      fetch(`/game/${gameId}/team/${teamId}/use-helper/${helper}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({})
      })
      .then(response => response.json())
      .then(async (data) => {
        if (data.success) {
          btn.classList.add('used', 'is-used');
          btn.disabled = true;
          btn.title = `${helperName}: مستخدم`;
          await window.showPopup(data.message || 'تم استخدام وسيلة المساعدة.', 'success');
        } else {
          await window.showPopup(data.message || 'فشل استخدام وسيلة المساعدة.', 'error');
        }
      })
      .catch(async (err) => {
        console.error('Error using helper:', err);
        await window.showPopup('حدث خطأ أثناء استخدام وسيلة المساعدة.', 'error');
      });
    });
  });
});
