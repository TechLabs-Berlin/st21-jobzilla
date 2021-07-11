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



<!--
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
-->


        <meta name="description" content="Latest updates and statistic charts">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
        <!--begin::Base Styles -->
        <link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/style.bundle.css" rel="stylesheet" type="text/css" />
        <!--end::Base Styles -->

    </head>
    <!-- end::Head -->
    <!-- end::Body -->
    <body class="m--skin- m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default"  >
        <!-- begin:: Page -->
        <div class="m-grid m-grid--hor m-grid--root m-page">
            <div class="m-grid__item m-grid__item--fluid m-grid  m-error-1" style="background-image: url(<?php echo CDN_URL; ?>media_metrov5_4/assets/app/media/img//error/bg3.jpg);">
                <div class="m-error_container">
                    <span class="m-error_number">
                        <h1>
                            <?php echo dashboard_lang("_404_PAGE_TITLE")?>
                        </h1>
                    </span>
                    <p class="m-error_desc">
                        <?php echo dashboard_lang("_404_PAGE_BODY")?><br/>

                                <a href="<?php echo base_url();?>">
                                    <?php echo dashboard_lang("_RETURN_HOME")?> 
                                </a>
                    </p>
                </div>
            </div>
        </div>
        <!-- end:: Page -->
        <!--begin::Base Scripts -->
        <script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
        <script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/scripts.bundle.js" type="text/javascript"></script>
        <!--end::Base Scripts -->
    </body>
    <!-- end::Body -->
</html>
