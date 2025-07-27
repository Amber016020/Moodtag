<?php 
session_start();
include_once "config.php";
if (!isset($_SESSION['unique_id'])) {
    header("location: ../login.php");
}
?>

<body>
<h1>受測者管理系統</h1>

<!-- ========== PARTNER 區塊 ========== -->
<h2>Partner 資料</h2>
<form method="post">
  <input type="number" name="partner_user_id" placeholder="user_id" required>
  <input type="number" name="partner_groupNumber" placeholder="groupNumber" required>
  <button type="submit" name="add_partner">新增</button>
</form>

<?php
// 新增
if (isset($_POST['add_partner'])) {
    $uid = $_POST['partner_user_id'];
    $group = $_POST['partner_groupNumber'];
    mysqli_query($conn, "INSERT INTO partner (user_id, groupNumber) VALUES ('$uid', '$group')");
}

// 刪除
if (isset($_GET['delete_partner'])) {
    $id = $_GET['delete_partner'];
    mysqli_query($conn, "DELETE FROM partner WHERE user_id = '$id'");
}

// 編輯表單
$edit_partner = null;
if (isset($_GET['edit_partner'])) {
    $edit_id = $_GET['edit_partner'];
    $res = mysqli_query($conn, "SELECT * FROM partner WHERE user_id = '$edit_id'");
    $edit_partner = mysqli_fetch_assoc($res);
}
if ($edit_partner) {
    echo "<h3>編輯 Partner</h3>
    <form method='post'>
      <input type='hidden' name='edit_partner_user_id' value='{$edit_partner['user_id']}'>
      <input type='number' name='edit_partner_groupNumber' value='{$edit_partner['groupNumber']}' required>
      <button type='submit' name='update_partner'>更新</button>
    </form>";
}
if (isset($_POST['update_partner'])) {
    $uid = $_POST['edit_partner_user_id'];
    $group = $_POST['edit_partner_groupNumber'];
    mysqli_query($conn, "UPDATE partner SET groupNumber='$group' WHERE user_id='$uid'");
}

// 顯示
$partner_result = mysqli_query($conn, "SELECT * FROM partner ORDER BY groupNumber");
echo "<table border='1'><tr><th>user_id</th><th>groupNumber</th><th>操作</th></tr>";
while ($row = mysqli_fetch_assoc($partner_result)) {
    echo "<tr>
        <td>{$row['user_id']}</td>
        <td>{$row['groupNumber']}</td>
        <td>
          <a href='?edit_partner={$row['user_id']}'>修改</a> | 
          <a href='?delete_partner={$row['user_id']}'>刪除</a>
        </td>
      </tr>";
}
echo "</table>";
?>

<!-- ========== TESTSEQUENCE 區塊 ========== -->
<h2>TestSequence 題目管理</h2>
<form method="post">
  <input type="number" name="groupNumber" placeholder="groupNumber" required>
  <input type="text" name="pattern" placeholder="pattern" required>
  <input type="text" name="topic" placeholder="topic" required>
  <input type="number" name="sortOrder" placeholder="sortOrder" required>
  <button type="submit" name="add_sequence">新增題目</button>
</form>

<?php
if (isset($_POST['add_sequence'])) {
    $g = $_POST['groupNumber'];
    $p = $_POST['pattern'];
    $t = $_POST['topic'];
    $s = $_POST['sortOrder'];
    mysqli_query($conn, "INSERT INTO testsequence (groupNumber, pattern, topic, sortOrder) VALUES ('$g', '$p', '$t', '$s')");
}

if (isset($_GET['delete_sequence_group']) && isset($_GET['delete_sequence_sort'])) {
    $group = $_GET['delete_sequence_group'];
    $sort = $_GET['delete_sequence_sort'];
    mysqli_query($conn, "DELETE FROM testsequence WHERE groupNumber = '$group' AND sortOrder = '$sort'");
}

