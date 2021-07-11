<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<!DOCTYPE html>
<!--

<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<?php $site_favicon=$this->config->item ( 'site_favicon' ); ?>
<head>
<meta charset="utf-8"/>
<title><?php echo $this->config->item('site_title'); ?></title>
<link href="<?php echo CDN_URL.$site_favicon;?>" rel="shortcut icon" type="image/vnd.microsoft.icon" />
<link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/style.bundle.css" rel="stylesheet" type="text/css"/>

<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" type="text/javascript"></script>



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
        <a href="<?php echo base_url().'dashboard/home';?>"><img alt="" width="124" height="" src="<?php echo CDN_URL.$this->config->item('site_logo')?>"></a>
    <?php } ?>
</div>
<!-- END LOGO -->
