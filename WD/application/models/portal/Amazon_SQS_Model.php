<?php
/*
 * @author Ashrafuzzaman Sujan
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
require "vendor/autoload.php";
use Aws\Sqs\SqsClient; 
use Aws\Exception\AwsException;
class Amazon_SQS_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->config("amazon_sqs");
    }  

    public function pushDataToSqsForEmailNotification(){
        $response = array("status" => 0, "message" => dashboard_lang("_DATA_PUSH_OPERATION_INTO_SQS_FAILED"));
        $sqsClient = SqsClient::factory(array(
            'credentials' => array(
                'key' => $this->config->item('sqs_access_key'),
                'secret' => $this->config->item('sqs_secret_key')
            ),
            'version' => $this->config->item('sqs_version'),
            'region' => $this->config->item('sqs_region')
        ));

        $params = [
            'DelaySeconds' => 10,
            'MessageAttributes' => [
                "apiURL" => [
                    'DataType' => "String",
                    'StringValue' => $this->config->item('email_notification_api_url')
                ]
            ],
            'MessageBody' => "Push data to SQS",
            'QueueUrl' => $this->config->item('email_notification_sqs_url')
        ];
        
        try {

            $apiUrl = $params['MessageAttributes']['apiURL']['StringValue']; 

            if (strpos($apiUrl, 'localhost') === false) {
                            
                $result = $sqsClient->sendMessage($params);
                $response['status'] = 1;
                $response['message'] = dashboard_lang("_DATA_PUSH_OPERATION_INTO_SQS_SUCCESS");
            }
            
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }

        return $response;
    }
}