<?php
/*
 * @author boo2mark, Ashrafuzzaman Sujan
 *
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

require "vendor/autoload.php";
use Aws\Lambda\LambdaClient;
Class Authy{

    var $authy_api ;
    var $CI ;
    

    public function __construct(){
        $this->CI = & get_instance();
        $this->CI->config->load('social');
        $this->authy_api = new Authy\AuthyApi( $this->CI->config->item('authy_api_key') );
    }
    public function createNewAuthyUser($email, $phone, $country_code , $project='portal', $env='dev'){
        $response = array(
            'status'=>200
        );
        

        $user = $this->authy_api->registerUser($email, $phone, $country_code); // email, cellphone, country_code
        // var_dump($user);
        if($user->ok()) {
            $response['user_id'] = $user->id();
            
        } else {
            $error = ''; 
            foreach($user->errors() as $field => $message) {
                $error .= "$field = $message\n" ;
            }
            $response['status'] = '500'; 
            $response['message'] = $error ; 
        }

        return ($response);
    }

    public function verify_token($authy_id, $token_entered_by_user){   

        /**
         * Old code
         */

        
        $verification = $this->authy_api->verifyToken($authy_id, $token_entered_by_user);
        // var_dump($verification); die();
        if ($verification->ok()) {
            return true  ; 
        }else{
            return false ; 
        }

        /**
         * New code with lambda invoke
         */

        // $lambdaData = array('authy_user_id'=> $authy_id, 'user_provided_token' => $token_entered_by_user);
        // $synchronousResponse = $this->_lambdaClient->invoke(array(
        //     'FunctionName' => $this->CI->config->item('authy_token_verification'),
        //     'Payload' => json_encode($lambdaData)
        // ));

        // $outputs = json_decode((string) $synchronousResponse->get('Payload'));
        // // var_dump($outputs);
        // if($outputs->statusCode == 200){
        //     // echo $outputs->body;
        //     return true; 
        // } else{
        //     return false; 
        // }

    }

    public function generateQR($authy_id, $email ){
        $s = curl_init();
        curl_setopt($s, CURLOPT_URL, "https://api.authy.com/protected/json/users/{$authy_id}/secret");
        curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($s, CURLOPT_POST, 1);
        curl_setopt($s, CURLOPT_HTTPHEADER, array(
            'X-Authy-API-Key: '.$this->CI->config->item('authy_api_key')            
        ));

        curl_setopt($s, CURLOPT_POSTFIELDS, http_build_query([
            'label' => $this->CI->config->item('#APPLICATION_NAME') . "($email)",          
            'qr_size' => '300'
        ]));
        curl_setopt($s, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($s);
        curl_close($s);
        return $response ; 
    }
}