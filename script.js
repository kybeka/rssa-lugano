// ── JUICER: show fallback if feed not yet configured ──────
const juicerFeed = document.querySelector('.juicer-feed');
const juicerFallback = document.getElementById('latestFallback');
if (juicerFeed && juicerFallback) {
  const feedId = juicerFeed.getAttribute('data-feed-id');
  if (!feedId || feedId === 'YOUR-FEED-ID') {
    juicerFallback.classList.add('latest__fallback--visible');
  }
}

// ── NAV: scroll state ──────────────────────────────────────
const nav = document.getElementById('nav');
const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 48);
window.addEventListener('scroll', onScroll, { passive: true });
onScroll();

// ── NAV: mobile drawer ────────────────────────────────────
const toggle     = document.getElementById('navToggle');
const drawer     = document.getElementById('navDrawer');
const drawerLinks = drawer.querySelectorAll('a');

toggle.addEventListener('click', () => {
  const open = drawer.classList.toggle('open');
  toggle.setAttribute('aria-expanded', open);
  toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
  document.body.style.overflow = open ? 'hidden' : '';
});

drawerLinks.forEach(link => {
  link.addEventListener('click', () => {
    drawer.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Open navigation');
    document.body.style.overflow = '';
  });
});

// Close drawer on outside click (backdrop)
document.addEventListener('click', (e) => {
  if (drawer.classList.contains('open') &&
      !drawer.contains(e.target) &&
      !toggle.contains(e.target)) {
    drawer.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Open navigation');
    document.body.style.overflow = '';
  }
});

// ── SCROLL REVEAL ─────────────────────────────────────────
const revealEls = document.querySelectorAll('.reveal');
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('in');
      io.unobserve(e.target);
    }
  });
}, { threshold: 0.06, rootMargin: '0px 0px -48px 0px' });

revealEls.forEach(el => io.observe(el));

// Trigger hero immediately (already in view)
requestAnimationFrame(() => {
  document.querySelectorAll('.hero .reveal').forEach(el => el.classList.add('in'));
});

// ── CONTACT FORM ──────────────────────────────────────────
const form   = document.getElementById('contactForm');
const submit = document.getElementById('formSubmit');
const status = document.getElementById('formStatus');

const FORM_WIRED = !form.action.includes('YOUR_FORM_ID');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  // If Formspree hasn't been configured, open mailto as fallback
  if (!FORM_WIRED) {
    const name    = form.firstName.value + ' ' + form.lastName.value;
    const message = form.message.value;
    const subject = encodeURIComponent('Hello from the RSSA website');
    const body    = encodeURIComponent(`From: ${name}\n\n${message}`);
    window.location.href = `mailto:RSSA@usi.ch?subject=${subject}&body=${body}`;
    return;
  }

  submit.disabled = true;
  submit.textContent = 'Sending…';
  status.textContent = '';
  status.className = 'form-status';

  try {
    const res = await fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { Accept: 'application/json' },
    });

    if (res.ok) {
      submit.textContent = 'Send message';
      submit.disabled = false;
      status.textContent = 'Message sent. We\'ll be in touch soon.';
      status.className = 'form-status success';
      form.reset();
      setTimeout(() => {
        status.textContent = '';
        status.className = 'form-status';
      }, 8000);
    } else {
      throw new Error('Server error');
    }
  } catch {
    submit.textContent = 'Send message';
    submit.disabled = false;
    status.textContent = 'Something went wrong. Email us directly at RSSA@usi.ch';
    status.className = 'form-status error';
  }
});
