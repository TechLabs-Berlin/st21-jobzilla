<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Verify_user_model extends CI_Model
{

    private $_table_name = "user_phone_verification";
    private $_users_table_name = "dashboard_login";

    function __construct()
    {
        parent::__construct();
        $this->load->model('portal/Sms_api_model');
        $this->load->model('portal/login_sessions');
        $this->load->library("portal/Authy");
    }

    public function check_user_email_verified()
    {
        $udata = $this->session->all_userdata();

        if (isset($udata['verify_user_id']) && strlen($udata['verify_user_id']) > 0) {
            
            $getMaxAttmetTime = $this->getMaxAttemptTime( $udata['verify_user_id'] );
            if (isset($getMaxAttmetTime) && $getMaxAttmetTime > time()) {
                
                $check_settings = $this->config->item('#ENABLE_2FA_AUTHENTICATION');
                if ($check_settings == '1') {
                    redirect(site_url('dashboard/user_phone_verification'));
                }
            } else {
                $this->unset_email_logged_sess_data();
            }
        } else {
            $user = BUserHelper::get_instance(); 

            if(is_object($user) && @$user->user->id > 0 ){
                //do nothing because user alrady loged in 
            }else{
                //flash user data 
                $this->unset_email_logged_sess_data();
            }
            
        }
    }
    
    public function getMaxAttemptTime( $userId ) {
    
        $this->db->select("code_expiration_time");
        $result = $this->db->get_where("user_phone_verification", array(
            "user_id" => $userId,
            "is_verified" => 0
        ))->result_array();
    
        if ( sizeof($result) > '0') {
            return $result[0]['code_expiration_time'];
        }else {
            return 0;
        }
    }

    function checking_db_cookie($udata)
    {
        $_table_name = $this->_table_name;
        $this->db->select('max_remember_me_time');
        $this->db->from($_table_name);
        $this->db->where('user_id', $udata['user_id']);
        $this->db->where('is_verified', 1);
        $this->db->where('max_remember_me_time >', 0);
        $result = $this->db->get()->result_array();
        
        if (sizeof($result) > '0') {
            
            if ($result[0]['max_remember_me_time'] > time()) {
                
                $this->Login_sessions->set_session_data();
                $this->Login_sessions->redirecting_to_dboard();
                exit();
            }
        }
    }

    public function pushing_code_expiration_time($udata, $time)
    {
        $_tbl_name = $this->_table_name;
        
        $code_expiration_time_attr = array(
            'code_expiration_time' => $time
        );
        
        $this->db->select("code_expiration_time");
        $this->db->where("user_id", $udata['verify_user_id']);
        $this->db->where("is_verified", 0);
        $val_arr = $this->db->get($_tbl_name)->result_array();
        
        if (empty($val_arr)) {
            $this->db->insert($_tbl_name, $code_expiration_time_attr);
        } else {
            
            $this->db->update($_tbl_name, $code_expiration_time_attr, array(
                'user_id' => $udata['verify_user_id'],
                'is_verified' => 0
            ));
        }
    }

    public function get_expire_time($udata)
    {
        $_tbl_name = $this->_table_name;
        
        $this->db->select('code_expiration_time');
        $this->db->where('is_verified', 0);
        $this->db->where('user_id', $udata['verify_user_id']);
        
        return $this->db->get($_tbl_name)->result_array();
    }

    function is_user_id_exist($fetched_u_id)
    {
        $_table_name = $this->_table_name;
        
        $udata['verify_user_id'] = $fetched_u_id;
        
        $this->db->select("*");
        $this->db->from($_table_name);
        $this->db->where('user_id', $fetched_u_id);
        $this->db->where('is_verified', 0);
        
        $result = $this->db->get()->result_array();
        
        $expire_time = $this->get_expire_time($udata);
        
        if (isset($expire_time)) {
            if ((sizeof($expire_time) > 0 && $expire_time[0]['code_expiration_time'] > time()) || empty($expire_time)) {
                
                if (sizeof($result) > '0') {
                    
                    if ($result[0]['is_blocked'] == '1' && $result[0]['block_expiration_time'] > '0' && $result[0]['block_expiration_time'] > time()) {
                        
                        $this->session->set_flashdata('failed_attempt_alert', dashboard_lang('_YOU_ARE_BLOCKED_FOR_CERTAIN_PERIOD_PLS_TRY_AGAIN_LATER'));
                        
                        redirect(site_url($this->config->item('login_url')));
                    } else 
                        if ($result[0]['is_blocked'] == '1' && $result[0]['block_expiration_time'] > '0' && $result[0]['block_expiration_time'] < time()) {
                            // generate code, update is_blocked = 0, code, block_expiration_time = 0, max_failed_attempt = 0
                            $upv_column_data = array(
                                'is_blocked' => '0',
                                'max_failed_attempt' => '0',
                                'block_expiration_time' => '0'
                            );
                            
                            $this->db->update($_table_name, $upv_column_data, array(
                                'user_id' => $fetched_u_id,
                                'is_blocked' => 1
                            ));
                            
                            return true;
                        } else {
                            return true;
                        }
                } else {
                    return false;
                }
            } else {
                $this->verify_user_model->unset_email_logged_sess_data();
                $message = dashboard_lang("_CODE_SUBMISSION_TIME_EXPIRED_TRY_AGAIN");
                $this->session->set_flashdata('failed_attempt_alert', $message);
                redirect(site_url($this->config->item('login_url')));
            }
        } else {
            $this->verify_user_model->unset_email_logged_sess_data();
            $message = dashboard_lang("_SOMETHING_WENT_WRONG_PLEASE_TRY_AGAIN");
            $this->session->set_flashdata('failed_attempt_alert', $message);
            redirect(site_url($this->config->item('login_url')));
        }
    }

    public function delete_user_verify_row($udata)
    {
        $_tbl_name = $this->_table_name;
        
        $delete_res = $this->db->delete($_tbl_name, array(
            'user_id' => $udata['user_id'],
            'is_verified' => 0
        ));
        
        if ($delete_res) {
            return true;
        } else {
            $this->verify_user_model->unset_email_logged_sess_data();
            $message = dashboard_lang("_DELETING_PREVIOUS_DATA_FAILED_TRY_AGAIN_TO_LOGIN");
            $this->session->set_flashdata('failed_attempt_alert', $message);
            redirect(site_url($this->config->item('login_url')));
        }
    }

    function generate_code()
    {
        return mt_rand(10000, 99999);
    }

    function insert_user_info($user_id)
    {
        $_table_name = $this->_table_name;
        $last_attempt_time = time();
        $code = $this->generate_code();

        $sms_sent = $this->Sms_api_model->send_code($user_id, $code);
        
        if ($sms_sent) {
            
            $data_to_inserted = array(
                'user_id' => $user_id,
                'code' => $code,
                'last_attempt_time' => $last_attempt_time
            );
            
            $this->db->where('user_id', $user_id);
            $this->db->delete($_table_name);
            
            $result = $this->db->insert($_table_name, $data_to_inserted);
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            
            $this->unset_email_logged_sess_data();
            $this->session->set_flashdata('failed_attempt_alert', dashboard_lang('_CODE_COULD_NOT_SENT_SOMETHING_WENT_WRONG'));
            
            redirect(site_url($this->config->item('login_url')));
        }
    }

    function get_mobile_number($user_id)
    {
        $this->db->select("mobile_number");
        $result = $this->db->get_where("dashboard_login", array(
            "id" => $user_id,
            "is_deleted" => 0
        ))->result_array();
        
        if (sizeof($result) > '0') {
            
            return $result[0]['mobile_number'];
        } else {
            return '';
        }
    }

    function verify_code($udata, $given_code)
    {
        $_table_name = $this->_table_name;
        
        $this->db->select("code");
        $this->db->from($_table_name);
        $this->db->where('user_id', $udata['verify_user_id']);
        $this->db->where('is_verified', 0);
        
        $result = $this->db->get()->result_array();
        if (sizeof($result) > 0) {
            
            if ($given_code == $result['0']['code']) {
                return true;
            } else {
                return false;
            }
        }
    }

    function verify_app_token($udata, $given_code){
        $_table_name = $this->_users_table_name;        
        $this->db->select("authy_id");
        $this->db->from($_table_name);
        $this->db->where('id', $udata['verify_user_id']);        
        $result = $this->db->get()->row_array();
        if (is_array($result)) {              
            // check the token
            $isValidToken = $this->authy->verify_token($result['authy_id'], $given_code);
            if($isValidToken){
                return true;
            }
        }
        return false;
    }

    function setting_verified($udata)
    {
        $_table_name = $this->_table_name;
        
        $is_verified_data = array(
            'is_verified' => '1'
        );
        
        if ($this->db->update($_table_name, $is_verified_data, array(
            'user_id' => $udata['verify_user_id']
        ))) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setAccountId( $userId ) {
        $this->user = new BUser($userId);
        $this->session->set_userdata("account_id", $this->user->account_id);
        
        return $this->user->account_id;
    }

    function updating_wrong_attempt($udata)
    {
        $_table_name = $this->_table_name;
        $this->load->config('phone_verification');
        
        $selected_field = [
            'max_code_generate_attempt',
            'max_failed_attempt'
        ];
        
        $this->db->select($selected_field);
        $this->db->where('user_id', $udata['verify_user_id']);
        $this->db->where('is_verified', 0);
        $result = $this->db->get($_table_name)->result_array();
        
        $incremented_r = $result[0]['max_code_generate_attempt'] + 1;
        $incremented_failed_attempt = $result[0]['max_failed_attempt'] + 1;
        $max_number_of_attempt = intval($this->config->item('#2FA_MAX_CODE_REGENERATE_ATTEMPT'));
        
        $accountId = $this->setAccountId( $udata['verify_user_id'] );
        $max_failed_attempt = $this->User_roles_model->get_max_failed_attempt( $accountId );
        if( $max_failed_attempt < 1 ){
            $max_failed_attempt = $this->config->item('#2FA_MAX_LOGIN_ATTEMPT');
        }
        
        if ($incremented_failed_attempt == $max_failed_attempt) {
            $max_block_time = $this->User_roles_model->get_max_timeout_for_auth( $accountId );
            if( $max_block_time < 1 || empty( $max_block_time ) || is_null( $max_block_time ) ){
                $max_block_time = intval( $this->config->item('max_block_time') );
            }
            $mfa_column_data = array(
                'is_blocked' => '1',
                'max_failed_attempt' => '0',
                'max_code_generate_attempt' => '0',
                'block_expiration_time' => time() + $max_block_time
            );
            
            $this->db->update($_table_name, $mfa_column_data, array(
                'user_id' => $udata['verify_user_id'],
                'is_verified' => 0
            ));
            
            $this->unset_email_logged_sess_data();
            $this->session->set_flashdata('failed_attempt_alert', dashboard_lang('_YOU_ARE_BLOCKED_FOR_CERTAIN_PERIOD_PLS_TRY_AGAIN_AFTER') . " " . ( $max_block_time/60 ) . " " . dashboard_lang('_MINUTES_LATER'));
            
            redirect(site_url($this->config->item('login_url')));
        } else if ( $incremented_failed_attempt < $max_failed_attempt ) {
            
            $mfa_column_data = array(
                'max_failed_attempt' => $incremented_failed_attempt
            );
            
            $update_result = $this->db->update($_table_name, $mfa_column_data, array(
                'user_id' => $udata['verify_user_id'],
                'is_verified' => 0,
                'is_blocked' => 0
            ));
            
            if ($incremented_r < $max_number_of_attempt) {
            
                $mcga_column_data = array(
                    'max_code_generate_attempt' => $incremented_r
                );
            
                $update_result = $this->db->update($_table_name, $mcga_column_data, array(
                    'user_id' => $udata['verify_user_id'],
                    'is_verified' => 0
                ));
            
                $this->session->set_flashdata('sms_sent_error', dashboard_lang('_YOU_ENTERED_WRONG_CODE_PLEASE_TRY_AGAIN.'));
            
                return $update_result;
            } else {
            
                $regen_rand_code = $this->generate_code();
            
                $result_of_smsapi = $this->sms_api_model->send_code($udata['verify_user_id'], $regen_rand_code);
              
                if ($result_of_smsapi) {
            
                    $data_updated = $this->resetting_max_attempt($udata, $regen_rand_code);
            
                    if ($data_updated) {
            
                        $this->session->set_flashdata("sms_sent_success", dashboard_lang('_NEW_CODE_SENT_PLEASE_TRY_AGAIN'));
                    } else {
                        $this->session->set_flashdata("sms_sent_error", dashboard_lang('_CODE_SENT_BUT_USER_DATA_NOT_UPDATED'));
                    }
                } else {
            
                    $this->session->set_flashdata("sms_sent_error", dashboard_lang('_CODE_COULD_NOT_SENT_SOMETHING_WENT_WRONG'));
                }
            }
            
            
        }
        
    }

    public function unset_email_logged_sess_data()
    {
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('account_id');
        $this->session->unset_userdata('verify_user_id');
        $this->session->unset_userdata('max_attempt_time');
        $this->session->unset_userdata("verify_phone_number");
        $this->session->unset_userdata("verify_sent_code");
    }

    public function settingup_cookie($udata)
    {
        $_table_name = $this->_table_name;
        $this->load->helper('cookie');
        $this->load->config('phone_verification');
        $cookie_remember_time = $this->config->item('cookie_remember_time') * 24 * 3600 + time();
        
        $db_attr_max_rem_me_time = array(
            'max_remember_me_time' => $cookie_remember_time
        )
        ;
        $this->db->update($_table_name, $db_attr_max_rem_me_time, array(
            'user_id' => $udata['verify_user_id'],
            'is_verified' => 1
        ));
    }

    function block_exp_time($user_id)
    {
        $_table_name = $this->_table_name;
        
        $this->db->select('block_expiration_time');
        $this->db->from($_table_name);
        $this->db->where('is_verified', 0);
        $this->db->where('is_blocked', 1);
        $this->db->where('user_id', $user_id);
        
        $result = $this->db->get()->result_array();
        if (sizeof($result) > 0) {
            
            if ($result[0]['block_expiration_time'] < time()) {
                
                return false;
            } else {
                return true;
            }
        } else {
            
            return false;
        }
    }

    function resetting_max_attempt($udata, $code)
    {
        $_table_name = $this->_table_name;
        
        $resetting_data = array(
            'max_code_generate_attempt' => '0',
            'code' => $code
        );
        
        $resetting_result = $this->db->update($_table_name, $resetting_data, array(
            'user_id' => $udata['verify_user_id'],
            'is_verified' => 0
        ));
        
        if ($resetting_result) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getInputIcon() {
        
        $code = $this->config->item("#2FA_SEND_CODE");
        $icon = "fa fa-envelope";
        if ( $code == 'sms' ) {
            
            $icon = "fa fa-mobile";
        }
        
        return $icon;
        
    }

    public function verify_phone_number ($getUrl=false)
     {
        $this->session->unset_userdata("verify_phone_number");
        $this->session->unset_userdata("verify_sent_code");
        $send_code_medium = $this->config->item('#2FA_SEND_CODE');
        if ( $send_code_medium !== 'sms' ) return true;
        else if ( $send_code_medium == 'sms' ) {
            $userId = $this->session->userdata('verify_user_id');
            $accountId = $this->session->userdata('account_id');
            $result = $this->db->order_by("id", "DESC")->get_where("user_verification_mobile", array("user_id" => $userId, "is_deleted" => 0, "account_id" => $accountId))->row_array();
            if ( empty( $result ) || $result["is_code_verified"] == "0" ) {
                if ( $result["is_code_verified"] == "0" ) $this->db->delete("user_verification_mobile", array("id" => $result["id"]));
                $this->session->set_userdata("verify_phone_number", "1");
                $redirect_path = 'dashboard/verify_phone_number';
                if ( $getUrl ) return site_url($redirect_path);
                return redirect(site_url($redirect_path));
            }
            return true;
        }
     }
}
