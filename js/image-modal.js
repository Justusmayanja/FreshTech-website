// Image Modal for Product Images
(function initImageModal() {
  // Create modal element
  const modal = document.createElement('div');
  modal.className = 'image-modal';
  modal.innerHTML = `
    <span class="image-modal-close">&times;</span>
    <img class="image-modal-content" alt="Product image">
  `;
  document.body.appendChild(modal);

  const modalImg = modal.querySelector('.image-modal-content');
  const closeBtn = modal.querySelector('.image-modal-close');

  // Function to open modal
  function openModal(imgSrc, imgAlt) {
    modal.classList.add('active');
    modalImg.src = imgSrc;
    modalImg.alt = imgAlt || 'Product image';
    document.body.style.overflow = 'hidden';
  }

  // Function to close modal
  function closeModal() {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }

  // Add click handlers to all product images
  document.addEventListener('click', function(e) {
    if (e.target.matches('.product img')) {
      e.preventDefault();
      openModal(e.target.src, e.target.alt);
    }
  });

  // Close modal on close button click
  closeBtn.addEventListener('click', closeModal);

  // Close modal when clicking outside the image
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      closeModal();
    }
  });

  // Close modal on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });
})();
