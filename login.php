<?php
require_once __DIR__.'/config.php';
if (!empty($_SESSION['user'])) { header('Location: index.php'); exit; }
$err='';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $u=trim($_POST['username']??''); $p=$_POST['password']??'';
    $st=$pdo->prepare("SELECT password FROM users WHERE username=?");
    $st->execute([$u]);
    if($u!=='' && $st->rowCount() && password_verify($p,$st->fetchColumn())){
        $_SESSION['user']=$u; header('Location: index.php'); exit;
    }
    $err='Invalid username or password';
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Login - <?= e(SHOP_NAME) ?></title>
<link rel="stylesheet" href="style.css"></head>
<body class="login-body">
  <form class="login-card" method="post" autofocus>
    <div class="login-head">
      <div class="n"><?= strtolower(e(SHOP_NAME)) ?></div>
      <div class="t"><?= e(SHOP_TAG) ?></div>
    </div>
    <div class="login-body-in">
      <?php if($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <br><br>
      <button class="btn btn-accent w100" type="submit">Login</button>
    </div>
  </form>
</body></html>

