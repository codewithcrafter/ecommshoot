const http = require('node:http');
const fs = require('node:fs');

function getJson(url) {
  return new Promise((resolve, reject) => {
    http.get(url, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try { resolve(JSON.parse(data)); } catch (e) { reject(e); }
      });
    }).on('error', reject);
  });
}

class CDPClient {
  constructor(wsUrl) {
    this.wsUrl = wsUrl;
    this.ws = null;
    this.id = 1;
    this.pending = new Map();
  }

  connect() {
    return new Promise((resolve, reject) => {
      this.ws = new WebSocket(this.wsUrl);
      this.ws.onopen = () => resolve();
      this.ws.onerror = (err) => reject(err);
      this.ws.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        if (msg.id && this.pending.has(msg.id)) {
          const { resolve, reject } = this.pending.get(msg.id);
          this.pending.delete(msg.id);
          if (msg.error) reject(new Error(msg.error.message));
          else resolve(msg.result);
        }
      };
    });
  }

  send(method, params = {}) {
    return new Promise((resolve, reject) => {
      const msgId = this.id++;
      this.pending.set(msgId, { resolve, reject });
      this.ws.send(JSON.stringify({ id: msgId, method, params }));
    });
  }

  close() {
    if (this.ws) this.ws.close();
  }
}

async function captureSections() {
  const versionInfo = await getJson('http://127.0.0.1:9222/json/version');
  const browser = new CDPClient(versionInfo.webSocketDebuggerUrl);
  await browser.connect();

  const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
  const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
  await page.connect();

  await page.send('Page.enable');
  await page.send('DOM.enable');

  await page.send('Emulation.setDeviceMetricsOverride', {
    width: 375,
    height: 812,
    deviceScaleFactor: 2,
    mobile: true
  });

  await page.send('Page.navigate', { url: 'http://127.0.0.1:8000/' });
  await new Promise(r => setTimeout(r, 1000));

  // Capture full page screenshot
  const fullScr = await page.send('Page.captureScreenshot', {
    format: 'png',
    captureBeyondViewport: true
  });
  fs.writeFileSync('screenshots/mobile_full_home_375.png', Buffer.from(fullScr.data, 'base64'));
  console.log('Saved screenshots/mobile_full_home_375.png');

  // Check section widths and bounding rects
  const sectionReport = await page.send('Runtime.evaluate', {
    expression: `(() => {
      const sections = Array.from(document.querySelectorAll('section, header, footer, .top-dispatch-bar'));
      return sections.map(s => {
        const r = s.getBoundingClientRect();
        const cls = s.className || s.tagName;
        return {
          tag: s.tagName,
          class: cls,
          width: r.width,
          scrollWidth: s.scrollWidth,
          left: r.left,
          right: r.right,
          overflow: s.scrollWidth > window.innerWidth
        };
      });
    })()`,
    returnByValue: true
  });

  console.log('SECTIONS REPORT:', JSON.stringify(sectionReport.result.value, null, 2));

  await page.send('Target.closeTarget', { targetId });
  page.close();
  browser.close();
}

captureSections().catch(console.error);

