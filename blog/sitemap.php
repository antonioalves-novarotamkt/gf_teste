<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
header('Content-Type: application/xml; charset=utf-8');
$pdo = db();
$rows = $pdo->query("SELECT slug, COALESCE(updated_at, publish_at, created_at) AS mod_at FROM posts WHERE status='published' AND (publish_at IS NULL OR publish_at <= NOW()) ORDER BY COALESCE(publish_at, created_at) DESC")->fetchAll();
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
echo '  <url><loc>' . BLOG_URL . '/</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
foreach ($rows as $r) {
  $loc = htmlspecialchars(BLOG_URL . '/post.php?slug=' . urlencode($r['slug']), ENT_XML1);
  echo '  <url><loc>' . $loc . '</loc><lastmod>' . date('Y-m-d', strtotime($r['mod_at'])) . '</lastmod><priority>0.7</priority></url>' . "\n";
}
echo '</urlset>';
