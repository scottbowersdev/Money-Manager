<?php
/* ************************************************************
===============================================================                                                                                                                                                                           
-- MM - v1.0
===============================================================
************************************************************ */
include_once('inc/support/common.php');
include_once('inc/support/php_includes.php');

/**
 * Page Variables
 */
$page = 'Sign Up';
$pageTitle = 'Sign Up';
$subPage = '';

/**
 * User already logged in
 */
if($_SESSION['admin']['logged_in'] == 1) {
	header("Location: ".$url_website."admin/month/".date('j')."/".date('Y')."/");
	exit();
}

/**
 * Step redirection / setup
 */
if(!is_array($_SESSION['signup'])) {

	$_SESSION['signup']['step'] = '1';
	
} else {

	if($_SESSION['signup']['step'] > 1) {
	
		header("Location: ".$url_website."signup/2/");
		
	}
	
}

/**
 * On form submit
 */
if(isset($_POST['submit'])) {
	
	// Set vars
	$username = $_POST['username'];
	$password = $_POST['password'];
	$password_c = $_POST['password_c'];
	$email = $_POST['email'];
	$full_name = $_POST['full_name'];
	$dp = $_FILES['dp'];
	$monthlyIncome = $_POST['monthlyIncome'];
		
	// ********** Add error checking for username and password ************ //

	// Error checking
	if($username == NULL) { $_SESSION['error'][] = "Please enter a username"; }
	if( (strlen($password) < 8) || (strlen($password) > 72) ) { 
		$_SESSION['error'][] = "Your password must be between 8 and 72 characters long"; 
	} elseif($password != $password_c) {
		$_SESSION['error'][] = "Your passwords do not match, please try again"; 
	} else {
		require("inc/support/PasswordHash.php");
		$hasher = new PasswordHash(8, false);
		$password_enc = $hasher->HashPassword($password);	
	}
	if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) { $_SESSION['error'][] = "Please enter a valid email address"; }
	if($full_name == NULL) { $_SESSION['error'][] = "Please enter your full name"; }
	if($monthlyIncome == NULL) { $_SESSION['error'][] = "Please enter your monthly income"; }
	
	// If no error, continue
	if(!is_array($_SESSION['error'])) {
	
		// Add user to db
		$insert = $db->prepare("INSERT INTO `users` SET 
		  `date_added` = NOW(), 
		  `username` = :username, 
		  `password` = :password, 
		  `email` = :email, 
		  `full_name` = :full_name, 
		  `monthly_income` = :monthlyIncome 
		");
		$insert->execute(array(
		  "username" => $username, 
		  "password" => $password_enc, 
		  "email" => $email, 
		  "full_name" => $full_name, 
		  "monthlyIncome" => $monthlyIncome 
		));
		$_SESSION['signup']['id'] = $db->lastInsertId();
		$_SESSION['signup']['step'] = 2;
		$_SESSION['signup']['monthlyIncome'] = $monthlyIncome;
		
		// Add DP
		if($dp['error'] == '0') {
		
			$img_name = imageName($dp['name'],$_SESSION['signup']['id'].'-'.$username.'-dp');
			move_uploaded_file($dp['tmp_name'],"img/dp/".$img_name);
			
			$insert = $db->prepare("UPDATE `users` SET `dp` = :dp WHERE `id` = :id");
			$insert->execute(array("dp" => $img_name, "id" => $_SESSION['signup']['id']));
			
		}
		
		// Header to Step 2
		header("Location: ".$url_website."signup/2/");
		exit();
		
	}
					
}

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<!-- Page Includes -->
<?php include('inc/support/page_includes.php'); ?>
<!-- // Page Includes -->
</head>
<body id="page-signup">

<div id="signup-container">

  <div class="header">
    <img src="<?= $url_website ?>img/logo.png" alt="Money Manager" />
  </div>
  
  <div class="padding">
  
    <h1>Sign Up</h1>
    <h2>Step 1 - Account Information</h2>
    
	<?php /* Success Message */ if(is_array($_SESSION['success'])) { echo notification('success',$_SESSION['success']); } ?>
    <?php /* Error Message */ if(is_array($_SESSION['error'])) { echo notification('error',$_SESSION['error']); } ?>
    <?php /* Notification Message */ if(is_array($_SESSION['message'])) { echo notification('message',$_SESSION['message']); } ?>
        
    <form method="post" enctype="multipart/form-data">
    
      <div class="row">
        <div class="left"><label for="username">Username <span class="required">*</span></label></div>
        <div class="right"><div><input type="text" name="username" id="username" autofocus maxlength="45" value="<?= (is_array($_SESSION['error']) ? $_POST['username'] : FALSE) ?>" /></div></div>
        <div class="clr"></div>
      </div>
      
      <div class="row">
        <div class="left"><label for="password">Password <span class="required">*</span></label></div>
        <div class="right"><div><input type="password" name="password" id="password" maxlength="72" /></div></div>
        <div class="clr"></div>
      </div>
      
      <div class="row">
        <div class="left"><label for="password_c">Confirm Password <span class="required">*</span></label></div>
        <div class="right"><div><input type="password" name="password_c" id="password_c" maxlength="72" /></div></div>
        <div class="clr"></div>
      </div>

      <div class="row">
        <div class="left"><label for="email">Email Address <span class="required">*</span></label></div>
        <div class="right"><div><input type="email" name="email" id="email" maxlength="100" value="<?= (is_array($_SESSION['error']) ? $_POST['email'] : FALSE) ?>" /></div></div>
        <div class="clr"></div>
      </div>

      <div class="row">
        <div class="left"><label for="full_name">Name <span class="required">*</span></label></div>
        <div class="right"><div><input type="text" name="full_name" id="full_name" maxlength="60" value="<?= (is_array($_SESSION['error']) ? $_POST['full_name'] : FALSE) ?>" /></div></div>
        <div class="clr"></div>
      </div>

      <div class="row">
        <div class="left"><label for="dp">Display Picture</label></div>
        <div class="right"><div><input type="file" name="dp" id="dp" /></div></div>
        <div class="clr"></div>
      </div>

      <div class="row">
        <div class="left"><label for="monthlyIncome">Monthly Income <small>(GBP)<span class="required">*</span></label></div>
        <div class="right"><div><input type="number" name="monthlyIncome" id="monthlyIncome" placeholder="&pound;" maxlength="14" value="<?= (is_array($_SESSION['error']) ? $_POST['monthlyIncome'] : FALSE) ?>" /></div></div>
        <div class="clr"></div>
      </div>
      
      <input type="submit" value="Submit" id="submit" name="submit" class="button solid " />
      
      <div class="clr"></div>
    
    </form>
  
  </div>
    
</div>

<?php include('inc/pages/footer.php') ?>

</body>
</html>