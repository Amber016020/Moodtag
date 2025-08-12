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
    $sql = "
    SELECT
    m.*,
    u.*,
    m.msg_id AS msgId,

    /* 系統判斷情緒：同一則訊息、同一個標記者（=發送者），取最新一筆 */
    (
        SELECT e1.emotion
        FROM emotion e1
        WHERE e1.emotion IS NOT NULL
        AND e1.msg_id = m.msg_id
        AND e1.labeler_user_id = m.outgoing_msg_id
        ORDER BY e1.emotion_time DESC
        LIMIT 1
    ) AS latest_emotion,

    /* 自己標記的最新情緒：同一則訊息、目前登入者作的標記，且他就是該訊息的發送者 */
    (
        SELECT e1.affectEmo
        FROM affectlabel e1
        WHERE e1.affectEmo IS NOT NULL
        AND e1.msg_id = m.msg_id
        AND (e1.user_id = {$outgoing_id} OR e1.user_id =  {$incoming_id})
        AND m.outgoing_msg_id = e1.user_id
        ORDER BY e1.emo_time DESC
        LIMIT 1
    ) AS latest_emotion_by_human

    FROM messages m
    LEFT JOIN users u
    ON u.unique_id = m.outgoing_msg_id

    WHERE
    (
        (m.outgoing_msg_id = {$outgoing_id} AND m.incoming_msg_id = {$incoming_id})
        OR
        (m.outgoing_msg_id = {$incoming_id} AND m.incoming_msg_id = {$outgoing_id})
    )
    AND m.`pattern` = '{$mode}'
    AND m.isPractice = '{$isPractice}'
    ORDER BY m.msg_id ASC
    ";

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
