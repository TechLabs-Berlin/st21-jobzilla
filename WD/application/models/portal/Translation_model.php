<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Translation_model extends CI_Model
{

    private $_tableName = 'translations';

    public $_table_prefix;

    function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
    }

    public function insert_translations($key, $value)
    {
        $this->load->model('language_model');
        $translation_table_name = $this->_table_prefix . $this->_tableName;
        $site_language = $this->language_model->get_user_default_language();
        if (! $site_language) {
            
            $site_language = $this->config->item('#LANGUAGE_DEFAULT');
        }
        
        $account_id = get_default_account_id();
        $language_id = $this->language_model->get_lanuage_id($site_language);
        


        if ($site_language != "english") {
            
            $data['language_id'] = $language_id;
            $data['language_key'] = $key;
            $data['language_value'] = "";
            $data['account_id'] = $account_id;
            $data['is_deleted'] = 0;
        } else {
            
            $data['language_id'] = $language_id;
            $data['language_key'] = $key;
            $data['language_value'] = $value;
            $data['account_id'] = $account_id;
            $data['is_deleted'] = 0;
        }
        
        $this->db->insert($translation_table_name, $data);
        
        return $value;
        
    }
}