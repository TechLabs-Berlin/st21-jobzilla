<?php
/*
 * @author boo2mark
 *
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

function send_email($to, $to_name, $cc, $subject, $body)
{
    $CI = & get_instance();
    $CI->load->model('portal/Utility_model');
    $CI->load->model('portal/Mail_Model');
    #$CI->load->library('email');
    
    $from_name = $CI->Utility_model->get_mail_sender_name();
    $from = $CI->Utility_model->get_mail_sender_address();
    $bcc = $CI->Utility_model->get_BCC();
    /*
    $CI->email->clear(TRUE);
    $CI->email->set_newline("\r\n");
    $CI->email->from($from, $from_name);
    $CI->email->to($to, $to_name);
    $CI->email->bcc($bcc);
    $CI->email->subject($subject);
    $CI->email->message($body);
    $isSend = $CI->email->send();
    */

    $isSend = $CI->Mail_Model->send_mail($to, $from_name, $from, '', $body, $subject, '', $to_name, '', $bcc);
    
    activityEmail($from_name, $from, $to, $cc, $bcc, $subject, $body);
    
    if ($isSend) {
        
        return true;
    } else {
        
        return false;
        // return $CI->email->print_debugger();
    }
}

function render_user_invitation_info($user_id)
{
    $CI = & get_instance();
    $CI->load->model('portal/Dashboard_login_model');
    return $CI->Dashboard_login_model->render_user_invitation_info($user_id);
}

