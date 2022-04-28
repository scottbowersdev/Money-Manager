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
 * Month and Year
 */
$monthNumber = $_GET['m'];
$dateObject = DateTime::createFromFormat('!m', $monthNumber);
$monthName = $dateObject->format('F');
$year = $_GET['y'];

/**
 * Page Variables
 */
$page = 'View Month';
$pageTitle = $monthName.' '.$year;
$page_id = '';
$subPage = '';

/**
 * Get monthly data
 */
$qryGetMonth = $db->prepare("SELECT * FROM `months` WHERE `active` = '1' AND `user_id` = :user_id AND `month` = :month AND `year` = :year");
$qryGetMonth->execute(array("user_id" => $_SESSION['admin']['id'], "month" => $monthNumber, "year" => $year));
$resGetMonth = $qryGetMonth->fetch(PDO::FETCH_ASSOC);
$totGetMonth = $qryGetMonth->rowCount();

// If no month exists - create it
if($totGetMonth == 0) {
	
		// Add new month
		$add_m = $db->prepare("INSERT INTO `months` SET `user_id` = :user_id, `month` = :month, `year` = :year, `income` = :income");
		$add_m->execute(array("user_id" => $_SESSION['admin']['id'], "month" => $monthNumber, "year" => $year, "income" => $_SESSION['admin']['monthly_income']));
		$new_month_id = $db->lastInsertId();
	
		// Add all recurring outgoings for the new month
		$qryGeRecurring = $db->prepare("SELECT * FROM `outgoings_recurring` WHERE `active` = '1' AND `user_id` = :user_id");
		$qryGeRecurring->execute(array("user_id" => $_SESSION['admin']['id']));
		$resGetRecurring = $qryGeRecurring->fetchAll(PDO::FETCH_ASSOC);
		$totGetRecurring = $qryGeRecurring->rowCount();
	
		if($totGetRecurring > 0) {
			
			foreach($resGetRecurring as $i => $item) {
				
				$add_o = $db->prepare("INSERT INTO `outgoings` SET `user_id` = :user_id, `month_id` = :month_id, `recurring` = '1', `day` = :day, `title` = :title, `cost` = :cost");
				$add_o->execute(array("user_id" => $_SESSION['admin']['id'], "month_id" => $new_month_id, "day" => $item['day'], "title" => $item['title'], "cost" => $item['cost']));
				
			}
			
		}
	
		// Get month data again
		$qryGetMonth = $db->prepare("SELECT * FROM `months` WHERE `active` = '1' AND `user_id` = :user_id AND `month` = :month AND `year` = :year");
		$qryGetMonth->execute(array("user_id" => $_SESSION['admin']['id'], "month" => $monthNumber, "year" => $year));
		$resGetMonth = $qryGetMonth->fetch(PDO::FETCH_ASSOC);
		$totGetMonth = $qryGetMonth->rowCount();
	
}

// Get outgoings
$qryGetOutgoings = $db->prepare("SELECT * FROM `outgoings` WHERE `active` = '1' AND `user_id` = :user_id AND `month_id` = :month_id ORDER BY `recurring` DESC,`day` ASC,`cost` ASC");
$qryGetOutgoings->execute(array("user_id" => $_SESSION['admin']['id'], "month_id" => $resGetMonth['id']));
$resGetOutgoings = $qryGetOutgoings->fetchAll();
$totGetOutgoings = $qryGetOutgoings->rowCount();

// Get total recurring outgoings
$qryGetTotRecurring = $db->prepare("SELECT * FROM `outgoings` WHERE `active` = '1' AND `user_id` = :user_id AND `month_id` = :month_id AND `recurring` = '1'");
$qryGetTotRecurring->execute(array("user_id" => $_SESSION['admin']['id'], "month_id" => $resGetMonth['id']));
$totGetTotRecurring = $qryGetTotRecurring->rowCount();

$pots = [];

// Figure out each pot
foreach($resGetOutgoings as $i => $item) {
    
    preg_match_all("/\\[(.*?)\\]/", $item['title'], $matches); 
    
    if($matches[1][0] != '') {
        $pots[$matches[1][0]] += $item['cost'];
    }
    
}

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
	
		$add_o = $db->prepare("INSERT INTO `outgoings` SET `user_id` = :user_id, `month_id` = :month_id, `recurring` = '0', `day` = :day, `title` = :title, `cost` = :cost");
		$add_o->execute(array("user_id" => $_SESSION['admin']['id'], "month_id" => $resGetMonth['id'], "day" => $day, "title" => $title, "cost" => $cost));
		
		$_SESSION['msgBox']['header'] = "Success";
		$_SESSION['msgBox']['content'] = "Your new outgoing has been successfully added";
		$_SESSION['msgBox']['type'] = "success";
		$_SESSION['msgBox']['timer'] = "3000";
		header("Location: ".$url_website."admin/month/".$_GET['m']."/".$_GET['y']."/"); exit();
		
	}

}

