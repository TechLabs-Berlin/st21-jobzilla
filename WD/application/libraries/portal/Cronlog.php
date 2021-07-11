<?php
/*
 * @author boo2mark
 *
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Cronlog
{

    protected $CI;

    public $_table_prefix = "";

    protected $_login_table_name = 'dashboard_login';

    protected $_accounts_table_name = 'accounts';

    /*
     * constructor
     */
    function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
        $this->_table_prefix = $this->CI->config->item('prefix');
    }

    /*
     * Save Cron Activity
     */
    function activityCron($cron_name, $exception_message, $error_code)
    {
        $logCron = array(
            'failed_date' => strtotime('now'),
            'cron_name' => $cron_name,
            'error_code' => $error_code,
            'exception_message' => $exception_message
        );
        $saveCronActivity = $this->CI->db->insert('user_cron_activity', $logCron);
        // return $saveCronActivity;
    }
    /*
     * End of Save Cron Activity
     */
}
