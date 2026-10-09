<?php
$PAGE='profit';
require __DIR__.'/header.php';

$t=$pdo->query("SELECT IFNULL(SUM(bi.total),0) rev, IFNULL(SUM(bi.cost*bi.qty),0) cost
                FROM bill_items bi JOIN bills b ON b.id=bi.bill_id")->fetch();
$rev=(float)$t['rev']; $cost=(float)$t['cost'];
$pro=$rev-$cost; $mar=$rev>0?($pro/$rev*100):0;

$revM=array_fill(1,12,0.0); $costM=array_fill(1,12,0.0);
foreach($pdo->query("SELECT MONTH(b.date) m, SUM(bi.total) rev, SUM(bi.cost*bi.qty) cost
                     FROM bill_items bi JOIN bills b ON b.id=bi.bill_id
                     WHERE YEAR(b.date)=YEAR(CURDATE()) GROUP BY MONTH(b.date)") as $r){
    $revM[(int)$r['m']]=(float)$r['rev']; $costM[(int)$r['m']]=(float)$r['cost'];
}
$MONTHS=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$ACCENT="getComputedStyle(document.documentElement).getPropertyValue('--accent').trim()||'#C2185B'";
?>
<h1 class="page-title">📈 Profit Analytics</h1>

<div class="stat-grid">
  <div class="stat s-green"><span>Total Revenue</span><b><?= money($rev) ?></b></div>
  <div class="stat s-blue"><span>Total Cost</span><b><?= money($cost) ?></b></div>
  <div class="stat s-pink"><span>Total Profit</span><b><?= money($pro) ?></b></div>
  <div class="stat s-yellow"><span>Profit Margin</span><b><?= number_format($mar,1) ?>%</b></div>
</div>

<div class="frame">
  <div class="frame-title">Revenue vs Profit (Monthly — <?= date('Y') ?>)</div>
  <div class="frame-body" id="chartWrap"><canvas id="proChart" height="110"></canvas></div>
</div>

<div class="frame">
  <div class="frame-title">Monthly Summary</div>
  <table class="tbl">
    <thead><tr><th>Month</th><th>Revenue</th><th>Cost</th><th>Profit</th></tr></thead>
    <tbody>
    <?php foreach($MONTHS as $k=>$m): $i=$k+1; ?>
      <tr><td><?= $m ?></td>
          <td><?= money($revM[$i]) ?></td>
          <td><?= money($costM[$i]) ?></td>
          <td class="<?= ($revM[$i]-$costM[$i])>=0?'paid':'unpaid' ?>">
              <?= money($revM[$i]-$costM[$i]) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
if (window.Chart) {
  const A = <?= $ACCENT ?>;
  new Chart(document.getElementById('proChart'), {
    type:'bar',
    data:{ labels:<?= json_encode($MONTHS) ?>,
      datasets:[
        {label:'Revenue', data:<?= json_encode(array_values($revM)) ?>,  backgroundColor:A},
        {label:'Profit',  data:<?= json_encode(array_values(array_map(fn($r,$c)=>$r-$c,$revM,$costM))) ?>, backgroundColor:'#2e7d32'}
      ]},
    options:{ scales:{y:{beginAtZero:true}} }
  });
} else {
  document.getElementById('chartWrap').innerHTML='<div class="empty">Chart.js not loaded</div>';
}
</script>
<?php include __DIR__.'/footer.php'; ?>
