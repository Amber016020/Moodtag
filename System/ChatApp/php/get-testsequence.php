<?php
    session_start();
    include_once "config.php";

    $user_id = $_POST['user_id'];

    $sql = "SELECT * FROM testsequence 
            INNER JOIN partner ON testsequence.groupNumber = partner.groupNumber 
            WHERE partner.user_id = {$user_id}";
    $query = mysqli_query($conn, $sql);

    $data = array();

    if (mysqli_num_rows($query) > 0) {
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row; // 將每一行的數據添加到數組中
        }
    }

    // 回傳 JSON 格式的數據
    echo json_encode($data);
?>