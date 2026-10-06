<?php
// Dados de TESTE para desenvolvimento local: php bin/seed-dev.php
// Recusa rodar fora do ambiente 'dev'. As credenciais abaixo só existem no banco local de testes.
//
//   Guia de teste:  CPF 529.982.247-25  /  senha Guia2026teste
//   ADM de teste:   adm@645turismo.test /  senha Adm2026teste
if (PHP_SAPI !== 'cli') {
  exit(1);
}
require dirname(__DIR__) . '/app/bootstrap.php';
if (!em_dev()) {
  fwrite(STDERR, "Seed de testes só roda com 'ambiente' => 'dev'.\n");
  exit(1);
}

executar_migracoes();
if (valor("SELECT 1 FROM admins WHERE email = 'adm@645turismo.test'")) {
  echo "Seed já aplicado.\n";
  exit(0);
}

$agora = agora();
$hoje = hoje();
$adminId = inserir('admins', [
  'nome' => 'Administrador de Teste', 'email' => 'adm@645turismo.test',
  'senha_hash' => hash_senha('Adm2026teste'), 'papel' => 'admin', 'ativo' => 1, 'criado_em' => $agora,
]);

$guiaId = inserir('guias', [
  'codigo' => gerar_codigo_guia('52998224725'), 'status' => 'aprovado', 'cadastro_etapa' => 5,
  'nome' => 'Guia de Teste da Silva', 'cpf' => '52998224725', 'celular' => '11999990000',
  'email' => 'guia@645turismo.test', 'nascimento' => '1990-05-10', 'genero' => 'Prefiro não informar',
  'nacionalidade' => 'Brasileiro', 'camiseta' => 'M', 'pcd' => 0, 'aceita_pernoite' => 1,
  'especialidades' => 'Passeios de trem, Excursões rodoviárias',
  'cidade' => 'São Paulo', 'uf' => 'SP', 'cadastur_numero' => '00.000000.00-0', 'cadastur_situacao' => 'Ativo',
  'cadastur_uf' => 'SP', 'cadastur_categorias' => 'Regional, Nacional',
  'senha_hash' => hash_senha('Guia2026teste'), 'termos_aceitos_em' => $agora,
  'aprovado_em' => $agora, 'aprovado_por' => $adminId, 'criado_em' => $agora,
]);
inserir('guia_funcoes', ['guia_id' => $guiaId, 'funcao_id' => 1]);
inserir('guia_idiomas', ['guia_id' => $guiaId, 'idioma' => 'Espanhol', 'nivel' => 'fluente']);

/** Cria uma viagem publicada com um dia de trabalho e escala o guia de teste. */
function seed_viagem(array $dados, string $data, int $guiaId, int $adminId, string $statusEscala): int {
  $id = inserir('viagens', $dados + [
    'tipo' => 'trem', 'data_inicio' => $data, 'data_fim' => $data, 'status' => 'publicada',
    'criado_por' => $adminId, 'criado_em' => agora(),
  ]);
  inserir('viagem_origens', ['viagem_id' => $id, 'local' => $dados['origem'], 'horario' => $dados['horario_saida'], 'ordem' => 0]);
  inserir('viagem_origens', ['viagem_id' => $id, 'local' => 'São Paulo — Metrô Tietê', 'horario' => '07:30', 'ordem' => 1]);
  $vaga = inserir('viagem_vagas', ['viagem_id' => $id, 'funcao_id' => 1, 'vagas' => 1, 'valor_diaria' => 250]);
  $diaria = inserir('diarias', [
    'viagem_id' => $id, 'data' => $data, 'horario_apresentacao' => $dados['horario_apresentacao'],
    'hora_inicio' => $dados['horario_saida'], 'hora_fim' => $dados['previsao_chegada'],
  ]);
  inserir('escalas', [
    'diaria_id' => $diaria, 'guia_id' => $guiaId, 'viagem_vaga_id' => $vaga, 'status' => $statusEscala,
    'valor' => 250, 'convidado_por' => $adminId, 'convidado_em' => agora(),
  ]);
  return $id;
}

