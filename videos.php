<?php

include 'config.php';

if (!isset($_SESSION['user'])) { header('Location: auth.php'); exit(); }

$videosFile = 'videos.json';

$videos = file_exists($videosFile) ? json_decode(file_get_contents($videosFile), true) : [];

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>مكتبة مرئيات المحراب</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>

:root { --green: #1b4d3e; --gold: #d4af37; --bg: #fdfaf5; }

body {

    margin:0;

    font-family: 'Segoe UI', sans-serif;

    background: var(--bg);

    overflow-x: hidden;

}

/* شعار / جملة حلوة */

.header-banner {

    background: var(--green);

    color: var(--gold);

    text-align: center;

    padding: 20px 15px;

    font-size: 1.4rem;

    font-weight: bold;

    box-shadow: 0 5px 15px rgba(0,0,0,0.2);

}

/* قائمة الفيديوهات الأولى */

.video-list {

    max-width: 800px;

    margin: 20px auto;

    display: flex;

    flex-direction: column;

    gap: 12px;

}

.video-list button {

    padding: 12px 20px;

    font-size: 1rem;

    background: var(--green);

    color: var(--gold);

    border: none;

    border-radius: 10px;

    cursor: pointer;

    text-align: right;

    transition: 0.2s;

}

.video-list button:hover {

    background: var(--gold);

    color: var(--green);

}

/* حاوية الفيديوهات بالكامل */

.video-container {

    display: none; /* مخفية في البداية */

    height: calc(100vh - 80px);

    overflow-y: scroll;

    scroll-snap-type: y mandatory;

}

/* كل فيديو يأخذ full screen */

.video-card {

    position: relative;

    height: 100vh;

    scroll-snap-align: start;

    display: flex;

    justify-content: center;

    align-items: center;

    background: black;

}

/* الفيديو نفسه */

video {

    width: 90%;       /* تصغير الفيديو عن كامل الشاشة */

    height: auto;

    max-height: 90%;

    object-fit: contain;

    border-radius: 15px;

}

/* معلومات الفيديو */

.video-info {

    position: absolute;

    bottom: 20px;

    left: 15px;

    color: white;

    text-shadow: 0 0 10px rgba(0,0,0,0.7);

}

.video-info h3 { margin: 0 0 10px 0; font-size: 1.3rem; }

.stats { display: flex; gap: 15px; font-size: 0.95rem; }

.view-count { color: var(--gold); font-weight: bold; }

/* أيقونات جانبية */

.actions {

    position: absolute;

    right: 15px;

    bottom: 80px;

    display: flex;

    flex-direction: column;

    gap: 20px;

    color: white;

    font-size: 1.5rem;

}

.actions i { cursor: pointer; text-shadow: 0 0 10px rgba(0,0,0,0.7); }

/* استجابة للجوال */

@media(max-width:768px){

    .video-info h3 { font-size: 1.1rem; }

    .actions { font-size: 1.3rem; gap: 15px; }

    .header-banner { font-size: 1.2rem; padding: 15px 10px; }

    video { width: 100%; max-height: 80%; }

}

</style>

</head>

<body>

<div class="header-banner">

📽️ استمتع بمكتبة مرئيات المحراب - علم، ترفيه، وإلهام

</div>

<!-- قائمة الفيديوهات الأولى -->

<div class="video-list" id="videoList">

    <?php foreach(array_reverse($videos) as $v): if($v['status'] !== 'approved') continue; ?>

        <button data-video="<?php echo $v['id']; ?>"><?php echo $v['title']; ?></button>

    <?php endforeach; ?>

</div>

<!-- حاوية الفيديوهات -->

<div class="video-container" id="videoContainer">

    <?php foreach(array_reverse($videos) as $v): if($v['status'] !== 'approved') continue; ?>

    <div class="video-card">

        <video preload="metadata" playsinline>

            <source src="<?php echo $v['url']; ?>#t=0.5" type="video/mp4">

        </video>

        <div class="video-info">

            <h3><?php echo $v['title']; ?></h3>

            <div class="stats">

                <span><i class="far fa-calendar-alt"></i> <?php echo $v['date']; ?></span>

                <span class="view-count"><i class="fas fa-eye"></i> <span id="view-<?php echo $v['id']; ?>"><?php echo $v['views'] ?? 0; ?></span> مشاهدة</span>

            </div>

        </div>

        <div class="actions">

            <i class="fas fa-heart"></i>

            <i class="fas fa-comment"></i>

            <i class="fas fa-share"></i>

        </div>

    </div>

    <?php endforeach; ?>

</div>

<script>

const videos = document.querySelectorAll('video');

const container = document.getElementById('videoContainer');

const list = document.getElementById('videoList');

// تشغيل الفيديو الظاهر فقط

const observer = new IntersectionObserver(entries => {

    entries.forEach(entry => {

        const vid = entry.target;

        if(entry.isIntersecting) {

            vid.play();

            vid.muted = false; // شغّل الصوت

            const id = vid.nextElementSibling?.querySelector('.view-count span')?.id.replace('view-','');

            if(id) countView(id);

        } else {

            vid.pause();

        }

    });

}, { threshold: 0.75 });

// إعطاء لكل فيديو dataset.id

videos.forEach(v=>{

    v.dataset.id = v.nextElementSibling?.querySelector('.view-count span')?.id.replace('view-','') || '';

    observer.observe(v);

});

// عد المشاهدات

function countView(videoId) {

    fetch('update_views.php?id=' + videoId)

    .then(res => res.json())

    .then(data => {

        if(data.newViews) document.getElementById('view-' + videoId).innerText = data.newViews;

    });

}

// عند اختيار فيديو من القائمة

list.querySelectorAll('button').forEach(btn=>{

    btn.addEventListener('click', ()=>{

        // أخفي القائمة

        list.style.display = 'none';

        // إظهار الفيديوهات

        container.style.display = 'block';

        // النزول للفيديو المختار

        const id = btn.dataset.video;

        const vidCard = Array.from(container.children).find(c => c.querySelector('video').dataset.id === id);

        if(vidCard) vidCard.scrollIntoView({behavior:'smooth'});

    });

});

</script>

</body>

</html>