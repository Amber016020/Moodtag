<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// === 讀取設定檔（返回陣列）===
$configPath = __DIR__ . '/../config.php';
$config = is_file($configPath) ? include $configPath : null;

if (!is_array($config)) {
    http_response_code(500);
    echo json_encode(['error' => 'Missing or invalid config.php']);
    exit;
}

$apiKey = trim((string)($config['openai_api_key'] ?? ''));
if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(['error' => 'openai_api_key is empty in config.php']);
    exit;
}

// === 讀原始 body ===
$input = file_get_contents('php://input');
if ($input === false || $input === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty request body']);
    exit;
}

// === 轉發到 OpenAI ===
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS     => $input,
    CURLOPT_TIMEOUT        => 45,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$response = curl_exec($ch);
$curlErr  = curl_error($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'cURL error: ' . $curlErr]);
    exit;
}

http_response_code($httpCode ?: 200);
echo $response;
