<?php
$PAGE='newbill';
require __DIR__.'/header.php';
$items = $pdo->query("SELECT name, price FROM items ORDER BY name")->fetchAll();
?>
<h1 class="page-title">New Bill</h1>
<div class="grid-2">
  <!-- LEFT -->
  <div>
    <div class="frame"><div class="frame-title">👤 Customer Details</div><div class="frame-body">
      <label>Name</label>
      <div class="pos-rel">
        <input type="text" id="cname" autocomplete="off" placeholder="type to search existing customers...">
        <div id="sug" class="suggest" style="display:none"></div>
      </div>
      <label>Phone</label>
      <input type="text" id="cphone" maxlength="10" placeholder="10 digits (optional)">
      <label>Address</label>
      <input type="text" id="caddr" placeholder="optional">
    </div></div>

    <div class="frame"><div class="frame-title">➕ Add Item</div><div class="frame-body">
      <label>Item</label>
      <input type="text" id="item" list="itemList" autocomplete="off">
      <datalist id="itemList">
        <?php foreach($items as $i): ?><option value="<?= e($i['name']) ?>"></option><?php endforeach; ?>
      </datalist>
      <div style="display:flex;gap:10px">
        <div style="flex:1"><label>Qty</label><input type="number" id="qty" value="1" min="1"></div>
        <div style="flex:1"><label>Price (₹)</label><input type="number" id="price" step="0.01" min="0"></div>
      </div>
    </div></div>

    <div class="row" style="padding:0 0 4px">
      <button class="btn btn-accent" style="flex:1" onclick="addToCart()">＋ Add to Cart</button>
      <button class="btn btn-red" style="flex:1" onclick="clearAll()">🗑 Clear All</button>
    </div>
  </div>

  <!-- RIGHT -->
  <div>
    <div class="frame">
      <div class="frame-title">🛒 Current Cart</div>
      <table class="tbl">
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody id="cartBody"></tbody>
      </table>
      <div class="row">
        <button class="btn btn-blue" onclick="editQty()">✏ Edit Qty</button>
        <button class="btn btn-red" onclick="removeItem()">✖ Remove</button>
      </div>
    </div>

    <div class="grand"><span style="font-weight:700">Grand Total:</span><b id="grand">₹0.00</b></div>

    <div class="frame"><div class="frame-title">💳 Payment Mode</div>
      <div class="frame-body radio-row">
        <label><input type="radio" name="mode" value="Cash" checked> 💵 Cash</label>
        <label><input type="radio" name="mode" value="Card"> 💳 Card</label>
        <label><input type="radio" name="mode" value="UPI"> 📱 UPI</label>
      </div>
    </div>

    <div class="row" style="padding:0">
      <button class="btn btn-green" style="flex:1" onclick="saveBill('UNPAID')">🖨 Print&nbsp; [F5]</button>
      <button class="btn btn-accent" style="flex:1" onclick="saveBill('PAID')">💰 Pay&nbsp; [F7]</button>
    </div>
  </div>
</div>

<script>
const PRICE   = <?= json_encode(array_column($items,'price','name')) ?>;
const cname   = document.getElementById('cname'),
      phoneEl = document.getElementById('cphone'),
      addrEl  = document.getElementById('caddr'),
      itemEl  = document.getElementById('item'),
      qtyEl   = document.getElementById('qty'),
      priceEl = document.getElementById('price'),
      sug     = document.getElementById('sug');

const rs  = n => '₹'+Number(n).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
const esc = s => String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

/* price auto-fill from item list */
itemEl.addEventListener('change', ()=>{
  const p = PRICE[itemEl.value.trim()];
  if (p != null) priceEl.value = Number(p).toFixed(2);
});

