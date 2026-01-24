// PulseTech Solutions Interactions

const body = document.body;

const themeToggle = () => {
  const current = body.getAttribute('data-theme') || 'dark';
  const next = current === 'dark' ? 'light' : 'dark';
  body.setAttribute('data-theme', next);
  localStorage.setItem('fts-theme', next);
};

const stored = localStorage.getItem('fts-theme');
if (stored) body.setAttribute('data-theme', stored);
else body.setAttribute('data-theme', 'dark');

document.addEventListener('click', (e) => {
  if (e.target.matches('[data-theme-toggle]')) {
    e.preventDefault();
    themeToggle();
  }
});

// Header + navigation interactions
const siteNav = document.querySelector('[data-site-nav]');
const navToggle = document.querySelector('[data-nav-toggle]');
const navPanel = document.querySelector('[data-nav-panel]');
const navBackdrop = document.querySelector('[data-nav-backdrop]');
const siteHeader = document.querySelector('[data-site-header]');
const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
let navOpen = false;
let resizeTimer;

function toggleBodyLock(lock) {
  document.body.classList.toggle('nav-locked', Boolean(lock));
}

function setNavState(open) {
  if (!siteNav || !navToggle || !navPanel) return;
  navOpen = open;
  siteNav.classList.toggle('site-nav--open', open);
  navToggle.setAttribute('aria-expanded', String(open));
  const hideForMobile = !open && window.innerWidth <= 960;
  navPanel.setAttribute('aria-hidden', hideForMobile ? 'true' : 'false');
  toggleBodyLock(open);
  if (open) {
    const focusable = navPanel.querySelector(focusableSelector);
    if (focusable) focusable.focus({ preventScroll: true });
    else navPanel.focus({ preventScroll: true });
  }
}

function closeNav() { setNavState(false); }
function openNav() { setNavState(true); }

function handleOutsideClick(e) {
  if (!navOpen || !navPanel || !navToggle) return;
  const isToggle = navToggle.contains(e.target);
  const isPanel = navPanel.contains(e.target);
  if (!isToggle && !isPanel) closeNav();
}

function handleEscape(e) {
  if (e.key === 'Escape' && navOpen) {
    e.preventDefault();
    closeNav();
    navToggle?.focus({ preventScroll: true });
  }
}

function handleFocusTrap(e) {
  if (!navOpen || !navPanel || e.key !== 'Tab') return;
  const focusables = Array.from(navPanel.querySelectorAll(focusableSelector)).filter(el => !el.hasAttribute('disabled') && el.offsetParent !== null);
  if (!focusables.length) return;
  const first = focusables[0];
  const last = focusables[focusables.length - 1];
  const active = document.activeElement;
  if (e.shiftKey && active === first) {
    e.preventDefault();
    last.focus();
  } else if (!e.shiftKey && active === last) {
    e.preventDefault();
    first.focus();
  }
}

if (navToggle && navPanel) {
  navToggle.addEventListener('click', () => {
    navOpen ? closeNav() : openNav();
  });

  navBackdrop?.addEventListener('click', closeNav);

  navPanel.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (link) closeNav();
  });

  document.addEventListener('click', handleOutsideClick);
  document.addEventListener('keydown', handleEscape);
  document.addEventListener('keydown', handleFocusTrap);

  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      if (window.innerWidth > 960 && navOpen) closeNav();
    }, 150);
  });

  // init state respects viewport
  setNavState(false);
}

// Legacy static page nav (index.html) support
const legacyToggle = document.querySelector('[data-menu-toggle]');
const legacyNavLinks = document.querySelector('.nav-links');
function resetLegacyNav() {
  if (!legacyNavLinks) return;
  if (window.innerWidth > 960) legacyNavLinks.classList.remove('open');
}
if (legacyToggle && legacyNavLinks) {
  legacyToggle.addEventListener('click', () => {
    legacyNavLinks.classList.toggle('open');
  });
  window.addEventListener('resize', resetLegacyNav);
  resetLegacyNav();
}

// Portfolio filters
const filterButtons = document.querySelectorAll('.filter-btn');
const portfolioItems = document.querySelectorAll('[data-category]');
filterButtons.forEach((btn) => {
  btn.addEventListener('click', () => {
    filterButtons.forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.dataset.filter;
    portfolioItems.forEach((item) => {
      if (cat === 'all' || item.dataset.category.includes(cat)) item.style.display = 'block';
      else item.style.display = 'none';
    });
  });
});

