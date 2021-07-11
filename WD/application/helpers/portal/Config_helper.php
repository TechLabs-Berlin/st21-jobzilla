<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class ConfigHelper extends BObject
{

    public static $_instance;

    public $_config_values;

    public function __construct()
    {
        parent::__construct();
        $CI = & get_instance();
        $account_id = get_default_account_id();
        
        if ($account_id) {
            $settings = array_merge(@$settings, $CI->db->get_where($CI->config->item("prefix") . 'settings', array(
                'account_id' => $account_id
            ))
                ->result_array());
        }
        if (isset($settings) && is_array($settings) && ! empty($settings)) {
            foreach ($settings as $setting) {
                $this->set_item($setting['setting'], $setting['value']);
            }
        }
    }

    /*
     * get instance of the config object
     */
    public static function get_instance()
    {
        if (! isset(self::$_instance)) {
            self::$_instance = new ConfigHelper();
        }
        return self::$_instance;
    }

    /*
     * get value of the key
     */
    public static function get($key)
    {
        $instance = self::get_instance();
        return $instance->{$key};
    }

    public function set_item($key, $value)
    {
        $this->_config_values[$key] = $value;
    }
}