$base = [
  'nome' => 'Trem da República — Rodoviário',
  'cliente' => 'Venda direta',
  'origem' => 'São Paulo — Metrô Barra Funda',
  'destino' => 'Jundiaí — Estação Jundiaí',
  'horario_apresentacao' => '06:30', 'horario_saida' => '07:00', 'horario_saida_destino' => '16:30', 'previsao_chegada' => '18:00',
  'ponto_encontro' => 'Terminal Rodoviário Barra Funda, plataforma de fretamento',
  'veiculo' => 'onibus_46', 'poltronas_bloqueadas' => '1, 2', 'transporte' => 'Viação Exemplo · placa ABC1D23',
  'coordenador_nome' => 'Coordenação 645', 'coordenador_telefone' => '11952504927',
  'motorista_nome' => 'Sr. Antônio', 'motorista_telefone' => '11988887777',
  'guia_local_nome' => 'Carla (receptivo Jundiaí)', 'guia_local_telefone' => '11977776666',
  'uniforme' => 'Camiseta 645 e crachá', 'alimentacao' => 'Almoço livre no destino',
  'roteiro' => "07:00 Saída da Barra Funda\n09:00 Embarque no trem histórico\n12:00 Almoço livre\n16:30 Saída de Jundiaí\n18:00 Chegada prevista",
  'regras' => 'Conferir documento com foto no embarque. Crianças só com responsável.',
];
$hojeId = seed_viagem($base + ['codigo' => 'TR.R.' . date('Ymd') . '.1'], $hoje, $guiaId, $adminId, 'confirmado');
seed_viagem(array_merge($base, ['codigo' => 'TR.R.' . date('Ymd', strtotime('+7 days')) . '.1']),
  date('Y-m-d', strtotime('+7 days')), $guiaId, $adminId, 'convidado');

inserir('viagem_contatos', ['viagem_id' => $hojeId, 'papel' => 'Atrativo', 'nome' => 'Bilheteria Trem da República', 'telefone' => '1145550000', 'ordem' => 0]);

// Passageiros: reservas (Venda) com 1 a 3 pessoas, poltronas a partir da 3 (1 e 2 bloqueadas).
$nomes = ['Ana Paula Ribeiro', 'Bruno Carvalho', 'Carla Mendes', 'Daniel Souza', 'Eduarda Lima', 'Felipe Rocha', 'Gabriela Nunes',
  'Henrique Alves', 'Isabela Martins', 'João Pedro Costa', 'Karina Duarte', 'Lucas Ferreira', 'Mariana Teixeira', 'Nicolas Barbosa',
  'Olívia Castro', 'Paulo Henrique Dias', 'Quitéria Santos', 'Rafael Moreira', 'Sabrina Pires', 'Thiago Araújo', 'Úrsula Campos',
  'Vinícius Gomes', 'Wesley Cardoso', 'Yasmin Correia', 'Zeca Monteiro', 'Beatriz Lopes', 'Caio Fernandes', 'Débora Azevedo',
  'Elisa Pacheco', 'Fábio Rezende', 'Giovana Prado', 'Hugo Batista', 'Íris Cavalcanti', 'Jonas Freitas'];
$tamanhos = [3, 1, 2, 2, 1, 3, 2, 1, 2, 2, 3, 1, 2, 2, 1, 2, 2, 2];
$poltrona = 3;
$i = 0;
foreach ($tamanhos as $r => $tam) {
  for ($k = 0; $k < $tam && $i < count($nomes); $k++, $i++) {
    passageiro_incluir($hojeId, [
      'nome' => $nomes[$i],
      'tipo_documento' => $i % 5 === 4 ? 'Certidão de nascimento' : ($i % 3 ? 'RG' : 'CPF'),
      'documento' => $i % 3 ? sprintf('%02d.%03d.%03d-%d', 10 + $i, 100 + $i * 7, 200 + $i * 3, $i % 10) : sprintf('%03d.%03d.%03d-%02d', 100 + $i, 200 + $i, 300 + $i, $i),
      'nascimento' => date('Y-m-d', strtotime('-' . (8 + $i * 2) . ' years -' . ($i * 11) . ' days')),
      'venda' => 'V-' . (1020 + $r),
      'embarque' => $i % 4 === 3 ? 'Metrô Tietê' : 'Barra Funda',
      'observacao' => $i === 4 ? 'Vegetariana' : ($i === 9 ? 'Usa cadeira de rodas dobrável' : null),
      'poltrona' => (string) $poltrona++,
      'telefone' => $k === 0 ? sprintf('1199%07d', 1000000 + $r * 731) : null,
    ], 'admin', $adminId);
  }
}

inserir('avisos', [
  'titulo' => 'Bem-vindo ao novo sistema', 'mensagem' => 'Convites, briefing, lista de passageiros e pagamentos agora ficam todos aqui.',
  'publico' => 'todos', 'ativo' => 1, 'criado_por' => $adminId, 'criado_em' => $agora,
]);

echo "Seed de testes aplicado. Credenciais no cabeçalho deste arquivo.\n";
