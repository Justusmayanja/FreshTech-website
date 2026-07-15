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

// Mobile navigation — half-page drawer from the right
const legacyToggle = document.querySelector('[data-menu-toggle]');
const legacyNavLinks = document.querySelector('.nav-links');
const legacyNavbar = document.querySelector('.navbar');

function setLegacyNavOpen(open) {
  if (!legacyNavLinks) return;
  legacyNavLinks.classList.toggle('open', open);
  document.querySelector('.nav-backdrop')?.classList.toggle('is-open', open);
  body.classList.toggle('nav-locked', open);
  legacyToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
  legacyToggle?.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
}

function mountLegacyNav() {
  if (!legacyNavLinks || !legacyNavbar) return;

  if (window.innerWidth <= 960) {
    if (legacyNavLinks.parentElement !== document.body) {
      document.body.appendChild(legacyNavLinks);
    }
    return;
  }

  setLegacyNavOpen(false);
  if (legacyNavLinks.parentElement !== legacyNavbar) {
    const navActions = legacyNavbar.querySelector('.nav-actions');
    if (navActions) {
      navActions.insertAdjacentElement('beforebegin', legacyNavLinks);
    } else {
      legacyNavbar.appendChild(legacyNavLinks);
    }
  }
}

function resetLegacyNav() {
  mountLegacyNav();
  if (window.innerWidth > 960) setLegacyNavOpen(false);
}

if (legacyToggle && legacyNavLinks) {
  let navBackdrop = document.querySelector('.nav-backdrop');
  if (!navBackdrop) {
    navBackdrop = document.createElement('div');
    navBackdrop.className = 'nav-backdrop';
    navBackdrop.setAttribute('aria-hidden', 'true');
    document.body.appendChild(navBackdrop);
  }

  legacyToggle.setAttribute('aria-expanded', 'false');

  legacyToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    setLegacyNavOpen(!legacyNavLinks.classList.contains('open'));
  });

  navBackdrop.addEventListener('click', () => setLegacyNavOpen(false));

  legacyNavLinks.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setLegacyNavOpen(false));
  });

  window.addEventListener('resize', resetLegacyNav);
  mountLegacyNav();
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
const contactFormRoot = document.getElementById('contact-form');
const contactForm = contactFormRoot?.tagName === 'FORM'
  ? contactFormRoot
  : contactFormRoot?.querySelector('form');
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
      if (lower.includes('social') || lower.includes('media')) serviceSelect.value = 'social';
      else if (lower.includes('software') || lower.includes('pos') || lower.includes('management')) serviceSelect.value = 'software';
      else if (lower.includes('web')) serviceSelect.value = 'website';
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

    window.location.href = `mailto:pulsetechsolutions.info@gmail.com?subject=${subject}&body=${bodyText}`;

    setTimeout(() => {
      contactForm.innerHTML = `
        <div style="padding: 40px; text-align: center; background: rgba(0,212,255,0.1); color: var(--text); border-radius: 8px; border: 1px solid var(--cyan);">
          <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 16px; color: var(--cyan);"></i>
          <h3 style="margin: 0 0 8px 0;">Thank you, ${name}!</h3>
          <p style="margin: 0;">Your email client should open shortly. If it doesn't, email us at <strong>pulsetechsolutions.info@gmail.com</strong>.</p>
        </div>
      `;
    }, 500);

    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
  });
}

