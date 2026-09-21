<?php
require_once __DIR__ . '/auth.php';

if (current_user()) { header('Location: index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = trim($_POST['username'] ?? '');
  $pass = $_POST['password'] ?? '';
  $stmt = db()->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
  $stmt->execute([$user]);
  $u = $stmt->fetch();
  if ($u && password_verify($pass, $u['pass_hash'])) {
    session_regenerate_id(true);
    $_SESSION['uid'] = $u['id'];
    header('Location: index.php');
    exit;
  }
  $err = 'Usuário ou senha incorretos.';
}
?>
<!DOCTYPE html><html lang="pt-BR"><head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Entrar — Painel do Blog · <?= e(SITE_NAME) ?></title>
<link rel="icon" type="image/png" href="../assets/gf-symbol.png" />
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<style>
  *{box-sizing:border-box;margin:0;padding:0;font-family:"Montserrat",sans-serif;}
  body{background:linear-gradient(150deg,#0a2150,#16387f 55%,#1f8a82);min-height:100vh;display:grid;place-items:center;padding:24px;color:#14203f;position:relative;overflow:hidden;}
  body::before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:54px 54px;-webkit-mask-image:radial-gradient(120% 80% at 70% 0%,#000,transparent 72%);mask-image:radial-gradient(120% 80% at 70% 0%,#000,transparent 72%);}
  .box{position:relative;background:#fff;border-radius:18px;max-width:400px;width:100%;padding:38px 34px;box-shadow:0 30px 70px rgba(0,0,0,.32);}
  .logo{height:46px;margin:0 auto 22px;display:block;}
  h1{font-size:21px;color:#0a2150;text-align:center;margin-bottom:4px;font-weight:800;}
  .sub{font-size:13px;color:#8992a8;text-align:center;margin-bottom:24px;}
  label{display:block;font-size:12.5px;font-weight:700;color:#56607a;margin:14px 0 6px;}
  input{width:100%;font-size:15px;padding:12px 14px;border:1.5px solid rgba(10,33,80,.16);border-radius:10px;}
  input:focus{outline:none;border-color:#ff5757;box-shadow:0 0 0 3px rgba(255,87,87,.12);}
  .btn{margin-top:22px;width:100%;background:#ff5757;color:#fff;border:none;font-weight:700;font-size:15px;padding:13px;border-radius:999px;cursor:pointer;transition:filter .2s;}
  .btn:hover{filter:brightness(1.05);}
  .err{background:#fff6f6;border:1px solid #f0c0c0;color:#8a1f1f;padding:11px 13px;border-radius:9px;font-size:13.5px;margin-bottom:6px;text-align:center;}
  .back{display:block;text-align:center;margin-top:18px;font-size:13px;color:#8992a8;text-decoration:none;}
  .back:hover{color:#16387f;}
</style></head><body>
<div class="box">
  <img class="logo" src="../assets/gf-symbol.png" alt="Goellner Ferreira" />
  <h1>Painel do Blog</h1>
  <div class="sub">Goellner Ferreira</div>
  <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
  <form method="post">
    <label>Usuário</label>
    <input type="text" name="username" autocomplete="username" autofocus required placeholder="silvana ou jonatas" />
    <label>Senha</label>
    <input type="password" name="password" autocomplete="current-password" required />
    <button class="btn" type="submit">Entrar</button>
  </form>
  <a class="back" href="<?= e(SITE_URL) ?>">← Voltar ao site</a>
</div>
</body></html>
