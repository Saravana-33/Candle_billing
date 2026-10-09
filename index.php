<?php
$PAGE='dashboard';
require __DIR__.'/header.php';

$revenue = $pdo->query("SELECT IFNULL(SUM(total),0) FROM bills")->fetchColumn();
$today   = $pdo->query("SELECT IFNULL(SUM(total),0) FROM bills WHERE DATE(date)=CURDATE()")->fetchColumn();
$nbills  = $pdo->query("SELECT COUNT(*) FROM bills")->fetchColumn();
$ncust   = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

$recent  = $pdo->query("SELECT b.id, c.name, b.date, b.total, b.status
                        FROM bills b JOIN customers c ON c.id=b.customer_id
                        ORDER BY b.id DESC LIMIT 10")->fetchAll();

$month = array_fill(1,12,0);
foreach($pdo->query("SELECT MONTH(date) m, SUM(total) s FROM bills
                     WHERE YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)") as $r){
    $month[(int)$r['m']] = (float)$r['s'];
}
?>
<h1 class="page-title">Dashboard</h1>

<div class="stat-grid">
  <div class="stat s-green"><span>∉ Total Revenue</span><b><?= money($revenue) ?></b></div>
  <div class="stat s-pink"><span>▤ Today's Sales</span><b><?= money($today) ?></b></div>
  <div class="stat s-blue"><span>▦ Total Bills</span><b><?= (int)$nbills ?></b></div>
  <div class="stat s-yellow"><span>👥 Customers</span><b><?= (int)$ncust ?></b></div>
</div>

<div class="frame">
  <div class="frame-title">▦ Monthly Revenue (<?= date('Y') ?>)</div>
  <div class="frame-body" id="chartWrap"><canvas id="revChart" height="95"></canvas></div>
</div>

<div class="frame">
  <div class="frame-title">🧾 Recent Bills</div>
  <table class="tbl" id="recentTbl">
    <thead><tr><th>#</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($recent as $r): ?>
      <tr class="clickable" data-id="<?= $r['id'] ?>">
        <td><?= $r['id'] ?></td>
        <td><?= e($r['name']) ?></td>
        <td><?= date('d-m-Y H:i', strtotime($r['date'])) ?></td>
        <td><?= money($r['total']) ?></td>
        <td class="<?= strtolower($r['status']) ?>"><?= $r['status'] ?></td>
      </tr>
    <?php endforeach; if(!$recent): ?>
      <tr><td colspan="5" class="empty">No bills yet — create your first bill!</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  <div class="row">
    <button class="btn btn-accent" onclick="viewSelected()">View Bill&nbsp; [F5]</button>
    <span class="spacer"></span>
    <button class="btn btn-blue" onclick="location.reload()">⟳ Refresh&nbsp; [Shift+V]</button>
  </div>
</div>

<script>
const ACCENT = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#C2185B';
const monthlyData = <?= json_encode(array_values($month)) ?>;
if (window.Chart) {
  new Chart(document.getElementById('revChart'), {
    type:'bar',
    data:{ labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
           datasets:[{ data:monthlyData, backgroundColor:ACCENT }]},
    options:{ plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
  });
} else {
  document.getElementById('chartWrap').innerHTML =
    '<div class="empty">Chart.js not loaded (check internet or download chart.umd.js locally)</div>';
}

let selId = null;
document.querySelectorAll('#recentTbl tr.clickable').forEach(tr=>{
  tr.addEventListener('click', ()=>{
    document.querySelectorAll('#recentTbl tr.clickable').forEach(x=>x.classList.remove('selected'));
    tr.classList.add('selected'); selId = tr.dataset.id;
  });
  tr.addEventListener('dblclick', ()=> location.href='invoice.php?id='+tr.dataset.id);
});
function viewSelected(){
  if(selId) location.href = 'invoice.php?id='+selId;
  else alert('Select a bill from Recent Bills first');
}
window.onF5 = viewSelected;
window.onRefresh = ()=>location.reload();
</script>
<?php include __DIR__.'/footer.php'; ?>
