<?php if (! defined ( 'BASEPATH' ))  exit ( 'No direct script access allowed' ); 
$menu_items = dashboard_get_menu_left_selection ();

$CI = &get_instance();
$currentAccountId = $CI->session->userdata("current_account_id");
if ( isset($currentAccountId) && strlen($currentAccountId) > 0 ) {

    $tenant = new BAccount ( $currentAccountId );
    $site_logo = $tenant->logo;
}else {

    $user_instance = BUserHelper::get_instance();
    if( strlen( $user_instance->tenant->logo ) > 0 ):
      $site_logo = $user_instance->tenant->logo;
    endif;
}

if(strlen($site_logo)):
    $margin_top = $this->config->item('#CORE_STYLE_HEADER_LOGO_MARGIN_TOP');
    $margin_left = $this->config->item('#CORE_STYLE_HEADER_LOGO_MARGIN_LEFT');
    $height = $this->config->item('#CORE_STYLE_HEADER_LOGO_HEIGHT');
    if( empty( $margin_top ) && empty( $margin_left ) && empty( $height )):
                
        $logo_attrs = array(124,10,-5);
    endif;
endif;
?>
<a href="<?php echo $site_url.'dashboard/home';?>" class="m-brand__logo-wrapper" data-toggle="m-popover" data-trigger="hover" data-placement="right" data-html="true">
    <img src="<?php echo $site_logo;?>" alt="" height="<?php echo @$height;?>" style="margin-top:<?php echo @$margin_top;?>px; margin-left:<?php echo @$margin_left;?>px;"/>
</a>