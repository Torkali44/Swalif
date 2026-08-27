import './bootstrap';

/* Reveal images only after full download/decode — no progressive band paint */
(() => {
  const mark = (img) => {
    if (!(img instanceof HTMLImageElement)) return;
    img.classList.add('sw-img');
    const ready = () => img.classList.add('is-ready');
    const fail = () => img.classList.add('is-ready', 'is-error');

    if (img.complete && img.naturalWidth > 0) {
      if (typeof img.decode === 'function') {
        img.decode().then(ready).catch(ready);
      } else {
        ready();
      }
      return;
    }

    img.addEventListener('load', () => {
      if (typeof img.decode === 'function') {
        img.decode().then(ready).catch(ready);
      } else {
        ready();
      }
    }, { once: true });
    img.addEventListener('error', fail, { once: true });
  };

  const scan = (root = document) => {
    root.querySelectorAll('img:not([data-no-sw-img])').forEach(mark);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => scan());
  } else {
    scan();
  }

  // Dynamically inserted images
  const mo = new MutationObserver((mutations) => {
    mutations.forEach((m) => {
      m.addedNodes.forEach((node) => {
        if (node.nodeType !== 1) return;
        if (node.tagName === 'IMG') mark(node);
        else if (node.querySelectorAll) scan(node);
      });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();

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
  let total = Number(timerEl.dataset.timerRing) || 60;
  const circum = 2 * Math.PI * 52;
  const bar = timerEl.querySelector('.timer__bar');
  const val = timerEl.querySelector('.timer__value b, #timerValue');
  if (!bar || !val) return;

  bar.style.strokeDasharray = String(circum);
  let remaining = total;
  let interval = null;
  let expiredHandled = false;
  let warnUnlocked = false;

  const warnAudio = document.getElementById('timerWarnSound');
  const endAudio = document.getElementById('timerEndSound');

  const playTimerSound = (kind) => {
    // Prefer Web Audio (works even when WAV files missing/blocked on host)
    const fallback = () => {
      if (typeof window.SwalifAudio?.play === 'function') {
        window.SwalifAudio.play(kind === 'warn' ? 'timer-warn' : 'timer-end');
      }
    };

    fallback();

    const el = kind === 'warn' ? warnAudio : endAudio;
    if (!el) return;

    try {
      const node = el.cloneNode(true);
      node.volume = 0.85;
      const p = node.play();
      if (p && typeof p.catch === 'function') {
        p.catch(() => { });
      }
    } catch (_) { }
  };

  const unlockAudio = () => {
    try {
      if (typeof window.SwalifAudio?.getCtx === 'function') {
        window.SwalifAudio.getCtx();
      }
    } catch (_) { }
    [warnAudio, endAudio].forEach((el) => {
      if (!el) return;
      try {
        el.muted = true;
        const p = el.play();
        const stop = () => {
          try {
            el.pause();
            el.currentTime = 0;
            el.muted = false;
          } catch (_) { }
        };
        if (p && typeof p.then === 'function') p.then(stop).catch(() => { el.muted = false; });
        else stop();
      } catch (_) {
        try { el.muted = false; } catch (__) { }
      }
    });
  };

  // Browsers block autoplay until a user gesture — unlock on first interaction.
  ['pointerdown', 'touchstart', 'keydown'].forEach((evt) => {
    document.addEventListener(evt, unlockAudio, { once: true, passive: true });
  });

  const goToAnswer = async () => {
    if (expiredHandled) return;
    expiredHandled = true;
    playTimerSound('end');
    const answerUrl = timerEl.dataset.answerUrl;
    try {
      if (typeof window.showPopup === 'function') {
        await window.showPopup('انتهى وقت الإجابة!', 'error', { autoCloseMs: 4000 });
      } else {
        await new Promise((r) => setTimeout(r, 4000));
      }
    } catch (_) {
      // continue to answer page even if popup fails
    }
    if (answerUrl) {
      window.location.href = answerUrl;
    }
  };

  const tick = () => {
    remaining -= 1;
    val.textContent = String(Math.max(remaining, 0));
    bar.style.strokeDashoffset = String(circum * (1 - Math.max(remaining, 0) / total));
    if (remaining <= 5 && remaining > 0) {
      timerEl.classList.add('warn');
      if (!warnUnlocked) {
        warnUnlocked = true;
        unlockAudio();
      }
      playTimerSound('warn');
    }
    if (remaining <= 0) {
      window.clearInterval(interval);
      interval = null;
      timerEl.classList.add('expired');
      goToAnswer();
    }
  };

  const start = () => {
    if (interval) return;
    timerEl.classList.remove('is-waiting', 'is-paused');
    interval = window.setInterval(tick, 1000);
  };

  const pause = () => {
    if (interval) {
      window.clearInterval(interval);
      interval = null;
    }
    timerEl.classList.add('is-paused');
  };

  const resetAndResume = (newSeconds = 30) => {
    if (interval) {
      window.clearInterval(interval);
      interval = null;
    }
    total = newSeconds;
    remaining = newSeconds;
    warnUnlocked = false;
    val.textContent = String(remaining);
    bar.style.strokeDashoffset = '0';
    timerEl.classList.remove('warn', 'expired', 'is-paused');
    interval = window.setInterval(tick, 1000);
  };

  window.swalifQuestionTimer = {
    pause,
    resume: start,
    resetAndResume,
  };

  if (timerEl.dataset.timerWaitVideo === 'true') {
    timerEl.classList.add('is-waiting');
    document.addEventListener('swalif:video-ready', start, { once: true });
  } else {
    start();
  }
});

/* Mobile nav */
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');
if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    navLinks.classList.toggle('is-open');
  });
}

