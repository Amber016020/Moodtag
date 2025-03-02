<?php
// 檢查是否有POST請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg'])) {
    // 接收POST請求中的msg參數
    $msg = $_POST['msg'];
// API端點和API密鑰
$api_url = "https://api.coze.com/open_api/v2/chat";
$api_key = "pat_WDFk4DI6kgBxCbUoi6KtyY05lKobhCpWeqggQl4XNWn2x0hlqnKo5zYL6rqw5DHR";

// 請求體
$data = array(
    "conversation_id" => "123",
    "bot_id" => "7378051227638644752",
    "user" => "123",
    "query" =>  $msg,
    "stream" => false
);

// 初始化cURL會話
$ch = curl_init($api_url);

// 設置cURL選項
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json',
    'Accept: */*',
    'Connection: keep-alive'
));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// 發送API請求並獲取響應
$response = curl_exec($ch);

// 檢查是否有錯誤
if(curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}

// 關閉cURL會話
curl_close($ch);

// 處理API響應
$response_data = json_decode($response, true);

// 初始化情緒標籤對象
$emotion_tags = new stdClass();
$emotion_tags->subjective = "";
$emotion_tags->objective = "";

// 遍歷API響應中的消息，提取情緒標籤
if (isset($response_data['messages']) && is_array($response_data['messages'])) {
    foreach ($response_data['messages'] as $message) {
        if (isset($message['role']) && $message['role'] == 'assistant' && isset($message['content'])) {
            // 假設情緒標籤包含在content字段中
            if (strpos($message['content'], '主觀情緒標籤') !== false || strpos($message['content'], 'OA') !== false) {
                preg_match('/主觀情緒標籤：([^\\n]+)/', $message['content'], $matches);
                preg_match('/OA：?"([^"]+)"/', $message['content'], $matches);
                if (isset($matches[1])) {
                    $emotion_tags->subjective = trim($matches[1]);
                }
            }
            if (strpos($message['content'], '客觀情緒標籤') !== false || strpos($message['content'], 'SA') !== false) {
                preg_match('/客觀情緒標籤：([^\\n]+)/', $message['content'], $matches);
                preg_match('/SA：?"([^"]+)"/', $message['content'], $matches);
                if (isset($matches[1])) {
                    $emotion_tags->objective = trim($matches[1]);
                }
            }
        }
    }
}

// 設置返回的HTTP頭為JSON
header('Content-Type: application/json');

// 輸出情緒標籤對象的JSON格式
echo json_encode($emotion_tags);
} else {
    echo 'No message provided';
}
?>