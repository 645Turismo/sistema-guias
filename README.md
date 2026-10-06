# Sistema de Guias — 645 Turismo

Área logada dos guias e Painel ADM, publicada em **https://sistema.645turismo.com.br**.
PHP 8 + MySQL, sem framework e sem build. Não aparece em buscadores (cabeçalho `X-Robots-Tag: noindex` em todas as respostas).

## Fluxo

**ADM:** Hoje → Viagens/Tours (cadastro com código `TR.R.20261010.1`, origem/destino, horários, pernoite, veículo e poltronas bloqueadas, contatos e guia local) → dias de trabalho → vagas por função → lista de passageiros (importada da planilha) → publicar → alocar guias (vê quem está disponível, sem marcação ou já em outra viagem no mesmo dia; convite ou alocação direta) → relatórios.

**Guia:** Hoje (próximo trabalho e pendências) → Viagens/Tours (aceitar/recusar convite, briefing, contatos) → **Lista de passageiros** (tabela no formato da planilha + mapa do carro, check-in/check-out ao vivo, ajustes destacados em amarelo com e-mail para a coordenação) → relatório da viagem em texto.

Lista de passageiros: importação de planilha .xlsx/.csv (cabeçalho reconhecido pelo nome, prévia antes de gravar, repetidos pulados, modelo para baixar).

Todas as telas são pensadas primeiro para o celular (onde os guias usam): tabelas viram cartões, sem rolagem lateral.

Ainda em construção: Disponibilidade, Recebimentos, Perfil e Ajuda (guia); Guias, Conferência, Pagamentos, Atendimento e Conteúdo (ADM); cadastro público de novos guias.

## Rodar localmente

```bash
cp config.example.php config.local.php   # ambiente 'dev' usa SQLite e grava e-mails em storage/logs/mail.log
php bin/seed-dev.php                     # cria o banco de teste (credenciais no cabeçalho do arquivo)
php -S localhost:8080 bin/servidor.php
```

## Instalar na Locaweb (sistema.645turismo.com.br)

1. No painel: criar o subdomínio `sistema` apontando para uma pasta própria, ativar **SSL** e escolher **PHP 8.x**.
2. Criar um banco **MySQL** e uma conta de e-mail para envios (ex.: `nao-responda@645turismo.com.br`).
3. Enviar os arquivos (o workflow `.github/workflows/deploy.yml` faz isso a cada push na `main`, usando os secrets `FTP_HOST`, `FTP_USUARIO`, `FTP_SENHA` e `FTP_PASTA`).
4. No servidor, criar `config.local.php` a partir de `config.example.php` com `'ambiente' => 'prod'`, a URL, o MySQL, o SMTP e um `setup_token` longo e aleatório.
5. Acessar `https://sistema.645turismo.com.br/instalar`, informar o token e criar o primeiro administrador.
6. **Apagar o `setup_token`** do `config.local.php`.

As pastas `app/`, `database/`, `storage/` e `bin/` são bloqueadas para a web (`.htaccess`). Documentos e fotos ficam em `storage/uploads`, servidos só por script com login.
