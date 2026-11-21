/**
 * ECOMSHOOT - Client-Side Vanilla JavaScript Engine
 * Professional interaction, viewport-clamped fixed dropdown, mobile accordion, portfolio filter & FAQ accordions
 */

document.addEventListener('DOMContentLoaded', () => {

  // 1. Dynamic Header Metrics & Permanent Sticky Elevation Engine
  const topBar = document.querySelector('.top-dispatch-bar');
  const header = document.getElementById('siteHeader') || document.querySelector('.site-header');

  const syncHeaderMetrics = () => {
    const topBarHeight = (topBar && window.getComputedStyle(topBar).display !== 'none') ? topBar.offsetHeight : 0;
    const headerHeight = header ? header.offsetHeight : (window.innerWidth <= 768 ? 60 : 72);
    const totalHeight = topBarHeight + headerHeight;

    document.documentElement.style.setProperty('--top-bar-height', `${topBarHeight}px`);
    document.documentElement.style.setProperty('--header-height', `${headerHeight}px`);
    document.documentElement.style.setProperty('--total-header-height', `${totalHeight}px`);
  };

  syncHeaderMetrics();
  window.addEventListener('resize', syncHeaderMetrics, { passive: true });
  window.addEventListener('orientationchange', syncHeaderMetrics, { passive: true });

  if (header) {
    const handleScroll = () => {
      if (window.scrollY > 15) {
        header.classList.add('is-scrolled');
      } else {
        header.classList.remove('is-scrolled');
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // 2. Mobile Navigation Toggle Drawer & Full Modal System
  const mobileToggle = document.getElementById('mobileMenuToggle') || document.querySelector('.mobile-toggle');
  const mainNav = document.getElementById('mainNav') || document.querySelector('.main-nav');
  const mobileDrawerClose = document.getElementById('mobileDrawerClose');
  const mobileNavBackdrop = document.querySelector('.mobile-nav-backdrop');

  if (mobileToggle && mainNav) {
    const drawerFocusable = () => Array.from(mainNav.querySelectorAll(
      'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )).filter((element) => !element.closest('.services-dropdown') ||
      element.closest('.nav-item.has-dropdown')?.classList.contains('dropdown-open'));

    const openMenu = () => {
      mainNav.classList.add('is-active');
      mobileNavBackdrop?.classList.add('is-active');
      mobileToggle.setAttribute('aria-expanded', 'true');
      document.body.classList.add('nav-lock-scroll');
      window.setTimeout(() => (mobileDrawerClose || drawerFocusable()[0])?.focus(), 0);
    };

    const closeMenu = () => {
      mainNav.classList.remove('is-active');
      mobileNavBackdrop?.classList.remove('is-active');
      mobileToggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('nav-lock-scroll');
    };

    mobileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const isExpanded = mobileToggle.getAttribute('aria-expanded') === 'true';
      if (isExpanded) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    if (mobileDrawerClose) {
      mobileDrawerClose.addEventListener('click', (e) => {
        e.stopPropagation();
        closeMenu();
      });
    }

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (mainNav.classList.contains('is-active') && !mainNav.contains(e.target) && !mobileToggle.contains(e.target)) {
        closeMenu();
      }
    });

    // Close menu when clicking regular navigation links
    mainNav.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        if (!link.classList.contains('nav-dropdown-trigger') && window.innerWidth <= 1024) {
          closeMenu();
        }
      });
    });

    // Close menu on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mainNav.classList.contains('is-active')) {
        closeMenu();
        mobileToggle.focus();
      }

      if (e.key === 'Tab' && mainNav.classList.contains('is-active')) {
        const focusable = drawerFocusable();
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (e.shiftKey && document.activeElement === first) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 1024 && mainNav.classList.contains('is-active')) {
        closeMenu();
      }
    }, { passive: true });
  }

  // 3. Reliable Viewport-Clamped Services Dropdown (Desktop fixed positioning, Mobile accordion)
  const dropdownToggle = document.getElementById('servicesDropdownToggle');
  const dropdownParent = document.getElementById('servicesDropdownParent') || (dropdownToggle ? dropdownToggle.closest('.nav-item') : null);
  const dropdownMenu = document.getElementById('servicesDropdownMenu');

  if (dropdownToggle && dropdownParent && dropdownMenu) {
    let hoverTimeout = null;

    // Viewport-safe calculation: never extends beyond the screen, never clipped
    const positionDropdown = () => {
      if (window.innerWidth <= 1024) {
        dropdownMenu.style.position = '';
        dropdownMenu.style.top = '';
        dropdownMenu.style.left = '';
        dropdownMenu.style.width = '';
        dropdownMenu.style.maxHeight = '';
        dropdownMenu.style.overflowY = '';
        dropdownMenu.style.zIndex = '';
        return;
      }

      const triggerElement = dropdownParent.querySelector('.nav-link-dropdown-group') || dropdownToggle;
      const rect = triggerElement.getBoundingClientRect();
      const panelWidth = Math.min(540, window.innerWidth - 32);
      
      // Center horizontally under the trigger
      let left = rect.left + (rect.width / 2) - (panelWidth / 2);

      // Clamp within viewport margins
      if (left < 16) {
        left = 16;
      } else if (left + panelWidth > window.innerWidth - 16) {
        left = window.innerWidth - panelWidth - 16;
      }

      const top = rect.bottom;
      const availableHeight = window.innerHeight - top - 16;
      const maxHeight = Math.max(160, availableHeight);

      dropdownMenu.style.position = 'fixed';
      dropdownMenu.style.top = `${top}px`;
      dropdownMenu.style.left = `${left}px`;
      dropdownMenu.style.width = `${panelWidth}px`;
      dropdownMenu.style.maxHeight = `${maxHeight}px`;
      dropdownMenu.style.overflowY = 'auto';
      dropdownMenu.style.zIndex = '1100';
    };

    const openDropdown = () => {
      clearTimeout(hoverTimeout);
      positionDropdown();
      dropdownParent.classList.add('dropdown-open');
      dropdownToggle.setAttribute('aria-expanded', 'true');
    };

    const closeDropdown = () => {
      dropdownParent.classList.remove('dropdown-open');
      dropdownToggle.setAttribute('aria-expanded', 'false');
    };

    // Desktop hover with anti-flicker delay
    dropdownParent.addEventListener('mouseenter', () => {
      if (window.innerWidth > 1024) {
        openDropdown();
      }
    });

    dropdownParent.addEventListener('mouseleave', () => {
      if (window.innerWidth > 1024) {
        hoverTimeout = setTimeout(closeDropdown, 300);
      }
    });

    dropdownMenu.addEventListener('mouseenter', () => {
      if (window.innerWidth > 1024) {
        clearTimeout(hoverTimeout);
      }
    });

    dropdownMenu.addEventListener('mouseleave', () => {
      if (window.innerWidth > 1024) {
        hoverTimeout = setTimeout(closeDropdown, 300);
      }
    });

    // Click toggle (Desktop & Mobile)
    dropdownToggle.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const isOpen = dropdownParent.classList.contains('dropdown-open');
      if (isOpen) {
        closeDropdown();
      } else {
        openDropdown();
      }
    });

    // Reposition on window resize and scroll
    window.addEventListener('resize', () => {
      if (dropdownParent.classList.contains('dropdown-open')) {
        positionDropdown();
      }
    }, { passive: true });

    window.addEventListener('scroll', () => {
      if (dropdownParent.classList.contains('dropdown-open') && window.innerWidth > 1024) {
        positionDropdown();
      }
    }, { passive: true });

    // Keyboard navigation: Escape key closes dropdown
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (dropdownParent.classList.contains('dropdown-open')) {
          closeDropdown();
          dropdownToggle.focus();
        }
        if (mainNav && mainNav.classList.contains('is-active')) {
          mainNav.classList.remove('is-active');
          mobileNavBackdrop?.classList.remove('is-active');
          if (mobileToggle) mobileToggle.setAttribute('aria-expanded', 'false');
          document.body.classList.remove('nav-lock-scroll');
        }
      }
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
      if (!dropdownParent.contains(e.target) && !dropdownMenu.contains(e.target)) {
        closeDropdown();
      }
    });

    // Keyboard arrow navigation inside dropdown
    const dropdownLinks = dropdownMenu.querySelectorAll('.dropdown-item-compact, .dropdown-view-all, .dropdown-footer-cta');
    dropdownMenu.addEventListener('keydown', (e) => {
      const focusable = Array.from(dropdownLinks);
      const index = focusable.indexOf(document.activeElement);

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        const next = (index + 1) % focusable.length;
        focusable[next].focus();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const prev = (index - 1 + focusable.length) % focusable.length;
        focusable[prev].focus();
      }
    });
  }

  // 4. FAQ Accordion Component (Supports .faq-question-btn and .faq-question)
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach((item) => {
    const questionBtn = item.querySelector('.faq-question-btn, .faq-question');
    if (questionBtn) {
      questionBtn.addEventListener('click', () => {
        const isOpen = item.classList.contains('active');
        
        // Close other items in the accordion
        faqItems.forEach((other) => {
          if (other !== item) {
            other.classList.remove('active');
            const otherBtn = other.querySelector('.faq-question-btn, .faq-question');
            if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
          }
        });

        if (isOpen) {
          item.classList.remove('active');
          questionBtn.setAttribute('aria-expanded', 'false');
        } else {
          item.classList.add('active');
          questionBtn.setAttribute('aria-expanded', 'true');
        }
      });
    }
  });

  // 6. Contact Form Frontend Validation
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      const email = contactForm.querySelector('input[name="email"]');
      const phone = contactForm.querySelector('input[name="phone"]');
      const name = contactForm.querySelector('input[name="full_name"]');

      if (!name || name.value.trim().length < 2) {
        alert('Please enter your full name (minimum 2 characters).');
        name.focus();
        e.preventDefault();
        return;
      }

      if (!email || !email.value.includes('@') || !email.value.includes('.')) {
        alert('Please provide a valid work email address.');
        email.focus();
        e.preventDefault();
        return;
      }

      if (!phone || phone.value.trim().length < 7) {
        alert('Please provide a valid contact telephone number.');
        phone.focus();
        e.preventDefault();
        return;
      }
    });
  }

  // 7. Automatic Lead Generation Timed Popup Controller
  const popupOverlay = document.getElementById('leadPopupOverlay');
  const popupClose = document.getElementById('leadPopupClose');
  const popupForm = document.getElementById('leadPopupForm');
  const popupSuccess = document.getElementById('leadPopupSuccess');
  const popupError = document.getElementById('leadPopupError');
  const popupSubmitBtn = document.getElementById('leadSubmitBtn');

  if (popupOverlay && window.__ECOMSHOOT_POPUP_CONFIG__) {
    const config = window.__ECOMSHOOT_POPUP_CONFIG__;

    // Clear legacy localStorage cooldown so it never blocks popups across the site
    try {
      localStorage.removeItem('ecomshoot_lead_cooldown');
    } catch (err) {}
    
    // Auto-fill tracking context
    const sourceUrlInput = document.getElementById('popupSourceUrl');
    const sourceTitleInput = document.getElementById('popupSourceTitle');
    if (sourceUrlInput) sourceUrlInput.value = window.location.href;
    if (sourceTitleInput) sourceTitleInput.value = document.title || 'Ecomshoot Photography';

    // Lightweight visual polish for the real popup only; submission flow remains unchanged.
    const popupDialog = document.getElementById('leadPopupDialog');
    const popupCamera = popupOverlay.querySelector('.lead-popup-camera-icon');
    if (popupDialog && window.matchMedia('(pointer: fine)').matches && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      popupDialog.addEventListener('mousemove', (event) => {
        const rect = popupDialog.getBoundingClientRect();
        popupDialog.style.setProperty('--spot-x', `${event.clientX - rect.left}px`);
        popupDialog.style.setProperty('--spot-y', `${event.clientY - rect.top}px`);
      });
    }
    if (popupCamera) {
      popupCamera.addEventListener('click', () => {
        popupCamera.classList.remove('is-flashing');
        void popupCamera.offsetWidth;
        popupCamera.classList.add('is-flashing');
      });
    }

    // Page-level dismissal tracker (persists only for current page lifecycle until refreshed or navigated)
    let pagePopupDismissed = false;
    let popupTimer = null;

    const openPopup = () => {
      if (pagePopupDismissed || popupOverlay.classList.contains('is-visible')) {
        return;
      }
      popupOverlay.style.display = 'flex';
      document.body.classList.add('lead-popup-lock-scroll');
      setTimeout(() => {
        popupOverlay.classList.add('is-visible');
        popupOverlay.setAttribute('aria-hidden', 'false');
      }, 20);
    };

    const closePopup = () => {
      pagePopupDismissed = true;
      if (popupTimer) {
        clearTimeout(popupTimer);
        popupTimer = null;
      }
      popupOverlay.classList.remove('is-visible');
      popupOverlay.setAttribute('aria-hidden', 'true');
      setTimeout(() => {
        popupOverlay.style.display = 'none';
        document.body.classList.remove('lead-popup-lock-scroll');
      }, 350);
    };

    // Close button trigger
    if (popupClose) {
      popupClose.addEventListener('click', closePopup);
    }

    // Backdrop click trigger
    popupOverlay.addEventListener('click', (e) => {
      if (e.target === popupOverlay) {
        closePopup();
      }
    });

    // Escape key trigger
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && popupOverlay.classList.contains('is-visible')) {
        closePopup();
      }
    });

    // Trigger exactly after 5 seconds on each page visit (single-shot, once per page load)
    if (config.enabled !== false) {
      popupTimer = setTimeout(openPopup, config.delayMs || 5000);
    }

    // AJAX Form Submission
    if (popupForm) {
      popupForm.addEventListener('submit', async (e) => {
        if (popupCamera) {
          popupCamera.classList.remove('is-flashing');
          void popupCamera.offsetWidth;
          popupCamera.classList.add('is-flashing');
        }
        e.preventDefault();

        // Client-side quick checks
        const nameInput = popupForm.querySelector('input[name="full_name"]');
        const emailInput = popupForm.querySelector('input[name="email"]');
        const mobileInput = popupForm.querySelector('input[name="mobile"]');
        const messageInput = popupForm.querySelector('textarea[name="message"]');

        if (!nameInput || nameInput.value.trim().length < 2) {
          alert('Please provide your full name.');
          if (nameInput) nameInput.focus();
          return;
        }

        if (!emailInput || !emailInput.value.includes('@') || !emailInput.value.includes('.')) {
          alert('Please enter a valid email address.');
          if (emailInput) emailInput.focus();
          return;
        }

        if (!mobileInput || mobileInput.value.trim().length < 7) {
          alert('Please enter a valid contact / mobile number.');
          if (mobileInput) mobileInput.focus();
          return;
        }

        if (!messageInput || messageInput.value.trim().length < 5) {
          alert('Please enter your photography requirements.');
          if (messageInput) messageInput.focus();
          return;
        }

        // Disable button to prevent duplicate clicks
        if (popupSubmitBtn) {
          popupSubmitBtn.disabled = true;
          popupSubmitBtn.querySelector('span').textContent = 'Submitting Inquiry...';
        }

        if (popupError) popupError.style.display = 'none';

        try {
          const formData = new FormData(popupForm);
          const response = await fetch(popupForm.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            body: formData
          });

          const data = await response.json();

          if (data.success) {
            // Show success banner
            if (popupForm) popupForm.style.display = 'none';
            if (popupSuccess) popupSuccess.style.display = 'block';

            // Auto-close after 3 seconds and mark dismissed for this page
            setTimeout(() => {
              closePopup();
            }, 3000);
          } else {
            if (popupError) {
              popupError.textContent = data.message || 'An error occurred. Please check your information and try again.';
              popupError.style.display = 'block';
            }
            if (popupSubmitBtn) {
              popupSubmitBtn.disabled = false;
              popupSubmitBtn.querySelector('span').textContent = 'Submit Inquiry';
            }
          }
        } catch (err) {
          if (popupError) {
            popupError.textContent = 'Connection error. Please try again.';
            popupError.style.display = 'block';
          }
          if (popupSubmitBtn) {
            popupSubmitBtn.disabled = false;
            popupSubmitBtn.querySelector('span').textContent = 'Submit Inquiry';
          }
        }
      });
    }
  }

  // ============================================================================
  // 8. MOTION DESIGN & SCROLL REVEAL ENGINE
  // High-performance IntersectionObserver with reduced-motion support
  // ============================================================================
  const initMotionEngine = () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Target elements across all pages
    const revealTargets = document.querySelectorAll(
      '.reveal-item, .industry-box, .blog-card-wide, .blog-card-item, .blog-card, .service-card, .service-editorial-card, ' +
      '.editorial-services-card, .editorial-blog-card, .benefit-card, .feature-box, .process-step-item, ' +
      '.strip-item, .editorial-pillar-card, .faq-item, .stat-card, .portfolio-card, .testimonial-card, ' +
      '.pricing-card, .section-header, .section-editorial-header, .section-title, .section-eyebrow, .services-style-card, ' +
      '.contact-info-card, .feature-bullet-item, .article-sidebar-card, .home-about-stats .proof-item, ' +
      '.why-floating-card, .about-tool-card, .portfolio-grid-item'
    );

    if (!revealTargets.length) return;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
      revealTargets.forEach(el => {
        el.classList.add('is-revealed');
      });
      return;
    }

    document.documentElement.classList.add('motion-ready');

    // Mark all target elements with reveal-item if not present
    revealTargets.forEach(el => {
      if (!el.classList.contains('reveal-item')) {
        el.classList.add('reveal-item');
      }
    });

    const observerOptions = {
      root: null,
      rootMargin: '0px 0px -40px 0px',
      threshold: 0.08
    };

    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;

          // Compute stagger delay if part of a sibling grid (75ms–90ms clean delay)
          const parent = el.parentElement;
          if (parent) {
            const siblings = Array.from(parent.children).filter(child => child.classList.contains('reveal-item'));
            if (siblings.length > 1) {
              const index = siblings.indexOf(el);
              if (index > 0 && index < 8) {
                el.style.transitionDelay = `${index * 80}ms`;
              }
            }
          }

          el.classList.add('is-revealed');
          revealObserver.unobserve(el);
        }
      });
    }, observerOptions);

    revealTargets.forEach(el => {
      revealObserver.observe(el);
    });
  };

  initMotionEngine();

  // ============================================================================
  // 9. TESTIMONIALS MOBILE CAROUSEL & TOUCH SWIPE ENGINE
  // ============================================================================
  const initTestimonialSlider = () => {
    const track = document.getElementById('testimonialTrack');
    if (!track) return;

    const slides = track.querySelectorAll('.testimonial-slide');
    const prevBtn = document.getElementById('testimonialPrev');
    const nextBtn = document.getElementById('testimonialNext');
    const dotsWrap = document.getElementById('testimonialDots');
    const dots = dotsWrap ? dotsWrap.querySelectorAll('.slider-dot') : [];

    if (!slides.length) return;

    const updateActiveDot = (index) => {
      dots.forEach((dot, i) => {
        if (i === index) {
          dot.classList.add('active');
          dot.setAttribute('aria-current', 'true');
        } else {
          dot.classList.remove('active');
          dot.removeAttribute('aria-current');
        }
      });
    };

    const getSlideWidth = () => {
      if (slides.length < 2) return track.clientWidth;
      const firstRect = slides[0].getBoundingClientRect();
      const secondRect = slides[1].getBoundingClientRect();
      const gap = secondRect.left - firstRect.right;
      return firstRect.width + gap;
    };

    const getCurrentSlideIndex = () => {
      const slideWidth = getSlideWidth();
      if (!slideWidth) return 0;
      return Math.round(track.scrollLeft / slideWidth);
    };

    const scrollToSlide = (index) => {
      const targetIndex = Math.max(0, Math.min(index, slides.length - 1));
      const slideWidth = getSlideWidth();
      track.scrollTo({
        left: targetIndex * slideWidth,
        behavior: 'smooth'
      });
      updateActiveDot(targetIndex);
    };

    if (prevBtn) {
      prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const current = getCurrentSlideIndex();
        scrollToSlide(current - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const current = getCurrentSlideIndex();
        scrollToSlide(current + 1);
      });
    }

    dots.forEach((dot, index) => {
      dot.addEventListener('click', (e) => {
        e.preventDefault();
        scrollToSlide(index);
      });
    });

    // Debounced scroll listener to synchronize dots during touch swipes
    let isScrollingTimer;
    track.addEventListener('scroll', () => {
      clearTimeout(isScrollingTimer);
      isScrollingTimer = setTimeout(() => {
        const current = getCurrentSlideIndex();
        updateActiveDot(current);
      }, 50);
    }, { passive: true });
  };

  initTestimonialSlider();

  // ============================================================================
  // 10. EDITORIAL TABLE OF CONTENTS & SCROLLSPY ENGINE
  // High-performance bidirectional scrollspy, smooth scrolling offset & mobile accordion
  // ============================================================================
  const initTableOfContents = () => {
    const tocLinks = document.querySelectorAll('.toc-nav-link');
    const mobileTocToggle = document.getElementById('mobileTocToggle');
    const mobileTocDrawer = document.getElementById('mobileTocDrawer');

    // Mobile collapsible TOC toggle
    if (mobileTocToggle && mobileTocDrawer) {
      mobileTocToggle.addEventListener('click', (e) => {
        e.preventDefault();
        const isExpanded = mobileTocToggle.getAttribute('aria-expanded') === 'true';
        mobileTocToggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
        mobileTocDrawer.hidden = isExpanded;
        mobileTocToggle.classList.toggle('is-open', !isExpanded);
      });
    }

    if (!tocLinks.length) return;

    // Get all heading IDs referenced by TOC
    const headings = [];
    const linkMap = new Map();

    tocLinks.forEach(link => {
      const targetId = link.getAttribute('data-target') || (link.getAttribute('href') || '').replace('#', '');
      if (!targetId) return;

      const headingEl = document.getElementById(targetId);
      if (headingEl) {
        if (!headings.includes(headingEl)) {
          headings.push(headingEl);
        }
        if (!linkMap.has(targetId)) {
          linkMap.set(targetId, []);
        }
        linkMap.get(targetId).push(link);
      }
    });

    if (!headings.length) return;

    // Sort headings in document order
    headings.sort((a, b) => {
      const pos = a.compareDocumentPosition(b);
      return (pos & Node.DOCUMENT_POSITION_FOLLOWING) ? -1 : 1;
    });

    let activeId = null;
    let isClickScrolling = false;
    let clickTimeout = null;

    const setActiveToc = (id) => {
      if (!id || id === activeId) return;
      activeId = id;

      tocLinks.forEach(l => {
        l.classList.remove('is-active');
        l.classList.remove('active');
      });

      const activeLinks = linkMap.get(id);
      if (activeLinks) {
        activeLinks.forEach(l => {
          l.classList.add('is-active');
          l.classList.add('active');

          // For desktop sticky sidebar: keep active link visible in scrollable container
          const container = l.closest('.desktop-toc-list');
          if (container && container.scrollHeight > container.clientHeight) {
            const lTop = l.offsetTop;
            const lHeight = l.offsetHeight;
            const cTop = container.scrollTop;
            const cHeight = container.clientHeight;

            if (lTop < cTop + 15) {
              container.scrollTo({ top: Math.max(0, lTop - 25), behavior: 'smooth' });
            } else if (lTop + lHeight > cTop + cHeight - 15) {
              container.scrollTo({ top: lTop - cHeight + lHeight + 25, behavior: 'smooth' });
            }
          }
        });
      }
    };

    // Smooth scroll navigation on click
    tocLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const targetId = link.getAttribute('data-target') || (link.getAttribute('href') || '').replace('#', '');
        const target = document.getElementById(targetId);
        if (!target) return;

        isClickScrolling = true;
        if (clickTimeout) clearTimeout(clickTimeout);

        const header = document.getElementById('siteHeader') || document.querySelector('.site-header');
        const headerHeight = header ? header.offsetHeight : 80;
        const targetTop = target.getBoundingClientRect().top + window.scrollY - (headerHeight + 24);

        window.scrollTo({
          top: Math.max(0, targetTop),
          behavior: 'smooth'
        });

        setActiveToc(targetId);

        if (history.pushState) {
          history.pushState(null, null, '#' + targetId);
        } else {
          location.hash = targetId;
        }

        // Close mobile drawer if open
        if (mobileTocDrawer && !mobileTocDrawer.hidden) {
          mobileTocDrawer.hidden = true;
          if (mobileTocToggle) {
            mobileTocToggle.setAttribute('aria-expanded', 'false');
            mobileTocToggle.classList.remove('is-open');
          }
        }

        clickTimeout = setTimeout(() => {
          isClickScrolling = false;
        }, 850);
      });
    });

    // High-performance bidirectional scrollspy calculation
    let ticking = false;

    const calculateActiveSection = () => {
      if (isClickScrolling) return;

      const header = document.getElementById('siteHeader') || document.querySelector('.site-header');
      const headerHeight = header ? header.offsetHeight : 80;
      const offsetThreshold = headerHeight + 35; // Reading zone offset below header

      const scrollPosition = window.scrollY || window.pageYOffset;
      const windowHeight = window.innerHeight;
      const docHeight = document.documentElement.scrollHeight;

      // Bottom of page edge case: activate the last heading
      if (windowHeight + scrollPosition >= docHeight - 80) {
        setActiveToc(headings[headings.length - 1].id);
        return;
      }

      // Check headings in document order
      let currentHeadingId = null;
      for (let i = 0; i < headings.length; i++) {
        const top = headings[i].getBoundingClientRect().top;
        if (top <= offsetThreshold) {
          currentHeadingId = headings[i].id;
        } else {
          break;
        }
      }

      // If user is before first heading (hero/lead area), activate first heading
      if (!currentHeadingId && headings.length > 0) {
        currentHeadingId = headings[0].id;
      }

      if (currentHeadingId) {
        setActiveToc(currentHeadingId);
      }
    };

    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          calculateActiveSection();
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });

    // Optional IntersectionObserver enhancement for intersection triggers
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(() => {
        if (!isClickScrolling) {
          calculateActiveSection();
        }
      }, {
        root: null,
        rootMargin: '-80px 0px -40% 0px',
        threshold: [0, 0.5, 1]
      });

      headings.forEach(h => observer.observe(h));
    }

    // Initial activation
    if (window.location.hash) {
      const hashId = window.location.hash.replace('#', '');
      if (document.getElementById(hashId)) {
        setTimeout(() => setActiveToc(hashId), 150);
      } else {
        calculateActiveSection();
      }
    } else {
      calculateActiveSection();
    }
  };

  initTableOfContents();

  // ============================================================================
  // 11. SUBTLE SECTION INTRODUCTION ENTRANCE ANIMATIONS
  // Lightweight, responsive intersection entrance for standalone section headers
  // ============================================================================
  const initSectionAnimations = () => {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    const headers = document.querySelectorAll('.section-editorial-header, .section-header');
    if (!headers.length) return;

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('section-intro-visible');
            entry.target.classList.remove('section-intro-hidden');
            obs.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.1,
        rootMargin: '0px 0px -40px 0px'
      });

      headers.forEach(header => {
        const rect = header.getBoundingClientRect();
        // If element is already in the viewport on initial load, show immediately
        if (rect.top < window.innerHeight) {
          header.classList.add('section-intro-visible');
        } else {
          header.classList.add('section-intro-hidden');
          observer.observe(header);
        }
      });
    } else {
      headers.forEach(h => h.classList.add('section-intro-visible'));
    }
  };

  initSectionAnimations();

  // Shared studio CTA polish; no content, link, or form behavior is changed.
  const studioCtas = document.querySelectorAll('.section-cta .cta-banner-editorial, .test-shoot-box, .cta-panel, .cta-card');
  const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  studioCtas.forEach((cta) => {
    if (!reducedMotion) {
      cta.classList.add('studio-cta-reveal');
      if (window.matchMedia('(pointer: fine)').matches) {
        cta.addEventListener('mousemove', (event) => {
          const rect = cta.getBoundingClientRect();
          cta.style.setProperty('--cta-x', `${event.clientX - rect.left}px`);
          cta.style.setProperty('--cta-y', `${event.clientY - rect.top}px`);
        });
      }
    }
  });
  if (!reducedMotion && 'IntersectionObserver' in window) {
    const ctaObserver = new IntersectionObserver((entries, observer) => entries.forEach((entry) => {
      if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
    }), { threshold: 0.15 });
    studioCtas.forEach((cta) => ctaObserver.observe(cta));
  } else {
    studioCtas.forEach((cta) => cta.classList.add('is-visible'));
  }

  // ============================================================================
  // 12. KPI NUMERIC COUNTERS COUNT-UP ANIMATION
  // ============================================================================
  const initKpiCounters = () => {
    const counterElements = document.querySelectorAll('.proof-number, .stat-big');
    if (!counterElements.length) return;

    const parseCounter = (text) => {
      const raw = text.trim();
      let prefix = '';
      let suffix = '';
      let decimals = 0;

      if (raw.startsWith('+')) {
        prefix = '+';
      }
      if (raw.endsWith('+')) {
        suffix = '+';
      } else if (raw.endsWith('%')) {
        suffix = '%';
      }

      let numStr = raw.replace(/^[+]/, '').replace(/[+%]/, '').trim();
      if (numStr.endsWith('K')) {
        suffix = 'K' + suffix;
        numStr = numStr.slice(0, -1);
      }

      if (numStr.includes('.')) {
        decimals = numStr.split('.')[1].length;
      }

      const target = parseFloat(numStr) || 0;
      return { prefix, suffix, target, decimals, original: raw };
    };

    const animateElement = (el) => {
      if (el.dataset.counted === 'true') return;
      el.dataset.counted = 'true';

      const info = parseCounter(el.textContent);
      if (info.target === 0 && !info.decimals) return;

      const duration = 1800; // ms
      const startTime = performance.now();

      const update = (now) => {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        const currentVal = ease * info.target;

        el.textContent = `${info.prefix}${currentVal.toFixed(info.decimals)}${info.suffix}`;

        if (progress < 1) {
          requestAnimationFrame(update);
        } else {
          el.textContent = info.original;
        }
      };

      requestAnimationFrame(update);
    };

    if ('IntersectionObserver' in window && !reducedMotion) {
      const counterObserver = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            animateElement(entry.target);
            obs.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1 });

      counterElements.forEach(el => counterObserver.observe(el));
    } else {
      counterElements.forEach(animateElement);
    }
  };

  initKpiCounters();

  // ============================================================================
  // 13. MOUSE-TRACKING RADIAL SPOTLIGHT ENGINE (FINE POINTERS)
  // ============================================================================
  const initCardSpotlights = () => {
    if (!window.matchMedia('(pointer: fine)').matches) return;
    const spotlightCards = document.querySelectorAll(
      '.service-editorial-card, .editorial-pillar-card, .about-tool-card, ' +
      '.industry-box, .proof-item, .benefit-card, .specs-card, .blog-card, ' +
      '.services-style-card, .test-shoot-box, .cta-banner-editorial, .faq-item, ' +
      '.about-stat-card, .card, .portfolio-grid-item'
    );
    spotlightCards.forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        card.style.setProperty('--mouse-x', `${x}px`);
        card.style.setProperty('--mouse-y', `${y}px`);
      }, { passive: true });
    });
  };

  initCardSpotlights();

  // 14. Blog sidebar lead form: submit to the existing endpoint and reveal a
  // confirmation only after the server has stored the lead successfully.
  const initBlogLeadForm = () => {
    const form = document.querySelector('[data-blog-lead-form]');
    const modal = document.getElementById('blogFormSuccessModal');
    if (!form || !modal) return;

    const submitButton = form.querySelector('[type="submit"]');
    const dialog = modal.querySelector('.blog-form-modal__dialog');
    const closeButtons = modal.querySelectorAll('[data-blog-form-close]');
    let lastFocused = null;
    let errorBox = null;

    const showError = (message) => {
      if (!errorBox) {
        errorBox = document.createElement('p');
        errorBox.className = 'blog-form-submit-error';
        errorBox.setAttribute('role', 'alert');
        form.prepend(errorBox);
      }
      errorBox.textContent = message;
    };

    const closeModal = () => {
      modal.hidden = true;
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('lead-popup-lock-scroll');
      lastFocused?.focus();
    };

    const openModal = () => {
      lastFocused = document.activeElement;
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('lead-popup-lock-scroll');
      window.setTimeout(() => dialog?.focus(), 0);
    };

    closeButtons.forEach((button) => button.addEventListener('click', closeModal));
    document.addEventListener('keydown', (event) => {
      if (modal.hidden) return;
      if (event.key === 'Escape') {
        event.preventDefault();
        closeModal();
      }
      if (event.key === 'Tab') {
        const focusable = Array.from(modal.querySelectorAll('button:not([disabled]), [href], [tabindex]:not([tabindex="-1"])'));
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
      }
    });

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (form.dataset.submitting === 'true') return;
      if (!form.reportValidity()) return;

      form.dataset.submitting = 'true';
      if (errorBox) errorBox.remove();
      errorBox = null;
      const originalLabel = submitButton?.innerHTML;
      if (submitButton) { submitButton.disabled = true; submitButton.textContent = 'Submitting…'; }

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin'
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) throw new Error(result.message || 'We could not submit your enquiry. Please try again.');
        form.reset();
        openModal();
      } catch (error) {
        showError(error.message || 'We could not submit your enquiry. Please try again.');
      } finally {
        form.dataset.submitting = 'false';
        if (submitButton) { submitButton.disabled = false; submitButton.innerHTML = originalLabel; }
      }
    });
  };

  initBlogLeadForm();

});
