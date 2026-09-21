<?php
/* Conexão PDO com o MySQL — usado por todas as páginas. */
require_once __DIR__ . '/config.php';

function db() {
  static $pdo = null;
  if ($pdo === null) {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
      $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
      ]);
    } catch (PDOException $e) {
      http_response_code(500);
      die('<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:24px;border:1px solid #f0c0c0;background:#fff6f6;border-radius:12px;color:#8a1f1f">'
        . '<h2 style="margin-bottom:8px">Não foi possível conectar ao banco de dados</h2>'
        . '<p>Confira os dados em <b>config.php</b> (host, nome, usuário e senha do banco criado na Locaweb).</p>'
        . '</div>');
    }
  }
  return $pdo;
}
