// PulseTech Solutions — static site interactions

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

// Mobile navigation
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
function initPortfolioFilters() {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const portfolioItems = document.querySelectorAll('.portfolio-item');
  if (!filterButtons.length || !portfolioItems.length) return;

  filterButtons.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      filterButtons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      const filterValue = btn.dataset.filter;
      portfolioItems.forEach((item) => {
        const categories = (item.getAttribute('data-category') || '').split(' ');
        const shouldShow = filterValue === 'all' || categories.includes(filterValue);
        item.classList.toggle('hidden', !shouldShow);
        item.style.display = shouldShow ? '' : 'none';
      });
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPortfolioFilters);
} else {
  initPortfolioFilters();
}

// Carousel drag-scroll
document.querySelectorAll('.carousel').forEach((c) => {
  let isDown = false;
  let startX;
  let scrollLeft;

  c.addEventListener('mousedown', (e) => {
    isDown = true;
    startX = e.pageX - c.offsetLeft;
    scrollLeft = c.scrollLeft;
    c.classList.add('dragging');
  });
  c.addEventListener('mouseleave', () => {
    isDown = false;
    c.classList.remove('dragging');
  });
  c.addEventListener('mouseup', () => {
    isDown = false;
    c.classList.remove('dragging');
  });
  c.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - c.offsetLeft;
    c.scrollLeft = scrollLeft - (x - startX) * 1.4;
  });
});

// Contact form — package prefill from URL
const contactForm = document.getElementById('contact-form');
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
      else if (lower.includes('app') || lower.includes('system')) serviceSelect.value = 'software';
      else if (lower.includes('commerce')) serviceSelect.value = 'ecommerce';
    }
  } else if (packageHidden) {
    packageHidden.value = '';
  }

  contactForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const submitBtn = contactForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    const formData = new FormData(contactForm);
    const name = formData.get('fullname') || '';
    const email = formData.get('email') || '';
    const phone = formData.get('phone') || '';
    const service = formData.get('service') || '';
    const message = formData.get('message') || '';
    const pkg = formData.get('selectedPackage') || '';

    const subject = encodeURIComponent(`PulseTech Inquiry from ${name}`);
    const bodyText = encodeURIComponent(
      `Name: ${name}\nEmail: ${email}\nPhone: ${phone}\nService: ${service}\nPackage: ${pkg}\n\n${message}`
    );

    window.location.href = `mailto:pulsetechsolutions@gmail.com?subject=${subject}&body=${bodyText}`;

    setTimeout(() => {
      contactForm.innerHTML = `
        <div style="padding: 40px; text-align: center; background: rgba(0,212,255,0.1); color: var(--text); border-radius: 8px; border: 1px solid var(--cyan);">
          <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 16px; color: var(--cyan);"></i>
          <h3 style="margin: 0 0 8px 0;">Thank you, ${name}!</h3>
          <p style="margin: 0;">Your email client should open shortly. If it doesn't, email us at <strong>pulsetechsolutions@gmail.com</strong>.</p>
        </div>
      `;
    }, 500);

    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
  });
}

// Broken image fallback
(function ensureAllImages() {
  const imgs = document.querySelectorAll('img');
  imgs.forEach((img) => {
    const setFallback = () => {
      if (img.dataset.fallbackApplied) return;
      img.dataset.fallbackApplied = '1';
      img.onerror = null;
      img.style.objectFit = 'cover';
      img.style.background = 'linear-gradient(145deg, var(--navy-2), var(--navy))';
    };
    img.addEventListener('error', setFallback);
    if (img.complete && img.naturalWidth === 0) setFallback();
  });
})();
