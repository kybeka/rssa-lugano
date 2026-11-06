// ── JUICER: fallback ──────────────────────────────────────
const juicerFeed     = document.querySelector('.juicer-feed');
const juicerFallback = document.getElementById('latestFallback');

if (juicerFeed && juicerFallback) {
  const feedId = juicerFeed.getAttribute('data-feed-id');
  setTimeout(() => { if (!juicerFeed.querySelector('.feed-item, .jcr-post:not(.juicer-ad)')) juicerFallback.classList.add('latest__fallback--visible'); }, 12000);
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

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (submit.disabled || !form.reportValidity()) return;
  submit.disabled = true;
  submit.textContent = 'Sending…';
  status.textContent = '';
  status.className = 'form-status';
  try {
    const response = await fetch(form.getAttribute('action'), {method:'POST', body:new FormData(form), credentials:'same-origin'});
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.data?.message || 'Unable to send. Please refresh and try again.');
    status.textContent = result.data.message;
    status.className = 'form-status success';
    form.reset();
  } catch (error) {
    status.textContent = error instanceof SyntaxError || error instanceof TypeError ? 'We could not confirm submission. Your message is still here; please check your connection before retrying.' : error.message;
    status.className = 'form-status error';
  } finally {
    submit.disabled = false;
    submit.textContent = 'Send message';
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && drawer.classList.contains('open')) { toggle.click(); toggle.focus(); }
});

// External Telegram thumbnails may expire while the post text remains useful.
document.addEventListener('error', (event) => {
  if (event.target instanceof HTMLImageElement && event.target.closest('.juicer-feed')) event.target.hidden = true;
}, true);

// Native dialog supplies keyboard focus containment and Escape dismissal.
const gallery = document.querySelector('.rssa-gallery-grid');
if (gallery && typeof HTMLDialogElement !== 'undefined') {
  const viewer = document.createElement('dialog');
  viewer.className = 'rssa-photo-viewer';
  viewer.setAttribute('aria-label', 'Event photo');
  viewer.innerHTML = '<button type="button" class="rssa-photo-close" aria-label="Close photo">Close ×</button><figure><img alt=""><figcaption></figcaption></figure>';
  document.body.append(viewer);
  const photo = viewer.querySelector('img');
  const caption = viewer.querySelector('figcaption');
  let opener;
  gallery.addEventListener('click', (event) => {
    const link = event.target.closest('a');
    if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    opener = link;
    photo.src = link.href;
    photo.alt = link.querySelector('img').alt;
    caption.textContent = link.closest('figure').querySelector('figcaption')?.textContent || '';
    viewer.showModal();
    document.body.classList.add('photo-viewer-open');
  });
  viewer.querySelector('button').addEventListener('click', () => viewer.close());
  viewer.addEventListener('click', (event) => { if (event.target === viewer) viewer.close(); });
  viewer.addEventListener('close', () => {
    document.body.classList.remove('photo-viewer-open');
    photo.removeAttribute('src');
    opener?.focus({preventScroll:true});
  });
}

// Native scrolling preserves touch, trackpad and no-script navigation.
if (gallery && gallery.children.length > 1) {
  const slides = Array.from(gallery.children);
  gallery.id = 'rssa-gallery-track';
  gallery.tabIndex = 0;
  gallery.setAttribute('role', 'region');
  gallery.setAttribute('aria-roledescription', 'carousel');
  gallery.setAttribute('aria-label', 'Event photos. Use arrow keys to browse.');
  const controls = document.createElement('div');
  controls.className = 'rssa-gallery-controls';
  controls.innerHTML = '<span class="rssa-gallery-count" aria-live="polite" aria-atomic="true"></span><div class="rssa-gallery-buttons"><button type="button" aria-label="Previous photos" aria-controls="rssa-gallery-track">←</button><button type="button" aria-label="Next photos" aria-controls="rssa-gallery-track">→</button></div>';
  gallery.after(controls);
  const [previous, next] = controls.querySelectorAll('button');
  const count = controls.querySelector('.rssa-gallery-count');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let current = 0;
  function updateGallery() {
    const bounds = gallery.getBoundingClientRect();
    const visible = slides.map((slide, index) => ({index, rect:slide.getBoundingClientRect()}))
      .filter(({rect}) => rect.left < bounds.right - 20 && rect.right > bounds.left + 20);
    if (!visible.length) return;
    current = visible[0].index;
    const last = visible[visible.length - 1].index;
    count.textContent = (current === last ? String(current + 1) : (current + 1) + '–' + (last + 1)) + ' / ' + slides.length + ' photos';
    previous.disabled = gallery.scrollLeft < 2;
    next.disabled = gallery.scrollLeft + gallery.clientWidth >= gallery.scrollWidth - 2;
  }
  function moveGallery(index) {
    const slide = slides[Math.max(0, Math.min(slides.length - 1, index))];
    const left = gallery.scrollLeft + slide.getBoundingClientRect().left - gallery.getBoundingClientRect().left;
    gallery.scrollTo({left, behavior:reducedMotion.matches ? 'instant' : 'smooth'});
  }
  previous.addEventListener('click', () => moveGallery(current - 1));
  next.addEventListener('click', () => moveGallery(current + 1));
  gallery.addEventListener('keydown', (event) => {
    const targets = {ArrowLeft:current - 1, ArrowRight:current + 1, Home:0, End:slides.length - 1};
    if (!(event.key in targets)) return;
    event.preventDefault();
    moveGallery(targets[event.key]);
  });
  let scrollFrame;
  gallery.addEventListener('scroll', () => {
    cancelAnimationFrame(scrollFrame);
    scrollFrame = requestAnimationFrame(updateGallery);
  }, {passive:true});
  new ResizeObserver(updateGallery).observe(gallery);
  updateGallery();
}

const announcements = document.querySelector('.rssa-announcements');
if (announcements && typeof ResizeObserver !== 'undefined') {
  const cards = Array.from(announcements.children);
  const masonryObserver = new ResizeObserver(entries => {
    for (const entry of entries) {
      const height = entry.borderBoxSize?.[0]?.blockSize ?? entry.target.getBoundingClientRect().height;
      const span = String(Math.ceil(height + 24));
      if (entry.target.style.gridRowEnd !== 'span ' + span) entry.target.style.gridRowEnd = 'span ' + span;
    }
  });
  // Measure first so the layout never collapses while enhancement starts.
  cards.forEach(card => {card.style.gridRowEnd = 'span ' + Math.ceil(card.getBoundingClientRect().height + 24);});
  announcements.classList.add('rssa-masonry');
  cards.forEach(card => masonryObserver.observe(card));
}
