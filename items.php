<?php
 $PAGE='items';
require_once __DIR__.'/config.php';

if ($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    if($act==='add'){
        $name =trim($_POST['name']??'');
        $price=(float)($_POST['price']??0);
        $cost =(float)($_POST['cost']??0);
        if($name!=='' && $price>=0 && $cost>=0)
            $pdo->prepare("INSERT IGNORE INTO items(name,price,cost) VALUES(?,?,?)")
                ->execute([$name,$price,$cost]);
        header('Location: items.php?added=1'); exit;
    }
    if($act==='edit'){
        $id=(int)($_POST['id']??0);
        $price=(float)($_POST['price']??0);
        $cost =(float)($_POST['cost']??0);
        if($id && $price>=0 && $cost>=0)
            $pdo->prepare("UPDATE items SET price=?, cost=? WHERE id=?")
                ->execute([$price,$cost,$id]);
        header('Location: items.php?updated=1'); exit;
    }
    if($act==='delete'){
        $id=(int)($_POST['id']??0);
        if($id) $pdo->prepare("DELETE FROM items WHERE id=?")->execute([$id]);
        header('Location: items.php?deleted=1'); exit;
    }
}

require __DIR__.'/header.php';
 $rows=$pdo->query("SELECT * FROM items ORDER BY name")->fetchAll();
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
  <h1 class="page-title" style="margin:0">📦 Manage Items (<?= count($rows) ?>)</h1>
</div>

<?php if(isset($_GET['added'])):   ?><div class="frame" style="border-left:4px solid #2e7d32"><div class="frame-body">✅ Item added successfully</div></div><?php endif; ?>
<?php if(isset($_GET['updated'])): ?><div class="frame" style="border-left:4px solid #1565c0"><div class="frame-body">✅ Item updated successfully</div></div><?php endif; ?>
<?php if(isset($_GET['deleted'])): ?><div class="frame" style="border-left:4px solid #c62828"><div class="frame-body">🗑 Item deleted</div></div><?php endif; ?>

<div class="frame">
  <div class="frame-title">➕ Add New Item</div>
  <form method="post" class="row" style="flex-wrap:wrap">
    <input type="hidden" name="action" value="add">
    <input type="text" name="name" placeholder="Item name" required style="flex:2;min-width:200px">
    <input type="number" name="price" placeholder="Selling price ₹" step="0.01" min="0" required style="flex:1;min-width:130px">
    <input type="number" name="cost" placeholder="Cost price ₹" step="0.01" min="0" required style="flex:1;min-width:130px">
    <button class="btn btn-green" type="submit">Save Item</button>
  </form>
</div>

<div class="frame">
  <div class="frame-title">All Items</div>
  <table class="tbl">
    <thead><tr><th>#</th><th>Item Name</th><th>Selling Price</th><th>Cost Price</th>
               <th>Profit/Unit</th><th>Margin</th><th style="width:170px">Actions</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r):
        $p=(float)$r['price']; $c=(float)$r['cost'];
        $pr=$p-$c; $mg=$p>0?($pr/$p*100):0; ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><b><?= e($r['name']) ?></b></td>
        <td><?= money($p) ?></td>
        <td><?= money($c) ?></td>
        <td class="<?= $pr>=0?'paid':'unpaid' ?>"><?= money($pr) ?></td>
        <td><?= number_format($mg,1) ?>%</td>
        <td>
          <form method="post" style="display:inline-flex;gap:4px">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <input type="number" name="price" value="<?= $p ?>" step="0.01" style="width:80px;padding:4px">
            <input type="number" name="cost" value="<?= $c ?>" step="0.01" style="width:80px;padding:4px">
            <button class="btn btn-blue" type="submit" style="padding:4px 10px">Save</button>
          </form>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete this item?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn btn-red" type="submit" style="padding:4px 10px">✖</button>
          </form>
        </td>
      </tr>
    <?php endforeach; if(!$rows): ?>
      <tr><td colspan="7" class="empty">No items found</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__.'/footer.php'; ?>