/* Admin mobile sidebar drawer */
(() => {
  const sidebar = document.getElementById('adminSidebar');
  const overlay = document.getElementById('adminOverlay');
  const openBtn = document.getElementById('adminMenuBtn');
  const closeBtn = document.getElementById('adminSidebarClose');
  if (!sidebar || !openBtn) return;

  const setOpen = (open) => {
    sidebar.classList.toggle('is-open', open);
    if (overlay) {
      overlay.hidden = !open;
      overlay.classList.toggle('is-open', open);
    }
    document.body.classList.toggle('admin-nav-open', open);
  };

  openBtn.addEventListener('click', () => setOpen(true));
  closeBtn?.addEventListener('click', () => setOpen(false));
  overlay?.addEventListener('click', () => setOpen(false));
  sidebar.querySelectorAll('.nav-link').forEach((link) => {
    link.addEventListener('click', () => setOpen(false));
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setOpen(false);
  });
})();

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
  const isLastQuestion = assignForm.hasAttribute('data-last-question');

  assignForm.querySelectorAll('.assign-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (submitting) return;
      submitting = true;
      teamInput.value = btn.dataset.teamId ?? '';
      assignForm.querySelectorAll('.assign-btn').forEach((b) => {
        b.disabled = true;
      });

      // أظهر تنبيه انتهاء اللعبة قبل الانتقال لصفحة النتيجة
      if (isLastQuestion && typeof window.showPopup === 'function') {
        try {
          await window.showPopup('انتهت اللعبة! خلصت كل الأسئلة — شوف النتيجة 🏆', 'success');
        } catch (_) {}
      }

      assignForm.submit();
    });
  });
}

/* Multiple-choice selection on question page */
(() => {
  const form = document.querySelector('[data-choice-form]');
  if (!form) return;

  const hidden = form.querySelector('#selectedOptionId');
  const hint = form.querySelector('[data-choice-hint]');
  const options = form.querySelectorAll('.answer[data-option-id]');

  options.forEach((btn) => {
    btn.addEventListener('click', () => {
      options.forEach((b) => b.classList.remove('selected'));
      btn.classList.add('selected');
      if (hidden) hidden.value = btn.dataset.optionId || '';
      if (hint) {
        hint.textContent = 'تمام — اضغط عرض الإجابة';
        hint.classList.remove('is-error');
      }
    });
  });

  form.addEventListener('submit', (e) => {
    if (!hidden?.value) {
      e.preventDefault();
      if (hint) {
        hint.textContent = 'لازم تختار إجابة الأول';
        hint.classList.add('is-error');
      }
      window.showPopup?.('اختار إجابة قبل عرض الإجابة', 'error');
    }
  });
})();

