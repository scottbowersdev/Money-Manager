<?php
/* ************************************************************
===============================================================                                                                                                                                                                           
-- MM - v1.0
===============================================================
************************************************************ */
include_once('../../inc/support/common.php');
include_once('../../inc/support/php_includes.php');

$paidValue = $_POST['paid'];
$id = $_POST['id'];

$update_outoing = $db->prepare("UPDATE `outgoings` SET `paid` = :paid WHERE id = :id");
$update_outoing->execute(array("paid" => $paidValue, "id" => $id));

$message = array(
	"status" => "success", 
	"id" => $id, 
	"paid" => $paidValue
);

echo json_encode($message);

?>