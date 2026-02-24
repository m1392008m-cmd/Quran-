<?php

// منع ظهور التحذيرات التي تفسد رد السيرفر على الاستضافات المجانية

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

ini_set('display_errors', 0); 

set_time_limit(3600); 

include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {

    header('Location: auth.php');

    exit();

}

$videosFile = 'videos.json';

$uploadDir = __DIR__ . '/uploads/videos/';

if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

// معالجة الرفع عبر Ajax

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file'])) {

    header('Content-Type: application/json');

    $response = ['status' => 'error', 'msg' => 'فشل النقل: قد يكون الملف أكبر من المسموح به في استضافتك'];

    

    $fileExt = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));

    $fileName = "Midan_" . time() . "_" . uniqid() . "." . $fileExt;

    $targetFile = $uploadDir . $fileName;

    

    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetFile)) {

        $videos = file_exists($videosFile) ? json_decode(file_get_contents($videosFile), true) : [];

        $videos[] = [

            'id' => uniqid(),

            'title' => htmlspecialchars($_POST['title']),

            'url' => 'uploads/videos/' . $fileName,

            'category' => $_POST['category'],

            'uploader' => 'المشرف',

            'status' => 'approved', // المشرف يرفع فيديوهات معتمدة فوراً

            'date' => date('Y-m-d H:i')

        ];

        file_put_contents($videosFile, json_encode($videos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $response = ['status' => 'success', 'msg' => 'تم الرفع بنجاح!'];

    }

    echo json_encode($response);

    exit();

}

// الموافقة على فيديو مستخدم

if (isset($_GET['approve'])) {

    $videos = json_decode(file_get_contents($videosFile), true);

    foreach ($videos as &$v) { if ($v['id'] == $_GET['approve']) $v['status'] = 'approved'; }

    file_put_contents($videosFile, json_encode($videos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    header("Location: admin_videos.php");

}

// حذف فيديو

if (isset($_GET['delete'])) {

    $videos = json_decode(file_get_contents($videosFile), true);

    foreach ($videos as $key => $v) {

        if ($v['id'] == $_GET['delete']) {

            if (file_exists($v['url'])) unlink($v['url']);

            unset($videos[$key]);

        }

    }

    file_put_contents($videosFile, json_encode(array_values($videos), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    header("Location: admin_videos.php");

}

$videos = file_exists($videosFile) ? json_decode(file_get_contents($videosFile), true) : [];

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>إدارة مرئيات المحراب</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>

        :root { --green: #1b4d3e; --gold: #d4af37; }

        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f2f5; padding: 15px; }

        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid var(--gold); margin-bottom: 20px; }

        input, select, .btn { width: 100%; padding: 12px; margin: 8px 0; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; }

        .btn { background: var(--green); color: var(--gold); font-weight: bold; border: none; cursor: pointer; }

        .prog-cont { display: none; background: #eee; border-radius: 10px; height: 25px; overflow: hidden; }

        .prog-bar { width: 0%; height: 100%; background: var(--gold); text-align: center; line-height: 25px; font-size: 12px; transition: 0.3s; }

        .video-item { background: white; padding: 12px; border-radius: 10px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; border-right: 4px solid var(--green); }

        .pending { border-right-color: #e67e22; background: #fff9f4; }

    </style>

</head>

<body>

<div class="card">

    <h3>🎥 ارفع فيديو جديد (بصفتك مشرف)</h3>

    <form id="uploadForm">

        <input type="text" id="title" placeholder="عنوان الفيديو" required>

        <select id="category">

            <option value="درس تعليمي">درس تعليمي</option>

            <option value="تحفيز">تحفيز</option>

        </select>

        <input type="file" id="video_file" accept="video/*" required>

        <div class="prog-cont" id="progCont"><div class="prog-bar" id="progBar">0%</div></div>

        <button type="submit" class="btn" id="subBtn">بدء الرفع للسيرفر 🚀</button>

    </form>

    <div id="status"></div>

</div>

<h3>🔔 طلبات بانتظار الموافقة</h3>

<?php foreach(array_reverse($videos) as $v): if($v['status'] == 'pending'): ?>

    <div class="video-item pending">

        <div><strong><?php echo $v['title']; ?></strong><br><small>من: <?php echo $v['uploader']; ?></small></div>

        <div>

            <a href="?approve=<?php echo $v['id']; ?>" style="color: green; margin-left:15px;"><i class="fas fa-check"></i> موافقة</a>

            <a href="?delete=<?php echo $v['id']; ?>" style="color: red;"><i class="fas fa-trash"></i> حذف</a>

        </div>

    </div>

<?php endif; endforeach; ?>

<h3>🎬 المرئيات المنشورة</h3>

<?php foreach(array_reverse($videos) as $v): if($v['status'] == 'approved'): ?>

    <div class="video-item">

        <div><strong><?php echo $v['title']; ?></strong><br><small><?php echo $v['date']; ?></small></div>

        <a href="?delete=<?php echo $v['id']; ?>" style="color: red;"><i class="fas fa-trash-alt"></i></a>

    </div>

<?php endif; endforeach; ?>

<script>

const uploadForm = document.getElementById('uploadForm');

uploadForm.onsubmit = function(e) {

    e.preventDefault();

    const formData = new FormData();

    formData.append('video_file', document.getElementById('video_file').files[0]);

    formData.append('title', document.getElementById('title').value);

    formData.append('category', document.getElementById('category').value);

    const xhr = new XMLHttpRequest();

    xhr.open('POST', 'admin_videos.php', true);

    xhr.upload.onprogress = function(e) {

        if (e.lengthComputable) {

            document.getElementById('progCont').style.display = 'block';

            const percent = Math.round((e.loaded / e.total) * 100);

            document.getElementById('progBar').style.width = percent + '%';

            document.getElementById('progBar').innerText = percent + '%';

        }

    };

    xhr.onload = function() {

        try {

            // تنظيف أي تحذيرات PHP قد تسبق الـ JSON (حل مشكلة الصورة)

            let cleanJSON = xhr.responseText.substring(xhr.responseText.lastIndexOf('{'));

            const res = JSON.parse(cleanJSON);

            alert(res.msg);

            if(res.status === 'success') location.reload();

        } catch(e) { alert("حدث خطأ في الرد من السيرفر. قد يكون الملف كبيراً جداً."); }

    };

    xhr.send(formData);

};

</script>

</body>

</html>

