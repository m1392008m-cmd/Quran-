<?php

include 'config.php';

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $videosFile = 'videos.json';

    

    if (file_exists($videosFile)) {

        $videos = json_decode(file_get_contents($videosFile), true);

        $newViews = 0;

        foreach ($videos as &$v) {

            if ($v['id'] === $id) {

                // إذا لم يكن هناك حقل للمشاهدات، نبدأ من 1، وإلا نزيد 1

                if (!isset($v['views'])) {

                    $v['views'] = 1;

                } else {

                    $v['views']++;

                }

                $newViews = $v['views'];

                break;

            }

        }

        file_put_contents($videosFile, json_encode($videos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        

        // نرسل الرقم الجديد لليوز في الصفحة فوراً

        echo json_encode(['newViews' => $newViews]);

    }

}

?>

