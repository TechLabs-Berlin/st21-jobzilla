<?php

/*
 * @author Atiqur Rahman
 */
class Version_checker_model extends CI_Model
{

    public $_errors = array();

    public $_errorFieldTypes = array();

    public $_liveDatabase;
    
    public function check_version_matched ( $host ) {

        $base_url = "http://".$host."/";
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $base_url."portal/version/application");
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $output = curl_exec($ch);
    
        curl_close($ch);
    
        return $output;
         
    
    }

    public function get_version( $file_name, $type ) {
    
        $xml_path = FCPATH . "application/core/$file_name.xml";
        $xml_data = simplexml_load_file($xml_path);
        $version  = (float) $xml_data->version[0];
         
        if ( $type == 'echo' ) {
            echo $version;
        } else {
            return $version;
        }
    
    
    }
}