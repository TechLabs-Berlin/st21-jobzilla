<?php
/*
 * @author Mark Rahman
 */
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
$data ['base_url'] = base_url ();
$data ['site_url'] = rtrim(site_url(), '/').'/';
$data ['site_title'] = $this->config->item ( '#TAB_NAME' );
$data ['site_logo'] = $this->config->item ( 'site_logo' );
$data ['site_favicon'] = $this->config->item ( 'site_favicon' );
$data ['content'] = @$content;
$this->load->helper ( 'portal/dashboard_list' );
$this->load->helper ( 'portal/dashboard_main' );
$this->load->helper ( 'portal/dashboard_menu' );
$template= $this->config->item('template_name');
$site_languages = $this->config->item('site_languages');
$user_language = get_current_user_lang();
$left_menu = 0;
$menu_postition = "";
$left_menu = $this->config->item("#PORTAL_LEFT_MENU");
if($left_menu == 0){
    $menu_postition = "top";
}else{
    $menu_postition = "left";
}
$user_menu_postition = BMenuHelper::get_menu_position_from_user_role();
if(!empty($user_menu_postition)){
    $menu_postition = $user_menu_postition;
}
$data ['menu_postition'] = $menu_postition;
foreach ($site_languages as $key => $value){
	if($user_language === $value){
		$site_lang = $key;
	}
}
$current_user_role = get_user_role();
$allowed_tables = get_user_viewable_tables($current_user_role);
// Save data into a session array
$this->session->set_userdata('allowed_tables', $allowed_tables);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="<?php if(isset($site_lang)){ echo $site_lang; } else{echo "en"; } ?>" class="wf-montserrat-n3-active wf-montserrat-n4-active wf-montserrat-n5-active wf-montserrat-n6-active wf-montserrat-n7-active wf-roboto-n3-active wf-roboto-n4-active wf-roboto-n5-active wf-roboto-n6-active wf-roboto-n7-active wf-active">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<?php
if (file_exists(FCPATH.'application/views/core_override/dashboard_metaitems.php')) {
	$this->load->view ( 'core_override/dashboard_metaitems', $data );
} else {
	
	$this->load->view ( $template.'/core_'.$template.'/dashboard_metaitems', $data );
}
?>
<head>
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="" name="description"/>
<meta content="" name="author"/>

<?php
$printLib = $this->config->item("#ITEMS_PRINT_LIBRARY");

if ( strpos( $_SERVER["REQUEST_URI"], "/items/edit/" ) !== false && $printLib == 'dymo') { ?>
    <script src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/items/dymo.connect.framework.js" type="text/javascript" charset="UTF-8"> </script>
    <script type="text/javascript" src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/items/Layout.js" ></script>
<?php } ?>

<?php if ( strpos( $_SERVER["REQUEST_URI"], "/stocklocations/edit/" ) !== false && $printLib == 'dymo') { ?>
    <script src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/items/dymo.connect.framework.js" type="text/javascript" charset="UTF-8"> </script>
    <script type="text/javascript" src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/stocklocations/Layout.js" ></script>
<?php } ?>

<!--begin::Web font -->
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
<script>
WebFont.load({
	google: {"families":["Montserrat:300,400,500,600,700","Roboto:300,400,500,600,700"]},
    active: function() {
        sessionStorage.fonts = true;
    }
});
</script>
<!--end::Web font -->

<!-- BEGIN THEME STYLES -->
<link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/demo4/base/style.bundle.css" rel="stylesheet" type="text/css"/>
<!-- BEGIN THEME STYLES -->
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/custom.css" rel="stylesheet" type="text/css"/>
<link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css"/>
<!-- common css for all pages -->
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/common-ui.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<!--<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" type="text/css"/>-->
<link rel="stylesheet" href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/datatables.bundle.css" type="text/css"/>
<script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/vendors/base/vendors.bundle.js" type="text/javascript"></script>
<script src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/jquery-ui.min.js" type="text/javascript"></script>
<script type="text/javascript"> var baseURL  ='<?php echo $data ['base_url']; ?>'; </script>
</head>
<?php 
$chat_script = $this->session->userdata('chat_script');
$popup_class = "";
if(isset($chat_script) AND $chat_script == 1){
    $popup_class = "popup";
}
$headerHeight = $this->config->item('#CORE_STYLE_HEADER_HEIGHT');
$headerbgcolor = $this->config->item('#CORE_STYLE_HEADER_BACKGROUND_COLOR');
$userbgcolor = $this->config->item('#CORE_STYLE_USER_BACKGROUND_COLOR');
$accentColor = $this->config->item('#CORE_STYLE_ACCENT_COLOR');
$accentHoverColor = $this->config->item('#CORE_STYLE_ACCENT_HOVER_COLOR');
$accentTextColor = $this->config->item('#CORE_STYLE_ACCENT_TEXT_COLOR');
$accentTextHoverColor = $this->config->item('#CORE_STYLE_ACCENT_TEXT_HOVER_COLOR');
$fieldBorderColor = $this->config->item('#CORE_STYLE_FIELD_BORDER_COLOR');
$anchorTextColor = $this->config->item('#CORE_STYLE_ANCHOR_TEXT_COLOR');
$anchorTextHoverColor = $this->config->item('#CORE_STYLE_ANCHOR_TEXT_HOVER_COLOR');
$menuFixScrolling = strtolower($this->config->item('#CORE_MENU_FIX_ON_SCROLLING'));

