<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class System_Model extends Base_Model
{

    private $_template_table = 'save_search_templates';

    private $_temp_tbl_name = 'temp_translations';

    private $_tbl_name = 'translations';

    private $_tbl_name_set = 'settings';

    private $_temp_tbl_name_set = 'temp_settings';

    public function __construct()
    {
        parent::__construct();
    }
    
    /*
     * saves template data
     */
    public function save_template($data)
    {
        if ($data['id'] > 0) {
            
            $this->db->where('id', $data['id']);
            $this->db->update($this->_template_table, $data);
        } else {
            
            $this->db->insert($this->_template_table, $data);
        }
    }

    public function check_translations($id)
    {
        $limit = 100;
        
        $query = $this->db->select('*')
            ->from($this->_temp_tbl_name)
            ->limit($limit)
            ->order_by('id', 'dsc')
            ->where('id >' . $id)
            ->get();
        $query_res = $query->result_array();
        
        foreach ($query_res as $translation_data) {
            $language_key = $translation_data['language_key'];
            $language_id = $translation_data['language_id'];
            $query_second = $this->db->select('*')
                ->from($this->_tbl_name)
                ->where(array(
                'language_key' => $language_key,
                'language_id' => $language_id
            ))
                ->order_by('id', 'dsc')
                ->get();
            $number_of_rows = $query_second->num_rows();
            $res_array = $query_second->result_array();
            
            $id = $translation_data['id'];
            unset($translation_data['id']);
            
            if ($number_of_rows == 0) {
                $this->db->insert($this->_tbl_name, $translation_data);
            } else {
                foreach ($res_array as $res) {
                    if (strlen($res['language_value']) == 0) {
                        $this->db->where('id', $res['id']);
                        $this->db->update($this->_tbl_name, array(
                            'language_value' => $translation_data['language_value']
                        ));
                    }
                }
            }
        }
        if (sizeof($query_res) > 0) {
            redirect(base_url("portal/system/check_translations/$id"));
        }
    }
    
    /*
     * comapring `settings` table wil `temp_settings` table
     * if not exists, insert into `settings'
     * if exists, nothing happens
     */
    public function check_settings($id)
    {
        $limit = 100;
        
        $query = $this->db->select('*')
            ->from($this->_temp_tbl_name_set)
            ->limit($limit)
            ->order_by('id', 'dsc')
            ->where('id >' . $id)
            ->get();
        $query_res = $query->result_array();
        
        foreach ($query_res as $settings_data) {
            $key = $settings_data['key'];
            $value = $settings_data['value'];
            $query_second = $this->db->select('*')
                ->from($this->_tbl_name_set)
                ->where(array(
                'setting' => $key
            ))
                ->order_by('id', 'dsc')
                ->get();
            $number_of_rows = $query_second->num_rows();
            $res_array = $query_second->result_array();
            
            $id = $settings_data['id'];
            unset($settings_data['id']);
            
            if ($number_of_rows == 0) {
                $this->db->insert($this->_tbl_name_set, $settings_data);
            }
        }
        if (sizeof($query_res) > 0) {
            redirect(base_url("portal/system/check_settings/$id"));
        }
    }
    
    /*
     * get's template data
     */
    public function get_template($id)
    {
        $row = $this->db->get_where($this->_template_table, array(
            'id' => $id
        ));
        return $row;
    }
    
    /*
     * delete a template
     */
    public function delete_search_view($id)
    {
        $this->db->set('is_deleted', 1);
        $this->db->where('id', $id);
        $this->db->update($this->_template_table);
        return 1;
    }

    public function get_listing_show_fields($table_name)
    {
        $user_id = $this->session->userdata('user_id');
        
        $this->db->select('list_fields,order_fields');
        $result = $this->db->get_where('listing_fields', array(
            "user_id" => $user_id,
            "table_name" => $table_name,
            "is_deleted" => 0
        ))->result_array();
        
        if (sizeof($result) > 0) {
            
            $data['listing_fields'] = $result['list_fields'];
        } else {}
    }
    
    /*
     * Getting All saved Templates From a Table Name
     *
     */
    public function get_all_saved_template($table_name)
    {
        $this->db->select('id,template_name,for_all');
        $this->db->order_by('template_name', $table_name);
        $this->db->where("is_deleted", 0);
        $this->db->where("table_name", $table_name);
        $this->db->group_start();
        $this->db->or_where("for_all", 1);
        $this->db->or_where("created_by", get_user_id());
        $this->db->group_end();
        $all_templates = $this->db->get($this->_template_table)->result_array();
        return $all_templates;
    }

    public function update_listing_fields($template_id, $table_name)
    {
        $update_listing = array();
        $update_listing['template_id'] = $template_id;
        
        if ($template_id == '0') {
            
            $update_listing['list_fields'] = $this->get_default_listing_fields($table_name);
        }
        
        $user_id = $this->session->userdata('user_id');
        
        $this->db->where("user_id", $user_id);
        $this->db->where("table_name", $table_name);
        
        $this->db->update("listing_fields", $update_listing);
    }

    public function save_template_form_data()
    {
        $action_type = $this->input->post('template_action_type');
        $show_fields = $this->input->post('template_listing_fields');
        $jesonData = $this->input->post('template_ordering_fields');
        $jesonArray = json_decode($jesonData, TRUE);
        $order_fields = serialize($jesonArray);

        if ( isset($_GET["iframeView"]) ) {

            $getParams = "?iframeView=1";
        }else {
            $getParams = "";
        }
        
        
        if ($action_type == 'save_as') {
            
            $data['table_name'] = $this->input->post('template_table_name');
            $data['template_name'] = $this->input->post('template_new_save_name');
            $data['session_data'] = $this->get_current_session_data($data['table_name'], $show_fields, $order_fields);
            $data['for_all'] = $this->input->post('template_for_all_user');
            $data['created_by'] = get_user_id();
            
            $template_id = $this->update_template($data);
            
            $this->update_listing_fields($template_id, $data['table_name']);
            
            redirect("dbtables/" . $data['table_name'] . "/listing".$getParams);
        } else 
            if ($action_type == 'show_template') {
                
                $table_name = $this->input->post('template_table_name');
                $template_id = $this->input->post('template_id');
                
                if ($template_id != '0') {
                    
                    $this->unset_all_column_filtering($table_name);
                    $this->unset_table_main_search($table_name);
                    $this->unset_table_sort_direction();
                    
                    $this->update_listing_fields($template_id, $table_name);
                    $this->set_template_search_from_id(array(
                        'id' => $template_id
                    ));
                } else {
                    
                    $this->unset_all_column_filtering($table_name);
                    $this->unset_table_main_search($table_name);
                    $this->unset_table_sort_direction();
                    
                    $this->update_listing_fields(0, $table_name);
                }
                
                redirect("dbtables/" . $table_name . "/listing".$getParams);
            } else 
                if ($action_type == 'save') {
                    $template_id = $this->input->post('template_id');
                    $data['table_name'] = $this->input->post('template_table_name');
                    $data['session_data'] = $this->get_current_session_data($data['table_name'], $show_fields, $order_fields);
                    
                    $this->update_template_by_id($data, $template_id);
                    
                    redirect("dbtables/" . $data['table_name'] . "/listing".$getParams);
                }
    }

    public function get_default_listing_fields($table_name)
    {
        $xmlData = $this->xml_parsing($table_name);
        
        foreach ($xmlData->field as $value) {
            if (isset($value['show']) && intval($value['show']) > 0) {
                $tableField[intval($value['show'])] = (string) $value['name'];
            }
        }
        
        return implode(",", $tableField);
    }

    protected function xml_parsing($table_name)
    {
        if (empty($this->_xmldata)) {
            $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . $table_name . ".xml";
            libxml_use_internal_errors(true);
            $this->_xmldata = simplexml_load_file($xmlFile);
            if ($this->_xmldata === false) {
                /*
                 * xml parsing error, shown error message in below
                 */
                foreach (libxml_get_errors() as $error) {
                    echo dashboard_lang('_DASHBOARD_XML_PERSING_ERROR') . "\t", $error->message;
                }
                exit();
            }
            $this->tableName = $this->config->item("prefix") . $this->_xmldata['name'];
            
            return $this->_xmldata;
        } else {
            return $this->_xmldata;
        }
    }

    public function update_template($data)
    {
        $check_template_exists = $this->db->get_where($this->_template_table, array(
            "template_name" => $data['template_name'],
            "is_deleted" => 0
        ))->result_array();
        
        if (sizeof($check_template_exists) > 0) {
            
            $this->db->where("id", $check_template_exists[0]['id']);
            $this->db->update($this->_template_table, $data);
            
            return $check_template_exists[0]['id'];
        } else {
            
            $this->db->insert($this->_template_table, $data);
            return $this->db->insert_id();
        }
    }

    public function update_template_by_id($data, $template_id)
    {
        $this->db->where("id", $template_id);
        $this->db->update($this->_template_table, $data);
    }

    public function get_current_session_data($table_name, $show_fields, $order_fields)
    {
        $column_filtering_data = $this->get_column_filtering($table_name);
        $main_search = $this->get_main_table_search($table_name);
        $table_sort_field = $this->get_table_sort_field();
        $table_sort_direction = $this->get_table_sort_direction();
        
        $per_page = $this->session->userdata('per_page@' . $table_name);
        if (strlen($per_page) == 0) {
            
            $per_page = 10;
        }
        
        return json_encode(array(
            "main_search" => $main_search,
            "table_sort_field" => $table_sort_field,
            "table_sort_direction" => $table_sort_direction,
            "table_name" => $table_name,
            "column_filtering_data" => $column_filtering_data,
            "show_fields" => $show_fields,
            "order_fields" => $order_fields,
            "per_page" => $per_page
        ));
    }

    public function get_table_sort_field()
    {
        $sort_field = $this->session->userdata('table_sort_field');
        if (isset($sort_field)) {
            
            return $sort_field;
        } else {
            return '';
        }
    }

    public function get_table_sort_direction()
    {
        $sort_direction = $this->session->userdata('table_sort_direction');
        if (isset($sort_direction)) {
            
            return $sort_direction;
        } else {
            return '';
        }
    }

    public function unset_all_column_filtering($table_name)
    {
        $all_session_data = $this->session->all_userdata();
        
        foreach ($all_session_data as $key => $value) {
            
            $check_key_exists = strpos($key, $table_name . "_");
            
            if ($check_key_exists !== false) {
                
                $this->session->unset_userdata($key);
            }
        }
    }

    public function unset_table_main_search($table_name)
    {
        $key = "search_" . $table_name;
        
        $all_session_data = $this->session->all_userdata();
        if (isset($all_session_data[$key])) {
            
            $this->session->unset_userdata($key);
        }
    }

    public function unset_table_sort_direction()
    {
        $this->session->unset_userdata('table_sort_field');
        $this->session->unset_userdata('table_sort_direction');
    }

    public function get_main_table_search($table_name)
    {
        $key = "search_" . $table_name;
        $value = $this->session->userdata($key);
        
        if (isset($value)) {
            
            return $value;
        } else {
            
            return '';
        }
    }

    public function get_column_filtering($table_name)
    {
        $match_key = $table_name . "_";
        $data = array();
        
        $all_session_data = $this->session->all_userdata();
        foreach ($all_session_data as $key => $value) {
            
            $check_key_matched = strpos($key, $match_key);
            if ($check_key_matched !== false) {
                
                $column_filtering_data = array();
                
                $explode_key = preg_replace("/$match_key/", '', $key, 1);
                
                if (strlen($explode_key) > 0) {
                    
                    $column_filtering_data[$explode_key] = $value;
                    $data[] = $column_filtering_data;
                }
            }
        }
        
        return $data;
    }
    
    // session json data will be like {"main_search":"23" ,"show_fields" => "", "order_fields" =>"", "items_per_page"=>"" ,
    // "table_sort_field" =>"",
    // "table_sort_direction" => "", "table_name":"", "column_filtering_data": [{"email":"niloy@gmail"}, {"phone":"233"}]}
    public function set_template_search_from_id($templates)
    {
        $this->db->select('id,table_name,session_data');
        $all_templates = $this->db->get_where($this->_template_table, $templates)->result_array();
        
        if (sizeof($all_templates) > 0) {
            
            $json_parsed_data = json_decode($all_templates[0]['session_data'], TRUE);
            
            $this->set_main_search($json_parsed_data, $all_templates[0]['table_name']);
            $this->set_column_filtering($json_parsed_data, $all_templates[0]['table_name']);
            $this->set_table_direction($json_parsed_data);
            $this->set_per_page($json_parsed_data);
            $this->set_show_fields_order_fields($json_parsed_data, $all_templates[0]['table_name']);
        }
    }

    public function set_per_page($json_parsed_data)
    {
        $per_page = $json_parsed_data['per_page'];
        $table_name = $json_parsed_data['table_name'];
        if (strlen($per_page) != '0') {
            
            $this->session->set_userdata('per_page@' . $table_name, $per_page);
        }
    }

    public function set_show_fields_order_fields($json_parsed_data, $table_name)
    {
        $data = array();
        
        $data['list_fields'] = $json_parsed_data['show_fields'];
        $data['order_fields'] = $json_parsed_data['order_fields'];
        
        $user_id = $this->session->userdata('user_id');
        
        $this->db->where("user_id", $user_id);
        $this->db->where("table_name", $table_name);
        $this->db->update("listing_fields", $data);
    }

    public function set_table_direction($parsed_data)
    {
        $this->session->unset_userdata('table_sort_field');
        $this->session->unset_userdata('table_sort_direction');
        $this->session->set_userdata('table_sort_field', $parsed_data['table_sort_field']);
        $this->session->set_userdata('table_sort_direction', $parsed_data['table_sort_direction']);
    }
    
    //
    public function set_main_search($parsed_data, $table_name)
    {
        $key = "search_" . $table_name;
        if (isset($parsed_data['main_search']) && strlen($parsed_data['main_search'])) {
            
            $this->session->set_userdata($key, $parsed_data['main_search']);
        }
    }
    
    // Setting column Filtering In Sessions
    public function set_column_filtering($parsed_data, $table_name)
    {
        $column_filtering_data = $parsed_data['column_filtering_data'];
        
        foreach ($column_filtering_data as $filtering_data) {
            
            foreach ($filtering_data as $key => $value) {
                
                $this->session->set_userdata($table_name . "_" . $key, $value);
            }
        }
    }

    public function udpate_existing_view_name($data, $id)
    {
        $this->db->where("id", $id);
        $this->db->update($this->_template_table, $data);
        return 1;
    }

    public function getFileFieldList($xmlData)
    {
        $fieldsArray = array();
        
        foreach ($xmlData as $fields) {
            $data = array();
            $fieldType = @(string) $fields['type'];
            if (! empty($fieldType) && ($fieldType == "image" || $fieldType == "file")) {
                $data['field_name'] = @(string) $fields['name'];
                $data['type'] = $fieldType;
                $data['sub_directory'] = @(string) $fields['sub_directory'];
                $data['source'] = @(string) $fields['source'];
                $fieldsArray[] = $data;
            }
        }
        return $fieldsArray;
    }

    public function get_template_id($table_name)
    {
        $user_id = $this->session->userdata('user_id');
        $result = $this->db->get_where("listing_fields", array(
            'table_name' => $table_name,
            "user_id" => $user_id,
            "is_deleted" => 0
        ))->result_array();
        
        if (sizeof($result) > 0) {
            
            return $result[0]['template_id'];
        } else {
            
            return 0;
        }
    }
}
