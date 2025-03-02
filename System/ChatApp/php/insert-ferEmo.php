<?php 
    session_start();
    include_once "config.php";
    // 檢查是否收到 POST 請求
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 接收 AJAX 請求中的參數
        $emotion = mysqli_real_escape_string($conn, $_POST['emotion']);
        $msg_id = mysqli_real_escape_string($conn, $_POST['msg_id']);
        $view_user_id = mysqli_real_escape_string($conn, $_POST['view_user_id']);

        $mode = '';
        $source = '';
        $labeler_user_id = '';
    
        if (isset($_POST['mode'])) {
            $mode = mysqli_real_escape_string($conn, $_POST['mode']);
        }
        if (isset($_POST['source'])) {
            $source = mysqli_real_escape_string($conn, $_POST['source']);
        }
        if (isset($_POST['labeler_user_id'])) {
            $labeler_user_id = mysqli_real_escape_string($conn, $_POST['labeler_user_id']);
        }
        if (isset($_POST['isMoodTag'])) {
            $isMoodTag = mysqli_real_escape_string($conn, $_POST['isMoodTag']);
        }

        // Set the time zone to Taipei
        date_default_timezone_set('Asia/Taipei'); 
        // Add current time
        $currentTime = date('Y-m-d H:i:s');

        // 如果已有舊的標記情緒，需要將舊的標記刪除
        // todo：如果標記者已經有自己標記的情緒，該蓋過去嗎?
        // 不應該蓋過去，所以要檢查如果上一個標記者是human，就不insert affectlabel
        $sql = "SELECT * FROM affectlabel 
                WHERE msg_id = {$msg_id} AND emo_del_time IS NOT NULL 
                ORDER BY emo_time DESC LIMIT 1";

        $query = mysqli_query($conn, $sql);

        
        $sql = mysqli_query($conn, "INSERT INTO emotion (msg_id, emotion, pattern, view_user_id, labeler_user_id, source, isMoodTag, emotion_time)
                                        VALUES ({$msg_id}, '{$emotion}', '{$mode}', '{$view_user_id}', {$labeler_user_id}, '{$source}',{$isMoodTag}, '{$currentTime}')") or die();

        // 如果affectlabel已有資料，就只insert emotion
        // emotion、affectlabel兩個tabel都要insert        
        if(mysqli_num_rows($query) == 0){
            $sql = mysqli_query($conn, "INSERT INTO affectlabel (msg_id, user_id, affectEmo, isMoodTag, emo_time) 
                                        VALUES ({$msg_id}, '{$view_user_id}','{$emotion}', {$isMoodTag}, '{$currentTime}')") or die();  
        }
    } else {
        // 如果不是 POST 請求，返回錯誤
        http_response_code(405);
        echo 'Method Not Allowed';
    }
?>