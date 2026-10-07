// Capturas do Manual do Guia (celular 390 x 844, retina). Dados fictícios do servidor de teste local.
import { abrirNavegador } from './cdp.mjs';
import { execFileSync } from 'node:child_process';
const estado = e => console.log(execFileSync('C:/Users/marcu/tools/php/php.exe', [new URL('./estado.php', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1'), e]).toString().trim());
const B = 'http://localhost:8080';
const D = new URL('./fotos/', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const espera = ms => new Promise(r => setTimeout(r, ms));

const { pagina: p, fechar } = await abrirNavegador(D + '../perfil-chrome-guia');
await p.tamanho(390, 844, true);
const foto = (nome, o) => p.foto(D + nome + '.png', o).then(() => console.log('ok', nome));
const rolar = sel => p.js(`const e = document.querySelector(${JSON.stringify(sel)}); if (e) { e.scrollIntoView({block: 'start'}); window.scrollBy(0, -70); } await new Promise(r => setTimeout(r, 400));`);
async function entrar(cpf, senha) {
  await p.ir(B + '/');
  await p.js(`document.querySelector('[name=cpf]').value = ${JSON.stringify(cpf)}; document.querySelector('[name=senha]').value = ${JSON.stringify(senha)}; document.querySelector('[name=senha]').form.submit();`);
  await espera(2500);
}

try {
  // Entrada e primeiro acesso
  await p.ir(B + '/');
  await rolar('.painel-acesso');
  await foto('g01-entrar');
  estado('pre');
  await entrar('529.982.247-25', 'Temp2026abc');
  await p.ir(B + '/primeiro-acesso');
  await foto('g02-primeiro-acesso');
  await p.js(`document.querySelector('[name=senha]').value = 'Ewerton2026novo'; document.querySelector('[name=confirmacao]').value = 'Ewerton2026novo'; document.querySelector('[name=senha]').form.submit();`);
  await espera(2500);
  await p.ir(B + '/cadastro/1');
  await foto('g03-cadastro');
  await p.ir(B + '/cadastro/4');
  await p.js(`const c = document.querySelector('[name=nao_emite_nf]'); c.checked = true; c.dispatchEvent(new Event('change'));`);
  await rolar('fieldset');
  await foto('g04-nao-emito-nf');
  await p.s('Network.enable'); await p.s('Network.clearBrowserCookies');
  estado('normal');

  // Guia em atividade
  await entrar('529.982.247-25', 'Guia2026teste');
  await p.ir(B + '/guia/hoje');
  await foto('g05-hoje');
  await p.ir(B + '/guia/viagens?aba=convites');
  await foto('g06-viagens');
  await p.ir(B + '/guia/viagens/3');
  await rolar('.convite');
  await foto('g07-convite');
  await p.ir(B + '/guia/viagens/2');
  await foto('g08-briefing');

  await p.ir(B + '/guia/viagens/1/passageiros', 2000);
  await foto('g09-lista-topo');
  await rolar('[data-corpo] tr');
  await foto('g10-lista-cartoes');
  await p.js(`document.querySelector('[data-corpo] .btn-editar').click(); await new Promise(r => setTimeout(r, 500));`);
  await foto('g11-editar');
  await p.js(`document.querySelector('[data-fechar]').click(); document.querySelector('[data-aba=mapa]').click(); await new Promise(r => setTimeout(r, 400));`);
  await rolar('.carro-moldura');
  await foto('g12-mapa');
  await p.js(`document.querySelector('[data-poltrona="3"]').click(); await new Promise(r => setTimeout(r, 600));`);
  await rolar('[data-info]');
  await foto('g13-poltrona');

  await p.ir(B + '/guia/viagens/1');
  await rolar('#relatorio');
  await foto('g14-relatorio');
  await p.ir(B + '/guia/disponibilidade');
  await foto('g15-disponibilidade');
  await p.ir(B + '/guia/recebimentos');
  await foto('g16-ganhos');
  await p.ir(B + '/guia/perfil');
  await foto('g17-perfil');
  await p.ir(B + '/guia/ajuda');
  await foto('g18-ajuda');
  await p.ir(B + '/guia/ajuda?aba=chamados');
  await rolar('#novo');
  await foto('g19-chamado');
} catch (e) {
  console.error('ERRO', e.message);
} finally {
  await fechar();
}
