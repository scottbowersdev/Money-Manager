<?php
/* ************************************************************
===============================================================                                                                                                                                                                           
-- MM - v1.0
===============================================================
************************************************************ */
include_once('../inc/support/common.php');
include_once('../inc/support/php_includes.php');

/**
 * Logged in check
 */
userLoggedIn();


/**
 * Page Variables
 */
$page = 'Recurring Payments';
$pageTitle = 'Recurring Payments';
$page_id = '';
$subPage = '';

/**
 * Get monthly data
 */
$qryGetRecurring = $db->prepare("SELECT * FROM `outgoings_recurring` WHERE `active` = '1' AND `user_id` = :user_id ORDER BY `day` ASC,`cost` ASC");
$qryGetRecurring->execute(array("user_id" => $_SESSION['admin']['id']));
$resGetRecurring = $qryGetRecurring->fetchAll();
$totGetRecurring = $qryGetRecurring->rowCount();

/**
 * Submit changes / new outgoing
 */
if(isset($_POST['submit-new'])) {

	$day = $_POST['day']['new'];
	$cost = $_POST['cost']['new'];
	$title = $_POST['title']['new'];
	
	if($day == '') { $_SESSION['error'][] = "Please select a day for your outgoing"; }
	if($cost == '') { $_SESSION['error'][] = "Please enter the cost of this new outgoing"; }
	if($title == '') { $_SESSION['error'][] = "Please enter a description"; }
	
	if(!is_array($_SESSION['error'])) {
	
		// Add new outgoing
		$add_r = $db->prepare("INSERT INTO `outgoings_recurring` SET `user_id` = :user_id, `day` = :day, `title` = :title, `cost` = :cost");
		$add_r->execute(array("user_id" => $_SESSION['admin']['id'], "day" => $day, "title" => $title, "cost" => $cost));
		
		// Get current month
		$qryGetCurrentMonth = $db->prepare("SELECT `id` FROM `months` WHERE `active` = '1' AND `user_id` = :user_id AND `month` = :month AND `year` = :year");
		$qryGetCurrentMonth->execute(array("user_id" => $_SESSION['admin']['id'], "month" => date('n'), "year" => date('Y')));
		$resGetCurrentMonth = $qryGetCurrentMonth->fetch(PDO::FETCH_ASSOC);
		$totGetCurrentMonth = $qryGetCurrentMonth->rowCount();
		
		// Add to all future months
		$qryGetFutureMonths = $db->prepare("SELECT `id` FROM `months` WHERE `active` = '1' AND `user_id` = :user_id AND `id` > :id");
		$qryGetFutureMonths->execute(array("user_id" => $_SESSION['admin']['id'], "id" => $resGetCurrentMonth['id']));
		$resGetFutureMonths = $qryGetFutureMonths->fetchAll();
		$totGetFutureMonths = $qryGetFutureMonths->rowCount();
		
		if($totGetFutureMonths > 0) {
			
			foreach($resGetFutureMonths as $item) {
				
				$add_to_months = $db->prepare("INSERT INTO `outgoings` SET `user_id` = :user_id, `month_id` = :month_id, `recurring` = '1', `day` = :day, `title` = :title, `cost` = :cost");
				$add_to_months->execute(array("user_id" => $_SESSION['admin']['id'], "month_id" => $item['id'], "day" => $day, "title" => $title, "cost" => $cost));
				
			}
			
		}
		
		$_SESSION['msgBox']['header'] = "Success";
		$_SESSION['msgBox']['content'] = "Your new recurring outgoing has been successfully added";
		$_SESSION['msgBox']['type'] = "success";
		$_SESSION['msgBox']['timer'] = "3000";
		header("Location: ".$url_website."admin/recurring-payments/"); exit();
		
	}

}

