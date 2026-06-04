// ── JUICER: fallback ──────────────────────────────────────
const juicerFeed     = document.querySelector('.juicer-feed');
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
const toggle      = document.getElementById('navToggle');
const drawer      = document.getElementById('navDrawer');
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

requestAnimationFrame(() => {
  document.querySelectorAll('.hero .reveal').forEach(el => el.classList.add('in'));
});

// ── CONTACT FORM ──────────────────────────────────────────
const form   = document.getElementById('contactForm');
const submit = document.getElementById('formSubmit');
const status = document.getElementById('formStatus');

form.addEventListener('submit', (e) => {
  e.preventDefault();
  if (!form.checkValidity()) { form.reportValidity(); return; }

  const name    = `${form.firstName.value.trim()} ${form.lastName.value.trim()}`;
  const replyTo = form.email.value.trim();
  const message = form.message.value.trim();
  const subject = encodeURIComponent('Message via RSSA website');
  const body    = encodeURIComponent(`From: ${name}\nReply to: ${replyTo}\n\n${message}`);

  let launched = false;
  const onBlur = () => { launched = true; };
  window.addEventListener('blur', onBlur, { once: true });

  window.location.href = `mailto:RSSA@usi.ch?subject=${subject}&body=${body}`;

  setTimeout(() => {
    window.removeEventListener('blur', onBlur);
    if (launched) {
      status.textContent = 'Your email client should have opened — just hit send!';
      status.className = 'form-status success';
    } else {
      status.textContent = 'No email client detected. Please email us directly at RSSA@usi.ch';
      status.className = 'form-status error';
    }
    setTimeout(() => { status.textContent = ''; status.className = 'form-status'; }, 8000);
  }, 600);
});
