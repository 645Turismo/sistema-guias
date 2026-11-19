# LGPD no sistema da 645 Turismo

Resumo técnico para a equipe. O texto para o público está em `/privacidade` e `/termos` (`app/views/publico/legal.php`).
Revise com assessoria jurídica antes de tratar como definitivo.

## Dados e bases legais
| Dado | Onde | Base legal |
|---|---|---|
| Cadastro, contatos, endereço, CPF, nascimento | `guias` | Execução de contrato / procedimentos preliminares (art. 7, V) |
| CNPJ, banco, PIX | `guias`, `guia_dados_bancarios` | Execução de contrato; obrigação fiscal (art. 7, II) |
| **Saúde** (PcD, restrição alimentar, doença) | `guias.pcd*`, `restricao_alimentar`, `doencas_preexistentes` | **Consentimento específico** (art. 11, I), com data em `guias.consentimento_saude_em` |
| Passageiros (nome, documento, nascimento, telefone) | `passageiros` | Execução de contrato / obrigação legal; a 645 é controladora |
| Acesso (IP, auditoria) | `auditoria`, `login_tentativas` | Legítimo interesse (segurança) |

## O que o sistema faz
- **Consentimento de saúde**: checkbox separado no cadastro e no perfil (`guia_form/pessoais.php`). Sem marcar, não salva dado de saúde. "Alimentação padrão" não conta como dado sensível. Revogação em Perfil → Privacidade apaga os campos.
- **Versão da política aceita**: `guias.termos_versao` (constante `POLITICA_VERSAO` em `app/lib/lgpd.php`). Ao mudar o texto da política, altere a constante.
- **Direitos do titular** (Perfil → Privacidade): baixar cópia dos dados (JSON, sem hash de senha), pedido de acesso/correção/exclusão/portabilidade (vira chamado com motivo "Privacidade (LGPD)", prazo de 15 dias) e revogação do consentimento de saúde.
- **Anonimização** (Painel ADM → ficha do prestador → "Anonimizar dados (LGPD)", só coordenador): apaga identificação, contatos, endereço, saúde, documentos, foto, dados bancários e acesso; mantém escalas, relatórios, NFs e pagamentos (obrigação fiscal) sem ligação com a pessoa. Bloqueia se houver convite, escala ou pagamento em andamento.
- **Minimização**: o guia só vê a lista de passageiros até o dia seguinte ao fim da viagem.
- **Retenção automática** (`lgpd_retencao_agendada`, 1x/dia, no fim das requisições, como os lembretes; também `lgpd_retencao_processar()`):

| O quê | Prazo |
|---|---|
| Dados dos passageiros e listas anexadas | 180 dias após `viagens.data_fim` (anonimiza) |
| Cadastro `rascunho` sem atividade | 90 dias (apaga) |
| Localização em `envios` | 180 dias |
| `login_tentativas`, `password_resets` | 30 dias |
| `emails_enviados` | 180 dias |
| `emails_pendentes` (fila de reenvio após erro SMTP 451) | some ao entregar ou após 6 tentativas; descarte de segurança em 3 dias |
| `auditoria` | 730 dias |

Prazos são constantes no topo de `app/lib/lgpd.php`; a política pública cita esses mesmos valores.

## Pendências de decisão (não resolvíveis só no código)
- Designar formalmente o encarregado (DPO, art. 41) e, se quiser, publicar o nome. Hoje o canal é o e-mail `email_alertas_lista` (padrão `contato@645turismo.com.br`).
- Confirmar os prazos de retenção com a assessoria jurídica (passageiros: 180 dias; fiscal: 5 anos).
- Fontes do Google Fonts (`comum/head.php`): enviam o IP ao Google. Para eliminar, hospedar as fontes no próprio servidor.
- Cadastros e passageiros que existiam antes desta versão não têm `termos_versao`/`consentimento_saude_em`; o consentimento de saúde é pedido na próxima edição do perfil.
- Pré-cadastro em massa pelo ADM: a pessoa confirma termos no primeiro acesso; avaliar aviso por e-mail no momento do pré-cadastro.
