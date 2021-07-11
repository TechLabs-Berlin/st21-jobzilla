<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard_login_model extends CI_Model
{

    public $_table_prefix;

    public $_account_id;

    public $_table_name = 'dashboard_login';

    public function __construct()
    {
        parent::__construct();
        $this->_account_id = get_account_id();
        $this->_table_prefix = $this->config->item('prefix');
        $this->_email_verified_table = $this->_table_prefix."verified_email_list";
        $this->load->model('portal/Utility_model');
        $this->load->model("portal/Email_Template_Model");
        $this->template = $this->config->item('template_name');
    }

    public function update_password($id)
    {
        $pass = random_string('alnum', 6);
        $pass_md5 = md5($pass);
        $table_name = $this->_table_prefix . 'dashboard_login';
        $status = $this->db->update($table_name, array(
            'password' => $pass_md5
        ), array(
            'id' => $id
        ));
        
        if ($status) {
            return $pass;
        }
    }

    public function updatePassword($id_to_update, $password, $re_password)
    {
        if ($password != $re_password) {
            $return = 0;
        } else {
            $return = $this->db->update($this->_table_name, array(
                'password' => md5($password)
            ), array(
                'id' => $id_to_update
            ));
        }
        
        return $return;
    }

    public function send_invitation_email()
    {
        $this->load->model('portal/Utility_model');
        
        $sendPassword = $data['send_password'] = $this->input->post('send_password');
        $userId = $this->input->post('user_id');
        $raw_password = $data['password'] = $this->input->post('raw_password');
        $language = $this->input->post('language');
        
        $data['link'] = $this->generateLink($userId, $sendPassword, $raw_password, $language);
        $data['user_details'] = $this->db->get_where("dashboard_login", array(
            "id" => $userId
        ))->result_array()[0];
        
        $userName = $data['user_details']['first_name'] . " " . $data['user_details']['last_name'];
        
        if (strlen($language) > '0') {
            Dashboard_main_helper::init_translation($language);
        }
        
        if ( $data['send_password'] ) {
            
            $templateDetails = $this->Email_Template_Model->getMesssageTemplate( "_INVITATION_MAIL_WITH_PASSWORD" );
            $data['link'] = $this-> generateWithPassEmailLink ( $data['link'] );
        }else {
            $templateDetails = $this->Email_Template_Model->getMesssageTemplate( "_INVITATION_MAIL_WITHOUT_PASSWORD" );
            $data['link'] = $this-> generateWithoutPassEmailLink ( $data['link'] );
        }
        
        $findItems = [
            "{name}",
            "{email}",
            "{password}",
            "{email-link}",
            "{login-link}",
            "{invite-line-1}",
            "{invite-line-2}",
            "{invite-line-3}"
        ];
        
        $replaceItems = [
            $userName,
            $data['user_details']['email'],
            $data['password'],
            $data['link'],
            $data['link'],
            dashboard_lang('_EMAIL_USER_INVITATION_HEREBY_WE_INVITE_YOU_TO_OUR_CUSTOMER_PORTAL'),
            dashboard_lang('_EMAIL_USER_EMAIL_USER_INVITATION_HERE_IS_YOUR_ACCOUNT_LOGIN_INFOS_INVITE_LINE2'),
            dashboard_lang('_EMAIL_USER_INVITATION_IF_ANY_QUESTIONS_OR_COMMENTS_PLEASE_DO_NOT_HESITATE_TO_CONTACT_US'),
        ];
        
        $emailBody = str_replace($findItems, $replaceItems, $templateDetails["body"]);

        $emailSubject = $templateDetails["subject"];
        $cc = $this->config->item("#EMAIL_CC");
        
        $this->update_invitation_time($userId);
        $mail_sent = send_email($data['user_details']['email'], $userName, $cc, $emailSubject, $emailBody);
        
        $loggedInUserLang = $this->session->userdata("user_language");
        Dashboard_main_helper::init_translation($loggedInUserLang);
        
        if ($mail_sent) {
            
            echo json_encode(array(
                "message" => dashboard_lang("_INVIATION_SUCCESSFULLY_SENT"),
                "invitation_text" => render_user_invitation_info($userId)
            ));
        } else {
            echo json_encode(array(
                "message" => dashboard_lang("_SOMETHING_WENT_WRONG")
            ));
        }
        exit();
    }
    
    public function generateWithPassEmailLink ( $link ) {
    
        $html = '<a  target="_blank" href="'.$link.'">'.$link.'</a>';
    
        return $html;
    }
    
    public function generateWithoutPassEmailLink ( $link ) {
        
        $html = '<a style="font-family:Arial;background:#36a3f7;text-decoration:none !important;text-align:center;border: medium none;color:#ffffff !important;font-size: 20px;font-weight: 700;width: 100%;display:block;padding: 12px 0;" target="_blank" 
                 href="'.$link.'">
                    '.dashboard_lang("_EMAIL_USERS_LINK_TO_CREATE_PASSWORD").'
                 </a>';
        
        return $html;
    }

    public function update_invitation_time($user_id)
    {
        $data = array();
        $data['notified_by'] = $this->session->userdata("user_id");
        $data['invitation_time'] = time();
        
        $this->db->where("id", $user_id);
        $this->db->update("dashboard_login", $data);
    }

    public function generateLink($userId, $sendPassword, $new_password, $language)
    {
        if ($sendPassword) {
            
            $this->update_new_password($new_password);
            return base_url() . "?site_lang=" . $language;
        } else {
            $key = time() . rand(0000, 9999);
            
            $secretKey = hash('sha256', $key);
            
            $userEncryptedId = md5($userId);
            
            $this->db->where("id", $userId);
            $this->db->update("dashboard_login", array(
                "token" => $secretKey,
                "token_valid_time" => time() + (3600 * 24)
            ));
            
            return base_url() . 'invitation/create_password/' . $secretKey . '/' . $userEncryptedId . "?site_lang=" . $language;
        }
    }

    public function update_new_password($password)
    {
        $user_id = $this->input->post("user_id");
        $new_password = $data['password'] = md5(trim($password));
        $data['last_password_changed_at'] = time();
        
        $this->db->where("id", $user_id);
        $this->db->update("dashboard_login", array(
            "token" => "",
            "token_valid_time" => 0
        ));

        $user_details = $this->db->get_where("dashboard_login", array(
             "id" => $user_id,
         ))->row_array();
 
	    $this->session->set_userdata("user_creation_email", $user_details['email']);
        
        $this->db->where("id", $user_id);
        return $this->db->update("dashboard_login", $data);
    }

    public function render_user_invitation_info($user_id)
    {
        $user_details = $this->db->get_where("dashboard_login", array(
            "id" => $user_id
        ))->result_array()[0];
        
        $sending_user_details = $this->db->get_where("dashboard_login", array(
            "id" => $user_details['notified_by'],
            "is_deleted" => 0
        ))->result_array();
        
        if (sizeof($sending_user_details) > '0' && $user_details['invitation_time'] > '0') {
            
            return dashboard_lang("_INVITATION_EMAIL_SENT") . " " . dashboard_lang("_TO") ." ". $user_details['email'] ." ". date("d M Y H:i", $user_details['invitation_time']) . " " . dashboard_lang("_BY") . " " . $sending_user_details[0]['first_name'] . " " . $sending_user_details[0]['last_name'];
        } else {
            return '';
        }
        
        // Invitation email sent 23 juli 2016
        // by [firstname] [lastname]");
    }

    public function verify_key($secretKey, $userEncryptedId)
    {
        $current_time = time();
        
        $this->db->select("id");
        $this->db->from("dashboard_login");
        $this->db->where("token", $secretKey);
        $this->db->where("token_valid_time > $current_time");
        
        $result = $this->db->get()->result_array();
        
        if (sizeof($result) > '0') {
            
            $hashedId = md5($result[0]['id']);
            if ($hashedId == $userEncryptedId) {
                
                return $result[0]['id'];
            } else {
                
                return false;
            }
        } else {
            
            return false;
        }
    }
    
    public function checkEmailVerified( $userId, $email, $resend=0 ) {
        
        $result = $this->db->get_where( $this->_email_verified_table, array(
            "email" => $email 
        ))->result_array();
        
        $isSent = false;
        
        if ( sizeof($result) > '0' ) {
             
             $isVerified = $result[0]['is_verified'];
             if ( $isVerified == '0' ) {
                 
                  $isSent = $this->sendEmailVerification( $result[0]['id'], $userId, $email, $resend );
                  
                  $this->db->where("id", $userId);
                  $this->db->update( $this->_table_prefix . 'dashboard_login', array(
                      "status" => 0
                  ));
             }else {
                 $this->db->where("id", $userId);
                 $this->db->update( $this->_table_prefix . 'dashboard_login', array(
                     "status" => 1
                 ));
             }
             
        }else {
            $data['email'] = $email;
            $data['is_verified'] = 0;
            $data['verification_email_sent'] = 0;
            $data['account_id'] = $this->session->userdata('account_id');
            $data['is_deleted'] = 0;
            
            $this->db->insert($this->_email_verified_table, $data);
            
            $verifiedId = $this->db->insert_id();
            $isSent = $this->sendEmailVerification( $verifiedId, $userId, $email, $resend );
            
            $this->db->where("id", $userId);
            $this->db->update( $this->_table_prefix . 'dashboard_login', array(
                "status" => 0
            ));
        }
        
        if ( $resend === 1 ) {
            
            if( $isSent )
                return array( 'status' => 1, 'msg' => dashboard_lang("_VERIFICATION_LINK_SUCCESSFULLY_SENT_TO"). ' ' . $email );
            else
               return array( 'status' => 0, 'msg' => dashboard_lang("_SOMETHING_WENT_WRONG_VERIFICATION_LINK_WAS_NOT_SENT_TO"). ' ' . $email . ' ' . dashboard_lang('_PLEASE_TRY_AGAIN') );
        }
    }
    
    public function sendEmailVerification( $id, $userId, $email, $resend=0 ) {

        $subject = dashboard_lang("_VERIFICATION_EMAIL");
        $data['app_name'] = $this->Utility_model->get_mail_sender_name();
        $data['logo'] = $this->Utility_model->get_application_logo();
        $data['user_details'] = $user_details = $this->userDetails( $userId );
        $cc = $this->config->item("#EMAIL_CC");
        
        $token = $this->generateToken();
        $data['link'] = base_url()."dashboard/email_verification?token=".$token;

        $language = $this->input->post('language');
        if (strlen($language) > '0') {
            Dashboard_main_helper::init_translation($language);
        }
        $body = $this->load->view($this->template."/$this->_table_name/verification_email_content", $data, TRUE);
        $to_name = $user_details['first_name']." ".$user_details['last_name'];
       
        $loggedInUserLang = $this->session->userdata("user_language");
        Dashboard_main_helper::init_translation($loggedInUserLang);

        $emailSent = send_email($email, $to_name, $cc, $subject, $body);
        
        if ( $emailSent ){
            
             $this->db->where("id", $id);
             $this->db->update($this->_email_verified_table, array("verification_email_sent" => 1, "token" => $token ));
        }
        
        if ( $resend === 1 ) {
            return $emailSent;
        }
    }
    
    public function check_token_verification( $token ) {
        
        $this->db->select("id,email");
        $result = $this->db->get_where($this->_email_verified_table, array(
            "token" => $token,
            "is_verified" => 0
        ))->result_array();

        if ( sizeof($result) > 0 ) {
            
             $this->db->where("id", $result[0]['id']);
             $isVerified = $this->db->update( $this->_email_verified_table,  array(
                 "token" => '',
                 'is_verified' => 1
             ));
             
             $this->db->where("email", $result[0]['email']);
             $this->db->update( $this->_table_prefix . 'dashboard_login', array(
                 "status" => 1
             ));
             
             if ( $isVerified ) {
                 
                 $this->logoutFromUserVerify(1);
             }
             
        } else {
            
            $this->logoutFromUserVerify(0);
        }
    }
    
    public function logoutFromUserVerify( $isSuccess )
    {
        $this->Events_model->executeEvent("logout", "user_logout");
    
        $this->load->library('Authentication');
        $this->authentication->logoutFromPortal(0);
        $user_id_config = $this->config->item('user_id');
        $user_id = $this->session->userdata($user_id_config);

        if (!empty($user_id)) {
            $this->db->where("user_id", $user_id);
            $this->db->delete($this->_table_prefix . 'login_session');

            $this->db->where('user_id', $user_id)->delete('lock_tables');
        }
                
        redirect( site_url( $this->config->item('login_url')."/user_verify/".$isSuccess ) );
    }
    
    public function generateToken() {
        
        $rand = time().rand(10000,100000);
        
        return sha1($rand);
    }
    
    public function userDetails(  $userId ) {
        
        $result = $this->db->get_where( $this->_table_prefix . 'dashboard_login', array(
            "id" => $userId
        ))->result_array();
        
        return $result[0];
    }

    public function user_email_exist($email,$id=0)
    {
        if($id>0) $this->db->where('id <>',  $id);

        $this->db->where('email', trim($email) );
        $this->db->where('is_deleted', '0' );
        $this->db->where('account_id', $this->session->userdata('account_id') );
        $data = $this->db->get( $this->tablePrefix . $this->current_class_name ,1)->row();

        if( $data ) return true;

        return false;
    }



    public function isUserEmailVerifiedByID( $userId = 0 ) {
        
        $this->db->where('id', $userId);
        $email = $this->db->get($this->tablePrefix.$this->current_class_name, 1)->row()->email;

        $result = $this->db->get_where( $this->_email_verified_table, array(
            "email" => $email 
        ))->result_array();
        
        if ( sizeof($result) > 0 ) {
             
             $isVerified = $result[0]['is_verified'];
             return (boolean) $isVerified;
             
        }
        return false;
    }
    
    public function getUserDetails ( $userId = 0 ) {
        
        $result = $this->db->get_where( $this->_table_name, [
            "id" => intval( $userId ),
            "is_deleted" => 0,
            "account_id" =>  $this->_account_id
        ])->result_array();
        
        if ( sizeof($result) > 0 ) {
            return $result[0];
        }else {
            return "";
        }
    }

    public function checkUserHasAuthyId($userId){
        $authyId = "";
        $mfaStatus = -1; 
        $result = $this->db->get_where( $this->_table_name, [
            "id" => intval( $userId ),
            "is_deleted" => 0
        ])->row_array();

        if(is_array($result)){
             
            if(empty($result['authy_id'])){
                if(!empty($result['mobile_postfix'])){       
                    //now need to check if this mobile number is unique 
                    $usersWithSameMobile = $this->db->get_where( $this->_table_name, [
                        "mobile_postfix" => $result['mobile_postfix'],
                        "is_deleted" => 0
                    ])->result_array();
                    // var_dump($usersWithSameMobile); die();
                    
                    if( count($usersWithSameMobile) > 1 ){
                        //that means same mobile with multiple users 
                        return array("authyId" => $authyId, "mfaStatus" => $mfaStatus , 'message' => dashboard_lang('_AUTHY_ERROR_NO_UNIQUE_MOBILE_NUMBER_FOUND') ) ;
                    }
                     
                }else{
                    //if no mobile then no authy 
                    return array("authyId" => $authyId, "mfaStatus" => $mfaStatus , 'message' => dashboard_lang('_AUTHY_ERROR_NO_MOBILE_NUMBER_FOUND') ) ;
                }
                // var_dump($result); die();
                $authyUser = $this->authy->createNewAuthyUser($result['email'], $result['mobile_postfix'], $result['mobile_prefix'] );
                // var_dump($authyUser); die();
                if(!empty($authyUser['user_id'])){
                    $this->db->set('authy_id', $authyUser['user_id']);
                    // $this->db->set('mfa_status', 2);
                    $this->db->where("id", $userId);
                    $this->db->update($this->_table_name);
                    $authyId = $authyUser['user_id'];
                }
            } else{
                $authyId = $result['authy_id'];

                //now need to check if this mobile number is unique 
                $usersWithSameMobile = $this->db->get_where( $this->_table_name, [
                    "mobile_postfix" => $result['mobile_postfix'],
                    "is_deleted" => 0
                ])->result_array();
                
                if( count($usersWithSameMobile) > 1 ){
                    //that means same mobile with multiple users 
                    return array("authyId" => $authyId, "mfaStatus" => $mfaStatus , 'message' => dashboard_lang('_AUTHY_ERROR_NO_UNIQUE_MOBILE_NUMBER_FOUND') ) ;
                }

            }
        }

        return array("authyId" => $authyId, "mfaStatus" => @$result['mfa_status'], "userEmail" => @$result['email']) ;
    }

    public function verifyMfaToken($userId, $token){
        $response = array("status" => 0);  
        $authyId = "";
        $result = $this->db->get_where( $this->_table_name, [
            "id" => intval( $userId ),
            "is_deleted" => 0
        ])->row_array();

        if(is_array($result)){
            if(!empty($result['authy_id'])){
                $authyId = $result['authy_id'];
            }
        }      
        // check the token
        $isValidToken = $this->authy->verify_token($authyId, $token);
        if($isValidToken){
            $this->db->set('user_2fa_options', 1);
            $this->db->set('mfa_status', 2);
            $this->db->where("id", $result['id']);
            $this->db->update($this->_table_name);
            // echo $this->db->last_query();
            $response["status"] = 1;
            $response["message"] = dashboard_lang("_TOKEN_ACCEPTED");
        } else{
            $response["message"] = dashboard_lang("_INVALID_TOKEN");
        }
        return $response;

    }
}