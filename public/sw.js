// AmeelHub service worker
// Scope is set per-panel at registration time (see partials/pwa-register.blade.php):
//   navigator.serviceWorker.register('/sw.js', { scope: '/worker/' })
//   navigator.serviceWorker.register('/sw.js', { scope: '/agent/' })
//
// IMPORTANT SAFETY RULE:
// This worker only caches static, non-sensitive assets (css/js/images/fonts/icons)
// and one static offline fallback page. It NEVER caches page HTML, Livewire
// responses, or any /api or wallet/escrow/milestone data. Every such request is
// always fetched fresh from the network. Bump CACHE_VERSION to force clients to
// drop old cached assets after a deploy.

const CACHE_VERSION = 'ameelhub-shell-v1';

const SHELL_ASSETS = [
  '/offline.html',
  '/icons/icon-worker-192.png',
  '/icons/icon-worker-512.png',
  '/icons/icon-agent-192.png',
  '/icons/icon-agent-512.png',
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_VERSION).then((cache) =>
      cache.addAll(SHELL_ASSETS).catch(() => {
        // Don't fail install if one asset is missing (e.g. icon not deployed yet)
      })
    )
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key))
        )
      )
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Only handle GET requests from our own origin. Everything else (POST forms,
  // Livewire actions, cross-origin) is left completely untouched.
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  const isStaticAsset = /\.(css|js|png|jpg|jpeg|svg|webp|woff2?|ico)$/i.test(url.pathname);

  if (isStaticAsset) {
    // Cache-first for static assets only — safe, no user/financial data.
    event.respondWith(
      caches.match(req).then((cached) => {
        if (cached) return cached;
        return fetch(req).then((res) => {
          if (res && res.ok) {
            const clone = res.clone();
            caches.open(CACHE_VERSION).then((cache) => cache.put(req, clone));
          }
          return res;
        });
      })
    );
    return;
  }

  // Pages, Livewire, API, wallet/escrow/milestone data: ALWAYS network-first.
  // Never serve a stale cached copy of financial or account data. Only fall
  // back to the static offline page when the network is unreachable.
  event.respondWith(
    fetch(req).catch(() => caches.match('/offline.html'))
  );
});