<?php
// Completa o banco de TESTE (depois do bin/seed-dev.php) com o que os manuais mostram:
// segundo prestador ("Sua equipe"), passageiros Staff e de colo, trabalho terminado (Pós-viagem),
// serviços com horários por dia, sobreaviso, dia sem trabalho e coordenação sem custo.
// Só roda no ambiente dev; os dados são fictícios.
chdir('C:/Users/marcu/Desktop/645turismo-sistema');
require 'app/bootstrap.php';
if (!em_dev()) {
  exit("Só no ambiente dev.\n");
}
$agora = agora();
$dia = fn(int $n) => date('Y-m-d', strtotime(($n >= 0 ? '+' : '') . $n . ' days'));
$adm = (int) valor("SELECT id FROM admins WHERE email = 'adm@645turismo.test'");
$g1 = (int) valor("SELECT id FROM guias WHERE cpf = '52998224725'");
$funcao = fn(string $slug) => (int) valor('SELECT id FROM funcoes WHERE slug = ?', [$slug]);

// Segundo prestador (aparece em "Sua equipe" e na coordenação sem custo).
$g2 = inserir('guias', [
  'codigo' => gerar_codigo_guia('11144477735'), 'status' => 'aprovado', 'cadastro_etapa' => 5,
  'nome' => 'Fernanda Teste Lima', 'cpf' => '11144477735', 'celular' => '11988881234', 'email' => 'fernanda@645turismo.test',
  'nascimento' => '1992-03-02', 'cidade' => 'São Paulo', 'uf' => 'SP', 'senha_hash' => hash_senha('Guia2026teste'),
  'termos_aceitos_em' => $agora, 'aprovado_em' => $agora, 'aprovado_por' => $adm, 'criado_em' => $agora,
]);
inserir('guia_funcoes', ['guia_id' => $g2, 'funcao_id' => $funcao('coordenador')]);
inserir('guia_funcoes', ['guia_id' => $g1, 'funcao_id' => $funcao('recepcionista')]);

// Viagem de hoje (id 1): embarques iguais às origens, tipos de passageiro e um Staff na poltrona bloqueada.
q("UPDATE passageiros SET embarque = 'São Paulo — Metrô Barra Funda' WHERE embarque = 'Barra Funda'");
q("UPDATE passageiros SET embarque = 'São Paulo — Metrô Tietê' WHERE embarque = 'Metrô Tietê'");
q("UPDATE passageiros SET tipo_pax = 'adulto' WHERE viagem_id = 1");
q("UPDATE passageiros SET tipo_pax = 'crianca' WHERE viagem_id = 1 AND nascimento > ?", [$dia(-365 * 12)]);
passageiro_incluir(1, ['nome' => 'Marina Costa (equipe 645)', 'tipo_documento' => 'RG', 'documento' => '12.345.678-9', 'poltrona' => '1',
  'embarque' => 'São Paulo — Metrô Barra Funda', 'tipo_pax' => 'staff', 'observacao' => 'Staff de apoio'], 'admin', $adm);
$vaga1 = (int) valor('SELECT id FROM viagem_vagas WHERE viagem_id = 1');
q('UPDATE viagem_vagas SET vagas = 2 WHERE id = ?', [$vaga1]);
$d1 = (int) valor('SELECT id FROM diarias WHERE viagem_id = 1');
inserir('escalas', ['diaria_id' => $d1, 'guia_id' => $g2, 'viagem_vaga_id' => $vaga1, 'status' => 'confirmado', 'valor' => 250,
  'convidado_por' => $adm, 'convidado_em' => $agora, 'respondido_em' => $agora]);

/** Cria um trabalho publicado com dias e vagas; devolve [id, ids dos dias]. */
function manual_trabalho(array $v, array $dias, int $adm): array {
  $id = inserir('viagens', $v + ['status' => 'publicada', 'criado_por' => $adm, 'criado_em' => agora(),
    'data_inicio' => $dias[0]['data'], 'data_fim' => end($dias)['data']]);
  $ids = [];
  foreach ($dias as $d) {
    $ids[] = inserir('diarias', $d + ['viagem_id' => $id]);
  }
  return [$id, $ids];
}
function manual_escalar(array $dias, int $guia, int $vaga, string $status, array $valores, int $adm): void {
  foreach ($dias as $i => $diaria) {
    inserir('escalas', ['diaria_id' => $diaria, 'guia_id' => $guia, 'viagem_vaga_id' => $vaga, 'status' => $status,
      'valor' => $valores[$i] ?? 0, 'convidado_por' => $adm, 'convidado_em' => agora(), 'respondido_em' => $status === 'convidado' ? null : agora()]);
  }
}

