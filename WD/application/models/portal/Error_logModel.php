<?php

/*
 * @author boo2mark
 *
 */
class Error_logModel extends CI_Model
{

    private $_error_log_table = 'error_log';

    public function __construct()
    {
        parent::__construct();
    }

    /*
     * tracks an error message into error log table
     */
    public function trackLog($table, $message, $action)
    {
        $data = array(
            'table_name' => $table,
            'message' => $message,
            'action' => $action,
            'time' => time()
        );
        $this->db->insert($this->_error_log_table, $data);
    }
}