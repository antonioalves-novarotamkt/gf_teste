<?php
/* ============================================================
   Goellner Ferreira — Blog · Configuração
   ------------------------------------------------------------
   EDITE APENAS OS 4 VALORES ABAIXO com os dados do banco de
   dados MySQL criado no painel da Locaweb.
   ============================================================ */

// ---- Banco de dados (pegue no painel da Locaweb) ----
define('DB_HOST', 'localhost');          // normalmente "localhost" na Locaweb
define('DB_NAME', 'SEU_BANCO');          // nome do banco que você criou
define('DB_USER', 'SEU_USUARIO');        // usuário do banco
define('DB_PASS', 'SUA_SENHA');          // senha do banco

// ---- Configuração do site ----
define('SITE_NAME', 'Goellner Ferreira');
// Endereço público do blog (sem barra no final):
define('BLOG_URL', 'https://goellnerferreira.com.br/blog');
// Endereço do site principal (para o menu):
define('SITE_URL', 'https://goellnerferreira.com.br');

date_default_timezone_set('America/Sao_Paulo');

// Categorias disponíveis (edite à vontade)
$GF_CATEGORIES = ['Auditoria', 'Finanças', 'Gestão', 'Cases', 'Novidades'];

// Contatos (rodapé)
$GF_CONTACTS = [
  ['nome' => 'Silvana', 'tel' => '+55 92 9 8112-5980', 'href' => '+5592981125980'],
  ['nome' => 'Jonatas', 'tel' => '+55 11 9 9977-3787', 'href' => '+5511999773787'],
];
define('GF_INSTAGRAM', 'https://www.instagram.com/goellnerferreira/');
