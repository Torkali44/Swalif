/* ===== RESULT PAGE — CONFETTI ANIMATION ===== */
const canvas = document.getElementById('confetti');
const ctx = canvas.getContext('2d');
let W, H, particles = [];
const COLORS = ['#FF1744','#F4C842','#00E5FF','#00C853','#FF2D95','#7C3AED','#FFB300'];

function resize(){ W = canvas.width = innerWidth; H = canvas.height = innerHeight; }
resize(); addEventListener('resize', resize);

function spawn(n){
  for (let i=0;i<n;i++){
    particles.push({
      x: Math.random()*W,
      y: -20 - Math.random()*H*0.5,
      vx: (Math.random()-0.5)*3,
      vy: 2 + Math.random()*4,
      size: 6 + Math.random()*8,
      rot: Math.random()*Math.PI*2,
      vr: (Math.random()-0.5)*0.2,
      color: COLORS[Math.floor(Math.random()*COLORS.length)],
      shape: Math.random() > 0.5 ? 'rect' : 'circle',
      life: 1
    });
  }
}
spawn(160);
// Continuous light burst
setInterval(()=> spawn(6), 400);

function loop(){
  ctx.clearRect(0,0,W,H);
  particles = particles.filter(p => p.y < H + 40);
  particles.forEach(p=>{
    p.x += p.vx; p.y += p.vy; p.rot += p.vr; p.vy += 0.03;
    ctx.save();
    ctx.translate(p.x, p.y);
    ctx.rotate(p.rot);
    ctx.fillStyle = p.color;
    if (p.shape==='rect'){
      ctx.fillRect(-p.size/2, -p.size/4, p.size, p.size/2);
    } else {
      ctx.beginPath(); ctx.arc(0,0,p.size/2,0,Math.PI*2); ctx.fill();
    }
    ctx.restore();
  });
  requestAnimationFrame(loop);
}
loop();
