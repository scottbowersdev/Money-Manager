<?php
/* ************************************************************
===============================================================                                                                                                                                                                           
-- MM - v1.0
===============================================================
************************************************************ */
include_once('../../inc/support/common.php');
include_once('../../inc/support/php_includes.php');

$id = $_POST['id'];

$update_wishlist = $db->prepare("UPDATE `wishlist` SET `purchased` = '1', `date_purchased` = NOW() WHERE id = :id");
$update_wishlist->execute(array("id" => $id));

$message = array(
	"status" => "success", 
	"id" => $id, 
	"paid" => $paidValue
);

echo json_encode($message);

?>