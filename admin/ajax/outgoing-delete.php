<?php
/* ************************************************************
===============================================================                                                                                                                                                                           
-- MM - v1.0
===============================================================
************************************************************ */
include_once('../../inc/support/common.php');
include_once('../../inc/support/php_includes.php');

$id = $_POST['id'];

if($_POST['recurring']) { 
	
	// Get current recurring info
	$qryGetCurrRecurring = $db->prepare("SELECT `day`,`title`,`cost` FROM `outgoings_recurring` WHERE `active` = '1' AND `user_id` = :user_id AND `id` = :id");
	$qryGetCurrRecurring->execute(array("user_id" => $_SESSION['admin']['id'], "id" => $id));
	$resGetCurrRecurring = $qryGetCurrRecurring->fetch(PDO::FETCH_ASSOC);
	
	// Remove from recurring table
	$update_outgoing = $db->prepare("UPDATE `outgoings_recurring` SET `active` = '0' WHERE id = :id");
	$update_outgoing->execute(array("id" => $id));

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

			$add_to_months = $db->prepare("DELETE FROM `outgoings` WHERE `day` = :currDay AND `cost` = :currCost AND `title` = :currTitle AND `month_id` = :month_id");
			$add_to_months->execute(array("currDay" => $resGetCurrRecurring['day'], "currCost" => $resGetCurrRecurring['cost'], "currTitle" => $resGetCurrRecurring['title'], "month_id" => $item['id']));

		}

	}

} else { 
	
	$update_outgoing = $db->prepare("UPDATE `outgoings` SET `active` = '0' WHERE id = :id");
	$update_outgoing->execute(array("id" => $id));
	
}

$message = array(
	"status" => "success", 
	"id" => $id
);

echo json_encode($message);

?>