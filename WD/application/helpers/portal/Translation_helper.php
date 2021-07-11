<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class BTranslationHelper extends BObject
{

    public static $_instance;

    public function __construct()
    {
        parent::__construct();
        
        $CI = & get_instance();
        $account_id = get_default_account_id();
        $CI->load->model('language_model');
        $site_language = $this->language_model->get_user_default_language();
        
        $_table_prefix = $CI->config->item("prefix");
        
        $language_detail = $CI->db->get_where($_table_prefix . 'languages', array(
            'name' => $site_language
        ))->row_array();
        
        if (isset($language_detail) && isset($language_detail['id']) && $language_detail) {
            $transtaions = $CI->db->get_where($_table_prefix . 'translations', array(
                'language_id' => $language_detail['id'],
                'account_id' => 1,
                'is_deleted' => 0
            ))->result_array();
            if ($account_id) {
                $transtaions = array_merge($transtaions, $CI->db->get_where($_table_prefix . 'translations', array(
                    'language_id' => $language_detail['id'],
                    'account_id' => $account_id,
                    'is_deleted' => 0
                ))->result_array());
            }
            if (isset($transtaions) && is_array($transtaions) && ! empty($transtaions)) {
                foreach ($transtaions as $transtaion) {
                    $CI->lang->language[$transtaion['language_key']] = $transtaion['language_value'];
                }
            }
        }
    }

    /*
     * get instance of the config object
     */
    public static function get_instance()
    {
        if (! isset(self::$_instance)) {
            self::$_instance = new BTranslationHelper();
        }
        return self::$_instance;
    }

    /*
     * get the translation of a string
     */
    public static function get($key)
    {
        self::get_instance();
        $CI = get_instance();
        
        if ($CI->lang->language[$key]) {
            
            return $CI->lang->language[$key];
        } else {
            /*
             * translation not found
             * so add new translation for english
             * or return translation key for other lang
             */
            $CI->load->model('dashboard_model');
            $not_found_translated_string = format_string($key);
            $inserted_string = "";
            $inserted_string = $CI->dashboard_model->insert_translations($key, $not_found_translated_string);
            
            if (strlen($inserted_string) != 0) {
                return $inserted_string;
            } else {
                return $key;
            }
        }
    }
}
