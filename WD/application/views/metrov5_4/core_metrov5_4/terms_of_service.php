<!DOCTYPE html>
<!--

<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title><?php echo $this->config->item('site_title'); ?></title>
<link href="<?php echo CDN_URL;?>img/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
  <!--begin::Web font -->
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
<script>
WebFont.load({
	google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
	active: function() {
		sessionStorage.fonts = true;
	}
});

</script>
		<!--end::Web font -->

<!-- BEGIN THEME STYLES -->

<link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/style.bundle.css" rel="stylesheet" type="text/css"/>
<!-- BEGIN THEME STYLES -->
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/custom.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>


<!-- END THEME STYLES -->

<script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
<script src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/jquery-ui.min.js" type="text/javascript"></script>

<script type="text/javascript"> var baseURL  ='<?php echo $data ['base_url']; ?>'; </script>
<link rel="shortcut icon" href="favicon.ico"/>

</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
<div class="menu-toggler sidebar-toggler">
</div>
<!-- END SIDEBAR TOGGLER BUTTON -->
<div class="logo">
	<?php if(strlen($this->config->item('site_logo'))){

	    ?>
        <a href="<?php echo base_url().'dashboard/home';?>"><img alt="" width="124" height="" src="<?php echo base_url().$this->config->item('site_logo')?>"></a>
    <?php } ?>
</div>
<!-- END LOGO -->
<!-- BEGIN LOGIN -->
<div class="content">
	<!-- BEGIN REGISTRATION FORM -->
	<form class="signup-form" action="" method="post" role="form">
            <h3 class="form-title"><?php echo dashboard_lang('_TERMS_OF_SERVICE');?></h3>
		<p><?php echo dashboard_lang('_TERMS_OF_SERVICE_BODY');?></p>
                <div class="form-actions">
                    <a href="<?php echo base_url() . "dashboard/index"; ?>"  id="register-back-btn" type="button" class="btn grey-salsa btn-outline"><?php echo dashboard_lang('_BACK_TO_LOGIN');?></a>
                </div>
</div>
<div class="copyright">

</div>
<!-- END LOGIN -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->


<script src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/common.js" type="text/javascript"></script>

<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>
