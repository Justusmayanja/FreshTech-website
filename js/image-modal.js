// Image lightbox for portfolio items
(function initImageModal() {
  const modal = document.createElement('div');
  modal.className = 'image-modal';
  modal.innerHTML = `
    <span class="image-modal-close">&times;</span>
    <img class="image-modal-content" alt="">
  `;
  document.body.appendChild(modal);

  const modalImg = modal.querySelector('.image-modal-content');
  const closeBtn = modal.querySelector('.image-modal-close');

  function openModal(imgSrc, imgAlt) {
    modal.classList.add('active');
    modalImg.src = imgSrc;
    modalImg.alt = imgAlt || 'Portfolio image';
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }

  document.addEventListener('click', (e) => {
    const img = e.target.closest('.portfolio-item img');
    if (img) {
      e.preventDefault();
      openModal(img.src, img.alt);
    }
  });

  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
  });
})();
