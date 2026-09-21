<?php
/* Sessão + proteção de login + CSRF — incluído por todas as páginas do /admin. */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function current_user() {
  if (empty($_SESSION['uid'])) return null;
  static $u = null;
  if ($u === null) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['uid']]);
    $u = $stmt->fetch() ?: null;
  }
  return $u;
}

function require_login() {
  if (!current_user()) {
    header('Location: login.php');
    exit;
  }
}

function csrf_token() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}

function csrf_check() {
  $t = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
    http_response_code(400);
    die('Sessão expirada. Volte e tente novamente.');
  }
}

function flash_set($msg, $type = 'ok') { $_SESSION['flash'] = ['msg' => $msg, 'type' => $type]; }
function flash_get() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
