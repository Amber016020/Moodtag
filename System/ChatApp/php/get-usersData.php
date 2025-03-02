<?php
    session_start();
    include_once "config.php";

    $users = [];
    if (isset($_POST['userIds'])) {
        $userIds = json_decode($_POST['userIds'], true);
        foreach ($userIds as $unique_id) {
            $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id=$unique_id");
            if(mysqli_num_rows($sql) > 0){
                $users[] = mysqli_fetch_assoc($sql);
            }
        }
    }

    // 输出结果
    echo json_encode($users);
?>
