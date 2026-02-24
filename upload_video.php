<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

ini_set('display_errors', 0);

include 'config.php';

if (!isset($_SESSION['user'])) {

    header('Location: auth.php');

    exit();

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file'])) {

    header('Content-Type: application/json');

    $uploadDir = __DIR__ . '/uploads/videos/';

    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

    $fileExt = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));

    $fileName = "User_" . $_SESSION['user']['id'] . "_" . time() . "." . $fileExt;

    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetFile)) {

        $videosFile = 'videos.json';

        $videos = file_exists($videosFile) ? json_decode(file_get_contents($videosFile), true) : [];

        

        $videos[] = [

            'id' => uniqid(),

            'title' => htmlspecialchars($_POST['title']),

            'url' => 'uploads/videos/' . $fileName,

            'category' => $_POST['category'],

            'uploader' => $_SESSION['user']['username'],

            'status' => 'pending', // الحالة الافتراضية: بانتظار الموافقة

            'date' => date('Y-m-d H:i')

        ];

        

        file_put_contents($videosFile, json_encode($videos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        echo json_encode(['status' => 'success', 'msg' => 'تم الرفع! سيظهر الفيديو بعد مراجعة المشرف.']);

    } else {

        echo json_encode(['status' => 'error', 'msg' => 'فشل الرفع.']);

    }

    exit();

}

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ساهم بفيديو في المحراب</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>

        :root { --green: #1b4d3e; --gold: #d4af37; }

        body { font-family: 'Tajawal', sans-serif; background: #fdfaf5; padding: 15px; }

        .card { background: white; padding: 25px; border-radius: 15px; max-width: 500px; margin: auto; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-top: 5px solid var(--gold); }

        input, select, .btn { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; }

        .btn { background: var(--green); color: var(--gold); font-weight: bold; border: none; cursor: pointer; }

        .prog-cont { display: none; background: #eee; border-radius: 10px; height: 20px; overflow: hidden; margin: 10px 0; }

        .prog-bar { width: 0%; height: 100%; background: var(--gold); transition: 0.2s; text-align: center; font-size: 12px; line-height: 20px; }

    </style>

</head>

<body>

<div class="card">

    <h3 style="text-align:center; color: var(--green);">📤 شاركنا إبداعك</h3>

    <p style="text-align:center; font-size: 0.9rem; color: #666;">ارفع فيديوهاتك وسيقوم المشرفون بمراجعتها ونشرها.</p>

    

    <form id="uploadForm">

        <input type="text" id="title" placeholder="عنوان الفيديو" required>

        <select id="category">

            <option value="مشاركة فارس">مشاركة فارس</option>

            <option value="تطبيق عملي">تطبيق عملي</option>

            <option value="إبداع">إبداع</option>

        </select>

        <input type="file" id="video_file" accept="video/*" required>

        

        <div class="prog-cont" id="progCont">

            <div class="prog-bar" id="progBar">0%</div>

        </div>

        

        <button type="submit" class="btn" id="subBtn">إرسال للمراجعة 🚀</button>

    </form>

    <div id="status"></div>

    <br>

    <a href="index.php" style="display:block; text-align:center; color: var(--green); text-decoration:none;">العودة للرئيسية</a>

</div>

<script>

const uploadForm = document.getElementById('uploadForm');

uploadForm.onsubmit = function(e) {

    e.preventDefault();

    const file = document.getElementById('video_file').files[0];

    const formData = new FormData();

    formData.append('video_file', file);

    formData.append('title', document.getElementById('title').value);

    formData.append('category', document.getElementById('category').value);

    const xhr = new XMLHttpRequest();

    xhr.open('POST', 'upload_video.php', true);

    

    xhr.upload.onprogress = function(e) {

        document.getElementById('progCont').style.display = 'block';

        const percent = Math.round((e.loaded / e.total) * 100);

        document.getElementById('progBar').style.width = percent + '%';

        document.getElementById('progBar').innerText = percent + '%';

    };

    xhr.onload = function() {

        const res = JSON.parse(xhr.responseText);

        alert(res.msg);

        if(res.status === 'success') location.reload();

    };

    xhr.send(formData);

};

</script>

</body>

</html>

