<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$site_favicon=$this->config->item ( 'site_favicon' );

?><!DOCTYPE html>
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>

    <meta charset="utf-8"/>
    <title><?php echo $this->config->item('site_title'); ?></title>
    <link href="<?php echo CDN_URL.$site_favicon;?>" rel="shortcut icon" type="image/vnd.microsoft.icon" />
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
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


</head>

<body class="page-404-full-page">
<div class="row">
    <div class="col-md-12 page-404">
        <div class="number" style="color: #E43A45">
            404
        </div>
        <div class="details">
            <h3><?php echo dashboard_lang("_404_PAGE_TITLE")?></h3>
            <p>
                <?php echo dashboard_lang("_404_PAGE_BODY")?><br/>
                <a href="<?php echo base_url();?>">
                    <?php echo dashboard_lang("_RETURN_HOME")?> </a>

            </p>

        </div>
    </div>
</div>
<!-- END LOGIN -->

<script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/scripts.bundle.js" type="text/javascript"></script>
<script src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/common.js" type="text/javascript"></script>


<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>