// Basic carousel drag-scroll
const carousels = document.querySelectorAll('.carousel');
carousels.forEach((c) => {
  let isDown = false;
  let startX;
  let scrollLeft;
  c.addEventListener('mousedown', (e) => {
    isDown = true;
    startX = e.pageX - c.offsetLeft;
    scrollLeft = c.scrollLeft;
    c.classList.add('dragging');
  });
  c.addEventListener('mouseleave', () => { isDown = false; c.classList.remove('dragging'); });
  c.addEventListener('mouseup', () => { isDown = false; c.classList.remove('dragging'); });
  c.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - c.offsetLeft;
    const walk = (x - startX) * 1.4;
    c.scrollLeft = scrollLeft - walk;
  });
});

// Contact form package prefill and demo submit
const contactForm = document.querySelector('#contact-form');
if (contactForm) {
  const packageBadge = document.querySelector('[data-selected-package]');
  const packageNameSlot = document.querySelector('[data-package-name]');
  const packageHidden = document.querySelector('#selectedPackage');
  const serviceSelect = document.querySelector('#service');

  const params = new URLSearchParams(window.location.search);
  const pickedPackage = params.get('package');
  if (pickedPackage && packageBadge && packageNameSlot) {
    const decoded = pickedPackage.replace(/\+/g, ' ');
    packageBadge.style.display = 'block';
    packageNameSlot.textContent = decoded;
    if (packageHidden) packageHidden.value = decoded;
    if (serviceSelect) {
      const lower = decoded.toLowerCase();
      if (lower.includes('brand')) serviceSelect.value = 'branding';
      else if (lower.includes('web')) serviceSelect.value = 'website';
    }
  } else if (packageHidden) {
    packageHidden.value = '';
  }

  contactForm.addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Message sent! We will reply shortly.');
  });
}

// Ensure any broken image uses a local placeholder (works on nested pages)
(function ensureAllImages() {
  const fallbackURL = new URL('images/placeholder.svg', document.baseURI).toString();
  const imgs = document.querySelectorAll('img');
  imgs.forEach((img) => {
    const setFallback = () => {
      if (!img.src || img.src === fallbackURL) return;
      img.onerror = null;
      img.src = fallbackURL;
    };
    img.addEventListener('error', setFallback);
    if (img.complete && img.naturalWidth === 0) setFallback();
  });
})();

// ────────────────────────────────────────────────
// Featured Products – Dynamic loading from database
// ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  // Your existing DOMContentLoaded content can go here if you ever add more

  loadFeaturedProducts();
});

async function loadFeaturedProducts() {
  const container = document.getElementById('featured-products-container');
  if (!container) {
    console.warn('Featured products container (#featured-products-container) not found');
    return;
  }

  try {
    const response = await fetch('api/products.php');

    if (!response.ok) {
      throw new Error(`Server responded with status ${response.status}`);
    }

    const result = await response.json();

    if (result.status !== 'success') {
      throw new Error(result.message || 'API returned an error');
    }

    container.innerHTML = '';  // remove loading placeholder

    if (result.data.length === 0) {
      container.innerHTML = '<p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;">No featured products available right now.</p>';
      return;
    }

    result.data.forEach(product => {
      const currentPrice = Number(product.price_ugx).toLocaleString('en-US');
      const oldPriceHTML = product.old_price_ugx
        ? `<span class="price" style="text-decoration: line-through; opacity: 0.7; margin-left: 12px;">
             UGX ${Number(product.old_price_ugx).toLocaleString('en-US')}
           </span>`
        : '';

      const badgeHTML = product.discount
        ? `<span class="badge-sale">${product.discount}</span>`
        : '';

      const ratingNum = Math.round(product.rating || 5);
      const stars = '★★★★★'.slice(0, ratingNum) + '☆☆☆☆☆'.slice(0, 5 - ratingNum);

      const desc = product.description || '';

      const card = `
        <div class="card product">
          ${badgeHTML}
          <img src="${product.image_url || 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&h=260&fit=crop'}" 
               alt="${product.name}" loading="lazy" />
          <h4>${product.name}</h4>
          <p class="subtle">${desc} • UGX ${currentPrice}</p>
          <div class="price-row">
            <span class="price">UGX ${currentPrice}</span>
            ${oldPriceHTML}
            <span class="rating">${stars}</span>
          </div>
          <button class="btn btn-primary">Add to Cart</button>
        </div>
      `;

      container.innerHTML += card;
    });

  } catch (err) {
    console.error('Failed to load featured products:', err);
    container.innerHTML = `
      <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: #e63946;">
        <strong>Could not load featured products</strong><br>
        <small>${err.message}</small>
      </div>
    `;
  }
}