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
  './cards.html',
  './style.css',
  './script.js',
  './MO.html',
  './ans.json',
  './quran-simple.txt', // ملف القرآن الأساسي
  './manifest.json',
  './assets/images/icon-512.png',
  './assets/images/icon-192.png'
];

// تثبيت الـ Service Worker وتخزين الملفات
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('Caching assets...');
      return cache.addAll(assets);
    })
  );
});

// تفعيل الـ SW وحذف الكاش القديم
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      );
    })
  );
});

// استراتيجية "الرد من الكاش أولاً ثم الشبكة" لضمان السرعة والعمل أوفلاين
self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(res => {
      return res || fetch(e.request);
    })
  );
});
