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
function render_head($title, $desc = '', $extra_css = '', $opts = []){
  $t = e($title);
  $d = e(mb_substr(strip_tags($desc ?: 'Blog da Goellner Ferreira: artigos sobre auditoria, consultoria estratégica, finanças, governança, compliance e cases de empresas.'),0,160));
  $canon = e($opts['canonical'] ?? (BLOG_URL . '/'));
  $img = e($opts['image'] ?? (SITE_URL . '/assets/logo-gf.png'));
  $type = e($opts['type'] ?? 'website');
  $robots = e($opts['robots'] ?? 'index, follow, max-image-preview:large');
  $ld = isset($opts['jsonld']) ? '<script type="application/ld+json">' . json_encode($opts['jsonld'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . '</script>' : '';
  $extra_css = '<link rel="canonical" href="'.$canon.'" />
<meta name="robots" content="'.$robots.'" />
<meta property="og:type" content="'.$type.'" />
<meta property="og:site_name" content="Goellner Ferreira" />
<meta property="og:locale" content="pt_BR" />
<meta property="og:title" content="'.$t.'" />
<meta property="og:description" content="'.$d.'" />
<meta property="og:url" content="'.$canon.'" />
<meta property="og:image" content="'.$img.'" />
<meta name="twitter:card" content="summary_large_image" />
<link rel="alternate" type="application/rss+xml" title="Blog Goellner Ferreira" href="'.BLOG_URL.'/rss.php" />
'.$ld."
".$extra_css;
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
  echo '<style id="gfh-css">
.gfh{position:sticky;top:0;z-index:80;background:rgba(255,255,255,.92);-webkit-backdrop-filter:blur(14px);backdrop-filter:blur(14px);border-bottom:1px solid rgba(10,33,80,.08);font-family:"Montserrat",system-ui,sans-serif;transition:box-shadow .3s}
.gfh[data-stuck="true"]{box-shadow:0 4px 20px rgba(16,30,68,.06)}
.gfh *{box-sizing:border-box}
.gfh-in{max-width:1180px;margin:0 auto;padding:0 clamp(20px,5vw,58px);display:flex;align-items:center;gap:20px;height:84px}
.gfh-logo{display:flex;flex:none}.gfh-logo img{height:56px;width:auto;display:block}
.gfh-links{display:flex;align-items:center;gap:2px;margin-left:auto}
.gfh-links a{font-size:14.5px;font-weight:600;color:#14203f;padding:9px 13px;border-radius:999px;text-decoration:none;white-space:nowrap;transition:background .16s,color .16s}
.gfh-links a:hover{background:#f3efe7;color:#ff5757}
.gfh-links a.on{color:#ff5757}
.gfh-wm{display:inline-flex!important;align-items:center;gap:6px;color:#56607a!important;font-weight:600!important}
.gfh-wm svg{width:16px;height:16px}
.gfh-sep{width:1px;height:22px;background:rgba(10,33,80,.14);margin:0 8px}
.gfh-cta{flex:none;display:inline-flex;align-items:center;font-size:14px;font-weight:700;color:#fff!important;background:#ff5757;padding:11px 20px;border-radius:999px;text-decoration:none;white-space:nowrap;box-shadow:0 8px 22px rgba(255,87,87,.24);transition:transform .18s}
.gfh-cta:hover{transform:translateY(-2px);color:#fff}
.gfh-burger{display:none;margin-left:auto;flex-direction:column;gap:5px;background:none;border:0;cursor:pointer;padding:10px}
.gfh-burger span{width:26px;height:2px;background:#0a2150;border-radius:2px;display:block}
.gfh-mm{display:none;flex-direction:column;padding:6px clamp(20px,5vw,58px) 22px;background:#fbf9f4;border-top:1px solid rgba(10,33,80,.07)}
.gfh-mm.open{display:flex}
.gfh-mm a{padding:14px 4px;font-size:15.5px;font-weight:600;color:#14203f;text-decoration:none;border-bottom:1px solid rgba(10,33,80,.07)}
.gfh-mm a.on{color:#ff5757}
.gfh-mm .gfh-cta{margin-top:16px;justify-content:center;border:0;padding:14px 20px;font-size:15px}
@media(max-width:1260px){.gfh-links a{padding:9px 10px}.gfh-sep{margin:0 4px}.gfh-wm{font-size:0!important;gap:0}.gfh-in{gap:14px}}
@media(max-width:1140px){.gfh-links,.gfh-in>.gfh-cta{display:none}.gfh-burger{display:flex}}
@media(max-width:640px){.gfh-in{height:70px}.gfh-logo img{height:46px}}
</style>
<header class="gfh" id="gfh" data-stuck="false">
  <div class="gfh-in">
    <a class="gfh-logo" href="'.$site.'/"><img src="assets/logo-gf.png" alt="Goellner Ferreira — consultoria estratégica e auditoria" /></a>
    <nav class="gfh-links"><a href="'.$site.'/#sobre">Sobre</a><a href="'.$site.'/#solucoes">Soluções</a><a href="'.$site.'/#socios">Sócios</a><a href="index.php" class="on">Blog</a><a href="'.$site.'/cases.html">Clientes</a><a href="'.$site.'/#contato">Contato</a><span class="gfh-sep"></span><a class="gfh-wm" href="https://webmail-seguro.com.br/goellnerferreira.com.br/" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1z"/><path d="M3 7l9 6 9-6"/></svg>Webmail</a></nav>
    <a class="gfh-cta" href="'.$site.'/#contato">Fale com um especialista</a>
    <button class="gfh-burger" aria-label="Abrir menu" onclick="document.getElementById(\'gfhmm\').classList.toggle(\'open\')"><span></span><span></span><span></span></button>
  </div>
  <div class="gfh-mm" id="gfhmm"><a href="'.$site.'/#sobre">Sobre</a><a href="'.$site.'/#solucoes">Soluções</a><a href="'.$site.'/#socios">Sócios</a><a href="index.php" class="on">Blog</a><a href="'.$site.'/cases.html">Clientes</a><a href="'.$site.'/#contato">Contato</a><a href="https://webmail-seguro.com.br/goellnerferreira.com.br/" target="_blank" rel="noopener">Webmail</a><a class="gfh-cta" href="'.$site.'/#contato">Fale com um especialista</a></div>
</header>
<script>(function(){var h=document.getElementById(\'gfh\');function f(){h.setAttribute(\'data-stuck\',window.scrollY>12);}f();window.addEventListener(\'scroll\',f);document.querySelectorAll(\'#gfhmm a\').forEach(function(a){a.addEventListener(\'click\',function(){document.getElementById(\'gfhmm\').classList.remove(\'open\');});});})();</script>';
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
    <div class="wrap footer-bottom"><span>© '.$year.' Goellner Ferreira. Todos os direitos reservados.</span><span class="footer-credit">Desenvolvido por <a href="https://novarotamkt.com.br" target="_blank" rel="noopener">NovaRotaMkt</a></span></div>
  </footer>';
}
