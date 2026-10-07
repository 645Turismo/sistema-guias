# Fonte dos manuais (Guia e Painel ADM)

Os PDFs em `docs/manuais/` são gerados a partir destes arquivos HTML, com o Chrome em modo automático.

1. Com o servidor de teste rodando (`php -S localhost:8080 bin/servidor.php`) e o banco de teste preenchido, gere as capturas:
   `node fotos-guia.mjs` e `node fotos-adm.mjs` (salvam em `fotos/`; `estado.php` alterna o guia de exemplo para mostrar o primeiro acesso).
2. Gere os PDFs:
   `node pdf.mjs manual-guia.html "../Manual do Guia - 645 Turismo.pdf"` e
   `node pdf.mjs manual-adm.html "../Manual do Painel ADM - 645 Turismo.pdf"`.

Os dados das capturas são fictícios. Os scripts usam as credenciais de teste do `bin/seed-dev.php`, que só existem no banco local.
