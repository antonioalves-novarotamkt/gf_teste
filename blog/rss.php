<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
header('Content-Type: application/rss+xml; charset=utf-8');
$pdo = db();
$rows = $pdo->query("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON u.id=p.author_id WHERE p.status='published' AND (p.publish_at IS NULL OR p.publish_at <= NOW()) ORDER BY COALESCE(p.publish_at, p.created_at) DESC LIMIT 20")->fetchAll();
$x = function($s){ return htmlspecialchars((string)$s, ENT_XML1|ENT_QUOTES, 'UTF-8'); };
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0"><channel>';
echo '<title>Blog Goellner Ferreira</title><link>' . BLOG_URL . '/</link><language>pt-BR</language>';
echo '<description>Auditoria, consultoria estratégica, finanças e gestão empresarial.</description>';
foreach ($rows as $p) {
  $link = BLOG_URL . '/post.php?slug=' . urlencode($p['slug']);
  echo '<item><title>' . $x($p['title']) . '</title><link>' . $x($link) . '</link><guid>' . $x($link) . '</guid>';
  echo '<pubDate>' . date(DATE_RSS, strtotime($p['publish_at'] ?: $p['created_at'])) . '</pubDate>';
  echo '<category>' . $x($p['category']) . '</category><description>' . $x(strip_tags($p['excerpt'])) . '</description></item>';
}
echo '</channel></rss>';
