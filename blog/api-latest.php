<?php
/* ============================================================
   API simples — devolve os últimos posts publicados em JSON.
   Usada pela home (index.html) para mostrar "Do nosso blog"
   sempre atualizado, sem precisar editar código.
   ============================================================ */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . SITE_URL);

$limit = isset($_GET['limit']) ? max(1, min(12, (int)$_GET['limit'])) : 4;
$pdo = db();

$sql = "SELECT p.slug, p.title, p.category, p.excerpt, p.cover, p.publish_at, p.created_at, p.featured, u.name AS author_name
        FROM posts p LEFT JOIN users u ON u.id = p.author_id
        WHERE p.status = 'published' AND (p.publish_at IS NULL OR p.publish_at <= NOW())
        ORDER BY p.featured DESC, COALESCE(p.publish_at, p.created_at) DESC
        LIMIT ?";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$out = array_map(function($r) {
  return [
    'slug'     => $r['slug'],
    'category' => $r['category'],
    'title'    => $r['title'],
    'excerpt'  => $r['excerpt'],
    'author'   => $r['author_name'] ?: SITE_NAME,
    'date'     => fmt_date($r['publish_at'] ?: $r['created_at']),
    'cover'    => $r['cover'],
  ];
}, $rows);

echo json_encode($out, JSON_UNESCAPED_UNICODE);
