<?php
/*
 * @author boo2mark
 *
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Authentication
{

    protected $CI;

    public $_table_prefix = "";

    protected $_login_table_name = 'dashboard_login';

    protected $_accounts_table_name = 'accounts';

    /*
     * constructor
     */
    function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
        $this->_table_prefix = $this->CI->config->item('prefix');
        $this->CI->load->model('portal/login');
        $this->CI->load->model('portal/verify_user_model');
        $this->CI->load->model('portal/login_sessions');
        $this->CI->load->config('phone_verification');
        $this->CI->load->model('portal/Events_model');
        $this->CI->load->model('portal/Utility_model');
        $this->CI->load->model('portal/User_roles_model');
    }

    /*
     * check user authentication with data validation
     */
    public function checkAuthentication($postData, $ip, $internalLogin=false)
    {
        if (sizeof($postData) > 0 and ! empty($postData['email'])) {
            
            $loginTrue = 0;
            
            // check client ip is allowed or not
            $ipStatus = $this->checkClientIp($ip, $postData['email']);
            
            if ($ipStatus) {

                // internal login option
                if($internalLogin){
                    $loginTrue = $this->checkFormDataAgainstDb($postData['email'], $postData['password']);
                } else{
                    // check XSS
                    $email = $this->CI->security->xss_clean($postData['email']);
                    $password = $this->CI->security->xss_clean($postData['password']);

                    // check form token
                    $msg = $postData['token'];
                    $decrypted_string = $this->CI->encryption->decrypt($msg);

                    if($decrypted_string === $this->CI->config->item('encryption_text')){

                        if ($email and $password) {
                            if (isset($postData['g-recaptcha-response'])) {
                                $loginTrue = $this->checkFormDataAgainstDb($postData['email'], $postData['password'], $postData['g-recaptcha-response']);
                            } else {
                                $loginTrue = $this->checkFormDataAgainstDb($postData['email'], $postData['password']);
                            }
                        }

                    }else{

                        $this->CI->session->set_flashdata('failed_attempt_alert', dashboard_lang('_LOGIN_FAILED_FOR_INVALID_TOKEN_SUBMIT'));
                        $this->CI->Events_model->setSessionUsersIDs( array( "email" => $postData["email"] ) );
                        $this->CI->Events_model->executeEvent('login_fail', 'user_login', dashboard_lang('_LOGIN_FAILED_FOR_INVALID_TOKEN'));
                        $this->CI->Events_model->unsettingIDs();
                        redirect(site_url('/dashboard'));
                    }
                }                
                
            }
            
            if ($loginTrue) {
                
                $udata = $this->CI->session->all_userdata();
                $is_2fa_enabled = $this->CI->User_roles_model->get_is_2fa_enabled( $udata );
                // var_dump($is_2fa_enabled); die();
                if ($is_2fa_enabled != 0) {
                    
                    $user_blocked = $this->CI->verify_user_model->block_exp_time($udata['user_id']);
                    // var_dump($user_blocked); die();
                    if (! $user_blocked) {
                        
                        $user_id = $this->CI->session->all_userdata()['user_id'];
                        $account_id = $this->CI->session->userdata('account_id');
                        $this->CI->session->set_userdata('verify_user_id', $user_id);
                        // Checking Cookie in Database
                        $this->CI->verify_user_model->checking_db_cookie($udata);
                        
                        // check mfa is enabled or not
                        // var_dump($postData['email']); die();
                        $mfaStatus = self::mfaStatus($postData['email']);
                        // var_dump($mfaStatus); die();
                        if($mfaStatus == 1){
                            $redirect_path = "dashboard/user_phone_verification";                        
                            $this->CI->login_sessions->unsetting_sessions_all();                            
                            $this->CI->session->set_userdata('verify_user_id', $user_id);
                            $this->CI->session->set_userdata('account_id', $account_id);                            
                            $this->CI->verify_user_model->delete_user_verify_row($udata);
                            $this->resetMandatoryPassword("2fa", $udata);
                            $this->CI->verify_user_model->verify_phone_number();
                            redirect(site_url($redirect_path));
                        } else if($mfaStatus == 2){
                            $redirect_path = "dashboard/user_phone_verification";                        
                            $this->CI->login_sessions->unsetting_sessions_all();                            
                            $this->CI->session->set_userdata('verify_user_id', $user_id);
                            $this->CI->session->set_userdata('account_id', $account_id); 
                            $this->CI->session->set_userdata('app_based_verification', 1);                           
                            // $this->CI->verify_user_model->delete_user_verify_row($udata);
                            // $this->resetMandatoryPassword("2fa", $udata);
                            // $this->CI->verify_user_model->verify_phone_number();
                            redirect(site_url($redirect_path));
                        }
                        
                    } else {
                        
                        $this->CI->login_sessions->unsetting_sessions_all();
                        $max_block_time = $this->CI->User_roles_model->get_max_timeout_for_auth( $udata['account_id'] );
                        if( $max_block_time < 1 || empty( $max_block_time ) || is_null( $max_block_time ) ){
                            $max_block_time = intval( $this->config->item('max_block_time') );
                        }
                        $this->CI->session->set_flashdata('failed_attempt_alert', dashboard_lang('_YOU_ARE_BLOCKED_FOR_CERTAIN_PERIOD_PLS_TRY_AGAIN_AFTER') . " " . ( $max_block_time/60 ) . " " . dashboard_lang('_MINUTES_LATER'));
                        $this->CI->Events_model->setSessionUsersIDs( array( "email" => $postData["email"] ) );
                        $this->CI->Events_model->executeEvent('login_fail', 'user_login', dashboard_lang('_LOGIN_FAILED_FOR_USER_BLOCKED'));
                        $this->CI->Events_model->unsettingIDs();
                        redirect(site_url('/dashboard'));
                    }
                } else {
                    
                    $this->CI->login->remove_ip_block_data($ip);
                    $this->resetMandatoryPassword("non-2fa");
                    // Save Activity Log
                    $this->activityLog("login");
                    $user_role = get_user_role();
                    $opening_view = get_user_opening_view($user_role);
                    
                    if (! empty($this->CI->session->userdata('request_url'))) {
                        $redirect_path = $this->CI->session->userdata('request_url');
                    } elseif (! empty($opening_view)) {
                        $redirect_path = "portal/system/defaultview/" . $opening_view . "/listing";
                    } else {
                        $opening_view = $this->CI->config->item('#VIEW_ON_OPENING');
                        if (! empty($opening_view)) {
                            $redirect_path = "portal/system/defaultview/" . $opening_view . "/listing";
                        }
                    }
                    
                    $current_user_role = get_user_role();
                    $allowed_tables = get_user_viewable_tables($current_user_role);
                    // Save data into a session array
                    $this->CI->session->set_userdata('allowed_tables', $allowed_tables);
                    
                    if (sizeof($allowed_tables) < 1) {
                        $opening_view = $this->CI->config->item('redirect_path_after_login');
                        $redirect_path = $opening_view;
                    }

                    $this->CI->Events_model->executeEvent('login_success', 'user_login');
                    
                    redirect(site_url($redirect_path));
                }
            } else {
                
                // insert this ip to login_failed_ip table
                $this->CI->login->insert_failed_ip($ip);
                // if this ip found at blocked_ip table this set a session for captcha validation
                $this->CI->login->checkIfIpBlocked($ip);
                
                $this->CI->Events_model->setSessionUsersIDs( array( "email" => $postData["email"] ) );
                
                $this->CI->Events_model->executeEvent('login_fail', "user_login", dashboard_lang('_LOGIN_FAILED_EMAIL_PASSWORD'));
                $this->CI->Events_model->unsettingIDs();
                
            }
        }
    }
    
   

    /*
     * Check goggle re-capthca
     */
    public function checkGoogleRecaptcha($recaptcharesponse, $session_key)
    {
        // checking google re-capthe data
        $recaptcha_status = 1;
        if (empty($recaptcharesponse)) {
            $this->CI->session->set_userdata($session_key, dashboard_lang('_PLEASE_CLICK_I_AM_NOT_ROBOT'));
            $recaptcha_status = 0;
            
            $this->CI->Events_model->executeEvent("invalid_captcha", "user_login");
        } else {
            $secret_key = $this->CI->config->item("recaptcha_secret_key");
            $remote_address = $this->CI->config->item("base_url");
            
            $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secret_key . "&response=" . $recaptcharesponse . "&remoteip=" . $_SERVER['REMOTE_ADDR']), true);
            
            if ($response['success'] == false) {
                $this->CI->session->set_userdata('sign_up_message', dashboard_lang('_INVALID_CAPTCHA'));
                $recaptcha_status = 0;
                
                $this->CI->Events_model->executeEvent("invalid_captcha", "user_login");
            }
        }
        
        return $recaptcha_status;
    }

    /*
     * Check form data against database
     */
    private function checkFormDataAgainstDb($user_email = '', $user_pass = '', $recaptcharesponse = '')
    {
        
        // check captcha validation
        if ($this->CI->session->userdata("blocked_ip") == 1) {
            
            $resp = $this->checkGoogleRecaptcha($recaptcharesponse, $session_key = "login_error");
            
            if ($resp == 0) {
                // when the CAPTCHA was entered incorrectly
                return false;
            }
        }
        
        $user_id_config = $this->CI->config->item('user_id');
        // Check if already logged in
        if ($this->CI->session->userdata($user_id_config))
            return true;
            
            // Check against user table
        $user_pass = md5($user_pass);
        
        $this->CI->db->where(array(
            'email' => $user_email,
            'password' => $user_pass,
            'status' => 1,
            'is_deleted' => 0
        ));
        
        $dashboard_login_table = $this->_table_prefix . $this->_login_table_name;
        
        $query = $this->CI->db->get_where($dashboard_login_table);
        
        // data for session
        $this->CI->db->select("FIRST_NAME,IMAGE");
        $query1 = $this->CI->db->get_where("$dashboard_login_table", array(
            "email" => "$user_email"
        ));
        $user_info = $query->result_array();
        // data for session close
        
        if ($query->num_rows() > 0) {
            
            $userData = $query->row_array();
            /**
             * check user's tenant is enabled or not, if not then block that user from login
             */
            $accountsTable = $this->_table_prefix . $this->_accounts_table_name;
            try {
                $this->CI->db->select("status");
                $accountsTableQuery = $this->CI->db->get_where($accountsTable, array(
                    "id" => $userData['account_id']
                ));
                $usersTenantInfo = $accountsTableQuery->row_array();
                // if status is disabled then show error message and return false
                // var_dump($usersTenantInfo); die();
                if($usersTenantInfo['status'] == 0){
                    $this->CI->session->set_userdata('login_error', dashboard_lang('_YOUR_TENANT_HAS_DISABLED'));
                    return false;
                }
            } catch (Exception $e) {
                // no operation is required
            }
                         
            
            // check multi_tenancy enable/disable
            /*$statusMultiTenancy = $this->CI->config->item('#ENABLE_MULTI_TENANCY');
            if ($statusMultiTenancy) {
                
                // check user's tenant id
                $subdomain_name = "";
                $subdomain_arr = explode('.', $_SERVER['HTTP_HOST'], 2);
                if (sizeof($subdomain_arr) > 1) {
                    $subdomain_name = $subdomain_arr[0];
                }
                
                $default_tenant = $this->CI->config->item('#DEFAULT_TENANT');
                
                if (! empty($subdomain_name)) {
                    $this->CI->db->select('id');
                    $this->CI->db->where('id', $userData['account_id']);
                    $this->CI->db->where('slug', $subdomain_name);
                    $tenant_check_query = $this->CI->db->get($this->_table_prefix . "accounts");
                    if ($tenant_check_query->num_rows() < 1) {
                        $this->CI->session->set_userdata('login_error', dashboard_lang('_DASHBOARD_LOGIN_TENANT_ERROR_MESSAGE'));
                        return false;
                    }
                } else {
                    $this->CI->db->select('id');
                    $this->CI->db->where('id', $userData['account_id']);
                    $this->CI->db->where('slug', $default_tenant);
                    $tenant_check_query = $this->CI->db->get($this->_table_prefix . "accounts");
                    if ($tenant_check_query->num_rows() < 1) {
                        $this->CI->session->set_userdata('login_error', dashboard_lang('_DASHBOARD_LOGIN_TENANT_ERROR_MESSAGE'));
                        return false;
                    }
                }
            }*/
            
            $user_id_config = $this->CI->config->item('user_id');
            $user_role = $this->CI->config->item('user_role');
            $user_account_id = $this->CI->config->item('account_id');
            
            $this->CI->session->set_userdata($user_id_config, $userData['id']);
            $this->CI->session->set_userdata($user_role, $userData['role']);
            $this->CI->session->set_userdata($user_account_id, $userData['account_id']);
            $this->CI->session->set_userdata('user_language', $userData['language']);
            
            // session data for image upload validation at ckeditor
            
            set_account_id($userData['account_id']);
            if ($user_info[0]['image']) {
                $this->CI->session->set_userdata('image', $user_info[0]['image']);
            } else {
                $this->CI->session->set_userdata('image', "uploads/profile_image/blank-user.png");
            }
            
            $this->CI->session->set_userdata('first_name', $user_info[0]['first_name']);
            
            if (isset($_POST['remember']) && $_POST['remember'] == 1) {
                
                $value = hash('sha256', rand(0, 1000) . '*%Gy4x#GpUw[');
                
                $cookie = array(
                    'name' => 'rememberCookie',
                    'value' => $value,
                    'expire' => $this->CI->config->item("cookie_expire_time"),
                    'path' => '/'
                );
                
                set_cookie($cookie);
                
                $data = array(
                    'user_id' => $userData['id'],
                    'session_key' => $value
                );
                $login_session_table = $this->_table_prefix . "login_session";
                $this->CI->db->insert($login_session_table, $data);
            }
            
            return true;
        } else {
            
            $this->CI->session->set_userdata('login_error', dashboard_lang('_DASHBOARD_LOGIN_ERROR_MESSAGE'));
            return false;
        }
    }

    /*
     * check a user loggedin or not
     */
    public function checkUserLoggedIn()
    {
        $user_id_config = $this->CI->config->item('user_id');
        if ($this->CI->session->userdata($user_id_config)) {
            
            return true;
        } else {
            
            return false;
        }
    }

    public function userSignUp($postData)
    {
        $status = 1;
        // print_r($postData); die();
        if (sizeof($postData) > 0) {
            
            // checking google re-capthe data
            $recaptcha_status = $this->checkGoogleRecaptcha($postData['g-recaptcha-response'], $session_key = "sign_up_message");
            
            $this->CI->load->library('form_validation');
            $this->CI->form_validation->set_error_delimiters('<span class="error help-block">', '</span>');
            
            $this->CI->form_validation->set_rules('first_name', dashboard_lang('_FIRST_NAME'), '');
            // $this->CI->form_validation->set_rules('password', dashboard_lang('_PASSWORD'), '');
            $this->CI->form_validation->set_rules('email', dashboard_lang('_EMAIL'), 'required|valid_email');
            $this->CI->form_validation->set_rules('mobile_number', dashboard_lang('_MOBILE_NUMBER'), 'required');
            
            if ($this->CI->form_validation->run() == FALSE) {
                $status = 0;
            } else 
                if ($recaptcha_status == 0) {
                    
                    $status = 0;
                } else {
                    // check form token
                    $msg = $postData['token'];
                    $decrypted_string = $this->CI->encryption->decrypt($msg);
                    
                    if ($decrypted_string === $this->CI->config->item('encryption_text')) {
                        
                        $account_data = $this->checkAccountStatus($postData['email']);
                        
                        if ($account_data['status'] == 0) {
                            
                            $this->CI->session->set_userdata('sign_up_message', dashboard_lang('_GIVEN_EMAIL_ALREADY_EXIST'));
                             $this->CI->Events_model->executeEvent('signup_failed', 'user_signup');
                            redirect(base_url() . "dashboard/signup");
                        } elseif ($account_data['status'] == 1) {
                            
                            $this->CI->session->set_userdata('sign_up_message', dashboard_lang('_PLEASE_CHECK_YOUR_EMAIL_FOR_ACCOUNT_CONFIRMATION'));
                            $this->CI->session->set_userdata('alert_success', 1);
                            
                            $email_data = array(
                                'user_id' => $account_data['id']
                            );
                            email_sender($email_data, "return_user");
                            
                            redirect(base_url() . "dashboard/signup");
                        } else {
                            
                            // check user's tenant id
                            $account_id = 1;
                            $subdomain_name = "";
                            $subdomain_arr = explode('.', $_SERVER['HTTP_HOST'], 2);
                            if (sizeof($subdomain_arr) > 1) {
                                $subdomain_name = $subdomain_arr[0];
                            }
                            
                            $default_tenant = $this->CI->config->item('#DEFAULT_TENANT');
                            
                            if (! empty($subdomain_name)) {
                                $this->CI->db->select('id');
                                $this->CI->db->where('slug', $subdomain_name);
                                $tenant_check_query = $this->CI->db->get($this->_table_prefix . "accounts");
                                if ($tenant_check_query->num_rows() > 0) {
                                    $data = $tenant_check_query->row();
                                    $account_id = $data->id;
                                }
                            } else {
                                $this->CI->db->select('id');
                                $this->CI->db->where('slug', $default_tenant);
                                $tenant_check_query = $this->CI->db->get($this->_table_prefix . "accounts");
                                if ($tenant_check_query->num_rows() > 0) {
                                    $data = $tenant_check_query->row();
                                    $account_id = $data->id;
                                }
                            }

                            /**
                             * adjust company name or first name and last name
                             */

                            $companyName = empty($postData['company_name'])? $postData['first_name']." ".$postData['last_name']: $postData['company_name'];
                            $firstName = empty($postData['first_name']) ? $postData['company_name'] : $postData['first_name'];
                            $lastName = empty($postData['last_name']) ? dashboard_lang("_USER") : $postData['last_name'];
                            $password = random_password();

                            /**
                             * create new tenant for new signup user
                             */

                            $insert_data = array(
                                'name' => $companyName,
                                'status' => 1
                            ); // default tenant status is active
                            
                            $this->CI->db->insert($this->_table_prefix . $this->_accounts_table_name, $insert_data);
                            $account_id = $this->CI->db->insert_id();

                            /**
                             * Create new user account
                             */
                            $insert_data = array(
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'email' => $postData['email'],
                                'password' => md5($password),
                                'mobile_number' => $postData['mobile_number'],
                                'role' => $this->CI->config->item('default_signup_user_role'), // default user role
                                'account_id' => $account_id,
                                'status' => 1,
                                'last_password_changed_at' => time()
                            );
                            
                            $this->CI->db->insert($this->_table_prefix . $this->_login_table_name, $insert_data);
                            $user_id = $this->CI->db->insert_id();

                            $this->CI->Events_model->executeEvent('signup_success', 'user_signup');
                            /**
                             * Update user at accounts table
                             */
                            $this->CI->db->where("id", $account_id);
                            $this->CI->db->set("account_super_admin", $user_id);
                            $accountSuperAdminUpdateStatus = $this->CI->db->update($this->_table_prefix . $this->_accounts_table_name);
                            
                            // send email to new user and admin
                            $email_data['data_result'] = $insert_data;
                            $email_data['new_password'] = $password;
                            $email_data['user_id'] = $user_id;
                            email_sender($email_data, "sign_up");
                            
                            $this->CI->session->set_userdata('sign_up_message', dashboard_lang('_SIGNUP_PROCESS_COMPLETED_PLEASE_CHECK_YOUR_EMAIL_FOR_ACCOUNT_CONFIRMATION'));
                            $this->CI->session->set_userdata('alert_success', 1);
                            // check we have auto login or not
                            $autoLogin = $this->CI->config->item('#CORE_AUTOMATIC_LOGIN_AFTER_SIGNUP');    
                            if($autoLogin){
                                $dataArray = array("email" => $postData['email'], "password" => $password);
                                $this->checkAuthentication($dataArray, $_SERVER['REMOTE_ADDR'], true);
                            } else{
                                redirect(base_url() . "dashboard/signup");
                            }                  
                            
                        }
                    } else {
                        $status = 0;
                    }
                }
        } else {
            $status = 0;
        }
        
        if (! $status) {
            $this->CI->load->view($this->CI->config->item('template_name') .'/core_' . $this->CI->config->item('template_name') . '/authentication/signup');
        }
    }

    public function checkAccountStatus($email = '')
    {
        $return_data = array();
        if (isset($email) and ! empty($email)) {
            $this->CI->db->select('id,email,is_deleted');
            $this->CI->db->where('email', $email);
            $sql = $this->CI->db->get($this->_table_prefix . $this->_login_table_name);
            $rows = $sql->num_rows();
            
            if ($rows > 0) {
                $data = $sql->row();
                if ($data->is_deleted == 0) {
                    $return_data['status'] = 0;
                } else {
                    $update_data = array(
                        'is_deleted' => 0,
                        'status' => 0
                    );
                    
                    $this->CI->db->where('email', $email);
                    $result = $this->CI->db->update($this->_table_prefix . $this->_login_table_name, $update_data);
                    
                    if ($result) {
                        
                        $return_data['status'] = 1;
                        $return_data['id'] = $data->id;
                    }
                }
            } else {
                $return_data['status'] = 2;
            }
        }
        return $return_data;
    }

    /*
     * Reset user password
     */
    public function resetUserPassword()
    {
        $post = $this->CI->input->post();
        
        if (sizeof($post) > 0) {
            
            $this->CI->session->set_userdata('forget_pass', 1);
            
            $reset_email = $post['reset_email'];
            
            $this->CI->db->select('*');
            $result = $this->CI->db->get_where($this->_table_prefix . $this->_login_table_name, array(
                'email' => $reset_email
            ), 1);
            $data_result = $result->row_array();
            $data['first_name'] = $data_result['first_name'];
            $generalErrorMsgTranslationKey = "_IF_THE_EMAIL_MATCHES_A_LOGIN_WE_HAVE_SENT_A_RESET_LINK_TO_THE_EMAIL_TO_RESET_THE_PASSWORD";
            if ($data_result) {
                
                $token = md5(time());
                $data['to'] = $reset_email;
                
                $data['url'] = base_url() . "dashboard/input_reset_password?token=" . $token;
                $tenantData = $this->CI->Utility_model->get_tenant_info('', $reset_email);
                
                if(!empty($tenantData['email_logo'])){
                    $data['logo'] = 'uploads/tenant_email_logo/'.$tenantData['email_logo'];
                } elseif (!empty($tenantData['logo'])){
                    $data['logo'] = 'uploads/tenant_logo/'.$tenantData['logo'];
                }
                
                if(!empty($tenantData['name'])){
                    $data['app_name'] = $tenantData['name'];
                }
                
                // call a helper function to send email
                email_sender($data, "password_reset");
                
                $where = array(
                    'email' => $reset_email
                );
                $this->CI->db->select('*');
                $result = $this->CI->db->get_where($this->_table_prefix . 'reset_password', $where, 1);
                $row = $result->row_array();
                
                if ($row) {
                    $this->CI->db->where('id', $row['id']);
                    $this->CI->db->update($this->_table_prefix . "reset_password", array(
                        'token' => $token
                    ));
                } else {
                    
                    $ins = array(
                        'user_id' => $data_result['id'],
                        'email' => $reset_email,
                        'token' => $token
                    );
                    $this->CI->db->insert($this->_table_prefix . "reset_password", $ins);
                }
                
                $custom_msg = " reset password mail sent to " . $reset_email;
                
                $this->CI->Events_model->setSessionUsersIDs( array( "email" => $reset_email ) );
                
                $this->CI->Events_model->executeEvent("reset_password_link_sent", "reset_password", $custom_msg);
                
                $this->CI->session->set_userdata('change_password_message', dashboard_lang($generalErrorMsgTranslationKey));
                $this->CI->session->set_userdata('alert_success', 1);
                
                $this->CI->Events_model->unsettingIDs();
                
                redirect(site_url($this->CI->config->item('login_url')));
            } else {
                
                $this->CI->Events_model->executeEvent("reset_password_invalid_email", "reset_password");
                $this->CI->session->set_userdata('change_password_message', dashboard_lang($generalErrorMsgTranslationKey));
                $this->CI->session->set_userdata('alert_success', 1);
                redirect(site_url($this->CI->config->item('login_url')));
            }
        }
    }

    public function checkResetPassword($post, $token)
    {
        if (isset($token) and ! empty($token)) {
            
            $this->CI->db->select('*');
            $result = $this->CI->db->get_where($this->_table_prefix . 'reset_password', array(
                'token' => $token
            ), 1);
            
            $row = $result->row_array();
        }
        
        if ($row) {
            
            $data = array();
            
            if (sizeof($post) > 0) {
                
                $new_pass = md5($post['new_password']);
                
                if (empty($post['new_password'])) {
                    $data['message'] = dashboard_lang('_PLEASE_ENTER_PASSWORD');
                } else 
                    if ($post['new_password'] == $post['re_password']) {
                        
                        $this->CI->db->select('*');
                        $result = $this->CI->db->get_where($this->_table_prefix . $this->_login_table_name, array(
                            'email' => $row['email']
                        ), 1);
                        $data_result = $result->row_array();
                        
                        if ($data_result) {
                            // update pass and password update time
                            $this->CI->db->where('id', $data_result['id']);
                            $update1 = $this->CI->db->update($this->_table_prefix . $this->_login_table_name, array(
                                'password' => $new_pass,
                                "last_password_changed_at" => time()
                            ));
                            
                            // update token
                            $this->CI->db->where('id', $row['id']);
                            $update2 = $this->CI->db->update($this->_table_prefix . "reset_password", array(
                                'token' => ""
                            ));
                            
                            if ($update1 && $update2) {
                                $email_data['data_result'] = $data_result;
                                $email_data['new_password'] = $post['new_password'];
                                
                                // email_sender($email_data, "password_reset");
                                
                                $data['message'] = dashboard_lang('_PASSWORD_CHANGED_SUCCESSFULLY');
                                $data['btn_hide'] = TRUE;
                                $data['alert_success'] = TRUE;
                                $data['form_hide'] = TRUE;
                                
                                $this->CI->Events_model->setSessionUsersIDs( array( "email" => $row['email'] ) );
                                $this->CI->Events_model->executeEvent("reset_password_changed", "reset_password", "reset password changed for email " . $row['email']);
                            
                                $this->CI->Events_model->unsettingIDs( );
                            }
                        } else {
                            
                            $data['message'] = dashboard_lang('_EMAIL_NOT_EXISTS');
                            $data['form_hide'] = TRUE;
                        }
                    } else {
                        
                        $this->CI->Events_model->setSessionUsersIDs( array( "email" => $row['email'] ) );
                        
                        $data['message'] = dashboard_lang('_PASSWORD_IS_NOT_MATCHED');
                        $this->CI->Events_model->executeEvent("reset_password_changed", "reset_password", "password & password again not matched for email " . $row['email']);
                    
                        $this->CI->Events_model->unsettingIDs( );
                    }
            }
        } else {
            
            $data['message'] = dashboard_lang('_TOKEN_IS_INVALID_OR_EXPIRED');
            $data['btn_hide'] = TRUE;
            $data['form_hide'] = TRUE;
            
            $this->CI->Events_model->executeEvent("reset_password_changed", "reset_password", "user tried with invalid token");
        }
        
        $this->CI->load->view($this->CI->config->item('template_name').'/core_' . $this->CI->config->item('template_name') . '/authentication/reset_password', $data);
    }

    public function logoutFromPortal($isRedirect=1)
    {
        $user_id_config = $this->CI->config->item('user_id');
        $user_id = $this->CI->session->userdata($user_id_config);

        if(!empty($user_id)){
            //execute logout event
            $this->CI->Events_model->executeEvent("logout", "user_logout");            
            //track activity log
            $this->activityLog("logout");
            $this->CI->db->where("user_id", $user_id);
            $this->CI->db->delete($this->_table_prefix . 'login_session');
            $this->CI->db->where('user_id', $user_id)->delete('lock_tables');           
        }

        //destroy user session
        $this->CI->session->sess_destroy();
        //delete cookie
        $cookie = get_cookie('rememberCookie');        

        if (isset($cookie)) {

            $cookie = array(
                'name' => 'rememberCookie',
                'value' => '',
                'expire' => '0'
            );

            delete_cookie($cookie);                
        }

        if( $isRedirect === 1 ){
            redirect(site_url($this->CI->config->item('login_url')));
        }
    }

    /*
     * Change user password
     */
    public function changePassword($post)
    {
        $data = array();
        
        $dashboard_login_table = $this->_table_prefix . $this->_login_table_name;
        
        if (isset($post) && count($post)) {
            
            // $currentPassword = $post ['current_password'];
            $newPassword = $post['new_password'];
            $re_type_password = $post['retype_password'];
            
            // $user_current_pass = md5 ( $currentPassword );
            $user_new_pass = md5($newPassword);
            
            $this->CI->db->where(array(
                'id' => get_user_id()
            ));
            
            $query = $this->CI->db->get_where($dashboard_login_table);
            
            if ($query->num_rows() > 0) {
                $email_data['data_result'] = $query->row_array();
                $email_data['new_password'] = $newPassword;
                $data = array(
                    'password' => $user_new_pass
                );
                
                if ($newPassword != $re_type_password) {
                    
                    $data['change_password_error'] = dashboard_lang('_NEW_PASSWORD_AND_RETYPE_PASSWOED_NOT_MATCH');
                } else {
                    
                    if (strlen($re_type_password) < 6) {
                        
                        $data['change_password_error'] = dashboard_lang('_NEW_PASSWORD_SHOULD_HAVE_AT_LAST_6_CHARACTERS');
                    } else {
                        
                        // check password strength
                        $result = $this->checkPasswordStrength($newPassword);
                        if ($result['strong'] == 0) {
                            $data['change_password_message'] = $result['message'];
                        } else {
                            $this->CI->db->where("id", get_user_id());
                            $this->CI->db->update($dashboard_login_table, $data);
                            
                            $data['change_password_message'] = dashboard_lang('_PASSWORD_CHANGED_SUCCESS');
                        }
                    }
                }
            } else {
                $data['change_password_error'] = dashboard_lang('_CURRENT_PASSWORD_NOT_MATCH');
            }
        } else {
            if (! empty($post)) {
                $data['change_password_error'] = dashboard_lang('_PLEASE_ENTER_YOUR_CURRENT_PASSWORD');
            }
        }
        
        if (! get_user_id()) {
            redirect(site_url($this->CI->config->item('login_url')));
        }
        
        $this->CI->db->where(array(
            'id' => get_user_id()
        ));
        
        $query = $this->CI->db->get_where($dashboard_login_table);
        if (array_key_exists('id', $_GET)) {
            if ($_GET['id'] === $query->row_array()[0]) {}
        }
        
        $data['data'] = $query->row_array();
        
        $this->CI->template->write_view('content', $this->CI->config->item('template_name') . '/core_' . $this->CI->config->item('template_name') . '/authentication/setting', $data);
        
        $this->CI->template->render();
    }

    /*
     * Check password strength
     */
    public function checkPasswordStrength($password)
    {
        $result[] = array();
        $strong = 1;
        
        $r1 = '/[A-Z]/';
        $r2 = '/[a-z]/';
        $r3 = '/[0-9]/';
        $r4 = '/~|[!@#$%^&*()\-_=+{};:,<.>]/';
        $message = '';
        if (! preg_match($r1, $password)) {
            $message = dashboard_lang('_PLEASE_ENTER_AL_LEAST_ONE_UPPERCASE_LETTER');
            $strong = 0;
        }
        
        if (! preg_match($r2, $password)) {
            $message .= (! empty($message)) ? "<br>" : '';
            $message .= dashboard_lang('_PLEASE_ENTER_AL_LEAST_ONE_LOWERCASE_LETTER');
            $strong = 0;
        }
        
        if (! preg_match($r3, $password)) {
            $message .= (! empty($message)) ? "<br>" : '';
            $message .= dashboard_lang('_PLEASE_ENTER_AL_LEAST_ONE_NUMERIC_VALUE');
            $strong = 0;
        }
        
        if (! preg_match($r4, $password)) {
            $message .= (! empty($message)) ? "<br>" : '';
            $message .= dashboard_lang('_PLEASE_ENTER_AL_LEAST_ONE_SPECIAL_CHARACTER');
            $strong = 0;
        }
        
        $result['strong'] = $strong;
        $result['message'] = $message;
        
        
        return $result;
    }

    /*
     * Change user email address
     */
    public function changeEmail()
    {
        $result = false;
        
        $verification_key = $this->CI->input->get('verification_key');
        $email_change_log_table = $this->_table_prefix . "email_change_log";
        $dashboard_login_table = $this->_table_prefix . $this->_login_table_name;
        
        if (isset($verification_key)) {
            
            $this->CI->db->select('*');
            $this->CI->db->where('is_deleted', 0);
            $this->CI->db->where('valid_upto >', time());
            $this->CI->db->where('verification_key', $verification_key);
            $query = $this->CI->db->get($email_change_log_table);
            if ($query->num_rows() > 0) {
                $data = $query->row();
                $id = $data->id;
                $user_id = $data->user_id;
                $email = $data->new_email_address;
                // update dashboard login table
                $this->CI->db->where('id', $user_id);
                $result = $this->CI->db->update($dashboard_login_table, array(
                    'email' => $email
                ));
                // delete record from email_change_log table
                if ($result) {
                    $this->CI->db->where('id', $id);
                    $result = $this->CI->db->update($email_change_log_table, array(
                        'is_deleted' => 1
                    ));
                }
                
                if ($result) {
                    $this->logoutFromPortal();
                }
            } else {
                
                $url = base_url() . "dashboard/home";
                $error_msg['redirect_time'] = $redirect_time = $this->CI->config->item('no_permission_listing_view_redirect');
                header("Refresh: $redirect_time; URL=$url");
                $error_msg['not_show_time_msg'] = FALSE;
                $error_msg['heading'] = dashboard_lang('_ACCESS_DENIED');
                $error_msg['message'] = dashboard_lang('_THIS_LINK_IS_EXPIRED');
                
                $this->CI->load->view('errors/html/error_403', $error_msg);
            }
        } else {
            
            $data = array();
            
            $result = $this->addNewEmail();
            if ($result['status']) {
                $data['status'] = 1;
                $data['message'] = dashboard_lang("_CHECK_YOUR_EMAIL_TO_COMPLETE_THIS_OPERATION");
            } else {
                if (isset($result['type']) and $result['type'] == "duplicate_email") {
                    $data['status'] = 0;
                    $data['message'] = dashboard_lang("_GIVEN_EMAIL_USED_BY_OTHER_USER");
                }    
                elseif (isset($result['type']) and $result['type'] == "own_email") {
                    $data['status'] = 0;
                    $data['message'] = dashboard_lang("_OWN_EMAIL_ADDRESS_GIVEN");
                } else {
                    $data['status'] = 0;
                    $data['message'] = dashboard_lang("_EMAIL_CHANGE_FAILED");
                }
            }
            echo json_encode($data);
        }
    }

    /*
     * Change user email
     */
    protected function addNewEmail()
    {
        $expire_after = 86400; // 24 hour
        $result = array();
        
        $data = array();
        $email_data = array();
        $user_helper = BUserHelper::get_instance();
        $email_change_log_table = $this->_table_prefix . "email_change_log";
        $login_table = $this->_table_prefix . $this->_login_table_name;
        
        $new_email = $this->CI->input->post('email');
        
        // check new email exists in application or not
        $this->CI->db->select('id');
        $this->CI->db->where('email', $new_email);
        $this->CI->db->where('is_deleted', 0);
        $query = $this->CI->db->get($login_table);
        $row=$query->row();
        if ($query->num_rows() > 0) {
            $result['status'] = FALSE;
            if($this->CI->session->userdata('user_id')==$row->id)
                $result['type'] = "own_email";
            else
                $result['type'] = "duplicate_email";
            
            return $result;
        }
        
        if (isset($user_helper->user->id) and $user_helper->user->id > 0) {
            
            $data['user_id'] = $user_helper->user->id;
            $data['old_email_address'] = $user_helper->user->email;
            $data['new_email_address'] = $new_email;
            $data['verification_key'] = md5(time());
            $data['valid_upto'] = time() + $expire_after;
            
            $status = $this->CI->db->insert($email_change_log_table, $data);
        }
        
        if ($status) {
            
            $data['first_name'] = $user_helper->user->first_name;
            $data['url'] = base_url() . "dashboard/change_email?verification_key=" . $data['verification_key'];
            
            $result['status'] = email_sender($data, "change_email");
        }
        
        return $result;
    }

    protected function checkClientIp($ip, $userEmail)
    {
        $ipAarray = array();
        $status = FALSE;
        $listOfIp = "";
        $userRole = "";
        $account_id = get_account_id();
        
        $this->CI->db->select('role');
        $this->CI->db->where('email', $userEmail);
        $this->CI->db->where('account_id', $account_id);
        $this->CI->db->where('is_deleted', 0);
        $query = $this->CI->db->get($this->_table_prefix . $this->_login_table_name);
        if ($query->num_rows() > 0) {
            $data = $query->row();
            $userRole = $data->role;
        }
        
        $this->CI->db->select('ip_address_access,ip_logon_superadmin');
        $this->CI->db->where('id', $account_id);
        $query = $this->CI->db->get($this->_table_prefix . $this->_accounts_table_name);
        if ($query->num_rows() > 0) {
            $data = $query->row();
            if ($data->ip_logon_superadmin == 1 and $userRole == "super_admin") {
                return TRUE;
            } else {
                $listOfIp = $data->ip_address_access;
            }
        }
        
        if (empty($listOfIp)) {
            $status = TRUE;
        } else {
            $ipAarray = explode(',', $listOfIp);
            if (in_array($ip, $ipAarray)) {
                $status = TRUE;
            } else {
                $status = FALSE;
                $this->CI->session->set_userdata('login_error', dashboard_lang('_INVALID_LOGON_ADDRESS') . ' [' . $ip . ']');
            }
        }
        
        return $status;
    }

    /*
     * Save User Activity Log Login/Logout
     */
    public function activityLog($act_type)
    {
        $logData = array(
            'user_id' => $this->CI->session->userdata('user_id'),
            'login_time' => strtotime('now'),
            'user_os' => $this->CI->agent->platform(),
            'user_browser' => $this->CI->agent->browser(),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'activity_type' => $act_type
        );
        $saveLog = $this->CI->db->insert('user_activity', $logData);
        return $saveLog;
    }
    /*
     * End of Save User Activity log
     */

    public function resetMandatoryPassword ( $redirectingFrom, $udata=array() )
    {
        $currentTime = time();
        if ( empty( $udata ) )
            $udata = $this->CI->session->all_userdata();
        $user_id = $udata['user_id'];
        $account_id = $udata['account_id'];

        $selectFieldsArr = array(
            "dl.last_password_changed_at",
            "ur.slug",
            "ur.mandatory_password_change", 
            "ur.set_time_in_month"
        );
        $selectFields = implode( ",", $selectFieldsArr );
        $this->CI->db->select($selectFields);
        $this->CI->db->join("user_roles ur", "ur.slug = dl.role AND ur.is_deleted = 0");
        $userRowData = $this->CI->db->get_where("dashboard_login dl", array("dl.id" => $user_id, "dl.is_deleted" => 0, "dl.account_id" => $account_id, "dl.status" => 1))->row_array();

        $durationOfMonths = $this->CI->config->item("#MANDATORY_RESET_PASSWORD_IN_MONTH");
        if ( !empty( $userRowData["set_time_in_month"] ) )
            $durationOfMonths = $userRowData["set_time_in_month"];

        $durationOfMonthsTochange = 0;
        if ( !is_null( $userRowData["last_password_changed_at"] ) )
            $durationOfMonthsTochange = strtotime('+'.$durationOfMonths.' months', $userRowData["last_password_changed_at"]);

        if ( $currentTime > $durationOfMonthsTochange && $userRowData["mandatory_password_change"] === "1" ) {

            $this->CI->login_sessions->unsetting_sessions_all();
            $this->CI->session->set_userdata('verify_user_id', $user_id);
            $this->CI->session->set_userdata('account_id', $account_id);
            $this->CI->session->set_userdata('reset_mandatory_password_change', 1);
            $this->CI->session->set_userdata('redirecting_from', $redirectingFrom);
            $this->CI->Events_model->executeEvent('mandatory_password_change', 'user_login');
            $redirectPath = "dashboard/reset_mandatory_password_change";
            return redirect($redirectPath);
        }
    }

    public function mfaStatus($email){
        $dashboard_login_table = $this->_table_prefix .$this->_login_table_name;
        $this->CI->db->select("mfa_status");
        $query = $this->CI->db->get_where($dashboard_login_table, array(
            "email" => $email
        ));
        $user_info = $query->row_array();
        // var_dump($user_info); die();
        return $user_info['mfa_status'];
    }
}
