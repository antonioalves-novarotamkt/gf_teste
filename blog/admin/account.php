<?php
require_once __DIR__ . '/auth.php';
require_login();
$u = current_user();
$pdo = db();
$err = ''; $ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $cur = $_POST['current'] ?? '';
  $new = $_POST['new'] ?? '';
  $conf = $_POST['confirm'] ?? '';
  if (!password_verify($cur, $u['pass_hash'])) {
    $err = 'A senha atual está incorreta.';
  } elseif (strlen($new) < 6) {
    $err = 'A nova senha precisa ter pelo menos 6 caracteres.';
  } elseif ($new !== $conf) {
    $err = 'A confirmação não bate com a nova senha.';
  } else {
    $pdo->prepare("UPDATE users SET pass_hash = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
    $ok = 'Senha alterada com sucesso.';
  }
}
?>
<!DOCTYPE html><html lang="pt-BR"><head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Minha conta — Painel · <?= e(SITE_NAME) ?></title>
<link rel="icon" type="image/png" href="../assets/gf-symbol.png" />
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="admin.css" />
</head><body>
<div class="topbar">
  <img src="../assets/gf-symbol.png" alt="GF" />
  <div class="tt">Minha conta<span>Goellner Ferreira · Blog</span></div>
  <div class="sp"></div>
  <a class="tl" href="index.php">← Voltar ao painel</a>
</div>
<div class="wrap" style="max-width:560px">
  <div class="page-head"><div><h1>Minha conta</h1><div class="muted"><?= e($u['name']) ?> · login <b><?= e($u['username']) ?></b></div></div></div>
  <?php if ($ok): ?><div class="flash ok"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <div class="card-box">
    <h3>Trocar senha</h3>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
      <div class="field"><label>Senha atual</label><input type="password" name="current" required autocomplete="current-password" /></div>
      <div class="field"><label>Nova senha</label><input type="password" name="new" minlength="6" required autocomplete="new-password" placeholder="mínimo 6 caracteres" /></div>
      <div class="field"><label>Confirmar nova senha</label><input type="password" name="confirm" minlength="6" required autocomplete="new-password" /></div>
      <div class="save-bar" style="position:static"><button type="submit" class="btn btn-save">Salvar nova senha</button></div>
    </form>
  </div>
</div>
</body></html>
