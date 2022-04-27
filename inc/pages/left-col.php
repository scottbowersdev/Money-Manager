<?php

$curr_month = date('n');
$curr_year = date('Y');
$startDate = new DateTime($curr_year.'-'.$curr_month);
$endDate = new DateTime(($curr_year+1).'-'.$curr_month);
$dateInterval = new DateInterval('P1M');
$datePeriod   = new DatePeriod($startDate, $dateInterval, $endDate);


?>

<div id="left-col">
  <div id="logo">
    <a href="<?= $url_website ?>admin/month/<?= date('n') ?>/<?= date('Y') ?>/"><img src="<?= $url_website ?>img/logo.png" alt="Money Manager" /></a>
  </div>
  <div id="nav">
  
    <?php
	$year_loop = '';
	foreach ($datePeriod as $date) {
		
		if($year_loop != $date->format('Y')) { echo '<h4>'.$date->format('Y').'</h4>'; $year_loop = $date->format('Y'); }
					
		echo '<li><a href="'.$url_website.'admin/month/'.$date->format('n').'/'.$date->format('Y').'/"'.($monthNumber == $date->format('n') && $year == $date->format('Y') ? ' class="selected"' : FALSE).'><span class="large">'.$date->format('F').'</span><span class="small">'.$date->format('M').'</span></a></li>';
						
	}
	?>
    
  </div>
</div>