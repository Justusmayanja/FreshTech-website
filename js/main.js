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

// Portfolio filters - Works on page load and dynamically
function initPortfolioFilters() {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const portfolioItems = document.querySelectorAll('.portfolio-item');
  
  // Only run if we found elements
  if (filterButtons.length === 0 || portfolioItems.length === 0) {
    console.warn('Portfolio filters: buttons or items not found');
    return;
  }
  
  console.log('Portfolio filters initialized with', filterButtons.length, 'buttons and', portfolioItems.length, 'items');
  
  filterButtons.forEach((btn) => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      // Remove active class from all buttons
      filterButtons.forEach((b) => b.classList.remove('active'));
      // Add active class to clicked button
      btn.classList.add('active');
      
      // Get the filter value
      const filterValue = btn.dataset.filter;
      console.log('Filtering by:', filterValue);
      
      // Filter portfolio items
      let visibleCount = 0;
      portfolioItems.forEach((item) => {
        const itemCategory = item.getAttribute('data-category');
        if (!itemCategory) {
          console.warn('Item missing data-category:', item);
          return;
        }
        
        const categories = itemCategory.split(' ');
        const shouldShow = filterValue === 'all' || categories.includes(filterValue);
        
        console.log('Item categories:', categories, 'Filter:', filterValue, 'Show:', shouldShow);
        
        if (shouldShow) {
          item.classList.remove('hidden');
          item.style.display = '';
          visibleCount++;
        } else {
          item.classList.add('hidden');
          item.style.display = 'none';
        }
      });
      console.log('Showing', visibleCount, 'items');
    });
  });
}

// Run on DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPortfolioFilters);
} else {
  initPortfolioFilters();
}

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

// Contact Form Handler
const contactForm = document.getElementById('contact-form');
if (contactForm) {
  contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = contactForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    
    const formData = new FormData(contactForm);
    
    try {
      const response = await fetch('/contact-handler.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Show success message
        contactForm.innerHTML = `
          <div style="padding: 40px; text-align: center; background: #d4edda; color: #155724; border-radius: 8px;">
            <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 16px;"></i>
            <h3 style="margin: 0 0 8px 0;">Message Sent Successfully!</h3>
            <p style="margin: 0;">${result.message}</p>
          </div>
        `;
      } else {
        // Show error message
        alert(result.message || 'Failed to send message. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    } catch (error) {
      console.error('Error:', error);
      alert('An error occurred. Please try again or contact us directly.');
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });
}
     m o d a l . i n n e r H T M L   =   ` 
         < s p a n   c l a s s = " i m a g e - m o d a l - c l o s e " > & t i m e s ; < / s p a n > 
         < i m g   c l a s s = " i m a g e - m o d a l - c o n t e n t "   a l t = " P r o d u c t   i m a g e " > 
     ` ; 
     d o c u m e n t . b o d y . a p p e n d C h i l d ( m o d a l ) ; 
 
     c o n s t   m o d a l I m g   =   m o d a l . q u e r y S e l e c t o r ( ' . i m a g e - m o d a l - c o n t e n t ' ) ; 
     c o n s t   c l o s e B t n   =   m o d a l . q u e r y S e l e c t o r ( ' . i m a g e - m o d a l - c l o s e ' ) ; 
 
     / /   F u n c t i o n   t o   o p e n   m o d a l 
     f u n c t i o n   o p e n M o d a l ( i m g S r c ,   i m g A l t )   { 
         m o d a l . c l a s s L i s t . a d d ( ' a c t i v e ' ) ; 
         m o d a l I m g . s r c   =   i m g S r c ; 
         m o d a l I m g . a l t   =   i m g A l t   | |   ' P r o d u c t   i m a g e ' ; 
         d o c u m e n t . b o d y . s t y l e . o v e r f l o w   =   ' h i d d e n ' ; 
     } 
 
     / /   F u n c t i o n   t o   c l o s e   m o d a l 
     f u n c t i o n   c l o s e M o d a l ( )   { 
         m o d a l . c l a s s L i s t . r e m o v e ( ' a c t i v e ' ) ; 
         d o c u m e n t . b o d y . s t y l e . o v e r f l o w   =   ' ' ; 
     } 
 
     / /   A d d   c l i c k   h a n d l e r s   t o   a l l   p r o d u c t   i m a g e s 
     d o c u m e n t . a d d E v e n t L i s t e n e r ( ' c l i c k ' ,   f u n c t i o n ( e )   { 
         i f   ( e . t a r g e t . m a t c h e s ( ' . p r o d u c t   i m g ' ) )   { 
             e . p r e v e n t D e f a u l t ( ) ; 
             o p e n M o d a l ( e . t a r g e t . s r c ,   e . t a r g e t . a l t ) ; 
         } 
     } ) ; 
 
     / /   C l o s e   m o d a l   o n   c l o s e   b u t t o n   c l i c k 
     c l o s e B t n . a d d E v e n t L i s t e n e r ( ' c l i c k ' ,   c l o s e M o d a l ) ; 
 
     / /   C l o s e   m o d a l   w h e n   c l i c k i n g   o u t s i d e   t h e   i m a g e 
     m o d a l . a d d E v e n t L i s t e n e r ( ' c l i c k ' ,   f u n c t i o n ( e )   { 
         i f   ( e . t a r g e t   = = =   m o d a l )   { 
             c l o s e M o d a l ( ) ; 
         } 
     } ) ; 
 
     / /   C l o s e   m o d a l   o n   E s c a p e   k e y 
     d o c u m e n t . a d d E v e n t L i s t e n e r ( ' k e y d o w n ' ,   f u n c t i o n ( e )   { 
         i f   ( e . k e y   = = =   ' E s c a p e '   & &   m o d a l . c l a s s L i s t . c o n t a i n s ( ' a c t i v e ' ) )   { 
             c l o s e M o d a l ( ) ; 
         } 
     } ) ; 
 } ) ( ) ; 
 
 