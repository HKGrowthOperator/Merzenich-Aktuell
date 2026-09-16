(() => {
  const SCROLL_THRESHOLD = 32;

  function initHeaderScroll() {
    const header = document.querySelector('#siteHeader');
    const headerMain = header?.querySelector('.header-main');
    const brand = header?.querySelector('.brand');

    if (!header || !headerMain || !brand || headerMain.querySelector('.header-brand-slot')) return;

    const brandSlot = document.createElement('div');
    brandSlot.className = 'header-brand-slot';

    brand.parentNode.insertBefore(brandSlot, brand);
    brand.classList.add('brand--masthead');
    brandSlot.appendChild(brand);

    const compactBrand = brand.cloneNode(true);
    compactBrand.classList.remove('brand--masthead');
    compactBrand.classList.add('brand--compact');
    compactBrand.setAttribute('aria-label', 'Merzenich Aktuell Startseite');
    compactBrand.setAttribute('aria-hidden', 'true');
    compactBrand.tabIndex = -1;
    brandSlot.appendChild(compactBrand);

    let condensed = null;
    let ticking = false;

    function setAccessibilityState(nextCondensed) {
      brand.setAttribute('aria-hidden', String(nextCondensed));
      brand.tabIndex = nextCondensed ? -1 : 0;
      compactBrand.setAttribute('aria-hidden', String(!nextCondensed));
      compactBrand.tabIndex = nextCondensed ? 0 : -1;
    }

    function updateHeader() {
      ticking = false;
      const nextCondensed = window.scrollY > SCROLL_THRESHOLD;
      if (nextCondensed === condensed) return;

      condensed = nextCondensed;
      header.classList.toggle('is-condensed', nextCondensed);
      setAccessibilityState(nextCondensed);
    }

    function requestUpdate() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(updateHeader);
    }

    updateHeader();
    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('pageshow', requestUpdate);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeaderScroll, { once: true });
  } else {
    initHeaderScroll();
  }
})();
