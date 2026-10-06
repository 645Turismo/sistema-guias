# Sistema de Guias 645 Turismo — especificação

Área logada para os guias e Painel ADM da 645 Turismo, em **sistema.645turismo.com.br**.
O código fica num repositório próprio (`645turismo-sistema`), separado deste site. Este arquivo é só documentação interna e não é publicado (a pasta `docs/` está fora do deploy).

## Princípios
- Fluxo pensado para a rotina do guia de turismo: convite → briefing → lista de passageiros em campo → relatório → nota fiscal → pagamento.
- Identidade visual da 645: preto, #05100C, menta #53D9B2, amarelo #FADA28, vermelho de alerta #E8674F; fontes Montserrat e Newsreader.
- Nomenclatura: **Viagem/Tour**. Cada viagem tem um **código livre** definido pela equipe (ex.: `TR.R.20261010.1`).
- Nada do sistema aparece em buscadores (`noindex` em todas as respostas).

## Área do Guia
| Menu | O que faz |
|---|---|
| **Hoje** | Próximo trabalho em destaque, pendências (convites, relatórios, notas fiscais, respostas da equipe), próximos dias e recados da 645. |
| **Viagens/Tours** | Convites (aceitar ou recusar), confirmadas e realizadas. No detalhe: horários, origens, briefing, contatos (liberados após confirmar), relatório em texto. |
| **Lista de passageiros** | Tabela no formato da planilha da 645 + mapa do carro. Check-in e check-out ao vivo, telefone para ligar, reserva destacada no mapa, ajustes do guia em amarelo com aviso por e-mail. |
| **Disponibilidade** | *(próxima etapa)* Dias e períodos livres, aceite de pernoite. |
| **Recebimentos** | *(próxima etapa)* Diárias, nota fiscal, previsão de pagamento e comprovantes. |
| **Perfil** | *(próxima etapa)* Dados, Cadastur, idiomas, especialidades, regiões, documentos, dados bancários e senha. |
| **Ajuda** | *(próxima etapa)* Perguntas frequentes, guia prático e chamados (inclui aviso de imprevisto). |

Código do guia: 3 primeiros + 3 últimos dígitos do CPF (com sufixo -2, -3 se repetir).

## Painel ADM
| Menu | O que faz |
|---|---|
| **Hoje** | Viagens em campo, fila de trabalho e valor a pagar. |
| **Viagens/Tours** | Cadastro (código, origens com horário, destino, apresentação, saída, saída do destino, chegada, pernoite e hospedagem, veículo e poltronas bloqueadas, coordenação, motorista, guia local e outros contatos), dias de trabalho, vagas por função, lista de passageiros, publicação e alocação de guias (convite ou direto, vendo disponibilidade e conflitos). |
| **Funções** | Cadastro das funções exercidas pelos guias. |
| **Equipe** | Acessos ao painel por perfil (Administrador, Coordenador, Financeiro). |
| **Guias, Conferência, Pagamentos, Atendimento, Conteúdo** | *(próximas etapas)* |

## Lista de passageiros
- Colunas: Nome completo, Tipo do documento, Documento, Data de nascimento, Venda (nº da reserva), Embarque (local), Observação, Poltrona, Telefone.
- Importação da planilha em **.xlsx ou .csv** (ou colando linhas); cabeçalho reconhecido pelo nome, em qualquer ordem.
- Veículos: Micro-ônibus 26, Ônibus Padrão 46, Ônibus Padrão 50, Ônibus DD 64 (piso superior e inferior), ou outro tipo com descrição e capacidade.
- Alterações do guia avisam por e-mail o endereço configurado (hoje contato@645turismo.com.br).

## Infraestrutura (Locaweb)
Subdomínio `sistema` com SSL, PHP 8, banco MySQL, conta de e-mail para envios (SPF/DKIM), backup diário e cron para lembretes. Instalação pela página `/instalar` (detalhes no README do repositório do sistema).