/* Video questions — play once, then reveal question */
(() => {
  const video = document.querySelector('video[data-play-once]');
  if (!video) return;

  let finished = false;
  let started = false;
  const hint = document.querySelector('[data-video-hint]');
  const gate = document.querySelector('[data-video-gate]');
  const reveal = document.querySelector('[data-video-reveal]');

  const lockAndReveal = () => {
    if (finished) return;
    finished = true;
    video.pause();
    video.removeAttribute('controls');
    video.controls = false;
    video.classList.add('is-locked');
    if (hint) hint.textContent = '✓ انتهى العرض — هيظهر السؤال الآن';
    if (gate) gate.classList.add('is-done');
    if (reveal) {
      reveal.hidden = false;
      reveal.classList.add('is-visible');
      try { reveal.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch (e) { }
    }
    document.dispatchEvent(new CustomEvent('swalif:video-ready'));
  };

  video.addEventListener('play', () => {
    if (finished) {
      video.pause();
      return;
    }
    started = true;
    if (hint) hint.textContent = 'جاري التشغيل… ركّز كويس (مرة واحدة فقط)';
  });

  video.addEventListener('ended', lockAndReveal);

  // If user seeks near end after watching most of it, still lock
  video.addEventListener('timeupdate', () => {
    if (!started || finished || !video.duration) return;
    if (video.currentTime / video.duration >= 0.98) {
      lockAndReveal();
    }
  });

  video.addEventListener('seeking', () => {
    if (finished) {
      try { video.currentTime = video.duration || 0; } catch (e) { }
    }
  });
  video.addEventListener('contextmenu', (e) => e.preventDefault());
})();

/* Result confetti */
(() => {
  const canvas = document.getElementById('confetti');
  if (!canvas || !canvas.getContext) return;

  const winSound = document.getElementById('winSound');
  const playWinSound = () => {
    if (typeof window.SwalifAudio?.play === 'function') {
      try { window.SwalifAudio.play('correct'); } catch (_) { }
    }
    if (!winSound) return;
    const tryPlay = () => {
      winSound.currentTime = 0;
      winSound.play().catch(() => { });
    };
    tryPlay();
    const unlock = () => {
      tryPlay();
      document.removeEventListener('click', unlock);
      document.removeEventListener('touchstart', unlock);
    };
    document.addEventListener('click', unlock, { once: true });
    document.addEventListener('touchstart', unlock, { once: true });
  };

  // Play win sound immediately on result page load
  window.__swalifPlayWinSound = playWinSound;
  playWinSound();

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
  const emojiPreview = document.getElementById('avatarEmojiPreview');
  const characterInput = document.getElementById('characterIdInput');
  const characterButtons = document.querySelectorAll('.account-character[data-character-id]');

  const showCharacterPreview = (btn) => {
    if (!btn) return;
    const image = btn.dataset.characterImage;
    const icon = btn.dataset.characterIcon || '🧑';
    const gradient = btn.dataset.characterGradient || 'linear-gradient(135deg,#1E3A5F,#0F2440)';

    if (image && preview) {
      preview.src = image;
      preview.hidden = false;
      if (placeholder) placeholder.hidden = true;
      return;
    }

    if (!placeholder) return;

    if (preview) {
      preview.hidden = true;
      preview.removeAttribute('src');
    }

    placeholder.hidden = false;
    placeholder.style.background = gradient;
    if (emojiPreview) {
      emojiPreview.textContent = icon;
    } else {
      placeholder.textContent = icon;
    }
  };

  characterButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      characterButtons.forEach((b) => {
        b.classList.remove('is-selected');
        b.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('is-selected');
      btn.setAttribute('aria-selected', 'true');
      if (characterInput) characterInput.value = btn.dataset.characterId || '';
      if (input) input.value = '';
      showCharacterPreview(btn);
    });
  });

  input?.addEventListener('change', () => {
    const file = input.files?.[0];
    if (!file || !preview) return;
    const url = URL.createObjectURL(file);
    preview.src = url;
    preview.hidden = false;
    if (placeholder) placeholder.hidden = true;
    // Photo upload replaces character selection
    if (characterInput) characterInput.value = '';
    characterButtons.forEach((b) => {
      b.classList.remove('is-selected');
      b.setAttribute('aria-selected', 'false');
    });
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

  /* Interactive word-build (رتبها) questions */
  const normalizeArabicWord = (word) => {
    return String(word || '')
      .replace(/[\u064B-\u065F\u0670\u0640\u200C\u200D\uFEFF]/g, '')
      .replace(/\s+/g, '')
      .replace(/[أإآٱ]/g, 'ا')
      .replace(/ى/g, 'ي')
      .replace(/ئ/g, 'ي')
      .replace(/ؤ/g, 'و');
  };

  document.querySelectorAll('[data-word-build-game]').forEach((game) => {
    const form = game.closest('[data-word-build-form]') || game.closest('form');
    const payload = form?.querySelector('[data-word-build-payload]');
    const input = game.querySelector('[data-word-build-input]');
    const submitBtn = game.querySelector('[data-word-build-submit]');
    const foundWrap = game.querySelector('[data-word-build-found]');
    const foundList = game.querySelector('[data-word-build-found-list]');
    const result = game.querySelector('[data-word-build-result]');
    const progress = game.querySelector('[data-word-build-progress]');
    const totalWords = Number(game.dataset.totalWords) || 0;

    let validWords = [];
    try {
      validWords = JSON.parse(game.dataset.validWords || '[]');
    } catch (_) {
      validWords = [];
    }

    const normalizedValid = new Map(
      validWords.map((word) => [normalizeArabicWord(word), word])
    );
    const found = new Set();
    const foundLabels = [];

    const syncPayload = () => {
      if (payload) {
        payload.value = JSON.stringify(foundLabels);
      }
    };

    const updateProgress = () => {
      if (progress) {
        progress.textContent = `${found.size} / ${totalWords} كلمة`;
      }
      syncPayload();
    };

    const showResult = (text, type) => {
      if (!result) return;
      result.textContent = text;
      result.classList.remove('is-correct', 'is-wrong');
      if (type) result.classList.add(type);
    };

    const submitWord = () => {
      const raw = input?.value?.trim();
      if (!raw) return;

      const normalized = normalizeArabicWord(raw);

      if (found.has(normalized)) {
        showResult('سبق ووجدت هذه الكلمة', 'is-wrong');
        input?.classList.add('is-shake');
        setTimeout(() => input?.classList.remove('is-shake'), 400);
        return;
      }

      const matchedWord = normalizedValid.get(normalized);
      if (!matchedWord) {
        showResult('ليست من الكلمات المطلوبة', 'is-wrong');
        input?.classList.add('is-shake');
        setTimeout(() => input?.classList.remove('is-shake'), 400);
        return;
      }

      found.add(normalized);
      foundLabels.push(matchedWord);
      if (foundWrap) foundWrap.hidden = false;
      if (foundList) {
        const chip = document.createElement('span');
        chip.className = 'word-build-found__chip';
        chip.textContent = matchedWord;
        foundList.appendChild(chip);
      }

      if (input) input.value = '';
      updateProgress();

      if (found.size >= totalWords && totalWords > 0) {
        showResult('أحسنت! وجدت كل الكلمات 🎉', 'is-correct');
        // Auto-continue to answer page so verdict shows "إجابتك صحيحة"
        setTimeout(() => {
          syncPayload();
          form?.requestSubmit?.() || form?.submit();
        }, 650);
      } else {
        showResult(`صح! باقي ${totalWords - found.size} ${totalWords - found.size === 1 ? 'كلمة' : 'كلمات'}`, 'is-correct');
      }
    };

    submitBtn?.addEventListener('click', submitWord);
    input?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        submitWord();
      }
    });

    form?.addEventListener('submit', () => {
      syncPayload();
    });

    syncPayload();
  });
});