// Viagem terminada há 2 dias: aparece em Pós-viagem esperando relatório.
$baseViagem = um('SELECT nome, cliente, origem, destino, horario_apresentacao, horario_saida, horario_saida_destino, previsao_chegada,
  ponto_encontro, veiculo, poltronas_bloqueadas, transporte, coordenador_nome, coordenador_telefone, uniforme, alimentacao, roteiro, regras
  FROM viagens WHERE id = 1');
[$vt, $dt] = manual_trabalho(['codigo' => 'TR.R.' . date('Ymd', strtotime('-2 days')) . '.1', 'tipo' => 'trem'] + $baseViagem,
  [['data' => $dia(-2), 'horario_apresentacao' => '06:30', 'hora_inicio' => '07:00', 'hora_fim' => '18:00']], $adm);
inserir('viagem_origens', ['viagem_id' => $vt, 'local' => 'São Paulo — Metrô Barra Funda', 'horario' => '07:00', 'ordem' => 0]);
$vagaT = inserir('viagem_vagas', ['viagem_id' => $vt, 'funcao_id' => 1, 'vagas' => 1, 'valor_diaria' => 250]);
manual_escalar($dt, $g1, $vagaT, 'confirmado', [250], $adm);

// Serviço por diária, 3 dias com horários diferentes, sobreaviso e um dia sem trabalho, com pernoite.
[$sv, $ds] = manual_trabalho([
  'codigo' => 'SV.' . date('Ymd', strtotime('+2 days')) . '.1', 'nome' => 'Recepção — Congresso Saúde 2026', 'tipo' => 'recepcao',
  'categoria' => 'servico', 'modalidade' => 'diaria', 'cliente' => 'Associação Exemplo', 'destino' => 'Expo Center Norte — Pavilhão Azul',
  'ponto_encontro' => 'Portão 3, balcão de credenciamento', 'horario_apresentacao' => '07:00', 'horario_saida' => '08:00', 'previsao_chegada' => '18:00',
  'com_pernoite' => 1, 'hospedagem' => 'Hotel Exemplo Norte, café da manhã incluso', 'coordenador_nome' => 'Fernanda (coordenação)',
  'coordenador_telefone' => '11988881234', 'perfil_grupo' => 'Congressistas e palestrantes', 'qtd_passageiros' => 800,
  'uniforme' => 'Blazer preto e crachá 645', 'alimentacao' => 'Almoço no refeitório do evento',
  'roteiro' => "07:00 Apresentação no portão 3\n08:00 Abertura do credenciamento\n12:00 Almoço em turnos\n18:00 Encerramento",
  'regras' => 'Celular só nos intervalos. Dúvidas de palestrantes vão para a coordenação.',
], [
  ['data' => $dia(2), 'horario_apresentacao' => '07:00', 'hora_inicio' => '08:00', 'hora_fim' => '18:00', 'pernoite' => 1, 'situacao' => 'trabalho'],
  ['data' => $dia(3), 'horario_apresentacao' => '09:00', 'hora_inicio' => '10:00', 'hora_fim' => '16:00', 'pernoite' => 1, 'situacao' => 'sobreaviso'],
  ['data' => $dia(4), 'pernoite' => 1, 'situacao' => 'folga'],
  ['data' => $dia(5), 'horario_apresentacao' => '07:00', 'hora_inicio' => '08:00', 'hora_fim' => '14:00', 'situacao' => 'trabalho'],
], $adm);
$vagaR = inserir('viagem_vagas', ['viagem_id' => $sv, 'funcao_id' => $funcao('recepcionista'), 'vagas' => 2, 'valor_diaria' => 280]);
$vagaC = inserir('viagem_vagas', ['viagem_id' => $sv, 'funcao_id' => $funcao('coordenador'), 'vagas' => 1, 'valor_diaria' => 0]);
manual_escalar($ds, $g1, $vagaR, 'confirmado', [280, 280, 280, 280], $adm);
manual_escalar($ds, $g2, $vagaC, 'confirmado', [0, 0, 0, 0], $adm);

// Serviço de bordo com valor fechado: convite pendente.
[$sb, $db] = manual_trabalho([
  'codigo' => 'SV.' . date('Ymd', strtotime('+10 days')) . '.1', 'nome' => 'Serviço de bordo — Trem Republicano', 'tipo' => 'bordo',
  'categoria' => 'servico', 'modalidade' => 'empreitada', 'cliente' => 'Venda direta', 'destino' => 'Estação da Luz → Jundiaí',
  'horario_apresentacao' => '07:30', 'horario_saida' => '08:30', 'previsao_chegada' => '17:00', 'coordenador_nome' => 'Coordenação 645',
  'coordenador_telefone' => '11952504927', 'uniforme' => 'Uniforme de bordo 645',
], [
  ['data' => $dia(10), 'horario_apresentacao' => '07:30', 'hora_inicio' => '08:30', 'hora_fim' => '17:00'],
  ['data' => $dia(11), 'horario_apresentacao' => '07:30', 'hora_inicio' => '08:30', 'hora_fim' => '17:00'],
], $adm);
$vagaB = inserir('viagem_vagas', ['viagem_id' => $sb, 'funcao_id' => $funcao('comissario-bordo'), 'vagas' => 2, 'valor_diaria' => 900]);
manual_escalar($db, $g1, $vagaB, 'convidado', [900, 0], $adm);

// Comentário do prestador durante a viagem de hoje.
inserir('viagem_comentarios', ['viagem_id' => 1, 'guia_id' => $g1, 'texto' => 'Ônibus atrasou 15 minutos na Barra Funda. Já embarcamos.', 'criado_em' => $agora]);

// Introdução já vista: não cobre as capturas.
q('UPDATE guias SET tour_visto = 1');

echo "Dados do manual: viagem terminada $vt, serviço $sv, serviço de bordo $sb.\n";
