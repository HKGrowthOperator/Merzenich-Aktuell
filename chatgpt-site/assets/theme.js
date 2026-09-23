/* Merzenich Aktuell – Hellmodus ist seit 23.09.2026 die einzige Darstellung. */
(() => {
  document.documentElement.dataset.theme = 'light';
  document.documentElement.style.colorScheme = 'light';
  try { localStorage.removeItem('merzenich-theme'); } catch (e) {}
  document.querySelectorAll('.theme-toggle,.darstellung-knopf,.einstellungen,.einstellungen-schleier').forEach((el) => el.remove());
})();