/* customer autocomplete */
cname.addEventListener('input', async ()=>{
  const q = cname.value.trim();
  if (!q){ sug.style.display='none'; return; }
  const res  = await fetch('api.php?action=customers&q='+encodeURIComponent(q));
  const list = await res.json();
  if (!list.length){ sug.style.display='none'; return; }
  sug.innerHTML = list.map(c =>
    `<div data-n="${esc(c.name)}" data-p="${esc(c.phone)}" data-a="${esc(c.address)}">
       ${esc(c.name)} &nbsp;|&nbsp; ${esc(c.phone)||'-'}</div>`).join('');
  sug.style.display = 'block';
});
sug.addEventListener('click', ev=>{
  const d = ev.target.closest('div[data-n]'); if(!d) return;
  cname.value=d.dataset.n; phoneEl.value=d.dataset.p; addrEl.value=d.dataset.a;
  sug.style.display='none';
});
document.addEventListener('click', ev=>{ if(!ev.target.closest('.pos-rel')) sug.style.display='none'; });

/* cart */
let cart=[], selIdx=null;
function renderCart(){
  const b=document.getElementById('cartBody');
  b.innerHTML = cart.length ? cart.map((it,i)=>
    `<tr class="clickable ${i===selIdx?'selected':''}" data-i="${i}">
       <td>${esc(it.name)}</td><td>${it.qty}</td>
       <td>${rs(it.price)}</td><td>${rs(it.qty*it.price)}</td></tr>`).join('')
    : '<tr><td colspan="4" class="empty">Cart is empty</td></tr>';
  document.getElementById('grand').textContent =
    rs(cart.reduce((s,i)=>s+i.qty*i.price,0));
  b.querySelectorAll('tr[data-i]').forEach(tr=>tr.addEventListener('click',()=>{
    selIdx=+tr.dataset.i; renderCart();
  }));
}
function addToCart(){
  const name=itemEl.value.trim();
  if(!name) return alert('Choose or type an item name');
  const q=parseInt(qtyEl.value,10), p=parseFloat(priceEl.value);
  if(!(q>0)) return alert('Enter a valid quantity');
  if(isNaN(p)||p<0) return alert('Enter a valid price');
  cart.push({name,qty:q,price:p}); selIdx=cart.length-1;
  qtyEl.value=1; renderCart();
}
function editQty(){
  if(selIdx==null) return alert('Select an item in the cart');
  const q=prompt('New quantity:',cart[selIdx].qty);
  if(q===null) return;
  const n=parseInt(q,10);
  if(!(n>0)) return alert('Invalid quantity');
  cart[selIdx].qty=n; renderCart();
}
function removeItem(){
  if(selIdx==null) return alert('Select an item in the cart');
  cart.splice(selIdx,1); selIdx=null; renderCart();
}
function clearAll(){
  if(!cart.length) return;
  if(confirm('Remove all items from cart?')){ cart=[]; selIdx=null; renderCart(); }
}

/* save bill (Print = UNPAID, Pay = PAID) */
async function saveBill(status){
  const name=cname.value.trim();
  if(!name) return alert('Enter customer name');
  if(!cart.length) return alert('Cart is empty — add items first');
  const phone=phoneEl.value.trim(), address=addrEl.value.trim();
  if(phone && !/^\d{10}$/.test(phone)) return alert('Phone must be 10 digits (or leave blank)');
  const mode=document.querySelector('input[name=mode]:checked').value;
  const res=await fetch('api.php?action=save_bill',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({name,phone,address,mode,status,items:cart})
  });
  const r=await res.json();
  if(!r.ok) return alert(r.msg||'Error saving bill');
  if(status==='PAID'){
    if(confirm('Bill #'+r.id+' saved as PAID.\nPrint receipt now?'))
      location.href='invoice.php?id='+r.id+'&receipt=1&print=1';
    else location.reload();
  } else {
    location.href='invoice.php?id='+r.id+'&print=1';
  }
}
window.onF5=()=>saveBill('UNPAID');
window.onF7=()=>saveBill('PAID');
renderCart();
</script>
<?php include __DIR__.'/footer.php'; ?>
