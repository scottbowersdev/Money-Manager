<?php
/* ************************************************************
===============================================================                                                                                                                                                                           
-- MM - v1.0
===============================================================
************************************************************ */
include_once('../../inc/support/common.php');
include_once('../../inc/support/php_includes.php');

$id = $_POST['id'];
$value = str_replace(',','',$_POST['value']);

$update_outgoing = $db->prepare("UPDATE `months` SET `income` = :value WHERE id = :id");
$update_outgoing->execute(array("value" => $value, "id" => $id));

$message = array(
	"status" => "success", 
	"id" => $id
);

echo json_encode($message);

?>