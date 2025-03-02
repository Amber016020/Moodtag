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
        $sql = "SELECT *,messages.msg_id as msgId FROM messages 
                LEFT JOIN users ON users.unique_id = messages.outgoing_msg_id
                LEFT JOIN affectlabel ON affectlabel.user_id = {$outgoing_id} AND messages.msg_id = affectlabel.msg_id 
                AND affectlabel.emo_del_time IS NULL
                WHERE ((outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
                OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id})) AND pattern = '{$mode}' AND isPractice = '{$isPractice}' ORDER BY messages.msg_id";

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

                $emojisAffect = [
                    "anger" => "😡",
                    "disgust" => "🤮",
                    "fear" => "😨",
                    "sad" => "😢",
                    "surprise" => "😮"                     
                ];
                if($row['outgoing_msg_id'] === $outgoing_id){
                    $output .= '<div id="' . $row['msgId'] . '" class="chat outgoing">
                                    <div class="details">
                                        <span class="time">' . $msgTimeFormatted . '</span>
                                        <img class="smile" src="picture/smile.png" alt="Smile">
                                        <p>' . $row['msg'] ;

                    $output .= '</p></div><div id="affectLabel" class="affect outgoing"></div></div>';
                }else{
                    $output .= '<div id="' . $row['msgId'] . '" class="chat incoming">
                                    <div class="details">
                                        <img class="profile round" src="php/images/'.$row['img'].'" alt=""> 
                                        <p>' . $row['msg'];

                    $output .= '<div class="read-status">
                                    <span class="read">已讀</span>
                                    <span class="time">' . $msgTimeFormatted . '</span>
                                </div>
                                <img class="smile" src="picture/smile.png" alt="Smile">
                                </div>';

                    $output .= '<div id="affectLabel" class="affect incoming"></div></div>';
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