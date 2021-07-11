<?php

if (!defined('BASEPATH'))
  exit('No direct script access allowed');
/*
 * Database table prefix
 */
$config['prefix'] = '';

/*
 * Xml file path which contain database table name and table fields
 */
$config['xml_file_path'] = "application/tables";

/*
 * List of database table names
 */
$config['dashboard_tables'] = array();

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
$config['search_auto_suggest_limit'] = 100;

/*
 * All field default ordering configuration
 */
$config['all_order_by_value'] = "DESC";

/*
 * Site title
 */
$config['site_title'] = 'Kostentransparant Dashboard';

/*
 * Configuration of data listing in per page
 */
$config['list_per_page'] = array(10 => '10', 20 => '20', 50 => '50', 100 => '100', 999999 => 'All');

/*
 * 
 */
$config['listing_field_ellipsis_length'] = 40;
/*
 * core view file 
 * path list 
 */
//$config['core_view'] = array('list' => 'core/list', 'permission' => 'core/permission', 'edit' => 'core/edit', 'list_action' => 'core/list_action', 'edit_action' => 'core/edit_action', 'list_footer' => 'core/list_footer');
$config['core_view'] = array('list' => 'core_metro/list', 'permission' => 'core_metro/permission', 'edit' => 'core_metro/edit', 'list_action' => 'core_metro/list_action', 'edit_action' => 'core_metro/edit_action', 'list_footer' => 'core_metro/list_footer');
/*
 * admin template 
 * name
 */
//$config['template_name'] = 'dashboard';
$config['template_name'] = 'metro';
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
$config['file_upload_max_size'] = "2000";

/*
 * Image upload allow type
 */
$config['img_upload_allowed_types'] = array('jpg', 'jpeg', 'bmp');

/*
 * Image upload valid types
 */
$config['img_file_allowed_types'] = 'gif|jpg|png|jpeg|bmp|ico';

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
 * Dashboard controller sub-folder
 */
$config['controller_sub_folder'] = 'dbtables';
/*
 * Dashboard logo
 */
$config['site_logo'] = 'dashboardmedia/img/logo.png';
/*
 * Dashboard favicon
 */
$config['site_favicon'] = 'dashboardmedia/img/favicon.ico';
/*
 * Add Additional logo 
 * on runtime
 */
$config['enable_additional_logo'] = 0;

/*
 * Dashboard update permission yes/no
 */
$config['config_update_permission'] = 'no';

/*
 * Dashboard default currency symbol
 */
$config['default_currency_symbol'] = '€';
/*
 * Dashboard autosuggest max length 
 */
$config['search_auto_suggest_maxlength'] = '15';

$config['site_languages'] = array(
    'en' => 'english',
    'de' => 'deutsch',
);


$config['allowed_variable_list'] = array(
    'site_title',
);
