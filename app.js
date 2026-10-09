/* ---------- Theme (Change Color) ---------- */
const THEMES=[
 {accent:'#C2185B',dark:'#8E1247',light:'#F8BBD0'},
 {accent:'#1565C0',dark:'#0D3E7A',light:'#BBDEFB'},
 {accent:'#2E7D32',dark:'#1B4D1E',light:'#C8E6C9'},
 {accent:'#6A1B9A',dark:'#451064',light:'#E1BEE7'},
 {accent:'#EF6C00',dark:'#9A4700',light:'#FFE0B2'}];
let ti=parseInt(localStorage.getItem('ti')||'0',10)||0;
function applyTheme(i){
  const t=THEMES[i%THEMES.length], r=document.documentElement.style;
  r.setProperty('--accent',t.accent);
  r.setProperty('--accent-dark',t.dark);
  r.setProperty('--accent-light',t.light);
  localStorage.setItem('ti',i);
}
applyTheme(ti);
document.getElementById('changeColor')?.addEventListener('click',()=>{
  ti=(ti+1)%THEMES.length; applyTheme(ti);
});

/* ---------- Clock ---------- */
function tick(){
  const d=new Date();
  const D=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  const M=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  let h=d.getHours(), ap=h>=12?'PM':'AM'; h=h%12||12;
  const el=document.getElementById('clock');
  if(el) el.textContent=`${D[d.getDay()]}, ${String(d.getDate()).padStart(2,'0')} ${M[d.getMonth()]} | ${h}:${String(d.getMinutes()).padStart(2,'0')} ${ap}`;
}
setInterval(tick,1000); tick();

/* ---------- Keyboard shortcuts ---------- */
const NAV={F1:'index.php',F2:'new_bill.php',F3:'search.php',
  F4:'payments.php',F6:'items.php',F8:'profit.php',F9:'customers.php'};
document.addEventListener('keydown',e=>{
  if(NAV[e.key]){ e.preventDefault(); location.href=NAV[e.key]; return; }
  if(e.key==='F5' && window.onF5){ e.preventDefault(); window.onF5(); return; }
  if(e.key==='F7' && window.onF7){ e.preventDefault(); window.onF7(); return; }
  const t=(e.target.tagName||'').toLowerCase();
  const typing=(t==='input'||t==='textarea'||t==='select'||e.target.isContentEditable);
  if(e.key==='V' && e.shiftKey && !typing && window.onRefresh){
    e.preventDefault(); window.onRefresh();
  }
});

/* ---------- Shared helpers ---------- */
function askMode(){
  let m;
  while(true){
    m=prompt('Payment mode (Cash / Card / UPI):','Cash');
    if(m===null) return null;
    m=m.trim();
    if(['Cash','Card','UPI'].includes(m)) return m;
    alert('Please enter Cash, Card or UPI');
  }
}
