<?php 
    session_start();

        $language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
        include_once "../lang/$language.php";
        include_once "config.php";
        $outgoing_id = $_POST['outgoing_id'];
        $incoming_id = $_POST['incoming_id'];
        $mode = $_POST['mode'];
        $isPractice = ($_POST['isPractice'] === true || $_POST['isPractice'] === 'true' || $_POST['isPractice'] === 1 || $_POST['isPractice'] === '1') ? 1 : 0;
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
                if (!is_null($row['msg_time'])) {
                    $msgTimeFormatted = date("H:i", strtotime($row['msg_time']));
                } else {
                    $msgTimeFormatted = '00:00'; 
                }

                $emotionsEmojiDict = [
                    "anger" => "😡",
                    "disgust" => "🤮",
                    "fear" => "😨",
                    "sad" => "😢",
                    "surprise" => "😮"
                ];

                if($row['outgoing_msg_id'] === $outgoing_id){
                    $output .= '<div id="' . $row['msgId'] . '" class="chat outgoing">';
                    $output .= '<div id="affectLabelByHuman" class="affect outgoing human">';
                    // 如果是自己有做人為標記的訊息，要在上面加上情緒標記
                    if(isset($row['latest_emotion_by_human']) && isset($emotionsEmojiDict[$row['latest_emotion_by_human']]) !== false) {
                        $output .= $emotionsEmojiDict[$row['latest_emotion_by_human']];
                    } 
                    $output .= '</div><div class="details"><span class="time">' . $msgTimeFormatted . '</span>';
                    // 如果有辨識情緒，要出現在訊息前方
                    if(isset($row['latest_emotion']) && isset($emotionsEmojiDict[$row['latest_emotion']])) {
                        $output .= '<div id="emoTag" style="font-size: 25px;">' . $emotionsEmojiDict[$row['latest_emotion']] . '</div>';
                    }                    
                    $output .= '<img class="smile" src="picture/smile.png" alt="Smile">';
                    // 如果有人為標記的訊息，訊息要加綠色外框
                    if(isset($row['latest_emotion_by_human']) && isset($emotionsEmojiDict[$row['latest_emotion_by_human']]) !== false) {
                        $output .= '<p style="outline: 4px solid #00bb8c">' . $row['msg'] ;
                    } 
                    else{
                        $output .= '<p>' . $row['msg'] ;
                    }
                    // 如果是自己有做情緒標記的訊息，要在下面加上情緒標記
                    if(isset($row['affectEmo']) && isset($emotionsEmojiDict[$row['affectEmo']])) {
                        $output .= '</p></div><div id="affectLabel" class="affect outgoing">' . $emotionsEmojiDict[$row['affectEmo']] . '</div></div>';
                    } else {
                        $output .= '</p></div><div id="affectLabel" class="affect outgoing"></div></div>';
                    }
                }else{
                    $output .= '<div id="' . $row['msgId'] . '" class="chat incoming">';
                    $output .= '<div id="affectLabelByHuman" class="affect incoming human">';
                    // 如果是自己有做人為標記的訊息，要在上面加上情緒標記
                    if(isset($row['latest_emotion_by_human']) && isset($emotionsEmojiDict[$row['latest_emotion_by_human']])) {
                        $output .= $emotionsEmojiDict[$row['latest_emotion_by_human']];
                    } 
                    $output .= '</div><div class="details"><img class="profile round" src="php/images/'.$row['img'].'" alt="">';

                    // 如果有人為標記的訊息，訊息要加綠色外框
                    if(isset($row['needOutLine']) && isset($emotionsEmojiDict[$row['needOutLine']]) !== false) {
                        $output .= '<p style="outline: 4px solid #00bb8c">' . $row['msg'] . '</p>';
                    } else{
                        $output .= '<p>' . $row['msg'] . '</p>';
                    }
                    // 如果有辨識情緒，要出現在訊息前方
                    if(isset($row['latest_emotion']) && isset($emotionsEmojiDict[$row['latest_emotion']])) {
                        $output .= '<div id="emoTag" style="font-size: 25px;">' . $emotionsEmojiDict[$row['latest_emotion']] . '</div>';
                    }            
                    
                    $output .= '<div class="read-status">
                                    <span class="read">已讀</span>
                                    <span class="time">' . $msgTimeFormatted . '</span>
                                </div>
                                <img class="smile" src="picture/smile.png" alt="Smile">
                                </div>';
                    // 如果是自己有做情緒標記的訊息，要在下面加上情緒標記
                    if(isset($row['affectEmo']) && isset($emotionsEmojiDict[$row['affectEmo']])) {
                        $output .= '<div id="affectLabel" class="affect incoming">' . $emotionsEmojiDict[$row['affectEmo']] . '</div></div>';
                    } else {
                        $output .= '<div id="affectLabel" class="affect incoming"></div></div>';
                    }
                }
            }
        }else{
            $output .= '<div id="noMessages" class="text">No messages are available. Once you send message they will appear here.</div>';
        }
        echo $output;
?>