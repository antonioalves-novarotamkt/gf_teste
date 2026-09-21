<?php
require_once __DIR__ . '/auth.php';
require_login();
csrf_check();
global $GF_CATEGORIES;
$pdo = db();

$action = $_POST['action'] ?? 'save';

/* ---- Alternar destaque (a partir do dashboard) ---- */
if ($action === 'toggle_featured') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id) {
    $cur = $pdo->prepare("SELECT featured FROM posts WHERE id = ?");
    $cur->execute([$id]);
    $val = (int)$cur->fetchColumn();
    $pdo->prepare("UPDATE posts SET featured = ? WHERE id = ?")->execute([$val ? 0 : 1, $id]);
    flash_set($val ? 'Destaque removido.' : 'Post marcado como destaque.');
  }
  header('Location: index.php');
  exit;
}

/* ---- Salvar (novo ou edição) ---- */
$id        = (int)($_POST['id'] ?? 0);
$title     = trim($_POST['title'] ?? '');
$slug      = slugify($_POST['slug'] ?? '');
if ($slug === '') $slug = slugify($title);
$category  = trim($_POST['category'] ?? '');
$author_id = (int)($_POST['author_id'] ?? 0) ?: null;
$cover     = trim($_POST['cover'] ?? '');
$excerpt   = trim($_POST['excerpt'] ?? '');
$body      = clean_body($_POST['body'] ?? '');
$status    = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
$featured  = !empty($_POST['featured']) ? 1 : 0;

$publish_at = null;
$pa = trim($_POST['publish_at'] ?? '');
if ($pa !== '') {
  $ts = strtotime($pa);
  if ($ts) $publish_at = date('Y-m-d H:i:s', $ts);
} elseif ($status === 'published') {
  $publish_at = date('Y-m-d H:i:s'); // publica agora
}

if ($title === '') {
  flash_set('Dê um título ao post antes de salvar.', 'err');
  header('Location: ' . ($id ? "edit.php?id=$id" : 'edit.php'));
  exit;
}

// garante slug único
$check = $pdo->prepare("SELECT id FROM posts WHERE slug = ? AND id <> ? LIMIT 1");
$check->execute([$slug, $id]);
if ($check->fetch()) { $slug .= '-' . substr(uniqid(), -4); }

if ($id) {
  $stmt = $pdo->prepare("UPDATE posts SET slug=?, title=?, category=?, author_id=?, cover=?, excerpt=?, body=?, status=?, publish_at=?, featured=? WHERE id=?");
  $stmt->execute([$slug, $title, $category, $author_id, $cover, $excerpt, $body, $status, $publish_at, $featured, $id]);
  flash_set('Post atualizado com sucesso.');
} else {
  $stmt = $pdo->prepare("INSERT INTO posts (slug,title,category,author_id,cover,excerpt,body,status,publish_at,featured,views) VALUES (?,?,?,?,?,?,?,?,?,?,0)");
  $stmt->execute([$slug, $title, $category, $author_id, $cover, $excerpt, $body, $status, $publish_at, $featured]);
  $id = (int)$pdo->lastInsertId();
  flash_set($status === 'published' ? 'Post publicado! 🎉' : 'Rascunho salvo.');
}

if (($_POST['nav'] ?? '') === 'preview') {
  header('Location: preview.php?id=' . $id);
  exit;
}

header('Location: index.php');
exit;
