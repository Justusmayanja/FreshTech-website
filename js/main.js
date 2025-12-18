// FreshTech Solutions Interactions
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

// Mobile nav toggle
const mobileToggle = document.querySelector('[data-menu-toggle]');
const navLinks = document.querySelector('.nav-links');
if (mobileToggle && navLinks) {
  mobileToggle.addEventListener('click', () => {
    navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
    navLinks.classList.toggle('open');
  });
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
