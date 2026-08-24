/* ===== GAME PLAY — SEEN JEEM UAE ===== */

// Inject gradient defs into timer SVG
(function(){
  const svg = document.querySelector('.timer__ring');
  const defs = document.createElementNS('http://www.w3.org/2000/svg','defs');
  defs.innerHTML = `<linearGradient id="fireGrad" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0%" stop-color="#FFB300"/>
    <stop offset="50%" stop-color="#FF6D00"/>
    <stop offset="100%" stop-color="#FF1744"/>
  </linearGradient>`;
  svg.prepend(defs);
})();

// ===== TIMER =====
const TOTAL = 30;
const CIRCUM = 2 * Math.PI * 52;
const bar = document.getElementById('timerBar');
const val = document.getElementById('timerValue');
const timerEl = document.getElementById('timer');
bar.style.strokeDasharray = CIRCUM;
let remaining = TOTAL;
let interval;

function tick(){
  remaining--;
  val.textContent = remaining;
  bar.style.strokeDashoffset = CIRCUM * (1 - remaining/TOTAL);
  if (remaining <= 5) timerEl.classList.add('warn');
  if (remaining <= 0){ clearInterval(interval); reveal(false); }
}
function startTimer(){ interval = setInterval(tick, 1000); }
startTimer();

// ===== ANSWER SELECTION =====
const answers = document.querySelectorAll('.answer');
const actionBar = document.getElementById('actionBar');
const confirmBtn = document.getElementById('confirmBtn');
const skipBtn = document.getElementById('skipBtn');
const assignPanel = document.getElementById('assignPanel');

let selected = null;
let locked = false;

answers.forEach(a => a.addEventListener('click', ()=>{
  if (locked) return;
  answers.forEach(x=>x.classList.remove('selected'));
  a.classList.add('selected');
  selected = a;
  actionBar.hidden = false;
}));

confirmBtn.addEventListener('click', ()=> reveal(true));
skipBtn.addEventListener('click', ()=> reveal(false));

function reveal(userChose){
  locked = true;
  clearInterval(interval);
  answers.forEach(a=>{
    if (a.dataset.correct === 'true') a.classList.add('correct');
    else if (userChose && a === selected) a.classList.add('wrong');
  });
  actionBar.hidden = true;
  setTimeout(()=>{ assignPanel.hidden = false; assignPanel.scrollIntoView({behavior:'smooth'}); }, 700);
}

// ===== ASSIGN POINTS =====
document.querySelectorAll('.assign-btn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    console.log('Awarded to:', btn.textContent.trim());
    // In Laravel: POST /game/answer with team_id + question_id
    // Then load next question
  });
});