/* ==========================================
   Hex Letter Grid Game
   ========================================== */
document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-hex-game]');
  if (!root) return;

  const gameId = root.dataset.gameId || '';
  const resultUrl = root.dataset.resultUrl || '';
  const csrf = root.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '';
  const timeLimitTotal = Number(root.dataset.timeLimit) || 30;

  const questionText = root.querySelector('[data-hex-question-text]');
  const activeLetter = root.querySelector('[data-hex-active-letter]');
  const answerReveal = root.querySelector('[data-hex-answer-reveal]');
  const answerText = root.querySelector('[data-hex-answer-text]');
  const claimPanel = root.querySelector('[data-hex-claim-panel]');
  const progressEl = root.querySelector('[data-hex-progress]');
  const showAnswerBtn = root.querySelector('[data-hex-show-answer]');
  const newQuestionBtn = root.querySelector('[data-hex-new-question]');
  const timerWrap = root.querySelector('[data-hex-timer-wrap]');
  const timerValue = root.querySelector('[data-hex-timer-value]');
  const timerBar = root.querySelector('[data-hex-timer-bar]');
  const drawer = document.getElementById('hexGameDrawer');

  let activeCellId = null;
  let answerVisible = false;
  let timerInterval = null;
  let timerRemaining = timeLimitTotal;
  let claiming = false;
  const timerCircum = 2 * Math.PI * 34;
  const warnAudio = document.getElementById('timerWarnSound');
  const endAudio = document.getElementById('timerEndSound');

  const playTimerSound = (kind) => {
    // Prefer Web Audio (works even when WAV files missing/blocked)
    try {
      if (typeof window.SwalifAudio?.play === 'function') {
        window.SwalifAudio.play(kind === 'warn' ? 'timer-warn' : 'timer-end');
      }
    } catch (_) { }

    const el = kind === 'warn' ? warnAudio : endAudio;
    if (!el) return;
    try {
      const node = el.cloneNode(true);
      node.volume = 0.85;
      const p = node.play();
      if (p && typeof p.catch === 'function') p.catch(() => { });
    } catch (_) { }
  };

  const unlockAudio = () => {
    try {
      if (typeof window.SwalifAudio?.getCtx === 'function') {
        window.SwalifAudio.getCtx();
      }
    } catch (_) { }
    [warnAudio, endAudio].forEach((el) => {
      if (!el) return;
      try {
        el.muted = true;
        const p = el.play();
        const stop = () => {
          try {
            el.pause();
            el.currentTime = 0;
            el.muted = false;
          } catch (_) { }
        };
        if (p && typeof p.then === 'function') p.then(stop).catch(() => { el.muted = false; });
        else stop();
      } catch (_) {
        try { el.muted = false; } catch (__) { }
      }
    });
  };

  // Browsers block autoplay until a user gesture
  ['pointerdown', 'touchstart', 'keydown'].forEach((evt) => {
    document.addEventListener(evt, unlockAudio, { once: true, passive: true });
  });

  const playSound = (type) => {
    try { window.SwalifAudio?.play(type); } catch (_) { }
  };

  /* End game action */
  root.querySelector('[data-hex-end-game]')?.addEventListener('click', (e) => {
    playSound('click');
  });

  const stopTimer = () => {
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }
    if (timerWrap) timerWrap.hidden = true;
    timerWrap?.classList.remove('is-warn');
  };

  const startTimer = () => {
    stopTimer();
    unlockAudio();
    timerRemaining = timeLimitTotal;
    if (timerWrap) timerWrap.hidden = false;
    if (timerValue) timerValue.textContent = String(timerRemaining);
    if (timerBar) {
      timerBar.style.strokeDasharray = String(timerCircum);
      timerBar.style.strokeDashoffset = '0';
    }

    timerInterval = setInterval(() => {
      timerRemaining -= 1;
      if (timerValue) timerValue.textContent = String(Math.max(timerRemaining, 0));
      if (timerBar) {
        timerBar.style.strokeDashoffset = String(timerCircum * (1 - Math.max(timerRemaining, 0) / timeLimitTotal));
      }
      // Countdown beeps every second for the last 5 seconds (same as question timer)
      if (timerRemaining <= 5 && timerRemaining > 0) {
        timerWrap?.classList.add('is-warn');
        playTimerSound('warn');
      }
      if (timerRemaining <= 0) {
        stopTimer();
        playTimerSound('end');
        answerVisible = true;
        if (answerReveal) answerReveal.hidden = false;
        if (claimPanel) {
          claimPanel.hidden = false;
          claimPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      }
    }, 1000);
  };

  const resetQuestionUi = () => {
    activeCellId = null;
    answerVisible = false;
    stopTimer();
    if (answerReveal) answerReveal.hidden = true;
    if (claimPanel) claimPanel.hidden = true;
    if (questionText) questionText.textContent = 'اختر حرفاً من الشبكة لبدء السؤال';
    if (activeLetter) activeLetter.textContent = '؟';
    if (showAnswerBtn) showAnswerBtn.disabled = true;
    if (newQuestionBtn) newQuestionBtn.disabled = false;
  };

  const setActiveCell = (btn) => {
    if (!btn || btn.dataset.resolved === '1') return;

    root.querySelectorAll('[data-hex-cell]').forEach((el) => el.classList.remove('is-active'));
    btn.classList.add('is-active');

    activeCellId = btn.dataset.cellId;
    answerVisible = false;

    if (activeLetter) activeLetter.textContent = btn.dataset.letter || '؟';
    if (questionText) questionText.textContent = btn.dataset.question || '';
    if (answerText) answerText.textContent = btn.dataset.answer || '';
    if (answerReveal) answerReveal.hidden = true;
    if (claimPanel) claimPanel.hidden = true;
    if (showAnswerBtn) showAnswerBtn.disabled = false;
    if (newQuestionBtn) newQuestionBtn.disabled = false;

    if (window.innerWidth <= 960) {
      const qCard = root.querySelector('[data-hex-question-card]');
      if (qCard) {
        qCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }

    startTimer();
    playSound('select');
  };

  root.querySelectorAll('[data-hex-cell]').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (btn.dataset.resolved === '1') return;
      setActiveCell(btn);
    });
  });

  showAnswerBtn?.addEventListener('click', () => {
    if (!activeCellId) return;
    stopTimer();
    answerVisible = true;
    if (answerReveal) answerReveal.hidden = false;
    if (claimPanel) {
      claimPanel.hidden = false;
      claimPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    playSound('click');
  });

  newQuestionBtn?.addEventListener('click', () => {
    const available = [...root.querySelectorAll('[data-hex-cell]')].filter((b) => b.dataset.resolved !== '1');
    if (!available.length) return;
    const next = available[Math.floor(Math.random() * available.length)];
    setActiveCell(next);
  });

  const claimUrlFor = (cellId) => `/letter-grid/${gameId}/cell/${cellId}/claim`;

  const submitClaim = async (teamId, correct) => {
    if (!activeCellId || claiming) return;
    claiming = true;
    stopTimer();

    showAnswerBtn && (showAnswerBtn.disabled = true);
    root.querySelectorAll('[data-hex-claim-team], [data-hex-claim-none]').forEach((b) => {
      b.disabled = true;
    });

    try {
      const res = await fetch(claimUrlFor(activeCellId), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify({ team_id: teamId, correct }),
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        throw new Error(data.message || 'فشل احتساب الحرف');
      }

      playSound(correct ? 'correct' : 'wrong');

      const cellBtn = root.querySelector(`[data-hex-cell][data-cell-id="${data.cell.id}"]`);
      if (cellBtn) {
        cellBtn.classList.add('is-claiming');
        cellBtn.dataset.resolved = '1';
        cellBtn.disabled = true;
        cellBtn.classList.remove('is-active');
        if (data.cell.missed) {
          cellBtn.classList.add('is-missed');
        } else if (data.cell.team_index !== null && data.cell.team_index !== undefined) {
          cellBtn.classList.add('is-claimed', `is-team-${data.cell.team_index}`);
        }
        setTimeout(() => cellBtn.classList.remove('is-claiming'), 450);
      }

      data.teams?.forEach((team) => {
        const scoreEl = root.querySelector(`[data-hex-team-score="${team.id}"]`);
        if (scoreEl) scoreEl.textContent = String(team.score);
      });

      if (data.turn_index !== undefined) {
        let activeTurnName = '';
        root.querySelectorAll('[data-hex-team-bar]').forEach((bar) => {
          const isTurn = Number(bar.dataset.teamIndex) === Number(data.turn_index);
          bar.classList.toggle('is-turn', isTurn);
          const turnPill = bar.querySelector('[data-hex-turn-pill]');
          if (turnPill) turnPill.hidden = !isTurn;
          const turnDesc = bar.querySelector('[data-hex-turn-desc]');
          if (turnDesc) {
            turnDesc.textContent = isTurn ? 'حان دوره لاختيار الحرف والإجابة' : 'ينتظر دوره';
          }
          if (isTurn) {
            activeTurnName = bar.dataset.teamName || bar.querySelector('.hex-team-bar__name')?.textContent?.trim() || '';
          }
        });

        if (activeTurnName) {
          const panelTurn = root.querySelector('[data-hex-panel-turn-name]');
          if (panelTurn) panelTurn.textContent = activeTurnName;
          const mobileTurn = root.querySelector('[data-hex-mobile-turn-name]');
          if (mobileTurn) mobileTurn.textContent = activeTurnName;
          const statusTurn = root.querySelector('[data-hex-turn-indicator-text]');
          if (statusTurn) statusTurn.textContent = activeTurnName;
        }
      }

      if (data.resolved !== undefined) {
        if (progressEl) progressEl.textContent = String(data.resolved);
        const mobileResolved = root.querySelector('[data-hex-mobile-resolved]');
        if (mobileResolved) mobileResolved.textContent = String(data.resolved);
        const progressBar = root.querySelector('[data-hex-progress-bar]');
        if (progressBar && data.total) {
          const pct = Math.round((data.resolved / data.total) * 100);
          progressBar.style.width = `${pct}%`;
        }
      }

      resetQuestionUi();

      if (data.finished && data.redirect) {
        playSound('correct');
        if (typeof window.showPopup === 'function') {
          window.showPopup('انتهت شبكة الحروف! حان وقت تتويج الفائز 🏆', 'success');
        }
        setTimeout(() => { window.location.href = data.redirect; }, 1200);
      }
    } catch (err) {
      playSound('wrong');
      const isFinishedMsg = err.message && (err.message.includes('اللعبة منتهية') || err.message.includes('منتهية'));
      if (typeof window.showPopup === 'function') {
        if (isFinishedMsg && resultUrl) {
          window.showPopup('اللعبة منتهية بالفعل! جاري نقلك لصفحة النتيجة 🏆', 'error');
          setTimeout(() => { window.location.href = resultUrl; }, 1400);
        } else {
          window.showPopup(err.message || 'حدث خطأ. حاول مرة أخرى.', 'error');
        }
      } else {
        console.error(err);
      }
      claiming = false;
    } finally {
      claiming = false;
      root.querySelectorAll('[data-hex-claim-team], [data-hex-claim-none]').forEach((b) => {
        b.disabled = false;
      });
    }
  };

  root.querySelectorAll('[data-hex-claim-team]').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (!answerVisible || !activeCellId) return;
      submitClaim(Number(btn.dataset.teamId), true);
    });
  });

  root.querySelector('[data-hex-claim-none]')?.addEventListener('click', () => {
    if (!answerVisible || !activeCellId) return;
    submitClaim(null, false);
  });

  const initialActive = root.querySelector('[data-hex-cell].is-active:not([disabled])');
  if (initialActive) setActiveCell(initialActive);
});