if(isset($_POST['submit-edit'])) {

	$id = $_POST['monthID'];
	$day = $_POST['day']['edit'];
	$cost = $_POST['cost']['edit'];
	$title = $_POST['title']['edit'];
	
	if($day == '') { $_SESSION['error'][] = "Please select a day for your outgoing"; }
	if($cost == '') { $_SESSION['error'][] = "Please enter the cost of this new outgoing"; }
	if($title == '') { $_SESSION['error'][] = "Please enter a description"; }
	
	if(!is_array($_SESSION['error'])) {
		
		$qryGetCurrRecurring = $db->prepare("SELECT `day`,`title`,`cost` FROM `outgoings_recurring` WHERE `active` = '1' AND `user_id` = :user_id AND `id` = :id");
		$qryGetCurrRecurring->execute(array("user_id" => $_SESSION['admin']['id'], "id" => $id));
		$resGetCurrRecurring = $qryGetCurrRecurring->fetch(PDO::FETCH_ASSOC);
	
		$add_o = $db->prepare("UPDATE `outgoings_recurring` SET `day` = :day, `title` = :title, `cost` = :cost WHERE `id` = :id");
		$add_o->execute(array("day" => $day, "title" => $title, "cost" => $cost, "id" => $id));
		
		// Get current month
		$qryGetCurrentMonth = $db->prepare("SELECT `id` FROM `months` WHERE `active` = '1' AND `user_id` = :user_id AND `month` = :month AND `year` = :year");
		$qryGetCurrentMonth->execute(array("user_id" => $_SESSION['admin']['id'], "month" => date('n'), "year" => date('Y')));
		$resGetCurrentMonth = $qryGetCurrentMonth->fetch(PDO::FETCH_ASSOC);
		$totGetCurrentMonth = $qryGetCurrentMonth->rowCount();
				
		// Add to all future months
		$qryGetFutureMonths = $db->prepare("SELECT `id` FROM `months` WHERE `active` = '1' AND `user_id` = :user_id AND `id` > :id");
		$qryGetFutureMonths->execute(array("user_id" => $_SESSION['admin']['id'], "id" => $resGetCurrentMonth['id']));
		$resGetFutureMonths = $qryGetFutureMonths->fetchAll();
		$totGetFutureMonths = $qryGetFutureMonths->rowCount();
								
		if($totGetFutureMonths > 0) {
			
			foreach($resGetFutureMonths as $item) {
				
				$add_to_months = $db->prepare("UPDATE `outgoings` SET `day` = :day, `title` = :title, `cost` = :cost WHERE `day` = :currDay AND `cost` = :currCost AND `title` = :currTitle AND `month_id` = :month_id");
				$add_to_months->execute(array("day" => $day, "title" => $title, "cost" => $cost, "currDay" => $resGetCurrRecurring['day'], "currCost" => $resGetCurrRecurring['cost'], "currTitle" => $resGetCurrRecurring['title'], "month_id" => $item['id']));
				
			}
			
		}
		
		$_SESSION['msgBox']['header'] = "Success";
		$_SESSION['msgBox']['content'] = "Your recurring outgoing has been edited";
		$_SESSION['msgBox']['type'] = "success";
		$_SESSION['msgBox']['timer'] = "3000";
		header("Location: ".$url_website."admin/recurring-payments/"); exit();
		
	}

}

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<!-- Page Includes -->
<?php include('../inc/support/page_includes.php'); ?>
<!-- Charts -->
<script src="<?= $url_website ?>node_modules/canvasjs/dist/jquery.canvasjs.min.js"></script>
<!-- Charts -->
<script type="text/javascript">
$(document).ready(function(e) {
	    
	// New outgoing show
	$('#btn-new-outgoing').on('click tap', function() {
				
		$('.page-overlay').fadeIn();
		$('.modal#new-outgoing').delay(600).fadeIn();
		
	});
		
	// Outgoing Actions
	$('#recurring-container .item .fa').on('click tap', function() {
				
		var btnFunction = $(this).attr('data-function');
		var id = $(this).attr('data-id');
		
		if(btnFunction == 'edit') {
			
			var content = $.parseJSON($(this).attr('data-content'));
			
			$('#edit-outgoing #monthID').val($(this).attr('data-id'));
			$('#edit-outgoing #day').val(content['day']);
			$('#edit-outgoing #cost').val(content['cost']);
			$('#edit-outgoing #title').val(content['title'].replace('~~~',"'"));
			
			$('.page-overlay').fadeIn();
			$('.modal#edit-outgoing').delay(600).fadeIn();
			
		// Delete Outgoing	
		} else if(btnFunction == 'delete') {

			swal({
				title: "Are you sure?",
				text: 'Are you sure you wish to delete this recurring payment? This will be removed from all future months.', 
				icon: "warning",
				buttons: true,
				dangerMode: true,
				confirmButtonText: "Yes, delete it!"
			})
			.then((willDelete) => {
			if (willDelete) {
				$.ajax({
				  url : "<?= $url_website ?>admin/ajax/outgoing-delete.php",
				  type: "POST",
				  data : 'id='+id+'&recurring=1',
				  success: function(data, textStatus, jqXHR) {
									  
					  if(textStatus == 'success') {
						  
						setTimeout(function(){
						   window.location.reload(1);
						}, 2000);
						  
						swal({ title: "Success", text: "Outogoing has been deleted.", icon: "success", timer: 2000 });
						  
					  } else  {
						
						swal({ title: "Error", text: "There has been an error with your submission. Please try again later.", icon: "error", timer: 5000 });
						  
					  }
					  
				  }, error: function(jqXHR, textStatus, errorThrown) {
					  
					  swal({ title: "Error", text: "There has been an error with your submission. Please try again later.<Br />"+jqXHR+'--'+textStatus+'--'+errorThrown, icon: "error", timer: 10000 });
					  
				  }
			  });
			}
			});
						
		}
		
	});
		
});
</script>
<!-- // Page Includes -->
</head>
<body id="page-month">

