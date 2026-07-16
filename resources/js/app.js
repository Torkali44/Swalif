import './bootstrap';

/* Timer */
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

/* Mobile nav */
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');
if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    navLinks.classList.toggle('is-open');
  });
}

/* Category filter pills */
const pills = document.querySelectorAll('.pill[data-filter]');
const cats = document.querySelectorAll('.cat[data-cat], .cat[data-group], .cat-circle[data-cat], .cat-circle[data-group]');

pills.forEach((pill) => {
  pill.addEventListener('click', () => {
    pills.forEach((p) => p.classList.remove('is-active'));
    pill.classList.add('is-active');
    const filter = pill.dataset.filter;

    cats.forEach((cat) => {
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

  document.querySelectorAll('.cat, .cat-circle, .step, .plan').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = `opacity .55s ease ${i * 0.03}s, transform .55s ease ${i * 0.03}s`;
    io.observe(el);
  });
}
