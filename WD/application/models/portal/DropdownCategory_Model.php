<?php
/*
 * @author Atiqur Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class DropdownCategory_Model extends CI_Model
{

    /*
     * Function :: Create - for create new category
     */
    public $_account_id;
   
    public $_categoryTable = 'category_customers';


    public function __construct()
    {
        parent::__construct();
        $this->_account_id = get_account_id(1);
      
    }

   


   public function search_category( $search_string, $tableName = '' )
   {
      
       $query = $this->db->query("SELECT * FROM  ". $tableName ." WHERE id = '1' AND is_deleted='0' LIMIT 5");
       $query_1 = $this->db->query("SELECT * FROM  ". $tableName ." WHERE name LIKE '%$search_string%' AND id > '1' AND is_deleted='0' AND account_id =" . $this->_account_id . " LIMIT 5");
       $result_1 = $query->result_array();
       $result_2 = $query_1->result_array();
   
       $result = $result_2;//array_merge($result_1, $result_2);
       
       for ($count = 0; $count < sizeof($result); $count ++) {
            
            $all_parent = $this->get_root_item($result[$count]['id'], $tableName);
            $root_path = $this->format_parent($all_parent, $result[$count]['id'], $tableName);
            
            $result[$count]['name'] = $root_path;
        }
      
        return array(
            'items' => $result
        );
   }
   
   public function format_parent($all_parent, $id, $tableName)
   {
       $self_cat = $this->get_cat_name_by_id($id, $tableName);
       $str = '';
       for ($i = count($all_parent) - 1; $i >= 0; $i --) {
           $str .= $all_parent[$i] . " > ";
       }
       $str .= $self_cat;
       return $str;
   }

   public function get_root_item($id, $tableName)
   {
       $this->db->select('id,parent,name');
       $query = $this->db->get_where( $tableName , array(
           'id' => $id,
           'is_deleted' => 0
       ));
       $result = $query->result_array();
       $return_item = array();
       
       if ($result[0]['parent'] == 0) {

       } else {
           
           $this->db->select('id,parent,name');
           $query = $this->db->get_where( $tableName , array(
               'id' => $result[0]['parent'],
               'is_deleted' => 0
           ));
           $parent = $query->result_array();
           
           $return_item[] = $parent[0]['name'];
           
           $parent = $this->get_root_item($parent[0]['id'], $tableName);
           
           $return_item = array_merge($return_item, $parent);
       }
       
       return $return_item;
   }

   public function get_cat_name_by_id($id, $tableName)
    {
        $this->db->select('name');
        $query = $query = $this->db->get_where( $tableName , array(
            'id' => $id,
            'is_deleted' => 0
        ));
        $cate = $query->result_array();
        
        return $cate[0]['name'];
    }

   
}





