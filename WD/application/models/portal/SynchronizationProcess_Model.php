<?php
/*
 * Author: Ashrafuzzaman Sujan
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

class SynchronizationProcess_Model extends CI_Model 
{
    public function updateSynchronizationStatus($total=0, $current=0, $deleteFile=false, $successMsg = "", $errorMsg =""){
        
        $fileDirectory = "tmp";
        $uniqueFileName = $this->session->userdata("uniqueFileName");
        if(empty($uniqueFileName)){
            $userId = get_user_id();
            $uniqueFileName = $userId . "-". time() . "-". $this->generateRandomString();
            $this->session->set_userdata("uniqueFileName", $uniqueFileName);
        }
    
        $filePath = FCPATH.$fileDirectory."/".$uniqueFileName.".json";
    
        if ( $deleteFile){
            if(file_exists($filePath)){
                unlink($filePath);
            }
            $this->session->set_userdata("uniqueFileName", "");
            return false;
        }
        
        $arr = array(
            'total'=>$total,
            'current'=>$current,
            'successMsg' =>$successMsg,
        );
        
        if($errorMsg){
            $arr['errorMsg'] = $errorMsg;
        }            
    
        if (file_exists($filePath)) {
            $str = file_get_contents($filePath);
            $jsonArray = json_decode($str, true);
            $jsonArray[] = $arr;
            if(! write_file($filePath, json_encode($jsonArray))){
                $status = FALSE;
            } else{
                $status = TRUE;
            }
        } else {
            $jsonArray[] = $arr;
            if (! write_file($filePath, json_encode($jsonArray))) {
                $status = FALSE;
            }else{
                chmod($filePath, 0777);
                $status = TRUE;
            }
        }        
    
        return $status;
    }

    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}