<?php
$PAGE='customers';
require_once __DIR__.'/config.php';

if ($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    if($act==='add'){
        $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??''); $addr=trim($_POST['address']??'');
        if($name!=='')
            $pdo->prepare("INSERT INTO customers(name,phone,address) VALUES(?,?,?)")
                ->execute([$name,$phone,$addr]);
        header('Location: customers.php'); exit;
    }
    if($act==='phone'){
        $id=(int)($_POST['id']??0); $phone=trim($_POST['phone']??'');
        if($id && ($phone==='' || preg_match('/^\d{10}$/',$phone)))
            $pdo->prepare("UPDATE customers SET phone=? WHERE id=?")->execute([$phone,$id]);
        header('Location: customers.php?id='.$id); exit;
    }
}

require __DIR__.'/header.php';

$list=$pdo->query("SELECT c.id,c.name,c.phone,
                     IFNULL(SUM(b.total),0) biz, COUNT(b.id) n
                   FROM customers c LEFT JOIN bills b ON b.customer_id=c.id
                   GROUP BY c.id ORDER BY c.name")->fetchAll();

$selId=(int)($_GET['id']??0);
$sel=null; foreach($list as $c) if($c['id']==$selId){ $sel=$c; break; }
$paid=0;$unpaid=0;$most=null;$hist=[];
if($sel){
    $paid  =(int)$pdo->query("SELECT COUNT(*) FROM bills WHERE customer_id=$selId AND status='PAID'")->fetchColumn();
    $unpaid=(int)$pdo->query("SELECT COUNT(*) FROM bills WHERE customer_id=$selId AND status='UNPAID'")->fetchColumn();
    $st=$pdo->prepare("SELECT bi.item_name, SUM(bi.qty) tq, SUM(bi.total) ts
                       FROM bill_items bi JOIN bills b ON b.id=bi.bill_id
                       WHERE b.customer_id=? GROUP BY bi.item_name ORDER BY tq DESC LIMIT 1");
    $st->execute([$selId]); $most=$st->fetch();
    $st=$pdo->prepare("SELECT id,date,total,status FROM bills WHERE customer_id=? ORDER BY id DESC");
    $st->execute([$selId]); $hist=$st->fetchAll();
}
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
  <h1 class="page-title" style="margin:0">👥 Customer Explorer</h1>
  <button class="btn btn-green" onclick="document.getElementById('addDlg').showModal()">＋ Add Customer</button>
</div>

<div class="grid-2">
  <div class="frame">
    <div class="frame-title">Select Customer</div>
    <div class="frame-body">
      <?php foreach($list as $c): ?>
        <a class="cust-item <?= $c['id']==$selId?'active':'' ?>" href="customers.php?id=<?= $c['id'] ?>">
          <b>👤 <?= e($c['name']) ?></b>
          <span class="meta">&nbsp;📞 <?= e($c['phone']?:'-') ?>
            (<?= money($c['biz']) ?> | <?= (int)$c['n'] ?> bills)</span>
        </a>
      <?php endforeach; if(!$list): ?>
        <div class="empty">No customers yet — add one!</div>
      <?php endif; ?>
    </div>
  </div>

  <div>
    <?php if(!$sel): ?>
      <div class="frame"><div class="frame-body empty">Select a customer to view history</div></div>
    <?php else: ?>
      <div class="frame"><div class="frame-title">Details for: <?= e($sel['name']) ?></div>
        <div class="frame-body">
          <div class="stat-grid" style="margin:0">
            <div class="stat s-blue"><span>📞 Phone</span><b style="font-size:16px"><?= e($sel['phone']?:'N/A') ?></b></div>
            <div class="stat s-green"><span>Paid Bills</span><b><?= $paid ?></b></div>
            <div class="stat s-pink"><span>Unpaid Bills</span><b><?= $unpaid ?></b></div>
            <div class="stat s-yellow"><span>Total Business</span><b style="font-size:16px"><?= money($sel['biz']) ?></b></div>
          </div>
          <form method="post" style="margin-top:10px">
            <input type="hidden" name="action" value="phone">
            <input type="hidden" name="id" value="<?= $selId ?>">
            <input type="hidden" name="phone" id="newPhone">
            <button type="button" class="btn btn-blue" onclick="editPhone()">✏ Edit Phone</button>
          </form>
        </div>
      </div>

      <div class="frame"><div class="frame-title">★ Most Purchased Item</div>
        <table class="tbl">
          <thead><tr><th>Item Name</th><th>Total Qty</th><th>Total Spent</th></tr></thead>
          <tbody>
          <?php if($most): ?>
            <tr><td><?= e($most['item_name']) ?></td>
                <td><?= (int)$most['tq'] ?></td>
                <td><?= money($most['ts']) ?></td></tr>
          <?php else: ?><tr><td colspan="3" class="empty">No purchases yet</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="frame"><div class="frame-title">🧾 Bill History</div>
        <table class="tbl">
          <thead><tr><th>Bill #</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($hist as $h): ?>
            <tr class="clickable" onclick="location.href='invoice.php?id=<?= $h['id'] ?>'">
              <td><?= $h['id'] ?></td>
              <td><?= date('d-m-Y H:i',strtotime($h['date'])) ?></td>
              <td><?= money($h['total']) ?></td>
              <td class="<?= strtolower($h['status']) ?>"><?= $h['status'] ?></td>
            </tr>
          <?php endforeach; if(!$hist): ?>
            <tr><td colspan="4" class="empty">No bills</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<dialog id="addDlg">
  <form method="post">
    <div class="dlg-head">＋ Add New Customer</div>
    <div class="dlg-body" style="min-width:280px">
      <label>Customer Name</label>
      <input type="text" name="name" required>
      <label>Phone (10 digits)</label>
      <input type="text" name="phone" maxlength="10" placeholder="10-digit number">
      <label>Address (optional)</label>
      <input type="text" name="address">
      <div class="row" style="padding:12px 0 0">
        <button class="btn btn-green" type="submit">Save Customer</button>
        <button class="btn btn-gray" type="button" onclick="document.getElementById('addDlg').close()">Cancel</button>
      </div>
    </div>
  </form>
</dialog>

<script>
function editPhone(){
  const p=prompt('New phone number (10 digits, blank to clear):','<?= e($sel['phone']??'') ?>');
  if(p===null) return;
  if(p.trim()!=='' && !/^\d{10}$/.test(p.trim())) return alert('Phone must be 10 digits');
  document.getElementById('newPhone').value=p.trim();
  document.getElementById('newPhone').form.submit();
}
</script>
<?php include __DIR__.'/footer.php'; ?>
