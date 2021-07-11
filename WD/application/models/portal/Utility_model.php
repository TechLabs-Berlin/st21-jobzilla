<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Utility_model extends CI_Model
{

    public $_table_prefix;

    protected $account_id;

    function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
        $this->account_id = $this->session->userdata('account_id');
    }

    public function get_admin_data()
    {
        $table_name = $this->_table_prefix . "dashboard_login";
        $this->db->select('email, first_name');
        $this->db->where('role', 'super_admin');
        $this->db->where('notify_new_user', 1);
        $data = $this->db->get($table_name)->result();
        return $data;
    }

    function get_mail_sender_name()
    {
        $table_name = $this->_table_prefix . "settings";
        
        $this->db->select('value');
        $this->db->from($table_name);
        $this->db->where('setting', '#SENDER_NAME');
        
        return $this->db->get()->result_array()[0]['value'];
    }

    function get_mail_sender_address()
    {
        $table_name = $this->_table_prefix . "settings";
        
        $this->db->select('value');
        $this->db->from($table_name);
        $this->db->where('setting', '#SENDER_ADDRESS');
        
        return $this->db->get()->result_array()[0]['value'];
    }

    function get_application_logo()
    {
        $table_name = $this->_table_prefix . "settings";
        $user = BUserHelper::get_instance();
        
        if (! empty($user->tenant->email_logo)) {
            return 'uploads/tenant_email_logo/' . $user->tenant->email_logo;
        } else 
            if (! empty($user->tenant->logo)) {
                return 'uploads/tenant_logo/' . $user->tenant->logo;
            } else {
                $this->db->select('value');
                $this->db->from($table_name);
                $this->db->where('setting', '#APPLICATION_LOGO');
                return $this->db->get()->result_array()[0]['value'];
            }
    }

    public function get_tenant_info($tenant_id = 0, $email = "")
    {
        $accounts_table_name = $this->_table_prefix . "accounts";
        $user_table_name = $this->_table_prefix . "dashboard_login";
        
        if (! empty($email)) {
            $this->db->select('account_id');
            $this->db->where('email', $email);
            $this->db->where('is_deleted', 0);
            $row_data = $this->db->get($user_table_name)->row();
            if (isset($row_data->account_id)) {
                $tenant_id = $row_data->account_id;
            }
        }
        
        $this->db->select('name,logo,favicon,email_logo');
        $this->db->where('id', $tenant_id);
        return $this->db->get($accounts_table_name)->row_array();
    }

    function get_BCC()
    {
        $table_name = $this->_table_prefix . "settings";
        
        $this->db->select('value');
        $this->db->from($table_name);
        $this->db->where('setting', '#EMAIL_BCC');
        
        $result = $this->db->get()->result_array();
        
        if (count($result)) {
            return $result[0]['value'];
        } else {
            return '';
        }
    }

    function updateVariable($key, $value)
    {
        $tableName = $this->_table_prefix . "settings";
        $this->db->select('id');
        $this->db->where('setting', $key);
        $query = $this->db->get($tableName);
        if ($query->num_rows() > 0) {
            $data = $query->row();
            $id = $data->id;
            $this->db->where('id', $id);
            $this->db->update($tableName, array(
                'value' => $value
            ));
        }
    }
}