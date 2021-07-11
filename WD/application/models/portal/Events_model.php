<?php
/*
 * @author Ashrafuzzaman Sujan
*/
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

    class Events_model extends CI_Model
    {

        var $_events_table = 'events';

        public function checkDataValidation($tableName, $errorFor='', $errorType='', $inputValue, $conditionTrue = true){
            $valid = true;
            $modifiedValue = '';
            
            $this->db->select("code, description");
            $this->db->where('error_for', $errorFor);
            $this->db->where('error_type', $errorType);
            $this->db->where('status', 1);
            $this->db->where('is_deleted', 0);
            $events = $this->db->get($this->_events_table)->result(); 
            if(sizeof($events) > 0){
                foreach ($events as $event){
                
                    if(isset($event->code)){
                        eval($event->code);
                    } else{
                        $valid = true;
                        $modifiedValue = $inputValue;
                    }
                }
            } else{
                $valid = true;
                $modifiedValue = $inputValue;
            }                        
            
            $returnArr = array('valid'=> $valid, 'modifiedValue'=>$modifiedValue);
            return $returnArr;
            
        }
        
        
        public function executeEvent( $eventCode, $action ,$customMsg = '' ) {
            
            try {
                
                $codeExecuted = false;
                
                $this->db->select("code, description");
                $this->db->where('event_code', $eventCode);
                $this->db->where('status', 1);
                $this->db->where('is_deleted', 0);
                $events = $this->db->get($this->_events_table)->result();
                if(sizeof($events) > 0){
                    foreach ($events as $event){
                
                        if(isset($event->code)){
                            @eval($event->code);
                            $codeExecuted = true;
                        }
                    }
                }
                
                $returnArr = array('valid'=> $codeExecuted);
                
                return $returnArr;
                
            }catch ( ParseError  $e ) {
                
                $this->load->model("Event_logModel");
                $message = " $eventCode event Execution Error ";
                $action = " Error ";
                $this->Event_logModel->trackLog('Parse Error', $message, $action, "syntax_error");
            }
            
        }
        
        
        public function executeTableEntryEvent( $eventCode, $tableName, $data, $id ) {
        
            try {
        
                $codeExecuted = false;
        
                $this->db->select("code, description");
                $this->db->where('event_code', $eventCode);
                $this->db->where('status', 1);
                $this->db->where('is_deleted', 0);
                $events = $this->db->get($this->_events_table)->result();
                if(sizeof($events) > 0){
                    foreach ($events as $event){
        
                        if(isset($event->code)){
                            @eval($event->code);
                            $codeExecuted = true;
                        }
                    }
                }
        
                $returnArr = array('valid'=> $codeExecuted);
        
                return $returnArr;
        
            }catch ( ParseError  $e ) {
        
                $this->load->model("portal/Error_logModel");
                $message = " $eventCode event Execution Error ";
                $message .= "<br><strong>Line:</strong> {$e->getLine()}";
                $message .= "<br><strong>Message:</strong> {$e->getMessage()}";
                $action = "Error";
                $this->Error_logModel->trackLog($tableName, $message, $action);
            }
        
        }
        
        public function setSessionUsersIDs( $data_array )
        {
            $this->db->select('*');
            $this->db->where( $data_array );
            $login_user_row = $this->db->get( "dashboard_login" )->row();
            
            if( count( (array) $login_user_row ) > 0 ){
                $this->session->set_userdata('account_id', $login_user_row->account_id);
                $this->session->set_userdata('user_id', $login_user_row->id);
                return $login_user_row;
            } else {
                return false;
            }
        }
        
        public function unsettingIDs()
        {
            $this->session->unset_userdata('user_id');
            $this->session->unset_userdata('account_id');
        }
        
 

}
