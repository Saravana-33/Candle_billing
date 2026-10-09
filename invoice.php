<?php
require_once __DIR__.'/config.php';
require_login();

$id=(int)($_GET['id']??0);
$receipt=!empty($_GET['receipt']);

$st=$pdo->prepare("SELECT b.*, c.name cname, c.phone cphone
                   FROM bills b JOIN customers c ON c.id=b.customer_id WHERE b.id=?");
$st->execute([$id]); $b=$st->fetch();
if(!$b) die('<h3 style="padding:20px">Bill not found. <a href="index.php">Back</a></h3>');

$it=$pdo->prepare("SELECT * FROM bill_items WHERE bill_id=?");
$it->execute([$id]); $items=$it->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Bill #<?= $id ?></title>
<link rel="stylesheet" href="style.css"></head>
<body style="background:var(--bg)">

<div class="no-print" style="max-width:760px;margin:14px auto 0;display:flex;gap:10px;align-items:center">
  <button class="btn btn-green" onclick="window.print()">🖨 Print</button>
  <button class="btn btn-gray" onclick="history.back()">← Back</button>
  <span class="spacer"></span>
  <span class="badge <?= strtolower($b['status']) ?>"><?= $b['status'] ?></span>
</div>

<div class="sheet">
  <div class="inv-head">
    <div class="n"><?= strtolower(e(SHOP_NAME)) ?></div>
    <div class="t"><?= e(SHOP_TAG) ?></div>
    <div class="a"><?= e(SHOP_ADDR) ?> &nbsp;|&nbsp; Ph: <?= e(SHOP_PHONE) ?></div>
  </div>

  <div class="inv-meta">
    <div><b>Bill No:</b> #<?= str_pad($b['id'],5,'0',STR_PAD_LEFT) ?><br>
         <b>Date:</b> <?= date('d-m-Y h:i A', strtotime($b['date'])) ?></div>
    <div style="text-align:right"><b>Customer:</b> <?= e($b['cname']) ?><br>
         <?php if($b['cphone']): ?><b>Phone:</b> <?= e($b['cphone']) ?><?php endif; ?></div>
  </div>

  <?php if(!$receipt): ?>
  <table class="tbl">
    <thead><tr><th>#</th><th>Item</th><th>Qty</th><th>Price (₹)</th><th>Total (₹)</th></tr></thead>
    <tbody>
    <?php foreach($items as $k=>$r): ?>
      <tr><td><?= $k+1 ?></td><td><?= e($r['item_name']) ?></td>
          <td><?= $r['qty'] ?></td>
          <td><?= number_format($r['price'],2) ?></td>
          <td><?= number_format($r['total'],2) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <div class="inv-total"><span>GRAND TOTAL:</span><b><?= money($b['total']) ?></b></div>

  <div style="display:flex;justify-content:space-between;padding:0 4px">
    <span><b>Payment Mode:</b> <?= e($b['payment_mode']) ?></span>
    <span class="badge <?= strtolower($b['status']) ?>"><?= $b['status'] ?></span>
  </div>

  <div class="inv-foot">
    <?= $receipt ? 'Thank you for your payment!' : 'Thank You! Visit Again ♥' ?><br>
    <small><?= e(SHOP_NAME) ?> · Enterprise POS</small>
  </div>
</div>

<?php if(isset($_GET['print'])): ?>
<script>window.addEventListener('load',()=>setTimeout(()=>window.print(),400));</script>
<?php endif; ?>
</body></html>
