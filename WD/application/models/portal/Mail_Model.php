<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Mail_model extends CI_Model
{

    public $_table_prefix;

    function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
    }

    function send_mail_customer($customer_id, $pdf_file_location, $file_name)
    {
        $table_full_name = $this->_table_prefix . "contacts";
        $customer_email = $this->db->get_where($table_full_name, array(
            'customer_id' => $customer_id
        ))->result_array()[0]['email'];
        
        $from_name = $this->get_from_mail_name();
        $from_email = $this->get_from_mail_address();
        $message = dashboard_lang('_PDF_EMAIL_MESSAGE_BODY');
        $header = "";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type:text/html; charset=utf-8\n";
        if (file_exists($pdf_file_location)) {
            
            $header .= "MIME-Version: 1.0\r\n";
            $message = "<a href='" . base_url() . $pdf_file_location . "' download> " . base_url() . $pdf_file_location . "</a>";
        }
        $subject = dashboard_lang('_PDF_EMAIL_SUBJECT');
        $file = '';
        $this->send_mail($customer_email, $from_name, $from_email, $header, $message, $subject, $file , '', '', '');
    }

    function send_mail($to, $from_name, $from_email, $header, $message, $subject, $file, $to_name = '', $cc = '', $bcc = '')
    {
        $this->load->library('email');        
        $this->load->model('portal/Events_Model');

        $smtp_status = $this->config->item('smtp_status');        
        if ($smtp_status) :
            $this->email->set_newline("\r\n");        
        endif;
        

        if($to_name===''){
            $to_name = $to;
        }

 
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'; 

        $validEmail = false;
        if( is_array( $cc )){
            
            foreach( $cc as $key => $email){

                if( preg_match($regex, trim( $email ))) {

                    $validEmail = true;
                }else{
                    $validEmail = false;
                    break;
                }
            }
            if( $validEmail ){
                
                $this->email->cc($cc);
            }

        }else{
            if(preg_match($regex, trim($cc))) {

                $this->email->cc($cc);
            }
        }

        $validEmail = false;
        if( is_array( $bcc )){
            
            foreach( $bcc as $key => $email){

                if( preg_match($regex, trim( $email ))) {

                    $validEmail = true;
                }else{
                    $validEmail = false;
                    break;
                }
            }
            if( $validEmail ){
                
                $this->email->bcc($bcc);
            }

        }else{
            if(preg_match($regex, trim($bcc))) {

                $this->email->bcc($bcc);
            }
        }

        $this->email->to($to, $to_name);
        $this->email->from($from_email, $from_name);
        $this->email->subject($subject);
        $this->email->message($message);
        $smtp_status = $this->config->item('smtp_status');
    
        if (!empty($file)) {

            foreach ( $file as $filePath ) {

                if (file_exists($filePath)) {

                    $this->email->attach($filePath);
                }    
            }
        }
        
        $result = $this->email->send();
        
        $to = dashboard_lang('_SENT') .' '. dashboard_lang('_TO') . ': '. $to ."<$to_name>";
        if($cc != '') $cc = dashboard_lang('_CC') . ': '. $cc;
        if($bcc != '') $bcc = dashboard_lang('_BCC') . ': '. $bcc;

        $this->Events_model->_email_message = $message; 
        $this->Events_model->_action = $to . $cc. $bcc; 
        $this->Events_model->executeEvent('email_sent', 'email sent');
        $this->Events_model->_email_message = ''; 

        activityEmail('', $from_email, $to, $cc, $bcc, $subject, $message);

        $this->email->clear(TRUE);

        return $result;
    }


    function send_conversion_mail($to, $from_name, $from_email, $header, $message, $subject, $files, $to_name = '', $cc = '', $bcc = '')
    {
        $this->load->library('email');        
        $this->load->model('portal/Events_Model');

        $smtp_status = $this->config->item('smtp_status');        
        if ($smtp_status) :
            $this->email->set_newline("\r\n");        
        endif;
        

        if($to_name===''){
            $to_name = $to;
        }

 
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'; 

        if (preg_match($regex, trim($cc))) {
            $this->email->cc($cc);
        } 

        if (preg_match($regex, trim($bcc))) {
            $this->email->bcc($bcc);
        }
        
        $email = $this->config->item('#REPLAY_TO_EMAIL');
        if (preg_match($regex, trim($email))) {
            $this->email->reply_to($email);
        } 
        $this->email->to($to, $to_name);
        $this->email->from($from_email, $from_name);
        $this->email->subject($subject);
        $this->email->message($message);
        $smtp_status = $this->config->item('smtp_status');
    
        if (! empty($files)) {
            foreach($files as $file){
              $this->email->attach($file);
            }
        }
        
        $result = $this->email->send();

        if (! empty($files)) {
            foreach($files as $file){
            unlink($file);
            }
        }
        
        $to = dashboard_lang('_SENT') .' '. dashboard_lang('_TO') . ': '. $to ."<$to_name>";
        if($cc != '') $cc = dashboard_lang('_CC') . ': '. $cc;
        if($bcc != '') $bcc = dashboard_lang('_BCC') . ': '. $bcc;

        $this->Events_model->_email_message = $message; 
        $this->Events_model->_action = $to . $cc. $bcc; 
        $this->Events_model->executeEvent('email_sent', 'email sent');
        $this->Events_model->_email_message = ''; 
        
        return $result;
    }

    function send_external_mail($to, $from_name, $from_email, $header, $message, $subject, $files, $to_name = '', $cc = '', $bcc = '')
    {
        $this->load->library('email');        
        $this->load->model('portal/Events_Model');

        $smtp_status = $this->config->item('smtp_status');        
        if ($smtp_status) :
            $this->email->set_newline("\r\n");        
        endif;
        

        if($to_name===''){
            $to_name = $to;
        }
        
        $userData = BUserHelper::get_instance();
        $replay_to = getUserEmailFromId($userData->user->id);
        $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'; 

        $validEmail = false;
        if( is_array( $cc )){
            
            foreach( $cc as $key => $email){

                if( preg_match($regex, trim( $email ))) {

                    $validEmail = true;
                }else{
                    $validEmail = false;
                    break;
                }
            }
            if( $validEmail ){
                
                $this->email->cc($cc);
            }

        }else{
            if(preg_match($regex, trim($cc))) {

                $this->email->cc($cc);
            }
        }

        $validEmail = false;
        if( is_array( $bcc )){
            
            foreach( $bcc as $key => $email){

                if( preg_match($regex, trim( $email ))) {

                    $validEmail = true;
                }else{
                    $validEmail = false;
                    break;
                }
            }
            if( $validEmail ){
                
                $this->email->bcc($bcc);
            }

        }else{
            if(preg_match($regex, trim($bcc))) {

                $this->email->bcc($bcc);
            }
        }
    
        $this->email->to($to, $to_name);
        $this->email->from($from_email, $from_name);
        if( preg_match($regex, trim( $replay_to ))) {
            $this->email->reply_to($replay_to);
        }

        $this->email->subject($subject);
        $this->email->message($message);
        $smtp_status = $this->config->item('smtp_status');
    
        if (! empty($files)) {
            foreach($files as $file){
              $this->email->attach($file);
            }
        }
        
        $result = $this->email->send();

        if (! empty($files)) {
            foreach($files as $file){
            unlink($file);
            }
        }
        
        
        $to = dashboard_lang('_SENT') .' '. dashboard_lang('_TO') . ': '. $to ."<$to_name>";
        if($cc != '') $cc = dashboard_lang('_CC') . ': '. $cc;
        if($bcc != '') $bcc = dashboard_lang('_BCC') . ': '. $bcc;

        $this->Events_model->_email_message = $message; 
        $this->Events_model->_action = $to . $cc. $bcc; 
        $this->Events_model->executeEvent('email_sent', 'email sent');
        $this->Events_model->_email_message = ''; 

        activityEmail('', $from_email, $to, $cc, $bcc, $subject, $message);

        $this->email->clear(TRUE);

        return $result;
    }

    function get_from_mail_name()
    {
        $table_full_name = $this->_table_prefix . "settings";
        $this->db->select('value');
        $result = $this->db->get_where($table_full_name, array(
            'setting' => '#FROM_EMAIL_NAME'
        ))->result_array();
        
        if (sizeof($result) > 0) {
            
            return $result[0]['value'];
        } else {
            
            return '';
        }
    }

    function get_from_mail_address()
    {
        $this->load->config('messages');
        return $this->config->item('from_email_address');
    }
}