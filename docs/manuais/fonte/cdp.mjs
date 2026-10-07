// Pequeno cliente CDP (Chrome DevTools Protocol) para capturar telas e gerar PDF, sem dependências.
import { spawn } from 'node:child_process';
import { writeFileSync, mkdirSync } from 'node:fs';

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORTA = 9333;
const espera = ms => new Promise(r => setTimeout(r, ms));

export async function abrirNavegador(perfilDir) {
  mkdirSync(perfilDir, { recursive: true });
  const proc = spawn(CHROME, ['--headless=new', `--remote-debugging-port=${PORTA}`, `--user-data-dir=${perfilDir}`,
    '--no-first-run', '--no-default-browser-check', '--hide-scrollbars', '--force-color-profile=srgb', 'about:blank'], { stdio: 'ignore' });
  let info;
  for (let i = 0; i < 50; i++) {
    try { info = await (await fetch(`http://127.0.0.1:${PORTA}/json/version`)).json(); break; } catch { await espera(200); }
  }
  const ws = new WebSocket(info.webSocketDebuggerUrl);
  await new Promise(r => ws.addEventListener('open', r, { once: true }));
  let id = 0;
  const pendentes = new Map();
  const eventos = [];
  ws.addEventListener('message', ev => {
    const m = JSON.parse(ev.data);
    if (m.id && pendentes.has(m.id)) {
      const { ok, falha } = pendentes.get(m.id);
      pendentes.delete(m.id);
      m.error ? falha(new Error(JSON.stringify(m.error))) : ok(m.result);
    } else if (m.method) {
      eventos.forEach(f => f(m));
    }
  });
  const enviar = (method, params = {}, sessionId) => new Promise((ok, falha) => {
    const mid = ++id;
    pendentes.set(mid, { ok, falha });
    ws.send(JSON.stringify({ id: mid, method, params, ...(sessionId ? { sessionId } : {}) }));
  });
  const { targetId } = await enviar('Target.createTarget', { url: 'about:blank' });
  const { sessionId } = await enviar('Target.attachToTarget', { targetId, flatten: true });
  const s = (method, params) => enviar(method, params, sessionId);
  await s('Page.enable');
  await s('Runtime.enable');

  const pagina = {
    s,
    async tamanho(largura, altura, celular) {
      await s('Emulation.setDeviceMetricsOverride', { width: largura, height: altura, deviceScaleFactor: 2, mobile: celular });
    },
    async ir(url, extraMs = 700) {
      const carregou = new Promise(r => {
        const f = m => { if (m.method === 'Page.loadEventFired' && m.sessionId === sessionId) { eventos.splice(eventos.indexOf(f), 1); r(); } };
        eventos.push(f);
      });
      await s('Page.navigate', { url });
      await Promise.race([carregou, espera(15000)]);
      await s('Runtime.evaluate', { expression: 'document.fonts ? document.fonts.ready.then(() => 1) : 1', awaitPromise: true });
      await espera(extraMs);
    },
    async js(expressao) {
      const r = await s('Runtime.evaluate', { expression: `(async () => { ${expressao} })()`, awaitPromise: true, returnByValue: true });
      if (r.exceptionDetails) throw new Error('JS: ' + JSON.stringify(r.exceptionDetails.exception?.description || r.exceptionDetails.text));
      return r.result.value;
    },
    async foto(arquivo, { inteira = false, clip = null } = {}) {
      const p = { format: 'png', captureBeyondViewport: inteira };
      if (clip) p.clip = { ...clip, scale: 1 };
      else if (inteira) {
        const { cssContentSize } = await s('Page.getLayoutMetrics');
        p.clip = { x: 0, y: 0, width: cssContentSize.width, height: Math.min(cssContentSize.height, 4000), scale: 1 };
      }
      const { data } = await s('Page.captureScreenshot', p);
      writeFileSync(arquivo, Buffer.from(data, 'base64'));
    },
    async pdf(arquivo) {
      const { data } = await s('Page.printToPDF', { printBackground: true, preferCSSPageSize: true, displayHeaderFooter: false });
      writeFileSync(arquivo, Buffer.from(data, 'base64'));
    },
  };
  return { pagina, fechar: async () => { try { await enviar('Browser.close'); } catch {} proc.kill(); } };
}
