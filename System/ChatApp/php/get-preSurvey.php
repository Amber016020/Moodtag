<?php 
    session_start();

    // 設定語言
    $language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
    include_once "../lang/$language.php";
    include_once "config.php";

    // 接收來自 POST 的 JSON 字符串並解析
    $data = json_decode($_POST['data'], true);
    $user_ids = $data['user_ids'];  // 這是一個用戶 ID 陣列
    $mode = $data['mode'];          // 模式
    $isPractice = $data['isPractice']; // 是否是練習模式
    $surveys = $data['surveys'];    // 問卷的 scaleName 和 testOrder 的數組

    $output = [];

    // 遍歷每個 user_id 並查詢多個問卷的資料
    foreach ($user_ids as $user_id) {
        $user_output = ['surveys' => []];

        foreach ($surveys as $survey) {
            $scaleName = $survey['scaleName'];
            $testOrder = $survey['testOrder'];

            $sql = "SELECT * FROM survey WHERE user_id = {$user_id} 
                        AND scaleName = '{$scaleName}' 
                        AND testOrder = '{$testOrder}'";

            // 如果是 'PANAS' 或 'PAQ'，則需要加上 mode 條件
            if (in_array($scaleName, ['PANAS', 'PAQ'])) {
                $sql .= " AND mode = '{$mode}'";
            }
            $query = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($query) > 0) {
                $responses = [];
                while ($row = mysqli_fetch_assoc($query)) {
                    $responses[] = $row['response'];  // 收集該用戶該問卷的所有回應
                }

                // 如果有資料，將其標記為 "Data available"
                $user_output['surveys'][] = [
                    'scaleName' => $scaleName,
                    'testOrder' => $testOrder,
                    'responses' => $responses,
                    'status' => 'Data available'
                ];
            } else {
                // 沒有資料的情況
                $user_output['surveys'][] = [
                    'scaleName' => $scaleName,
                    'testOrder' => $testOrder,
                    'status' => 'No data available'
                ];
            }
        }

        $output[$user_id] = $user_output;
    }

    // 將結果以 JSON 格式返回
    echo json_encode($output);
?>