function email_sender($data, $type = '')
{
    $CI = & get_instance();
    
    $CI->load->model('portal/Utility_model');
    $CI->load->model('portal/Mail_Model');
    #$CI->load->library('email');
    $CI->load->config('dashboard');
    
    $from_name = $CI->Utility_model->get_mail_sender_name();
    $from = $CI->Utility_model->get_mail_sender_address();
    $CC = "";
    $BCC = $CI->Utility_model->get_BCC();
    $app_logo = $CI->Utility_model->get_application_logo();
    $theme_name = $CI->config->item('template_name');
    if ($type == 'password_reset') {
        
        $subject = dashboard_lang("_PASSWORD_RESET_NOTIFICATION");
        $to = $data['to'];
        $to_name = $data['first_name'];
        $view_data['email'] = $to;
        $view_data['first_name'] = $data['first_name'];
        $view_data['url'] = $data['url'];
        if(empty($data['logo'])){
            $view_data['logo'] = $app_logo;
        } else{
            $view_data['logo'] = $data['logo'];
        }
        if(empty($data['app_name'])){
            $view_data['app_name'] = $from_name;
        } else{
            $view_data['app_name'] = $data['app_name'];
        }       
        
        $msg_data = $CI->load->view( $theme_name .'/core_'.$theme_name.'/email/reset_password', $view_data, true);
    } else 
        if ($type == 'change_email') {
            
            // send change email notification
            
            $subject = dashboard_lang("_EMAIL_CHANGE_NOTIFICATION");
            $to = $data['old_email_address'];
            $to_name = $data['first_name'];
            $view_data['email'] = $to;
            $view_data['first_name'] = $data['first_name'];
            $view_data['old_email_address'] = $data['old_email_address'];
            $view_data['new_email_address'] = $data['new_email_address'];
            $view_data['logo'] = $app_logo;
            $view_data['app_name'] = $from_name;
            
            $msg_data = $CI->load->view($theme_name .'/core_'.$theme_name.'/email/change_email_notification', $view_data, true);
            /*
            if ($smtp_status) :
                $CI->email->set_newline("\r\n");
            
            
             endif;
             
            $CI->email->from($from, $from_name);
            $CI->email->to($to, $to_name);
            $CI->email->bcc($BCC);
            $CI->email->subject($subject);
            $CI->email->message($msg_data);
            $status = $CI->email->send();
            */
            $status = $CI->Mail_Model->send_mail($to, $from_name, $from, '', $msg_data, $subject, '', $to_name, '', '');
            // prepare data for sending email to new email address
            
            $subject = dashboard_lang("_EMAIL_CHANGE_NOTIFICATION");
            $to = $data['new_email_address'];
            $to_name = $data['first_name'];
            $view_data['email'] = $to;
            $view_data['first_name'] = $data['first_name'];
            $view_data['url'] = $data['url'];
            $view_data['logo'] = $app_logo;
            $view_data['app_name'] = $from_name;
            
            $msg_data = $CI->load->view($theme_name .'/core_'.$theme_name.'/email/change_email', $view_data, true);
        } else 
            if ($type == "sign_up") {
                
                $subject = dashboard_lang("_SIGN_UP_NOTIFICATION");
                $to = $data['data_result']['email'];
                $to_name = $data['data_result']['first_name'];
                
                $view_data['email'] = $to;
                $view_data['password'] = $data['new_password'];
                $view_data['first_name'] = $data['data_result']['first_name'];
                $view_data['logo'] = $app_logo;
                $view_data['app_name'] = $from_name;
                
                $msg_data = $CI->load->view($theme_name .'/core_'.$theme_name.'/email/signup', $view_data, true);
                
                $admin_data = $CI->Utility_model->get_admin_data();
                
                foreach ($admin_data as $row) {
                    
                    $admin_subject = dashboard_lang("_NEW_USER_NOTIFICATION");
                    
                    $view_data['first_name'] = $row->first_name;
                    $view_data['user_id'] = $data['user_id'];
                    $view_data['logo'] = $app_logo;
                    $view_data['app_name'] = $from_name;
                    $admin_email = $row->email;
                    
                    $admin_msg_data = $CI->load->view($theme_name .'/core_'.$theme_name.'/email/admin_notification', $view_data, true);
                    
                    $CI->Mail_Model->send_mail($admin_email, $from_name, $from, '', $admin_msg_data, $admin_subject, '', $row->first_name, '', $BCC);
                }
            } else 
                if ($type == "welcome_email") {
                    
                    $subject = dashboard_lang("_WELCOME_EMAIL_SUBJECT");
                    $to = $data['email'];
                    $to_name = $data['first_name'];
                    $id = $data['id'];
                    
                    if (isset($data['password'])) {
                        $password = $data['password'];
                    } else {
                        $password = update_password($id);
                    }
                    
                    $view_data['email'] = $to;
                    $view_data['password'] = $password;
                    $view_data['first_name'] = $data['first_name'];
                    $view_data['logo'] = $app_logo;
                    $view_data['app_name'] = $from_name;
                    
                    $msg_data = $CI->load->view($theme_name .'/core_'.$theme_name.'/email/welcome_email', $view_data, true);
                } elseif ($type == "return_user") {
                    
                    $admin_data = $CI->Utility_model->get_admin_data();
                    
                    foreach ($admin_data as $row) {
                        
                        $admin_subject = dashboard_lang("_RETURN_USER_NOTIFICATION");
                        
                        $view_data['first_name'] = $row->first_name;
                        $view_data['user_id'] = $data['user_id'];
                        $view_data['logo'] = $app_logo;
                        $view_data['app_name'] = $from_name;
                        $admin_email = $row->email;
                        
                        $admin_msg_data = $CI->load->view($theme_name .'/core_'.$theme_name.'/email/admin_notification', $view_data, true);
                        
                         $CI->Mail_Model->send_mail($admin_email, $from_name, $from, '', $admin_msg_data, $admin_subject, '', $row->first_name, '', $BCC);
                    }
                } else {
                    
                    // required data for email
                    
                    $subject = $data['subject'];
                    $to = $data['to'];
                    $to_name = $data['to_name'];
                    $msg_data = $data['msg_data'];
                    
                    // optional data for email
                    
                    if (isset($data['from']) and ! empty($data['from'])) {
                        $from = $data['from'];
                    }
                    
                    if (isset($data['from_name']) and ! empty($data['from_name'])) {
                        $from_name = $data['from_name'];
                    }
                    
                    if (isset($data['BCC']) and ! empty($data['BCC'])) {
                        $BCC = $data['BCC'];
                    }
                    if (isset($data['CC']) and ! empty($data['CC'])) {
                        $CC = $data['CC'];
                    }
                }
    

    $CI = & get_instance();
    $CI->load->library('email');    
    $CI->load->model('portal/Events_Model');

    // common email sender

    $CI->email->clear(TRUE);
    
    $smtp_status = $CI->config->item('smtp_status');  

    if ($smtp_status) :
        $CI->email->set_newline("\r\n");
    
    
     endif;
    $CI->email->from($from, $from_name);
    $CI->email->to($to, $to_name);
    $CI->email->cc($CC);
    $CI->email->bcc($BCC);
    $CI->email->subject($subject);
    $CI->email->message($msg_data);
    if (isset($data['files']) and sizeof($data['files']) > 0) {
        foreach ($data['files'] as $file) {
            if ((stripos($file, "http://") !== false) || (stripos($file, "https://") !== false)) {
                $CI->email->attach($file);
            } else {
                $CI->email->attach(FCPATH . 'uploads/' . $file);
            }
        }
    }

    $status = $CI->email->send();

    $CI->Events_model->_email_message = $msg_data; 
    $CI->Events_model->executeEvent('email_sent', 'email sent');
    $CI->Events_model->_email_message = ''; 

    // Save Email History
    activityEmail($from_name, $from, $to, $CC, $BCC, $subject, $msg_data);
    return $status;
}

