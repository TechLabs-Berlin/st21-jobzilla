<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class PageSpeedLogModel extends CI_Model
{

    private $_table_page_speed_log = 'page_speed_log';

    public function __construct()
    {
        parent::__construct();
    }

    public function transferLog()
    {
        if($this->existDb('speedLoggerDb')){

            $this->db->select('page_url,queries,query_count,query_exection_time,total_page_load_time,execution_date,project,user_id');
            $logs = $this->db->get($this->_table_page_speed_log);
            $speedLoggerDb = $this->load->database('speedLoggerDb', true);
            $items = $logs->result_array();
            if (count($items)) {
                $speedLoggerDb->insert_batch($this->_table_page_speed_log, $logs->result_array());
                $this->db->truncate($this->_table_page_speed_log);
            }
        }else{
            
            $this->db->truncate($this->_table_page_speed_log);
        }
        
    }

    private function existDb($params = ''){

        $file_path = APPPATH.'config/database.php';
        include($file_path);
        
        if (  isset($db[$params]))
        {
            return TRUE;
        }else{
            return FALSE;
        }
            
    }
        
}