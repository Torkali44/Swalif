/* ===== CATEGORY PAGE — SEEN JEEM UAE ===== */
const CATEGORIES = [
  // UAE
  { id:'uae-emirates',   name:'فئات الإمارات',       icon:'🇦🇪', type:'uae',      questions:120, popularity:98, isNew:false, hot:true,  c1:'#FF1744', c2:'#B00020' },
  { id:'uae-malls',      name:'مولات الإمارات',      icon:'🏬', type:'uae',      questions:85,  popularity:76, isNew:false, hot:false, c1:'#FF6D00', c2:'#B00020' },
  { id:'uae-cafes',      name:'كوفيهات الإمارات',    icon:'☕', type:'uae',      questions:60,  popularity:82, isNew:true,  hot:false, c1:'#8D6E63', c2:'#3E2723' },
  { id:'uae-restaurants',name:'مطاعم الإمارات',      icon:'🍽️', type:'uae',      questions:72,  popularity:70, isNew:false, hot:false, c1:'#E64A19', c2:'#7B1F1F' },
  { id:'uae-landmarks',  name:'معالم الإمارات',      icon:'🕌', type:'uae',      questions:110, popularity:95, isNew:false, hot:true,  c1:'#D4AF37', c2:'#8A6D1B' },
  { id:'uae-tourism',    name:'أماكن سياحية',        icon:'🏖️', type:'uae',      questions:90,  popularity:80, isNew:false, hot:false, c1:'#00BCD4', c2:'#006064' },
  { id:'uae-parks',      name:'حدائق ومنتزهات',      icon:'🌴', type:'uae',      questions:45,  popularity:60, isNew:true,  hot:false, c1:'#00C853', c2:'#004D40' },
  { id:'uae-hotels',     name:'فنادق ومنتجعات',      icon:'🏨', type:'uae',      questions:55,  popularity:65, isNew:false, hot:false, c1:'#7C3AED', c2:'#3D1E75' },
  { id:'uae-mosques',    name:'المساجد في الإمارات', icon:'🕋', type:'religion', questions:40,  popularity:74, isNew:false, hot:false, c1:'#00C853', c2:'#1B5E20' },
  { id:'uae-museums',    name:'المتاحف في الإمارات', icon:'🏛️', type:'uae',      questions:38,  popularity:55, isNew:false, hot:false, c1:'#5D4037', c2:'#2E1810' },
  { id:'uae-dates',      name:'التمور الإماراتية',   icon:'🌰', type:'uae',      questions:32,  popularity:50, isNew:true,  hot:false, c1:'#A0522D', c2:'#3E2723' },
  { id:'uae-foods',      name:'الأكلات الشعبية',     icon:'🍲', type:'uae',      questions:58,  popularity:78, isNew:false, hot:false, c1:'#FF6D00', c2:'#BF360C' },
  { id:'uae-proverbs',   name:'أمثال إماراتية',      icon:'💬', type:'uae',      questions:66,  popularity:72, isNew:false, hot:false, c1:'#D4AF37', c2:'#7C3AED' },
  { id:'uae-cars',       name:'سيارات الإمارات',     icon:'🏎️', type:'uae',      questions:48,  popularity:68, isNew:false, hot:false, c1:'#263238', c2:'#00E5FF' },

  // General
  { id:'quran',          name:'أكمل الآية القرآنية', icon:'📖', type:'religion', questions:200, popularity:92, isNew:false, hot:true,  c1:'#00C853', c2:'#1B5E20' },
  { id:'seerah',         name:'السيرة النبوية',      icon:'🕌', type:'religion', questions:150, popularity:85, isNew:false, hot:false, c1:'#2E7D32', c2:'#0D3D1A' },
  { id:'watches',        name:'ساعات',               icon:'⌚', type:'general',  questions:40,  popularity:52, isNew:true,  hot:false, c1:'#455A64', c2:'#1C313A' },
  { id:'perfumes',       name:'عطور عالمية',         icon:'🌸', type:'general',  questions:55,  popularity:64, isNew:false, hot:false, c1:'#FF2D95', c2:'#7C3AED' },
  { id:'animals',        name:'عالم الحيوان',        icon:'🦁', type:'general',  questions:80,  popularity:75, isNew:false, hot:false, c1:'#F57C00', c2:'#6D4C1B' },
  { id:'football',       name:'كرة القدم',           icon:'⚽', type:'sport',    questions:180, popularity:96, isNew:false, hot:true,  c1:'#00E5FF', c2:'#0064B7' },
  { id:'disney',         name:'ديزني',               icon:'🏰', type:'fun',      questions:95,  popularity:88, isNew:false, hot:true,  c1:'#FF2D95', c2:'#7C3AED' },
  { id:'flags',          name:'أعلام الدول',         icon:'🚩', type:'general',  questions:120, popularity:79, isNew:false, hot:false, c1:'#FF1744', c2:'#00843D' },
  { id:'coins',          name:'عملات',               icon:'🪙', type:'general',  questions:65,  popularity:58, isNew:true,  hot:false, c1:'#D4AF37', c2:'#8A6D1B' },
  { id:'guess-image',    name:'خمن الصورة',          icon:'🖼️', type:'media',    questions:110, popularity:90, isNew:false, hot:true,  c1:'#7C3AED', c2:'#3D1E75' },
  { id:'guess-place',    name:'خمن المكان',          icon:'📍', type:'media',    questions:88,  popularity:81, isNew:false, hot:false, c1:'#00BCD4', c2:'#0064B7' },
  { id:'color-image',    name:'لون الصورة',          icon:'🎨', type:'media',    questions:70,  popularity:66, isNew:true,  hot:false, c1:'#FF2D95', c2:'#00E5FF' },
  { id:'guess-sound',    name:'خمن الصوت',           icon:'🔊', type:'media',    questions:75,  popularity:73, isNew:false, hot:false, c1:'#00E5FF', c2:'#7C3AED' },
  { id:'ordering',       name:'ترتيب',               icon:'🔢', type:'fun',      questions:60,  popularity:56, isNew:false, hot:false, c1:'#F4C842', c2:'#B00020' },
  { id:'puzzles',        name:'ألغاز',               icon:'🧩', type:'fun',      questions:100, popularity:83, isNew:false, hot:false, c1:'#7C3AED', c2:'#FF2D95' },
];

