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
$page = 'Wishlist';
$pageTitle = "Wishlist";
$page_id = '';
$subPage = '';

/**
 * Get monthly data
 */
$qryGetWishlist = $db->prepare("SELECT * FROM `wishlist` WHERE `active` = '1' AND `user_id` = :user_id AND `purchased` = '0' ORDER BY `priority` ASC");
$qryGetWishlist->execute(array("user_id" => $_SESSION['admin']['id']));
$resGetWishlist = $qryGetWishlist->fetchAll(PDO::FETCH_ASSOC);
$totGetWishlist = $qryGetWishlist->rowCount();

/**
 * Submit changes / new item
 */
if(isset($_POST['submit-new'])) {

	$priority = $_POST['priority']['new'];
	$cost = $_POST['cost']['new'];
	$title = $_POST['title']['new'];
	$url = $_POST['url']['new'];
	
	if($priority == '') { $_SESSION['error'][] = "Please enter a priority for your item"; }
	if($cost == '') { $_SESSION['error'][] = "Please enter the cost of this item"; }
	if($title == '') { $_SESSION['error'][] = "Please enter a description"; }
	
	if(!is_array($_SESSION['error'])) {
	
		$add_o = $db->prepare("INSERT INTO `wishlist` SET `user_id` = :user_id, `priority` = :priority, `title` = :title, `cost` = :cost, `url` = :url");
		$add_o->execute(array("user_id" => $_SESSION['admin']['id'], "priority" => $priority, "title" => $title, "cost" => $cost, "url" => $url));
		
		$_SESSION['msgBox']['header'] = "Success";
		$_SESSION['msgBox']['content'] = "Your new wishlist item has been successfully added";
		$_SESSION['msgBox']['type'] = "success";
		$_SESSION['msgBox']['timer'] = "3000";
		header("Location: ".$url_website."admin/wishlist/"); exit();
		
	}

}

if(isset($_POST['submit-edit'])) {

	$priority = $_POST['priority']['edit'];
	$cost = $_POST['cost']['edit'];
	$title = $_POST['title']['edit'];
	$url = $_POST['url']['edit'];
	
	if($priority == '') { $_SESSION['error'][] = "Please enter a priority for your item"; }
	if($cost == '') { $_SESSION['error'][] = "Please enter the cost of this item"; }
	if($title == '') { $_SESSION['error'][] = "Please enter a description"; }
	
	if(!is_array($_SESSION['error'])) {
			
		$add_o = $db->prepare("UPDATE `wishlist` SET `priority` = :priority, `title` = :title, `cost` = :cost, `url` = :url WHERE `id` = :id");
		$add_o->execute(array("priority" => $priority, "title" => $title, "cost" => $cost, "url" => $url, "id" => $_POST['id']));
		
		$_SESSION['msgBox']['header'] = "Success";
		$_SESSION['msgBox']['content'] = "Your wishlist item has been edited";
		$_SESSION['msgBox']['type'] = "success";
		$_SESSION['msgBox']['timer'] = "3000";
		header("Location: ".$url_website."admin/wishlist/"); exit();
		
	}

}

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<!-- Page Includes -->
<?php include('../inc/support/page_includes.php'); ?>
<script type="text/javascript" src="<?= $url_website ?>node_modules/jquery-touchswipe/jquery.touchSwipe.min.js"></script>
<script type="text/javascript">
// Swipe Actions
$(function() {
  $(".months-table .row").swipe( {

	// Delete
    swipeLeft:function(event, direction, distance, duration) {

		var id = $(this).attr('data-id');

		swal({
			title: "Are you sure?",
			text: 'Are you sure you wish to delete this wishlist item?', 
			icon: "warning",
			buttons: true,
			dangerMode: true,
			confirmButtonText: "Yes, delete it!"
		})
		.then((willDelete) => {
		if (willDelete) {
			$.ajax({
				url : "<?= $url_website ?>admin/ajax/wishlist-delete.php",
				type: "POST",
				data : 'id='+id,
				success: function(data, textStatus, jqXHR) {
									
					if(textStatus == 'success') {
						
					setTimeout(function(){
						window.location.reload(1);
					}, 2000);
						
					swal({ title: "Success", text: "Wishlist item has been deleted.", icon: "success", timer: 2000 });
						
					} else  {
					
					swal({ title: "Error", text: "There has been an error with your submission. Please try again later.", icon: "error", timer: 5000 });
						
					}
					
				}, error: function(jqXHR, textStatus, errorThrown) {
					
					swal({ title: "Error", text: "There has been an error with your submission. Please try again later.<Br />"+jqXHR+'--'+textStatus+'--'+errorThrown, icon: "error", timer: 10000 });
					
				}
			});
		}
		});

	},

	// Mark as paid / unpaid
	swipeRight:function(event, direction, distance, duration) {
		
		var btnFunction = $(this).attr('data-function');
		var id = $(this).attr('data-id');
		
		swal({
			title: "Mark this item as purchased?",
			text: 'Are you sure you wish to update this Wishlist item?', 
			icon: "warning",
			buttons: true,
			dangerMode: true,
			cancel: {
				text: "Cancel",
				value: null,
				visible: false,
				className: "",
				closeModal: true,
			},
			confirm: {
				text: "Yes",
				value: true,
				visible: true,
				className: "",
				closeModal: true
			}
		})
		.then((willDelete) => {
		if (willDelete) {
		
			$.ajax({
			  url : "<?= $url_website ?>admin/ajax/wishlist-bought.php",
			  type: "POST",
			  data : 'id='+id,
			  success: function(data, textStatus, jqXHR) {
				  				  
				  if(textStatus == 'success') {
					  
					setTimeout(function(){
					   window.location.reload(1);
					}, 2000);
					  
					swal({ title: "Success", text: "Wishlist item marked as purchased", icon: "success", timer: 2000 });
					  
				  } else  {
					
					swal({ title: "Error", text: "There has been an error with your submission. Please try again later.", icon: "error", timer: 5000 });
					  
				  }
				  
			  }, error: function(jqXHR, textStatus, errorThrown) {
				  
				  swal({ title: "Error", text: "There has been an error with your submission. Please try again later.<Br />"+jqXHR+'--'+textStatus+'--'+errorThrown, icon: "error", timer: 10000 });
				  
			  }
		  });

		}
		});

	},

  });
});

