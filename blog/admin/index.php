<?php
require_once __DIR__ . '/auth.php';
require_login();
$u = current_user();
$pdo = db();

$now = date('Y-m-d H:i:s');
$rows = $pdo->query("SELECT p.*, au.name AS author_name FROM posts p LEFT JOIN users au ON au.id=p.author_id
  ORDER BY p.featured DESC, COALESCE(p.publish_at, p.created_at) DESC")->fetchAll();

$total = count($rows);
$pub = 0; $draft = 0; $views = 0;
foreach ($rows as $r) {
  $views += (int)$r['views'];
  if ($r['status'] === 'published' && (!$r['publish_at'] || $r['publish_at'] <= $now)) $pub++;
  else $draft++;
}
$flash = flash_get();

function status_badge($r, $now){
  if ($r['status'] === 'draft') return '<span class="badge-status st-draft"><span class="d"></span>Rascunho</span>';
  if ($r['publish_at'] && $r['publish_at'] > $now) return '<span class="badge-status st-sched"><span class="d"></span>Agendado</span>';
  return '<span class="badge-status st-pub"><span class="d"></span>Publicado</span>';
}
?>
<!DOCTYPE html><html lang="pt-BR"><head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Painel do Blog — <?= e(SITE_NAME) ?></title>
<link rel="icon" type="image/png" href="../assets/gf-symbol.png" />
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="admin.css" />
</head><body>
<div class="topbar">
  <img src="../assets/gf-symbol.png" alt="GF" />
  <div class="tt">Painel do Blog<span>Goellner Ferreira</span></div>
  <div class="sp"></div>
  <span class="who">Olá, <b><?= e(explode(' ', $u['name'])[0]) ?></b></span>
  <a class="tl" href="../index.php" target="_blank">Ver blog ↗</a>
  <a class="tl" href="account.php">Minha conta</a>
  <a class="tl" href="logout.php">Sair</a>
</div>

<div class="wrap">
  <?php if ($flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>

  <div class="page-head">
    <div><h1>Seus posts</h1><div class="muted">Gerencie, edite e publique os artigos do blog.</div></div>
    <a class="btn btn-accent" href="edit.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>Novo post</a>
  </div>

  <div class="stats">
    <div class="stat"><div class="n"><?= $total ?></div><div class="l">Posts no total</div></div>
    <div class="stat"><div class="n teal"><?= $pub ?></div><div class="l">Publicados</div></div>
    <div class="stat"><div class="n coral"><?= $draft ?></div><div class="l">Rascunhos / agendados</div></div>
    <div class="stat"><div class="n"><?= number_format($views, 0, ',', '.') ?></div><div class="l">Leituras somadas</div></div>
  </div>

  <?php if ($total === 0): ?>
    <div class="ptable"><div class="empty">Nenhum post ainda. Clique em <b>“Novo post”</b> para começar a escrever.</div></div>
  <?php else: ?>
    <div class="ptable">
      <div class="prow head">
        <div></div><div>Título</div><div class="col-hide">Categoria</div><div class="col-hide">Status</div><div class="col-hide">Leituras</div><div style="text-align:right">Ações</div>
      </div>
      <?php foreach ($rows as $r):
        $cover = trim($r['cover']); ?>
        <div class="prow">
          <?php if ($cover): ?>
            <img class="thumb" src="<?= e($cover) ?>" alt="" />
          <?php else: ?>
            <div class="thumb fb"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 3v5h5M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V8l-6-5z"/></svg></div>
          <?php endif; ?>
          <div>
            <div class="pt"><?= e($r['title'] ?: '(sem título)') ?></div>
            <div class="pm"><?= e($r['author_name'] ?: '—') ?> · <?= fmt_date($r['publish_at'] ?: $r['created_at']) ?></div>
          </div>
          <div class="col-hide"><span class="tag tag-cat"><?= e($r['category']) ?></span></div>
          <div class="col-hide"><?= status_badge($r, $now) ?></div>
          <div class="col-hide"><?= (int)$r['views'] ?></div>
          <div class="acts">
            <form method="post" action="save.php" style="display:inline">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
              <input type="hidden" name="action" value="toggle_featured" />
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
              <button class="star <?= ((int)$r['featured']===1)?'on':'' ?>" title="Destaque" type="submit">
                <svg viewBox="0 0 24 24" fill="<?= ((int)$r['featured']===1)?'currentColor':'none' ?>" stroke="currentColor" stroke-width="1.6"><path d="M12 3l2.7 5.5 6 .9-4.3 4.2 1 6-5.4-2.8L6.6 22l1-6L3.3 9.4l6-.9z"/></svg>
              </button>
            </form>
            <a class="icon-btn" href="edit.php?id=<?= (int)$r['id'] ?>" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg></a>
            <a class="icon-btn" href="../post.php?slug=<?= urlencode($r['slug']) ?>" target="_blank" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12zM12 9a3 3 0 100 6 3 3 0 000-6z"/></svg></a>
            <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Excluir este post definitivamente?');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
              <button class="icon-btn del" title="Excluir" type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/></svg></button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body></html>
