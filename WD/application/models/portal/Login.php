<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Login extends CI_Model
{

    public $_table_prefix;

    public function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
    }

    public function getIp()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        return $ip;
    }

    public function checkIfIpBlocked($ip)
    {
        $status = 0;
        
        $table_full_name = $this->_table_prefix . 'blocked_ip';
        $this->db->select('ip_address');
        $this->db->from($table_full_name);
        $this->db->where('ip_address', $ip);
        $this->db->where('status', 1);
        $this->db->where('is_deleted', 0);
        $query = $this->db->get();
        $block_id_data = $query->row();
        if (isset($block_id_data->ip_address)) {
            $this->session->set_userdata('blocked_ip', 1);
            $status = 1;
        } else {
            
            $status = 0;
        }
        
        return $status;
    }

    public function insert_failed_ip($ip)
    {
        $status = 0;
        
        $login_failed_ip_table = $this->_table_prefix . 'login_failed_ip';
        $this->db->select('id,ip_address,counter');
        $this->db->from($login_failed_ip_table);
        $this->db->where('ip_address', $ip);
        $this->db->where('is_deleted', 0);
        $query = $this->db->get();
        $row = $query->num_rows();
        
        if ($row > 0) {
            $block_id_data = $query->row();
            if (isset($block_id_data->ip_address)) {
                $max_attempt = $this->config->item("#FAIL_LOGIN");
                if ($block_id_data->counter < $max_attempt) {
                    $counter = $block_id_data->counter + 1;
                    $this->db->where('id', $block_id_data->id);
                    $result = $this->db->update($login_failed_ip_table, array(
                        'counter' => $counter
                    ));
                    if ($result) {
                        $status = 1;
                    }
                } else {
                    $counter = $block_id_data->counter + 1;
                    $this->db->where('id', $block_id_data->id);
                    $result = $this->db->update($login_failed_ip_table, array(
                        'counter' => $counter
                    ));
                    if ($result) {
                        $status = 1;
                    }
                    
                    $insert_data = array();
                    $insert_data['ip_address'] = $ip;
                    $insert_data['timestamp'] = time();
                    $insert_data['status'] = 1;
                    $blocked_ip_table = $this->_table_prefix . 'blocked_ip';
                    $result = $this->db->insert($blocked_ip_table, $insert_data);
                    
                    if ($result) {
                        $status = 1;
                        $table_name = $this->_table_prefix . 'blocked_ip';
                        $this->session->set_userdata($blocked_ip_table, 1);
                    }
                }
            }
        } else {
            
            $data = array();
            $data['timestamp'] = time();
            $data['ip_address'] = $ip;
            $data['counter'] = 1;
            
            $result = $this->db->insert($login_failed_ip_table, $data);
            if ($result) {
                $status = 1;
            }
        }
        
        return $status;
    }

    public function remove_ip_block_data($ip)
    {
        $status = 0;
        
        $login_failed_ip_table = $this->_table_prefix . 'login_failed_ip';
        $this->db->where('ip_address', $ip);
        $result = $this->db->update($login_failed_ip_table, array(
            'is_deleted' => 1
        ));
        
        $blocked_ip_table = $this->_table_prefix . 'blocked_ip';
        $this->db->where('ip_address', $ip);
        $result = $this->db->update($blocked_ip_table, array(
            'is_deleted' => 1
        ));
        
        if ($result) {
            $this->session->unset_userdata('blocked_ip');
            $status = 1;
        }
        
        return $status;
    }
}