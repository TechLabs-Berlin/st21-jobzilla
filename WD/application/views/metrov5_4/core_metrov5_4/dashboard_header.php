<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!-- BEGIN LOGO -->
<?php
$user_instance = BUserHelper::get_instance();
if( strlen( $user_instance->tenant->logo ) > 0 ):
    $site_logo = 'uploads/tenant_logo/' . $user_instance->tenant->logo;
endif;
?>
<?php 
if(strlen($site_logo)):
    $margin_top = $this->config->item('#CORE_STYLE_HEADER_LOGO_MARGIN_TOP');
    $margin_left = $this->config->item('#CORE_STYLE_HEADER_LOGO_MARGIN_LEFT');
    $height = $this->config->item('#CORE_STYLE_HEADER_LOGO_HEIGHT');
    if( empty( $margin_top ) && empty( $margin_left ) && empty( $height )):
                
        $logo_attrs = array(124,10,-5);
    endif;
endif;
                                       
?>
<div class="m-stack__item m-stack__item--middle m-brand__logo">
    <a href="<?php echo $site_url.'dashboard/home';?>" class="m-brand__logo-wrapper" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-html="true">
        <img src="<?php echo CDN_URL.$site_logo;?>" alt="" width="<?php echo @$height;?>" height="" style="margin-top:<?php echo @$margin_top;?>px;margin-left:<?php echo @$margin_left;?>px;" class="img-responsive logo-default"/>
    </a>
</div>
<div class="m-stack__item m-stack__item--middle m-brand__tools">
    <!-- BEGIN: Left Aside Minimize Toggle -->
    
    <!-- END -->
    <!-- BEGIN: Responsive Aside Left Menu Toggler -->
    <a href="javascript:;" id="m_aside_left_offcanvas_toggle" class="m-brand__icon m-brand__toggler m-brand__toggler--left m--visible-tablet-and-mobile-inline-block">
        <span></span>
    </a>
    <!-- END -->
    <!-- BEGIN: Responsive Header Menu Toggler -->
    <a id="m_aside_header_menu_mobile_toggle" href="javascript:;" class="m-brand__icon m-brand__toggler m--visible-tablet-and-mobile-inline-block">
        <span></span>
    </a>
    <!-- END -->
    <!-- BEGIN: Topbar Toggler -->
    <a id="m_aside_header_topbar_mobile_toggle" href="javascript:;" class="m-brand__icon m--visible-tablet-and-mobile-inline-block">
        <i class="flaticon-more"></i>
    </a>
    <!-- BEGIN: Topbar Toggler -->
</div>  
<!-- END LOGO -->
<!-- BEGIN RESPONSIVE MENU TOGGLER -->
<?php if($menu_postition == "left"): ?> 
    <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"></a>
<?php endif;?>