<?php

if (!defined('BASEPATH'))
  exit('No direct script access allowed');
/*
 * Database table prefix
 */
include (APPPATH . 'config/template.php');


$config['prefix'] = '';

/*
 * Xml file path which contain database table name and table fields
 */
$config['xml_file_path'] = "application/tables";


/*
 * Url shows "listing" which is method for data listing shown for every controller
 */
$config['listing_view'] = 'listing';

/*
 * The name of physical file name such as list.php page for data listing
 */
$config['listing_file_name'] = 'list';

/*
 * The name of physical file name such as list.php page for data listing
 */
$config['listing_action_file_name'] = 'list_action';

/*
 * The name of physical file name such as edit.php page for data edit
 */
$config['editing_action_file_name'] = 'edit_action';

/*
 * The name of physical file name at view folder/tablesFolder/
 */
$config['edit_form_bottom'] = 'edit_form_bottom';


/*
 * The name of physical file name such as list.php page for data listing
 */
$config['listing_footer_file_name'] = 'list_footer';

/*
 * Physical file name such as edit.php page for data adding/editing also working as method name
 */
$config['edit_view'] = 'edit';

/*
 * Delete method pointing
 */
$config['delete_view'] = 'delete';

/*
 * Copy method pointing
 */
$config['copy_view'] = 'copy';

/*
 * Ajax user view listing field selection method
 */
$config['ajax_update_user_selection'] = 'ajax_update_user_selection';

/*
 * Ajax user view listing field reset method
 */
$config['ajax_reset_user_selection'] = 'ajax_reset_user_selection';

/*
 * Ajax user view listing by order selection method
 */
$config['ajax_update_user_order'] = 'ajax_update_user_order';

/*
 * Ajax user view listing by order reset method
 */
$config['ajax_reset_user_order'] = 'ajax_reset_user_order';

/*
 * User may filter to show listing fields, uses table name
 */
$config['user_show_listing_field_table'] = "listing_fields";

/*
 * Auto suggest searching query limit for showing
 */
$config['search_auto_suggest_limit'] = 15;

/*
 * All field default ordering configuration
 */
$config['all_order_by_value'] = "DESC";

$config['lock_table_view'] = "delete_lock_table";

/*
 * Configuration of data listing in per page
 */
$config['list_per_page'] = array(10 => '10', 20 => '20', 50 => '50', 100 => '100', 1000 => '1000');

/*
 *
 */
$config['listing_field_ellipsis_length'] = 40;
/*
 * core view file
 * path list
 */
//$config['core_view'] = array('list' => 'core_sbadmin/list', 'permission' => 'core_sbadmin/permission', 'edit' => 'core_sbadmin/edit', 'list_action' => 'core_sbadmin/list_action', 'edit_action' => 'core_sbadmin/edit_action', 'list_footer' => 'core_sbadmin/list_footer');

/*
 * admin template
 * name
 */

//$config['template_name'] = 'sbadmin';
 $config['template_name'] = $template['active_template'];
// $config['template_name'] = 'metrov5';
#$config['template_name'] = 'metro';


/// 
$config['core_view'] = array('list' => 'core_'.$config['template_name'].'/list', 'permission' => 'core_'.$config['template_name'].'/permission', 'edit' => 'core_'.$config['template_name'].'/edit', 'list_action' => 'core_'.$config['template_name'].'/list_action', 'edit_action' => 'core_'.$config['template_name'].'/edit_action', 'list_footer' => 'core_'.$config['template_name'].'/list_footer','viewonly' => 'core_'.$config['template_name'].'/viewonly', );
//$config['template_name'] = 'metro';
/*
 * admin template
 * name
 */
$config['user_id'] = 'user_id';

/*
 * User role
 * as admin or user
 */
$config['user_role'] = 'user_role';

/*
 * App owner role
 */
$config['app_owner_role'] = 'app_owner';

/*
 * Super User role
 * as admin or user
 */
$config['super_user_role'] = 'super_admin';

/*
 * After login redirect path
 */
$config['redirect_path_after_login'] = "dashboard/home";

/*
 * No permission path
 */
$config['no_permission'] = "permission";

/*
 * Login url of the application
 */
$config['login_url'] = "dashboard";

/*
 * Image upload path
 */
$config['img_upload_path'] = "uploads/";

/*
 * Profile image upload path
 */
$config['profile_img_upload_path'] = "uploads/profile_image/";

/*
 * Background image upload path
 */
$config['background_image_upload_path'] = "uploads/background_image/";

