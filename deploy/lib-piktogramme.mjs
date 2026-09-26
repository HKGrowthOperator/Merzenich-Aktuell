/**
 * Piktogramme fuer Auswahlkarten (Anzeige-Assistent, Angebote, Unternehmen).
 * Linien in currentColor, viewBox 64x56. Keine Logos, keine Marken.
 */
// Piktogramme: eine Linienstaerke, eine Groesse, Farbe aus dem Text (currentColor).
const S = 'fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"';
export const BILD = {
  haus: `<path d="M10 30 32 12l22 18" ${S}/><path d="M16 26v20h32V26" ${S}/><path d="M28 46V34h8v12" ${S}/>`,
  wohnung: `<rect x="18" y="8" width="28" height="40" ${S}/><path d="M24 15h5M35 15h5M24 23h5M35 23h5M24 31h5M35 31h5M29 48v-8h6v8" ${S}/>`,
  grundstueck: `<path d="M8 40 20 22l24 4 12 16Z" ${S}/><path d="M20 22v-8M44 26v-8M20 14l6 2-6 2M44 18l6 2-6 2" ${S}/>`,
  gewerbe: `<path d="M8 46V24l12-6v6l12-6v6l12-6v28Z" ${S}/><path d="M44 12h8v34M16 36h6M28 36h6" ${S}/>`,
  preisschild: `<path d="M32 10h14v14L28 42 14 28Z" ${S}/><circle cx="40" cy="17" r="2.5" ${S}/><path d="M24 28l6 6" ${S}/>`,
  schluessel: `<circle cx="22" cy="24" r="9" ${S}/><path d="M29 29l17 17M40 40l4-4M44 44l4-4" ${S}/>`,
  kerze: `<rect x="26" y="22" width="12" height="24" ${S}/><path d="M32 8c5 5 5 9 0 12-5-3-5-7 0-12Z" ${S}/><path d="M20 46h24" ${S}/>`,
  danke: `<path d="M32 44S12 32 12 20a9 9 0 0 1 20-4 9 9 0 0 1 20 4c0 12-20 24-20 24Z" ${S}/>`,
  erinnerung: `<rect x="12" y="12" width="40" height="34" ${S}/><path d="M12 22h40M22 8v8M42 8v8" ${S}/><path d="M32 28c3 3 3 6 0 8-3-2-3-5 0-8ZM28 40h8" ${S}/>`,
  familie: `<circle cx="24" cy="16" r="6" ${S}/><circle cx="42" cy="20" r="5" ${S}/><path d="M12 46c1-11 6-16 12-16s11 5 12 16M34 46c1-8 4-12 8-12s8 4 9 12" ${S}/>`,
  geburt: `<path d="M14 26h30a0 0 0 0 1 0 0 15 15 0 0 1-15 15h0a15 15 0 0 1-15-15Z" ${S}/><path d="M29 26V12a14 14 0 0 1 14 14" ${S}/><circle cx="20" cy="45" r="3" ${S}/><circle cx="38" cy="45" r="3" ${S}/><path d="M44 26l6-6" ${S}/>`,
  ringe: `<circle cx="25" cy="32" r="11" ${S}/><circle cx="39" cy="32" r="11" ${S}/><path d="M22 17l3-5 3 5" ${S}/>`,
  ring: `<circle cx="32" cy="34" r="12" ${S}/><path d="M26 14h12l-6 8Z" ${S}/>`,
  jubilaeum: `<circle cx="32" cy="24" r="12" ${S}/><path d="M26 34l-4 14 10-5 10 5-4-14" ${S}/><path d="M28 24h8M32 20v8" ${S}/>`,
  torte: `<rect x="12" y="28" width="40" height="18" ${S}/><path d="M12 36c5 3 9 3 13 0s9-3 14 0 9 3 13 0M24 28v-8M32 28v-8M40 28v-8" ${S}/><path d="M24 14v2M32 14v2M40 14v2" ${S}/>`,
  megafon: `<path d="M8 24h10l26-12v32L18 32H8Z" ${S}/><path d="M18 32l5 14h7l-4-12M50 22c3 3 3 9 0 12" ${S}/>`,
  banner: `<rect x="8" y="16" width="48" height="22" ${S}/><path d="M14 23h20M14 30h12M42 22v10" ${S}/><path d="M14 46h36" ${S}/>`,
  tipp: `<path d="M32 8a13 13 0 0 1 8 23c-2 2-3 4-3 7H27c0-3-1-5-3-7a13 13 0 0 1 8-23Z" ${S}/><path d="M27 44h10M29 49h6" ${S}/>`,
  sponsoring: `<path d="M8 30l10-10 8 4 6-4 6 4 8-4 10 10-12 12-8-6-6 6-6-6-6 6Z" ${S}/>`,
  laden: `<path d="M10 22l4-10h36l4 10M10 22v24h44V22M10 22c0 4 4 6 7 6s7-2 7-6c0 4 4 6 8 6s8-2 8-6c0 4 4 6 7 6s7-2 7-6" ${S}/><path d="M28 46V36h8v10" ${S}/>`,
  beratung: `<path d="M8 12h30v20H20l-8 7v-7H8Z" ${S}/><path d="M42 22h14v18h-4v6l-7-6H30v-4" ${S}/>`,
  sonstiges: `<circle cx="20" cy="30" r="3" ${S}/><circle cx="32" cy="30" r="3" ${S}/><circle cx="44" cy="30" r="3" ${S}/>`,
};
export const bild = (k) => {
  if (!BILD[k]) throw new Error(`Piktogramm "${k}" fehlt`);
  return `<span class="anz-karte__bild" aria-hidden="true"><svg viewBox="0 0 64 56" focusable="false">${BILD[k]}</svg></span>`;
};
