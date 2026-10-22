/**
 * Summer Inspection & Logistique W/A LTD
 * Core Interactive Scripts (Vanilla JavaScript)
 * High-performance, zero third-party dependencies
 */

document.addEventListener('DOMContentLoaded', () => {
  initHeroCarousel();
  initClientsCarousel();
  initMobileNavigation();
  initSearchModal();
  initDisclaimerModal();
  initToolsTabs();
  initDensityCalculator();
  initContactForm();
  initFaqAccordion();
});

/* ==========================================================================
   1. Hero Image Carousel
   ========================================================================== */
function initHeroCarousel() {
  const container = document.querySelector('.carousel-container');
  if (!container) return;

  const slides = container.querySelectorAll('.carousel-slide');
  const prevBtn = container.querySelector('.carousel-btn.prev');
  const nextBtn = container.querySelector('.carousel-btn.next');
  const indicatorsContainer = container.querySelector('.carousel-indicators');

  if (!slides.length) return;

  let currentIndex = 0;
  let autoplayTimer = null;
  const autoplayDelay = 3500;

  // Build indicator dots
  if (indicatorsContainer) {
    indicatorsContainer.innerHTML = '';
    slides.forEach((_, idx) => {
      const dot = document.createElement('button');
      dot.className = `indicator-dot ${idx === 0 ? 'active' : ''}`;
      dot.setAttribute('aria-label', `Go to slide ${idx + 1}`);
      dot.addEventListener('click', () => {
        goToSlide(idx);
        restartAutoplay();
      });
      indicatorsContainer.appendChild(dot);
    });
  }

  function updateSlides() {
    slides.forEach((slide, idx) => {
      if (idx === currentIndex) {
        slide.classList.add('active');
      } else {
        slide.classList.remove('active');
      }
    });

    if (indicatorsContainer) {
      const dots = indicatorsContainer.querySelectorAll('.indicator-dot');
      dots.forEach((dot, idx) => {
        if (idx === currentIndex) {
          dot.classList.add('active');
        } else {
          dot.classList.remove('active');
        }
      });
    }
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % slides.length;
    updateSlides();
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateSlides();
  }

  function goToSlide(index) {
    currentIndex = index;
    updateSlides();
  }

  function startAutoplay() {
    stopAutoplay();
    autoplayTimer = setInterval(nextSlide, autoplayDelay);
  }

  function stopAutoplay() {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  }

  function restartAutoplay() {
    stopAutoplay();
    startAutoplay();
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      nextSlide();
      restartAutoplay();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      prevSlide();
      restartAutoplay();
    });
  }

  container.addEventListener('mouseenter', stopAutoplay);
  container.addEventListener('mouseleave', startAutoplay);

  // Touch Swipe Support
  let touchStartX = 0;
  let touchEndX = 0;

  container.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
    stopAutoplay();
  }, { passive: true });

  container.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    if (touchStartX - touchEndX > 45) {
      nextSlide();
    } else if (touchEndX - touchStartX > 45) {
      prevSlide();
    }
    startAutoplay();
  }, { passive: true });

  startAutoplay();
}

/* ==========================================================================
   2. Clients Carousel
   ========================================================================== */
