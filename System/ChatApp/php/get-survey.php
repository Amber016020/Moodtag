<?php 
    session_start();

        $language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
        include_once "../lang/$language.php";
        include_once "config.php";
        $user_id = $_POST['user_id'];
        $mode = $_POST['mode'];
        $ABCCT = "";
        $PAQ = "";
        $basePANAS = "";
        $prePANAS = "";
        $postPANAS = "";
        $TPA = "";
        $Preference = "";
        $output = "";
        $sql = "SELECT * FROM survey WHERE mode = '{$mode}' AND user_id = {$user_id} ORDER BY scaleName,testOrder,question_number";

        $query = mysqli_query($conn, $sql);
        if(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){
                if($row['scaleName'] == 'ABCCT'){
                    $ABCCT .= $row['response'] . " ";
                }
                else if($row['scaleName'] == 'PAQ'){
                    $PAQ .= $row['response'] . " ";
                }
                else if($row['scaleName'] == 'PANAS'){
                    if($row['testOrder'] == 'pre' ){
                        $prePANAS .= $row['response'] . " ";
                    }
                    else  if($row['testOrder'] == 'base' ){
                        $basePANAS .= $row['response'] . " ";
                    }
                    else{
                        $postPANAS .= $row['response'] . " ";                        
                    }
                }
                else if($row['scaleName'] == 'TPA'){
                    $TPA .= $row['response'] . " ";
                }
                else if($row['scaleName'] == 'Preference'){
                    $Preference .= $row['response'] . " ";
                }
            }
            $output .= "PAQ：" . $PAQ . "<br>" .
                       "basePANAS：" . $basePANAS ."<br>" .
                       "prePANAS：" . $prePANAS . "<br>" .
                       "postPANAS：" . $postPANAS . "<br>";
        }else{
            $output .= '<div id="noMessages" class="text">沒有問卷回復資料</div>';
        }

        $sql = "SELECT * FROM survey WHERE scaleName in ('ABCCT','TPA','Preference') AND user_id = {$user_id} ORDER BY scaleName,testOrder,question_number";

        $query = mysqli_query($conn, $sql);
        if(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){
                if($row['scaleName'] == 'ABCCT'){
                    $ABCCT .= $row['response'] . " ";
                }
                else if($row['scaleName'] == 'TPA'){
                    $TPA .= $row['response'] . " ";
                }
                else if($row['scaleName'] == 'Preference'){
                    $Preference .= $row['response'] . " ";
                }
            }
            $output .= "ABCCT：" . $ABCCT . "<br>" .
                       "TPA：" . $TPA . "<br>" .
                       "Preference：" . $Preference;
        }
        echo $output;
?>