$bgImageUrl = getBackgroundImage();
if ( stripos( $bgImageUrl, 'https://') === false && stripos( $bgImageUrl, 'http://') === false ) $bgImageUrl = CDN_URL.$bgImageUrl;

?>

<style>
    .m-header-menu .m-menu__nav>.m-menu__item.m-menu__item--active>.m-menu__link>.m-menu__item-here, .m-header-menu .m-menu__nav>.m-menu__item.m-menu__item--expanded>.m-menu__link>.m-menu__item-here {
        color: <?php echo $headerbgcolor; ?> !important;
    }
    .m-dropdown__header.m--align-center {
        background-color: <?php echo $userbgcolor; ?> !important;
        background-image: none !important;
    }
    span.m-dropdown__arrow.m-dropdown__arrow--right.m-dropdown__arrow--adjust {
        color: <?php echo $userbgcolor; ?> !important;
    }
    .btn-accent, .page-item.active .page-link, .m-datatable.m-datatable--default>.m-datatable__pager>.m-datatable__pager-info .m-datatable__pager-size .btn.dropdown-toggle, .m-datatable.m-datatable--default>.m-datatable__pager>.m-datatable__pager-nav>li>.m-datatable__pager-link.m-datatable__pager-link--active {
        color: <?php echo $accentTextColor; ?>;
        background-color: <?php echo $accentColor; ?>;
        border-color: <?php echo $accentColor; ?>;
    }
    .btn-accent:hover, .pagination .active > a:hover, .btn-accent:focus, .m-datatable.m-datatable--default>.m-datatable__pager>.m-datatable__pager-nav>li>.m-datatable__pager-link:hover, .m-datatable.m-datatable--default>.m-datatable__pager>.m-datatable__pager-info .m-datatable__pager-size .btn.dropdown-toggle:hover, .m-datatable.m-datatable--default>.m-datatable__pager>.m-datatable__pager-nav>li>.m-datatable__pager-link:hover  {
        color: <?php echo $accentTextHoverColor; ?>;
        background-color: <?php echo $accentHoverColor; ?> !important;
        border-color: <?php echo $accentHoverColor; ?> !important;
    }
    .form-control, .form-control[readonly], .select2-container--default .select2-selection--single {
        border-color: <?php echo $fieldBorderColor; ?> !important;
    }
    .m-datatable.m-datatable--default>.m-datatable__table>.m-datatable__foot .m-datatable__row>.m-datatable__cell, .m-datatable.m-datatable--default>.m-datatable__table>.m-datatable__head .m-datatable__row>.m-datatable__cell{
        background-color: <?php echo $headerbgcolor; ?> !important;
    }
    a {
        color: <?php echo $anchorTextColor; ?> !important;
    }
    a:hover,  .row-action a:hover i {
        color: <?php echo $anchorTextHoverColor; ?> !important;
    }
    .m-tabs-line.m-tabs-line--primary a.m-tabs__link.active, .m-tabs-line.m-tabs-line--primary a.m-tabs__link:hover, .m-tabs-line.m-tabs-line--primary.nav.nav-tabs .nav-link.active, .m-tabs-line.m-tabs-line--primary.nav.nav-tabs .nav-link:hover{
        border-bottom: 1px solid <?php echo $accentColor; ?>;
        color:#2162a5 !important;
    }
    .datepicker tbody tr>td.day.active{
        background-color: <?php echo $accentColor; ?> !important;
    }
    .form-control[readonly]{
        background: whitesmoke !important;
    }
</style>

<body style="background-image: url(<?php echo $bgImageUrl; ?>)" class="m-page--fluid m-header--static m-aside-left--enabled m-aside-left--offcanvas m-aside--offcanvas-default <?php echo $popup_class;?>">

