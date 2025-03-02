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
                -- 訊息是自己發的，但文字情緒標記兩邊都會出現
                LEFT JOIN affectlabel ON (affectlabel.user_id = {$outgoing_id} OR affectlabel.user_id = {$incoming_id})
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
                    "surprise" => "😱"
                ];

                $emotionsToCh = [
                    "anger" => "生氣",
                    "disgust" => "厭惡",
                    "fear" => "害怕",
                    "sad" => "傷心",
                    "surprise" => "驚訝"
                ];
                if($row['outgoing_msg_id'] === $outgoing_id){
                    $output .= '<div id="' . $row['msgId'] . '" class="chat outgoing">
                                    <div class="details">';
                    $output .= '<div id="affectLabelByHuman" class="outgoing human">';
                    if(isset($row['latest_emotion_by_human']) && isset($emotionsEmojiDict[$row['latest_emotion_by_human']])) {
                        $output .= $emotionsEmojiDict[$row['latest_emotion_by_human']];
                    } 
                    $output .= '</div><span class="time">' . $msgTimeFormatted . '</span>';

                    if(isset($row['latest_emotion']) && isset($emotionsEmojiDict[$row['latest_emotion']])) {
                        if($mode == 'OP'){
                            $output .= '<div id="emoTag" style="background-color: purple;color: white;font-family: ChenYuluoyan-Thin;">' . $emotionsToCh[$row['latest_emotion']] . '</div>';
                        }
                        else if ($mode == 'OM'){
                            $output .= '<div id="emoTag" style="background-color: purple;color: white;">' . $emotionsToCh[$row['latest_emotion']] . '</div>';
                        }
                    }

                    $output .= '<img class="smile" src="picture/smile.png" alt="Smile"><p>' . $row['msg'] ;
                    // todo：文字情緒辨識時，雙方都要insert affectlabel
                    if(isset($row['affectEmo']) && isset($emotionsEmojiDict[$row['affectEmo']])) {
                        $output .= '</p></div><div id="affectLabel" class="affect outgoing">' . $emotionsEmojiDict[$row['affectEmo']] . '</div></div>';
                    } else {
                        $output .= '</p></div><div id="affectLabel" class="affect outgoing"></div></div>';
                    }
                }else{
                    $output .= '<div id="' . $row['msgId'] . '" class="chat incoming">
                                    <div class="details">';
                    $output .= '<div id="affectLabelByHuman" class="outgoing human">';
                    if(isset($row['latest_emotion_by_human']) && isset($emotionsEmojiDict[$row['latest_emotion_by_human']])) {
                        $output .= $emotionsEmojiDict[$row['latest_emotion_by_human']];
                    } 
                    $output .= '</div><img class="profile round" src="php/images/'.$row['img'].'" alt="">
                                        <p>' . $row['msg'] . '</p>';
                                        
                    if(isset($row['latest_emotion']) && isset($emotionsEmojiDict[$row['latest_emotion']])) {
                        if($mode == 'OP'){
                            $output .= '<div id="emoTag" style="background-color: purple;color: white;font-family: ChenYuluoyan-Thin;font-size: 20px; ">' . $emotionsToCh[$row['latest_emotion']] . '</div>';
                        }
                        else if ($mode == 'OM'){
                            $output .= '<div id="emoTag" style="background-color: purple;color: white;">' . $emotionsToCh[$row['latest_emotion']] . '</div>';
                        }
                    }            
                    $output .= '<span class="time">' . $msgTimeFormatted . '</span><img class="smile" src="picture/smile.png" alt="Smile"></div>';
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
    }else{
        header("location: ../login.php");
    }

?>