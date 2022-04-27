<?php
/*
  *****************************
  PHP Basic settings
  *****************************
*/
session_start();
date_default_timezone_set('Europe/London');
require(__DIR__.'/../../vendor/autoload.php');

/*
  *****************************
  DOT ENV
  *****************************
*/
$env_root = dirname(__DIR__, 2);
if($_SERVER['SERVER_ADDR'] == "10.5.7.79") {
	$dotenv = Dotenv\Dotenv::createImmutable($env_root);
} else {
	$dotenv = Dotenv\Dotenv::createImmutable($env_root, '.env.dev');
}
$dotenv->load();

/*
  *****************************
  Error Reporting
  *****************************
*/
if($_ENV['DEBUG'] == 'true') {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
} else {
	error_reporting(0);
	ini_set('display_errors', 0);
}

/*
  *****************************
  Database Connection
  *****************************
*/
try {

	$db = new MyPDO('mysql:host='.$_ENV['DB_HOST'].';'.($_ENV['DB_PORT'] != '' ? 'port='.$_ENV['DB_PORT'].';' : FALSE).'dbname='.$_ENV['DB_NAME'].';charset=utf8', $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

} catch(PDOException $ex) {

    echo '<pre>'; print_r(['outcome' => "SQL ERROR", 'message' => $ex->getMessage()]); echo'</pre>';
	  die();

}

/*
  *****************************
  Check if user is logged in to the portal
  *****************************
*/
function userLoggedIn()
{
  global $url_website;
  if ($_SESSION['admin']['logged_in'] != 1) {
    $return_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $url_website . '?return_url=' . $return_url);
  }
}

/*
  *****************************
  Get Basic Settings
  -----------------
  get_settings('company_name');
  *****************************
*/
function getSettings($column)
{
  global $db;
  $get_content = $db->prepare("SELECT * FROM `settings` WHERE `id` = :id");
  $get_content->execute(array('id' => "1"));
  $get_content = $get_content->fetch(PDO::FETCH_ASSOC);
  return $get_content[$column];
}

$url_website = $_ENV['WEBSITE_URL'];

/*
  *****************************
  Message Generation
  -----------------
  notification('success','You have successfully completed this action');
  *****************************
*/
function notification($type, $message = array())
{

  switch ($type) {
    case 'success':
      $content = '<div class="success">';
      $icon = 'check';
      break;
    case 'error':
      $content = '<div class="error">';
      $icon = 'times';
      break;
    case 'message':
      $content = '<div class="notification">';
      $icon = 'exclamation';
      break;
  }

  foreach ($message as $message) {
    $content .= '<li><span class="fa-fw fa fa-' . $icon . '"></span>' . $message . '</li>';
  }

  $content .= '</div>';

  return $content;
}

/*
  *****************************
  Array echo
  -----------------
  showArray($array);
  *****************************
*/
function showArray($array)
{
  echo "<pre>";
  print_r($array);
  echo "</pre>";
}

/*
  *****************************
  Date Format
  -----------------
  dateFormat('2014-01-01','d/m/Y H:i');
  *****************************
*/
function dateFormat($date, $format)
{

  if ($format == NULL) {
    $format = 'd/m/Y';
  }

  $dateExplode = strtotime($date);
  $newDate = date($format, $dateExplode);

  return $newDate;
}

/*
  *****************************
  Random String Generator
  -----------------
  randomString(6);
  *****************************
*/
function randomString($length)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}

/*
  *****************************
  Create Image Name
  -----------------
  imageName($_FILES['image']['name']);
  *****************************
*/
function imageName($originalFileName, $newFileName)
{

  $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
  $newFileName = $newFileName . '.' . $fileExtension;
  return $newFileName;
}

/*
  *****************************
  Image Resize
  -----------------
  imgResize('../images/image.jpg',500,600,1,'FFFFFF');
  *****************************
*/
function imgResize($imageURL, $width, $height, $zc = 1, $cc = 'FFFFFF')
{

  global $url_website;

  $return_url = $url_website . 'inc/support/mthumb.php/?src=' . $imageURL . '&q=95&a=c';

  if ($width) {
    $return_url .= '&w=' . $width;
  }
  if ($height) {
    $return_url .= '&h=' . $height;
  }
  if ($zc) {
    $return_url .= '&zc=' . $zc;
  } // 1 = Zoom and crop | 2 = Zoom with no crop (Requires 'cc' too for background color)
  if ($cc) {
    $return_url .= '&cc=' . $cc;
  } // Hex value for color of the background (No #) 


  return $return_url;
}
