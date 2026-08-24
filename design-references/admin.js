/* ============================================================
   Admin Dashboard Logic (Demo — localStorage)
   في Laravel: استبدل fetch إلى /admin/categories و /admin/questions
   ============================================================ */

const STORAGE = {
  cats: 'sj_admin_cats',
  qs:   'sj_admin_qs',
};

const seedCats = [
  { id: 1, name_ar: 'تاريخ الإمارات', name_en: 'UAE History', icon: '🕌', color: '#FF1744', description: 'تاريخ الاتحاد والقادة', active: true },
  { id: 2, name_ar: 'رياضة', name_en: 'Sports', icon: '⚽', color: '#00C853', description: 'كرة قدم وبطولات', active: true },
  { id: 3, name_ar: 'جغرافيا', name_en: 'Geography', icon: '🌍', color: '#7C3AED', description: 'دول ومدن ومعالم', active: true },
  { id: 4, name_ar: 'أفلام', name_en: 'Movies', icon: '🎬', color: '#FF2D95', description: 'سينما عربية وعالمية', active: false },
  { id: 5, name_ar: 'علوم', name_en: 'Science', icon: '🔬', color: '#00E5FF', description: 'اكتشافات وحقائق', active: true },
  { id: 6, name_ar: 'ألغاز', name_en: 'Riddles', icon: '🧩', color: '#FFB800', description: 'ذكاء ومنطق', active: true },
];

const seedQs = [
  { id: 1, category_id: 1, level: 200, text: 'في أي عام قامت دولة الإمارات؟', answer: '1971', hint: 'بداية السبعينات', timer: 60, active: true },
  { id: 2, category_id: 1, level: 400, text: 'من هو مؤسس الاتحاد؟', answer: 'الشيخ زايد بن سلطان آل نهيان', hint: '', timer: 60, active: true },
  { id: 3, category_id: 2, level: 200, text: 'كم لاعباً في فريق كرة القدم؟', answer: '11', hint: '', timer: 45, active: true },
  { id: 4, category_id: 3, level: 600, text: 'ما أعلى قمة في العالم؟', answer: 'إفرست', hint: 'في نيبال', timer: 60, active: true },
  { id: 5, category_id: 5, level: 400, text: 'ما رمز الذهب في الكيمياء؟', answer: 'Au', hint: '', timer: 45, active: true },
];

// ---------- Store ----------
const load = k => JSON.parse(localStorage.getItem(k) || 'null');
const save = (k, v) => localStorage.setItem(k, JSON.stringify(v));
if (!load(STORAGE.cats)) save(STORAGE.cats, seedCats);
if (!load(STORAGE.qs))   save(STORAGE.qs,   seedQs);

const State = {
  get cats() { return load(STORAGE.cats) || []; },
  set cats(v){ save(STORAGE.cats, v); },
  get qs()   { return load(STORAGE.qs) || []; },
  set qs(v)  { save(STORAGE.qs, v); },
};

const $ = s => document.querySelector(s);
const $$ = s => document.querySelectorAll(s);
const nextId = arr => (arr.reduce((m, x) => Math.max(m, x.id), 0) + 1);
const toast = msg => {
  const t = $('#toast'); t.textContent = msg; t.hidden = false;
  clearTimeout(toast._t); toast._t = setTimeout(() => t.hidden = true, 2200);
};

// ---------- Navigation ----------
const titles = {
  dashboard: ['نظرة عامة', 'ملخص سريع عن اللعبة والمحتوى'],
  categories: ['الفئات', 'إضافة وتعديل وحذف الفئات'],
  questions: ['الأسئلة', 'إدارة الأسئلة وتعيين مستوى كل سؤال'],
  plans: ['الاشتراكات', 'إدارة الباقات والأسعار'],
  users: ['المستخدمون', 'قائمة المشتركين'],
};

function showView(name) {
  $$('.view').forEach(v => v.classList.toggle('active', v.dataset.view === name));
  $$('.nav-link').forEach(l => l.classList.toggle('active', l.dataset.view === name));
  const [t, s] = titles[name] || ['', ''];
  $('#viewTitle').textContent = t; $('#viewSub').textContent = s;
  $('#topAddBtn').style.display = (name === 'categories' || name === 'questions') ? '' : 'none';
  $('#topAddBtn').onclick = () => name === 'categories' ? openCatModal() : openQModal();
  location.hash = name;
}
document.addEventListener('click', e => {
  const link = e.target.closest('[data-view]');
  if (link) { e.preventDefault(); showView(link.dataset.view); }
});

