# Sistema de Guias — 645 Turismo

Área logada dos guias e Painel ADM, publicada em **https://sistema.645turismo.com.br**.
PHP 8 + MySQL, sem framework e sem build. Não aparece em buscadores (cabeçalho `X-Robots-Tag: noindex` em todas as respostas).

## Fluxo

**ADM:** Hoje → Viagens/Tours (cadastro com código `TR.R.20261010.1`, origem/destino, horários, pernoite, veículo e poltronas bloqueadas, contatos e guia local) → dias de trabalho → vagas por função → lista de passageiros (importada da planilha) → publicar → alocar guias (vê quem está disponível, sem marcação ou já em outra viagem no mesmo dia; convite ou alocação direta) → relatórios.

**Guia:** Hoje (próximo trabalho e pendências) → Viagens/Tours (aceitar/recusar convite, briefing, contatos) → **Lista de passageiros** (tabela no formato da planilha + mapa do carro, check-in/check-out ao vivo, ajustes destacados em amarelo com e-mail para a coordenação) → relatório da viagem em texto.

Lista de passageiros: importação de planilha .xlsx/.csv (cabeçalho reconhecido pelo nome, prévia antes de gravar, repetidos pulados, modelo para baixar).

Todas as telas são pensadas primeiro para o celular (onde os guias usam): tabelas viram cartões, sem rolagem lateral.

## Rodar localmente

```bash
cp config.example.php config.local.php   # ambiente 'dev' usa SQLite e grava e-mails em storage/logs/mail.log
php bin/seed-dev.php                     # cria o banco de teste (credenciais no cabeçalho do arquivo)
php -S localhost:8080 bin/servidor.php
```

## Publicar na Locaweb (sistema.645turismo.com.br)

O subdomínio aponta para a pasta `public_html/sistema`, que é a pasta `sistema/` do repositório do site (645Turismo/site). O deploy por FTP do site já publica essa pasta, então para publicar:

```bash
bash bin/publicar.sh      # copia o sistema para ../site/sistema (sem config, banco local, uploads e arquivos de dev)
```

Depois é só fazer commit e push no repositório do site. O envio nunca apaga nada no servidor.

**Primeira instalação:** com o servidor ainda sem `config.local.php`, qualquer página abre `/configurar`, que pede o código de configuração (entregue à equipe fora do sistema) e as senhas do MySQL e do e-mail, testa o banco e grava o `config.local.php` no próprio servidor. Em seguida `/instalar` cria as tabelas e o primeiro administrador. Depois de cada nova versão, `/instalar` com o mesmo código aplica as atualizações do banco.

As pastas `app/`, `database/`, `storage/` e `bin/` são bloqueadas para a web (`.htaccess`). Documentos e fotos ficam em `storage/uploads`, servidos só por script com login.
