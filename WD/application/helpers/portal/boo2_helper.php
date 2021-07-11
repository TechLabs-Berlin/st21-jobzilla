<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
    require_once FCPATH . 'application/helpers/portal/dashboard_main_helper.php';
/**
 * return user account id
 * 
 * @return int
 */
function get_account_id($additional_param=0)
{
    $CI = & get_instance();
    if( getActiveTenantId() ){
        return getActiveTenantId();
    } else if ( ! empty( $CI->session->userdata('account_id') ) ) {
        return $CI->session->userdata('account_id');
    } else {
        return 1;
    }
}
/*
 *  always return 1 as default account value
 *  @return int(1)
*/
function get_default_account_id()
{
    return 1;
}
/**
 * set the account id for a user
 * 
 * @param int $account_id            
 */
function set_account_id($account_id)
{
    $CI = & get_instance();
    $CI->session->set_userdata('account_id', $account_id);
}

/*
 * get account id from slug
 */
function get_account_from_slug($slug)
{
    $CI = & get_instance();
    $_table_prefix = $CI->config->items('prefix');
    $query = $CI->db->get_where($_table_prefix . 'accounts', array(
        'slug' => $slug
    ));
    return $query->row_array();
}

/*
 * get account validation
 */
function accountValidationRow($tableName, $id, $account_id = null)
{
    $CI = & get_instance();
    
    if (isSuperUser()) {
        // if super user then skip checking
        $accountValidationRow = true;
    } else {
        $accountValidationRow = false;
        if (is_null($account_id)) {
            $account_id = get_default_account_id();
        }
        $query = $CI->db->get_where($tableName, array(
            'id' => $id,
            'account_id' => $account_id
        ));
        if ($query->num_rows() > 0) {
            $accountValidationRow = true;
        }
    }
    
    return $accountValidationRow;
}

/*
 * determine whether user is super admin or not
 * return 0|1
 */
function isSuperUser()
{
    $CI = & get_instance();
    return (get_user_role() == $CI->config->item('super_user_role'));
}

function get_admin_data()
{
    $CI = get_instance();
    $_table_prefix = $CI->config->items('prefix');
    $CI->db->select('email, first_name');
    $CI->db->where('role', 'super_admin');
    $CI->db->where('notify_new_user', 1);
    $data = $CI->db->get($_table_prefix . 'dashboard_login')->result();
    
    return $data;
}

/* 
 * get background image for listing and detail view 
 */

function getBackgroundImage(){
    
    $CI = get_instance();
    $userBgDirectory = $CI->config->item("background_image_upload_path");
    $tenantBgDirectory = $CI->config->item("tenant_background_image_upload_path");
    $user_instance = BUserHelper::get_instance();
    $CI->load->model('portal/Amazon_s3_model');
    $CI->load->helper('portal/utility');

    if ( isset($user_instance->user->background_image) && stripos( $user_instance->user->background_image, 'https://') !== false ) {
        return $user_instance->user->background_image;
    }
    
    if(!empty($user_instance->user->background_image) && 
        (is_file($userBgDirectory.$user_instance->user->background_image) || 
        $CI->Amazon_s3_model->checkS3FileExists( $user_instance->user->background_image )) ) {
        
        return check_bg_img( $user_instance->user->background_image, $userBgDirectory );
        
    } elseif ( !empty($user_instance->tenant->background_image) &&
        (is_file($tenantBgDirectory.$user_instance->tenant->background_image) || 
        $CI->Amazon_s3_model->checkS3FileExists( $user_instance->tenant->background_image )) ) {
        
        return check_bg_img( $user_instance->tenant->background_image, $tenantBgDirectory );
        
    } else {
        
        return $CI->config->item('#DEFAULT_BACKGROUND_IMAGE');
    }
}

/**
 * Get current selected tenant id
 */
function getActiveTenantId ()
{
    $CI = & get_instance();
    if ( $CI->session->userdata('current_account_id') ) 
        return $CI->session->userdata('current_account_id');
    return 0;
}


// function for attributes

function renderAttributeValuesFromCat ( $categoryId, $itemId, $type ) {
        
    $CI = & get_instance();
    $CI->load->model("portal/Attributes_Model");

    return $CI->Attributes_Model->renderAttributeValuesFromCat ( $categoryId, $itemId, $type );
}

function getCategories () {

    $CI = & get_instance();
    $CI->load->model("portal/Attributes_Model");
    
    return $CI->Attributes_Model->getCategoriesLists() ;
}

function dd($array, $exit=false)
{
    echo '<pre>';
    print_r($array);
    echo '</pre>';
    if ( $exit ) exit;
}

function hasAppOwnerExtraAuthorization ()
{
    $CI = & get_instance();
    $userHelper = BUserHelper::get_instance();
    $appOwnerRoleSlug = $CI->config->item("app_owner_role");
    if ( empty( $userHelper->user_role->extra_authorization ) || $userHelper->user->role !== $appOwnerRoleSlug ) return false;
    return true;
}

function getUsedIdFromEmail($email)
{
    $CI = get_instance();
    $CI->db->select('id');
    $CI->db->from('dashboard_login');
    $CI->db->where('email', $email);
    return $CI->db->get()->row()->id;

}

function getAllHistory($tableName, $id, $limit = null)
{
    $CI = &get_instance();
    $CI->db->select('event_log.*, dashboard_login.first_name, dashboard_login.last_name');
    $CI->db->from('event_log');
    $CI->db->join('dashboard_login', 'on event_log.user_id=dashboard_login.id', 'left');
    $CI->db->where('event_log.table_name', $tableName);
    $CI->db->where('event_log.row_id', $id);
    $CI->db->where('event_log.field IS NOT NULL');
    $CI->db->where('event_log.is_deleted', 0);
    $CI->db->where('event_log.account_id',  get_account_id());
    $CI->db->order_by('event_log.time',  'DESC');
    if ( !is_null($limit) ) $CI->db->limit($limit);
    $query=$CI->db->get()->result_array();
    
    return $query;
}


function projectDutchTimeDate($local="en", $time = 0, $nowTime=false){
    if($local == "nl"){
        setlocale(LC_TIME , 'nl_NL');
    }
    if(empty($time) && $nowTime){
        $time = time();
    } else if ( empty($time) && !$nowTime ) {
        return null;
    }
    return strftime("%d %b %Y %H:%M  ", $time);
}


function dutchTimeDate($local="en", $time = 0){

    if($local == "nl"){
        setlocale(LC_TIME , 'nl_NL');
    }
    if(empty($time)){
        $time = time();
    }

    return strftime("%d %h %Y %H:%M  ", $time);
}

function getUserEmailFromId($id)
{
    $CI = get_instance();
    $CI->db->select('*');
    $CI->db->from('dashboard_login');
    $CI->db->where('id', $id);
    return $CI->db->get()->row()->email;

}