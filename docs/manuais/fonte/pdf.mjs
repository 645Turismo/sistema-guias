import { abrirNavegador } from './cdp.mjs';
const [entrada, saida] = process.argv.slice(2);
const dir = new URL('./', import.meta.url).pathname.replace(/^\/([A-Z]:)/, '$1');
const { pagina, fechar } = await abrirNavegador(dir + 'perfil-chrome-pdf');
await pagina.ir('file:///' + dir + entrada, 2500);
await pagina.pdf(saida);
await fechar();
console.log('pdf', saida);
