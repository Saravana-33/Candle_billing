<?php
require_once __DIR__.'/config.php';
require_login();
$PAGE = $PAGE ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(SHOP_NAME) ?> - Enterprise POS</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand"><?= strtolower(e(SHOP_NAME)) ?></div>
    <div class="brand-sub"><?= e(SHOP_TAG) ?></div>
    <div class="nav-label">NAVIGATION</div>
    <nav>
      <a href="index.php"     class="<?= $PAGE==='dashboard'?'active':'' ?>">▦&nbsp; Dashboard&nbsp; [F1]</a>
      <a href="new_bill.php"  class="<?= $PAGE==='newbill'?'active':'' ?>">🧾&nbsp; New Bill&nbsp; [F2]</a>
      <a href="search.php"    class="<?= $PAGE==='search'?'active':'' ?>">🔍&nbsp; Search&nbsp; [F3]</a>
      <a href="payments.php"  class="<?= $PAGE==='payments'?'active':'' ?>">💳&nbsp; Payments&nbsp; [F4]</a>
      <a href="items.php" class="<?= $PAGE==='items'?'active':'' ?>">📦&nbsp; Items&nbsp; [F6]</a>
      <a href="customers.php" class="<?= $PAGE==='customers'?'active':'' ?>">👥&nbsp; Customers&nbsp; [F9]</a>
      <a href="profit.php"    class="<?= $PAGE==='profit'?'active':'' ?>">📈&nbsp; Profit&nbsp; [F8]</a>
    </nav>
    <button id="changeColor" class="color-btn">◐ Change Color</button>
    <a class="logout" href="logout.php">⎋ Logout (<?= e($_SESSION['user']) ?>)</a>
  </aside>
  <main class="main">
    <div class="topbar">
      <span class="top-title"><?= e(SHOP_NAME) ?> · Enterprise POS</span>
      <span id="clock" class="clock"></span>
    </div>
    <div class="content">