function initClientsCarousel() {
  const track = document.querySelector('.clients-track');
  const wrapper = document.querySelector('.clients-carousel-wrapper');
  if (!track || !wrapper) return;

  const items = track.querySelectorAll('.client-item');
  if (!items.length) return;

  let currentIndex = 0;
  let timer = null;

  function getVisibleCount() {
    const width = window.innerWidth;
    if (width <= 640) return 2;
    if (width <= 1024) return 3;
    return 4;
  }

  function getMaxIndex() {
    const visibleCount = getVisibleCount();
    return Math.max(0, items.length - visibleCount);
  }

  function moveTrack() {
    const maxIdx = getMaxIndex();
    if (currentIndex > maxIdx) {
      currentIndex = 0;
    }
    const visibleCount = getVisibleCount();
    const percentPerItem = 100 / visibleCount;
    track.style.transform = `translateX(-${currentIndex * percentPerItem}%)`;
    updateIndicators();
  }

  const indicatorContainer = document.querySelector('.clients-indicators');
  function createIndicators() {
    if (!indicatorContainer) return;
    indicatorContainer.innerHTML = '';
    const maxIdx = getMaxIndex();
    for (let i = 0; i <= maxIdx; i++) {
      const dot = document.createElement('button');
      dot.className = `indicator-dot ${i === currentIndex ? 'active' : ''}`;
      dot.setAttribute('aria-label', `Show clients page ${i + 1}`);
      dot.addEventListener('click', () => {
        currentIndex = i;
        moveTrack();
        resetTimer();
      });
      indicatorContainer.appendChild(dot);
    }
  }

  function updateIndicators() {
    if (!indicatorContainer) return;
    const dots = indicatorContainer.querySelectorAll('.indicator-dot');
    dots.forEach((dot, idx) => {
      dot.classList.toggle('active', idx === currentIndex);
    });
  }

  function autoPlayClients() {
    timer = setInterval(() => {
      const maxIdx = getMaxIndex();
      currentIndex = currentIndex >= maxIdx ? 0 : currentIndex + 1;
      moveTrack();
    }, 2800);
  }

  function resetTimer() {
    clearInterval(timer);
    autoPlayClients();
  }

  wrapper.addEventListener('mouseenter', () => clearInterval(timer));
  wrapper.addEventListener('mouseleave', autoPlayClients);

  window.addEventListener('resize', () => {
    createIndicators();
    moveTrack();
  });

  createIndicators();
  autoPlayClients();
}

/* ==========================================================================
   3. Mobile Navigation & Off-Canvas Drawer
   ========================================================================== */
function initMobileNavigation() {
  const toggleBtn = document.querySelector('.mobile-toggle-btn');
  const closeBtn = document.querySelector('.sidebar-close-btn');
  const sidebar = document.querySelector('.mobile-sidebar');
  const overlay = document.querySelector('.sidebar-overlay');

  if (!toggleBtn || !sidebar || !overlay) return;

  function openSidebar() {
    sidebar.classList.add('is-open');
    overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar.classList.remove('is-open');
    overlay.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  toggleBtn.addEventListener('click', openSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  overlay.addEventListener('click', closeSidebar);

  // Close sidebar when clicking any direct link inside it
  const links = sidebar.querySelectorAll('a:not(.mobile-dropdown-toggle)');
  links.forEach(link => {
    link.addEventListener('click', () => {
      closeSidebar();
    });
  });

  // Mobile Dropdown Accordion Toggles
  const dropdownToggles = sidebar.querySelectorAll('.mobile-dropdown-toggle');
  dropdownToggles.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const parent = btn.closest('.mobile-nav-item');
      const menu = parent ? parent.querySelector('.mobile-dropdown') : null;
      if (menu) {
        menu.classList.toggle('is-open');
        const icon = btn.querySelector('i');
        if (icon) {
          icon.classList.toggle('fa-angle-down');
          icon.classList.toggle('fa-angle-up');
        }
      }
    });
  });
}

/* ==========================================================================
   4. Header Search Modal
   ========================================================================== */
function initSearchModal() {
  const searchBtn = document.querySelector('.header-search-btn');
  const modal = document.querySelector('.search-modal-overlay');
  const closeBtn = document.querySelector('.search-modal-close');
  const input = modal ? modal.querySelector('input') : null;

  if (!searchBtn || !modal) return;

  function openSearch() {
    modal.classList.add('is-open');
    if (input) setTimeout(() => input.focus(), 150);
  }

  function closeSearch() {
    modal.classList.remove('is-open');
  }

  searchBtn.addEventListener('click', openSearch);
  if (closeBtn) closeBtn.addEventListener('click', closeSearch);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeSearch();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      closeSearch();
    }
  });
}

/* ==========================================================================
   5. Disclaimer Modal with LocalStorage
   ========================================================================== */