/*
 * Save User Email Activity
 */
function activityEmail($firstname, $from, $to, $cc, $bc, $subject, $message)
{
    $CI = & get_instance();
    $logEmail = array(
        'email_sendout_time' => strtotime('now'),
        'from_name' => $firstname,
        'from_email' => $from,
        'email_to' => $to,
        'email_cc' => $cc,
        'email_bcc' => $bc,
        'email_subject' => $subject,
        'email_message	' => $message
    );
    $saveEmailActivity = $CI->db->insert('user_email_activity', $logEmail);
    // return $saveEmailActivity;
}

/*
 * End of Save User Email Activity
 */
function update_password($id)
{
    $CI = & get_instance();
    $CI->load->model('Dashboard_login_model');
    $pass = $CI->Dashboard_login_model->update_password($id);
    return $pass;
}

function redirect_to_table_listing($table)
{
    $CI = & get_instance();
    $location = "Location: " . base_url() . $CI->config->item('controller_sub_folder') . '/' . $table . '/listing';
    header($location);
}

function get_record_position_in_page($position, $per_page)
{
    $record_position = "";
    
    if (isset($position)) {
        
        for ($i = 1; $i <= 100; $i ++) {
            
            $record_in_page = $per_page * $i;
            if ($record_in_page >= $position) {
                $record_position = $record_in_page - $per_page;
                break;
            }
        }
    }
    
    return $record_position;
}
// get a record date by primary key
function getDataFromId($tableName, $recordId, $fieldName = "id", $singleRow = false, $returnArr = false, $sortField = "", $sortOrder = "asc", $isDeleted=true)
{
    $CI = & get_instance();
    $CI->db->select('*');
    $CI->db->where($fieldName, $recordId);
    if($isDeleted){
        $CI->db->where('is_deleted', 0);
    }
    if (! empty($sortField)) {
        $CI->db->order_by($sortField, $sortOrder);
    }
    if ($returnArr) {
        return $CI->db->get($tableName)->result_array();
    } elseif ($singleRow) {
        return $CI->db->get($tableName)->row();
    } else {
        return $CI->db->get($tableName)->result();
    }
}

function getDataFromTbl($tableName, $selectedField = "*", $whereClauseArr = array(), $orderingClasue = "", $singleRow = false, $returnArr = false)
{
    $CI = & get_instance();
    $CI->db->select($selectedField);
    if (sizeof($whereClauseArr) > 0)
        $CI->db->where($whereClauseArr);
    if (strlen($orderingClasue) > 0)
        $CI->db->order_by($orderingClasue);
    $queryResult = $CI->db->get($tableName);
    if ($returnArr)
        return $queryResult->result_array();
    elseif ($singleRow)
        return $queryResult->row();
    else
        return $queryResult->result();
}

// check bg image s3 url
function check_bg_img($file_name, $uploadDir=''){

    if(!empty($file_name)){
        
        $CI = &get_instance();
        if ((stripos($file_name, "http://") !== false) || (stripos($file_name, "https://") !== false)) {
            return $file_name;
        } else {
            
            $file_path = '';
            if( !empty($uploadDir) ){
                
                $file_path = $file_path.$uploadDir;
            } else {
                
                if ( file_exists( 'uploads/tenant_background_image/'.$file_name ) ) {
                    
                    return 'uploads/tenant_background_image/'.$file_name;
                } else if ( file_exists( 'uploads/background_image/'.$file_name ) ) {
                    
                    return 'uploads/background_image/'.$file_name;
                } else if ( file_exists( $file_name ) ) {
                    
                    return $file_name;
                } else {
                    
                    return $CI->config->item('#DEFAULT_BACKGROUND_IMAGE');
                }
            }
            return $file_path.$file_name;
        }
    }else {
        return '';
    }

}

function autoversion($url) {
        
    $modfiedTime = filectime($url);
    
    return CDN_URL.$url."?v=".$modfiedTime;
    
}

// functionality for multiselect 

