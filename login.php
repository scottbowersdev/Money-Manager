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
$page = 'Login';
$pageTitle = 'Login';
$subPage = '';

/**
 * User already logged in
 */
if($_SESSION['admin']['logged_in'] == 1) {
	header("Location: ".$url_website."admin/month/".date('n')."/".date('Y')."/");
	exit();
}

/**
 * Log In
 */
if(isset($_POST['login'])) {
	
	// Set vars
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	// Check username for match
	$getUser = $db->prepare("SELECT * FROM `users` WHERE `username` = :username AND `active` = '1'");
	$getUser->execute(array("username"=>$username));
	$getUserInformation = $getUser->fetch(PDO::FETCH_ASSOC);
	$getUserCount = $getUser->rowCount();
	
	// If user found, continue
	if($getUserCount > 0) {
		
		// Check password of user
		include_once("inc/support/PasswordHash.php");
		$hasher = new PasswordHash(8, false);
		$password_check = $hasher->CheckPassword($password, $getUserInformation['password']);
				
		// If password checks out					
		if($password_check) {
			
			$_SESSION['admin']['logged_in'] = 1;
			$_SESSION['admin']['id'] = $getUserInformation['id'];
			$_SESSION['admin']['username'] = $getUserInformation['username'];
			$_SESSION['admin']['email'] = $getUserInformation['email'];
			$_SESSION['admin']['full_name'] = $getUserInformation['full_name'];
			$_SESSION['admin']['dp'] = $getUserInformation['dp'];
			$_SESSION['admin']['monthly_income'] = $getUserInformation['monthly_income'];
			$_SESSION['success'][] = "You have been logged in successfully.";
			
			$insertLog = $db->prepare("INSERT INTO `user_logs` (`user_id`, `ip`, `date`) VALUES (:user_id, :ip, NOW())");
			$insertLog->execute(array("user_id"=>$getUserInformation['id'], "ip"=>$_SERVER['REMOTE_ADDR']));
			
			if(isset($_GET['return_url'])) {
				
			  header("Location: ".$_GET['return_url']);
			  exit();
			  
			} else {
				
			  header("Location: ".$url_website."admin/month/".date('n')."/".date('Y')."/");
			  exit();
			  
			}
			
		} else {
			
			$_SESSION['msgBox']['header'] = "Error logging in";
			$_SESSION['msgBox']['content'] = "The email address or password you entered was incorrect, please try again!";
			$_SESSION['msgBox']['type'] = "error";
			$_SESSION['msgBox']['timer'] = "3000";
			
		}
		
	// Error if no email was found
	} else {
		
			$_SESSION['msgBox']['header'] = "Error logging in";
			$_SESSION['msgBox']['content'] = "The email address or password you entered was incorrect, please try again!";
			$_SESSION['msgBox']['type'] = "error";
			$_SESSION['msgBox']['timer'] = "3000";
		
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
<body id="page-login">

<div id="login-container">

  <div class="padding"><img src="<?= $url_website ?>img/logo.png" alt="Money Manager" /></div>
      
  <form method="post" enctype="multipart/form-data">
  	<div class="padding">
      <div class="row col1">
        <div class="fa fa-fw fa-user"></div>
        <input type="text" name="username" id="username" autofocus placeholder="Username" value="<?= (is_array($_SESSION['msgBox']) ? $_POST['username'] : FALSE) ?>" />
        <div class="clr"></div>
      </div>
      <div class="row col1">
        <div class="fa fa-fw fa-lock"></div>
        <input type="password" name="password" id="password" placeholder="Password" />
        <div class="clr"></div>
      </div>
    </div>
    
    <input type="submit" name="login" id="login" value="Login" class="button" />
    
    <p><small>Don't have an account? <a href="<?= $url_website ?>signup/1/">Sign up here</a></small></p>
  
  </form>
    
</div>

<?php include('inc/pages/footer.php') ?>

</body>
</html>