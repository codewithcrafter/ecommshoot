/**
 * ECOMSHOOT - Comprehensive Mobile Responsiveness Refinement & Navbar Verification Test Suite
 * Tests viewports: 320px, 360px, 375px, 390px, 414px, 430px, 540px, 768px, 820px, 1024px, 1280px, 1366px, 1440px, 1920px.
 * Tests all 15 public pages, portfolio cards, services, drawer modal, and sticky header.
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

async function runRefinementAudit() {
  console.log('================================================================');
  console.log('ECOMSHOOT - FINAL MOBILE RESPONSIVENESS REFINEMENT AUDIT');
  console.log('================================================================\n');

  const versionInfo = await getJson('http://127.0.0.1:9222/json/version');
  const browser = new CDPClient(versionInfo.webSocketDebuggerUrl);
  await browser.connect();

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
  // TEST SECTION 1: 14 TARGET VIEWPORT SIZES (HORIZONTAL OVERFLOW TEST)
  // -------------------------------------------------------------
  console.log('--- 1. AUDITING 14 TARGET VIEWPORTS (0px HORIZONTAL OVERFLOW CHECK) ---');
  const targetViewports = [
    { name: 'Mobile Small (iPhone SE)', width: 320, height: 568, mobile: true },
    { name: 'Mobile Android Standard', width: 360, height: 740, mobile: true },
    { name: 'Mobile iPhone Standard', width: 375, height: 812, mobile: true },
    { name: 'Mobile iPhone 12/13/14', width: 390, height: 844, mobile: true },
    { name: 'Mobile iPhone Plus/Max', width: 414, height: 896, mobile: true },
    { name: 'Mobile iPhone Pro Max', width: 430, height: 932, mobile: true },
    { name: 'Mobile Large / Foldable', width: 540, height: 720, mobile: true },
    { name: 'Tablet Portrait (iPad)', width: 768, height: 1024, mobile: true },
    { name: 'Tablet Medium (iPad Air)', width: 820, height: 1180, mobile: false },
    { name: 'Tablet Landscape / Laptop', width: 1024, height: 768, mobile: false },
    { name: 'Desktop HD Standard', width: 1280, height: 800, mobile: false },
    { name: 'Laptop Standard', width: 1366, height: 768, mobile: false },
    { name: 'Desktop Widescreen', width: 1440, height: 900, mobile: false },
    { name: 'Full HD Ultra-Wide', width: 1920, height: 1080, mobile: false }
  ];

  for (const vp of targetViewports) {
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
    const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
    await page.connect();

    await page.send('Page.enable');
    await page.send('DOM.enable');

    await page.send('Emulation.setDeviceMetricsOverride', {
      width: vp.width,
      height: vp.height,
      deviceScaleFactor: vp.mobile ? 2 : 1,
      mobile: vp.mobile
    });

    await page.send('Page.navigate', { url: 'http://127.0.0.1:8000/' });
    await new Promise(r => setTimeout(r, 400));

    const overflowCheck = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const docWidth = document.documentElement.clientWidth;
        const scrollWidth = document.documentElement.scrollWidth;
        const bodyScrollWidth = document.body.scrollWidth;
        const maxScroll = Math.max(scrollWidth, bodyScrollWidth);
        const overflow = Math.max(0, maxScroll - docWidth);
        return {
          clientWidth: docWidth,
          scrollWidth: maxScroll,
          overflow: overflow
        };
      })()`,
      returnByValue: true
    });

    const res = overflowCheck.result.value;
    assert(res.overflow === 0, `${vp.width}px - ${vp.name}: width=${res.clientWidth}px, scrollWidth=${res.scrollWidth}px (0px overflow)`);

    // Capture screenshots for required verification viewports
    if ([320, 375, 390, 430, 768].includes(vp.width)) {
      await captureScreenshot(page, `final_viewport_${vp.width}.png`);
    }

    await page.send('Target.closeTarget', { targetId });
    page.close();
  }

  // -------------------------------------------------------------
  // TEST SECTION 2: PORTFOLIO & PROJECT CARDS ON MOBILE
  // -------------------------------------------------------------
  console.log('\n--- 2. PORTFOLIO & PROJECT CARDS AUDIT (375px & 320px) ---');
  for (const w of [375, 320]) {
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
    const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
    await page.connect();

    await page.send('Page.enable');
    await page.send('DOM.enable');

    await page.send('Emulation.setDeviceMetricsOverride', {
      width: w,
      height: 812,
      deviceScaleFactor: 2,
      mobile: true
    });

    await page.send('Page.navigate', { url: 'http://127.0.0.1:8000/' });
    await new Promise(r => setTimeout(r, 600));

    const cardMetrics = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const port = document.getElementById('portfolio');
        if (port) port.scrollIntoView();
        const items = Array.from(document.querySelectorAll('.portfolio-grid-item'));
        const container = port.querySelector('.container');
        const cRect = container.getBoundingClientRect();
        
        return items.map((item, i) => {
          const rect = item.getBoundingClientRect();
          const img = item.querySelector('.portfolio-inner-frame img');
          const iRect = img ? img.getBoundingClientRect() : null;
          const overlay = item.querySelector('.portfolio-hover-overlay');
          const title = item.querySelector('.overlay-title');
          const link = item.querySelector('.overlay-link');
          const tag = item.querySelector('.overlay-tag');
          return {
            index: i,
            cardWidth: Math.round(rect.width),
            cardHeight: Math.round(rect.height),
            containerWidth: Math.round(cRect.width),
            fitsContainer: rect.width <= cRect.width + 1 && rect.left >= cRect.left - 2,
            hasImage: iRect && iRect.width > 0 && iRect.height > 0,
            hasTag: !!tag && tag.textContent.trim().length > 0,
            hasTitle: !!title && title.textContent.trim().length > 0,
            hasLink: !!link && link.textContent.trim().length > 0
          };
        });
      })()`,
      returnByValue: true
    });

    const cards = cardMetrics.result.value;
    assert(cards.length === 7, `${w}px: Exactly 7 portfolio cards rendered`);
    const allFit = cards.every(c => c.fitsContainer);
    assert(allFit, `${w}px: All portfolio cards fit viewport container without horizontal edge spill`);
    const allImagesValid = cards.every(c => c.hasImage && c.hasTag && c.hasTitle && c.hasLink);
    assert(allImagesValid, `${w}px: All cards contain valid imagery, category tag, title, and CTA link`);

    await captureScreenshot(page, `final_portfolio_${w}.png`);

    await page.send('Target.closeTarget', { targetId });
    page.close();
  }

  // -------------------------------------------------------------
  // TEST SECTION 3: MOBILE NAVBAR & DRAWER FULL BEHAVIOR
  // -------------------------------------------------------------
  console.log('\n--- 3. MOBILE NAVBAR & DRAWER COMPLETE BEHAVIOR (375px) ---');
  {
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
    await new Promise(r => setTimeout(r, 600));

    // 1. Initial navbar state
    const navState = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const header = document.querySelector('.site-header');
        const logo = document.querySelector('.brand-logo');
        const toggle = document.getElementById('mobileMenuToggle');
        const hRect = header.getBoundingClientRect();
        const lRect = logo.getBoundingClientRect();
        const tRect = toggle.getBoundingClientRect();
        return {
          headerHeight: Math.round(hRect.height),
          headerTop: Math.round(hRect.top),
          logoLeft: Math.round(lRect.left),
          toggleRight: Math.round(window.innerWidth - tRect.right),
          isLeftAligned: lRect.left < 40,
          isRightAligned: (window.innerWidth - tRect.right) < 40
        };
      })()`,
      returnByValue: true
    });
    const ns = navState.result.value;
    assert(ns.headerHeight === 60, `Mobile header is compact 60px height (${ns.headerHeight}px)`);
    assert(ns.isLeftAligned, `Brand logo is cleanly left-aligned (${ns.logoLeft}px from edge)`);
    assert(ns.isRightAligned, `Hamburger toggle is cleanly right-aligned (${ns.toggleRight}px from edge)`);

    // 2. Open drawer via hamburger
    await page.send('Runtime.evaluate', {
      expression: `document.getElementById('mobileMenuToggle').click();`
    });
    await new Promise(r => setTimeout(r, 300));

    const openCheck = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const nav = document.getElementById('mainNav');
        const toggle = document.getElementById('mobileMenuToggle');
        const hasLock = document.body.classList.contains('nav-lock-scroll');
        const closeBtn = document.getElementById('mobileDrawerClose');
        const servicesCount = document.querySelectorAll('#servicesDropdownMenu .dropdown-item-compact').length;
        const ctaBtn = document.querySelector('.mobile-menu-cta-wrap a');
        return {
          isActive: nav.classList.contains('is-active'),
          expanded: toggle.getAttribute('aria-expanded') === 'true',
          hasLockScroll: hasLock,
          hasCloseButton: !!closeBtn,
          servicesCount: servicesCount,
          hasCTA: !!ctaBtn
        };
      })()`,
      returnByValue: true
    });
    const oc = openCheck.result.value;
    assert(oc.isActive, 'Mobile drawer opens on hamburger click (.is-active)');
    assert(oc.hasLockScroll, 'Body background scrolling is locked (body.nav-lock-scroll)');
    assert(oc.hasCloseButton, 'Drawer contains dedicated close button (#mobileDrawerClose)');
    assert(oc.servicesCount === 9, 'All 9 commercial service links are present in mobile drawer');
    assert(oc.hasCTA, 'Book a Studio Shoot CTA is present in mobile drawer');

    await captureScreenshot(page, 'final_mobile_drawer_open.png');

    // 3. Test Services Accordion expansion
    await page.send('Runtime.evaluate', {
      expression: `document.getElementById('servicesDropdownToggle').click();`
    });
    await new Promise(r => setTimeout(r, 300));

    const accordionCheck = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const parent = document.getElementById('servicesDropdownParent');
        const menu = document.getElementById('servicesDropdownMenu');
        const style = window.getComputedStyle(menu);
        return {
          isOpen: parent.classList.contains('dropdown-open'),
          isVisible: style.display !== 'none'
        };
      })()`,
      returnByValue: true
    });
    const ac = accordionCheck.result.value;
    assert(ac.isOpen && ac.isVisible, 'Services accordion expands smoothly inside mobile drawer');

    await captureScreenshot(page, 'final_mobile_services_expanded.png');

    // 4. Test closing via dedicated close button
    await page.send('Runtime.evaluate', {
      expression: `document.getElementById('mobileDrawerClose').click();`
    });
    await new Promise(r => setTimeout(r, 300));

    const closeCheck = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const nav = document.getElementById('mainNav');
        const hasLock = document.body.classList.contains('nav-lock-scroll');
        return {
          isClosed: !nav.classList.contains('is-active'),
          lockRemoved: !hasLock
        };
      })()`,
      returnByValue: true
    });
    const cc = closeCheck.result.value;
    assert(cc.isClosed, 'Mobile drawer closes on dedicated close button click');
    assert(cc.lockRemoved, 'Background scrolling is restored after closing drawer');

    // 5. Test closing via Escape key
    await page.send('Runtime.evaluate', {
      expression: `document.getElementById('mobileMenuToggle').click();`
    });
    await new Promise(r => setTimeout(r, 200));

    await page.send('Runtime.evaluate', {
      expression: `document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));`
    });
    await new Promise(r => setTimeout(r, 200));

    const escCheck = await page.send('Runtime.evaluate', {
      expression: `!document.getElementById('mainNav').classList.contains('is-active')`,
      returnByValue: true
    });
    assert(escCheck.result.value, 'Mobile drawer closes on Escape key press');

    await page.send('Target.closeTarget', { targetId });
    page.close();
  }

  // -------------------------------------------------------------
  // TEST SECTION 4: FLOATING WHATSAPP & CALL BUTTONS
  // -------------------------------------------------------------
  console.log('\n--- 4. FLOATING CONTACT BUTTONS (MOBILE SPECIFICATION) ---');
  {
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
    await new Promise(r => setTimeout(r, 600));

    const floatCheck = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const dock = document.querySelector('.floating-contact-dock');
        const btns = Array.from(document.querySelectorAll('.floating-contact-btn'));
        const waBtn = document.querySelector('.floating-btn-whatsapp');
        const callBtn = document.querySelector('.floating-btn-call');
        const dRect = dock.getBoundingClientRect();
        
        return {
          dockPresent: !!dock,
          buttonCount: btns.length,
          btnWidth: btns[0] ? Math.round(btns[0].getBoundingClientRect().width) : 0,
          dockRight: Math.round(window.innerWidth - dRect.right),
          dockBottom: Math.round(window.innerHeight - dRect.bottom),
          hasWaHref: waBtn ? waBtn.href.includes('wa.me') : false,
          hasCallHref: callBtn ? callBtn.href.includes('tel:') : false
        };
      })()`,
      returnByValue: true
    });
    const fc = floatCheck.result.value;
    assert(fc.dockPresent && fc.buttonCount === 2, 'Floating contact dock renders both WhatsApp and Call buttons');
    assert(fc.btnWidth <= 44, `Mobile button size reduced appropriately (${fc.btnWidth}px <= 44px)`);
    assert(fc.dockRight >= 12 && fc.dockBottom >= 12, `Proper margin from screen edges (right: ${fc.dockRight}px, bottom: ${fc.dockBottom}px)`);
    assert(fc.hasWaHref, 'WhatsApp button uses official wa.me protocol');
    assert(fc.hasCallHref, 'Call button uses official tel: protocol');

    await page.send('Target.closeTarget', { targetId });
    page.close();
  }

  // -------------------------------------------------------------
  // TEST SECTION 5: AUDITING ALL 15 PUBLIC PAGES AT 375px & 320px
  // -------------------------------------------------------------
  console.log('\n--- 5. AUDITING ALL 15 PUBLIC PAGES AT 375px & 320px ---');
  const allPublicPages = [
    { name: 'Home', url: 'http://127.0.0.1:8000/' },
    { name: 'About Us', url: 'http://127.0.0.1:8000/about.php' },
    { name: 'Services Directory', url: 'http://127.0.0.1:8000/services.php' },
    { name: 'Service: Product Photography', url: 'http://127.0.0.1:8000/product-photography.php' },
    { name: 'Service: E-Commerce', url: 'http://127.0.0.1:8000/ecommerce-product-photography.php' },
    { name: 'Service: Lifestyle', url: 'http://127.0.0.1:8000/lifestyle-product-photography.php' },
    { name: 'Service: Fashion', url: 'http://127.0.0.1:8000/fashion-product-photography.php' },
    { name: 'Service: Creative', url: 'http://127.0.0.1:8000/creative-product-photography.php' },
    { name: 'Service: Jewellery', url: 'http://127.0.0.1:8000/jewellery-product-photography.php' },
    { name: 'Service: Retouching', url: 'http://127.0.0.1:8000/product-photo-editing-retouching.php' },
    { name: 'Service: White BG', url: 'http://127.0.0.1:8000/white-background-product-photography.php' },
    { name: 'Service: Commercial', url: 'http://127.0.0.1:8000/professional-product-photography.php' },
    { name: 'Blog Listing', url: 'http://127.0.0.1:8000/blog.php' },
    { name: 'Blog Detail #1', url: 'http://127.0.0.1:8000/blog/1' },
    { name: 'Contact Us', url: 'http://127.0.0.1:8000/contact.php' }
  ];

  for (const pageItem of allPublicPages) {
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank' });
    const page = new CDPClient(`ws://127.0.0.1:9222/devtools/page/${targetId}`);
    await page.connect();

    await page.send('Page.enable');
    await page.send('DOM.enable');

    // Test at 375px
    await page.send('Emulation.setDeviceMetricsOverride', {
      width: 375,
      height: 812,
      deviceScaleFactor: 2,
      mobile: true
    });
    await page.send('Page.navigate', { url: pageItem.url });
    await new Promise(r => setTimeout(r, 400));

    const check375 = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const docWidth = document.documentElement.clientWidth;
        const maxScroll = Math.max(document.documentElement.scrollWidth, document.body.scrollWidth);
        const header = document.querySelector('.site-header');
        return {
          overflow: Math.max(0, maxScroll - docWidth),
          isHeaderFixed: header ? window.getComputedStyle(header).position === 'fixed' : false
        };
      })()`,
      returnByValue: true
    });

    // Test at 320px
    await page.send('Emulation.setDeviceMetricsOverride', {
      width: 320,
      height: 568,
      deviceScaleFactor: 2,
      mobile: true
    });
    await new Promise(r => setTimeout(r, 200));

    const check320 = await page.send('Runtime.evaluate', {
      expression: `(() => {
        const docWidth = document.documentElement.clientWidth;
        const maxScroll = Math.max(document.documentElement.scrollWidth, document.body.scrollWidth);
        return Math.max(0, maxScroll - docWidth);
      })()`,
      returnByValue: true
    });

    const ov375 = check375.result.value.overflow;
    const isFixed = check375.result.value.isHeaderFixed;
    const ov320 = check320.result.value;

    assert(ov375 === 0 && ov320 === 0 && isFixed, `${pageItem.name}: 375px (0px overflow) • 320px (0px overflow) • Header Fixed=true`);

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

runRefinementAudit().catch(err => {
  console.error('Fatal error in audit suite:', err);
  process.exit(1);
});

