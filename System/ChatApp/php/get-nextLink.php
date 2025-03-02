<?php
    session_start();
    include_once "config.php";
    
    $mode = $_POST['mode'] ?? '';
    $practice = ($_POST['practice'] === true || $_POST['practice'] === 'true' || $_POST['practice'] === 1 || $_POST['practice'] === '1') ? 1 : 0;

    $outgoing_id = isset($_POST['outgoing_id']) ? $_POST['outgoing_id'] : $_SESSION['unique_id'];
    $sql = "SELECT * FROM users INNER JOIN partner p1 ON users.unique_id = p1.user_id  
    WHERE NOT unique_id = {$outgoing_id} 
    AND p1.groupNumber = (
        SELECT p2.groupNumber 
        FROM partner p2
        WHERE p2.user_id = {$outgoing_id})";

    $query = mysqli_query($conn, $sql);
    $output = "";
    if(mysqli_num_rows($query) == 0){
        $output .= "No users are available to chat";
    }elseif(mysqli_num_rows($query) > 0){
        while($row = mysqli_fetch_assoc($query)){
            if ($practice) {
                switch ($mode) {
                    // self -> Moodtag
                    case 'self-affect':
                        $mode = 'Moodtag';
                        break;
                    // Moodtag -> 正式開始
                    case 'Moodtag':
                        $practice = false;
                        $mode = 'neutral';
                        break;
                    // 從一開始 -> self
                    default:
                        $mode = 'self-affect';
                        break;
                }
            }
    
            // 當不是練習時，從 testsequence 表中獲取模式
            else {
                $groupNumber = $row['groupNumber'];
                $sql2 = "SELECT * FROM testsequence WHERE groupNumber={$groupNumber} ORDER BY sortOrder";
                $query2 = mysqli_query($conn, $sql2);
                $isCurrent = false;
    
                while ($row2 = mysqli_fetch_assoc($query2)) {
                    // 平靜模式 -> C1
                    if ($mode == 'neutral') {
                        $mode = $row2['pattern'];
                        break;
                    } 
                    // 替換至下一模式
                    elseif ($isCurrent) {
                        $mode = $row2['pattern'];
                        break;
                    }
                    // 確認是否為當前模式
                    if ($mode == $row2['pattern']) {
                        $isCurrent = true;
                    }
                }
            }
            $practiceValue = $practice ? 'true' : 'false';
            $output .= 'php/chat.php?user_id='. $row['unique_id'] .'&mode=' . $mode .'&practice=' . $practiceValue .'';
        }
    }
    echo $output;
    
?>