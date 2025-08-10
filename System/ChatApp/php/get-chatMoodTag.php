<?php 
session_start();
if(isset($_SESSION['unique_id'])){
    $language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
    include_once "../lang/$language.php";
    include_once "config.php";
    $outgoing_id = $_SESSION['unique_id'];
    $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
    $mode = $_POST['mode'];
    $isPractice = $_POST['isPractice'];
    $output = "";
    $sql = "SELECT *,messages.msg_id as msgId,

            -- 子查詢查出系統標記的情緒
            (SELECT e1.emotion FROM emotion e1
            LEFT JOIN emotion e2 ON e1.msg_id = e2.msg_id 
                                AND e1.source NOT LIKE'Human%' 
                                AND e2.source NOT LIKE'Human%' 
                                AND e1.emotion_time < e2.emotion_time
            WHERE e2.msg_id IS NULL AND e1.source NOT LIKE 'Human%' 
                                    AND messages.msg_id=e1.msg_id 
                                    AND messages.outgoing_msg_id=e1.labeler_user_id
            ) AS latest_emotion,

            -- 子查詢查出自己標記的人為標記的情緒
            (SELECT e1.emotion FROM emotion e1
            LEFT JOIN emotion e2 ON e1.msg_id = e2.msg_id 
                                AND e1.source LIKE'Human%' 
                                AND e2.source LIKE'Human%' 
                                AND e1.emotion_time < e2.emotion_time
            WHERE e2.msg_id IS NULL AND e1.source LIKE 'Human%' 
                                    AND messages.msg_id=e1.msg_id 
                                    AND messages.outgoing_msg_id=e1.labeler_user_id
                                    AND e1.labeler_user_id={$outgoing_id}
                                    AND e1.emotion is not null
            ) AS latest_emotion_by_human

            FROM messages 
            LEFT JOIN users ON users.unique_id = messages.outgoing_msg_id
            LEFT JOIN affectlabel ON affectlabel.user_id = {$outgoing_id} 
                        AND messages.msg_id = affectlabel.msg_id 
                        AND affectlabel.emo_del_time IS NULL
            WHERE ((outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
                OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id})) 
                AND messages.pattern = '{$mode}' 
                AND isPractice = '{$isPractice}' 
            ORDER BY messages.msg_id";

    $query = mysqli_query($conn, $sql);
    if(mysqli_num_rows($query) > 0){
        while($row = mysqli_fetch_assoc($query)){
            $msgTimeFormatted = $row['msg_time'] ? date("H:i", strtotime($row['msg_time'])) : '00:00';

            // 1) 統一映射
            $emotionsEmojiDict = [
                "anger" => "😡",
                "disgust" => "🤮",
                "fear" => "😨",
                "sad" => "😢",
                "surprise" => "😮"
            ];

            // 2) 取出 manual(人為) 與 moodtag(系統) 的「代碼」與「emoji」
            $manualKey   = $row['latest_emotion_by_human'] ?? ($row['affectEmo'] ?? null);
            $moodtagKey  = $row['latest_emotion'] ?? null;

            $manualEmoji  = $manualKey  && isset($emotionsEmojiDict[$manualKey])  ? $emotionsEmojiDict[$manualKey]  : '';
            $moodtagEmoji = $moodtagKey && isset($emotionsEmojiDict[$moodtagKey]) ? $emotionsEmojiDict[$moodtagKey] : '';

            // 3) 基本框架（共用）
            $msgId   = $row['msgId'];
            $isMine  = ($row['outgoing_msg_id'] === $outgoing_id);

            if ($isMine) {
                $output .= '<div id="'.$msgId.'" class="chat outgoing" data-msg-id="'.$msgId.'">';
                $output .= '  <div class="details">';
                $output .= '    <span class="time">'.$msgTimeFormatted.'</span>';
                $output .= '    <img class="smile" src="images/smile.png" alt="Smile">';
                $output .= '    <p>'.$row['msg'].'</p>';
                $output .= '  </div>';
            } else {
                $output .= '<div id="'.$msgId.'" class="chat incoming" data-msg-id="'.$msgId.'">';
                $output .= '  <div class="details">';
                $output .= '    <img class="profile round" src="images/'.$row['img'].'" alt="">';
                $output .= '    <p>'.$row['msg'].'</p>';
                $output .= '    <div class="read-status">';
                $output .= '      <span class="read">已讀</span>';
                $output .= '      <span class="time">'.$msgTimeFormatted.'</span>';
                $output .= '      <img class="smile" src="images/smile.png" alt="Smile">';
                $output .= '    </div>';
                $output .= '  </div>';
            }

            // 4) 隱藏的 affect 標籤（給右欄鏡射用）
            $output .= '  <div class="affect labels" data-manual="'.htmlspecialchars($manualKey ?? '', ENT_QUOTES).'" data-moodtag="'.htmlspecialchars($moodtagKey ?? '', ENT_QUOTES).'" style="display:none">';
            $output .= '    <span class="affect-label manual">'.$manualEmoji.'</span>';
            $output .= '    <span class="affect-label moodtag">'.$moodtagEmoji.'</span>';
            $output .= '  </div>';

            $output .= '</div>'; // .chat
        }

    }else{
        $output .= '<div id="noMessages" class="text">No messages are available. Once you send message they will appear here.</div>';
    }
    echo $output;
}else{
    header("location: ../login.php");
}
?>