$(document).ready(function(e) {

	// Context Menu
	$('.col.actions .fa-ellipsis-vertical').on('click', function (e) {
		$(this).next('.context-menu').stop(true, true).slideDown();
		$(this).parent().siblings().children('.context-menu').stop(true, true).slideUp();
		return false;
	});
	$('.col.actions').on('mouseleave', function () {
		$(this).children('.context-menu').stop(true, true).slideUp();
		return false;
	});
        
	// New outgoing show
	$('#btn-new-outgoing').on('click tap', function() {
				
		$('.page-overlay').fadeIn();
		$('.modal#new-outgoing').delay(600).fadeIn();
		
	});
	
	// Outgoing Actions
	$('.months-table .context-menu li').on('click tap', function() {
		
		var btnFunction = $(this).attr('data-function');
		var id = $(this).attr('data-id');
		
		// Mark as paid / unpaid
		if(btnFunction == 'paid') {
					
			$.ajax({
			  url : "<?= $url_website ?>admin/ajax/wishlist-bought.php",
			  type: "POST",
			  data : 'id='+id,
			  success: function(data, textStatus, jqXHR) {
				  				  
				  if(textStatus == 'success') {
					  
					setTimeout(function(){
					   window.location.reload(1);
					}, 2000);
					  
					swal({ title: "Success", text: "Wishlist item marked as purchased", icon: "success", timer: 2000 });
					  
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
			$('#edit-outgoing #priority').val(content['priority']);
			$('#edit-outgoing #cost').val(content['cost']);
			$('#edit-outgoing #title').val(content['title'].replace('~~~',"'"));
			$('#edit-outgoing #url').val(content['url']);
			
			$('.page-overlay').fadeIn();
			$('.modal#edit-outgoing').delay(600).fadeIn();
			
		// Delete Outgoing	
		} else if(btnFunction == 'delete') {

			swal({
				title: "Are you sure?",
				text: 'Are you sure you wish to delete this wishlist item?', 
				icon: "warning",
				buttons: true,
				dangerMode: true,
				confirmButtonText: "Yes, delete it!"
			})
			.then((willDelete) => {
			if (willDelete) {
				$.ajax({
				  url : "<?= $url_website ?>admin/ajax/wishlist-delete.php",
				  type: "POST",
				  data : 'id='+id,
				  success: function(data, textStatus, jqXHR) {
									  
					  if(textStatus == 'success') {
						  
						setTimeout(function(){
						   window.location.reload(1);
						}, 2000);
						  
						swal({ title: "Success", text: "Wishlist item has been deleted.", icon: "success", timer: 2000 });
						  
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
    <h2>New Wishlist Item</h2>
    <form method="post" enctype="multipart/form-data">
      <div class="row">
        <div class="input-day">
					<label for="priority">Priority</label>
          <input type="number" min="0" step="any" id="priority" name="priority[new]" value="<?= (is_array($_SESSION['error']) ? $_POST['priority']['new'] : FALSE) ?>" />
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
        <div class="input-description">
          <label for="url">URL</label>
          <input type="text" id="url" name="url[new]" value="<?= (is_array($_SESSION['error']) ? $_POST['url']['new'] : FALSE) ?>" />
        </div>
      </div>
      <input type="submit" value="Add new wishlist item" id="submit" name="submit-new" class="button solid green" />
    </form>
  </div>
</div>

<div class="modal" id="edit-outgoing" data-function="edit">
  <div class="padding">
    <div class="fa fa-fw fa-times modal-close"></div>
    <h2>Edit Wishlist Item</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" id="id" name="id" />
      <div class="row">
        <div class="input-day">
          <label for="priority">Priority</label>
          <input type="number" min="0" step="any" id="priority" name="priority[edit]" value="<?= (is_array($_SESSION['error']) ? $_POST['priority']['edit'] : FALSE) ?>" />
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
        <div class="input-description">
          <label for="url">URL</label>
          <input type="text" id="url" name="url[edit]" value="<?= (is_array($_SESSION['error']) ? $_POST['url']['edit'] : FALSE) ?>" />
        </div>
      </div>
      <input type="submit" value="Update Wishlist Item" id="submit-edit" name="submit-edit" class="button solid green" />
    </form>
  </div>
</div>
<!-- Modal Boxes -->

<div id="container">
  
  <div id="main-col">
  
    <?php include('../inc/pages/header.php'); ?>
    
    <div id="main-content">
      
      <!-- TABLE -->
      <div class="months-table">
      
        <div class="header row">
          <div class="col day">Priority</div>
          <div class="col desc">Description</div>
          <div class="col cost">Cost</div>
          <div class="col actions">&nbsp;</div>
        </div>
        
        <?php foreach($resGetWishlist as $item) { $totalAmount += $item['cost']; ?>
        <div class="row<?= ($count == $totGetWishlist ? ' last-recurring"' : FALSE) ?>" data-function="paid" data-id="<?= $item['id'] ?>">
          <div class="col day<?= ($count%2==0 ? ' even' : FALSE) ?>"><?= $item['priority'] ?></div>
					<div class="col desc<?= ($count%2==0 ? ' even' : FALSE) ?>"><?= $item['title'] ?><?php if($item['url']) { ?> <a href="<?= $item['url'] ?>" target="_blank" class="fa fa-fw fa-external-link"></a><?php } ?></div>
          <div class="col cost<?= ($count%2==0 ? ' even' : FALSE) ?>">&pound;<?= number_format($item['cost'],2) ?></div>
          <div class="col actions<?= ($count%2==0 ? ' even' : FALSE) ?>">

			<i class="fa-solid fa-ellipsis-vertical"></i>
			<div class="context-menu">
				<li data-function="paid" data-id="<?= $item['id'] ?>"><i class="fa fa-fw fa-check"></i> Mark as purchased</li>
				<li data-function="edit" data-id="<?= $item['id'] ?>" data-content='{"priority": "<?= $item['priority'] ?>", "url": "<?= $item['url'] ?>", "cost": "<?= $item['cost'] ?>", "title": "<?= str_replace("'","~~~",$item['title']) ?>"}'><i class="fa fa-fw fa-pencil"></i> Edit</li>
				<li data-function="delete" data-id="<?= $item['id'] ?>"><i class="fa fa-fw fa-trash"></i> Delete</li>
          	</div>
          </div>
        </div>
        <?php $count++; } ?>
      </div>
      <!-- TABLE -->
      
      <!-- TOTALS -->
        <div class="totals padding">
          <div class="item total-out">
            <h4>Total Items</h4>
            <h2 style="color: #333;"><?= $totGetWishlist ?></h2>
          </div>
          <div class="item total-remaining">
            <h4>Total Amount</h4>
            <h2 style="color: #333;">&pound;<?= number_format($totalAmount,2) ?></h2>
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