<?php
/*
 * @author Atiqur Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Category_Model extends CI_Model
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
        $this->load->model('portal/Events_model');
    }
    
    public function init( $table_name ) {
        $this->_current_table_name = $table_name;
    }

    public function create_category($data)
    {
        $valid = $this->check_validity($data);
        $status = 0;
        if ($valid) {
            $data['account_id'] = $this->_account_id;
            $status = $this->db->insert($this->_current_table_name, $data);
            $msg_id =  $this->db->insert_id();
            $status = $msg_id;

            $this->Events_model->executeTableEntryEvent('add_entry', $this->_current_table_name, $data, $msg_id );
        
        } else {
            $status = 'existance';
        }
        
        return $status;
    }

    /*
     * Function :: Update - for update existing category
     */
    public function update($id, $data)
    {
        $data['account_id'] = $this->_account_id;
        $status = $this->db->update($this->_current_table_name, $data, array(
            'id' => $id
        ));

        $this->Events_model->executeTableEntryEvent('update_entry', $this->_current_table_name, $data, $id );
        
        return $status;
        

    }

    /*
     * Function :: Delete - for delete existing category
     */
    public function delete($id)
    {
        $data = array(
            'is_deleted' => 1
        );
       
       $this->Events_model->executeTableEntryEvent('delete_entry', $this->_current_table_name, array(), $id );
       
       $query = $this->db->update($this->_current_table_name, $data, array(
                'id' => $id
            ));
                    

        return $query;
    }

    /*
     * Function :: Chceck_validity - for check specific category exist or not
     * return :: boolean
     */
    public function check_validity($data)
    {
        $this->db->select('id');
        $query = $this->db->get_where($this->_current_table_name, array(
            'name' => $data['name'],
            'parent' => $data['parent'],
            'is_deleted' => 0,
            'account_id' => $this->_account_id
        ));
        if ($query->num_rows() > 0) {
            return false;
        } else {
            return true;
        }
    }

    function check_validity_by_name($name, $parent)
    {
        $this->db->select('id');
        $query = $this->db->get_where($this->_current_table_name, array(
            'name' => $name,
            'parent' => $parent,
            'is_deleted' => 0,
            'account_id' => $this->_account_id
        ));
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    /*
     * Function :: Check_have_child - for check status the specific category is root or not
     * return :: boolean
     */
    public function check_have_child($id)
    {
        $this->db->select('id');
        $query = $this->db->get_where($this->_current_table_name, array(
            'parent' => $id,
            'is_deleted' => 0,
            'account_id' => $this->_account_id
        ));
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Function :: get_child - for getting all first child of specifc category
     */
    public function get_childs($id)
    {
        $this->db->select('id');
        $query = $this->db->get_where($this->_current_table_name, array(
            'parent' => $id,
            'is_deleted' => 0,
            'account_id' => $this->_account_id
        ));
    }

    /*
     * Function :: count_child - for counting cild of specific parent
     */
    public function count_child($id)
    {
        $query = "SELECT COUNT(name) as total FROM ".$this->_current_table_name." WHERE parent = $id AND is_deleted != 1 AND 'account_id' = $this->_account_id";
        $query = $this->db->query($query);
        
        foreach ($query->result() as $row) {
            $total = $row->total;
        }
        return $total;
    }

    /*
     * Function :: get_parent - for getting parent information of specific category
     */
    public function get_parent()
    {}
    
    /*
     * Function :: display_category() - for display category
     */
    public function get_category($id)
    {
        
        $query = $this->db->get_where($this->_current_table_name, array(
            'id' => $id,
            'is_deleted' => 0
        ));
        if (empty($query->result_array())) {
            $temp_data = 0;
        } else {
            foreach ($query->result_array() as $row) {
                $temp_data = $row;
            }
        }
        return $temp_data;
    }

    /*
     * Function :: get_all_category()
     */
    public function get_all_category()
    {
        $temp_data = array();
        $query = $this->db->get_where($this->_current_table_name, array(
            'is_deleted' => 0,
            'account_id' => $this->_account_id
        ));
        if (empty($query->result_array())) {
            $temp_data = 0;
        } else {
            foreach ($query->result_array() as $row) {
                array_push($temp_data, $row);
            }
        }
        
        return $temp_data;
    }

    public function display_category()
    {}

    public function get_root_path($price_id)
    {
        $this->db->select('categories_id');
        $query = $this->db->get_where('prices', array(
            'id' => $price_id,
            'is_deleted' => 0
        ));
        $result = $query->result_array();
        
        $this->db->select('name');
        $query = $query = $this->db->get_where($this->_current_table_name, array(
            'id' => $result[0]['categories_id'],
            'is_deleted' => 0
        ));
        $cate = $query->result_array();
        array_push($this->all_parent, $cate[0]['name']);
        
        $this->get_root_item($result[0]['categories_id']);
        
        return $this->all_parent;
    }

    public function get_cat_name_by_id($id)
    {
        $this->db->select('name');
        $query = $query = $this->db->get_where($this->_current_table_name, array(
            'id' => $id,
            'is_deleted' => 0
        ));
        $cate = $query->result_array();
        
        return $cate[0]['name'];
    }

    public function get_root_item($id)
    {
        $this->db->select('id,parent,name');
        $query = $this->db->get_where($this->_current_table_name, array(
            'id' => $id,
            'is_deleted' => 0
        ));
        $result = $query->result_array();
        $return_item = array();
        
        if ($result[0]['parent'] == 0) {} else {
            
            $this->db->select('id,parent,name');
            $query = $this->db->get_where($this->_current_table_name, array(
                'id' => $result[0]['parent'],
                'is_deleted' => 0
            ));
            $parent = $query->result_array();
            
            $return_item[] = $parent[0]['name'];
            
            $parent = $this->get_root_item($parent[0]['id']);
            
            $return_item = array_merge($return_item, $parent);
        }
        
        return $return_item;
    }

    public function format_parent($all_parent, $id)
    {
        $self_cat = $this->get_cat_name_by_id($id);
        $str = '';
        for ($i = count($all_parent) - 1; $i >= 0; $i --) {
            $str .= $all_parent[$i] . " > ";
        }
        $str .= $self_cat;
        return $str;
    }

    /*
     * Function :: search() - for searching a category
     */
    public function search_category($str)
    {
        $tableNameUpper = strtoupper($this->_current_table_name);
        $limit = $this->config->item("#CORE_{$tableNameUpper}_CATEGORY_MAX_PARENTS_WILL_SHOW");
        $limit = empty($limit)? 30 : $limit;
        $str = $this->db->escape_like_str($str);
        $query = $this->db->query("SELECT * FROM  ".$this->_current_table_name." WHERE id = '1' AND is_deleted='0' LIMIT {$limit}");
        $query_1 = $this->db->query("SELECT * FROM  ".$this->_current_table_name." WHERE name LIKE '%$str%' AND id > '1' AND is_deleted='0' AND account_id =" . $this->_account_id . " LIMIT {$limit}");
        $result_1 = $query->result_array();
        $result_2 = $query_1->result_array();
        
        $result = array_merge($result_1, $result_2);

        for ($count = 0; $count < sizeof($result); $count ++) {
            
            $all_parent = $this->get_root_item($result[$count]['id']);
            $root_path = $this->format_parent($all_parent, $result[$count]['id']);
            
            $result[$count]['name'] = $root_path;
        }
        
        return array(
            'items' => $result
        );
    }

    /*
     * Function get_roo_items
     */
    public function get_root_items($parent_id = 1)
    {
        $data = array();
        $temp_data = array();
        
        $sql_query = 'SELECT * FROM '.$this->_current_table_name.'  WHERE parent="' . $parent_id . '" AND is_deleted=0 AND account_id =' . $this->_account_id . ' ORDER BY sort ASC';
        $query = $this->db->query($sql_query);
        
        foreach ($query->result() as $row) {
            $temp_data['row'] = $row;
            $temp_data['count'] = $this->count_child($row->id);
            array_push($data, $temp_data);
        }
        
        return $data;
    }

    public function tree_count($id)
    {
        $total = $this->count_child($id);
        return $total;
    }

    function get_cat_name_from_id($category_id)
    {
        return $this->db->query("SELECT name FROM ".$this->_current_table_name."  WHERE id='$category_id' AND is_deleted='0' AND account_id =" . $this->_account_id . "")->result_array()[0]['name'];
    }

    function get_categorys_list($category_id)
    {
        $query = $this->db->query("SELECT * FROM ".$this->_current_table_name."  WHERE id=$category_id and is_deleted=0 AND account_id =" . $this->_account_id . "");
        return $query->result();
    }

    function check_exists_in_another_table($cat_id)
    {
        $found = false;
        $check_exists[] = $this->check_exists_soft_delete('objects', 'categories_id', $cat_id);
        $check_exists[] = $this->check_exists_soft_delete('prices', 'categories_id', $cat_id);
        $check_exists[] = $this->check_exists_hard_delete('objects_categories', 'categories_id', $cat_id);
        
        for ($count = 0; $count < sizeof($check_exists); $count ++) {
            
            if ($check_exists[$count] == '1') {
                
                $found = true;
            }
        }
        
        return $found;
    }

    function check_exists_soft_delete($table_name, $field_name, $id)
    {
        $check_exists = $this->db->query("SELECT * FROM ".$this->_current_table_name." WHERE `$field_name`='$id' AND is_deleted='0' AND account_id =" . $this->_account_id)->num_rows();
        if ($check_exists > 0) {
            
            return 1;
        } else {
            
            return 0;
        }
    }

    function check_exists_hard_delete($table_name, $field_name, $id)
    {
        $check_exists = $this->db->query("SELECT * FROM ".$this->_current_table_name." WHERE `$field_name`='$id' AND account_id =" . $this->_account_id . "")->num_rows();
        if ($check_exists > 0) {
            
            return 1;
        } else {
            
            return 0;
        }
    }

    function get_most_parent_cat_id($category_id)
    {
        $parent_categories = array();
        
        $not_end = true;
        $parent_categories[] = $parent_id = $category_id;
        while ($not_end) {
            
            $cat_result = $this->db->query("SELECT id,parent FROM ".$this->_current_table_name."  WHERE is_deleted='0' AND parent='$parent_id'")->result_array();
            if (sizeof($cat_result) > 0) {
                
                $parent_id = $cat_result[0]['id'];
                $parent_categories[] = $parent_id;
            } else {
                $not_end = false;
            }
        }
        
        return $this->get_cat_name_from_id($parent_categories[sizeof($parent_categories) - 1]);
    }

    public function all_childs_list($category_id)
    {
        $child_categories = array();
        
        $not_end = true;
        $child_categories[] = $parent_id = $category_id;
        
        while ($not_end) {
            
            $cat_result = $this->db->query("SELECT id FROM `".$this->_current_table_name."` WHERE is_deleted='0' AND parent='$parent_id'")->result_array();
            if (sizeof($cat_result) > 0) {
                
                $parent_id = $cat_result[0]['id'];
                
                foreach ($cat_result as $cats) {
                    foreach ($this->all_childs_list($cats['id']) as $catsss) {
                        $child_categories[] = $catsss;
                    }
                }
            } else {
                $not_end = false;
            }
        }
        
        return $child_categories;
    }

    public function delete_parent_category()
    {
        $cat_id = $this->input->post("cat_id");
        $this->db->select("id");
        $all_immediate_childs = $this->db->get_where($this->_current_table_name, array(
            "parent" => $cat_id,
            "is_deleted" => 0
        ))->result_array();
        
        $all_cats[] = $cat_id;
        foreach ($all_immediate_childs as $childs) {
            
            $all_childs = $this->all_childs_list($childs['id']);
            foreach ($all_childs as $child) {
                $all_cats[] = $child;
            }
        }
        
        foreach ($all_cats as $category) {
            
           $this->Events_model->executeTableEntryEvent('delete_entry', $this->_current_table_name, array(), $category );        
            $this->db->where("id", $category);
            $this->db->update($this->_current_table_name, array(
                "is_deleted" => 1
            ));

        }


    }

    public function uploadCategoryPicture() {        
        $this->load->model('Upload_file_Model');
        $msg = "";
        $response = $this->Upload_file_Model->doUpload('file', $docTypeId, $mainTblId);     
        if ($response["status"]) {
            $file_path = $response['filePath'];
            $status = 1;
            $TABLE_NAME_UPPER = strtoupper($this->_current_table_name);
            $maxImageWidth = $this->config->item("#CORE_{$TABLE_NAME_UPPER}_CATEGORY_IMAGE_WIDTH");
            $maxImageWidth = empty($maxImageWidth)? 300 : $maxImageWidth;
            $image_file_preview = "<img src='{$file_path}' alt='' class='img' id='cat-picture-preview' style='max-width: {$maxImageWidth}px;'>";
        } else {
            $status = 0;
            $file_path = "";
            $image_file_preview = '';
            $msg = dashboard_lang('_FILE_UPLOADING_FAILED_PLEASE_TRY_AGAIN');
        } 
               
        return array("status" => $status, "file_path" => $file_path, "image_file_preview" => $image_file_preview, "msg" => $msg);
    }

    public function checkStatusOfRoot($parent){
        if(empty($parent)){
            $rootCat = $this->db->get_where($this->_current_table_name, array(
                "name" => "root",
                "is_deleted" => 0,
                "account_id" => $this->_account_id,
            ))->row_array();
    
            if(is_array($rootCat)){
                return $rootCat['id'];
            } else{
                $data = array(
                    'name' => "root",
                    'sort' => 0,
                    'parent' => 0,
                    'picture' => "",
                    'account_id' => $this->_account_id,
                );
                $this->db->insert($this->_current_table_name, $data);
                return $this->db->insert_id();
            }
        } else{
            return $parent;
        }        
    }
}
