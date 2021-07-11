<?php

/*
 * @author boo2mark
 *
 */
class Event_logModel extends CI_Model
{

    private $_event_log_table = 'event_log';
    private $_errors_table = 'errors';

    public function __construct()
    {
        parent::__construct();
    }

    /*
     * tracks an error message into error log table
     */
    public function trackLog($table, $message, $event_id, $action , $row_id = 0, $fieldName=null)
    {
        $saveLoggingData = $this->config->item("#SAVE_LOGGING_DATA");

        $data = array(
            'table_name' => $table,
            'message' => $message,
            'action' => $action,
            'field' => $fieldName,
            'event_id' => $event_id,
            'time' => time(),
            'user_id'=> get_user_id(),
            'row_id'=> $row_id,
            'ip'=> $this->getIp(),
            'account_id' => $this->session->userdata('account_id')
        );
        
        if ( $saveLoggingData == 'file' ) {
            
            $logFilePath = FCPATH."application/logs/";
            
            $filePath = $logFilePath."event_log.log";

            if ( file_exists($filePath) ) {
                if ( !is_writable($filePath) ) {
                    chmod($filePath, 0777);
                }
                 
                $fh = fopen($filePath, "a");
                $write_data = date("d-m-Y H:i:s")." ".json_encode($data);
                if(!fwrite($fh, $write_data."\n")){
                     
                    $this->insertData( $data );
                }
                 
            }else {
                $this->insertData( $data );
            }
            
        }else{
            $this->insertData( $data );
        }

    }
    
    public function insertData ( $data ) {
        $this->db->insert($this->_event_log_table, $data);
    }
	
	/*
     * get list of error codes
     * with description
     */
    public function getErrorList($orderBy = 'error_code')
    {
        $this->db->select("*");
        $this->db->where('status', 1);
        $errors = $this->db->get($this->_errors_table)->result();
        return $errors;
    }
    
    public function getIp()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        return $ip;
    }
    
    public function getLogs( $userid, $limit, $offset)
    {
        // $this->db->select("message");  
        $this->db->select("table_name");  
        $this->db->select("action");  
        $this->db->select("row_id");  
        $this->db->select("time");  
        $this->db->order_by('time', 'desc');
        $this->db->limit($limit);
        $this->db->offset($offset);
        $this->db->where('user_id', $userid);
        $this->db->where('table_name !=', 'user_notification');
        $logs = $this->db->get($this->_event_log_table)->result();
        $data['logs']=$logs;
        $data['edit_date_format']= $this->config->item("#EDIT_VIEW_DATE_TIME_FORMAT");
        return $data;
    }
}