if(isset($_POST['submit-edit'])) {

	$day = $_POST['day']['edit'];
	$cost = $_POST['cost']['edit'];
	$title = $_POST['title']['edit'];

	if($day == '') { $_SESSION['error'][] = "Please select a day for your outgoing"; }
	if($cost == '') { $_SESSION['error'][] = "Please enter the cost of this new outgoing"; }
	if($title == '') { $_SESSION['error'][] = "Please enter a description"; }
	
	if(!is_array($_SESSION['error'])) {
	
		$add_o = $db->prepare("UPDATE `outgoings` SET `day` = :day, `title` = :title, `cost` = :cost WHERE `id` = :id");
		$add_o->execute(array("day" => $day, "title" => $title, "cost" => $cost, "id" => $_POST['id']));
		
		$_SESSION['msgBox']['header'] = "Success";
		$_SESSION['msgBox']['content'] = "Your outgoing has been edited";
		$_SESSION['msgBox']['type'] = "success";
		$_SESSION['msgBox']['timer'] = "3000";
		header("Location: ".$url_website."admin/month/".$_GET['m']."/".$_GET['y']."/"); exit();
		
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
<script type="text/javascript" src="<?= $url_website ?>node_modules/jquery-touchswipe/jquery.touchSwipe.min.js"></script>
<script src="<?= $url_website ?>node_modules/canvasjs/dist/jquery.canvasjs.min.js"></script>
<!-- Charts -->
<script type="text/javascript">
$(function() {
  $(".months-table .row").swipe( {

    swipeLeft:function(event, direction, distance, duration) {
		alert("You swiped " + direction);  
	},

	swipeRight:function(event, direction, distance, duration) {
		alert("You swiped " + direction); 
	},

  });
});

$(document).ready(function(e) {
    
	// New outgoing show
	$('#btn-new-outgoing').on('click tap', function() {
				
		$('.page-overlay').fadeIn();
		$('.modal#new-outgoing').delay(600).fadeIn();
		
	});
	
	// Edit monthly income
	$('#monthlyIncome').on('click tap', function() {
		
		if($(this).is("[readonly]")) {
		
			$(this).removeAttr('readonly');
			
			$(this).on('keypress', function (e) {
			 	if(e.which === 13){
	
					$(this).attr("readonly", "readonly");
	
					$.ajax({
					  url : "<?= $url_website ?>admin/ajax/outgoing-monthly-income.php",
					  type: "POST",
					  data : 'id=<?= $resGetMonth['id'] ?>&value='+$(this).val(),
					  success: function(data, textStatus, jqXHR) {
										  
						  if(textStatus == 'success') {
							  
							setTimeout(function(){
							   window.location.reload(1);
							}, 1000);
							  
							swal({ title: "Success", text: "Monthly income updated", icon: "success", timer: 1000, showConfirmButton: false });
							  
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
	
	// Outgoing Actions
	$('.months-table .fa').on('click tap', function() {
		
		var btnFunction = $(this).attr('data-function');
		var id = $(this).attr('data-id');
		
		// Mark as paid / unpaid
		if(btnFunction == 'paid' || btnFunction == 'not-paid') {
			
			if(btnFunction == 'paid') { var formData = 'paid=1'; } else { var formData = 'paid=0'; }
		
			$.ajax({
			  url : "<?= $url_website ?>admin/ajax/outgoing-paid-unpaid.php",
			  type: "POST",
			  data : formData+'&id='+id,
			  success: function(data, textStatus, jqXHR) {
				  				  
				  if(textStatus == 'success') {
					  
					setTimeout(function(){
					   window.location.reload(1);
					}, 1000);
					  
					swal({ title: "Success", text: "Outogoing marked as "+(btnFunction == 'paid' ? "paid" : "not paid"), icon: "success", timer: 1000, showConfirmButton: false });
					  
				  } else  {
					
					swal({ title: "Error", text: "There has been an error with your submission. Please try again later.", icon: "error", timer: 5000 });
					  
				  }
				  
			  }, error: function(jqXHR, textStatus, errorThrown) {
				  
				  swal({ title: "Error", text: "There has been an error with your submission. Please try again later.<Br />"+jqXHR+'--'+textStatus+'--'+errorThrown, icon: "error", timer: 10000 });
				  
			  }
		  });
		
		// Edit Outgoing	
		} else if(btnFunction == 'edit') {
			
			var content = $.parseJSON($(this).attr('data-content'));
			
			$('#edit-outgoing #id').val($(this).attr('data-id'));
			$('#edit-outgoing #day').val(content['day']);
			$('#edit-outgoing #cost').val(content['cost']);
			$('#edit-outgoing #title').val(content['title'].replace('~~~',"'"));
			
			$('.page-overlay').fadeIn();
			$('.modal#edit-outgoing').delay(600).fadeIn();
			
		// Delete Outgoing	
		} else if(btnFunction == 'delete') {

			swal({
				title: "Are you sure?",
				text: 'Are you sure you wish to delete this outgoing?', 
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
				  data : formData+'&id='+id,
				  success: function(data, textStatus, jqXHR) {
									  
					  if(textStatus == 'success') {
						  
						setTimeout(function(){
						   window.location.reload(1);
						}, 1000);
						  
						swal({ title: "Success", text: "Outogoing has been deleted.", icon: "success", timer: 1000, showConfirmButton: false });
						  
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
    <h2>New Outgoing</h2>
    <form method="post" enctype="multipart/form-data">
      <div class="row">
        <div class="input-day">
          <label for="day">Day of the month</label>
          <input type="number" min="1" max="28" id="day" name="day[new]" value="<?= (!empty($_SESSION['error']) ? $_POST['day']['new'] : FALSE) ?>" />
        </div>
        <div class="input-cost">
          <label for="cost">Cost</label>
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
    <h2>Edit Outgoing</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" id="id" name="id" />
      <div class="row">
        <div class="input-day">
          <label for="day">Day of the month</label>
          <input type="number" min="1" max="28" id="day" name="day[edit]" value="<?= (!empty($_SESSION['error']) ? $_POST['day']['edit'] : FALSE) ?>" />
        </div>
        <div class="input-cost">
          <label for="cost">Cost</label>
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
    
    <div id="main-content">
      
      <!-- TABLE -->
      <div class="months-table">
      
        <div class="header row">
          <div class="col day">Day</div>
          <div class="col desc">Description</div>
          <div class="col cost">Cost</div>
          <div class="col actions">Actions</div>
        </div>
        
        <?php 
        
        $monthlyTotal = 0;
        $count = 1; 
		$dailyTotals = array();
        foreach($resGetOutgoings as $item) { 
          
        $monthlyTotal += $item['cost'];
		
		$dailyTotals[$item['day']] += $item['cost'];
        
        $itemDayObject = DateTime::createFromFormat('d', $item['day']);
        $ordinalSuffix = $itemDayObject->format('S');
                
        ?>
        <div class="row<?= ($count == $totGetTotRecurring ? ' last-recurring"' : FALSE) ?>">
          <div class="col day<?= ($count%2==0 ? ' even' : FALSE).($item['paid'] == 1 ? ' paid' : FALSE).($resGetMonth['year'].($resGetMonth['month'] < 10 ? '0'.$resGetMonth['month'] : $resGetMonth['month']).($item['day'] < 10 ? '0'.$item['day'] : $item['day']) < date('Ymd') ? ' overdue' : FALSE) ?>"><?= $item['day'] ?><small><?= $ordinalSuffix ?></small></div>
          <div class="col desc<?= ($count%2==0 ? ' even' : FALSE) ?>"><?= $item['title'] ?></div>
          <div class="col cost<?= ($count%2==0 ? ' even' : FALSE) ?>">&pound;<?= number_format($item['cost'],2) ?></div>
          <div class="col actions<?= ($count%2==0 ? ' even' : FALSE) ?>">
            <?php if($item['paid'] == 1) { ?><a href="#" class="fa fa-fw fa-times tooltip" data-function="not-paid" data-id="<?= $item['id'] ?>" title="Mark this as not paid"></a><?php } else { ?><a href="#" class="fa fa-fw fa-check tooltip" data-function="paid" data-id="<?= $item['id'] ?>" title="Mark this as paid"></a><?php } ?>
            <a href="#" class="fa fa-fw fa-pencil tooltip" data-function="edit" data-id="<?= $item['id'] ?>" data-content='{"day": "<?= $item['day'] ?>", "cost": "<?= $item['cost'] ?>", "title": "<?= str_replace("'","~~~",$item['title']) ?>"}' title="Edit this outgoing"></a>
            <a href="#" class="fa fa-fw fa-trash tooltip" data-function="delete" data-id="<?= $item['id'] ?>" title="Delete this outgoing"></a>
          </div>
        </div>
        <?php 
        
        $count++; 
        } 
        
        ?>
      
      </div>
      <!-- TABLE -->

      <!-- TOTALS -->
        <div class="totals padding">
          <div class="item total-out">
            <h4>Total Out</h4>
            <h2>&pound;<?= number_format($monthlyTotal,2) ?></h2>
            <small><?= number_format($monthlyTotal/$resGetMonth['income']*100,0) ?>%</small>
            </div>
          <div class="item total-remaining">
            <h4>Total Remaining</h4>
            <h2>&pound;<?= number_format($resGetMonth['income'] - $monthlyTotal,2) ?></h2>
            <small><?= number_format(($resGetMonth['income'] - $monthlyTotal)/$resGetMonth['income']*100,0) ?>%</small>
          </div>
          <div class="item weekly-budget">
            <h4>Weekly Budget</h4>
            <h2>&pound;<?= number_format(($resGetMonth['income'] - $monthlyTotal) / 4,2) ?></h2>
            <small>Per week</small>
          </div>
          <div class="clr"></div>
        </div>
      <!-- TOTALS --> 
      
      <!-- POTS -->
        <div class="totals padding">
            <?php foreach($pots as $pot_name => $cost) { ?>
          <div class="item">
            <h4><?= $pot_name ?></h4>
            <h2>&pound;<?= number_format($cost,2) ?></h2>
            </div>
            <?php } ?>
          <div class="clr"></div>
        </div>
      <!-- POTS --> 
            
    </div>
  
  </div>

</div>


<?php include('../inc/pages/footer.php') ?>

</body>
</html>