<!-- begin:: Page -->
        <div class="m-grid m-grid--hor m-grid--root m-page">

            <?php if ( empty($load_only_edit) || $load_only_edit === '0' ) { ?>
            <!-- BEGIN: Header -->
            <header class="m-grid__item  m-grid m-grid--desktop m-grid--hor-desktop  m-header <?php if( $menuFixScrolling == 'yes' ){echo "m-header-sticky";}?>" style="height: <?php echo $headerHeight;?>px !important; background-color: <?php echo $headerbgcolor; ?> !important;">
                <div class="m-grid__item m-grid__item--fluid m-grid m-grid--desktop m-grid--hor-desktop m-container m-container--fluid m-container--responsive">
                    <div class="m-grid__item m-grid__item--fluid m-grid m-grid--desktop m-grid--ver-desktop m-header__wrapper">
                        <!-- BEGIN: Brand -->
                            <div class="m-grid__item m-brand" style="height: <?php echo $headerHeight;?>px !important;">
                                <div class="m-stack m-stack--ver m-stack--general m-stack--inline">
                                    <!-- start logo -->
                                    <div class="m-stack__item m-stack__item--middle m-brand__logo">

                                        <?php $this->load->view ( 'metrov5_4/core_'.$template.'/dashboard_logo' );?>

                                    </div>
                                    <!-- end logo-->
                                   
                                     
                                 
                                    <!--start optional menu-->
                                    <div class="m-stack__item m-stack__item--middle m-brand__tools">
                                        <!-- begin::Responsive Header Menu Toggler-->
                                        <a id="m_aside_header_menu_mobile_toggle" href="javascript:;" class="m-brand__icon m-brand__toggler m--visible-tablet-and-mobile-inline-block">
                                            <span></span>
                                        </a>
                                        <!-- end::Responsive Header Menu Toggler-->
                                        <a id="m_aside_header_topbar_mobile_toggle" href="javascript:;" class="m-brand__icon m--visible-tablet-and-mobile-inline-block">
                                            <i class="flaticon-more"></i>
                                        </a>
                                    </div>
                                    <!-- end menu -->    
                                </div>
                            </div>
                        <!-- END: Brand -->
                                    
                            
                            <!-- begin::Topbar -->
                            <div class="m-grid__item m-grid__item--fluid m-header-head" id="m_header_nav">                         <?php                 
                                                        $this->load->view ( 'metrov5_4/core_'.$template.'/dashboard_topmenu' );                     
                                         ?>     
                                <div id="m_header_topbar" class="m-topbar  m-stack m-stack--ver m-stack--general" style="height: <?php echo $headerHeight;?>px;">
                                    <div class="m-stack__item m-topbar__nav-wrapper">

                                            <?php
                                                if (file_exists(FCPATH.'application/views/core_override/dashboard_topright.php')) {					
                                                    $this->load->view ( 'core_override/dashboard_topright', $data );
                                                } else {

                                                    $this->load->view ( 'metrov5_4/core_'.$template.'/dashboard_topright', $data );
                                                }
                                            ?>
                                    </div>
                                </div>
                            </div>
                        <!-- end::Topbar -->
                    
                    </div>
                </div>
            </header>
            <!-- END: Header -->
            <?php } ?>
			
            <!-- BEGIN CONTAINER -->
            <div id="mainContainer" class="m-grid__item m-grid__item--fluid m-grid m-grid m-grid--hor m-container m-container--fluid m-container--responsive">
               <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor-desktop m-grid--desktop m-body">
               <div class="m-grid__item m-grid__item--fluid m-grid m-grid--desktop m-grid--ver-desktop ">
                 <div class='m-grid__item m-grid__item--fluid m-wrapper'>
                    <!-- BEGIN SIDEBAR -->                           
	
                    <!-- BEGIN CONTENT -->
                	
                        
                                
                                <?php if ( !empty($class_name) && $class_name === 'dashboard_login' && !empty($user_verification_msg) ) {?>
                                <div class="user-alert-section" style="margin: 37px 15px 0 15px;">
                                    <div class="alert alert-danger alert-dismissible fade show m-alert m-alert--air">                 
                                         <?php echo $user_verification_msg; ?>                  
                                    </div>
                                </div>
                                <?php } ?>  
                            <!-- End Heading -->
                            <?php
                            if (file_exists(FCPATH.'application/views/core_override/dashboard_maincontent.php')) {
                	
                                $this->load->view ( 'core_override/dashboard_maincontent', $data );
                				
                            } else {
                				
                                $this->load->view ( 'metrov5_4/core_'.$template.'/dashboard_maincontent', $data );
                            }
                            ?>          
                				
                       <!-- END CONTENT --> 
                	
                <!-- END CONTAINER -->
            </div>
        </div>
    </div>      
