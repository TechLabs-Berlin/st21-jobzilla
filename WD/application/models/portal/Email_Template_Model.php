<?php
/*
 * @author Atiqur Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Email_Template_Model extends CI_Model
{

    public $_account_id;
    public $_messageTemplateTbl;
    public $_messageVariablesTbl;

    public function __construct()
    {
        parent::__construct();
        $this->_account_id = $this->session->userdata("account_id");
        $this->_messageTemplateTbl = "message_templates";
        $this->_messageVariablesTbl = "message_variables";
        $this->load->model("portal/Utility_model");
    }
    
    public function getMesssageTemplate( $templateType = '' ) {
        
        $result = $this->db->get_where ( $this->_messageTemplateTbl, [
            "template_type" => $templateType,
            "is_deleted" => 0,
            "account_id" => $this->_account_id 
        ])->result_array();
        
        if (sizeof($result) > 0 ) {
            
            $variables = $this->db->get_where ( $this->_messageVariablesTbl, [
                "is_deleted" => 0,
                "account_id" => $this->_account_id
            ])->result_array();
            
            $messageBody = $result[0]["documents_msg_body"];
            
            foreach ( $variables as $items ) {
                
                $value = $items["default_value"];
                $description = $items["description"];
                
                if ( strlen($description) > 0 ) {
                    $replaceValue = $description;
                }else {
                    $replaceValue = $value;
                }
                
                $messageBody = str_replace ( $items["variable_key"] , $replaceValue, $messageBody );
            }
            
            $template = $this->config->item('template_name');
            $data = [
                "msg_body" => $messageBody,
                'logo' => $this->Utility_model->get_application_logo(),
                'app_name' => $this->Utility_model->get_mail_sender_name(),
            ];
            
            $messageBody = $this->load->view ( $template."/core_metrov5_4/email/message-email-template", $data, TRUE );
            
            return ["subject" => $result[0]["templates_title"], "body" => $messageBody];
            
        }else {
            
            return ["subject" => "" , "body" => ""];
        }
    }

   
}