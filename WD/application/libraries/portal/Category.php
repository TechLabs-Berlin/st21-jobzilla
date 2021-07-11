<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Category
{

    /* Member Variable */
    private $id;

    public $_current_table_name;

    public function __construct($params = '')
    {
        $CI = & get_instance();
        $CI->load->model('portal/Category_Model');
        $this->Category_Model = $CI->Category_Model;
    }

    public function init($table_name)
    {
        $this->Category_Model->init($table_name);
        $this->_current_table_name = $table_name;
    }

    /*
     * Function :: get_id()
     */
    public function get_id()
    {}

    /*
     * Function :: Create - for create new category
     */
    public function create_category($data)
    {
        $status = $this->Category_Model->create_category($data);
        return $status;
    }

    /*
     * Function :: Update - for update existing category
     */
    public function update($id, $data, $parent)
    {
        $child_array = $this->Category_Model->all_childs_list($id);
        
        
        if( in_array($parent, $child_array) ){
            $status = "child";
        } else {
            $status = $this->Category_Model->update($id, $data);
        }
        return $status;
    }

    /*
     * Function :: Delete - for delete existing category
     */
    public function delete($id)
    {
        $deletion = $this->Category_Model->delete($id);
        return $deletion;
    }

    /*
     * Function :: check_validity_by_name - for check specific category exist or not
     * return :: boolean
     */
    public function check_validity_by_name($name, $parent)
    {
        $valid = $this->Category_Model->check_validity_by_name($name, $parent);
        return $valid;
    }

    /*
     * Function :: Check_have_child - for check status the specific category is root or not
     * return :: boolean
     */
    public function check_have_child($id)
    {
        $have_child = $this->Category_Model->check_have_child($id);
        return $have_child;
    }

    /*
     * Function :: get_child - for getting all first child of specifc category
     */
    public function get_childs($id)
    {}

    /*
     * Function :: count_child - for counting cild of specific parent
     */
    public function count_child($id)
    {
        $children = $this->Category_Model->count_child($id);
        return $children;
    }

    /*
     * Function :: get_parent - for getting parent information of specific category
     */
    public function get_parent()
    {}

    /*
     * Function :: get_category() get specific category by given id
     */
    public function get_category($id)
    {
        $category = $this->Category_Model->get_category($id);
        return $category;
    }

    public function get_all_category()
    {
        $category = $this->Category_Model->get_all_category();
        return $category;
    }

    /*
     * Function :: display_category() - for display category
     */
    public function display_category()
    {}

    /*
     * Function :: search() - for searching a category
     */
    public function search_category($str)
    {
        $result = $this->Category_Model->search_category($str);
        echo json_encode($result);
    }

    public static function get_root_items($cat_id = 1)
    {
        $CI = & get_instance();
        $CI->load->model('Category_Model');
        $all_parents = $CI->Category_Model->get_root_items($cat_id);
        
        return $all_parents;
    }

    public function tree_count($id)
    {
        $total = $this->Category_Model->tree_count($id);
    }

    function check_exists_in_another_table($cat_id)
    {
        return $this->Category_Model->check_exists_in_another_table($cat_id);
    }

    public function delete_parent_category($cat_id)
    {
        $check_exists_in_another_table = SqlHelper::delete($this->_current_table_name, 'id', $cat_id, 0);
        
        if (! $check_exists_in_another_table['deleted']) {
            
            $message['status'] = false;
            $message['type'] = 'used_another_table';
            $message['message'] = dashboard_lang("_CANNOT_DELETE_USED_IN_ANOTHER_TABLE");
        } else {
            
            $message['status'] = true;
            $message['type'] = 'success';
            $this->Category_Model->delete_parent_category();
        }
        echo json_encode($message);
    }
}
