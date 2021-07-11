<?php

if (! defined ( 'BASEPATH' ))
    exit ( 'No direct script access allowed' );
$menu_items = dashboard_get_menu_left_selection ();
?>
<!-- BEGIN SIDEBAR -->
<div id="m_ver_menu" class="m-aside-menu  m-aside-menu--skin-dark m-aside-menu--submenu-skin-dark" data-menu-vertical="true"
		 data-menu-scrollable="false" data-menu-dropdown-timeout="500">
	<ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow ">
		
	<?php 
	
	foreach ($menu_items['childs'][1] as $root_item){

         $output = BMenuHelper::render_menu_html($root_item , $menu_items['childs']);
         echo $output['html'];
    }
  
?>
	</ul>
</div>
<!-- END SIDEBAR MENU -->