const CACHE_NAME = 'islamic-app-v3';
const assets = [
  './',
  './index.html',
  './moshaf.html',
  './azkar.html',
  './hadith.html',
  './duas.html',
  './sebha.html',
  './prayer-times.html',
  './asmaa.html',
  './werd.html',
  './cards.html',
  './manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      // استخدام addAll بحذر، إذا فشل ملف واحد سيفشل الكل
      // لذا يفضل التأكد من وجود كل الملفات في المستودع
      return cache.addAll(assets);
    })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request).catch(() => {
        if (event.request.mode === 'navigate') {
          return caches.match('./index.html');
        }
      });
    })
  );
});
