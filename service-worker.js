const CACHE_NAME = 'islamic-app-final';
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
  './manifest.json',
  './style.css',
  './script.js',
  './assets/images/arabesque.png',
  './assets/images/icon-512.png',
  './assets/images/icon-192.png',
  './assets/images/icon-144.png'
];

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(assets))
  );
});

self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(res => res || fetch(e.request))
  );
});
