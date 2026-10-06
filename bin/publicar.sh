#!/usr/bin/env bash
# Copia o sistema para a pasta sistema/ do repositório do site, que o deploy por FTP do site
# publica em public_html/sistema (raiz do subdomínio sistema.645turismo.com.br).
# Ficam de fora: configuração local, banco de teste, uploads, logs e arquivos de desenvolvimento.
set -euo pipefail
cd "$(dirname "$0")/.."
destino="${1:-../site/sistema}"
mkdir -p "$destino"
git ls-files -co --exclude-standard \
  | grep -Ev '^(\.github/|\.claude/|deploy/|docs/|producao/|README\.md$|\.gitattributes$|\.gitignore$|bin/seed-dev\.php$|bin/publicar\.sh$)' \
  | tar -cf - -T - | tar -xf - -C "$destino"
echo "Sistema copiado para $destino. Agora faça commit e push no repositório do site."
