<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$pdo = db();
$post = null;
if ($slug !== '') {
  $stmt = $pdo->prepare("SELECT p.*, u.name AS author_name, u.role AS author_role, u.avatar AS author_avatar
    FROM posts p LEFT JOIN users u ON u.id=p.author_id
    WHERE p.slug = ? AND p.status='published' AND (p.publish_at IS NULL OR p.publish_at <= NOW()) LIMIT 1");
  $stmt->execute([$slug]);
  $post = $stmt->fetch();
}

if (!$post) {
  render_head('Artigo não encontrado — ' . SITE_NAME);
  render_header();
  echo '<main style="max-width:680px;margin:0 auto;padding:120px 24px;text-align:center">
    <h1 style="font-size:32px">Artigo não encontrado</h1>
    <p style="color:var(--muted);margin-top:14px">O conteúdo que você procura pode ter sido movido.</p>
    <a class="btn btn-accent" href="index.php" style="margin-top:26px">Ver todos os artigos</a>
  </main>';
  render_footer();
  echo '</body></html>';
  exit;
}

// contador de visitas (+1)
try { $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]); } catch (Exception $e) {}

// relacionados
$rel = $pdo->prepare("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON u.id=p.author_id
  WHERE p.id <> ? AND p.status='published' AND (p.publish_at IS NULL OR p.publish_at <= NOW())
  ORDER BY COALESCE(p.publish_at,p.created_at) DESC LIMIT 3");
$rel->execute([$post['id']]);
$related = $rel->fetchAll();

$url = BLOG_URL . '/post.php?slug=' . urlencode($post['slug']);
$shareText = $post['title'] . ' — ' . SITE_NAME;
$avatar = author_avatar($post['author_avatar']);

render_head($post['title'] . ' — ' . SITE_NAME, $post['excerpt']);
render_header();
?>
<div class="article-head">
  <div class="wrap article-head-inner">
    <a class="back-link" href="index.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>Voltar ao blog</a>
    <div><span class="chip"><?= e($post['category']) ?></span></div>
    <h1><?= e($post['title']) ?></h1>
    <div class="article-byline">
      <img class="av" src="<?= e($avatar) ?>" alt="<?= e($post['author_name']) ?>" />
      <div><div class="who"><?= e($post['author_name'] ?: SITE_NAME) ?></div><div class="when"><?= fmt_date($post['publish_at'] ?: $post['created_at'], true) ?> · <?= (int)$post['views'] ?> leituras</div></div>
    </div>
  </div>
</div>

<?php if (!empty($post['cover'])): ?>
  <div class="article-cover"><div class="frame"><img src="<?= e($post['cover']) ?>" alt="<?= e($post['title']) ?>" /></div></div>
<?php endif; ?>

<article class="article-body"<?= empty($post['cover']) ? ' style="padding-top:clamp(40px,5vw,64px)"' : '' ?>>
  <?= clean_body($post['body']) ?>
</article>

<div class="share">
  <span class="share-label">Compartilhar:</span>
  <div class="share-btns">
    <a class="share-btn share-wa" target="_blank" rel="noopener" href="https://wa.me/?text=<?= rawurlencode($shareText . ' ' . $url) ?>"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M.06 24l1.68-6.13A11.86 11.86 0 010 11.94 11.94 11.94 0 0111.94 0a11.86 11.86 0 018.43 3.5 11.82 11.82 0 013.49 8.44c0 6.58-5.36 11.93-11.94 11.93a11.9 11.9 0 01-5.7-1.45L.06 24zM6.6 20.13c1.68.99 3.28 1.59 5.33 1.59 5.45 0 9.9-4.43 9.9-9.88a9.8 9.8 0 00-2.9-7 9.78 9.78 0 00-7-2.9 9.9 9.9 0 00-9.9 9.9c0 2.07.6 3.62 1.62 5.33l-.99 3.62 3.62-.96zm10.9-3.96c-.07-.12-.27-.2-.57-.35-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.8.37s-1.04 1.01-1.04 2.48 1.06 2.88 1.21 3.08c.15.2 2.1 3.2 5.07 4.49.71.3 1.26.49 1.69.62.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2-1.41.25-.69.25-1.28.18-1.41z"/></svg>WhatsApp</a>
    <a class="share-btn share-li" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($url) ?>"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5A2.5 2.5 0 1 0 5 8.5a2.5 2.5 0 0 0 0-5zM3 9h4v12H3zM9 9h3.8v1.7h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21H17.6v-5.4c0-1.29-.02-2.95-1.8-2.95-1.8 0-2.08 1.4-2.08 2.85V21H9z"/></svg>LinkedIn</a>
    <button type="button" class="share-btn share-ig" id="shareIgBtn" data-url="<?= e($url) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.6" cy="6.4" r="1.1" fill="currentColor" stroke="none"/></svg><span id="shareIgLabel">Instagram</span></button>
    <a class="share-btn share-cp" target="_blank" rel="noopener" href="mailto:?subject=<?= rawurlencode($post['title']) ?>&body=<?= rawurlencode($url) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>E-mail</a>
  </div>
</div>
<script>
(function(){
  var btn = document.getElementById('shareIgBtn');
  if (!btn) return;
  btn.addEventListener('click', function(){
    var url = btn.getAttribute('data-url');
    var label = document.getElementById('shareIgLabel');
    var restore = function(){ label.textContent = 'Instagram'; };
    var openIg = function(){ window.open('https://instagram.com/', '_blank', 'noopener'); };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(function(){
        label.textContent = 'Link copiado!';
        setTimeout(function(){ openIg(); setTimeout(restore, 1800); }, 400);
      }).catch(function(){ openIg(); });
    } else {
      openIg();
    }
  });
})();
</script>

<div class="article-foot">
  <div class="author-box">
    <img class="av" src="<?= e($avatar) ?>" alt="<?= e($post['author_name']) ?>" />
    <div><div class="role"><?= e($post['author_role'] ?: 'Goellner Ferreira') ?></div><h4><?= e($post['author_name'] ?: SITE_NAME) ?></h4><p>Goellner Ferreira — Consultoria &amp; Auditoria</p></div>
  </div>
</div>

<?php if (count($related)): ?>
<section class="more lay-cards">
  <h3>Continue lendo</h3>
  <div class="grid">
    <?php foreach ($related as $p): ?>
      <a class="card" href="post.php?slug=<?= urlencode($p['slug']) ?>">
        <?php if (!empty($p['cover'])): ?><div class="post-cover"><img src="<?= e($p['cover']) ?>" alt="<?= e($p['title']) ?>" loading="lazy" /></div>
        <?php else: ?><div class="post-cover"><div class="cover-fallback"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 3v5h5M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V8l-6-5z"/></svg></div></div><?php endif; ?>
        <div class="card-body"><span class="chip"><?= e($p['category']) ?></span><h3><?= e($p['title']) ?></h3><p><?= e($p['excerpt']) ?></p></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php render_footer(); ?>
</body></html>
