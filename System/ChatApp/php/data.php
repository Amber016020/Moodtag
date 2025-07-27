<?php
    while($row = mysqli_fetch_assoc($query)){
        $sql2 = "SELECT * FROM messages 
                WHERE (incoming_msg_id = {$row['unique_id']} OR outgoing_msg_id = {$row['unique_id']}) 
                AND (outgoing_msg_id = {$outgoing_id}  OR incoming_msg_id = {$outgoing_id}) 
                AND pattern = '{$mode}' 
                AND isPractice = {$practice}                
                ORDER BY msg_id DESC LIMIT 1";

        $query2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($query2);
        if (mysqli_num_rows($query2) > 0) {
            if (strpos($row2['msg'], '<img') !== false) {
                $result = "傳送一張圖片";
            } else {
                $result = $row2['msg'];
            }
        } else {
            $result = "此聊天室無訊息";
            // $result = "No message available";
        }

        (strlen($result) > 28) ? $msg =  substr($result, 0, 28) . '...' : $msg = $result;
        if(isset($row2['outgoing_msg_id'])){
            ($outgoing_id == $row2['outgoing_msg_id']) ? $you = "You: " : $you = "";
        }else{
            $you = "";
        }
        ($row['status'] == "Offline now") ? $offline = "offline" : $offline = "";
        ($outgoing_id == $row['unique_id']) ? $hid_me = "hide" : $hid_me = "";

        $output .= '<a href="chat.php?user_id='. $row['unique_id'] .'&mode=' . $mode .'&practice=' . $practice .'">
                    <div class="content">
                    <img src="images/'. $row['img'] .'" alt="">
                    <div class="details">
                        <span>'. $row['lname']. " " . $row['fname'] .'</span>
                        <p>'. $you . $msg .'</p>
                    </div>
                    </div>
                    <div class="status-dot '. $offline .'"><i class="fas fa-circle"></i></div>
                </a>';
    }
?>