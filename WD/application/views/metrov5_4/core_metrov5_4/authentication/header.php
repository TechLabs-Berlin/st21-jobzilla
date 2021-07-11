<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$site_favicon=$this->config->item ( 'site_favicon' );
//$social_sign_up = $this->config->item('social_sign_up');
$signupWithFacebook = $this->config->item('#LOGIN_WITH_FACEBOOK');
$signupWithTwitter = $this->config->item('#LOGIN_WITH_TWITTER');
$signupWithGoogle = $this->config->item('#LOGIN_WITH_GOOGLE');
$signupWithLinkedin = $this->config->item('#LOGIN_WITH_LINKEDIN');
$forget_pass = $this->session->userdata('forget_pass');
$bgImageUrl = getBackgroundImage();
$bgImageUrl = ( !stripos($bgImageUrl, 'http://') && !stripos($bgImageUrl, 'https://') ) ? CDN_URL . $bgImageUrl: $bgImageUrl; 
?><!DOCTYPE html>
<html lang="en" >
    <!-- begin::Head -->
    <head>
        <meta charset="utf-8" />
        <title><?php echo $this->config->item('#TAB_NAME'); ?></title>

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

	<link href="<?php echo CDN_URL.$site_favicon;?>" rel="shortcut icon" type="image/vnd.microsoft.icon" />
	<!-- BEGIN GLOBAL MANDATORY STYLES -->
	
	<link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/style.bundle.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/login.css" rel="stylesheet" type="text/css"/>
	<link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
		        <!--end::Base Styles -->

    
    <script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" type="text/javascript"></script>
	<?php if( $signupWithLinkedin ): ?>	

	<!--Add linkedin JS SDK-->

	<script type="text/javascript" src="//platform.linkedin.com/in.js">

		api_key:   <?php echo $this->config->item('api_key_linkedin'); ?>

		onLoad:    onLinkedInLoad
		authorize: false
		scope: r_basicprofile r_emailaddress
	</script>
	
	<?php endif; if( $signupWithGoogle ):?>

	<!--Google plus Sign up API-->

	<script src="https://apis.google.com/js/api:client.js"></script>
	<script>
		var googleUser = {};
		var startApp = function() {
			gapi.load('auth2', function(){
				// Retrieve the singleton for the GoogleAuth library and set up the client.
				auth2 = gapi.auth2.init({
					client_id: '<?php echo $this->config->item('client_id_google'); ?>',
					cookiepolicy: 'single_host_origin',
					// Request scopes in addition to 'profile' and 'email'
					//scope: 'additional_scope'
				});
				attachSignin(document.getElementById('customBtn'));
			});
		};

	</script>
	
	<?php endif;?>
	
    <script src='https://www.google.com/recaptcha/api.js'></script>
    
    <script type="text/javascript">
    var signupWithFacebook = "<?php echo $signupWithFacebook; ?>";
    var signupWithTwitter = "<?php echo $signupWithTwitter; ?>";
    var signupWithGoogle = "<?php echo $signupWithGoogle; ?>";
    var signupWithLinkedin = "<?php echo $signupWithLinkedin; ?>";
    var fb_app_id = "<?php echo $this->config->item('fb_app_id');?>";
	var isHeader = 1;
    </script>
    
    <?php require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/authentication/password_validation_script.php');?>
</head>
    <!-- end::Head -->

    
    <!-- end::Body -->
<body class="m--skin- m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default"  >

        
        
    	<!-- begin:: Page -->
<div class="m-grid m-grid--hor m-grid--root m-page">
    
			
				<div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor m-login m-login--singin m-login--2 m-login-2--skin-1  <?php if(isset($forget_pass) && $forget_pass == 1) {echo 'none';} ?> <?php if(isset($forget_pass) && $forget_pass == 1) { echo "m-login--forget-password"; } ?>" id="m_login" style="background-image: url(<?php echo $bgImageUrl;?>);">
	<div class="m-grid__item m-grid__item--fluid	m-login__wrapper">
		<div class="m-login__container">
			<div class="m-login__logo">
			  <?php if(strlen($this->config->item('login_logo'))){ ?>
				<a href="<?php echo base_url().'dashboard/home';?>"><img alt=""  height="" src="<?php echo CDN_URL.$this->config->item('login_logo'); ?>"></a>
				<div class="subtitle"><?php echo dashboard_lang('_NSCLC_SURVEY'); ?> </div>

			   <?php } ?>	
			</div>