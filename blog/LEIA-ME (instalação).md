# Blog Goellner Ferreira — Guia de Instalação na Locaweb

Este é o blog com **painel administrativo** (login, escrever, publicar — sem mexer em código).
Siga os passos abaixo **uma única vez** para deixar tudo no ar.

---

## O que você vai precisar
- Acesso ao **Painel da Locaweb** (Central do Cliente)
- Acesso à **hospedagem** via Gerenciador de Arquivos ou FTP (ex: FileZilla)
- 10 minutinhos ☕

---

## PASSO 1 — Criar o banco de dados MySQL

1. Entre no **Painel da Locaweb** → seção **Banco de Dados / MySQL**.
2. Clique em **Criar novo banco**. Anote estes 4 dados:
   - **Host** (servidor) — geralmente `localhost` (ou algo como `mysql.seusite.com.br`)
   - **Nome do banco** (ex: `goellner_blog`)
   - **Usuário** do banco
   - **Senha** do banco

> Guarde esses 4 valores — você vai usá-los no Passo 3.

---

## PASSO 2 — Enviar os arquivos

1. No **Gerenciador de Arquivos** (ou FTP), entre na pasta pública do site
   (normalmente `public_html` ou `www`).
2. Envie **a pasta inteira** deste blog para dentro dela, com o nome **`blog`**.
   - O endereço final ficará: **`https://goellnerferreira.com.br/blog`**
3. Confirme que a estrutura ficou assim:

```
public_html/
  blog/
    config.php
    db.php
    functions.php
    index.php
    post.php
    setup.php
    blog.css
    .htaccess
    assets/   (logo, símbolo, fotos)
    admin/    (o painel)
```

---

## PASSO 3 — Configurar a conexão

1. Abra o arquivo **`blog/config.php`** (pelo Gerenciador de Arquivos → Editar).
2. Preencha os 4 dados do banco que você anotou no Passo 1:

```php
define('DB_HOST', 'localhost');       // o host do seu banco
define('DB_NAME', 'goellner_blog');   // o nome do banco
define('DB_USER', 'seu_usuario');     // o usuário
define('DB_PASS', 'sua_senha');       // a senha
```

3. (Opcional) Confira que `BLOG_URL` e `SITE_URL` estão com seu domínio correto.
4. **Salve** o arquivo.

---

## PASSO 4 — Rodar o instalador

1. No navegador, acesse: **`https://goellnerferreira.com.br/blog/setup.php`**
2. Defina as **senhas** de acesso da **Silvana** e do **Jonatas**.
   - Logins: `silvana` e `jonatas`
3. Clique em **Instalar o blog**. Pronto — tabelas criadas e um post de exemplo publicado.
4. ⚠️ **MUITO IMPORTANTE:** depois que aparecer “Tudo pronto”, **APAGUE o arquivo `setup.php`**
   do servidor (por segurança). O blog continua funcionando normalmente sem ele.

---

## PASSO 5 — Usar o painel

- **Blog (público):** `https://goellnerferreira.com.br/blog`
- **Painel (login):** `https://goellnerferreira.com.br/blog/admin`

No painel você pode:
- ✍️ **Escrever** posts com texto formatado (negrito, títulos, listas, citações, links)
- 🖼️ Enviar **foto de capa** (ajustada automaticamente)
- 📌 Marcar **post em destaque** (aparece em primeiro no blog)
- 💾 Salvar como **rascunho** ou **publicar**
- 📅 **Agendar** a data de publicação
- 👀 Ver o **número de leituras** de cada post
- 🔑 Trocar sua senha em **Minha conta**

---

## Dúvidas comuns

**“Erro ao conectar ao banco de dados”**
→ Reveja os 4 valores em `config.php`. Na Locaweb o host costuma ser `localhost`; se não funcionar, veja no painel qual é o host do MySQL.

**Quero o blog na raiz (`/`) e não em `/blog`**
→ Envie os arquivos direto na `public_html` e ajuste `BLOG_URL`/`SITE_URL` no `config.php`.

**Adicionar mais autores ou categorias**
→ Categorias: edite a lista `$GF_CATEGORIES` no `config.php`.
→ Autores: posso te entregar uma telinha de “usuários” no painel — é só pedir.

**Esqueci a senha do painel**
→ Reenvie o `setup.php`, rode de novo (ele atualiza as senhas) e apague o arquivo outra vez.

---

Desenvolvido por **NovaRotaMkt** · Identidade visual Goellner Ferreira.
