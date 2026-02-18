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
  './style.css',
  './script.js',
  './MO.html',
  './ans.json',
  './quran-simple.txt',
  './manifest.json',
  './assets/images/icon-192.png',
  './assets/images/icon-512.png'
];

// تثبيت الكاش
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(assets);
    })
  );
  self.skipWaiting();
});

// حذف النسخ القديمة
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// جلب الملفات (Network First Strategy)
self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request)
      .then(response => {
        return caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, response.clone());
          return response;
        });
      })
      .catch(() => {
        return caches.match(event.request);
      })
  );
});
