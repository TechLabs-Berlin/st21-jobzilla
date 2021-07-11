<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Table_permission_model extends CI_Model
{

    public $_table_prefix;

    function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
    }

    /*
     * Get Contacts all data
     */
    public function get_table_permission_data($id)
    {
        $data = array();
        $account_id = $this->session->userdata('account_id');
        $table_name = $this->_table_prefix . "table_permissions";
        $query = $this->db->get_where($table_name, array(
            'id' => $id,
            'is_deleted' => 0,
            'account_id' => $account_id
        ));
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data = $row;
            }
        }
        
        return $data;
    }
    
    /*
     * Getting all fields for a specific permissions
     */
    public function listsPermissionFields($table_name, $permission )
    {
        $this->db->select("field_name");
        $query = $this->db->get_where('table_permissions', array(
            'table' => $table_name,
            'role' => $this->session->userdata('user_role'),
            'permission' => $permission,
            'account_id' => $this->session->userdata('account_id'),
            'is_deleted' => 0
        ));
    
        $fields = array();
    
    
        foreach ( $query->result_array() as $lists ) {
            $fields[] = $lists['field_name'];
        }
        
        return $fields;
    }
    
    
    public function getEditPermission( $tableName, $userRole = false) {
    
        if ( $userRole === false ) {
            $user_role = $this->session->userdata('user_role');
        }else {
            $user_role = $userRole;
        }
        
        $this->db->select("table,permission");
        $result = $this->db->get_where("table_permissions", array(
            "role" => $user_role,
            "is_deleted" => 0
        ))->result_array();
         
        $permission = 0;
         
        if ( sizeof($result) > '0' ) {
             
            $has_all_permission = false;
            $has_table_permission = false;
            $has_edit_permission = false;
            
            foreach ( $result as $table_permissions ) {
                if ( $table_permissions['table'] == $tableName ) {
                    $has_table_permission = true;
                    
                    if ( $table_permissions['permission'] == '2' ) {
                        $has_edit_permission = true;
                    }
                }
    
                if ( $table_permissions['table'] == '*' ) {
                    $all_permission = $table_permissions['permission'];
                    $has_all_permission = true;
                }
            }
             
            if ( $has_table_permission ) {
                 
                if ( $has_edit_permission ) {
                    return 2;
                }else {
                    return 1;
                }
                
            }else if ($has_all_permission) {
                return $all_permission;
            }else {
                return $permission;
            }
             
        }else {
             
            return 0;
        }
    
    
    }
}