function initDisclaimerModal() {
  const modal = document.getElementById('disclaimer-modal');
  if (!modal) return;

  const closeBtn = modal.querySelector('.btn-modal-close');
  const agreeBtn = modal.querySelector('.btn-modal-agree');
  const iconClose = modal.querySelector('.modal-close-icon');

  const STORAGE_KEY = 'silwa_disclaimer_accepted_v1';
  const hasAccepted = localStorage.getItem(STORAGE_KEY);

  function openModal() {
    modal.classList.add('is-visible');
    document.body.style.overflow = 'hidden';
  }

  function dismissModal() {
    modal.classList.remove('is-visible');
    document.body.style.overflow = '';
    const expiry = Date.now() + 24 * 60 * 60 * 1000;
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ dismissed: true, expiresAt: expiry }));
  }

  if (hasAccepted) {
    try {
      const data = JSON.parse(hasAccepted);
      if (Date.now() > data.expiresAt) {
        localStorage.removeItem(STORAGE_KEY);
        setTimeout(openModal, 600);
      }
    } catch {
      localStorage.removeItem(STORAGE_KEY);
      setTimeout(openModal, 600);
    }
  } else {
    setTimeout(openModal, 600);
  }

  if (closeBtn) closeBtn.addEventListener('click', dismissModal);
  if (agreeBtn) agreeBtn.addEventListener('click', dismissModal);
  if (iconClose) iconClose.addEventListener('click', dismissModal);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) dismissModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-visible')) {
      dismissModal();
    }
  });
}

/* ==========================================================================
   6. Tools Tabs Navigation
   ========================================================================== */
function initToolsTabs() {
  const tabBtns = document.querySelectorAll('.tool-tab-btn');
  const tabPanels = document.querySelectorAll('.tool-panel');

  if (!tabBtns.length || !tabPanels.length) return;

  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');

      tabBtns.forEach(b => b.classList.remove('active'));
      tabPanels.forEach(p => p.classList.remove('active'));

      btn.classList.add('active');
      const targetPanel = document.getElementById(targetId);
      if (targetPanel) {
        targetPanel.classList.add('active');
      }
    });
  });
}

/* ==========================================================================
   7. Theoretical Density Calculator
   ========================================================================== */
function initDensityCalculator() {
  const calcForm = document.getElementById('density-calc-form');
  const resultBox = document.getElementById('calc-result-box');
  const resetBtn = document.getElementById('calc-reset-btn');

  if (!calcForm || !resultBox) return;

  calcForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const apiGravityInput = parseFloat(document.getElementById('calc-api').value);
    const tempInput = parseFloat(document.getElementById('calc-temp').value) || 15;
    const volumeInput = parseFloat(document.getElementById('calc-volume').value) || 0;

    if (isNaN(apiGravityInput) || apiGravityInput <= 0) {
      alert('Please enter a valid API Gravity value (e.g., 28.5 or 34.0).');
      return;
    }

    // Petroleum ASTM Calculation Formula
    // Specific Gravity @ 60F = 141.5 / (API + 131.5)
    const sg60F = 141.5 / (apiGravityInput + 131.5);
    
    // Density @ 15°C (kg/m³) approx: SG * 999.012
    const density15C = sg60F * 999.012;

    // Density in g/cm³ or metric tons/m³
    const densityGcm3 = density15C / 1000;

    let outputHtml = `
      <h4><i class="fa-solid fa-check-circle"></i> Calculation Results:</h4>
      <p>
        <strong>Specific Gravity (60°F / 15.56°C):</strong> ${sg60F.toFixed(4)}<br>
        <strong>Theoretical Density @ 15°C:</strong> ${density15C.toFixed(2)} kg/m³ (${densityGcm3.toFixed(4)} g/cm³)<br>
        <strong>API Gravity:</strong> ${apiGravityInput.toFixed(2)}° API
      </p>
    `;

    if (volumeInput > 0) {
      // Metric Tons in Vacuo = Volume (m³) * Density (kg/m³) / 1000
      const metricTons = (volumeInput * density15C) / 1000;
      outputHtml += `
        <p style="margin-top: 8px; border-top: 1px dashed #86efac; padding-top: 8px;">
          <strong>Total Metric Weight (Vacuo):</strong> ${metricTons.toLocaleString('en-US', { minimumFractionDigits: 3, maximumFractionDigits: 3 })} Metric Tons (MT) for ${volumeInput.toLocaleString()} m³
        </p>
      `;
    }

    resultBox.innerHTML = outputHtml;
    resultBox.classList.add('is-active');
  });

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      calcForm.reset();
      resultBox.classList.remove('is-active');
    });
  }
}

