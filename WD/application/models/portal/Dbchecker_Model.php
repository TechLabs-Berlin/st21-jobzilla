<?php

/*
 * @author Atiqur Rahman
 */
class Dbchecker_Model extends CI_Model
{

    public $_errors = array();

    public $_errorFieldTypes = array();

    public $_liveDatabase;
    
    public function checkDBData()
    {
        $this->db->select('*');
        $this->db->where('account_id', $this->session->userdata('account_id'));
        $this->db->where('is_deleted', 0);
        $db_checker_row = $this->db->get('dbchecker')->row();
        if( count( $db_checker_row ) > 0 || ! is_null( $db_checker_row ) ){
            return $db_checker_row;
        } else {
            return false;
        }

    }
    
    public function insertDbData()
    {
        $this->load->model('portal/Version_checker_model');
        
        $hostname =  $this->input->post('hostname');
        $username =  $this->input->post('username');
        $password =  $this->input->post('password');
        $url =  $this->input->post('url');
        $dbname =  $this->input->post('dbname');
        
        $checkDB = $this->checkDB( $hostname, $username, $password, $dbname);
        $curl_ouput = $this->Version_checker_model->check_version_matched( $url );
        $file_ouput = $this->Version_checker_model->get_version( "application", "return" );
        $main_msg = "";
        if( $curl_ouput == $file_ouput ){
            $main_msg = dashboard_lang('_VERSION_MATCHES');
        } else {
            $main_msg = dashboard_lang('_VERSION_DOES_NOT_MATCH') . '<br/>' . $url . ' ' . dashboard_lang('_HAS_VERSION') . ' ' .$curl_ouput . '<br/>' . dashboard_lang('_CURRENT_VERSION_IS') . ' ' . $file_ouput;
        }
        if( $checkDB['status'] != 0 ){
            $config['hostname'] = trim($hostname);
            $config['username'] = trim($username);
            $config['password'] = trim($password);
            $config['database'] = trim($dbname);
            $config['url'] = trim($url);
            $config['account_id'] = $this->session->userdata('account_id');
            $config['is_deleted'] = 0;
            var_dump( $curl_ouput );
            var_dump( $file_ouput ); exit;
            if( $curl_ouput == $file_ouput ){
                return array( 
                    'is_inserted' => $this->db->insert( "dbchecker", $config ), 
                    'msg' => $checkDB['msg'],
                    'db_data' => $this->checkDBData(),
                    'v_msg_curl' => $curl_ouput,
                    'v_msg_file' => $file_ouput,
                    'msg_2' => $main_msg
                );
            } else {
                return array(
                    'is_inserted' => $this->db->insert( "dbchecker", $config ),
                    'msg' => $checkDB['msg'],
                    'db_data' => $this->checkDBData(),
                    'v_msg_curl' => $curl_ouput,
                    'v_msg_file' => $file_ouput,
                    'msg_2' => $main_msg
                );
            }
        } else {
            
            return array( 
                'is_inserted' => false, 
                'msg' => $checkDB['msg'],
                'db_data' => $this->checkDBData(),
                'v_msg_curl' => $curl_ouput,
                'v_msg_file' => $file_ouput,
                'msg_2' =>  $main_msg
            );
        }
        
    }

    public function checkDB( $host, $username, $pass, $db )
    {
        $config['hostname'] = trim($host);
        $config['username'] = trim($username);
        $config['password'] = trim($pass);
        $config['database'] = $this->_liveDatabase = trim($db);
        $config['dbdriver'] = 'mysqli';
        $config['dbcollat'] = 'utf8_general_ci';
        
        $liveDB = @$this->load->database($config, true);
        $msg = '';
        
        if (mysqli_connect_errno()) {
            $status = 0;
            $msg = dashboard_lang('_COULD_NOT_CONNECT_TO_DATABASE') . " " . mysqli_connect_error();
        } else {
            
            $this->compare_db($liveDB, $this->db);
            if (sizeof($this->_errors) > '0' || sizeof($this->_errorFieldTypes) > '0') {
                
                $status = 2;
                $msg = implode("<br/><br/>", $this->_errors);
                $msg .= "<br/><br/>";
                $msg .= implode("<br/><br/>", $this->_errorFieldTypes);
            } else {
                $status = 1;
                $msg = dashboard_lang("_BOTH_DATABASES_ARE_SAME");
            }
        }
        
        return array(
            'status' => $status,
            'msg' => $msg
        );
    }

    public function compare_tables($type = 'TABLE', $devItems, $liveItems, $devTableName = '')
    {
        $notMatched = array();
        foreach ($devItems as $item) {
            
            if (! in_array($item, $liveItems)) {
                $notMatched[] = $type == 'TABLE' ? $item : $devTableName . '.' . $item;
            }
        }
        
        if (sizeof($notMatched) > '0') {
            
            $this->_errors[] = implode(", ", $notMatched) . " " . dashboard_lang("THOSE_" . $type . "_ARE_IN") . " " . $this->_liveDatabase . " " . dashboard_lang("_BUT_NOT_IN") . " " . $this->db->database;
        }
    }

    public function fieldTypesDetails($dbInstance, $tableName)
    {
        $fieldsDetails = array();
        foreach ($dbInstance->field_data($tableName) as $details) {
            
            $fieldsDetails[$details->name]['name'] = $details->name;
            $fieldsDetails[$details->name]['type'] = $details->type;
            $fieldsDetails[$details->name]['max_length'] = $details->max_length;
            $fieldsDetails[$details->name]['primary_key'] = $details->primary_key;
            $fieldsDetails[$details->name]['default'] = $details->default;
        }
        
        return $fieldsDetails;
    }

    public function compareFieldTypes($devInstance, $liveInstance, $tableName)
    {
        $liveFieldsDetails = $this->fieldTypesDetails($liveInstance, $tableName);
        
        foreach ($devInstance->field_data($tableName) as $details) {
            
            if (isset($liveFieldsDetails[$details->name]) && is_array($liveFieldsDetails[$details->name])) {
                
                foreach ($details as $key => $value) {
                    
                    if ($liveFieldsDetails[$details->name][$key] != $value) {
                        
                        $this->_errorFieldTypes[] = "In  " . $this->_liveDatabase . " table $tableName field " . $details->name . "  Fieldtype $key is " . $value . ", but in " . $this->db->database . " Fieldtype $key is " . $liveFieldsDetails[$details->name][$key];
                    }
                }
            }
        }
    }

    public function compare_db($devDB, $liveDB)
    {
        $allDevTables = $devDB->list_tables();
        $allLiveTables = $liveDB->list_tables();
        
        $this->compare_tables("TABLE", $allDevTables, $allLiveTables);
        
        foreach ($allDevTables as $eachTable) {
            
            $devTableFields = $devDB->list_fields($eachTable);
            if (in_array($eachTable, $allLiveTables)) {
                
                $liveTableFields = $liveDB->list_fields($eachTable);
                $this->compare_tables("FIELDS", $devTableFields, $liveTableFields, $eachTable);
                $this->compareFieldTypes($devDB, $liveDB, $eachTable);
            }
        }
    }
}
