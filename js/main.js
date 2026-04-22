/* =================================================================
   A1 TECHNOVATION — Main JavaScript 2025
================================================================= */

document.addEventListener('DOMContentLoaded', () => {

  /* ── NAVBAR ─────────────────────────────────────────────────── */
  const navbar = document.querySelector('.navbar');
  const mobileToggle = document.querySelector('.mobile-toggle');
  const mobileMenu   = document.querySelector('.mobile-menu');

  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 20);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  if (mobileToggle && mobileMenu) {
    const open  = () => { mobileMenu.classList.add('open'); mobileToggle.classList.add('open'); document.body.style.overflow = 'hidden' };
    const close = () => { mobileMenu.classList.remove('open'); mobileToggle.classList.remove('open'); document.body.style.overflow = '' };

    mobileToggle.addEventListener('click', () => mobileMenu.classList.contains('open') ? close() : open());
    mobileMenu.querySelectorAll('.mobile-menu-link').forEach(l => l.addEventListener('click', close));
    document.addEventListener('keydown', e => e.key === 'Escape' && close());
  }

  /* Active nav link */
  const navLinks = document.querySelectorAll('.nav-link, .mobile-menu-link');
  const page = location.pathname.split('/').pop() || 'index.html';
  navLinks.forEach(l => {
    const href = (l.getAttribute('href') || '').split('/').pop();
    if (href === page || (page === 'index.html' && href === '')) l.classList.add('active');
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
      const svc   = form.querySelector('#service')?.value;
      const msg   = form.querySelector('#message')?.value.trim();
      if (!name || name.length < 2)        { showErr('name',    'Please enter your full name.'); ok = false } else showErr('name', '');
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showErr('email', 'Enter a valid email address.'); ok = false } else showErr('email', '');
      if (!svc)                            { showErr('service', 'Please select a service.');      ok = false } else showErr('service', '');
      if (!msg || msg.length < 10)         { showErr('message', 'Message must be at least 10 characters.'); ok = false } else showErr('message', '');
      return ok;
    };
    form.addEventListener('submit', e => {
      e.preventDefault();
      if (!validate()) return;
      const btn = form.querySelector('[type="submit"]');
      const orig = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending…';
      btn.disabled = true;

      const formData = new FormData(form);
      const data = {
        name: form.querySelector('#name').value,
        email: form.querySelector('#email').value,
        phone: form.querySelector('#phone').value || 'Not provided',
        company: form.querySelector('#company').value || 'Not provided',
        service: form.querySelector('#service').value,
        budget: form.querySelector('#budget').value || 'Not specified',
        message: form.querySelector('#message').value,
        timestamp: new Date().toISOString()
      };

      fetch('https://formspree.io/f/meevrprq', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(response => {
        btn.innerHTML = orig;
        btn.disabled = false;
        if (response.ok) {
          form.reset();
          toast('success', 'Message sent! We\'ll be in touch within 24 hours.');
        } else {
          toast('error', 'Failed to send. Please email info.a1technovation@gmail.com');
        }
      })
      .catch(err => {
        btn.innerHTML = orig;
        btn.disabled = false;
        toast('error', 'Failed to send. Email us at info.a1technovation@gmail.com');
      });
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