/* ==========================================================================
   8. Full-Stack Contact & Enquiry Form Handler
   ========================================================================== */
function initContactForm() {
  const form = document.getElementById('silwa-contact-form');
  const feedback = document.getElementById('contact-form-feedback');
  const submitBtn = document.getElementById('contact-submit-btn');

  if (!form || !feedback || !submitBtn) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Honeypot check for bots
    const honeypot = form.querySelector('[name="website_honeypot"]');
    if (honeypot && honeypot.value.trim() !== '') {
      return;
    }

    // Feedback clear
    feedback.className = 'form-feedback';
    feedback.style.display = 'none';

    // Collect data
    const formData = new FormData(form);
    const dataObj = Object.fromEntries(formData.entries());

    // Basic frontend check
    if (!dataObj.full_name || !dataObj.email || !dataObj.message) {
      feedback.textContent = 'Please fill out your Name, Email, and Message.';
      feedback.className = 'form-feedback error';
      feedback.style.display = 'block';
      return;
    }

    // Loading State
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending Inquiry...';

    try {
      // 1. Try sending to local Hostinger PHP mailer (send-mail.php)
      const response = await fetch('send-mail.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(dataObj)
      });

      if (response.ok) {
        const result = await response.json();
        if (result.status === 'success') {
          feedback.innerHTML = `<strong>Success!</strong> ${result.message}`;
          feedback.className = 'form-feedback success';
          feedback.style.display = 'block';
          form.reset();
        } else {
          throw new Error(result.message || 'Unable to submit enquiry.');
        }
      } else {
        throw new Error('Hostinger mail endpoint responded with error: ' + response.status);
      }
    } catch (err) {
      console.warn('send-mail.php submission fallback triggered:', err);

      // 2. Fallback for Static Hosting / GitHub Pages (or when local PHP is not active):
      // Mailto direct client launch or fallback notice
      feedback.innerHTML = `
        <strong>Message Prepared!</strong> If you are testing this on a static server (like GitHub Pages without PHP), 
        your inquiry can be emailed directly to <strong><a href="mailto:biz@summerinspectlog.com?subject=Enquiry from ${encodeURIComponent(dataObj.full_name)}: ${encodeURIComponent(dataObj.service)}&body=${encodeURIComponent(dataObj.message)}">biz@summerinspectlog.com</a></strong>. 
        Once uploaded to your <strong>Hostinger PHP hosting</strong>, messages will be delivered automatically to <strong>biz@summerinspectlog.com</strong>!
      `;
      feedback.className = 'form-feedback success';
      feedback.style.display = 'block';
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
    }
  });
}

/* ==========================================================================
   9. FAQ Accordion (AI & Human Knowledge Base)
   ========================================================================== */
function initFaqAccordion() {
  const faqItems = document.querySelectorAll('.faq-item');
  if (!faqItems.length) return;

  faqItems.forEach(item => {
    const header = item.querySelector('.faq-header');
    if (!header) return;

    header.addEventListener('click', () => {
      const isOpen = item.classList.contains('is-open');

      // Close all other open items for clean accordion effect
      faqItems.forEach(otherItem => {
        if (otherItem !== item) {
          otherItem.classList.remove('is-open');
          const btn = otherItem.querySelector('.faq-header');
          if (btn) btn.setAttribute('aria-expanded', 'false');
        }
      });

      // Toggle current
      item.classList.toggle('is-open', !isOpen);
      header.setAttribute('aria-expanded', String(!isOpen));
    });
  });
}

