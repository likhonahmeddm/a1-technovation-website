/* =================================================================
   A1 TECHNOVATION — Main JavaScript 2025
================================================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ── NAVBAR ─────────────────────────────────────────────────── */
  const navbar = document.querySelector('.navbar');
  const mobileToggle = document.querySelector('.mobile-toggle');
  const mobileMenu   = document.querySelector('.mobile-menu');
  const desktopServicesItem = document.querySelector('.navbar .nav-item');
  const desktopServicesLink = desktopServicesItem?.querySelector('.nav-link[href$="/pages/services"], .nav-link[href$="pages/services"], .nav-link[href$="services"]');
  const mobileServicesGroup = document.querySelector('.mobile-menu-group');
  const mobileServicesLink = mobileServicesGroup?.querySelector('.mobile-menu-link[href$="/pages/services"], .mobile-menu-link[href$="pages/services"], .mobile-menu-link[href$="services"]');
  const mobileSubmenu = mobileServicesGroup?.querySelector('.mobile-submenu');

  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 20);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  if (mobileToggle && mobileMenu) {
    let lastScrollY = 0;

    const open = () => {
      lastScrollY = window.scrollY;
      mobileMenu.classList.add('open');
      mobileToggle.classList.add('open');
      mobileToggle.setAttribute('aria-expanded', 'true');
      mobileToggle.setAttribute('aria-label', 'Close menu');
      mobileMenu.setAttribute('aria-hidden', 'false');
      document.body.classList.add('menu-open');
      document.body.style.top = `-${lastScrollY}px`;
      document.body.style.position = 'fixed';
      document.body.style.width = '100%';
      if (mobileServicesGroup?.classList.contains('current')) {
        mobileServicesGroup.classList.add('open');
      }
    };
    const close = ({ restoreFocus = false, restoreScroll = true } = {}) => {
      mobileMenu.classList.remove('open');
      mobileToggle.classList.remove('open');
      mobileToggle.setAttribute('aria-expanded', 'false');
      mobileToggle.setAttribute('aria-label', 'Open menu');
      mobileMenu.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('menu-open');
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.width = '';
      if (restoreScroll) window.scrollTo(0, lastScrollY);
      if (restoreFocus) mobileToggle.focus({ preventScroll: true });
    };

    if (mobileServicesLink && mobileServicesGroup && mobileSubmenu) {
      mobileServicesLink.setAttribute('aria-expanded', mobileServicesGroup.classList.contains('current') ? 'true' : 'false');
      mobileServicesLink.setAttribute('aria-controls', 'mobileServicesSubmenu');
      mobileSubmenu.id = mobileSubmenu.id || 'mobileServicesSubmenu';
      mobileServicesLink.addEventListener('click', (e) => {
        if (window.innerWidth <= 1024) {
          e.preventDefault();
          const willOpen = !mobileServicesGroup.classList.contains('open');
          mobileServicesGroup.classList.toggle('open', willOpen);
          mobileServicesLink.setAttribute('aria-expanded', String(willOpen));
        }
      });
    }

    mobileToggle.addEventListener('click', () => mobileMenu.classList.contains('open') ? close({ restoreFocus: true }) : open());
    mobileMenu.querySelectorAll('.mobile-menu-link').forEach(l => {
      if (l === mobileServicesLink) return;
      l.addEventListener('click', () => close());
    });
    mobileMenu.querySelectorAll('.mobile-submenu-link').forEach(l => l.addEventListener('click', () => close()));
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && mobileMenu.classList.contains('open')) close({ restoreFocus: true });
    });
    window.addEventListener('resize', () => {
      if (window.innerWidth > 1024 && mobileMenu.classList.contains('open')) close();
    });
    close({ restoreScroll: false });
  }

  /* Active nav link */
  const navLinks = document.querySelectorAll('.nav-link, .mobile-menu-link, .mobile-submenu-link');
  const page = location.pathname.replace(/\/$/, '').split('/').pop() || '';
  const isHome = page === '';
  const isServiceSection = page === 'services' || page.startsWith('services-') || page.startsWith('seo-services-');
  navLinks.forEach(l => {
    const href = (l.getAttribute('href') || '').replace(/\/$/, '').split('/').pop();
    if (href === page || (isHome && href === '')) {
      l.classList.add('active');
      l.setAttribute('aria-current', 'page');
    }
  });
  if (isServiceSection) {
    desktopServicesItem?.classList.add('current');
    desktopServicesLink?.classList.add('active');
    desktopServicesLink?.setAttribute('aria-current', 'page');
    mobileServicesGroup?.classList.add('current');
    mobileServicesLink?.classList.add('active');
    mobileServicesLink?.setAttribute('aria-current', 'page');
    mobileServicesLink?.setAttribute('aria-expanded', 'true');
  }

  /* Footer year */
  document.querySelectorAll('.f-copy').forEach(el => {
    el.textContent = `© ${new Date().getFullYear()} A1 Technovation. All rights reserved.`;
  });

  /* ── SCROLL-TO-TOP ───────────────────────────────────────────── */
  const scrollBtn = document.querySelector('.scroll-top');
  if (scrollBtn) {
    window.addEventListener('scroll', () => scrollBtn.classList.toggle('visible', window.scrollY > 400), { passive: true });
    scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  /* ── REVEAL ANIMATIONS ──────────────────────────────────────── */
  const revealEls = document.querySelectorAll('.reveal,.reveal-l,.reveal-r,.reveal-s');
  if ('IntersectionObserver' in window && revealEls.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target) } });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(el => io.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('in'));
  }

  /* ── COUNTER ANIMATION ──────────────────────────────────────── */
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    const ease = t => t < 0.5 ? 4*t*t*t : 1 - Math.pow(-2*t+2,3)/2;
    const animateCounter = (el) => {
      const target = parseFloat(el.dataset.count);
      const suffix = el.dataset.suffix || '';
      const prefix = el.dataset.prefix || '';
      const decimals = el.dataset.decimals ? parseInt(el.dataset.decimals) : 0;
      const duration = 2000;
      let start = null;
      const step = (ts) => {
        if (!start) start = ts;
        const progress = Math.min((ts - start) / duration, 1);
        const val = target * ease(progress);
        el.textContent = prefix + val.toFixed(decimals) + suffix;
        if (progress < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    const counterIO = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { animateCounter(e.target); counterIO.unobserve(e.target) } });
    }, { threshold: 0.5 });
    counters.forEach(c => counterIO.observe(c));
  }

  /* ── FAQ ACCORDION ──────────────────────────────────────────── */
  document.querySelectorAll('.faq-item').forEach(item => {
    item.querySelector('.faq-q')?.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(o => o.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });

  /* ── PORTFOLIO FILTER ───────────────────────────────────────── */
  const filterBtns  = document.querySelectorAll('.f-btn');
  const portItems   = document.querySelectorAll('.port-card');
  if (filterBtns.length && portItems.length) {
    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const cat = btn.dataset.filter;
        portItems.forEach(item => {
          const match = cat === 'all' || item.dataset.category === cat;
          item.style.transition = 'opacity .3s, transform .3s';
          item.style.opacity    = match ? '1' : '0';
          item.style.transform  = match ? 'scale(1)' : 'scale(0.95)';
          item.style.pointerEvents = match ? '' : 'none';
          setTimeout(() => { item.style.display = match ? '' : 'none' }, match ? 0 : 300);
          if (match) { setTimeout(() => { item.style.display = '' }, 0); setTimeout(() => { item.style.opacity = '1'; item.style.transform = 'scale(1)' }, 10) }
        });
      });
    });
  }

  /* ── CONTACT FORM ───────────────────────────────────────────── */
  const form = document.querySelector('#contact-form');
  if (form) {
    const formAlert = document.getElementById('contactFormAlert');
    const captchaPrompt = document.getElementById('contact-captcha-prompt');
    const captchaRefresh = document.getElementById('contact-captcha-refresh');
    const captchaTokenInput = document.getElementById('captcha_token');
    const captchaAnswerInput = document.getElementById('captcha_answer');
    const setFormAlert = (type, message) => {
      if (!formAlert) return;
      formAlert.hidden = !message;
      formAlert.className = `form-alert ${type}`;
      formAlert.textContent = message;
    };
    const clearFormAlert = () => {
      if (!formAlert) return;
      formAlert.hidden = true;
      formAlert.className = 'form-alert';
      formAlert.textContent = '';
    };
    const showErr = (id, msg) => {
      const el = document.getElementById(id + '-err');
      if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none' }
      const fi = document.getElementById(id);
      if (fi) fi.style.borderColor = msg ? '#ef4444' : '';
    };
    const validate = () => {
      let ok = true;
      const name  = form.querySelector('#name')?.value.trim();
      const email = form.querySelector('#email')?.value.trim();
      const website = form.querySelector('#website')?.value.trim();
      const svc   = form.querySelector('#service')?.value;
      const msg   = form.querySelector('#message')?.value.trim();
      const captchaAnswer = captchaAnswerInput?.value.trim();
      const captchaToken = captchaTokenInput?.value.trim();
      const blockedEmailDomains = ['10minutemail.com', 'guerrillamail.com', 'mailinator.com', 'tempmail.com', 'temp-mail.org', 'yopmail.com', 'example.com', 'test.com', 'invalid.com'];
      const emailDomain = email?.split('@')[1]?.toLowerCase() || '';
      const hasBlockedEmailDomain = blockedEmailDomains.some(domain => emailDomain === domain || emailDomain.endsWith(`.${domain}`));
      if (!name || name.length < 2)        { showErr('name',    'Please enter your full name.'); ok = false } else showErr('name', '');
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showErr('email', 'Enter a valid email address.'); ok = false }
      else if (hasBlockedEmailDomain)      { showErr('email', 'Please use a real business or personal email address.'); ok = false }
      else showErr('email', '');
      if (website) {
        try {
          const parsedWebsite = new URL(website);
          const hostname = parsedWebsite.hostname.replace(/^www\./, '').toLowerCase();
          const blockedWebsiteDomains = ['example.com', 'test.com', 'localhost'];
          const blockedWebsite = blockedWebsiteDomains.some(domain => hostname === domain || hostname.endsWith(`.${domain}`));
          if (!['http:', 'https:'].includes(parsedWebsite.protocol) || !hostname.includes('.') || blockedWebsite) {
            showErr('website', 'Enter your real public website URL starting with https://');
            ok = false;
          } else {
            showErr('website', '');
          }
        } catch (_) {
          showErr('website', 'Enter a complete website URL starting with https://');
          ok = false;
        }
      } else showErr('website', '');
      if (!svc)                            { showErr('service', 'Please select a service.');      ok = false } else showErr('service', '');
      if (!msg || msg.length < 10)         { showErr('message', 'Message must be at least 10 characters.'); ok = false } else showErr('message', '');
      if ((msg?.match(/https?:\/\/|www\./gi) || []).length > 2) { showErr('message', 'Please remove extra links from your message.'); ok = false }
      if (!captchaAnswer)                  { showErr('captcha_answer', 'Please solve the security check.'); ok = false } else showErr('captcha_answer', '');
      if (!captchaToken)                   { showErr('captcha_answer', 'The security check is still loading. Please wait a moment.'); ok = false }
      return ok;
    };
    const setCaptchaLoadingState = (isLoading) => {
      if (captchaRefresh) captchaRefresh.disabled = isLoading;
      if (captchaPrompt) captchaPrompt.textContent = isLoading ? 'Loading security question…' : captchaPrompt.textContent;
    };
    const loadCaptcha = async ({ silent = false } = {}) => {
      if (!captchaPrompt || !captchaTokenInput || !captchaAnswerInput) return true;

      setCaptchaLoadingState(true);
      showErr('captcha_answer', '');

      try {
        const response = await fetch('/php/contact-captcha.php', {
          method: 'GET',
          headers: { 'Accept': 'application/json' },
          cache: 'no-store'
        });
        const result = await response.json();
        const challenge = result?.captcha;

        if (!response.ok || result?.status !== 'success' || !challenge?.token || !challenge?.prompt) {
          throw new Error(result?.message || 'Unable to load the security check.');
        }

        captchaPrompt.textContent = challenge.prompt;
        captchaTokenInput.value = challenge.token;
        captchaAnswerInput.value = '';
        captchaAnswerInput.disabled = false;
        return true;
      } catch (error) {
        captchaPrompt.textContent = 'Security question unavailable right now.';
        captchaTokenInput.value = '';
        captchaAnswerInput.disabled = true;
        showErr('captcha_answer', 'Security check could not be loaded. Refresh the page and try again.');
        if (!silent) {
          setFormAlert('error', 'Security check could not be loaded right now. Please refresh the page and try again.');
        }
        return false;
      } finally {
        if (captchaRefresh) captchaRefresh.disabled = false;
      }
    };
    const params = new URLSearchParams(window.location.search);
    const fallbackStatus = params.get('form_status');
    const fallbackMessage = params.get('form_message');
    if (fallbackStatus && fallbackMessage) {
      setFormAlert(fallbackStatus === 'success' ? 'success' : 'error', fallbackMessage);
      const cleanUrl = `${window.location.pathname}${window.location.hash}`;
      window.history.replaceState({}, document.title, cleanUrl);
    }
    if (captchaRefresh) {
      captchaRefresh.addEventListener('click', () => {
        loadCaptcha();
      });
    }
    loadCaptcha({ silent: true });
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearFormAlert();
      if (!validate()) return;
      const btn = form.querySelector('[type="submit"]');
      const orig = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending…';
      btn.disabled = true;
      const pageUrlInput = form.querySelector('[name="page_url"]');
      if (pageUrlInput) pageUrlInput.value = window.location.href;
      const formData = new FormData(form);
      formData.set('page_url', window.location.href);

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: formData
        });

        let result = null;
        try {
          result = await response.json();
        } catch (_) {}

        btn.innerHTML = orig;
        btn.disabled = false;

        if (response.ok) {
          const successMessage = "Thanks for your message! We'll get back to you within 24 hours.";
          form.reset();
          if (captchaTokenInput) captchaTokenInput.value = '';
          clearFormAlert();
          setFormAlert('success', successMessage);
          toast('success', successMessage);
          loadCaptcha({ silent: true });
          return;
        }

        if (Array.isArray(result?.errors)) {
          result.errors.forEach(({ field, message }) => {
            if (field && ['name', 'email', 'website', 'service', 'message', 'captcha_answer'].includes(field)) {
              showErr(field, message || 'Please review this field.');
            }
          });
        }

        if (result?.errors?.some(err => err.field === 'captcha_answer')) {
          loadCaptcha({ silent: true });
        }

        const errorMessage =
          result?.errors?.map(err => err.message).filter(Boolean).join(' ') ||
          result?.message ||
          'Something went wrong. Please email us at info.a1technovation@gmail.com';

        setFormAlert('error', errorMessage);
        toast('error', errorMessage);
      } catch (err) {
        btn.innerHTML = orig;
        btn.disabled = false;
        form.submit();
      }
    });
  }

  /* ── NEWSLETTER FORM ────────────────────────────────────────── */
  document.querySelectorAll('.newsletter-form').forEach(f => {
    f.addEventListener('submit', e => {
      e.preventDefault();
      const input = f.querySelector('input[type="email"]');
      if (!input?.value.trim()) return;
      input.value = '';
      toast('success', 'Subscribed! Welcome to A1 Insights.');
    });
  });

  /* ── TOAST NOTIFICATION ─────────────────────────────────────── */
  window.toast = (type, message) => {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fas ${icons[type] || icons.info} t-ico"></i><span>${message}</span>`;
    document.body.appendChild(el);
    requestAnimationFrame(() => { requestAnimationFrame(() => el.classList.add('show')) });
    setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 400) }, 4000);
  };

  /* ── SMOOTH ANCHOR SCROLL ───────────────────────────────────── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }) }
    });
  });

  /* ── LAZY IMAGES ────────────────────────────────────────────── */
  if ('IntersectionObserver' in window) {
    document.querySelectorAll('img[data-src]').forEach(img => {
      const io = new IntersectionObserver(([e]) => {
        if (e.isIntersecting) { img.src = img.dataset.src; img.removeAttribute('data-src'); io.unobserve(img) }
      }, { rootMargin: '200px' });
      io.observe(img);
    });
  }

});
