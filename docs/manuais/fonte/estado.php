<?php
// Alterna o guia de exemplo (id 1) entre "pré-cadastro com senha temporária" e o estado normal, só para as capturas.
chdir('C:/Users/marcu/Desktop/645turismo-sistema');
require 'app/bootstrap.php';
if (($argv[1] ?? '') === 'pre') {
  atualizar('guias', ['status' => 'pre_cadastro', 'trocar_senha' => 1, 'cadastro_etapa' => 4, 'senha_hash' => hash_senha('Temp2026abc'),
    'senha_temporaria_expira' => date('Y-m-d H:i:s', strtotime('+7 days'))], 'id = 1');
} else {
  atualizar('guias', ['status' => 'aprovado', 'trocar_senha' => 0, 'cadastro_etapa' => 5, 'senha_hash' => hash_senha('Guia2026teste'),
    'senha_temporaria_expira' => null], 'id = 1');
}
echo "estado {$argv[1]}\n";
