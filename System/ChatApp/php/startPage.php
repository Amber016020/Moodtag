<?php 
  session_start();
  include_once "php/config.php";
  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
  }
?>
<?php include_once "php/header.php"; ?>
<body>
  <div class="wrapper">
    <section class="users">
      <header>
      <style>
        /* 設置容器樣式，確保其子元素居中 */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* 全屏高度 */
            margin: 0; /* 去除預設的邊距 */
        }

        /* 設置<a>元素為行內塊，這樣它可以包含按鈕 */
        #nextModeLink {
            display: ruby-text;
        }

        /* 設置按鈕樣式 */
        #stratButton {
            font-size: 30px; /* 放大字體 */
            padding: 20px 30px; /* 增加內邊距 */
            cursor: pointer; /* 添加指針樣式 */
            display: block;
            margin: 0 auto;
            background-color: #60b1d3;
            color: white;
            border: none;
            border-radius: 4px;
        }

        #stratButton:hover {
            background-color: #467b91;
        }
    </style>
        <div class="content">
          <?php 
            $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
            if(mysqli_num_rows($sql) > 0){
              $row = mysqli_fetch_assoc($sql);
            }
          ?>
          <img src="php/images/<?php echo $row['img']; ?>" alt="">
          <div class="details">
            <span><?php echo $row['lname']. " " . $row['fname'] ?></span>
            <p><?php echo $row['status']; ?></p>
          </div>
        </div>
        <a href="php/logout.php?logout_id=<?php echo $row['unique_id']; ?>" class="logout"><?php echo $lang['logout']; ?></a>
      </header>
      <?php if (isset($_SESSION['isExperimenter']) && $_SESSION['isExperimenter'] == 1): ?>
        <a href="php/monitor.php" class="logout"><?php echo $lang['monitor']; ?></a>
      <?php endif; ?>      
      <div style="display: grid;">
        <a id="nextModeLink" href="#">
          <button id="stratButton">開始練習</button>
        </a>        
      </div>


    </section>
  </div>

  <script src="javascript/startPage.js"></script>

</body>
</html>
