/**
 * ECOMSHOOT - Fixed Navbar & Viewport Scroll Verification Test Suite
 * Tests sticky fixed positioning, z-index, dropdown placement, mobile drawer,
 * and content scroll underneath the header from top to bottom on all major pages.
 */
const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');

const SCREENSHOT_DIR = path.resolve(__dirname, '../screenshots');
if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

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

async function captureScreenshot(page, filename) {
  const result = await page.send('Page.captureScreenshot', { format: 'png' });
  const filepath = path.join(SCREENSHOT_DIR, filename);
  fs.writeFileSync(filepath, Buffer.from(result.data, 'base64'));
}

async function runTestSuite() {
  console.log('================================================================');
  console.log('ECOMSHOOT - FIXED NAVBAR SCROLL & POSITIONING VERIFICATION');
  console.log('================================================================\n');

  const versionInfo = await getJson('http://127.0.0.1:9222/json/version');
  const browser = new CDPClient(versionInfo.webSocketDebuggerUrl);
  await browser.connect();

  const pagesToTest = [
    { name: 'Home', url: 'http://127.0.0.1:8000/' },
    { name: 'About', url: 'http://127.0.0.1:8000/about.php' },
    { name: 'Services Directory', url: 'http://127.0.0.1:8000/services.php' },
    { name: 'Service Detail', url: 'http://127.0.0.1:8000/product-photography.php' },
    { name: 'Blog Listing', url: 'http://127.0.0.1:8000/blog.php' },
    { name: 'Blog Detail', url: 'http://127.0.0.1:8000/blog/1' },
    { name: 'Contact', url: 'http://127.0.0.1:8000/contact.php' }
  ];

  let totalTests = 0;
  let passedTests = 0;

  function assert(condition, message) {
    totalTests++;
    if (condition) {
      passedTests++;
      console.log(`  [PASS] ${message}`);
    } else {
      console.error(`  [FAIL] ${message}`);
    }
  }

  // -------------------------------------------------------------
  // TEST SECTION 1: SCROLL FROM TOP TO BOTTOM ON ALL MAJOR PAGES
  // -------------------------------------------------------------
  console.log('--- 1. SCROLL FROM TOP TO BOTTOM ON ALL 7 MAJOR PAGES (DESKTOP 1440px) ---');

  for (const p of pagesToTest) {
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
    const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
    await page.connect();

    await page.send('Page.enable');
    await page.send('DOM.enable');
    await page.send('CSS.enable');

    await page.send('Emulation.setDeviceMetricsOverride', {
      width: 1440,
      height: 900,
      deviceScaleFactor: 1,
      mobile: false
    });

    await page.send('Page.navigate', { url: p.url });
    await new Promise(r => setTimeout(r, 600));

    // Verify initial state at scroll = 0
    const topState = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const header = document.querySelector('.site-header');
        const topBar = document.querySelector('.top-dispatch-bar');
        const body = document.body;
        const hRect = header ? header.getBoundingClientRect() : null;
        const tRect = topBar ? topBar.getBoundingClientRect() : null;
        const hStyle = window.getComputedStyle(header);
        const bStyle = window.getComputedStyle(body);
        return {
          position: hStyle.position,
          zIndex: parseInt(hStyle.zIndex, 10),
          headerTop: hRect ? hRect.top : -999,
          headerBottom: hRect ? hRect.bottom : -999,
          topBarTop: tRect ? tRect.top : null,
          topBarBottom: tRect ? tRect.bottom : null,
          bodyPaddingTop: parseFloat(bStyle.paddingTop),
          docHeight: document.documentElement.scrollHeight
        };
      })()`,
      returnByValue: true
    });

    const ts = topState.result.value;
    assert(ts.position === 'fixed', `${p.name}: .site-header has position: fixed`);
    assert(ts.zIndex >= 1000, `${p.name}: .site-header has z-index >= 1000 (${ts.zIndex})`);
    assert(ts.bodyPaddingTop >= 100, `${p.name}: body padding-top (${ts.bodyPaddingTop}px) correctly offsets fixed header`);

    // Scroll to 25%, 50%, 75%, and 100% (bottom of page)
    const scrollPoints = [0.25, 0.50, 0.75, 1.0];
    let allPointsPassed = true;

    for (const ratio of scrollPoints) {
      const scrollYTarget = Math.round((ts.docHeight - 900) * ratio);
      await page.send('Runtime.evaluate', { expression: `window.scrollTo(0, ${scrollYTarget});` });
      await new Promise(r => setTimeout(r, 200));

      const scrollState = await page.send('Runtime.evaluate', {
        expression: `(() => {
          const header = document.querySelector('.site-header');
          const hRect = header.getBoundingClientRect();
          const hasScrolledClass = header.classList.contains('is-scrolled');
          return {
            scrollY: window.scrollY,
            headerTop: hRect.top,
            headerBottom: hRect.bottom,
            headerHeight: hRect.height,
            isLockedAtTop: hRect.top >= 0 && hRect.top < 70,
            isScrolled: hasScrolledClass
          };
        })()`,
        returnByValue: true
      });

      const ss = scrollState.result.value;
      if (!ss.isLockedAtTop) {
        allPointsPassed = false;
        console.error(`  [FAIL] ${p.name} at scrollY=${ss.scrollY} header slipped: top=${ss.headerTop}`);
      }
    }

    assert(allPointsPassed, `${p.name}: Navbar remains locked at top throughout full scroll to bottom`);

    // Capture screenshot at bottom
    const safeName = p.name.toLowerCase().replace(/[^a-z0-9]/g, '_');
    await captureScreenshot(page, `fixed_navbar_${safeName}_bottom.png`);

    await page.send('Target.closeTarget', { targetId });
    page.close();
  }

  // -------------------------------------------------------------
  // TEST SECTION 2: MOBILE VIEWPORT VERIFICATION (375px & 320px)
  // -------------------------------------------------------------
  console.log('\n--- 2. MOBILE VIEWPORT VERIFICATION (375px & 320px) ---');

  const mobileViewports = [
    { name: 'iPhone Standard', width: 375, height: 812 },
    { name: 'iPhone SE', width: 320, height: 568 }
  ];

  for (const vp of mobileViewports) {
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
    const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
    await page.connect();

    await page.send('Page.enable');
    await page.send('DOM.enable');
    await page.send('CSS.enable');

    await page.send('Emulation.setDeviceMetricsOverride', {
      width: vp.width,
      height: vp.height,
      deviceScaleFactor: 2,
      mobile: true
    });

    await page.send('Page.navigate', { url: 'http://127.0.0.1:8000/' });
    await new Promise(r => setTimeout(r, 600));

    // Scroll to 800px on mobile
    await page.send('Runtime.evaluate', { expression: `window.scrollTo(0, 800);` });
    await new Promise(r => setTimeout(r, 300));

    const mobileScroll = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const header = document.querySelector('.site-header');
        const hRect = header.getBoundingClientRect();
        const toggle = document.getElementById('mobileMenuToggle');
        const tRect = toggle.getBoundingClientRect();
        return {
          scrollY: window.scrollY,
          headerTop: hRect.top,
          headerBottom: hRect.bottom,
          headerWidth: hRect.width,
          viewportWidth: window.innerWidth,
          isHeaderVisible: hRect.top >= 0 && hRect.bottom > 0,
          toggleVisible: tRect.top >= 0 && tRect.bottom <= hRect.bottom && tRect.right <= window.innerWidth
        };
      })()`,
      returnByValue: true
    });

    const ms = mobileScroll.result.value;
    assert(ms.isHeaderVisible, `${vp.name} (${vp.width}px): Navbar remains continuously visible when scrolled`);
    assert(ms.toggleVisible, `${vp.name} (${vp.width}px): Hamburger toggle remains visible and within viewport bounds`);

    // Test mobile menu toggle while scrolled
    await page.send('Runtime.evaluate', {
      expression: `document.getElementById('mobileMenuToggle').click();`
    });
    await new Promise(r => setTimeout(r, 300));

    const drawerCheck = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const nav = document.getElementById('mainNav');
        const header = document.querySelector('.site-header');
        const nRect = nav.getBoundingClientRect();
        const hRect = header.getBoundingClientRect();
        return {
          isActive: nav.classList.contains('is-active'),
          navTop: nRect.top,
          headerBottom: hRect.bottom,
          isBelowHeader: nRect.top >= hRect.bottom - 1,
          navBottom: nRect.bottom,
          windowHeight: window.innerHeight,
          fitsViewport: nRect.bottom <= window.innerHeight + 2
        };
      })()`,
      returnByValue: true
    });

    const dc = drawerCheck.result.value;
    assert(dc.isActive, `${vp.name}: Mobile navigation drawer opens when clicked`);
    assert(dc.isBelowHeader, `${vp.name}: Mobile navigation drawer positions cleanly below the fixed header`);
    assert(dc.fitsViewport, `${vp.name}: Mobile navigation drawer fits available viewport without overflow`);

    await captureScreenshot(page, `fixed_navbar_mobile_drawer_${vp.width}.png`);

    // Close drawer
    await page.send('Runtime.evaluate', {
      expression: `document.getElementById('mobileMenuToggle').click();`
    });
    await new Promise(r => setTimeout(r, 200));

    await page.send('Target.closeTarget', { targetId });
    page.close();
  }

  // -------------------------------------------------------------
  // TEST SECTION 3: SERVICES DROPDOWN INTERACTION WHEN SCROLLED
  // -------------------------------------------------------------
  console.log('\n--- 3. SERVICES DROPDOWN INTERACTION WHEN SCROLLED (DESKTOP) ---');
  {
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
    const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
    await page.connect();

    await page.send('Page.enable');
    await page.send('DOM.enable');
    await page.send('CSS.enable');

    await page.send('Emulation.setDeviceMetricsOverride', {
      width: 1440,
      height: 900,
      deviceScaleFactor: 1,
      mobile: false
    });

    await page.send('Page.navigate', { url: 'http://127.0.0.1:8000/' });
    await new Promise(r => setTimeout(r, 600));

    // Scroll to 1200px
    await page.send('Runtime.evaluate', { expression: `window.scrollTo(0, 1200);` });
    await new Promise(r => setTimeout(r, 300));

    // Open dropdown
    await page.send('Runtime.evaluate', {
      expression: `document.getElementById('servicesDropdownToggle').click();`
    });
    await new Promise(r => setTimeout(r, 300));

    const ddCheck = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const menu = document.getElementById('servicesDropdownMenu');
        const header = document.querySelector('.site-header');
        const mRect = menu.getBoundingClientRect();
        const hRect = header.getBoundingClientRect();
        return {
          isOpen: menu.offsetParent !== null || window.getComputedStyle(menu).display !== 'none',
          menuTop: mRect.top,
          headerBottom: hRect.bottom,
          isBelowHeader: mRect.top >= hRect.bottom,
          menuBottom: mRect.bottom,
          viewportHeight: window.innerHeight,
          fitsVertically: mRect.bottom <= window.innerHeight,
          menuLeft: mRect.left,
          menuRight: mRect.right,
          viewportWidth: window.innerWidth,
          fitsHorizontally: mRect.left >= 0 && mRect.right <= window.innerWidth
        };
      })()`,
      returnByValue: true
    });

    const ddc = ddCheck.result.value;
    assert(ddc.isOpen, 'Services dropdown opens successfully on click while page is scrolled');
    assert(ddc.isBelowHeader, 'Services dropdown is positioned cleanly below the fixed header');
    assert(ddc.fitsVertically, 'Services dropdown is not clipped by viewport bottom');
    assert(ddc.fitsHorizontally, 'Services dropdown is not clipped by viewport horizontal edges');

    await captureScreenshot(page, 'fixed_navbar_dropdown_scrolled.png');

    await page.send('Target.closeTarget', { targetId });
    page.close();
  }

  // -------------------------------------------------------------
  // TEST SECTION 4: SINGLE SCROLLBAR VERIFICATION
  // -------------------------------------------------------------
  console.log('\n--- 4. SINGLE SCROLLBAR VERIFICATION ---');
  {
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
    const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
    await page.connect();

    await page.send('Page.enable');
    await page.send('DOM.enable');

    await page.send('Page.navigate', { url: 'http://127.0.0.1:8000/' });
    await new Promise(r => setTimeout(r, 600));

    const scrollbarCheck = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const bodyStyle = window.getComputedStyle(document.body);
        const htmlStyle = window.getComputedStyle(document.documentElement);
        const bodyScrollable = document.body.scrollHeight > window.innerHeight && (bodyStyle.overflowY === 'scroll' || bodyStyle.overflowY === 'auto');
        const htmlScrollable = document.documentElement.scrollHeight > window.innerHeight && (htmlStyle.overflowY === 'scroll' || htmlStyle.overflowY === 'auto' || htmlStyle.overflowY === 'visible');
        return {
          bodyOverflowY: bodyStyle.overflowY,
          htmlOverflowY: htmlStyle.overflowY,
          bodyHeight: document.body.style.height,
          hasDuplicateScrollbar: bodyScrollable && htmlScrollable && document.body.style.height === '100vh'
        };
      })()`,
      returnByValue: true
    });

    const sc = scrollbarCheck.result.value;
    assert(!sc.hasDuplicateScrollbar, 'No second scrollbar detected on page (body does not trap scrolling with height: 100vh)');

    await page.send('Target.closeTarget', { targetId });
    page.close();
  }

  browser.close();

  console.log('\n================================================================');
  console.log(`TOTAL TESTS: ${totalTests} | PASSED: ${passedTests} | FAILED: ${totalTests - passedTests}`);
  console.log(`SUCCESS RATE: ${Math.round((passedTests / totalTests) * 100)}%`);
  console.log('================================================================');

  if (passedTests === totalTests) {
    process.exit(0);
  } else {
    process.exit(1);
  }
}

runTestSuite().catch(err => {
  console.error('Fatal error in test suite:', err);
  process.exit(1);
});