/* ==========================================
   Theme Toggling & Custom Dialog Modals
   ========================================== */
window.showPopup = function (message, type = 'success', options = {}) {
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

  const autoCloseMs = Number(options?.autoCloseMs) || 0;

  return new Promise((resolve) => {
    let closed = false;
    const close = () => {
      if (closed) return;
      closed = true;
      overlay.classList.remove('is-active');
      setTimeout(() => {
        overlay.remove();
        resolve();
      }, 300);
    };

    document.getElementById('modalOkBtn').addEventListener('click', close);
    if (autoCloseMs > 0) {
      setTimeout(close, autoCloseMs);
    }
  });
};

window.showConfirm = function (message) {
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

window.swalifHandleCategoryPlay = async function (url, meta = {}) {
  if (!url) return false;

  const total = parseInt(meta.total, 10);
  const remaining = parseInt(meta.remaining, 10);
  const knowsTotal = Number.isFinite(total);
  const knowsRemaining = Number.isFinite(remaining);
  // When set, skip the "replay?" confirmation and navigate directly
  const noReplayConfirm = !!meta.noReplayConfirm;

  try {
    // Empty category: warn, then still allow opening the page
    if (knowsTotal && total <= 0) {
      if (typeof window.showPopup === 'function') {
        await window.showPopup(
          'هالفئة فاضية الحين 🎯<br>بنضيف لها أسئلة قريب — ارجع لها بعدين وتقدر تلعب!',
          'error'
        );
      } else {
        alert('هالفئة فاضية الحين — بنضيف أسئلة قريب، ارجع لها بعدين.');
      }
      return false;
    }

    // Show replay confirmation only on pages that opt-in (e.g. categories index).
    // Pages with data-no-replay-confirm (category show, game setup) go directly.
    if (!noReplayConfirm && knowsRemaining && knowsTotal && remaining <= 0 && total > 0) {
      let replay = true;
      if (typeof window.showConfirm === 'function') {
        replay = await window.showConfirm('خلّصت كل أسئلة هالفئة! تبي تلعبها من جديد؟');
      }
      if (!replay) return false;
    }
  } catch (_) {
    // ignore popup errors — still navigate
  }

  window.location.assign(url);
  return true;
};

document.addEventListener('click', (e) => {
  const el = e.target.closest('[data-category-play]');
  if (!el) return;
  const url = el.dataset.playUrl || el.getAttribute('href') || '';
  if (!url) return;
  e.preventDefault();
  e.stopPropagation();

  // Fail-open: if anything breaks, still go to the URL
  try {
    Promise.resolve(window.swalifHandleCategoryPlay(url, {
      total: el.dataset.total,
      remaining: el.dataset.remaining,
      noReplayConfirm: el.hasAttribute('data-no-replay-confirm'),
    })).catch(() => window.location.assign(url));
  } catch (_) {
    window.location.assign(url);
  }
});

/* Game-over popup is shown on the last answer assign (before result), not on the result page itself. */
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

  // Board lifelines handled directly in blade pages
});

