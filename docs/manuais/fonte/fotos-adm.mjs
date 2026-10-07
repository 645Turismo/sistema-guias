// Capturas do Manual do ADM (computador 1366 x 860, retina). Dados fictícios do servidor de teste local.
import { abrirNavegador } from './cdp.mjs';
const B = 'http://localhost:8080';
const D = new URL('./fotos/', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const espera = ms => new Promise(r => setTimeout(r, ms));
const { pagina: p, fechar } = await abrirNavegador(D + '../perfil-chrome-adm');
await p.tamanho(1366, 860, false);
const foto = (nome, o) => p.foto(D + nome + '.png', o).then(() => console.log('ok', nome));
const rolar = (sel, ajuste = 90) => p.js(`const e = document.querySelector(${JSON.stringify(sel)}); if (!e) throw new Error('sem ' + ${JSON.stringify(sel)}); e.scrollIntoView({block: 'start'}); window.scrollBy(0, -${ajuste}); await new Promise(r => setTimeout(r, 400));`);
const submeter = async () => { await espera(2500); };
try {
  await p.ir(B + '/admin');
  await foto('a01-entrar');
  await p.js(`document.querySelector('[name=email]').value = 'adm@645turismo.test'; document.querySelector('[name=senha]').value = 'Adm2026teste'; document.querySelector('[name=senha]').form.submit();`);
  await submeter();
  if (!process.env.SO_RESTO) {
  await p.ir(B + '/admin/hoje'); await foto('a02-hoje');
  await p.ir(B + '/admin/viagens'); await foto('a03-viagens');
  await p.ir(B + '/admin/viagens/nova'); await foto('a04-nova');
  await rolar('[name="origens[]"]', 160); await foto('a05-nova-origens');
  await rolar('[name=veiculo]', 160); await foto('a06-nova-veiculo');
  await p.ir(B + '/admin/viagens/3'); await foto('a07-viagem');
  await rolar('#dias'); await foto('a08-dias-vagas');
  await rolar('#equipe'); await foto('a09-escalar');
  await p.ir(B + '/admin/viagens/1/passageiros', 2000); await foto('a10-passageiros');
  await rolar('#importar', 120);
  await p.js(`document.querySelector('#importar').open = true; await new Promise(r => setTimeout(r, 300));`);
  await rolar('#importar', 120); await foto('a11-importar');
  } else { await p.ir(B + '/admin/viagens/1/passageiros', 1500); }
  const linhas = ['Nome completo;Embarque;Poltrona;Tipo de passageiro', 'Luiza Prado;São Paulo — Metrô Tietê;8;Adulto', 'Tiago Prado;São Paulo — Metrô Tietê;8;Criança de colo', 'Marcos Dias;Campinas;1;Adulto'].join(String.fromCharCode(10));
  await p.js(`document.querySelector('#importar textarea').value = ${JSON.stringify(linhas)}; document.querySelector('#importar form').submit();`);
  await submeter(); await foto('a12-importar-previa');
  await p.ir(B + '/admin/viagens/nova?copiar=2'); await foto('a13-copiar');
  await p.ir(B + '/admin/guias?aba=todos'); await foto('a14-guias');
  await p.ir(B + '/admin/guias/1'); await foto('a15-guia-ficha');
  await p.ir(B + '/admin/guias/pre-cadastro'); await foto('a16-pre-cadastro');
  const { root } = await p.s('DOM.getDocument', {});
  const { nodeId } = await p.s('DOM.querySelector', { nodeId: root.nodeId, selector: 'input[name=arquivo]' });
  await p.s('DOM.setFileInputFiles', { nodeId, files: [D.replace(/fotos\/$/, '') + 'pre-exemplo.csv'] });
  await p.js(`document.querySelector('input[name=arquivo]').form.submit();`);
  await submeter(); await rolar('#previa', 120); await foto('a17-pre-previa');
  await p.js(`document.querySelector('form[action$="cancelar"]').submit();`); await submeter();
  await p.ir(B + '/admin/conferencia'); await foto('a18-conferencia');
  await p.ir(B + '/admin/pagamentos'); await foto('a19-pagamentos');
  await p.ir(B + '/admin/atendimento'); await foto('a20-atendimento');
  await p.ir(B + '/admin/conteudo'); await foto('a21-conteudo');
  await p.ir(B + '/admin/funcoes'); await foto('a22-funcoes');
  await p.ir(B + '/admin/equipe'); await foto('a23-equipe');
} catch (e) { console.error('ERRO', e.message); } finally { await fechar(); }