// ---------- Categories render ----------
let catFilter = 'all';
function renderCats() {
  const grid = $('#catGrid');
  const cats = State.cats.filter(c =>
    catFilter === 'all' ? true : catFilter === 'active' ? c.active : !c.active
  );
  const qs = State.qs;
  grid.innerHTML = cats.map(c => `
    <div class="cat-card">
      <span class="status-dot ${c.active ? '' : 'off'}"></span>
      <div class="cat-ico" style="background:${c.color}">${c.icon || '📚'}</div>
      <h4>${c.name_ar}</h4>
      <div class="cat-count">${qs.filter(q => q.category_id === c.id).length} سؤال</div>
      <div class="cat-actions">
        <button class="icon-btn" data-edit-cat="${c.id}" title="تعديل">✏️</button>
        <button class="icon-btn" data-toggle-cat="${c.id}" title="تفعيل/إيقاف">${c.active ? '⏸️' : '▶️'}</button>
        <button class="icon-btn danger" data-del-cat="${c.id}" title="حذف">🗑️</button>
      </div>
    </div>
  `).join('') || `<p class="muted">لا توجد فئات — أضف واحدة!</p>`;
}
$$('.chip').forEach(ch => ch.onclick = () => {
  $$('.chip').forEach(c => c.classList.remove('active'));
  ch.classList.add('active');
  catFilter = ch.dataset.filter; renderCats();
});

// ---------- Category modal ----------
const catModal = $('#catModal'), catForm = $('#catForm');
function openCatModal(cat) {
  catForm.reset();
  $('#catModalTitle').textContent = cat ? 'تعديل الفئة' : 'فئة جديدة';
  if (cat) {
    Object.entries(cat).forEach(([k, v]) => {
      const el = catForm.elements[k]; if (!el) return;
      if (el.type === 'checkbox') el.checked = v; else el.value = v;
    });
  } else {
    catForm.elements.active.checked = true;
    catForm.elements.color.value = '#FF1744';
  }
  catModal.classList.add('open');
}
$('#addCatBtn').onclick = () => openCatModal();
catForm.onsubmit = e => {
  e.preventDefault();
  const fd = new FormData(catForm);
  const data = Object.fromEntries(fd);
  data.active = catForm.elements.active.checked;
  const cats = State.cats;
  if (data.id) {
    const i = cats.findIndex(c => c.id == data.id);
    cats[i] = { ...cats[i], ...data, id: +data.id };
    toast('تم تحديث الفئة ✓');
  } else {
    delete data.id;
    cats.push({ id: nextId(cats), ...data });
    toast('تمت إضافة الفئة ✓');
  }
  State.cats = cats;
  catModal.classList.remove('open');
  renderAll();
};

// ---------- Questions render ----------
function renderQs() {
  const cats = State.cats;
  const catMap = Object.fromEntries(cats.map(c => [c.id, c]));
  const catFilterVal = $('#filterCat').value;
  const lvlFilterVal = $('#filterLevel').value;
  const rows = State.qs.filter(q =>
    (!catFilterVal || q.category_id == catFilterVal) &&
    (!lvlFilterVal || q.level == lvlFilterVal)
  );
  $('#qTbody').innerHTML = rows.map(q => {
    const c = catMap[q.category_id];
    return `
      <tr>
        <td>${q.id}</td>
        <td>${c ? `<span style="display:inline-flex;align-items:center;gap:6px"><span style="background:${c.color};width:22px;height:22px;border-radius:6px;display:inline-grid;place-items:center;color:#fff">${c.icon}</span>${c.name_ar}</span>` : '—'}</td>
        <td style="max-width:340px">${q.text}</td>
        <td><strong>${q.answer}</strong></td>
        <td><span class="badge-level lvl-${q.level}">${q.level}</span></td>
        <td>
          <button class="icon-btn" data-edit-q="${q.id}">✏️</button>
          <button class="icon-btn danger" data-del-q="${q.id}">🗑️</button>
        </td>
      </tr>
    `;
  }).join('') || `<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted)">لا أسئلة مطابقة</td></tr>`;
}

