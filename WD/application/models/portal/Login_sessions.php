<?php
/*
 * @author Atiqur Rahman
 */
if (! defined('BASEPATH'))
    
    exit('No direct script access allowed');

class Login_sessions extends CI_Model
{

    public $tablePrefix;

    function __construct()
    {
        parent::__construct();
        $this->tablePrefix = $this->config->item("prefix");
    }

    public function redirecting_to_dboard( $getUrl=false )
    {
        $ip = $this->login->getIp();
        
        $this->login->remove_ip_block_data($ip);
        // Save Activity Log
        $this->authentication->activityLog("login");
        $user_role = get_user_role();
        $opening_view = get_user_opening_view($user_role);
        
        if (! empty($this->session->userdata('request_url'))) {
            $redirect_path = $this->session->userdata('request_url');
        } elseif (! empty($opening_view)) {
            $redirect_path = "dbtables/" . $opening_view . "/listing";
        } else {
            $opening_view = $this->config->item('#VIEW_ON_OPENING');
            if (! empty($opening_view)) {
                $redirect_path = "dbtables/" . $opening_view . "/listing";
            }
        }
        
        $current_user_role = get_user_role();
        $allowed_tables = get_user_viewable_tables($current_user_role);
        
        // Save data into a session array
        $this->session->set_userdata('allowed_tables', $allowed_tables);
        
        if (sizeof($allowed_tables) < 1) {
            $opening_view = $this->config->item('redirect_path_after_login');
            $redirect_path = $opening_view;
        }
        if ( !$getUrl )
            return redirect(site_url($redirect_path));
        return site_url($redirect_path);
    }

    public function set_session_data()
    {
        $udata = $this->session->all_userdata();
        $userId = empty( $udata['verify_user_id'] ) ? $udata['user_id']: $udata['verify_user_id'];
        $data_uid = array(
            'id' => $userId
        );
        
        $userData = $this->dashboard_model->get('dashboard_login', '*', $data_uid)->row_array();
        if ( empty( $userData ) ) {
            $this->session->sess_destroy();
            $this->session->set_userdata("login_error", dashboard_lang("_SOMETHING_WENT_WRONG"));
            return redirect("dashboard");
        }
        $user_id_config = $this->config->item('user_id');
        $user_role = $this->config->item('user_role');
        $user_account_id = $this->config->item('account_id');
        
        $this->session->set_userdata($user_id_config, $userData['id']);
        $this->session->set_userdata($user_role, $userData['role']);
        $this->session->set_userdata($user_account_id, $userData['account_id']);
        $this->session->set_userdata('user_language', $userData['language']);
        $this->session->unset_userdata("verify_user_id", $userId);
    }

    public function unsetting_sessions_all()
    {
        $all_session_key = $this->session->all_userdata();
        foreach ($all_session_key as $key => $value) {
            $this->session->unset_userdata($key);
        }
    }
}