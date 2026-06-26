const CACHE_NAME = 'imagelib-v1';
const urlsToCache = [
    '.', 'index.php', 'gallery.php', 'upload.php', 'profile.php',
    'login.php', 'register.php', 'global.css', 'global.js', 'toast.js', 'logo.png'
];

self.addEventListener('install', function(event) {
    event.waitUntil(caches.open(CACHE_NAME).then(function(cache) { return cache.addAll(urlsToCache); }));
});

self.addEventListener('activate', function(event) {
    event.waitUntil(caches.keys().then(function(cacheNames) {
        return Promise.all(cacheNames.map(function(cacheName) {
            if (cacheName !== CACHE_NAME) return caches.delete(cacheName);
        }));
    }));
});

self.addEventListener('fetch', function(event) {
    event.respondWith(caches.match(event.request).then(function(response) {
        return response || fetch(event.request);
    }));
});