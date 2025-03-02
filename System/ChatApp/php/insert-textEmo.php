<?php 
    session_start();
    include_once "config.php";
    // 檢查是否收到 POST 請求
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 接收 AJAX 請求中的參數
        $emotion = mysqli_real_escape_string($conn, $_POST['emotion']);
        $msg_id = mysqli_real_escape_string($conn, $_POST['msg_id']);
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $oldEmo = mysqli_real_escape_string($conn, $_POST['oldEmo']);

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

        // Set the time zone to Taipei
        date_default_timezone_set('Asia/Taipei'); 
        // Add current time
        $currentTime = date('Y-m-d H:i:s');

        // 如果已有舊的標記情緒，需要將舊的標記刪除
        if($oldEmo !== ""){
            $sql = "SELECT * FROM affectlabel WHERE msg_id = {$msg_id} AND affectEmo = '{$oldEmo}' AND emo_del_time IS NULL ORDER BY emo_time DESC LIMIT 1";

            $query = mysqli_query($conn, $sql);
            
            // 如果有查到資料，就update
            if(mysqli_num_rows($query) > 0){
                $row = mysqli_fetch_assoc($query);
                $updateSql = "UPDATE affectlabel SET emo_del_time = '{$currentTime}' WHERE affect_id = {$row['affect_id']}";
                $updateQuery = mysqli_query($conn, $updateSql);
            }

        }
        // 如果已有舊的標記情緒，且不等於當前標記情緒，代表需要insert一筆資料
        if($oldEmo != $emotion){
            $sql = mysqli_query($conn, "INSERT INTO affectlabel (msg_id, user_id, affectEmo, isMoodTag, emo_time) VALUES ({$msg_id}, '{$user_id}','{$emotion}', 0, '{$currentTime}')") or die();            
        }

        // 如果是人為標記，要insert emotion table
        // todo：重覆要刪掉(不應該刪掉吧?應該是db search時要將重複的資料排除，只抓取最新的一筆)
        if($mode != ''){
            $sql = mysqli_query($conn, "INSERT INTO emotion (msg_id, emotion, pattern, view_user_id, labeler_user_id, source, isMoodTag, emotion_time)
                                        VALUES ({$msg_id}, '{$emotion}', '{$mode}', '{$user_id}', {$labeler_user_id}, '{$source}',0, '{$currentTime}')") or die();
        }


    } else {
        // 如果不是 POST 請求，返回錯誤
        http_response_code(405);
        echo 'Method Not Allowed';
    }
?>