function fillCatSelects() {
  const cats = State.cats;
  const opts = cats.map(c => `<option value="${c.id}">${c.icon} ${c.name_ar}</option>`).join('');
  $('#filterCat').innerHTML = `<option value="">كل الفئات</option>` + opts;
  $('#qCatSelect').innerHTML = opts;
}
$('#filterCat').onchange = renderQs;
$('#filterLevel').onchange = renderQs;

// ---------- Question modal ----------
const qModal = $('#qModal'), qForm = $('#qForm');
function openQModal(q) {
  qForm.reset();
  $('#qModalTitle').textContent = q ? 'تعديل السؤال' : 'سؤال جديد';
  fillCatSelects();
  if (q) {
    Object.entries(q).forEach(([k, v]) => {
      if (k === 'level') { const r = qForm.querySelector(`[name=level][value="${v}"]`); if (r) r.checked = true; return; }
      const el = qForm.elements[k]; if (!el) return;
      if (el.type === 'checkbox') el.checked = v; else el.value = v;
    });
  } else {
    qForm.elements.active.checked = true;
    qForm.elements.timer.value = 60;
  }
  qModal.classList.add('open');
}
$('#addQBtn').onclick = () => openQModal();
qForm.onsubmit = e => {
  e.preventDefault();
  const fd = new FormData(qForm);
  const data = Object.fromEntries(fd);
  data.active = qForm.elements.active.checked;
  data.category_id = +data.category_id;
  data.level = +data.level;
  data.timer = +data.timer;
  const qs = State.qs;
  if (data.id) {
    const i = qs.findIndex(x => x.id == data.id);
    qs[i] = { ...qs[i], ...data, id: +data.id };
    toast('تم تحديث السؤال ✓');
  } else {
    delete data.id;
    qs.push({ id: nextId(qs), ...data });
    toast('تمت إضافة السؤال ✓');
  }
  State.qs = qs;
  qModal.classList.remove('open');
  renderAll();
};

// ---------- Global event delegation ----------
document.addEventListener('click', e => {
  const t = e.target;
  if (t.matches('[data-close]') || t.classList.contains('modal')) {
    t.closest('.modal')?.classList.remove('open');
    if (t.classList.contains('modal')) t.classList.remove('open');
  }
  if (t.dataset.editCat) openCatModal(State.cats.find(c => c.id == t.dataset.editCat));
  if (t.dataset.toggleCat) {
    const cats = State.cats; const c = cats.find(x => x.id == t.dataset.toggleCat);
    c.active = !c.active; State.cats = cats; renderAll();
    toast(c.active ? 'تم التفعيل ✓' : 'تم الإيقاف');
  }
  if (t.dataset.delCat) {
    if (confirm('حذف الفئة وكل أسئلتها؟')) {
      const id = +t.dataset.delCat;
      State.cats = State.cats.filter(c => c.id !== id);
      State.qs = State.qs.filter(q => q.category_id !== id);
      renderAll(); toast('تم الحذف');
    }
  }
  if (t.dataset.editQ) openQModal(State.qs.find(q => q.id == t.dataset.editQ));
  if (t.dataset.delQ) {
    if (confirm('حذف السؤال؟')) {
      State.qs = State.qs.filter(q => q.id != t.dataset.delQ);
      renderAll(); toast('تم الحذف');
    }
  }
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') $$('.modal.open').forEach(m => m.classList.remove('open'));
});

// ---------- Dashboard ----------
function renderDashboard() {
  $('#statCats').textContent = State.cats.length;
  $('#statQs').textContent = State.qs.length;
  const catMap = Object.fromEntries(State.cats.map(c => [c.id, c]));
  const recent = [...State.qs].slice(-5).reverse();
  $('#recentQs').innerHTML = recent.map(q => {
    const c = catMap[q.category_id];
    return `<div class="mini-item">
      <div>
        <div class="q">${q.text}</div>
        <div class="meta"><span>${c ? c.icon + ' ' + c.name_ar : '—'}</span><span class="badge-level lvl-${q.level}">${q.level}</span></div>
      </div>
      <button class="icon-btn" data-edit-q="${q.id}">✏️</button>
    </div>`;
  }).join('') || '<p class="muted">لا توجد أسئلة بعد</p>';
}

// ---------- Init ----------
function renderAll() { fillCatSelects(); renderCats(); renderQs(); renderDashboard(); }
renderAll();
showView(location.hash.replace('#', '') || 'dashboard');
