<?php 
  session_start();
  if(isset($_SESSION['unique_id'])){
    header("location: users.php");
  }
?>

<?php include_once "php/header.php"; ?>
<body>
  <div class="wrapper">
    <section class="form login">
      <header><?php echo $lang['title']; ?></header>
      <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="error-text"></div>
        <div class="field input">
          <label><?php echo $lang['account_label']; ?></label>
          <input type="text" name="account" placeholder="<?php echo $lang['account_placeholder']; ?>" required>
        </div>
        <div class="field input">
          <label><?php echo $lang['password_label']; ?></label>
          <input type="password" name="password" placeholder="<?php echo $lang['password_placeholder']; ?>" required>
          <i class="fas fa-eye"></i>
        </div>
        <div class="field button">
          <input type="submit" name="submit" value="<?php echo $lang['continue_button']; ?>">
        </div>
      </form>
      <div class="link"><?php echo $lang['signup_link']; ?><a href="signup.php"><?php echo $lang['signup_now']; ?></a></div>
    
      <form class="lang" action="php/switch_language.php" method="GET">
        <label for="language"><?php echo $lang['Select_Language']; ?></label>
        <select name="language" id="language" onchange="this.form.submit()">
          <option value="en" <?php echo ($language == 'en') ? 'selected' : ''; ?>>English</option>
          <option value="zh" <?php echo ($language == 'zh') ? 'selected' : ''; ?>>中文</option>
        </select>
      </form>
    </section>
  </div>

  
  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/login.js"></script>

</body>
</html>
