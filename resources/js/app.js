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
const homePills = document.querySelectorAll('.filters .pill[data-filter], .filters__row .pill[data-filter]');
const homeCats = document.querySelectorAll('.cat[data-cat], .cat[data-group], .cat-circle[data-cat], .cat-circle[data-group]');

homePills.forEach((pill) => {
  pill.addEventListener('click', () => {
    if (pill.closest('.categories-design')) return;
    homePills.forEach((p) => p.classList.remove('is-active'));
    pill.classList.add('is-active');
    const filter = pill.dataset.filter;

    homeCats.forEach((cat) => {
      const group = cat.dataset.group;
      const tag = cat.dataset.cat;
      let match = filter === 'all';
      if (filter === 'uae') match = group === 'uae' || tag === 'uae';
      else if (filter === 'general') match = group === 'general';
      else match = tag === filter;
      cat.classList.toggle('is-hidden', !match);
    });
  });
});

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
      const name = (card.dataset.name || '').toLowerCase();
      let match = activeFilter === 'all';
      if (activeFilter === 'uae') match = group === 'uae' || filter === 'uae';
      else if (activeFilter === 'general') match = filter === 'general' || group === 'general';
      else match = filter === activeFilter;
      if (term && !name.includes(term)) match = false;
      card.classList.toggle('is-hidden', !match);
      if (match) visible += 1;
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
    term = e.target.value.trim().toLowerCase();
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
  }, { threshold: 0.12 });

  document.querySelectorAll('.cat, .cat-circle, .cat-card, .step, .plan').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = `opacity .55s ease ${i * 0.03}s, transform .55s ease ${i * 0.03}s`;
    io.observe(el);
  });
}

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

/* Init Theme */
document.addEventListener('DOMContentLoaded', () => {
  const themeToggle = document.getElementById('themeToggle');
  if (themeToggle) {
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
      document.body.classList.add('dark');
      themeToggle.textContent = '☀️';
    } else {
      document.body.classList.remove('dark');
      themeToggle.textContent = '🌙';
    }

    themeToggle.addEventListener('click', () => {
      if (document.body.classList.contains('dark')) {
        document.body.classList.remove('dark');
        localStorage.setItem('theme', 'light');
        themeToggle.textContent = '🌙';
      } else {
        document.body.classList.add('dark');
        localStorage.setItem('theme', 'dark');
        themeToggle.textContent = '☀️';
      }
    });
  }

  /* Board manual score adjustment */
  document.querySelectorAll('.board-score-control').forEach(container => {
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
  document.querySelectorAll('.board-lifelines-control .lifeline-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      if (btn.classList.contains('used')) return;

      const helper = btn.dataset.helper;
      const container = btn.closest('.board-lifelines-control');
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
          btn.classList.add('used');
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
