



document.addEventListener('DOMContentLoaded', () => {

  
  
  

  const hamburgerBtn    = document.getElementById('hamburgerBtn');
  const categoryBar     = document.getElementById('categoryBar');
  const categoryOverlay = document.getElementById('categoryOverlay');
  const closeBtn        = document.getElementById('categoryBarClose');

  
  if (hamburgerBtn && categoryBar && categoryOverlay && closeBtn) {
    const categoryItems = document.querySelectorAll('.category-bar-item.has-children');

    function closeSubmenus() {
      categoryItems.forEach(item => {
        item.classList.remove('open');
        item.classList.remove('desktop-open');
        const link = item.querySelector('.category-bar-link');
        if (link) link.setAttribute('aria-expanded', 'false');
      });
    }

    function openMenu() {
      categoryBar.classList.add('open');
      hamburgerBtn.classList.add('open');
      categoryOverlay.classList.add('show');
      categoryOverlay.removeAttribute('aria-hidden');
      hamburgerBtn.setAttribute('aria-expanded', 'true');
      hamburgerBtn.setAttribute('aria-label', 'Fermer le menu catégories');
      document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
      categoryBar.classList.remove('open');
      hamburgerBtn.classList.remove('open');
      categoryOverlay.classList.remove('show');
      categoryOverlay.setAttribute('aria-hidden', 'true');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
      hamburgerBtn.setAttribute('aria-label', 'Ouvrir le menu catégories');
      document.body.style.overflow = '';
      closeSubmenus();
    }

    hamburgerBtn.addEventListener('click', () => {
      categoryBar.classList.contains('open') ? closeMenu() : openMenu();
    });

    categoryOverlay.addEventListener('click', closeMenu);
    closeBtn.addEventListener('click', closeMenu);

    
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && categoryBar.classList.contains('open')) {
        closeMenu();
        hamburgerBtn.focus(); 
      }
    });

    
    categoryItems.forEach(item => {
      const link = item.querySelector('.category-bar-link');
      if (!link) return;

      link.addEventListener('click', e => {
        e.preventDefault();

        const isDesktop = window.innerWidth > 768;
        const isOpen = isDesktop
          ? item.classList.contains('desktop-open')
          : item.classList.contains('open');

        closeSubmenus();

        if (!isOpen) {
          if (isDesktop) {
            item.classList.add('desktop-open');
          } else {
            item.classList.add('open');
          }
          link.setAttribute('aria-expanded', 'true');
        }
      });
    });

    document.addEventListener('click', e => {
      if (window.innerWidth <= 768) return;
      if (!categoryBar.contains(e.target) && !hamburgerBtn.contains(e.target)) {
        closeSubmenus();
      }
    });

    
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        if (window.innerWidth > 768) {
          categoryBar.classList.remove('open');
          hamburgerBtn.classList.remove('open');
          categoryOverlay.classList.remove('show');
          categoryOverlay.setAttribute('aria-hidden', 'true');
          hamburgerBtn.setAttribute('aria-expanded', 'false');
          hamburgerBtn.setAttribute('aria-label', 'Ouvrir le menu catégories');
          document.body.style.overflow = '';
          closeSubmenus();
        }
      }, 150);
    });

  } 

  
  
  

  document.querySelectorAll('.filter-form input[type="radio"]')
    .forEach(input => {
      input.addEventListener('change', () => {
        input.closest('form')?.submit();
      });
    });

  document.querySelectorAll('.filter-form input[type="checkbox"]')
    .forEach(input => {
      input.addEventListener('change', () => {
        input.closest('form')?.submit();
      });
    });

  
  
  

  const paymentRadios = document.querySelectorAll('input[name="methode"]');

  if (paymentRadios.length > 0) {
    function updatePaymentHighlight(selected) {
      
      paymentRadios.forEach(radio => {
        const label = radio.closest('label') || radio.closest('.payment-option');
        if (label) {
          label.style.borderColor = '';
          label.style.background  = '';
        }
      });

      
      const label = selected.closest('label') || selected.closest('.payment-option');
      if (label) {
        label.style.borderColor = 'var(--color-primary)';
        label.style.background  = 'var(--color-primary-xlight)';
      }
    }

    paymentRadios.forEach(radio => {
      radio.addEventListener('change', function () {
        updatePaymentHighlight(this);
      });

      
      if (radio.checked) updatePaymentHighlight(radio);
    });
  }

  
  
  

  document.querySelectorAll('.alert[data-auto-hide]').forEach(alertEl => {
    
    
    const delay = parseInt(alertEl.dataset.autoHide) || 5000;

    setTimeout(() => {
      alertEl.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      alertEl.style.opacity    = '0';
      alertEl.style.transform  = 'translateY(-8px)';
      setTimeout(() => alertEl.remove(), 500);
    }, delay);
  });

  
  
  

  const mainImage = document.getElementById('main-image');

  if (mainImage) {
    document.querySelectorAll('.thumb-img').forEach(thumb => {
      thumb.addEventListener('click', function () {
        const newSrc = this.dataset.src;
        if (!newSrc) return;

        
        mainImage.style.opacity = '0';
        setTimeout(() => {
          mainImage.src         = newSrc;
          mainImage.style.opacity = '1';
        }, 200);

        
        document.querySelectorAll('.thumb-img').forEach(t => {
          t.classList.remove('active');
          t.setAttribute('aria-selected', 'false');
        });
        this.classList.add('active');
        this.setAttribute('aria-selected', 'true');
      });
    });

    
    mainImage.style.transition = 'opacity 0.2s ease';
  }

  
  
  

  const topBtn = document.createElement('button');
  topBtn.innerHTML  = '<i class="fas fa-arrow-up" aria-hidden="true"></i>';
  topBtn.title      = 'Retour en haut de page';
  topBtn.className  = 'back-to-top'; 
  topBtn.setAttribute('aria-label', 'Retour en haut de page');
  topBtn.setAttribute('aria-hidden', 'true'); 
  document.body.appendChild(topBtn);

  let ticking = false; 
  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(() => {
        const show = window.scrollY > 400;
        topBtn.classList.toggle('visible', show);
        topBtn.setAttribute('aria-hidden', show ? 'false' : 'true');
        ticking = false;
      });
      ticking = true;
    }
  });

  topBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    setTimeout(() => {
      const firstFocusable = document.querySelector(
        'a[href], button:not([disabled]), input, [tabindex="0"]'
      );
      firstFocusable?.focus();
    }, 500);
  });

  
  
  

  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;

        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
          img.classList.add('loaded'); 
        }
        observer.unobserve(img);
      });
    }, {
      rootMargin: '100px', 
      threshold: 0.01
    });

    document.querySelectorAll('img[data-src]')
      .forEach(img => imageObserver.observe(img));

  } else {
    
    document.querySelectorAll('img[data-src]').forEach(img => {
      img.src = img.dataset.src;
      img.removeAttribute('data-src');
    });
  }

  
  
  

  document.querySelectorAll('.qty-wrapper').forEach(wrapper => {
    const input   = wrapper.querySelector('.qty-input');
    const btnMinus = wrapper.querySelector('.qty-minus');
    const btnPlus  = wrapper.querySelector('.qty-plus');

    if (!input || !btnMinus || !btnPlus) return;

    btnMinus.addEventListener('click', () => {
      const min = parseInt(input.min) || 1;
      const val = parseInt(input.value) || 1;
      if (val > min) {
        input.value = val - 1;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });

    btnPlus.addEventListener('click', () => {
      const max = parseInt(input.max) || 99;
      const val = parseInt(input.value) || 1;
      if (val < max) {
        input.value = val + 1;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });

    
    input.addEventListener('change', () => {
      const min = parseInt(input.min) || 1;
      const max = parseInt(input.max) || 99;
      let val = parseInt(input.value) || min;
      val = Math.min(Math.max(val, min), max);
      input.value = val;
    });
  });

  
  
  

  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      const msg = el.dataset.confirm || 'Êtes-vous sûr de vouloir effectuer cette action ?';
      if (!confirm(msg)) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  });

});
