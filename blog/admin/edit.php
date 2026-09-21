<?php
require_once __DIR__ . '/auth.php';
require_login();
global $GF_CATEGORIES;
$pdo = db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
if ($id) {
  $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $post = $stmt->fetch();
}
$isNew = !$post;

// defaults
$f = [
  'id' => $post['id'] ?? 0,
  'title' => $post['title'] ?? '',
  'slug' => $post['slug'] ?? '',
  'category' => $post['category'] ?? ($GF_CATEGORIES[0] ?? ''),
  'author_id' => $post['author_id'] ?? current_user()['id'],
  'cover' => $post['cover'] ?? '',
  'excerpt' => $post['excerpt'] ?? '',
  'body' => $post['body'] ?? '',
  'status' => $post['status'] ?? 'draft',
  'publish_at' => $post['publish_at'] ?? '',
  'featured' => (int)($post['featured'] ?? 0),
];
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();
$flash = flash_get();
// datetime-local value
$dtVal = '';
if ($f['publish_at']) { $dtVal = date('Y-m-d\TH:i', strtotime($f['publish_at'])); }
?>
<!DOCTYPE html><html lang="pt-BR"><head>
<meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= $isNew ? 'Novo post' : 'Editar post' ?> — Painel · <?= e(SITE_NAME) ?></title>
<link rel="icon" type="image/png" href="../assets/gf-symbol.png" />
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="admin.css" />
</head><body>
<div class="topbar">
  <img src="../assets/gf-symbol.png" alt="GF" />
  <div class="tt"><?= $isNew ? 'Novo post' : 'Editar post' ?><span>Goellner Ferreira · Blog</span></div>
  <div class="sp"></div>
  <a class="tl" href="index.php">← Voltar ao painel</a>
</div>

<form method="post" action="save.php" id="postForm">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
<input type="hidden" name="action" value="save" />
<input type="hidden" name="id" value="<?= (int)$f['id'] ?>" />
<input type="hidden" name="body" id="bodyInput" />
<input type="hidden" name="cover" id="coverInput" value="<?= e($f['cover']) ?>" />