/* Free-trial leave warning + subscribe locks */
(() => {
  const leaveRoot = document.querySelector('[data-free-leave-guard]');
  if (leaveRoot) {
    const message = leaveRoot.dataset.freeLeaveMessage
      || 'إذا طلعت الحين بتنتهي تجربتك المجانية، وحق تلعب فئة ثانية لازم تشترك. متأكد تبي تطلع؟';

    const guardNavigate = async (url) => {
      const ok = typeof window.showConfirm === 'function'
        ? await window.showConfirm(message)
        : window.confirm(message);
      if (ok) window.location.href = url;
    };

    document.querySelectorAll('[data-free-leave-link]').forEach((link) => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        guardNavigate(link.href);
      });
    });

    // Generic: leaving play pages via logo/back outside board actions
    leaveRoot.querySelectorAll('a[href]').forEach((link) => {
      if (link.hasAttribute('data-free-leave-link')) return;
      if (link.hasAttribute('data-ignore-free-leave')) return;
      const href = link.getAttribute('href') || '';
      if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
      // Stay inside current game
      if (href.includes('/game/')) return;

      link.addEventListener('click', (e) => {
        e.preventDefault();
        guardNavigate(link.href);
      });
    });
  }

  document.querySelectorAll('[data-subscribe-lock]').forEach((el) => {
    el.addEventListener('click', async (e) => {
      e.preventDefault();
      const msg = el.dataset.subscribeMessage
        || document.querySelector('[data-subscribe-guard]')?.dataset.subscribeMessage
        || 'انتهت فئتك المجانية. اشترك عشان تقدر تلعب فئات ثانية.';
      if (typeof window.showPopup === 'function') {
        await window.showPopup(msg, 'error');
      }
      window.location.href = el.getAttribute('href') || '/subscribe';
    });
  });

  const startForm = document.querySelector('[data-free-start-confirm]');
  if (startForm) {
    startForm.addEventListener('submit', async (e) => {
      if (startForm.dataset.confirmed === '1') return;
      e.preventDefault();
      const msg = startForm.dataset.freeStartMessage
        || 'هذي فئتك المجانية الوحيدة. متأكد تبي تبدأ؟';
      const ok = typeof window.showConfirm === 'function'
        ? await window.showConfirm(msg)
        : window.confirm(msg);
      if (!ok) return;
      startForm.dataset.confirmed = '1';
      startForm.requestSubmit();
    });
  }
})();
