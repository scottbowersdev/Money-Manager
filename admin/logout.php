<?php
/* ************************************************************
===============================================================                                                                                                                                                                           
-- MM - v1.0
===============================================================
************************************************************ */
include_once('../inc/support/common.php');
include_once('../inc/support/php_includes.php');

unset($_SESSION['admin']);

$_SESSION['msgBox']['header'] = "Success";
$_SESSION['msgBox']['content'] = "You have been logged out of the system";
$_SESSION['msgBox']['type'] = "success";
$_SESSION['msgBox']['timer'] = "3000";
header('Location: '.$url_website); exit();


?>