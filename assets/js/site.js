(() => {
  const yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  const copyBtn = document.getElementById('copyIbanBtn');
  const ibanText = document.getElementById('ibanText');
  if (copyBtn && ibanText) {
    copyBtn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText((ibanText.textContent || '').trim());
        const old = copyBtn.textContent;
        copyBtn.textContent = 'Copiato!';
        setTimeout(() => { copyBtn.textContent = old; }, 1500);
      } catch (e) {
        alert("Copia non disponibile: seleziona l'IBAN e copia manualmente.");
      }
    });
  }

  const COOKIE_KEY = 'cookieConsent';
  const cookieBanner = document.getElementById('cookieBanner');
  if (!cookieBanner) return;

  const btnAccept = document.getElementById('cookieAccept');
  const btnReject = document.getElementById('cookieReject');
  const btnPrefsToggle = document.getElementById('cookiePrefsToggle');
  const btnSavePrefs = document.getElementById('cookieSavePrefs');
  const prefsBox = document.getElementById('cookiePrefs');
  const chkAnalytics = document.getElementById('cookieAnalytics');

  const saveConsent = (value) => {
    localStorage.setItem(COOKIE_KEY, JSON.stringify({ ...value, date: new Date().toISOString() }));
    cookieBanner.classList.add('d-none');
  };

  const loadConsent = () => {
    try {
      return JSON.parse(localStorage.getItem(COOKIE_KEY) || 'null');
    } catch {
      return null;
    }
  };

  const showBannerIfNeeded = () => {
    if (loadConsent()) return;
    cookieBanner.classList.remove('d-none');
  };

  btnAccept?.addEventListener('click', () => {
    saveConsent({ essential: true, analytics: true });
  });

  btnReject?.addEventListener('click', () => {
    if (chkAnalytics) chkAnalytics.checked = false;
    saveConsent({ essential: true, analytics: false });
  });

  btnPrefsToggle?.addEventListener('click', () => {
    if (!prefsBox || !btnSavePrefs || !btnPrefsToggle) return;
    const isHidden = prefsBox.hasAttribute('hidden');
    if (isHidden) {
      prefsBox.removeAttribute('hidden');
      btnSavePrefs.hidden = false;
      btnPrefsToggle.textContent = 'Chiudi preferenze';
    } else {
      prefsBox.setAttribute('hidden', 'true');
      btnSavePrefs.hidden = true;
      btnPrefsToggle.textContent = 'Preferenze';
    }
  });

  btnSavePrefs?.addEventListener('click', () => {
    saveConsent({ essential: true, analytics: chkAnalytics?.checked });
  });

  showBannerIfNeeded();
})();

(() => {
  const navCollapse = document.getElementById('nav');
  if (!navCollapse || !window.bootstrap?.Collapse) return;
  const navbar = bootstrap.Collapse.getOrCreateInstance(navCollapse, { toggle: false });
  const closeIfOpen = () => {
    if (navCollapse.classList.contains('show')) {
      navbar.hide();
    }
  };
  window.addEventListener('scroll', closeIfOpen, { passive: true });
  window.addEventListener('wheel', closeIfOpen, { passive: true });
  window.addEventListener('touchmove', closeIfOpen, { passive: true });
  navCollapse.addEventListener('scroll', closeIfOpen, { passive: true });
  navCollapse.addEventListener('click', (event) => {
    const target = event.target;
    if (target && target.closest('a')) {
      navbar.hide();
    }
  });
})();
