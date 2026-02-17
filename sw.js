const CACHE_NAME = 'islamic-app-v2';
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
  './cards.html'
];

// تثبيت الملفات في ذاكرة المتصفح
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('Caching assets...');
      return cache.addAll(assets);
    })
  );
});

// تشغيل الموقع من الكاش في حال انقطاع النت
self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(res => {
      return res || fetch(e.request);
    })
  );
});