<div class="wrap">
  <?php if ($flash): ?><div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
  <div class="editor-grid">
    <!-- coluna principal -->
    <div>
      <div class="card-box">
        <div class="field">
          <label>Título do artigo</label>
          <input type="text" name="title" id="titleInput" value="<?= e($f['title']) ?>" placeholder="Ex: Controles internos que sustentam o crescimento" required />
          <div class="slugline">Link: post.php?slug=<b id="slugPreview"><?= e($f['slug'] ?: '…') ?></b></div>
        </div>
        <div class="field">
          <label>Resumo <span style="font-weight:500;color:var(--faint)">(aparece na lista do blog)</span></label>
          <textarea name="excerpt" id="excerptInput" maxlength="280" placeholder="1 ou 2 frases que resumem o artigo."><?= e($f['excerpt']) ?></textarea>
          <div class="hint"><span id="excCount">0</span>/280</div>
        </div>
      </div>

      <div class="card-box">
        <h3>Conteúdo</h3>
        <div class="rt-toolbar" id="rtToolbar">
          <button type="button" data-cmd="bold" title="Negrito"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 010 8H6zM6 12h9a4 4 0 010 8H6z"/></svg></button>
          <button type="button" data-cmd="italic" title="Itálico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 4h-9M14 20H5M15 4L9 20"/></svg></button>
          <div class="sep"></div>
          <button type="button" data-block="H2" title="Título">H2</button>
          <button type="button" data-block="H3" title="Subtítulo">H3</button>
          <button type="button" data-block="P" title="Parágrafo">P</button>
          <div class="sep"></div>
          <button type="button" data-cmd="insertUnorderedList" title="Lista"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></button>
          <button type="button" data-block="BLOCKQUOTE" title="Citação"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h4v6H7zM7 13c0 2 1 3 3 3M13 7h4v6h-4zM13 13c0 2 1 3 3 3"/></svg></button>
          <button type="button" id="btnLink" title="Link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1.5 1.5M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1.5-1.5"/></svg></button>
        </div>
        <div class="rt-editor" id="rtEditor" contenteditable data-ph="Escreva o conteúdo do artigo aqui…"></div>
        <div class="hint" style="margin-top:8px">💡 Pode colar direto do Word ou Google Docs — a formatação é ajustada automaticamente ao padrão do blog (títulos, negrito, listas e links são mantidos; o resto é limpo).</div>
      </div>
    </div>

    <!-- coluna lateral -->
    <div>
      <div class="card-box">
        <h3>Publicação</h3>
        <div class="field statusopts">
          <label class="statusopt" id="opt-pub"><input type="radio" name="status" value="published" <?= $f['status']==='published'?'checked':'' ?> /><span><span class="t">Publicar</span><span class="d">Visível no blog agora (ou na data agendada)</span></span></label>
          <label class="statusopt" id="opt-draft"><input type="radio" name="status" value="draft" <?= $f['status']!=='published'?'checked':'' ?> /><span><span class="t">Rascunho</span><span class="d">Salva sem aparecer no site</span></span></label>
        </div>
        <div class="field" id="schedWrap" style="margin-top:14px">
          <label>Data de publicação <span style="font-weight:500;color:var(--faint)">(opcional — agende para o futuro)</span></label>
          <input type="datetime-local" name="publish_at" value="<?= e($dtVal) ?>" />
          <div class="hint">Deixe em branco para publicar imediatamente. Uma data futura agenda o post.</div>
        </div>
      </div>

      <div class="card-box">
        <h3>Detalhes</h3>
        <div class="field">
          <label>Categoria</label>
          <select name="category">
            <?php foreach ($GF_CATEGORIES as $c): ?>
              <option <?= $c===$f['category']?'selected':'' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Autor</label>
          <select name="author_id">
            <?php foreach ($users as $au): ?>
              <option value="<?= (int)$au['id'] ?>" <?= ((int)$au['id']===(int)$f['author_id'])?'selected':'' ?>><?= e($au['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Link (URL) — opcional</label>
          <input type="text" name="slug" id="slugInput" value="<?= e($f['slug']) ?>" placeholder="gerado do título" />
        </div>
        <div class="field toggle">
          <div class="tx"><div class="t">Post em destaque</div><div class="d">Aparece em primeiro lugar no blog</div></div>
          <label class="sw"><input type="checkbox" name="featured" value="1" <?= $f['featured']?'checked':'' ?> /><span class="sl"></span></label>
        </div>
      </div>

      <div class="card-box">
        <h3>Foto de capa</h3>
        <div class="cover-up <?= $f['cover']?'has':'' ?>" id="coverBox">
          <?php if ($f['cover']): ?>
            <img id="coverImg" src="<?= e($f['cover']) ?>" alt="capa" />
          <?php else: ?>
            <div class="inner" id="coverInner"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 19V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2zM8.5 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM21 15l-5-5L5 21"/></svg>Clique para enviar uma imagem</div>
          <?php endif; ?>
        </div>
        <input type="file" id="coverFile" accept="image/*" style="display:none" />
        <div class="cover-actions">
          <button type="button" class="btn btn-ghost btn-sm" id="coverPick">Enviar / trocar</button>
          <button type="button" class="btn btn-ghost btn-sm" id="coverClear">Remover</button>
        </div>
        <div class="hint">Sem capa, o post mostra um fundo da marca. A imagem é reduzida automaticamente.</div>
      </div>
    </div>
  </div>

  <div class="save-bar">
    <button type="submit" name="nav" value="index" class="btn btn-save"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2zM17 21v-8H7v8M7 3v5h8"/></svg>Salvar post</button>
    <button type="submit" name="nav" value="preview" formtarget="_blank" class="btn btn-ghost"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12zM12 9a3 3 0 100 6 3 3 0 000-6z"/></svg>Salvar e pré-visualizar</button>
    <a class="btn btn-ghost" href="index.php">Cancelar</a>
  </div>
</div>
</form>

<script>
(function(){
  var editor = document.getElementById('rtEditor');
  editor.innerHTML = <?= json_encode($f['body'], JSON_UNESCAPED_UNICODE) ?> || '';

  var toolbar = document.getElementById('rtToolbar');
  toolbar.addEventListener('mousedown', function(e){
    var b = e.target.closest('button'); if(!b) return;
    e.preventDefault();
    if(b.id==='btnLink'){ var u=prompt('Endereço do link (https://…):'); if(u) document.execCommand('createLink',false,u); editor.focus(); return; }
    if(b.dataset.cmd){ document.execCommand(b.dataset.cmd,false,null); editor.focus(); return; }
    if(b.dataset.block){ document.execCommand('formatBlock',false,b.dataset.block); editor.focus(); return; }
  });

  // Padroniza qualquer conteúdo colado (Word, Google Docs, sites) ao visual do blog
  var ALLOWED_TAGS = ['B','STRONG','I','EM','U','H2','H3','UL','OL','LI','BLOCKQUOTE','A','P','BR'];
  function sanitizePaste(html){
    var div = document.createElement('div');
    div.innerHTML = html;
    div.querySelectorAll('script,style,meta,link,img,table,tr,td,th,tbody,thead,span,div,font').forEach(function(n){
      // span/div/font viram apenas texto (unwrap), o resto some
      if(n.tagName==='SPAN'||n.tagName==='DIV'||n.tagName==='FONT'){
        var p=n.parentNode; while(n.firstChild) p.insertBefore(n.firstChild,n); p.removeChild(n);
      } else { n.remove(); }
    });
    var changed = true, guard = 0;
    while (changed && guard < 6) {
      changed = false; guard++;
      div.querySelectorAll('*').forEach(function(el){
        if (ALLOWED_TAGS.indexOf(el.tagName) === -1) {
          var p = el.parentNode; if(!p) return;
          while (el.firstChild) p.insertBefore(el.firstChild, el);
          p.removeChild(el);
          changed = true;
        }
      });
    }
    div.querySelectorAll('*').forEach(function(el){
      Array.prototype.slice.call(el.attributes).forEach(function(attr){
        if (!(el.tagName === 'A' && attr.name === 'href')) el.removeAttribute(attr.name);
      });
      if (el.tagName === 'A') el.setAttribute('target','_blank');
    });
    return div.innerHTML;
  }
  editor.addEventListener('paste', function(e){
    e.preventDefault();
    var cd = e.clipboardData || window.clipboardData;
    var html = cd.getData('text/html');
    var clean;
    if (html) {
      clean = sanitizePaste(html);
    } else {
      var text = cd.getData('text/plain') || '';
      clean = text.split(/\n{2,}/).map(function(p){
        var d=document.createElement('div'); d.textContent=p; return '<p>'+d.innerHTML+'</p>';
      }).join('');
    }
    document.execCommand('insertHTML', false, clean);
  });

  // slug + título
  var titleInput=document.getElementById('titleInput'),
      slugInput=document.getElementById('slugInput'),
      slugPreview=document.getElementById('slugPreview');
  var slugTouched = <?= $f['slug'] ? 'true':'false' ?>;
  function slugify(s){return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').slice(0,90);}
  function refreshSlug(){var v=slugTouched?slugInput.value:slugify(titleInput.value); if(!slugTouched) slugInput.value=slugify(titleInput.value); slugPreview.textContent=slugify(v)||'…';}
  titleInput.addEventListener('input', refreshSlug);
  slugInput.addEventListener('input', function(){slugTouched=true; slugInput.value=slugify(slugInput.value); slugPreview.textContent=slugInput.value||'…';});

  // excerpt count
  var exc=document.getElementById('excerptInput'), excCount=document.getElementById('excCount');
  function refreshExc(){excCount.textContent=exc.value.length;}
  exc.addEventListener('input',refreshExc); refreshExc();

  // status highlight + sched
  function refreshStatus(){
    var pub=document.querySelector('input[name=status][value=published]').checked;
    document.getElementById('opt-pub').classList.toggle('sel',pub);
    document.getElementById('opt-draft').classList.toggle('sel',!pub);
  }
  document.querySelectorAll('input[name=status]').forEach(function(r){r.addEventListener('change',refreshStatus);});
  refreshStatus();

  // cover upload (client-side resize -> dataURL, salvo no banco)
  var coverFile=document.getElementById('coverFile'), coverInput=document.getElementById('coverInput'),
      coverBox=document.getElementById('coverBox');
  document.getElementById('coverPick').addEventListener('click',function(){coverFile.click();});
  coverBox.addEventListener('click',function(){coverFile.click();});
  document.getElementById('coverClear').addEventListener('click',function(){
    coverInput.value=''; coverBox.classList.remove('has');
    coverBox.innerHTML='<div class="inner"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 19V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2zM8.5 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM21 15l-5-5L5 21"/></svg>Clique para enviar uma imagem</div>';
  });
  coverFile.addEventListener('change',function(e){
    var file=e.target.files[0]; if(!file) return;
    var r=new FileReader();
    r.onload=function(){var img=new Image(); img.onload=function(){
      var max=1200,w=img.width,h=img.height; if(w>max){h=Math.round(h*max/w); w=max;}
      var c=document.createElement('canvas'); c.width=w; c.height=h;
      c.getContext('2d').drawImage(img,0,0,w,h);
      var data=c.toDataURL('image/jpeg',0.82);
      coverInput.value=data; coverBox.classList.add('has');
      coverBox.innerHTML='<img id="coverImg" src="'+data+'" alt="capa" />';
    }; img.src=r.result;};
    r.readAsDataURL(file);
    coverFile.value='';
  });

  // on submit: copy body, ensure slug
  document.getElementById('postForm').addEventListener('submit',function(){
    document.getElementById('bodyInput').value=editor.innerHTML;
    if(!slugInput.value) slugInput.value=slugify(titleInput.value);
  });
})();
</script>
</body></html>
