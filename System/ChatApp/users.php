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
    <div class="mode-select-wrapper">
        <label for="modeSelect"><?php echo $lang['select_mode']; ?>：</label>
        <select id="modeSelect">
          <option value="neutral"><?php echo $lang['neutral']; ?></option>
          <option value="control"><?php echo $lang['control']; ?></option>
          <option value="self-affect"><?php echo $lang['self-affect']; ?></option>
          <option value="Moodtag"><?php echo $lang['Moodtag']; ?></option>
        </select>
      </div>
      <div style="text-align: right; margin-right: 20px;">
        <label for="practiceCheckbox"><?php echo $lang['practice']; ?></label>
        <input type="checkbox" id="practiceCheckbox" name="practiceCheckbox">
      </div>
      <div class="search">
        <span class="text"><?php echo $lang['select_user_to_start_chat']; ?></span>
        <input type="text" placeholder="<?php echo $lang['enter_name_to_search']; ?>">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="users-list">
  
      </div>
    </section>
  </div>

  <script src="javascript/users.js"></script>

</body>
</html>
