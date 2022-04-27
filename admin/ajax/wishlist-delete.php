<?php
/* ************************************************************
===============================================================                                                                                                                                                                           
-- MM - v1.0
===============================================================
************************************************************ */
include_once('../../inc/support/common.php');
include_once('../../inc/support/php_includes.php');

$id = $_POST['id'];

$update_outgoing = $db->prepare("UPDATE `wishlist` SET `active` = '0' WHERE id = :id");
$update_outgoing->execute(array("id" => $id));
	
$message = array(
	"status" => "success", 
	"id" => $id
);

echo json_encode($message);

?>