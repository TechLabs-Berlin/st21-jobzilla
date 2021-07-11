<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Language_model extends CI_Model
{

    public $_table_prefix = '';

    public function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
    }

    function get_all_language()
    {
        $table_name = $this->_table_prefix . "languages";
        
        $this->db->select('name,code');
        $this->db->where("is_deleted", 0);
        $query = $this->db->get($table_name);
        
        return $query->result();
    }

    function get_user_default_language()
    {
        $CI = & get_instance();
        $userHelper = BUserHelper::get_instance();
        
        $language = $this->config->item('#LANGUAGE_DEFAULT');
        
        if (empty($userHelper->user->id)) {
            $languages = $this->get_all_language();
            
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) and ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                
                foreach ($languages as $language_selected) {
                    if ($language_selected->code == $lang) {
                        $language = $language_selected->name;
                    }
                }
            }
        } else {
            
            $language = $userHelper->user->language;
        }
        
        return $language;
    }

    function get_lanuage_id($lang_name)
    {
        $table_name = $this->_table_prefix . "languages";
        $result = $this->db->get_where($table_name, array(
            'name' => $lang_name
        ))->result_array();
        
        if (sizeof($result) > 0) {
            
            return $result[0]['id'];
        } else {
            
            return '2';
        }
    }
}