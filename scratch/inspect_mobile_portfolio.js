const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');

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

async function inspectPortfolio() {
  const versionInfo = await getJson('http://127.0.0.1:9222/json/version');
  const browser = new CDPClient(versionInfo.webSocketDebuggerUrl);
  await browser.connect();

  const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
  const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
  await page.connect();

  await page.send('Page.enable');
  await page.send('DOM.enable');
  await page.send('CSS.enable');

  await page.send('Emulation.setDeviceMetricsOverride', {
    width: 375,
    height: 812,
    deviceScaleFactor: 2,
    mobile: true
  });

  await page.send('Page.navigate', { url: 'http://127.0.0.1:8000/' });
  await new Promise(r => setTimeout(r, 800));

  // Scroll to portfolio section
  await page.send('Runtime.evaluate', {
    expression: `(() => {
      const port = document.getElementById('portfolio');
      if (port) port.scrollIntoView();
    })()`
  });
  await new Promise(r => setTimeout(r, 600));

  const scr = await page.send('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync('screenshots/mobile_portfolio_375.png', Buffer.from(scr.data, 'base64'));
  console.log('Saved screenshots/mobile_portfolio_375.png');

  // Check metrics of portfolio grid and items
  const metrics = await page.send('Runtime.evaluate', {
    expression: `(() => {
      const portSection = document.getElementById('portfolio');
      const grid = document.querySelector('.asymmetric-portfolio-grid');
      const items = Array.from(document.querySelectorAll('.portfolio-grid-item'));
      const filters = document.querySelector('.portfolio-filter-row');
      const container = portSection.querySelector('.container');
      
      const itemData = items.map((item, idx) => {
        const rect = item.getBoundingClientRect();
        const img = item.querySelector('img');
        const overlay = item.querySelector('.portfolio-hover-overlay');
        const title = item.querySelector('.overlay-title');
        const tag = item.querySelector('.overlay-tag');
        const sub = item.querySelector('.overlay-sub');
        const oRect = overlay ? overlay.getBoundingClientRect() : null;
        const iRect = img ? img.getBoundingClientRect() : null;
        return {
          idx,
          width: rect.width,
          height: rect.height,
          left: rect.left,
          right: rect.right,
          imgWidth: iRect ? iRect.width : 0,
          imgHeight: iRect ? iRect.height : 0,
          titleText: title ? title.textContent.trim() : '',
          tagText: tag ? tag.textContent.trim() : '',
          subText: sub ? sub.textContent.trim() : ''
        };
      });

      return {
        viewportWidth: window.innerWidth,
        containerWidth: container ? container.getBoundingClientRect().width : null,
        containerLeft: container ? container.getBoundingClientRect().left : null,
        gridWidth: grid ? grid.getBoundingClientRect().width : null,
        filtersWidth: filters ? filters.getBoundingClientRect().width : null,
        itemCount: items.length,
        items: itemData
      };
    })()`,
    returnByValue: true
  });

  console.log('PORTFOLIO METRICS:', JSON.stringify(metrics.result.value, null, 2));

  await page.send('Target.closeTarget', { targetId });
  page.close();
  browser.close();
}

inspectPortfolio().catch(console.error);