<div class="page-overlay black"></div>
<!-- Modal Boxes -->
<div class="modal" id="new-outgoing" data-function="new">
  <div class="padding">
    <div class="fa fa-fw fa-times modal-close"></div>
    <h2>New Recurring Payment</h2>
    <p>Please note: this will edit all future recurring payments also.</p>
    <form method="post" enctype="multipart/form-data" id="form-submit-new">
      <div class="row">
        <div class="input-day">
          <label for="day">Day of the month</label>
          <input type="number" min="1" max="28" id="day" name="day[new]" value="<?= (!empty($_SESSION['error']) ? $_POST['day']['edit'] : FALSE) ?>" />
        </div>
        <div class="input-cost">
          <label for="cost">Cost <small>(GBP)</small></label>
          <input type="number" min="0" step="any" id="cost" name="cost[new]" placeholder="&pound;" value="<?= (is_array($_SESSION['error']) ? $_POST['cost']['new'] : FALSE) ?>" />
        </div>
        <div class="clr"></div>
        <div class="input-description">
          <label for="title">Description</label>
          <input type="text" id="title" name="title[new]" value="<?= (is_array($_SESSION['error']) ? $_POST['title']['new'] : FALSE) ?>" />
        </div>
      </div>
      <input type="submit" value="Add new outgoing" id="submit" name="submit-new" class="button solid green" />
    </form>
  </div>
</div>

<div class="modal" id="edit-outgoing" data-function="edit">
  <div class="padding">
    <div class="fa fa-fw fa-times modal-close"></div>
    <h2>Edit Recurring Payment</h2>
    <p>Please note: this will edit all future recurring payments also.</p>
    <form method="post" enctype="multipart/form-data" id="form-submit-edit">
      <input type="hidden" id="monthID" name="monthID" />
      <div class="row">
        <div class="input-day">
          <label for="day">Day of the month</label>
          <input type="number" min="1" max="28" id="day" name="day[edit]" value="<?= (!empty($_SESSION['error']) ? $_POST['day']['edit'] : FALSE) ?>" />
        </div>
        <div class="input-cost">
          <label for="cost">Cost <small>(GBP)</small></label>
          <input type="number" min="0" step="any" id="cost" name="cost[edit]" placeholder="&pound;" value="<?= (is_array($_SESSION['error']) ? $_POST['cost']['edit'] : FALSE) ?>" />
        </div>
        <div class="clr"></div>
        <div class="input-description">
          <label for="title">Description</label>
          <input type="text" id="title" name="title[edit]" value="<?= (is_array($_SESSION['error']) ? $_POST['title']['edit'] : FALSE) ?>" />
        </div>
      </div>
      <input type="submit" value="Update outgoing" id="submit-edit" name="submit-edit" class="button solid green" />
    </form>
  </div>
</div>
<!-- Modal Boxes -->

<div id="container">

  <?php include('../inc/pages/left-col.php'); ?>
  
  <div id="main-col">
  
    <?php include('../inc/pages/header.php'); ?>
    
    <div id="main-content" class="padding">
      
      <!-- ITEMS -->
      <div id="recurring-container">
        
        <?php 
        
        $total = 0;
        $count = 1; 
				$dailyTotals = array();
        foreach($resGetRecurring as $item) { 
          
        $total += $item['cost'];
		
				$dailyTotals[$item['day']] += $item['cost'];
        
        $itemDayObject = DateTime::createFromFormat('d', $item['day']);
        $ordinalSuffix = $itemDayObject->format('S');
                
        ?>
        <div class="item">
          <div class="padding">
            <div class="day"><?= $item['day'] ?><small><?= $ordinalSuffix ?></small></div>
            <div class="desc"><?= $item['title'] ?></div>
            <div class="cost">&pound;<?= number_format($item['cost'],2) ?></div>
          </div>
          <div class="actions">
            <a href="#" class="fa fa-fw fa-pencil tooltip" data-function="edit" data-id="<?= $item['id'] ?>" data-content='{"day": "<?= $item['day'] ?>", "cost": "<?= $item['cost'] ?>", "title": "<?= str_replace("'","~~~",$item['title']) ?>"}' title="Edit this outgoing"></a>
            <a href="#" class="fa fa-fw fa-trash tooltip" data-function="delete" data-id="<?= $item['id'] ?>" title="Delete this outgoing"></a>
          </div>
        </div>
        <?php 
        
        $count++; 
        } 
        
        ?>
        
        <div class="clr"></div>
      
      </div>
      <!-- TABLE -->
      
      <!-- TOTALS -->
      <div class="totals">
        <div class="item total-out">
          <h4>Total Out</h4>
          <h2>&pound;<?= number_format($total,2) ?></h2>
        </div>
        <div class="clr"></div>
      </div>
      <!-- TOTALS --> 
    
    </div>
  
  </div>

</div>


<?php include('../inc/pages/footer.php') ?>

</body>
</html>