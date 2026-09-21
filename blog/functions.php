<?php
/* Helpers + cabeçalho/rodapé compartilhados (mesmo visual do site). */
require_once __DIR__ . '/config.php';

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function slugify($s){
  $s = mb_strtolower(trim($s ?? ''), 'UTF-8');
  $tr = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a','é'=>'e','ê'=>'e','è'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ò'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n'];
  $s = strtr($s, $tr);
  $s = preg_replace('/[^a-z0-9]+/', '-', $s);
  $s = trim($s, '-');
  return substr($s, 0, 90) ?: ('post-' . time());
}

$GF_MES = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
$GF_MES_LONG = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];

function fmt_date($iso, $long = false){
  global $GF_MES, $GF_MES_LONG;
  if (!$iso) return '';
  $ts = strtotime($iso);
  $d = (int)date('j', $ts); $m = (int)date('n', $ts); $y = date('Y', $ts);
  if ($long) return $d . ' de ' . $GF_MES_LONG[$m-1] . ' de ' . $y;
  return sprintf('%02d', $d) . ' ' . $GF_MES[$m-1] . ' ' . $y;
}

/* Sanitiza o HTML do corpo do post — mantém só tags seguras de formatação. */
function clean_body($html){
  $allowed = '<p><br><strong><b><em><i><u><h2><h3><ul><ol><li><blockquote><a><img>';
  $html = strip_tags($html, $allowed);
  // remove atributos perigosos (on*, javascript:)
  $html = preg_replace('/\son\w+="[^"]*"/i', '', $html);
  $html = preg_replace("/\son\w+='[^']*'/i", '', $html);
  $html = preg_replace('/javascript:/i', '', $html);
  return $html;
}

function author_avatar($file){
  // arquivos em /assets — fallback para o símbolo
  $f = $file ?: 'gf-symbol.png';
  return 'assets/' . $f;
}

/* ---------------- Cabeçalho ---------------- */
function render_head($title, $desc = '', $extra_css = ''){
  $t = e($title);
  $d = e($desc ?: 'Blog da Goellner Ferreira — auditoria, finanças, gestão e cases.');
  echo <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-H2V8YQGPY5"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-H2V8YQGPY5');</script>
<title>$t</title>
<meta name="description" content="$d" />
<link rel="icon" type="image/png" href="assets/gf-symbol.png" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500;1,600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="blog.css" />
$extra_css
</head>
<body>
HTML;
}

function render_header(){
  $site = SITE_URL;
  echo '<header class="site-header" data-stuck="false" id="siteHeader">
    <div class="wrap nav">
      <a href="'.$site.'"><img class="nav-logo" src="assets/logo-gf.png" alt="Goellner Ferreira" /></a>
      <nav class="nav-links">
        <a class="nav-link" href="'.$site.'/#inicio">Início</a>
        <a class="nav-link" href="'.$site.'/#sobre">Sobre</a>
        <a class="nav-link" href="'.$site.'/#solucoes">Soluções</a>
        <a class="nav-link active" href="index.php">Blog</a>
        <a class="nav-link" href="'.$site.'/#contato">Contato</a>
      </nav>
      <div class="nav-cta"><a class="btn btn-accent" href="'.$site.'/#contato">Fale com um especialista</a></div>
      <button class="hamburger" aria-label="Menu" onclick="document.getElementById(\'mm\').classList.toggle(\'open\')"><span></span><span></span><span></span></button>
    </div>
    <div class="mobile-menu" id="mm">
      <a href="'.$site.'/#inicio">Início</a>
      <a href="'.$site.'/#sobre">Sobre</a>
      <a href="'.$site.'/#solucoes">Soluções</a>
      <a href="index.php">Blog</a>
      <a href="'.$site.'/#contato">Contato</a>
    </div>
  </header>
  <script>(function(){var h=document.getElementById("siteHeader");function f(){h.setAttribute("data-stuck",window.scrollY>12);}f();window.addEventListener("scroll",f);})();</script>';
}

function render_footer(){
  global $GF_CONTACTS;
  $site = SITE_URL;
  $year = date('Y');
  $contatos = '';
  foreach ($GF_CONTACTS as $c) {
    $contatos .= '<a href="tel:'.e($c['href']).'">'.e($c['nome']).' · '.e($c['tel']).'</a>';
  }
  $insta = GF_INSTAGRAM;
  echo '<footer class="footer">
    <div class="wrap footer-top">
      <div class="footer-col"><img class="footer-logo" src="assets/logo-gf.png" alt="Goellner Ferreira" /><p class="footer-about">Soluções em consultoria estratégica.</p></div>
      <div class="footer-col"><h5>Navegação</h5><a href="'.$site.'/#inicio">Início</a><a href="'.$site.'/#sobre">Sobre</a><a href="'.$site.'/#solucoes">Soluções</a><a href="index.php">Blog</a><a href="'.$site.'/#contato">Contato</a></div>
      <div class="footer-col"><h5>Contato</h5>'.$contatos.'<a href="'.$insta.'" target="_blank" rel="noopener">@goellnerferreira</a></div>
    </div>
    <div class="wrap footer-bottom"><span>© '.$year.' Goellner Ferreira. Todos os direitos reservados.</span><span class="footer-credit">Desenvolvido por <a href="https://www.agencianovarota.com.br" target="_blank" rel="noopener">NovaRotaMkt</a></span></div>
  </footer>';
}
