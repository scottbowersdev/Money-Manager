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

//unset($_SESSION['signup']);

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

	header("Location: ".$url_website."signup/1/");
	
}

/**
 * On form submit
 */
if(isset($_POST['submit'])) {
	
	// Error checking
	foreach($_POST['day'] as $i => $value) {
	
		if($_POST['day'][$i] == '') { $_SESSION['error'][] = "Please enter a day for entry ".$i; }
		if($_POST['title'][$i] == '') { $_SESSION['error'][] = "Please enter a description for entry ".$i; }
		if($_POST['cost'][$i] == '') { $_SESSION['error'][] = "Please enter a cost for entry ".$i; }
		
	}
	
	// If no error, continue
	if(!is_array($_SESSION['error'])) {
	
		// Add recurring to db
		foreach($_POST['day'] as $i => $value) {
			
			$insert = $db->prepare("INSERT INTO `outgoings_recurring` SET 
			  `date_added` = NOW(), 
			  `user_id` = :user_id, 
			  `day` = :day, 
			  `title` = :title, 
			  `cost` = :cost
			");
			$insert->execute(array(
			  "user_id" => $_SESSION['signup']['id'], 
			  "day" => $_POST['day'][$i], 
			  "title" => $_POST['title'][$i], 
			  "cost" => $_POST['cost'][$i]
			));
			
		}
		
		// Create 5 years in the future
		$begin = new DateTime(date('Y-m'));
		$end = new DateTime(date('Y-m', strtotime('+5 years')));
		$end = $end->modify( '+1 month' ); 
		
		$interval = new DateInterval('P1M');
		$daterange = new DatePeriod($begin, $interval ,$end);
		
		foreach($daterange as $date){
			
			// Insert as 'month'
			$add_m = $db->prepare("INSERT INTO `months` SET `user_id` = :user_id, `month` = :month, `year` = :year, `income` = :income");
			$add_m->execute(array("user_id" => $_SESSION['signup']['id'], "month" => $date->format("m"), "year" => $date->format("Y"), "income" => $_SESSION['signup']['monthlyIncome']));
			$month_id = $db->lastInsertId();
			
			// Insert each outgoing for each month
			foreach($_POST['day'] as $i => $value) {
			
				$add_o = $db->prepare("INSERT INTO `outgoings` SET `user_id` = :user_id, `month_id` = :month_id, `recurring` = '1', `day` = :day, `title` = :title, `cost` = :cost");
				$add_o->execute(array("user_id" => $_SESSION['signup']['id'], "month_id" => $month_id, "day" => $_POST['day'][$i], "title" => $_POST['title'][$i], "cost" => $_POST['cost'][$i]));
				
			}
			
		}

		// Remove Sessions
		unset($_SESSION['signup']);
		
		// Header to Login
		$_SESSION['success'][] = "Good news! Your account is set up and ready to go. Please login using the form below.";
		header("Location: ".$url_website);
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
<script type="text/javascript">
$(document).ready(function(e) {
    
	$('#signup-outgoings .button.fa').on('click tap', function() {
		
		var count = parseInt($('#outgoingCount').val());
		var func = $(this).attr('data-function');
		
		// Add
		if(func == 'add') {
		
			count = count+1;
			$('#outgoingCount').val(count);
			
			$('#signup-outgoings #item-container').append('<div class="row" id="row-'+count+'"><h4>Outgoing '+count+'</h4><div class="input-day"><label for="day_'+count+'">Day of the month</label><select id="day_'+count+'" name="day['+count+']"><?php $count = 1; $limit = 28; do { echo '<option value="'.$count.'">'.$count.'</option>'; $count ++; } while($count <= 28); ?></select></div><div class="input-cost"><label for="cost_'+count+'">Cost <small>(GBP)</small></label><input type="number" min="0" step="any" id="cost_'+count+'" name="cost['+count+']" placeholder="&pound;" /></div><div class="clr"></div><div class="input-description"><label for="title_'+count+'">Description</label><input type="text" id="title_'+count+'" name="title['+count+']" /></div></div>');
			
		} else if(func == 'remove' && count > 1) {
		
			$('#row-'+count).remove();
			
			count = count-1;
			$('#outgoingCount').val(count);
			
		}
		
	});
	
});
</script>
</head>
<body id="page-signup">

<div id="signup-container">

  <div class="header">
    <img src="<?= $url_website ?>img/logo.png" alt="Money Manager" />
  </div>
  
  <div class="padding">
  
    <h1>Sign Up</h1>
    <h2>Step 2 - Recurring Outgoings</h2>
    
    <p>Please enter all of your recurring outgoings below to pre-populate your monthly sheets</p>
    
	<?php /* Success Message */ if(is_array($_SESSION['success'])) { echo notification('success',$_SESSION['success']); } ?>
    <?php /* Error Message */ if(is_array($_SESSION['error'])) { echo notification('error',$_SESSION['error']); } ?>
    <?php /* Notification Message */ if(is_array($_SESSION['message'])) { echo notification('message',$_SESSION['message']); } ?>
    
    <form method="post" enctype="multipart/form-data" id="signup-outgoings">
    
      <input type="hidden" name="outgoingCount" id="outgoingCount" value="<?php if(!is_array($_SESSION['error'])) { echo '1'; } else { echo count($_POST['day']); } ?>" />
      
      <div id="item-container">
    
		<?php if(!is_array($_SESSION['error'])) { ?>
        
        <div class="row" id="row-1">
          <h4>Outgoing 1</h4>
          <div class="input-day">
            <label for="day_1">Day of the month</label>
            <select id="day_1" name="day[1]">
              <?php
                $count = 1;
                $limit = 28;
                do {
                    echo '<option value="'.$count.'"';
                    if(is_array($_SESSION['error']) && $_POST['day']['1'] == $count) { echo ' selected'; }
                    echo '>'.$count.'</option>';
                $count ++; } while($count <= 28);
              ?>
            </select>
          </div>
          <div class="input-cost">
            <label for="cost_1">Cost <small>(GBP)</small></label>
            <input type="number" min="0" step="any" id="cost_1" name="cost[1]" placeholder="&pound;" value="<?= (is_array($_SESSION['error']) ? $_POST['cost']['1'] : FALSE) ?>" />
          </div>
          <div class="clr"></div>
          <div class="input-description">
            <label for="title_1">Description</label>
            <input type="text" id="title_1" name="title[1]" value="<?= (is_array($_SESSION['error']) ? $_POST['title']['1'] : FALSE) ?>" />
          </div>
        </div>
        
        <?php } else { ?>
        
        <?php foreach($_POST['day'] as $i => $value) { ?>
        
        <div class="row" id="row-<?= $i ?>">
          <h4>Outgoing <?= $i ?></h4>
          <div class="input-day">
            <label for="day_<?= $i ?>">Day of the month</label>
            <select id="day_<?= $i ?>" name="day[<?= $i ?>]">
              <?php
                $count = 1;
                $limit = 28;
                do {
                    echo '<option value="'.$count.'"';
                    if($_POST['day'][$i] == $count) { echo ' selected'; }
                    echo '>'.$count.'</option>';
                $count ++; } while($count <= 28);
              ?>
            </select>
          </div>
          <div class="input-cost">
            <label for="cost_<?= $i ?>">Cost <small>(GBP)</small></label>
            <input type="number" min="0" step="any" id="cost_<?= $i ?>" name="cost[<?= $i ?>]" placeholder="&pound;" value="<?= (is_array($_SESSION['error']) ? $_POST['cost'][$i] : FALSE) ?>" />
          </div>
          <div class="clr"></div>
          <div class="input-description">
            <label for="title_<?= $i ?>">Description</label>
            <input type="text" id="title_<?= $i ?>" name="title[<?= $i ?>]" value="<?= (is_array($_SESSION['error']) ? $_POST['title'][$i] : FALSE) ?>" />
          </div>
        </div>
        
        <?php } ?>
        
        <?php } ?>
      
      </div>
      
      <div class="button solid add fa fa-fw fa-plus" data-function="add"></div>
      <div class="button solid remove fa fa-fw fa-minus" data-function="remove"></div>

      <input type="submit" value="Finish Setup" id="submit" name="submit" class="button solid " />
      
      <div class="clr"></div>
    
    </form>
    
  </div>
    
</div>

<?php include('inc/pages/footer.php') ?>

</body>
</html>