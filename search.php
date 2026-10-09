<?php
$PAGE='search';
require_once __DIR__.'/config.php';

/* POST actions: toggle status / delete */
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $bid=(int)($_POST['bill_id']??0);
    $q  =(string)($_POST['q']??'');
    $act=$_POST['action']??'';
    if($act==='toggle' && $bid){
        $st=$pdo->prepare("SELECT status FROM bills WHERE id=?"); $st->execute([$bid]);
        if($st->fetchColumn()==='UNPAID'){
            $mode=in_array($_POST['mode']??'', ['Cash','Card','UPI'], true)?$_POST['mode']:'Cash';
            $pdo->prepare("UPDATE bills SET status='PAID',payment_mode=? WHERE id=?")->execute([$mode,$bid]);
        }else{
            $pdo->prepare("UPDATE bills SET status='UNPAID',payment_mode='-' WHERE id=?")->execute([$bid]);
        }
    }
    if($act==='delete' && $bid){
        $pdo->prepare("DELETE FROM bills WHERE id=?")->execute([$bid]); /* items cascade */
    }
    header('Location: search.php'.($q!==''?'?q='.urlencode($q):'')); exit;
}

require __DIR__.'/header.php';
$q=trim($_GET['q']??'');
$st=$pdo->prepare("SELECT b.id,c.name,b.total,b.date,b.status,b.payment_mode
                   FROM bills b JOIN customers c ON c.id=b.customer_id
                   WHERE c.name LIKE ? ORDER BY b.id DESC LIMIT 300");
$st->execute([$q===''?'%':'%'.$q.'%']);
$rows=$st->fetchAll();
?>
<h1 class="page-title">Search Bills</h1>

<form class="frame" method="get" style="padding:12px 14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
  <label style="margin:0">🔍 Search Customer:</label>
  <input type="text" name="q" value="<?= e($q) ?>" style="max-width:280px" placeholder="customer name...">
  <button class="btn btn-blue" type="submit">Search</button>
  <a class="btn btn-gray" href="search.php">Show All</a>
</form>

<div class="frame">
  <div class="frame-title">Bills (<?= count($rows) ?>)</div>
  <table class="tbl" id="billTbl">
    <thead><tr><th>Bill #</th><th>Customer</th><th>Amount</th><th>Date</th><th>Status</th><th>Mode</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr class="clickable" data-id="<?= $r['id'] ?>" data-status="<?= $r['status'] ?>">
        <td><?= $r['id'] ?></td><td><?= e($r['name']) ?></td>
        <td><?= money($r['total']) ?></td>
        <td><?= date('d-m-Y H:i',strtotime($r['date'])) ?></td>
        <td class="<?= strtolower($r['status']) ?>"><?= $r['status'] ?></td>
        <td><?= e($r['payment_mode']) ?></td>
      </tr>
    <?php endforeach; if(!$rows): ?>
      <tr><td colspan="6" class="empty">No bills found</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  <form method="post" id="actForm">
    <input type="hidden" name="bill_id" id="f_bid">
    <input type="hidden" name="action"  id="f_act">
    <input type="hidden" name="mode"    id="f_mode">
    <input type="hidden" name="q" value="<?= e($q) ?>">
    <div class="row">
      <button type="button" class="btn btn-accent" onclick="viewBill()">View Details</button>
      <button type="button" class="btn btn-green" onclick="toggleStatus()">Mark Paid / Unpaid</button>
      <button type="button" class="btn btn-red" onclick="delBill()">Delete Bill</button>
      <span class="spacer"></span>
      <small class="muted">Tip: click a row to select · double-click to view</small>
    </div>
  </form>
</div>

<script>
let sel=null;
document.querySelectorAll('#billTbl tr.clickable').forEach(tr=>{
  tr.addEventListener('click',()=>{
    document.querySelectorAll('#billTbl tr.clickable').forEach(x=>x.classList.remove('selected'));
    tr.classList.add('selected'); sel=tr;
  });
  tr.addEventListener('dblclick',()=>location.href='invoice.php?id='+tr.dataset.id);
});
function needSel(){ if(!sel){ alert('Select a bill first'); return false; } return true; }
function submit(action,mode){
  f_bid.value=sel.dataset.id; f_act.value=action; f_mode.value=mode||'';
  document.getElementById('actForm').submit();
}
function viewBill(){ if(needSel()) location.href='invoice.php?id='+sel.dataset.id; }
function toggleStatus(){
  if(!needSel()) return;
  if(sel.dataset.status==='UNPAID'){
    const m=askMode(); if(m) submit('toggle',m);
  } else if(confirm('Mark bill #'+sel.dataset.id+' as UNPAID?')){
    submit('toggle');
  }
}
function delBill(){
  if(!needSel()) return;
  if(confirm('Delete bill #'+sel.dataset.id+' and its items permanently?')) submit('delete');
}
</script>
<?php include __DIR__.'/footer.php'; ?>
