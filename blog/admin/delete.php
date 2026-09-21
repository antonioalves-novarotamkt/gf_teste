<?php
require_once __DIR__ . '/auth.php';
require_login();
csrf_check();
$id = (int)($_POST['id'] ?? 0);
if ($id) {
  db()->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
  flash_set('Post excluído.');
}
header('Location: index.php');
exit;
