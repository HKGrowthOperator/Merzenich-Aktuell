/* Merzenich Aktuell – Service Worker: Offline-Fallback und Cache für Assets. Netzwerk zuerst für HTML. */
const VERSION = 'a-da77c0447098';
const STATIC = 'ma-static-' + VERSION;
const PAGES = 'ma-pages-' + VERSION;
const PRECACHE = [
  '/',
  '/offline/',
  '/assets/style.css',
  '/assets/v19.css',
  '/assets/v20.css',
  '/assets/recovery.css',
  '/assets/korrekturen.css',
  '/assets/theme.css',
  '/assets/v20.js',
  '/assets/app.js',
  '/assets/kopf.js',
  '/assets/homepage-polish.css',
  '/assets/homepage-polish.js',
  '/assets/img/favicon.svg'
];

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(STATIC)
      .then(cache => Promise.all(PRECACHE.map(async url => {
        try {
          const response = await fetch(url, { cache: 'reload' });
          if (response.ok) await cache.put(url, response.clone());
        } catch (_) {
          // Einzelne optionale Assets duerfen die SW-Installation nicht blockieren.
        }
      })))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(k => k !== STATIC && k !== PAGES).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;
  if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/redaktion') || url.pathname.startsWith('/.netlify')) return;

  if (req.mode === 'navigate') {
    e.respondWith(
      fetch(req)
        .then(res => {
          if (res.ok) {
            const copy = res.clone();
            caches.open(PAGES).then(c => c.put(req, copy));
          }
          return res;
        })
        .catch(() => caches.match(req).then(r => r || caches.match('/offline/')))
    );
    return;
  }

  if (/\.(css|js)$/.test(url.pathname)) {
    e.respondWith(
      // Exakt mit Query: jedes Asset traegt ?v=<Inhalts-Hash>. Mit ignoreSearch
      // lieferte der alte Worker nach einem Deploy altes CSS zu neuem HTML
      // (gemeldet 24.09.: Ortsmarke ohne Abstand).
      caches.match(req).then(async cached => {
        if (cached) return cached;
        const fresh = await fetch(req, { cache: 'reload' });
        if (fresh.ok) {
          const copy = fresh.clone();
          caches.open(STATIC).then(c => c.put(req, copy));
        }
        return fresh;
      })
    );
    return;
  }

  if (/\.(woff2|png|svg|jpg|jpeg|webp|avif|ico)$/.test(url.pathname)) {
    e.respondWith(
      caches.match(req, { ignoreSearch: true }).then(r => r || fetch(req).then(res => {
        if (res.ok) {
          const copy = res.clone();
          caches.open(STATIC).then(c => c.put(req, copy));
        }
        return res;
      }))
    );
  }
});