$edit_seq = null;
if (isset($_GET['edit_sequence_group']) && isset($_GET['edit_sequence_sort'])) {
    $group = $_GET['edit_sequence_group'];
    $sort = $_GET['edit_sequence_sort'];
    $res = mysqli_query($conn, "SELECT * FROM testsequence WHERE groupNumber = '$group' AND sortOrder = '$sort' LIMIT 1");
    $edit_seq = mysqli_fetch_assoc($res);
}
if ($edit_seq) {
    echo "<h3>編輯 TestSequence</h3>
    <form method='post'>
      <input type='hidden' name='edit_groupNumber' value='{$edit_seq['groupNumber']}'>
      <input type='hidden' name='original_sortOrder' value='{$edit_seq['sortOrder']}'>
      <input type='text' name='edit_pattern' value='{$edit_seq['pattern']}' required>
      <input type='text' name='edit_topic' value='{$edit_seq['topic']}' required>
      <input type='number' name='edit_sortOrder' value='{$edit_seq['sortOrder']}' required>
      <button type='submit' name='update_sequence'>更新</button>
    </form>";
}
if (isset($_POST['update_sequence'])) {
    $g = $_POST['edit_groupNumber'];
    $p = $_POST['edit_pattern'];
    $t = $_POST['edit_topic'];
    $s = $_POST['edit_sortOrder'];
    $original_sort = $_POST['original_sortOrder'];

    mysqli_query($conn, "UPDATE testsequence 
        SET pattern='$p', topic='$t', sortOrder='$s' 
        WHERE groupNumber='$g' AND sortOrder='$original_sort'");
}

$seq_result = mysqli_query($conn, "SELECT * FROM testsequence ORDER BY groupNumber, sortOrder");
echo "<table border='1'><tr><th>groupNumber</th><th>pattern</th><th>topic</th><th>sortOrder</th><th>操作</th></tr>";
while ($row = mysqli_fetch_assoc($seq_result)) {
    echo "<tr>
        <td>{$row['groupNumber']}</td>
        <td>{$row['pattern']}</td>
        <td>{$row['topic']}</td>
        <td>{$row['sortOrder']}</td>
        <td>
            <a href='?edit_sequence_group={$row['groupNumber']}&edit_sequence_sort={$row['sortOrder']}'>修改</a> |
            <a href='?delete_sequence_group={$row['groupNumber']}&delete_sequence_sort={$row['sortOrder']}'>刪除</a>
        </td>
      </tr>";
}
echo "</table>";
?>

<!-- ========== USERS 區塊 ========== -->
<h2>使用者管理</h2>
<form method="post">
  <input type="number" name="user_id" placeholder="user_id" required>
  <input type="text" name="unique_id" placeholder="unique_id" required>
  <input type="text" name="fname" placeholder="fname" required>
  <input type="text" name="lname" placeholder="lname" required>
  <input type="text" name="account" placeholder="account" required>
  <input type="password" name="password" placeholder="password" required>
  <select name="isExperimenter" required>
    <option value="0">受測者（0）</option>
    <option value="1">實驗者（1）</option>
  </select>
  <input type="text" name="img" placeholder="img（ex: man.png）">
  <input type="text" name="status" placeholder="status（ex: Active now）">
  <button type="submit" name="add_user">新增使用者</button>
</form>

<?php
if (isset($_POST['add_user'])) {
    $id = $_POST['user_id'];
    $unique_id = $_POST['unique_id'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $account = $_POST['account'];
    $pwd = $_POST['password'];
    $encrypt_pwd = md5($pwd);  // ✅ 使用 MD5 加密
    $isExp = $_POST['isExperimenter'];
    $img = $_POST['img'];
    $status = $_POST['status'];

    mysqli_query($conn, "INSERT INTO users 
    (user_id, unique_id, fname, lname, account, password, isExperimenter, img, status) 
    VALUES ('$id', '$unique_id', '$fname', '$lname', '$account', '$encrypt_pwd', '$isExp', '$img', '$status')");
}

if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id = '$id'");
}

$edit_user = null;
if (isset($_GET['edit_user'])) {
    $uid = $_GET['edit_user'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$uid'");
    $edit_user = mysqli_fetch_assoc($res);
}
if ($edit_user) {
    echo "<h3>編輯使用者</h3>
    <form method='post'>
      <input type='hidden' name='edit_user_id' value='{$edit_user['user_id']}'>
      <label>unique_id: <input type='text' name='edit_unique_id' value='{$edit_user['unique_id']}' required></label><br>
      <label>fname: <input type='text' name='edit_fname' value='{$edit_user['fname']}' required></label><br>
      <label>lname: <input type='text' name='edit_lname' value='{$edit_user['lname']}' required></label><br>
      <label>account: <input type='text' name='edit_account' value='{$edit_user['account']}' required></label><br>
      <label>password: <input type='password' name='edit_password' placeholder='不修改則留空'></label><br>
      <label>isExperimenter: 
        <select name='edit_isExperimenter'>
          <option value='0' " . ($edit_user['isExperimenter'] == 0 ? "selected" : "") . ">0</option>
          <option value='1' " . ($edit_user['isExperimenter'] == 1 ? "selected" : "") . ">1</option>
        </select>
      </label><br>
      <label>img: <input type='text' name='edit_img' value='{$edit_user['img']}'></label><br>
      <label>status: <input type='text' name='edit_status' value='{$edit_user['status']}'></label><br>
      <button type='submit' name='update_user'>更新</button>
    </form>";
}
if (isset($_POST['update_user'])) {
    $id = $_POST['edit_user_id'];
    $unique_id = $_POST['edit_unique_id'];
    $fname = $_POST['edit_fname'];
    $lname = $_POST['edit_lname'];
    $account = $_POST['edit_account'];
    $pwd = $_POST['edit_password'];
    $isExp = $_POST['edit_isExperimenter'];
    $img = $_POST['edit_img'];
    $status = $_POST['edit_status'];

    if ($pwd) {
        mysqli_query($conn, "UPDATE users SET 
            unique_id='$unique_id',
            fname='$fname',
            lname='$lname',
            account='$account',
            password='$pwd',
            isExperimenter='$isExp',
            img='$img',
            status='$status'
            WHERE user_id='$id'");
    } else {
        mysqli_query($conn, "UPDATE users SET 
            unique_id='$unique_id',
            fname='$fname',
            lname='$lname',
            account='$account',
            isExperimenter='$isExp',
            img='$img',
            status='$status'
            WHERE user_id='$id'");
    }
}

$user_result = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id");
echo "<table border='1'>
<tr>
  <th>user_id</th>
  <th>unique_id</th>
  <th>fname</th>
  <th>lname</th>
  <th>account</th>
  <th>password</th>
  <th>isExperimenter</th>
  <th>img</th>
  <th>status</th>
  <th>操作</th>
</tr>";

while ($row = mysqli_fetch_assoc($user_result)) {
    echo "<tr>
        <td>{$row['user_id']}</td>
        <td>{$row['unique_id']}</td>
        <td>{$row['fname']}</td>
        <td>{$row['lname']}</td>
        <td>{$row['account']}</td>
        <td>{$row['password']}</td>
        <td>{$row['isExperimenter']}</td>
        <td>{$row['img']}</td>
        <td>{$row['status']}</td>
        <td>
          <a href='?edit_user={$row['user_id']}'>修改</a> |
          <a href='?delete_user={$row['user_id']}'>刪除</a>
        </td>
      </tr>";
}
echo "</table>";
?>

</body>
</html>
