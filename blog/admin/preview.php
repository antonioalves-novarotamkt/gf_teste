<?php
/* Pré-visualização de um post (rascunho, agendado ou publicado) — só para logados. */
require_once __DIR__ . '/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$pdo = db();
$stmt = $pdo->prepare("SELECT p.*, u.name AS author_name, u.role AS author_role, u.avatar AS author_avatar
  FROM posts p LEFT JOIN users u ON u.id=p.author_id WHERE p.id = ? LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) { header('Location: index.php'); exit; }

$now = date('Y-m-d H:i:s');
$isLive = $post['status'] === 'published' && (!$post['publish_at'] || $post['publish_at'] <= $now);
$avatar = author_avatar($post['author_avatar']);

if ($post['status'] === 'draft') { $bannerTxt = '📝 Prévia de RASCUNHO — este post ainda não está publicado.'; }
elseif ($post['publish_at'] && $post['publish_at'] > $now) { $bannerTxt = '📅 Prévia de post AGENDADO para ' . fmt_date($post['publish_at'], true) . '.'; }
else { $bannerTxt = '✅ Este post já está PUBLICADO no blog.'; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Prévia — <?= e($post['title']) ?></title>
<meta name="robots" content="noindex" />
<link rel="icon" type="image/png" href="../assets/gf-symbol.png" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500;1,600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="../blog.css" />
<style>
  .preview-banner{position:sticky;top:0;z-index:80;display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;background:#0a2150;color:#fff;padding:12px 20px;font-size:13.5px;font-weight:600;text-align:center;}
  .preview-banner a{color:#6cbfe0;font-weight:700;text-decoration:none;}
  .preview-banner a:hover{text-decoration:underline;}
</style>
</head>
<body>
  <div class="preview-banner">
    <span><?= e($bannerTxt) ?></span>
    <a href="edit.php?id=<?= (int)$post['id'] ?>">← Voltar para editar</a>
  </div>

  <div class="article-head">
    <div class="wrap article-head-inner">
      <div><span class="chip"><?= e($post['category']) ?></span></div>
      <h1><?= e($post['title']) ?></h1>
      <div class="article-byline">
        <img class="av" src="<?= e($avatar) ?>" alt="<?= e($post['author_name']) ?>" />
        <div><div class="who"><?= e($post['author_name'] ?: SITE_NAME) ?></div><div class="when"><?= fmt_date($post['publish_at'] ?: $post['created_at'], true) ?></div></div>
      </div>
    </div>
  </div>

  <?php if (!empty($post['cover'])): ?>
    <div class="article-cover"><div class="frame"><img src="<?= e($post['cover']) ?>" alt="<?= e($post['title']) ?>" /></div></div>
  <?php endif; ?>

  <article class="article-body"<?= empty($post['cover']) ? ' style="padding-top:clamp(40px,5vw,64px)"' : '' ?>>
    <?= clean_body($post['body']) ?>
  </article>

  <div class="article-foot" style="margin-bottom:60px">
    <div class="author-box">
      <img class="av" src="<?= e($avatar) ?>" alt="<?= e($post['author_name']) ?>" />
      <div><div class="role"><?= e($post['author_role'] ?: 'Goellner Ferreira') ?></div><h4><?= e($post['author_name'] ?: SITE_NAME) ?></h4><p>Goellner Ferreira — Consultoria &amp; Auditoria</p></div>
    </div>
  </div>
</body>
</html>