const TYPE_LABEL = { uae:'إمارات', general:'عامة', religion:'دينية', sport:'رياضة', fun:'ترفيه', media:'صور/صوت' };

let activeFilter = 'all';
let activeSort = 'popular';
let searchTerm = '';

const grid = document.getElementById('categoryGrid');
const emptyState = document.getElementById('emptyState');

function render(){
  let list = CATEGORIES.filter(c=>{
    if (activeFilter !== 'all' && c.type !== activeFilter) return false;
    if (searchTerm && !c.name.includes(searchTerm)) return false;
    return true;
  });

  list.sort((a,b)=>{
    if (activeSort==='popular')   return b.popularity - a.popularity;
    if (activeSort==='new')       return (b.isNew?1:0) - (a.isNew?1:0);
    if (activeSort==='az')        return a.name.localeCompare(b.name,'ar');
    if (activeSort==='questions') return b.questions - a.questions;
    return 0;
  });

  grid.innerHTML = list.map(c => `
    <article class="card" style="--c1:${c.c1};--c2:${c.c2}" data-id="${c.id}">
      ${c.hot ? '<span class="card__badge">🔥 الأكثر لعبًا</span>' : c.isNew ? '<span class="card__badge">✨ جديد</span>' : ''}
      <span class="card__tag">${TYPE_LABEL[c.type] || 'عامة'}</span>
      <div class="card__icon">${c.icon}</div>
      <div>
        <h3 class="card__title">${c.name}</h3>
        <div class="card__meta">
          <span>📝 ${c.questions} سؤال</span>
          <span class="card__levels" title="سهل / متوسط / صعب">
            <i class="on"></i><i class="on"></i><i class="on"></i>
          </span>
        </div>
      </div>
    </article>
  `).join('');

  emptyState.hidden = list.length > 0;
}

// filters
document.getElementById('filters').addEventListener('click', e=>{
  const btn = e.target.closest('.pill'); if(!btn) return;
  document.querySelectorAll('.pill').forEach(p=>p.classList.remove('active'));
  btn.classList.add('active');
  activeFilter = btn.dataset.filter;
  render();
});

document.getElementById('sortSelect').addEventListener('change', e=>{
  activeSort = e.target.value; render();
});

document.getElementById('searchInput').addEventListener('input', e=>{
  searchTerm = e.target.value.trim(); render();
});

grid.addEventListener('click', e=>{
  const card = e.target.closest('.card'); if(!card) return;
  console.log('Open category:', card.dataset.id);
  // In Laravel: window.location = `/categories/${card.dataset.id}`
});

render();
