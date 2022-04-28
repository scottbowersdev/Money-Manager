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
$page = 'Month List';
$pageTitle = 'Month List';
$page_id = '';
$subPage = '';

/**
 * Get Month List
 */
$curr_month = date('n');
$curr_year = date('Y');
$startDate = new DateTime($curr_year.'-'.$curr_month);
$endDate = new DateTime(($curr_year+2).'-'.$curr_month);
$dateInterval = new DateInterval('P1M');
$datePeriod   = new DatePeriod($startDate, $dateInterval, $endDate);

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

</script>
<!-- // Page Includes -->
</head>
<body id="month-list">

<div class="page-overlay black"></div>

<div id="container">
  
  <div id="main-col">
  
    <?php include('../inc/pages/header.php'); ?>
    
    <div id="main-content">
      
      <!-- LIST -->
      <div class="months-list">
      
        <?php
        $year_loop = '';
        foreach ($datePeriod as $date) {
            
            if($year_loop != $date->format('Y')) { echo '<h2>'.$date->format('Y').'</h2>'; $year_loop = $date->format('Y'); }
                        
            echo '<a class="item" href="'.$url_website.'admin/month/'.$date->format('n').'/'.$date->format('Y').'/">'.$date->format('F').'<i class="fa-solid fa-angle-right"></i> </a>';
                            
        }
        ?>
            
    </div>
    <!-- LIST -->
  
  </div>

</div>


<?php include('../inc/pages/footer.php') ?>

</body>
</html>