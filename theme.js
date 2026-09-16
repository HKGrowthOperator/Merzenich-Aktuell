(() => {
  const STORAGE_KEY = 'merzenich-theme';
  const LIGHT = 'light';
  const DARK = 'dark';

  function readTheme() {
    try {
      return localStorage.getItem(STORAGE_KEY) === DARK ? DARK : LIGHT;
    } catch {
      return LIGHT;
    }
  }

  function applyTheme(theme, persist = true) {
    const next = theme === DARK ? DARK : LIGHT;
    document.documentElement.dataset.theme = next;
    document.documentElement.style.colorScheme = next;

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', next === DARK ? '#0b0b0b' : '#ffffff');

    document.querySelectorAll('[data-theme-option]').forEach((button) => {
      const active = button.dataset.themeOption === next;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', String(active));
    });

    if (persist) {
      try {
        localStorage.setItem(STORAGE_KEY, next);
      } catch {
        // Storage can be unavailable in private/restricted browsing. Theme still works for this page view.
      }
    }
  }

  function iconSettings() {
    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 8.25A3.75 3.75 0 1 0 12 15.75 3.75 3.75 0 0 0 12 8.25Zm8.25 3.75a6.7 6.7 0 0 0-.09-1.08l2.02-1.57-2-3.46-2.52 1a8.2 8.2 0 0 0-1.87-1.08L15.4 3h-4l-.39 2.81a8.2 8.2 0 0 0-1.87 1.08l-2.52-1-2 3.46 2.02 1.57A6.7 6.7 0 0 0 6.55 12c0 .37.03.73.09 1.08l-2.02 1.57 2 3.46 2.52-1c.57.45 1.2.81 1.87 1.08L11.4 21h4l.39-2.81a8.2 8.2 0 0 0 1.87-1.08l2.52 1 2-3.46-2.02-1.57c.06-.35.09-.71.09-1.08Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>';
  }

  function iconSun() {
    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 2.5v2M12 19.5v2M4.5 12h-2M21.5 12h-2M5.28 5.28 6.7 6.7M17.3 17.3l1.42 1.42M18.72 5.28 17.3 6.7M6.7 17.3l-1.42 1.42" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
  }

  function iconMoon() {
    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19.25 15.5A8.2 8.2 0 0 1 8.5 4.75 8.25 8.25 0 1 0 19.25 15.5Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
  }

  function initThemeControls() {
    const headerMain = document.querySelector('.header-main');
    const headerStatus = document.querySelector('.header-status');
    const mobileMenuToggle = document.querySelector('#mobileMenuToggle');
    if (!headerMain || !headerStatus || !mobileMenuToggle) return;

    const desktopButton = document.createElement('button');
    desktopButton.type = 'button';
    desktopButton.className = 'settings-toggle settings-toggle-desktop';
    desktopButton.setAttribute('aria-label', 'Darstellung einstellen');
    desktopButton.setAttribute('aria-controls', 'settingsDrawer');
    desktopButton.setAttribute('aria-expanded', 'false');
    desktopButton.title = 'Darstellung';
    desktopButton.innerHTML = `${iconSettings()}<span class="settings-toggle-label">Darstellung</span>`;
    headerStatus.append(desktopButton);

    const mobileButton = document.createElement('button');
    mobileButton.type = 'button';
    mobileButton.className = 'settings-toggle settings-toggle-mobile';
    mobileButton.setAttribute('aria-label', 'Darstellung einstellen');
    mobileButton.setAttribute('aria-controls', 'settingsDrawer');
    mobileButton.setAttribute('aria-expanded', 'false');
    mobileButton.title = 'Darstellung';
    mobileButton.innerHTML = iconSettings();
    headerMain.insertBefore(mobileButton, mobileMenuToggle);

    const overlay = document.createElement('div');
    overlay.className = 'settings-overlay';
    overlay.id = 'settingsOverlay';
    overlay.hidden = true;

    const drawer = document.createElement('aside');
    drawer.className = 'settings-drawer';
    drawer.id = 'settingsDrawer';
    drawer.setAttribute('role', 'dialog');
    drawer.setAttribute('aria-modal', 'true');
    drawer.setAttribute('aria-labelledby', 'settingsTitle');
    drawer.setAttribute('aria-hidden', 'true');
    drawer.innerHTML = `
      <div class="settings-drawer-head">
        <h2 id="settingsTitle">Einstellungen</h2>
        <button type="button" class="settings-close" aria-label="Einstellungen schließen">×</button>
      </div>
      <div class="settings-section">
        <div class="settings-section-title">Darstellung</div>
        <div class="theme-segment" role="group" aria-label="Farbschema">
          <button type="button" data-theme-option="light" aria-pressed="false">${iconSun()}<span>Hell</span></button>
          <button type="button" data-theme-option="dark" aria-pressed="false">${iconMoon()}<span>Dunkel</span></button>
        </div>
        <p class="settings-help">Hell ist die Standardansicht. Ihre Auswahl bleibt auf diesem Gerät gespeichert.</p>
      </div>`;

    document.body.append(overlay, drawer);

    const closeButton = drawer.querySelector('.settings-close');
    const themeButtons = drawer.querySelectorAll('[data-theme-option]');
    let lastTrigger = null;

    function setExpanded(expanded) {
      desktopButton.setAttribute('aria-expanded', String(expanded));
      mobileButton.setAttribute('aria-expanded', String(expanded));
    }

    function openDrawer(trigger) {
      lastTrigger = trigger || document.activeElement;
      overlay.hidden = false;
      requestAnimationFrame(() => {
        overlay.classList.add('is-open');
        drawer.classList.add('is-open');
      });
      drawer.setAttribute('aria-hidden', 'false');
      document.body.classList.add('settings-open');
      setExpanded(true);
      closeButton.focus();
    }

    function closeDrawer() {
      overlay.classList.remove('is-open');
      drawer.classList.remove('is-open');
      drawer.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('settings-open');
      setExpanded(false);
      window.setTimeout(() => {
        overlay.hidden = true;
      }, 200);
      if (lastTrigger && typeof lastTrigger.focus === 'function') lastTrigger.focus();
    }

    desktopButton.addEventListener('click', () => openDrawer(desktopButton));
    mobileButton.addEventListener('click', () => openDrawer(mobileButton));
    closeButton.addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);

    themeButtons.forEach((button) => {
      button.addEventListener('click', () => applyTheme(button.dataset.themeOption));
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && drawer.classList.contains('is-open')) closeDrawer();
    });

    applyTheme(readTheme(), false);
  }

  applyTheme(readTheme(), false);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeControls, { once: true });
  } else {
    initThemeControls();
  }
})();
