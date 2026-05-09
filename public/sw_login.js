// Service Worker minimo para el login: solo habilita la instalabilidad de la PWA.
// El SW real de la app vive en /sw_inspecciones.js con scope /inspecciones/.

const LOGIN_CACHE = 'enterprisesst-login-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((names) =>
            Promise.all(
                names.filter((n) => n.startsWith('enterprisesst-login-') && n !== LOGIN_CACHE)
                     .map((n) => caches.delete(n))
            )
        ).then(() => self.clients.claim())
    );
});

// Network-first muy simple. Solo intercepta GET dentro del scope de login.
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
