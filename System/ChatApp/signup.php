<?php 
  session_start();
  if(isset($_SESSION['unique_id'])){
    header("location: users.php");
  }
?>

<?php include_once "php/header.php"; ?>
<body>
  <div class="wrapper">
    <section class="form signup">
      <header><?php echo $lang['title']; ?></header>
      <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="error-text"></div>
        <div class="name-details">
          <div class="field input">
            <label><?php echo $lang['First_Name']; ?></label>
            <input type="text" name="fname" placeholder="<?php echo $lang['Enter_First_Name']; ?>" required>
          </div>
          <div class="field input">
            <label><?php echo $lang['Last_Name']; ?></label>
            <input type="text" name="lname" placeholder="<?php echo $lang['Enter_Last_Name']; ?>" required>
          </div>
        </div>
        <div class="field input">
          <label><?php echo $lang['Account']; ?></label>
          <input type="text" name="account" placeholder="<?php echo $lang['Enter_Your_Account']; ?>" required>
        </div>
        <div class="field input">
          <label><?php echo $lang['Password']; ?></label>
          <input type="password" name="password" placeholder="<?php echo $lang['Enter_New_Password']; ?>" required>
          <i class="fas fa-eye"></i>
        </div>
        <div class="field image">
          <label><?php echo $lang['Select_Image']; ?></label>
          <input type="file" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" required>
        </div>
        <div class="field button">
          <input type="submit" name="submit" value="<?php echo $lang['Continue_to_Chat']; ?>">
        </div>
      </form>
      <div class="link"><?php echo $lang['Already_Signed_Up']; ?> <a href="login.php"><?php echo $lang['Login_Now']; ?></a></div>
    </section>
  </div>

  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/signup.js"></script>

</body>
</html>
