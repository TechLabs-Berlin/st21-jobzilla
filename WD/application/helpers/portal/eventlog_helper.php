<?php
/*
 * @author: Sujan
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class eventLog_helper
{      
    /*
     * Parameters: File Name,Directory name, Module name, Status name, Message
     * Returns: true/false 
     * Return type:	bool
     */    
     
    public static function setLog($fileName, $directoryName, $module, $statusName, $message){
        
        /*  
         * create logfile if not exists
         * build the line
         * append the line in log file
         */
        
        $status = TRUE;
        $fileName .= "-" . date("d-m-Y", time()) . ".txt";
        $filePath = FCPATH.$directoryName."/".$fileName; 
        //echo $filePath;
        $dateTime = date("y m d H:i:s", time());
        $data = "[$dateTime]    ".$module.':'.$statusName."    ".$message."\n";
        if(file_exists($filePath)){
            if(!file_put_contents($filePath, $data, FILE_APPEND)){
                $status = FALSE;
            }
            
        }else{
            if(!write_file($filePath, $data)){
                $status = FALSE;
            }
        } 
        
        return $status;
        
    } 
       
}