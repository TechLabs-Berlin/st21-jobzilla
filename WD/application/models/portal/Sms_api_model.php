<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
require 'vendor/autoload.php';

class Sms_api_model extends CI_Model
{

    private $_table_name = "user_phone_verification";

    function __construct()
    {
        parent::__construct();
        $this->load->config('phone_verification');
        $this->load->model('portal/Verify_user_model');
        $this->load->model('portal/Utility_model');
        $this->load->helper('portal/utility');
    }

    function send_code($user_id, $code)
    {
        $send_code = $this->config->item('#2FA_SEND_CODE');
        
        $this->db->select("first_name,last_name,email,mobile_number");
        $result = $this->db->get_where('dashboard_login', array(
            "id" => $user_id,
            "is_deleted" => 0
        ))->result_array();
        
        $user_details = array();
        
        if (sizeof($result) > '0') {
            $user_details = $result[0];
        } else {
            $user_details['mobile_number'] = '';
        }
        
        if ($send_code == 'sms') {
            
            return $this->send_sms($user_details['mobile_number'], $code);
        } else {
            
            return $this->send_email($user_details, $code);
        }
    }

    function send_email($user_details, $code)
    {
        $data = array();
        
        $to = $user_details['email'];
        $to_name = $user_details['first_name'] . " " . $user_details['last_name'];
        
        $data['app_name'] = $this->Utility_model->get_mail_sender_name();
        $data['logo'] = $this->Utility_model->get_application_logo();
        $data['user_details'] = $user_details;
        $data['code'] = $code;
        
        $subject = dashboard_lang('_VERIFICATION_CODE');
        $template = $this->config->item('template_name');

        $body = $this->load->view($template.'/dashboard_2fa_email/2fa_email_content', $data, true);
        $cc = '';
        return send_email($to, $to_name, $cc, $subject, $body);
    }

    function send_sms($mobile_number, $code)
    {
        // return true;
        $this->load->model('portal/Events_Model');

        try {
            
            if (strlen($mobile_number) < '8') {
                
                $this->Verify_user_model->unset_email_logged_sess_data();
                $this->session->set_flashdata('failed_attempt_alert', dashboard_lang('_YOU_MOBILE_NO_IS_NOT_VALID'));
                redirect(site_url($this->config->item('login_url')));
            } else {
                
                $params = array(
                    'credentials' => array(
                        'key' => $this->config->item('awsKey'),
                        'secret' => $this->config->item('awsSecret')
                    ),
                    'region' => $this->config->item('awsRegion'), // < your aws from SNS Topic region
                    'version' => 'latest'
                );
                $sns = new \Aws\Sns\SnsClient($params);
                
                $args = array(
                    'MessageAttributes' => [
                        'AWS.SNS.SMS.SenderID' => [
                            'DataType' => 'String',
                            'StringValue' => $this->config->item("#SMS_SENDER_ID")
                        ]
                    ],
                    "SMSType" => "Transactional",
                    "Message" => dashboard_lang('_YOUR_VERFICATION_CODE_IS')." : ".$code,
                    "PhoneNumber" => $mobile_number
                );
                
                $result = $sns->publish($args);
                
                $statusCode = $result['@metadata']['statusCode'];
                $msgId = $result['MessageId'];
                


                // $this->add_log(json_encode($result), $code, $mobile_number);
                if ($statusCode == '200' && strlen($msgId) > '1') {
                   
                    /*      add log for sms sent    */
                    $log_message= "PhoneNumber:" . $mobile_number . ' Message:'. dashboard_lang('_YOUR_VERFICATION_CODE_IS')." : ".$code;
                    $this->Events_model->_sms_message = $log_message; 
                    $this->Events_model->executeEvent('sms_sent', 'sms sent');
                    $this->Events_model->_sms_message = ''; 

                    return true;

                } else {
                    return false;
                }
            }



        } catch (Exception $e) {
            
            $this->Verify_user_model->unset_email_logged_sess_data();
            $this->session->set_flashdata('failed_attempt_alert', $e->getMessage());
            redirect(site_url($this->config->item('login_url')));
        }
    }

    public function add_log($json_result, $verification_code, $mobile_number_to_be_sent)
    {
        $content = "Full Result: " . $json_result . "\nVerification Code: " . $verification_code . "\nMobile Number: " . $mobile_number_to_be_sent . "\n\n";
        $fp = fopen(FCPATH . "uploads/send_sms_" . date('Y-m-d-H-i-s') . ".txt", "a+");
        fwrite($fp, $content);
        fclose($fp);
    }

    public function add_event_log($value='')
    {
        $this->load->model('');
    }
}