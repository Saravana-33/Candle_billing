<?php
$PAGE='payments';
require_once __DIR__.'/config.php';

if ($_SERVER['REQUEST_METHOD']==='POST'){
    $bid=(int)($_POST['bill_id']??0);
    $q  =(string)($_POST['q']??'');
    if(($_POST['action']??'')==='pay' && $bid){
        $mode=in_array($_POST['mode']??'', ['Cash','Card','UPI'], true)?$_POST['mode']:'Cash';
        $pdo->prepare("UPDATE bills SET status='PAID',payment_mode=? WHERE id=?")->execute([$mode,$bid]);
    }
    header('Location: payments.php'.($q!==''?'?q='.urlencode($q):'')); exit;
}

require __DIR__.'/header.php';
$q=trim($_GET['q']??'');
$like=$q===''?'%':'%'.$q.'%';
$sql="SELECT b.id,c.name,b.total,b.date,b.payment_mode
      FROM bills b JOIN customers c ON c.id=b.customer_id
      WHERE b.status=:st AND c.name LIKE :q ORDER BY b.id DESC";
$st=$pdo->prepare($sql);
$st->execute(['st'=>'UNPAID','q'=>$like]); $un=$st->fetchAll();
$st->execute(['st'=>'PAID',  'q'=>$like]); $pd=$st->fetchAll();
$unSum=0; foreach($un as $r) $unSum+=$r['total'];
?>
<h1 class="page-title">Payments</h1>

<form class="frame" method="get" style="padding:12px 14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
  <label style="margin:0">🔍 Search Customer:</label>
  <input type="text" name="q" value="<?= e($q) ?>" style="max-width:260px" placeholder="customer name...">
  <button class="btn btn-blue" type="submit">Search</button>
  <a class="btn btn-gray" href="payments.php">Show All</a>
</form>

<div class="sum-card">
  <div class="t">⚠ UNPAID BILLS</div>
  <div style="display:flex;justify-content:space-between;align-items:baseline">
    <div class="v"><?= count($un) ?> Bills</div>
    <div class="v"><?= money($unSum) ?></div>
  </div>
</div>

<div class="grid-split">
  <div class="frame" style="margin:0">
    <div class="list-head">UNPAID LIST</div>
    <table class="tbl" id="unTbl">
      <thead><tr><th>Bill #</th><th>Customer Name</th><th>Amount</th><th>Date</th><th>Mode</th></tr></thead>
      <tbody>
      <?php foreach($un as $r): ?>
        <tr class="clickable" data-id="<?= $r['id'] ?>">
          <td><?= $r['id'] ?></td><td><?= e($r['name']) ?></td>
          <td><?= money($r['total']) ?></td>
          <td><?= date('d-m-Y',strtotime($r['date'])) ?></td>
          <td><?= e($r['payment_mode']) ?></td>
        </tr>
      <?php endforeach; if(!$un): ?>
        <tr><td colspan="5" class="empty">No unpaid bills 🎉</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="frame" style="margin:0">
    <div class="list-head green">PAID LIST</div>
    <table class="tbl" id="paidTbl">
      <thead><tr><th>Bill #</th><th>Customer Name</th><th>Amount</th><th>Date</th><th>Mode</th></tr></thead>
      <tbody>
      <?php foreach($pd as $r): ?>
        <tr class="clickable" data-id="<?= $r['id'] ?>">
          <td><?= $r['id'] ?></td><td><?= e($r['name']) ?></td>
          <td><?= money($r['total']) ?></td>
          <td><?= date('d-m-Y',strtotime($r['date'])) ?></td>
          <td><?= e($r['payment_mode']) ?></td>
        </tr>
      <?php endforeach; if(!$pd): ?>
        <tr><td colspan="5" class="empty">No paid bills yet</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<form method="post" id="payForm">
  <input type="hidden" name="action" value="pay">
  <input type="hidden" name="bill_id" id="p_bid">
  <input type="hidden" name="mode"    id="p_mode">
  <input type="hidden" name="q" value="<?= e($q) ?>">
  <div class="row" style="padding:14px 0 0">
    <button type="button" class="btn btn-green" style="flex:1" onclick="processPay()">💰 Process Payment</button>
    <button type="button" class="btn btn-accent" style="flex:1" onclick="printRec()">🖨 Print Receipt</button>
  </div>
</form>

<script>
function hook(id,set){
  document.querySelectorAll('#'+id+' tr.clickable').forEach(tr=>{
    tr.addEventListener('click',()=>{
      document.querySelectorAll('#'+id+' tr.clickable').forEach(x=>x.classList.remove('selected'));
      tr.classList.add('selected'); set(tr.dataset.id);
    });
  });
}
let selUn=null, selPaid=null;
hook('unTbl',  v=>selUn=v);
hook('paidTbl',v=>selPaid=v);
function processPay(){
  if(!selUn) return alert('Select an UNPAID bill first');
  const m=askMode(); if(!m) return;
  p_bid.value=selUn; p_mode.value=m;
  document.getElementById('payForm').submit();
}
function printRec(){
  if(!selPaid) return alert('Select a PAID bill first');
  window.open('invoice.php?id='+selPaid+'&receipt=1','_blank');
}
</script>
<?php include __DIR__.'/footer.php'; ?>
