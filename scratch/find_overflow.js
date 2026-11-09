const http = require('http');

async function inspect() {
  const list = await new Promise((resolve) => {
    http.get('http://127.0.0.1:9222/json/list', res => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => resolve(JSON.parse(data)));
    });
  });
  const ws = new WebSocket(list[0].webSocketDebuggerUrl);
  let id = 1;
  const send = (method, params = {}) => new Promise((resolve) => {
    const curId = id++;
    const handler = (event) => {
      const msg = JSON.parse(event.data);
      if (msg.id === curId) {
        ws.removeEventListener('message', handler);
        resolve(msg.result);
      }
    };
    ws.addEventListener('message', handler);
    ws.send(JSON.stringify({ id: curId, method, params }));
  });

  ws.addEventListener('open', async () => {
    await send('Emulation.setDeviceMetricsOverride', {
      width: 320,
      height: 700,
      deviceScaleFactor: 2,
      mobile: true
    });
    await send('Page.navigate', { url: 'http://127.0.0.1:8000/index.php' });
    await new Promise(r => setTimeout(r, 1200));

    const evalRes = await send('Runtime.evaluate', {
      expression: `(() => {
        const docW = document.documentElement.clientWidth;
        const overflowing = [];
        document.querySelectorAll('*').forEach(el => {
          const rect = el.getBoundingClientRect();
          if (rect.right > docW + 0.5) {
            overflowing.push({
              tag: el.tagName,
              cls: typeof el.className === 'string' ? el.className : '',
              id: el.id,
              left: Math.round(rect.left),
              right: Math.round(rect.right),
              width: Math.round(rect.width),
              scrollW: el.scrollWidth,
              text: (el.textContent || '').trim().slice(0, 30)
            });
          }
        });
        return {
          clientWidth: docW,
          docScrollWidth: document.documentElement.scrollWidth,
          bodyScrollWidth: document.body.scrollWidth,
          overflowCount: overflowing.length,
          overflowing: overflowing.slice(0, 20)
        };
      })()`,
      returnByValue: true
    });
    console.log(JSON.stringify(evalRes.result.value, null, 2));

    // Also inspect floating buttons at 375px
    await send('Emulation.setDeviceMetricsOverride', {
      width: 375,
      height: 700,
      deviceScaleFactor: 2,
      mobile: true
    });
    await new Promise(r => setTimeout(r, 400));
    const floatRes = await send('Runtime.evaluate', {
      expression: `(() => {
        const dock = document.querySelector('.floating-contact-dock, .floating-actions');
        if (!dock) return 'Dock not found';
        const rect = dock.getBoundingClientRect();
        const style = window.getComputedStyle(dock);
        return {
          rect: { top: rect.top, right: rect.right, bottom: rect.bottom, left: rect.left, width: rect.width, height: rect.height },
          computed: { right: style.right, bottom: style.bottom, position: style.position },
          windowInner: { w: window.innerWidth, h: window.innerHeight }
        };
      })()`,
      returnByValue: true
    });
    console.log('Floating dock info:', JSON.stringify(floatRes.result.value, null, 2));

    process.exit(0);
  });
}
inspect();