function renderMultiSelectOptions ( $ref_table_name, $ref_key, $ref_value, $all_values_list, $order_on, $order_by, $is_translated ) 
{  
    $CI = & get_instance();
    $CI->load->model('EavModel'); 
    return $CI->EavModel->renderMultiSelectOptions( 
        $ref_table_name, 
        $ref_key, 
        $ref_value, 
        $all_values_list,
        $order_on,
        $order_by,
        $is_translated
    );
} 

//
function getUserDetails( $userId ) {
    
    $CI = & get_instance();
    $CI->load->model("Dashboard_login_model");
    
    $result = $CI->Dashboard_login_model->getUserDetails ( $userId );
    
    if ( $result != '' ) {
        
        return $result["user_2fa_options"];
    }else {
        return 0;
    }
}



function  getStatusForColor($id){
    $CI = & get_instance();
    $CI->load->model('EavModel'); 
    return $CI->EavModel->getStatusForColor($id);
}

function  checkAllSelectedStatus($id,$fieldName="statuses_id", $tableName ="statuses_comes_after"){
    $CI = & get_instance();
    $CI->load->model('EavModel'); 
    return $CI->EavModel->checkAllSelectedStatus($id,$fieldName, $tableName);
}


function getCustomStatusListsByName ( $tableName, $fieldName ) {

    $CI = &get_instance();
    $CI->load->model("portal/Customers_Model");

    $result = $CI->Customers_Model->getStatusListsByName ($tableName, $fieldName);

    return $result;
}


function checkIfROleAllowedToEditTemplate() {

    $CI = &get_instance();
    $CI->load->model("portal/User_roles_model");

    $result = $CI->User_roles_model->checkIfROleAllowedToEditTemplate ();

    return $result;
}

function random_password() 
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $password = array(); 
    $alpha_length = strlen($alphabet) - 1; 
    for ($i = 0; $i < 8; $i++) 
    {
        $n = rand(0, $alpha_length);
        $password[] = $alphabet[$n];
    }
    return implode($password); 
}

function  getSelectAllEav($id, $insertTable, $insertTableForignKey, $insertTableReferenceKey){
    
    $CI = & get_instance();
    $CI->load->model('EavModel'); 
    return $CI->EavModel->getSelectAllEav($id, $insertTable, $insertTableForignKey, $insertTableReferenceKey);
}

function getTableColumnWidth ( $tableName, $fieldName ) {

    $CI = &get_instance();

    $customFieldsAllowed = strtolower ( $CI->config->item('#CORE_CUSTOM_FIELDS_WIDTH_ALLOWED'));

    if ( $customFieldsAllowed == 'true' ) {


        $settingsDefinedForFields = $CI->config->item("#CORE_FIELDS_MAXWIDTHPX_".strtoupper($tableName)."_".strtoupper( $fieldName));
        if ( strlen($settingsDefinedForFields) > 0 ) {

            return $settingsDefinedForFields;
        }else {

            return $CI->config->item("#CORE_FIELDS_DEFAULT_MAXWIDTHPX");
        }
    }else {

        return "";
    }
}

function getTotalTableWidth( $listingField, $tableName ) {

    $CI = &get_instance();
    $customFieldsAllowed = strtolower ( $CI->config->item('#CORE_CUSTOM_FIELDS_WIDTH_ALLOWED'));

    $hasDefinedFields = false;

    foreach ($listingField as $fieldName) { 

        $settingsDefinedForFields = $CI->config->item("#CORE_FIELDS_MAXWIDTHPX_".strtoupper($tableName)."_".strtoupper( $fieldName));

        if ( strlen($settingsDefinedForFields) > 0 && strpos( $settingsDefinedForFields, "px" ) !== false ) {

            $hasDefinedFields = true;
        }
    }

    if ( $hasDefinedFields && $customFieldsAllowed == 'true') {

        $totalPixel = 82;

        foreach ($listingField as $fieldName) { 
            $settingsDefinedForFields = $CI->config->item("#CORE_FIELDS_MAXWIDTHPX_".strtoupper($tableName)."_".strtoupper( $fieldName));

            if ( strlen($settingsDefinedForFields) > 0 ) {

                $eachColumnPixel = (int) filter_var( $settingsDefinedForFields, FILTER_SANITIZE_NUMBER_INT);
            }else {

                $eachColumnPixel = (int) filter_var( $CI->config->item("#CORE_FIELDS_DEFAULT_MAXWIDTHPX"), FILTER_SANITIZE_NUMBER_INT);
            }

            $totalPixel = $totalPixel + $eachColumnPixel + 20;
        }
        
        return "width: {$totalPixel}px !important;";

    }else {

        return "";
    }
}