/*
 * Tenant background image upload path
 */
$config['tenant_background_image_upload_path'] = "uploads/tenant_background_image/";

/*
 * Image upload path
 */
$config['file_upload_path'] = "uploads/";

/*
 * Image upload max size KB
 */
$config['img_upload_max_size'] = "2000";

/*
 * Image upload max width
 */
$config['img_upload_max_width'] = "5120";

/*
 * Image upload max height
 */
$config['img_upload_max_height'] = "3840";

/*
 * File upload max size KB
 */
$config['file_upload_max_size'] = "5000";

/*
 * Image upload allow type
 */
$config['img_upload_allowed_types'] = array('jpg', 'jpeg', 'bmp', 'png' , 'ico','gif');

/*
 * Image upload valid types
 */
$config['img_file_allowed_types'] = 'gif|jpg|png|bmp|ico|ICO';

/*
 * File upload valid types
 */
$config['file_allowed_types'] = 'gif|jpg|png|pdf|jpeg|bmp|zip|rar|docs|docx|ico|ICO';

/*
 * Image thumbnail size height
 */
$config['img_thumbnail_size_height'] = "80";

/*
 * Image thumbnail size height
 */
$config['img_thumbnail_size_width'] = "80";

/*
 * set the maximum filename increment
 */
$config['max_filename_increment'] = 20000;

/*
 * Dashboard controller sub-folder
 */
$config['controller_sub_folder'] = 'dbtables';
/*
 * Dashboard logo
 */
$config['site_logo'] = 'img/logo.png';

/*
 * Dashboard logo
 */
$config['login_logo'] = 'img/login_logo.png';


/*
 * Dashboard favicon
 */
$config['site_favicon'] = 'media_sbadmin/img/favicon.ico';
/*
 * Add Additional logo
 * on runtime
 */

/*
 * Dashboard default currency symbol
 */
$config['default_currency_symbol'] = '�';
/*
 * Dashboard autosuggest max length
 */
$config['search_auto_suggest_maxlength'] = '15';

$config['site_languages'] = array(
    'en' => 'english',
    'nl' => 'dutch');




/*
 * Show chat box on dashbord or not
 */
$config['chat_script'] = 1;

/*
 * Show Message system on dashbord yes or no
 */
$config['messages'] = 1;

$config['max_failed_attempt'] = 10000;

$config['fail_initial'] = -1;

$config['fail_minimum'] = 0;

$config["message_title_length"] = 100;

$config['password_character_limit'] = 9;

$config['max_search_text'] = 80;

//portal form token encryption text
$config['encryption_text'] = "Check form request";


/*default user slug name for sign up user*/
$config['default_signup_user_role'] = 'super_admin';
/*set default user account id for sign up user*/
$config['default_signup_user_account_id'] = '1';

$config['dashboard_msg_tab_name'] = 'dashboard__DISCUSSIONS';

$config['site_name'] = 'CI Dashboard';

$config['message_site_name'] = 'CI Dashboard';

$config['allow_add_in_drop_down_select'] = 1;

$config['dropdown_in_ajax_value'] = 99999999;

$config['no_permission_listing_view_redirect'] = 5;

$config['max_time_to_edit'] = 18000;

$config['default_quoted_indetifier'] = "`";
//field border color
$config['field_border_color'] = "#d8e2f0";
//setup accent color
$config['accent_color'] = "#2162a5";
$config['accent_hover_color'] = "#479df2";
//adjust header height
$config['header_height'] = 68;
$config['header_bgcolor'] = "#E1E8EE";
//array's parems : width,margin-top,margin-left
$config['top_left_logo'] = array(30,0,-25); 
//on click user image, dropdown bg color
$config['user_bgcolor'] = "#c4daed";
//smtp status
$config['smtp_status'] = 0;
//date picker position
$config['date_picker_position'] = "bottom";
//max autosuggest items
$config['max_autosuggest_items'] = 20;
//ignor redirect address
$config['ignore_address'] = array('dbtables/message/render_all_notification_list');
// category text length
$config['category_length'] = 10;
//
$config['msg_store_file'] = "uploads/msg_files";
/*
 * Default avater url
 */
$config['default_avater_url'] = 'https://d1ti08iuyibvtp.cloudfront.net/default/default_avatar.png';
/**
 * View only action text
 */
$config['view_only_action_text'] = 'view-only';
/**
 * Expiry time in sec for remember me cookie
 */
$config['cookie_expire_time'] = (10 * 365 * 24 * 60 * 60);