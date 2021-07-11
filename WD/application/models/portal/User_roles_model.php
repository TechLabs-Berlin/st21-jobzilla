<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class User_roles_model extends CI_Model
{

    public $_table_prefix;

    public $table_name = 'user_roles';
    
    private $templateName;

    public function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
        $this->load->model('portal/Verify_user_model');
        $this->templateName = $this->config->item('template_name');
    }

    public function is_slug_unique($sent_data_from_ajax)
    {
        $row_id = $sent_data_from_ajax['row_id'];
        $slug_name = $sent_data_from_ajax['slug'];
        
        $success = 1;
        $msg = '';
        
        if ($row_id == '0') {
            
            // search all for slug's uniqness
            
            $this->db->select("*");
            $this->db->from("user_roles");
            $this->db->where('slug', $slug_name);
            
            $result = $this->db->get()->result_array();
            
            if (sizeof($result) > 0 && isset($result)) {
                
                $msg = dashboard_lang("_USER_ROLE_ALREADY_EXISTS");
                $success = 0;
            }
        } else {
            
            // search all except the $row_id's slug
            $this->db->select("*");
            $this->db->from("user_roles");
            $this->db->where('slug', $slug_name);
            $this->db->where('id !=', $row_id);
            
            $result = $this->db->get()->result_array();
            
            if (sizeof($result) > 0 && isset($result)) {
                
                $msg = dashboard_lang("_USER_ROLE_ALREADY_EXISTS");
                $success = 0;
            }
        }
        
        echo json_encode(array(
            'success' => $success,
            'msg' => $msg
        ));
    }
    
    public function get_specific_dl_data( $condition_value, $get_field, $condition_field, $table_name ){
        $tenant_id = $this->session->userdata('account_id');
        $this->db->select($get_field);
        $this->db->where($condition_field, $condition_value);
        $res = $this->db->get($table_name)->result_array();
        if( sizeof( $res ) > 0 ){
            return $res[0][$get_field];
        } else {
            return false;
        }
    }
    
    public function get_specific_data( $condition_array = array(), $get_field, $table_name ){
        $tenant_id = $this->session->userdata('account_id');
        $this->db->select($get_field);
        if( sizeof( $condition_array ) > 0 ){
            $this->db->where($condition_array);
        }
        $res = $this->db->get($table_name)->result_array();
        if( sizeof( $res ) > 0 ){
            return $res;
        } else {
            return false;
        }
    }
    
    public function get_is_2fa_enabled( $udata ){
        $is_2fa = 0;
        $tenant_2fa = $this->get_specific_data( array( 'id' => $udata['account_id'] ), '2fa', 'accounts' );
        if( sizeof( $tenant_2fa ) > 0 ){
            $is_2fa = $tenant_2fa[0]['2fa'];
            if( $is_2fa == 1 ){
                $role_2fa = $this->get_specific_data( array( 'slug' => $udata['user_role'] ), '2fa_options', 'user_roles' );
                if( sizeof( $role_2fa ) > 0 ){
                    $is_2fa = $role_2fa[0]['2fa_options'];
                    if( $is_2fa == 2 ){
                        $user_2fa = $this->get_specific_data( array( 'id' => $udata['user_id'] ), "user_2fa_options", "dashboard_login" );
                        if( sizeof( $user_2fa ) > 0 ){
                            $is_2fa = $user_2fa[0]['user_2fa_options'];
                        }
                    }
                }
            }
            
        }
        return $is_2fa;
        
    }
    
    public function get_max_failed_attempt( $tenant_id ){
        $returned_val = 0;
        $tenant_info = $this->get_specific_data( array( "id" => $tenant_id ), "attempt", "accounts");
        if( sizeof( $tenant_info ) > 0 ){
            $returned_val = intval( $tenant_info[0]['attempt'] );
        }
        return $returned_val;
    }
    
    public function get_max_timeout_for_auth( $tenant_id )
    {
        $returned_val = 0;
        $tenant_info = $this->get_specific_data( array( "id" => $tenant_id ), "timeout", "accounts");
        if( sizeof( $tenant_info ) > 0 ){
            $returned_val = intval( $tenant_info[0]['timeout'] )*60;
        }
        return $returned_val;
    }
    
    public function get_hint_contact( $contact_info, $first_characters, $last_character ){
        $fcount = intval( $first_characters );
        $lcount = intval( $last_character );
        $lcount_m = $lcount - 1;
        
        $fset = substr($contact_info, 0, $fcount);
        $length = intval( strlen( $contact_info ) - 1 );
        $lset = substr($contact_info, ( $length - $lcount_m ), $lcount);
            
        $response = $fset . '*****' . $lset;
        return $response;
    }
    
    public function generate_verification_code($mobile_number="", $return=false){
        $tenant_id = $this->session->userdata('account_id');
        $user_id = $this->session->userdata('user_id');
        if ( empty( $user_id ) ) $user_id = $this->session->userdata('verify_user_id');
        $mobile_number = empty( $mobile_number )? $this->input->post('mobile_number'): $mobile_number;
        $type = $this->input->post('type');
        $condition_array = array( "user_id" => $user_id, "account_id" => $tenant_id, "mobile_number" => $mobile_number );
        $timeout = $this->get_specific_data(array("id" => $tenant_id), "timeout", "accounts");
        $verification_data = $this->get_specific_data( $condition_array, '*', 'user_verification_mobile' );
        $code = $this->Verify_user_model->generate_code();
        $phone =  $this->input->post('mobile_number');
        $mobile_postfix = $this->input->post("mobile_postfix");
        $mobile_prefix = $this->input->post("mobile_prefix");
        $hint_mbl_number = $this->get_hint_contact( $mobile_number, 2, 3 );
        $isInserted = TRUE;
        $data = array(
            "mobile_number" => $mobile_number,
            "code" => $code,
            "created_at" => time(),
            "is_code_verified" => 0,
            "user_id" => $user_id,
            "account_id" => $tenant_id
        );
        
        if( ! $verification_data ){
           
            $isInserted = $this->Sms_api_model->send_sms( $mobile_number, $code );
            
            if( $isInserted ){
                
                $this->db->insert( "user_verification_mobile",  $data );
                
                
                $retured_val = array(
                            'status' => 'modal',
                            'html_render' => $this->render_verification_modal( array( "hint_mbl_number" => $hint_mbl_number )), 
                            'id' => $this->db->insert_id()
                        );
            } else {
                $retured_val = array(
                            'status' => '0',
                            'alert_msg' => dashboard_lang('_CODE_COULD_NOT_BE_SENT'), 
                            'id' => 0
                        );
            }
            if ( $return ) return $retured_val;
            echo json_encode( $retured_val );
        } else {
            foreach ( $verification_data as $v_data ){
                if( $v_data['mobile_number'] != $mobile_number ){
                    $isInserted = $this->Sms_api_model->send_sms( $mobile_number, $code );
                    
                    if( $isInserted ){
                        $this->db->insert( "user_verification_mobile",  $data );
                        $retured_val = array(
                            'status' => 'modal',
                            'html_render' => $this->render_verification_modal( array( "hint_mbl_number" => $hint_mbl_number ) ), 
                            'id' => $this->db->insert_id()
                        );
                    } else {
                        $retured_val = array(
                            'status' => '0',
                            'alert_msg' => dashboard_lang('_CODE_COULD_NOT_BE_SENT'), 
                            'id' => $v_data['id']
                        );
                    }
                } else if( $v_data['mobile_number'] == $mobile_number ) {
                    if( $v_data['is_code_verified'] == 1 ){
                        $retured_val = array( "status" => "alert", "alert_msg" => dashboard_lang("_CODE_ALREADY_VERIFIED"), 'id' => $v_data['id'], "msg" => "<i class='fa fa-check-circle' aria-hidden='true'></i>".dashboard_lang("_CODE_ALREADY_VERIFIED__VERIFIED_ON")." ". date("d F Y H:i", $v_data['code_verified_on']), "verified_on" => $v_data['code_verified_on'] );
                        $data = array(
                            "mobile_number" => $phone ,
                            "mobile_postfix"=>$mobile_postfix,
                            "mobile_prefix"=>$mobile_prefix,
                            'is_phone_verified'=> 1,
                            'phone_verified_on'=> $v_data['code_verified_on']
                        );
                        $this->phoneVerifiedAction($data);
                    } else {
                        
                        $timeoutInMin = $timeout[0]['timeout'];
                        if ( $timeoutInMin === false || $timeoutInMin < '1' ) {
                            if ( $this->config->item('#DEFAULT_MAX_TIMEOUT_FOR_VERIFICATION') == NULL ) {
                                $timeoutInMin = $this->config->item('timeout_for_mobile_verification_in_minute');
                            } else {
                                $timeoutInMin = $this->config->item('#DEFAULT_MAX_TIMEOUT_FOR_VERIFICATION');
                            }
                        }
                        
                        if( $v_data['created_at'] + ( intval( $timeoutInMin ) * 60 ) > time() ){
                            $retured_val = array(
                                        'status' => 'modal',
                                        'html_render' => $this->render_verification_modal( array( "hint_mbl_number" => $hint_mbl_number ) ), 
                                        'id' => $v_data['id']
                                    );
                        } else {
                            $isInserted = $this->Sms_api_model->send_sms( $mobile_number, $code );
                            
                
                            if( $isInserted ){
                                $this->db->where('id', $v_data['id']);
                                $is_code_generated = $this->db->update( "user_verification_mobile",  $data );
                                $retured_val = array(
                                        'status' => 'modal',
                                        'html_render' => $this->render_verification_modal( array( "hint_mbl_number" => $hint_mbl_number ) ), 
                                        'id' => $v_data['id']
                                    );
                            } else {
                                $retured_val = array(
                                    'status' => '0',
                                    'alert_msg' => dashboard_lang('_CODE_COULD_NOT_BE_SENT'), 
                                    'id' => $v_data['id']
                                );
                            }
                        }
                    }
                }       
            }
            if ( $return ) return $retured_val;
            echo json_encode( $retured_val );
        }
    }
    
    public function render_verification_modal( $data ){
        return $this->load->view("$this->templateName/core_$this->templateName/authentication/mobile-verification/verification-modal-view", $data, TRUE);
    }
    
    public function verify_the_code()
    {
        $_table_name = "user_verification_mobile";
        $phone =  $this->input->post('mobile_number');
        $mobile_postfix = $this->input->post("mobile_postfix");
        $mobile_prefix = $this->input->post("mobile_prefix");
        $this->db->select("code,id,is_code_verified,code_verified_on");
        $this->db->from($_table_name);
        $this->db->where('user_id', $this->session->userdata('user_id'));
        $this->db->where('mobile_number', $phone);
        $this->db->where('code', $this->input->post('code'));
    
        $result = $this->db->get()->result_array();
        $data = array(
            "mobile_number" => $phone ,
            "mobile_postfix"=>$mobile_postfix,
            "mobile_prefix"=>$mobile_prefix
        );
        if (sizeof($result) > 0) {
           $code_verified = $result['0']['is_code_verified'];
           if ( $code_verified == '0' ) {
                $this->db->where("id", $result['0']['id']);
                $this->db->update($_table_name, array("is_code_verified" => 1, "code_verified_on" => time()));
                $data['is_phone_verified'] = 1;
                $data['phone_verified_on'] = time();
                $this->phoneVerifiedAction($data);
                $response =  array("status" => 1, "phone" => $phone,  "msg" => "<i class='fa fa-check-circle' aria-hidden='true'></i>".dashboard_lang('_CODE_SUCCESSFULLY_VERIFIED_VERIFIED_ON')." ".date( "d F Y H:i", time() ) , "verified_on" => dashboard_lang("_VERIFIED_ON") . " " . date( "d F Y H:i", time() ) );
           }else {
            $data['is_phone_verified'] = 1;
            $data['phone_verified_on'] = $result['0']['code_verified_on'];
               $this->phoneVerifiedAction($data);
               $response =  array("status" => 0, "phone" => $phone, "msg" => "<i class='fa fa-check-circle' aria-hidden='true'></i>".dashboard_lang("_CODE_ALREADY_VERIFIED__VERIFIED_ON")." ".date( "d F Y H:i", $result[0]["code_verified_on"] ), "verified_on" => $result[0]['code_verified_on']);
           }
            
        }else {
            $response =  array("status" => 0, "phone" => $phone, "msg" => "<div class='alert alert-danger alert-dismissible fade show m-alert m-alert--square m-alert--air' role='alert'> <button type='button' class='close' data-dismiss='alert' aria-label='Close'></button>".dashboard_lang("_CODE_DOES_NOT_MATCH_PLEASE_CHECK_AGAIN")."</div>", "verified_on" => 0);
        }
        echo json_encode($response);
    }
    
    public function phoneVerifiedAction( $data ) {

        $this->db->where("id", $this->session->userdata('user_id'));
        $this->db->update("dashboard_login", $data);
        
    }

    public function getUserRolesLists () {

        $result = $this->db->get_where( $this->table_name, [
            "is_deleted" => 0
        ])->result_array();

        return $result;
    }


    public function checkIfROleAllowedToEditTemplate() {

        $currentUserRole = get_user_role();

        $result = $this->db->get_where( $this->table_name, [
            "slug" => $currentUserRole,
            "is_deleted" => 0
        ] )->row_array();

        $roleId = $result["id"];

        $settingsValue = explode(",", $this->config->item("#PORTAL_SHARED_LISTVIEW_TEMPLATE_EDIT_BY_USER_ROLE"));

        if (in_array( $roleId, $settingsValue ) ) {

            return true;
        }else {
            return false;
        }
    }
    
}


