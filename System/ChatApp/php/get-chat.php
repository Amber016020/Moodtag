<?php
session_start();

if (!isset($_SESSION['unique_id'])) {
    header("location: ../login.php");
    exit;
}

$language = $_SESSION['language'] ?? 'en';
include_once "../lang/$language.php";
include_once "config.php";

$outgoing_id = $_SESSION['unique_id'];
$incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
$mode        = $_POST['mode'];
$isPractice  = $_POST['isPractice'];

$output = "";

// 取舊訊息（一般模式：本檔沒有系統情緒的子查詢）
$sql = "
SELECT
  *,
  messages.msg_id AS msgId
FROM messages
LEFT JOIN users
  ON users.unique_id = messages.outgoing_msg_id
LEFT JOIN affectlabel
  ON affectlabel.user_id = {$outgoing_id}
 AND messages.msg_id = affectlabel.msg_id
 AND affectlabel.emo_del_time IS NULL
WHERE (
        (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
     OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id})
      )
  AND messages.pattern = '{$mode}'
  AND messages.isPractice = '{$isPractice}'
ORDER BY messages.msg_id
";

$query = mysqli_query($conn, $sql);

if ($query && mysqli_num_rows($query) > 0) {
    // emoji 對照
    $emotionsEmojiDict = [
        "anger"    => "😡",
        "disgust"  => "🤮",
        "fear"     => "😨",
        "sad"      => "😢",
        "surprise" => "😮",
    ];

    while ($row = mysqli_fetch_assoc($query)) {
        $msgId  = $row['msgId'];
        $isMine = ($row['outgoing_msg_id'] === $outgoing_id);

        $msgTimeFormatted = !is_null($row['msg_time'])
            ? date("H:i", strtotime($row['msg_time']))
            : '00:00';

        // 這支沒有系統情緒；manual 從 affectlabel.affectEmo 來
        $manualKey   = $row['affectEmo'] ?? null;
        $moodtagKey  = null; // 無系統情緒
        $manualEmoji = ($manualKey && isset($emotionsEmojiDict[$manualKey])) ? $emotionsEmojiDict[$manualKey] : '';
        $moodEmoji   = ''; // 沒有系統情緒

        // 訊息內容做跳脫
        $msgText = htmlspecialchars($row['msg'] ?? '', ENT_QUOTES);
        $imgPath = htmlspecialchars($row['img'] ?? '', ENT_QUOTES);

        if ($isMine) {
            $output .= '<div id="'.$msgId.'" class="chat outgoing" data-msg-id="'.$msgId.'">';
            $output .= '  <div class="details">';
            $output .= '    <span class="time">'.$msgTimeFormatted.'</span>';
            $output .= '    <img class="smile" src="images/smile.png" alt="Smile">';
            $output .= '    <p>'.$msgText.'</p>';
            $output .= '  </div>';
        } else {
            $output .= '<div id="'.$msgId.'" class="chat incoming" data-msg-id="'.$msgId.'">';
            $output .= '  <div class="details">';
            $output .= '    <img class="profile round" src="images/'.$imgPath.'" alt="">';
            $output .= '    <p>'.$msgText.'</p>';
            $output .= '    <div class="read-status">';
            $output .= '      <span class="read">已讀</span>';
            $output .= '      <span class="time">'.$msgTimeFormatted.'</span>';
            $output .= '      <img class="smile" src="images/smile.png" alt="Smile">';
            $output .= '    </div>';
            $output .= '  </div>';
        }

        // 隱藏 affect 結構（右側鏡射用）
        $output .= '  <div class="affect labels"'
                .  ' data-manual="'.htmlspecialchars($manualKey ?? '', ENT_QUOTES).'"'
                .  ' data-moodtag="'.htmlspecialchars($moodtagKey ?? '', ENT_QUOTES).'"'
                .  ' style="display:none">';
        $output .= '    <span class="affect-label manual">'.$manualEmoji.'</span>';
        $output .= '    <span class="affect-label moodtag">'.$moodEmoji.'</span>';
        $output .= '  </div>';

        $output .= '</div>'; // .chat
    }
} else {
    $output .= '<div id="noMessages" class="text">No messages are available. Once you send message they will appear here.</div>';
}

echo $output;
