<?php
// === 安全取得參數 ===
// 這兩個是用在列表與 chat.php 的 query string，所以從 $_GET 拿
$mode     = isset($_POST['mode']) ? trim($_POST['mode']) : '';
$practice = isset($_POST['practice']) ? (int)$_POST['practice'] : null;

// 避免 SQL 注入（字串才需要 escape）
$mode_esc = $mode !== '' ? mysqli_real_escape_string($conn, $mode) : '';

// 目前登入者 id（原本程式通常是這樣來的，保險再轉 int）
$outgoing_id = isset($_SESSION['unique_id']) ? (int)$_SESSION['unique_id'] : 0;

// === 產生列表 ===
while ($row = mysqli_fetch_assoc($query)) {
    $peerId = isset($row['unique_id']) ? (int)$row['unique_id'] : 0;

    // 動態組 WHERE：只有在有值時才加條件，避免 SQL 語法缺值
    $extra = '';
    if ($mode_esc !== '') {
        $extra .= " AND pattern = '{$mode_esc}'";
    }
    if ($practice !== null) {
        $extra .= " AND isPractice = {$practice}";
    }

    // 查兩人之間的最後一則訊息
    $sql2 = "
        SELECT *
        FROM messages
        WHERE
            (incoming_msg_id = {$peerId} OR outgoing_msg_id = {$peerId})
        AND (outgoing_msg_id = {$outgoing_id} OR incoming_msg_id = {$outgoing_id})
        {$extra}
        ORDER BY msg_id DESC
        LIMIT 1
    ";

    $query2 = mysqli_query($conn, $sql2);

    // 預設
    $result = "此聊天室無訊息";
    $row2   = null;

    if ($query2 && mysqli_num_rows($query2) > 0) {
        $row2 = mysqli_fetch_assoc($query2);
        if (strpos($row2['msg'] ?? '', '<img') !== false) {
            $result = "傳送一張圖片";
        } else {
            $result = $row2['msg'] ?? $result;
        }
    }

    // 截斷訊息避免撐版
    $msg = (mb_strlen($result, 'UTF-8') > 28)
        ? mb_substr($result, 0, 28, 'UTF-8') . '...'
        : $result;

    // 是否自己發的
    if (!empty($row2['outgoing_msg_id'])) {
        $you = ((int)$outgoing_id === (int)$row2['outgoing_msg_id']) ? "You: " : "";
    } else {
        $you = "";
    }

    // 狀態 & 自己隱藏
    $offline = ($row['status'] ?? '') === "Offline now" ? "offline" : "";
    $hid_me  = ((int)$outgoing_id === (int)$peerId) ? "hide" : "";

    // 把 mode/practice 原樣帶回去（沒帶就不要出現在 URL）
    $qs = http_build_query(array_filter([
        'user_id'  => $peerId,
        'mode'     => $mode !== '' ? $mode : null,
        'practice' => $practice !== null ? $practice : null,
    ], fn($v) => $v !== null));

    $output .= '
        <a href="php/chat.php?' . $qs . '" class="' . $hid_me . '">
            <div class="content">
                <img src="php/images/' . htmlspecialchars($row['img'] ?? '', ENT_QUOTES) . '" alt="">
                <div class="details">
                    <span>' . htmlspecialchars(($row['lname'] ?? '') . ' ' . ($row['fname'] ?? ''), ENT_QUOTES) . '</span>
                    <p>' . htmlspecialchars($you . $msg, ENT_QUOTES) . '</p>
                </div>
            </div>
            <div class="status-dot ' . $offline . '"><i class="fas fa-circle"></i></div>
        </a>';
}
?>
