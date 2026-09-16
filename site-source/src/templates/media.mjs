import { esc } from '../lib/util.mjs';

/* Bild-Helfer: lokale Bilder laufen über das Netlify Image CDN (responsive, WebP/AVIF automatisch),
   externe Quellbilder werden direkt geladen und fallen bei Fehlern auf eine Marken-Grafik zurück. */
const WIDTHS = [480, 800, 1200, 1600];

export function cdn(src, w, cfg) {
  if (!cfg.imageCdn) return src;
  if (/^https?:\/\//.test(src)) {
    // Externe Quellen nur über CDN, wenn Host freigegeben (netlify.toml [images].remote_images)
    const ok = (cfg.remoteImageHosts || []).some(h => src.includes(h));
    if (!ok) return src;
  }
  return `/.netlify/images?url=${encodeURIComponent(src)}&w=${w}&q=72`;
}

export function placeholder(kind = 'nachrichten', label = '') {
  return `/assets/img/ph-${kind}.svg`;
}

/**
 * Bild als <img> mit srcset. opts: alt, sizes, priority (LCP), width/height, kind (Platzhalter), classes
 */
export function img(src, opts = {}, cfg) {
  const { alt = '', sizes = '(max-width: 640px) 100vw, (max-width: 1080px) 60vw, 800px', priority = false, eager = false, kind = 'nachrichten', cls = '' } = opts;
  const ph = placeholder(kind);
  if (!src) {
    return `<img src="${ph}" alt="${esc(alt)}" width="1600" height="900" loading="${priority || eager ? 'eager' : 'lazy'}" decoding="async" class="ph-img ${cls}">`;
  }
  const isExternal = /^https?:\/\//.test(src);
  const srcset = WIDTHS.map(w => `${cdn(src, w, cfg)} ${w}w`).join(', ');
  const useSet = cfg.imageCdn && (!isExternal || (cfg.remoteImageHosts || []).some(h => src.includes(h)));
  return `<img src="${esc(useSet ? cdn(src, 1200, cfg) : src)}"${useSet ? ` srcset="${esc(srcset)}" sizes="${esc(sizes)}"` : ''} alt="${esc(alt)}" width="1600" height="900"` +
    ` loading="${priority || eager ? 'eager' : 'lazy'}"${priority ? ' fetchpriority="high"' : ''} decoding="async"` +
    `${isExternal ? ' referrerpolicy="no-referrer"' : ''} data-ph="${ph}" class="${cls}">`;
}

/* Bildtyp-Label nach Redaktionsstandard */
export const IMAGE_TYPES = {
  original: 'Originalbild',
  veranstaltungsbild: 'Offizielles Veranstaltungsbild',
  quellenmotiv: 'Quellenmotiv',
  archivbild: 'Archivbild',
  symbolbild: 'Symbolbild',
  logo: 'Offizielles Vereinslogo',
  grafik: 'Grafik',
  leserfoto: 'Leserfoto'
};
export function imageTypeLabel(t) { return IMAGE_TYPES[t] || ''; }