</div>
<div id="ajax_loader" style="position: fixed;
    top: 0px;
    background: rgba(0, 0, 0, 0.25);
    height: 100%;
    width: 100%;
    z-index: 99999;display: none;">
    <img style="position: absolute;
        top: 50%;
        left: 50%;
        margin-left: -16px;
        margin-top: -16px;
        z-index: 9999999;" alt="" src="<?php echo CDN_URL;?>img/ajax-loader.gif">
 </div>
<?php 
$footerMenuArray = array(
    "_FOOTER_MENU_1" => "#FOOTER_MENU_LINK_1",
    "_FOOTER_MENU_2" => "#FOOTER_MENU_LINK_2",
    "_FOOTER_MENU_3" => "#FOOTER_MENU_LINK_3",
    "_FOOTER_MENU_4" => "#FOOTER_MENU_LINK_4"
);
?>
<?php if ( empty($load_only_edit) || $load_only_edit === '0' ) { ?>
<footer class="m-grid__item     m-footer ">
    <div class="m-container m-container--fluid m-container--responsive">                      
        <div class="m-footer__wrapper">
            <div class="m-stack m-stack--flex-tablet-and-mobile m-stack--ver m-stack--desktop">
                <div class="m-stack__item m-stack__item--left m-stack__item--middle m-stack__item--last">
                    <span class="m-footer__copyright m--pull-left footer-text-color">
                        <?php echo date("Y"); ?> © <?php echo $this->config->item('#FOOTER_COPYRIGHT_TEXT'); ?>
                    </span>
                </div>
                <div class="m-stack__item m-stack__item--right m-stack__item--middle m-stack__item--first">
                    <ul class="m-footer__nav m-nav m-nav--skin-dark m-nav--inline m--pull-right" >
                    <?php foreach ( $footerMenuArray as $menuName => $menuLink ) {
                        if( strlen($this->config->item($menuLink)) > 6 ) {?>
                        <li class="m-nav__item">
                            <a href="<?php echo $this->config->item($menuLink); ?>" class="m-nav__link" target="_blank">
                                <span class="m-nav__link-text footer-text-color"><?php echo dashboard_lang($menuName); ?></span>
                            </a>
                        </li>
                        <?php }
                    }?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php } ?>
<div class="m-scroll-top m-scroll-top--skin-top scroll-bottom" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
    <i class="la la-arrow-up"></i>
</div>		
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->

<script>
var zindex = 9999;
</script>
<script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/demo4/base/scripts.bundle.js" type="text/javascript"></script>

<script src="<?php echo autoversion('media_metrov5_4/portal_core/js/common.js');?>" type="text/javascript"></script>

<script type="text/javascript">
 var toast_type='success';
 var toast_message='';
 
 <?php 
 if($this->session->flashdata('flash_message' )):
 
     if($this->session->userdata('dashboard_application_message_type') == 'error'){
     
         $this->session->set_userdata('dashboard_application_message_type', '');
         echo "toast_type='error';";
     }
 ?>
// toastr.options = { 
//     "closeButton": true,
//     "debug": false,
//     "newestOnTop": false,
//     "progressBar": false,
//     "positionClass": "toast-top-center",
//     "preventDuplicates": true,
//     "onclick": null,
//     "showDuration": "300",
//     "hideDuration": "1000",
//     "timeOut": 0,
//     "extendedTimeOut": 0,
//     "showEasing": "swing",
//     "hideEasing": "linear",
//     "showMethod": "fadeIn",
//     "hideMethod": "fadeOut",
//     "tapToDismiss": true
// };

    toastr.options = {
        "closeButton": true,
        "debug": false,
        "positionClass": "toast-top-right",
        "onclick": null,
        "showDuration": "1000",
        "hideDuration": "2000",
        "timeOut": "4000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
 
if (toast_type == 'error'){
	
    toastr.error("<?php echo $this->session->flashdata('flash_message'); ?><br /><br /><button type='button' class='btn btn-outline-light btn-sm--air--wide clear' onclick='toastr.clear()'>OK</button>");
} else{
	
    toastr.success("<?php echo $this->session->flashdata('flash_message'); ?>");
}
<?php endif; ?>
</script>
	

<?php 

$chat_script = $this->session->userdata('chat_script');
if(isset($chat_script) AND $chat_script == 1){
    $this->session->set_userdata('chat_script', 0);
}else{
    $user_helper = BUserHelper::get_instance();
    echo @$user_helper->user_role->script;
}

?>
</body>
<!-- END BODY -->
</html>