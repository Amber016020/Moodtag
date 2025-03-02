<?php
    session_start();
    include_once "config.php";

    // 初始化输出变量
    $output = '';

    // 执行第一个查询
    $sql = "SELECT p.groupNumber, GROUP_CONCAT(u.unique_id ORDER BY u.unique_id) AS usersID 
                   , GROUP_CONCAT(u.lname,u.fname ORDER BY u.unique_id) AS usersName
            FROM partner p
            JOIN users u ON p.user_id = u.unique_id
            GROUP BY p.groupNumber;";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<option value="' .  $row['usersID'] .'">';
            $output .= '組別' .  $row['groupNumber'] .'：';
            $output .= $row['usersName'];
        }
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    // 输出结果
    echo $output;
?>
