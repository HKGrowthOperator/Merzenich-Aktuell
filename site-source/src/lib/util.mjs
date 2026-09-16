import { createHash } from 'node:crypto';

export const TZ = 'Europe/Berlin';

export function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}
export function escAttr(s) { return esc(s); }

export function slugify(s) {
  return String(s).toLowerCase()
    .replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
}

export function stripHtml(html) {
  return String(html).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
}

export function truncate(s, n = 160) {
  s = String(s).trim();
  if (s.length <= n) return s;
  const cut = s.slice(0, n - 1);
  return cut.slice(0, Math.max(cut.lastIndexOf(' '), 40)) + ' …';
}

export function words(s) { return stripHtml(s).split(/\s+/).filter(Boolean).length; }
export function readingMinutes(html) { return Math.max(1, Math.round(words(html) / 200)); }

/* ---------- Datum ---------- */
export function toDate(v) {
  if (v instanceof Date) return v;
  if (typeof v === 'number') return new Date(v);
  const s = String(v).trim();
  // "2026-09-01 10:00" oder "2026-09-01T10:00" ohne Zone => Europe/Berlin annehmen
  if (/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}(:\d{2})?)?$/.test(s)) {
    const [d, t = '00:00:00'] = s.replace(' ', 'T').split('T');
    const iso = `${d}T${t.length === 5 ? t + ':00' : t}`;
    return new Date(iso + offsetFor(new Date(iso + 'Z')));
  }
  return new Date(s);
}

function offsetFor(date) {
  // Zeitzonen-Offset von Europe/Berlin für ein gegebenes Datum (+01:00 / +02:00)
  const f = new Intl.DateTimeFormat('en-US', { timeZone: TZ, timeZoneName: 'longOffset' });
  const part = f.formatToParts(date).find(p => p.type === 'timeZoneName').value; // GMT+02:00
  const m = part.match(/([+-]\d{2}:\d{2})/);
  return m ? m[1] : '+00:00';
}

export function isoLocal(date) {
  const d = toDate(date);
  const p = Object.fromEntries(new Intl.DateTimeFormat('en-CA', {
    timeZone: TZ, year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
  }).formatToParts(d).map(x => [x.type, x.value]));
  return `${p.year}-${p.month}-${p.day}T${p.hour === '24' ? '00' : p.hour}:${p.minute}:${p.second}${offsetFor(d)}`;
}

const DE_LONG = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
const DE_DATE = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, day: '2-digit', month: '2-digit', year: 'numeric' });
const DE_DATE_SHORT = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, day: '2-digit', month: '2-digit' });
const DE_TIME = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, hour: '2-digit', minute: '2-digit' });
const DE_MONTH = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, month: 'long', year: 'numeric' });
const DE_MON3 = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, month: 'short' });
const DE_DAY = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, day: 'numeric' });
const DE_WD = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, weekday: 'short' });
const DE_WDLONG = new Intl.DateTimeFormat('de-DE', { timeZone: TZ, weekday: 'long' });

export const fmt = {
  long: d => DE_LONG.format(toDate(d)),
  date: d => DE_DATE.format(toDate(d)),
  short: d => DE_DATE_SHORT.format(toDate(d)),
  time: d => DE_TIME.format(toDate(d)),
  dateTime: d => `${DE_DATE.format(toDate(d))} · ${DE_TIME.format(toDate(d))} Uhr`,
  shortTime: d => `${DE_DATE_SHORT.format(toDate(d))} · ${DE_TIME.format(toDate(d))}`,
  month: d => DE_MONTH.format(toDate(d)),
  mon3: d => DE_MON3.format(toDate(d)).replace('.', ''),
  day: d => DE_DAY.format(toDate(d)),
  wd: d => DE_WD.format(toDate(d)).replace('.', ''),
  wdLong: d => DE_WDLONG.format(toDate(d)),
  rfc822: d => toDate(d).toUTCString(),
  ymd: d => isoLocal(d).slice(0, 10),
  ym: d => isoLocal(d).slice(0, 7),
  relative(d, now = new Date()) {
    const diff = (now - toDate(d)) / 1000;
    if (diff < 60) return 'gerade eben';
    if (diff < 3600) return `vor ${Math.round(diff / 60)} Minuten`;
    if (diff < 86400) return `vor ${Math.round(diff / 3600)} Stunden`;
    if (diff < 86400 * 2) return 'gestern';
    if (diff < 86400 * 7) return `vor ${Math.round(diff / 86400)} Tagen`;
    return DE_DATE.format(toDate(d));
  }
};

export function hash(s, n = 8) { return createHash('sha1').update(String(s)).digest('hex').slice(0, n); }

export function uniq(arr) { return [...new Set(arr)]; }
export function by(key, dir = -1) { return (a, b) => (a[key] > b[key] ? 1 : a[key] < b[key] ? -1 : 0) * -dir; }
export function groupBy(arr, fn) {
  const m = new Map();
  for (const x of arr) { const k = fn(x); if (!m.has(k)) m.set(k, []); m.get(k).push(x); }
  return m;
}
export function json(o) { return JSON.stringify(o).replace(/</g, '\\u003c'); }
export function icsDate(d) { return toDate(d).toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, ''); }
export function icsText(s) { return String(s ?? '').replace(/\\/g, '\\\\').replace(/;/g, '\;').replace(/,/g, '\\,').replace(/\n/g, '\\n'); }