// Homepage blog cards — inline partial preview (company help, not full how-to)
function initBlogReadMore() {
  const cards = document.querySelectorAll('[data-blog-card]');
  if (!cards.length) return;

  cards.forEach((card) => {
    const toggle = card.querySelector('[data-blog-read-more]');
    const expand = card.querySelector('.blog-showcase-card__expand');
    const label = toggle?.querySelector('.blog-showcase-card__link-text');
    if (!toggle || !expand || !label) return;

    toggle.addEventListener('click', () => {
      const isOpen = card.classList.contains('is-expanded');

      cards.forEach((other) => {
        if (other === card) return;
        other.classList.remove('is-expanded');
        const otherToggle = other.querySelector('[data-blog-read-more]');
        const otherExpand = other.querySelector('.blog-showcase-card__expand');
        const otherLabel = otherToggle?.querySelector('.blog-showcase-card__link-text');
        if (otherExpand) otherExpand.hidden = true;
        if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
        if (otherLabel) otherLabel.textContent = 'Read More';
      });

      if (isOpen) {
        card.classList.remove('is-expanded');
        expand.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        label.textContent = 'Read More';
      } else {
        card.classList.add('is-expanded');
        expand.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        label.textContent = 'Read Less';
      }
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initBlogReadMore);
} else {
  initBlogReadMore();
}

// Blog page — show one full article at a time via Read More
function initBlogPageReadMore() {
  if (!document.body.classList.contains('blog-page')) return;

  const section = document.getElementById('articles');
  if (!section) return;

  const articles = section.querySelectorAll('.article-panel');
  const links = document.querySelectorAll('.blog-page .blog-showcase-card__link');
  if (!links.length || !articles.length) return;

  let activeId = null;

  function setLinkLabel(link, text) {
    link.innerHTML = `${text} <i class="fas fa-arrow-right" aria-hidden="true"></i>`;
  }

  function closeArticles() {
    activeId = null;
    section.hidden = true;
    articles.forEach((article) => article.classList.remove('is-active'));
    links.forEach((link) => setLinkLabel(link, 'Read More'));
  }

  links.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const id = link.getAttribute('href')?.replace('#', '');
      if (!id) return;

      if (activeId === id) {
        closeArticles();
        return;
      }

      activeId = id;
      section.hidden = false;
      articles.forEach((article) => {
        article.classList.toggle('is-active', article.id === id);
      });
      links.forEach((item) => {
        setLinkLabel(item, item.getAttribute('href')?.replace('#', '') === id ? 'Read Less' : 'Read More');
      });
      section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initBlogPageReadMore);
} else {
  initBlogPageReadMore();
}

// Live chat widget
const LIVE_CHAT = {
  phone: '256752895268',
  email: 'pulsetechsolutions.info@gmail.com',
  lastUserMessage: '',
};

function getLiveChatReply(text) {
  const msg = text.toLowerCase();
  if (/website|web design|web dev/.test(msg)) {
    return 'We build websites from UGX 450,000 — mobile-friendly, SEO-ready, and tailored for Ugandan businesses. Would you like a free quote?';
  }
  if (/mobile money|mtn|airtel|payment|pay/.test(msg)) {
    return 'We integrate MTN Mobile Money and Airtel Money into websites, e-commerce stores, and custom software. Integration projects start from UGX 200,000.';
  }
  if (/price|cost|budget|quote|how much/.test(msg)) {
    return 'Website packages: Starter UGX 450,000 · Business UGX 750,000 · Premium UGX 1,500,000. Tell us your project and we will send a personalised quote within 24 hours.';
  }
  if (/pos|retail|shop|store/.test(msg)) {
    return 'We build POS systems for Ugandan retailers — inventory, receipts, sales reports, and optional Mobile Money at checkout. Request a demo on our contact page.';
  }
  if (/social|tiktok|instagram|facebook|x\b|twitter/.test(msg)) {
    return 'Our social media growth service covers TikTok, Instagram, Facebook, and X — including filming, editing, and daily posting. Packages from UGX 150,000/month.';
  }
  if (/hello|hi|hey|good/.test(msg)) {
    return 'Hello! Welcome to PulseTech Solutions. How can we help — website, software, Mobile Money, or social media?';
  }
  return 'Thanks for your message! Our team at Makerere Kikoni will follow up soon. For a faster reply, continue the conversation on WhatsApp.';
}

function appendLiveChatMessage(container, text, type) {
  const bubble = document.createElement('div');
  bubble.className = `live-chat__msg live-chat__msg--${type}`;
  bubble.textContent = text;
  container.appendChild(bubble);
  container.scrollTop = container.scrollHeight;
}

function getWhatsAppLink(message) {
  const text = message
    ? `Hi PulseTech, ${message}`
    : 'Hi PulseTech! I would like to chat about a project.';
  return `https://wa.me/${LIVE_CHAT.phone}?text=${encodeURIComponent(text)}`;
}

function initLiveChat() {
  let widget = document.getElementById('live-chat-widget');
  if (!widget) {
    widget = document.createElement('div');
    widget.id = 'live-chat-widget';
    widget.className = 'live-chat';
    widget.setAttribute('aria-hidden', 'true');
    widget.innerHTML = `
      <button type="button" class="live-chat__backdrop" data-live-chat-close aria-label="Close chat"></button>
      <div class="live-chat__panel" role="dialog" aria-labelledby="live-chat-title" aria-modal="true">
        <div class="live-chat__header">
          <div>
            <strong id="live-chat-title">PulseTech Live Chat</strong>
            <span>Makerere Kikoni · Kampala</span>
          </div>
          <button type="button" class="live-chat__close" data-live-chat-close aria-label="Close chat">&times;</button>
        </div>
        <div class="live-chat__messages" data-live-chat-messages></div>
        <div class="live-chat__quick" data-live-chat-quick>
          <button type="button" data-live-chat-quick-msg="I need a website quote">Website quote</button>
          <button type="button" data-live-chat-quick-msg="Mobile Money integration">Mobile Money</button>
          <button type="button" data-live-chat-quick-msg="E-commerce store">E-commerce</button>
          <button type="button" data-live-chat-quick-msg="Social media help">Social media</button>
        </div>
        <form class="live-chat__form" data-live-chat-form>
          <input type="text" data-live-chat-input placeholder="Type your message..." autocomplete="off" />
          <button type="submit" aria-label="Send message"><i class="fas fa-paper-plane"></i></button>
        </form>
        <div class="live-chat__footer">
          <a href="${getWhatsAppLink('')}" target="_blank" rel="noopener" data-live-chat-whatsapp>Continue on WhatsApp · 0752 895 268</a>
        </div>
      </div>
    `;
    document.body.appendChild(widget);
  }

  const messagesEl = widget.querySelector('[data-live-chat-messages]');
  const form = widget.querySelector('[data-live-chat-form]');
  const input = widget.querySelector('[data-live-chat-input]');
  const whatsappLink = widget.querySelector('[data-live-chat-whatsapp]');
  let booted = false;

  function bootChat() {
    if (booted || !messagesEl) return;
    booted = true;
    appendLiveChatMessage(
      messagesEl,
      'Hi! Welcome to PulseTech Solutions. Ask about websites, Mobile Money, POS, or social media — we are here to help.',
      'bot'
    );
  }

  function sendUserMessage(text) {
    const trimmed = text.trim();
    if (!trimmed || !messagesEl) return;
    LIVE_CHAT.lastUserMessage = trimmed;
    appendLiveChatMessage(messagesEl, trimmed, 'user');
    if (whatsappLink) whatsappLink.href = getWhatsAppLink(trimmed);
    window.setTimeout(() => {
      appendLiveChatMessage(messagesEl, getLiveChatReply(trimmed), 'bot');
    }, 600);
  }

  function openChat() {
    bootChat();
    widget.classList.add('is-open');
    widget.setAttribute('aria-hidden', 'false');
    input?.focus();
  }

  function closeChat() {
    widget.classList.remove('is-open');
    widget.setAttribute('aria-hidden', 'true');
  }

  widget.querySelectorAll('[data-live-chat-close]').forEach((btn) => {
    btn.addEventListener('click', closeChat);
  });

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!input) return;
    sendUserMessage(input.value);
    input.value = '';
  });

  widget.querySelectorAll('[data-live-chat-quick-msg]').forEach((btn) => {
    btn.addEventListener('click', () => {
      sendUserMessage(btn.getAttribute('data-live-chat-quick-msg') || '');
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && widget.classList.contains('is-open')) closeChat();
  });

  const floatContainer = document.querySelector('.whatsapp-btn');
  if (floatContainer && !floatContainer.querySelector('[data-live-chat-open]')) {
    const chatBtn = document.createElement('button');
    chatBtn.type = 'button';
    chatBtn.className = 'floating-btn livechat';
    chatBtn.title = 'Live Chat';
    chatBtn.setAttribute('data-live-chat-open', '');
    chatBtn.setAttribute('aria-label', 'Open live chat');
    chatBtn.innerHTML = `
      <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2.546 20.2A1 1 0 003.8 21.454l3.032-.892A9.957 9.957 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.84 0-3.553-.622-4.917-1.668l-.347-.266-2.158.634.634-2.158-.266-.347A7.955 7.955 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/>
        <circle cx="8" cy="12" r="1.5"/>
        <circle cx="12" cy="12" r="1.5"/>
        <circle cx="16" cy="12" r="1.5"/>
      </svg>
    `;
    floatContainer.prepend(chatBtn);
  }

  if (!document.querySelector('.whatsapp-btn')) {
    const container = document.createElement('div');
    container.className = 'whatsapp-btn';
    container.innerHTML = `
      <button type="button" class="floating-btn livechat" title="Live Chat" data-live-chat-open aria-label="Open live chat">
        <svg width="26" height="26" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2.546 20.2A1 1 0 003.8 21.454l3.032-.892A9.957 9.957 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.84 0-3.553-.622-4.917-1.668l-.347-.266-2.158.634.634-2.158-.266-.347A7.955 7.955 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/>
          <circle cx="8" cy="12" r="1.5"/>
          <circle cx="12" cy="12" r="1.5"/>
          <circle cx="16" cy="12" r="1.5"/>
        </svg>
      </button>
      <a href="${getWhatsAppLink('')}" target="_blank" rel="noopener" class="floating-btn whatsapp" title="Chat on WhatsApp">
        <i class="fab fa-whatsapp" style="font-size:28px;"></i>
      </a>
    `;
    document.body.appendChild(container);
  }

  document.querySelectorAll('[data-live-chat-open]').forEach((trigger) => {
    if (trigger.dataset.liveChatBound) return;
    trigger.dataset.liveChatBound = '1';
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      openChat();
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLiveChat);
} else {
  initLiveChat();
}

// Homepage hero flyer carousel
function initHeroFlyers() {
  const root = document.querySelector('[data-hero-flyers]');
  if (!root) return;

  const slides = [...root.querySelectorAll('.hero-flyers__slide')];
  const dots = [...document.querySelectorAll('[data-hero-flyer-dot]')];
  if (!slides.length) return;

  let index = 0;
  let timer;

  function show(i) {
    index = (i + slides.length) % slides.length;
    slides.forEach((slide, idx) => slide.classList.toggle('is-active', idx === index));
    dots.forEach((dot, idx) => dot.classList.toggle('is-active', idx === index));
  }

  function next() {
    show(index + 1);
  }

  function startAuto() {
    window.clearInterval(timer);
    timer = window.setInterval(next, 4500);
  }

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      show(Number(dot.getAttribute('data-hero-flyer-dot')));
      startAuto();
    });
  });

  show(0);
  startAuto();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initHeroFlyers);
} else {
  initHeroFlyers();
}

// Copyright year — updates automatically
document.querySelectorAll('.copyright-year').forEach((el) => {
  el.textContent = String(new Date().getFullYear());
});

// Broken image fallback
(function ensureAllImages() {
  const imgs = document.querySelectorAll('img');
  imgs.forEach((img) => {
    const setFallback = () => {
      if (img.dataset.fallbackApplied) return;
      img.dataset.fallbackApplied = '1';
      img.onerror = null;
      img.style.objectFit = 'cover';
      img.style.background = 'var(--navy)';
    };
    img.addEventListener('error', setFallback);
    if (img.complete && img.naturalWidth === 0) setFallback();
  });
})();
