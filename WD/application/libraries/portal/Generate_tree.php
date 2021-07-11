<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Generate_tree
{

    /* Member Variable */
    private $id;

    public $_current_table_name;

    public function __construct($params = '')
    {
        $CI = & get_instance();
        $CI->load->model('portal/Generate_Tree_Model');
        $this->generate_tree_Model = $CI->Generate_Tree_Model;
    }

    public function init($table_name)
    {
        $this->generate_tree_Model->init($table_name);
    }

    public function generate_tree_structure($category_id)
    {
        return $this->generate_tree_Model->generate_tree_structure($category_id);
    }

    function get_category_details($cat_id)
    {
        return $this->generate_tree_Model->get_category_details($cat_id);        
    }

    function search_all_category($category_name)
    {
        return $this->generate_tree_Model->search_all_category($category_name);
    }
}