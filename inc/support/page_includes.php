<!-- Misc Meta -->
<meta name="author" content="Scott Bowers" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
<meta name="ROBOTS" content="NOINDEX,NOFOLLOW">
<!-- Misc Meta -->
<!-- Favicon -->
<link rel="apple-touch-icon" sizes="180x180" href="<?= $url_website ?>img/resources/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" href="<?= $url_website ?>img/resources/favicon/favicon-32x32.png" sizes="32x32">
<link rel="icon" type="image/png" href="<?= $url_website ?>img/resources/favicon/favicon-16x16.png" sizes="16x16">
<link rel="manifest" href="<?= $url_website ?>img/resources/favicon/manifest.json">
<link rel="mask-icon" href="<?= $url_website ?>img/resources/favicon/safari-pinned-tab.svg" color="#2ecc71">
<meta name="theme-color" content="#ffffff">
<!-- Favicon -->
<!-- User Defined Data -->
<meta http-equiv="keywords" content="<?= getSettings('meta_keywords') ?>">
<meta http-equiv="description" content="<?= getSettings('meta_description') ?>">
<title><?= $pageTitle." - ".getSettings('meta_title') ?></title>
<!-- User Defined Data -->
<!-- Stylesheet -->
<link href="<?= $url_website ?>style/main.css?v=1" rel="stylesheet" type="text/css">
<!-- Stylesheet -->
<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<!-- jQuery -->
<!-- Tooltip -->
<link rel="stylesheet" type="text/css" href="<?= $url_website ?>node_modules/tooltipster/src/css/tooltipster.css" />
<script type="text/javascript" src="<?= $url_website ?>node_modules/tooltipster/dist/js/tooltipster.bundle.min.js"></script>
<!-- Tooltip -->
<!-- Sweet Alerts -->
<link href="<?= $url_website; ?>node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet" type="text/css">
<script src="<?= $url_website; ?>node_modules/sweetalert/dist/sweetalert.min.js"></script>
<script type="text/javascript">
	<?php if(isset($_SESSION['msgBox'])) { ?>
		$(window).load(function(){
			swal({
				title: "<?= $_SESSION['msgBox']['header']; ?>",
				text: "<?= $_SESSION['msgBox']['content']; ?>",
				icon: "<?= $_SESSION['msgBox']['type']; ?>",
				timer: <?= $_SESSION['msgBox']['timer']; ?>
			});
		});
		<?php unset($_SESSION['msgBox']); ?>
	<?php } ?>
</script>
<!-- Sweet Alerts -->
<!-- Site wide JS -->
<script type="text/javascript">
$(document).ready(function(e) {
    
	// Tooltip
	$('.tooltip').tooltipster({
		touchDevices : false,
	});	
	
	// Close Modal
	$('.page-overlay, .modal-close').on('click tap', function() {
		
		if ($('#right-nav').is(":hidden")) {
			$('.modal').fadeOut();
			$('.page-overlay').delay(600).fadeOut();
		} else {
			$('#right-nav > div').fadeOut();
			$('#right-nav').delay(600).animate({width:'toggle'},500);
			$('.page-overlay').delay(1000).fadeOut();
		}
		
	});
	
	// Right Nav
	$('#btn-menu').on('click tap', function() {
		
		var height = $('#container').height();
		
		$('#right-nav').css('height',height+'px');
		$('.page-overlay').fadeIn();
		$('#right-nav').delay(600).animate({width:'toggle'},500);
		$('#right-nav > div').delay(1000).fadeIn();
		
	});
	
});
</script>
<!-- Site wide JS -->