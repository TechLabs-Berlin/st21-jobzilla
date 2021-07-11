<?php
/*
 * @author Atiqur Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Generate_Tree_Model extends CI_Model
{

    /*
     * Function :: Create - for create new category
     */
    public $_account_id;
    public $_current_table_name;

    public function __construct()
    {
        parent::__construct();
        $this->_account_id = get_account_id();
    }
    
    public function init( $table_name ) {
        $this->_current_table_name = $table_name;        
    }

    function generate_tree_structure($category_id)
    {
        $status = TRUE;
        while ($status) {
            $parent_category_id = $this->get_parent_id($category_id);
            $data1 = array();
            if ($parent_category_id == 1) {
                
                $sql_query = 'SELECT * FROM '.$this->_current_table_name.' WHERE parent="' . $parent_category_id . '" AND is_deleted=0 AND account_id =' . $this->_account_id . ' ORDER BY sort ASC';
                $query = $this->db->query($sql_query);
                
                foreach ($query->result() as $row) {
                    $temp_data['row'] = $row;
                    $temp_data['count'] = $this->count_child($row->id);
                    if ($row->id == $category_id) {
                        
                        $temp_data['is_generted_child'] = 1;
                    } else {
                        
                        $temp_data['is_generted_child'] = 0;
                    }
                    
                    $data1[] = $temp_data;
                }
                
                $data2['child_list'] = $data1;
                $response[] = $data2;
                $status = FALSE;
            } else {
                $data['child_list'] = $this->generate_child_element_list($category_id, $parent_category_id);
                $response[] = $data;
                $category_id = $parent_category_id;
            }
        }
        
        return $response;
    }

    function generate_child_element_list($category_id, $parent_id)
    {
        $return_data = array();
        $query = $this->db->query("SELECT * FROM ".$this->_current_table_name."  WHERE parent='$parent_id' AND is_deleted=0 AND account_id =" . $this->_account_id);
        $result = $query->result_array();
        
        for ($count = 0; $count < sizeof($result); $count ++) {
            
            $child_id = $result[$count]['id'];
            $tree = array();
            if ($category_id == $child_id) {
                
                $tree['is_generted_child'] = 1;
                $tree['id'] = $result[$count]['id'];
                $tree['name'] = $result[$count]['name'];
                $tree['parent'] = $result[$count]['parent'];
                $tree['parent_name'] = $this->get_parent_name($parent_id);
                $tree['parent_id'] = $this->get_parent_id($parent_id);
                $tree['has_child'] = $this->has_child($category_id);
            } else {
                
                $tree['is_generted_child'] = 0;
                $tree['id'] = $result[$count]['id'];
                $tree['name'] = $result[$count]['name'];
                $tree['parent'] = $result[$count]['parent'];
                $tree['parent_name'] = $this->get_parent_name($parent_id);
                $tree['parent_id'] = $this->get_parent_id($parent_id);
                $tree['has_child'] = $this->has_child($category_id);
            }
            
            $return_data[] = $tree;
        }
        
        return $return_data;
    }

    function has_child($category_id)
    {
        $query = $this->db->query("SELECT * FROM ".$this->_current_table_name."  WHERE parent='$category_id' AND is_deleted=0 AND account_id =" . $this->_account_id);
        $result = $query->result_array();
        
        if (sizeof($result) > 0) {
            
            return 1;
        } else {
            
            return 0;
        }
    }

    function get_parent_id($child_category_id)
    {
       $parent = "";
       $query = $this->db->query("SELECT parent FROM ".$this->_current_table_name."  WHERE id='$child_category_id' AND is_deleted=0");
       $result = $query->result_array();
       if(isset($result[0]['parent'])){
           $parent = $result[0]['parent'];
       }
       return $parent;
    }

    function get_parent_name($parent_id)
    {
       $name = "";
       $query = $this->db->query("SELECT name FROM ".$this->_current_table_name."  WHERE id='$parent_id' AND is_deleted=0");
       $result = $query->result_array();
       if(isset($result[0]['name'])){
           $name = $result[0]['name'];
       }
       return $name;
    }

    public function count_child($id)
    {
        $query = "SELECT COUNT(name) as total FROM ".$this->_current_table_name." WHERE parent = $id AND is_deleted != 1 AND account_id =" . $this->_account_id . "";
        $query = $this->db->query($query);
        
        foreach ($query->result() as $row) {
            $total = $row->total;
        }
        
        return $total;
    }

    function get_category_details($category_id)
    {
        $query = $this->db->query("SELECT * FROM ".$this->_current_table_name."  WHERE id='$category_id' AND is_deleted=0 AND account_id =" . $this->_account_id . "");
        $data = $query->result_array();
        $parent_id = @$data[0]['parent'];
        if(empty($parent_id)){
            $data = array();            
        } else{
            $data[0]['parent_name'] = $this->get_parent_name($parent_id);
        }
        return $data;
    }

    function search_all_category($category_name)
    {
        $category_name = $this->db->escape_like_str($category_name);
        $query = $this->db->query("SELECT * FROM ".$this->_current_table_name." WHERE name LIKE '%$category_name%' AND is_deleted=0 AND account_id =" . $this->_account_id . " LIMIT 0 ,6");
        return $query->result();
    }
}