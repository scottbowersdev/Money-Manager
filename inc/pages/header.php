<div id="header">
  <div class="padding">
    <a href="<?= $url_website ?>month-list/"><object data="<?= $url_website ?>img/logo.svg"> </object></a>
    <?php if($page == 'View Month') { ?>
    <h1><?= $monthName ?><small><?= $year ?></small></h1>
    <?php } else { ?>
      <h1><?= $page ?></h1>
    <?php } ?>
    <div class="right">
      <div class="button solid fa fa-bars tooltip" title="Main Menu" id="btn-menu"></div>
      <?php if($page != 'Month List') { ?><div class="button solid fa fa-plus tooltip" title="Create a new outgoing" id="btn-new-outgoing"></div><?php } ?>
      <?php if($page != 'Month List') { ?><a href="<?= $url_website ?>admin/month-list/" class="button solid fa fa-arrow-left tooltip" title="Back" id="btn-back"></a><?php } ?>

      <?php if($page == 'View Month') { ?>
      <div id="month-totals">&pound;<input type="text" name="monthlyIcome" id="monthlyIncome" size="8" class="tooltip" title="Click to edit, then hit Enter" value="<?= number_format($resGetMonth['income'],2) ?>" readonly /></div>
      <?php } ?>
      <div class="clr"></div>
    </div>
    <div class="clr"></div>
  </div>
</div>

<div id="right-nav">
  <div>
    <div class="user">
      <img src="<?= imgResize($url_website.'img/dp/'.$_SESSION['admin']['dp'],100,100,1) ?>" alt="" />
      <?= $_SESSION['admin']['full_name'] ?>
      <!--<div class="close-right-nav fa fa-fw fa-times"></div>-->
    </div>
    <ul>
      <li><a href="<?= $url_website ?>admin/recurring-payments/"><span class="fa fa-fw fa-repeat"></span> Recurring Payments</a></li>
      <li><a href="<?= $url_website ?>admin/wishlist/"><span class="fa fa-fw fa-gift"></span> Wishlist</a></li>
      <li><a href="<?= $url_website ?>admin/logout/"><span class="fa fa-fw fa-sign-out"></span> Logout</a></li>
    </ul>
  </div>
</div>