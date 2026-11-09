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

  if (mobileToggle && mainNav) {
    const updateNavTop = () => {
      if (header && window.innerWidth <= 1024) {
        const headerBottom = Math.round(header.getBoundingClientRect().bottom);
        mainNav.style.top = `${Math.max(0, headerBottom)}px`;
        mainNav.style.height = `calc(100vh - ${Math.max(0, headerBottom)}px)`;
        mainNav.style.height = `calc(100dvh - ${Math.max(0, headerBottom)}px)`;
      }
    };

    const openMenu = () => {
      updateNavTop();
      mainNav.classList.add('is-active');
      mobileToggle.setAttribute('aria-expanded', 'true');
      document.body.classList.add('nav-lock-scroll');
    };

    const closeMenu = () => {
      mainNav.classList.remove('is-active');
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
    });
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

      const rect = dropdownToggle.getBoundingClientRect();
      const panelWidth = Math.min(540, window.innerWidth - 32);
      
      // Center horizontally under the trigger
      let left = rect.left + (rect.width / 2) - (panelWidth / 2);

      // Clamp within viewport margins
      if (left < 16) {
        left = 16;
      } else if (left + panelWidth > window.innerWidth - 16) {
        left = window.innerWidth - panelWidth - 16;
      }

      const top = rect.bottom + 8;
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
        hoverTimeout = setTimeout(closeDropdown, 250);
      }
    });

    dropdownMenu.addEventListener('mouseenter', () => {
      if (window.innerWidth > 1024) {
        clearTimeout(hoverTimeout);
      }
    });

    dropdownMenu.addEventListener('mouseleave', () => {
      if (window.innerWidth > 1024) {
        hoverTimeout = setTimeout(closeDropdown, 250);
      }
    });

    // Click toggle (Desktop & Mobile)
    dropdownToggle.addEventListener('click', (e) => {
      e.preventDefault();
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

  // 5. Portfolio Filtering System (Supports .portfolio-pill-btn and .portfolio-filter-btn)
  const filterBtns = document.querySelectorAll('.portfolio-pill-btn, .portfolio-filter-btn');
  const portfolioCards = document.querySelectorAll('.portfolio-grid-item, .portfolio-card');

  if (filterBtns.length && portfolioCards.length) {
    filterBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const filter = btn.getAttribute('data-filter') || 'all';

        portfolioCards.forEach((card) => {
          const category = card.getAttribute('data-category') || '';
          if (filter === 'all' || category.includes(filter)) {
            card.style.display = 'block';
            setTimeout(() => {
              card.style.opacity = '1';
              card.style.transform = 'translateY(0) scale(1)';
            }, 10);
          } else {
            card.style.opacity = '0';
            card.style.transform = 'translateY(8px) scale(0.97)';
            setTimeout(() => {
              card.style.display = 'none';
            }, 200);
          }
        });
      });
    });
  }

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
    
    // Auto-fill tracking context
    const sourceUrlInput = document.getElementById('popupSourceUrl');
    const sourceTitleInput = document.getElementById('popupSourceTitle');
    if (sourceUrlInput) sourceUrlInput.value = window.location.href;
    if (sourceTitleInput) sourceTitleInput.value = document.title || 'Ecomshoot Photography';

    const checkCooldown = () => {
      try {
        const cooldownUntil = localStorage.getItem('ecomshoot_lead_cooldown');
        if (cooldownUntil && Date.now() < parseInt(cooldownUntil, 10)) {
          return true; // Still within cooldown window
        }
      } catch (err) {
        // LocalStorage unavailable
      }
      return false;
    };

    const setCooldown = () => {
      try {
        const cooldownTime = Date.now() + (config.cooldownMs || 24 * 3600 * 1000);
        localStorage.setItem('ecomshoot_lead_cooldown', cooldownTime.toString());
      } catch (err) {
        // LocalStorage unavailable
      }
    };

    let popupTimer = null;

    const openPopup = () => {
      if (checkCooldown() || popupOverlay.classList.contains('is-visible')) {
        return;
      }
      popupOverlay.style.display = 'flex';
      setTimeout(() => {
        popupOverlay.classList.add('is-visible');
        popupOverlay.setAttribute('aria-hidden', 'false');
      }, 20);
    };

    const closePopup = (markDismissed = true) => {
      popupOverlay.classList.remove('is-visible');
      popupOverlay.setAttribute('aria-hidden', 'true');
      setTimeout(() => {
        popupOverlay.style.display = 'none';
      }, 350);

      if (markDismissed) {
        setCooldown();
      }
    };

    // Close button trigger
    if (popupClose) {
      popupClose.addEventListener('click', () => closePopup(true));
    }

    // Backdrop click trigger
    popupOverlay.addEventListener('click', (e) => {
      if (e.target === popupOverlay) {
        closePopup(true);
      }
    });

    // Escape key trigger
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && popupOverlay.classList.contains('is-visible')) {
        closePopup(true);
      }
    });

    // Start timer if enabled and not cooled down
    if (config.enabled && !checkCooldown()) {
      popupTimer = setTimeout(openPopup, config.delayMs || 5000);
    }

    // AJAX Form Submission
    if (popupForm) {
      popupForm.addEventListener('submit', async (e) => {
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

            // Mark cooldown so visitor isn't shown again
            setCooldown();

            // Auto-close after 3.5 seconds
            setTimeout(() => {
              closePopup(false);
            }, 3500);
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
      '.reveal-item, .blog-card-wide, .blog-card-item, .service-card, .process-step-item, ' +
      '.faq-item, .stat-card, .portfolio-card, .testimonial-card, .pricing-card, ' +
      '.section-header, .feature-bullet-item, .article-sidebar-card'
    );

    if (!revealTargets.length) return;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
      revealTargets.forEach(el => {
        el.classList.add('is-revealed');
      });
      return;
    }

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

    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;

          // Compute stagger delay if part of a sibling grid
          const parent = el.parentElement;
          if (parent) {
            const siblings = Array.from(parent.children).filter(child => child.classList.contains('reveal-item'));
            if (siblings.length > 1) {
              const index = siblings.indexOf(el);
              if (index > 0 && index < 6) {
                el.style.transitionDelay = `${index * 60}ms`;
              }
            }
          }

          el.classList.add('is-revealed');
          observer.unobserve(el);
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

});
