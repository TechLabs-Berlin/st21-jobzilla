<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Base_Model extends CI_Model
{

    public $_table_prefix;

    public function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
    }

    public function get($where_array = '')
    {
        $data = array();
        $table_name = $this->_table_prefix . $this->table;
        
        $this->where($where_array);
        $query = $this->db->get($table_name);
        if ($query->num_rows() > 1) {
            foreach ($query->result_array() as $row) {
                $data[] = $row;
            }
        }
        
        return $data;
    }

    public function where($where_array = '')
    {
        $where_array['is_deleted'] = 0;
        $where_array['account_id'] = $this->session->userdata('account_id');
        $this->db->where($where_array);
    }
}