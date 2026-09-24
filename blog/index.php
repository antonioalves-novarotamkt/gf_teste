<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
global $GF_CATEGORIES;

$cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$q   = isset($_GET['q']) ? trim($_GET['q']) : '';

$pdo = db();
$where = "status='published' AND (publish_at IS NULL OR publish_at <= NOW())";
$params = [];
if ($cat !== '' && $cat !== 'Todos') { $where .= " AND category = ?"; $params[] = $cat; }
if ($q !== '') { $where .= " AND (title LIKE ? OR excerpt LIKE ? OR category LIKE ?)"; $like = '%'.$q.'%'; $params[] = $like; $params[] = $like; $params[] = $like; }

$sql = "SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON u.id=p.author_id
        WHERE $where ORDER BY p.featured DESC, COALESCE(p.publish_at, p.created_at) DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// destaque: primeiro post marcado como featured (só na visão geral, sem filtro/busca)
$showFeature = ($cat === '' || $cat === 'Todos') && $q === '';
$feature = null; $rest = $posts;
if ($showFeature && count($posts)) {
  foreach ($posts as $i => $p) { if ((int)$p['featured'] === 1) { $feature = $p; unset($posts[$i]); break; } }
  if (!$feature) { $feature = $posts[array_key_first($posts)] ?? null; if ($feature) unset($posts[array_key_first($posts)]); }
  $rest = array_values($posts);
}

function cover_html($p){
  if (!empty($p['cover'])) return '<div class="post-cover"><img src="'.e($p['cover']).'" alt="'.e($p['title']).'" loading="lazy" /></div>';
  return '<div class="post-cover"><div class="cover-fallback"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 3v5h5M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V8l-6-5z"/></svg></div></div>';
}
function meta_html($p){
  return '<div class="post-meta"><span>'.e($p['author_name'] ?: SITE_NAME).'</span><span class="dot"></span><span>'.fmt_date($p['publish_at'] ?: $p['created_at']).'</span></div>';
}

$isFiltered = ($cat !== '' && $cat !== 'Todos') || $q !== '';
$blogLd = ['@context'=>'https://schema.org','@type'=>'Blog','name'=>'Blog Goellner Ferreira','url'=>BLOG_URL.'/','inLanguage'=>'pt-BR','publisher'=>['@type'=>'Organization','name'=>SITE_NAME,'url'=>SITE_URL]];
render_head(
  ($cat !== '' && $cat !== 'Todos' ? $cat . ' | ' : '') . 'Blog Goellner Ferreira: auditoria, finanças e gestão empresarial',
  'Artigos sobre auditoria, consultoria estratégica, finanças corporativas, governança, compliance e perícia contábil, escritos pelos sócios da Goellner Ferreira.',
  '',
  ['canonical' => BLOG_URL . '/' . (($cat !== '' && $cat !== 'Todos') ? '?cat=' . urlencode($cat) : ''), 'robots' => $q !== '' ? 'noindex, follow' : 'index, follow', 'jsonld' => $blogLd]
);
render_header();
?>
<section class="blog-hero">
  <div class="wrap blog-hero-inner">
    <span class="eyebrow">Conteúdo &amp; Insights</span>
    <h1>Ideias que ajudam sua empresa a <em>decidir melhor</em></h1>
    <p>Artigos, novidades e cases sobre auditoria, finanças e gestão — direto de quem vive a realidade das empresas.</p>
  </div>
</section>

<div class="blog-tools">
  <div class="wrap blog-tools-inner">
    <form class="cats" method="get" style="border:none;padding:0;margin:0;display:flex;gap:8px;flex-wrap:wrap;flex:1;">
      <?php
        $cats = array_merge(['Todos'], $GF_CATEGORIES);
        $active = ($cat === '' ? 'Todos' : $cat);
        foreach ($cats as $c):
          $url = 'index.php' . ($c === 'Todos' ? '' : '?cat=' . urlencode($c));
      ?>
        <a class="cat<?= $c === $active ? ' active' : '' ?>" href="<?= $url ?>"><?= e($c) ?></a>
      <?php endforeach; ?>
    </form>
    <form class="search" method="get" action="index.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M11 18a7 7 0 100-14 7 7 0 000 14zM21 21l-4.3-4.3"/></svg>
      <input type="search" name="q" placeholder="Buscar no blog…" value="<?= e($q) ?>" />
    </form>
  </div>
</div>

<main class="feed lay-magazine">
  <div class="wrap">
    <div class="feed-count"><?= count($posts) + ($feature?1:0) ?> <?= (count($posts)+($feature?1:0))===1?'artigo':'artigos' ?><?= ($cat!==''&&$cat!=='Todos')?' · '.e($cat):'' ?><?= $q!==''?' · "'.e($q).'"':'' ?></div>
    <?php if (count($rest) === 0 && !$feature): ?>
      <div class="feed-empty">Nenhum artigo encontrado. Tente outra busca ou categoria.</div>
    <?php else: ?>
      <?php if ($feature): ?>
        <a class="feature" href="post.php?slug=<?= urlencode($feature['slug']) ?>">
          <?= cover_html($feature) ?>
          <div class="feature-body">
            <span class="chip"><?= e($feature['category']) ?></span>
            <h2><?= e($feature['title']) ?></h2>
            <p><?= e($feature['excerpt']) ?></p>
            <?= meta_html($feature) ?>
          </div>
        </a>
      <?php endif; ?>
      <div class="grid">
        <?php foreach ($rest as $p): ?>
          <a class="card" href="post.php?slug=<?= urlencode($p['slug']) ?>">
            <?= cover_html($p) ?>
            <div class="card-body">
              <span class="chip"><?= e($p['category']) ?></span>
              <h3><?= e($p['title']) ?></h3>
              <p><?= e($p['excerpt']) ?></p>
              <?= meta_html($p) ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php render_footer(); ?>
</body></html>
