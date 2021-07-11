<?php if (! defined ( 'BASEPATH' ))  exit ( 'No direct script access allowed' ); 
$menu_items = dashboard_get_menu_left_selection ();

$user_instance = BUserHelper::get_instance();
if( strlen( $user_instance->tenant->logo ) > 0 ):
$site_logo = 'uploads/tenant_logo/' . $user_instance->tenant->logo;
endif;

if(strlen($site_logo)):
    $margin_top = $this->config->item('#CORE_STYLE_HEADER_LOGO_MARGIN_TOP');
    $margin_left = $this->config->item('#CORE_STYLE_HEADER_LOGO_MARGIN_LEFT');
    $height = $this->config->item('#CORE_STYLE_HEADER_LOGO_HEIGHT');
    if( empty( $margin_top ) && empty( $margin_left ) && empty( $height )):
                
        $logo_attrs = array(124,10,-5);
    endif;
endif;

// get tenants data
$allTenants = getDataFromId("accounts", 0, "is_deleted");
?>


    <div class="m-stack m-stack--ver m-stack--general m-stack--inline pull-left">
        
        <button class="m-aside-header-menu-mobile-close  m-aside-header-menu-mobile-close--skin-light " id="m_aside_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
        
        <div id="m_header_menu" class="m-header-menu m-aside-header-menu-mobile m-aside-header-menu-mobile--offcanvas  m-header-menu--skin-light m-header-menu--submenu-skin-light m-aside-header-menu-mobile--skin-light m-aside-header-menu-mobile--submenu-skin-light">
            <ul class="m-menu__nav  m-menu__nav--submenu-arrow">

            <?php
                $i=0;
                foreach ($menu_items['menu_items'] as $top_menu):
                    if( $top_menu['parent'] == 1 ):
                        $i=$i+1;
                    endif;
                endforeach;
            ?>
            <?php
                foreach ($menu_items['childs'][1] as $root_item):
                    $output = BMenuHelper::render_top_menu_html($root_item , $menu_items['childs'], $i);
                    echo $output['html'];
                endforeach;
            ?>
            </ul>
        </div>
    
    </div>
