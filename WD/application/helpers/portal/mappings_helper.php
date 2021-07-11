<?php
/*
 * @author: Ashrafuzzaman Sujan
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class mappings_helper
{  
    public static function getFieldsFromDB($mapId){
        $CI = & get_instance();
        $returnArr = array();
        $CI->db->select("field_name");
        $CI->db->where('mappings_id',$mapId);
        $query = $CI->db->get('mapping_fields');
        if($query){
            $dataArr =  $query->result_array();
            foreach ($dataArr as $row){
                $returnArr[] = $row['field_name'];
            }
        } 
        
        return $returnArr;
    }
    
    public static function deleteRecords($fields, $mapId){
        
        $CI = & get_instance();        
        $xmlFields = array();
        $deleteItemIds = array();
        
        foreach ($fields as $field){
            if ((string) $field['primary'] != true) {
                $xmlFields[] = (string) $field['name'];
            }
        }
        
        $CI->db->select("id, field_name");
        $CI->db->where('mappings_id',$mapId);
        $query = $CI->db->get('mapping_fields');
        if($query){
            $dataRows = $query->result();
            foreach ($dataRows as $row){
                if(!in_array($row->field_name, $xmlFields)){
                    $deleteItemIds[] = $row->id;
                }
            }
        }
        
        foreach ($deleteItemIds as $id){
            $CI->db->where('id',$id);
            $CI->db->delete('mapping_fields');
        }
        
    }
    
    public static function getConversionValues($id=0){
        if(!empty($id)){
            $CI = & get_instance();
            $CI->db->select("*");
            $CI->db->where('mapping_field_id',$id);
            $CI->db->where('is_deleted', 0);
            return $CI->db->get('conversion_values')->result_array();
        }
    }
    
    /*  
     *This function will be able to convert string to decimal(for db) value
     */
    
    public static function stringToDecimal($Str){

        $strLen = strlen($Str);
        if($strLen){
            $decimalSeparatorPos = $strLen - 3;
            $decimalSeparator = '';
            if($decimalSeparatorPos > 0){
                $decimalSeparator = $Str[$decimalSeparatorPos];
                if($decimalSeparator != '.' && $decimalSeparator != ','){
                    $decimalSeparator = $Str[$decimalSeparatorPos + 1];
                }
                
            }            
            if($decimalSeparator === '.'){
                return str_replace(',', '', $Str);
            } elseif ($decimalSeparator === ','){
                $modifiedStr = str_replace('.', '', $Str);
                $modifiedStr = str_replace(',', '.', $modifiedStr);
                return $modifiedStr;
            } else{
                $modifiedStr = str_replace('.', '', $Str);
                $modifiedStr = str_replace(',', '', $modifiedStr);
                return $modifiedStr;
            }
        } else{
            return 0;
        }
    }
    
    public static function roundingInt($Str) {
        
        $strLen = strlen($Str);
        
        if ($strLen) {
            $floatingValue = str_replace(',', '.', $Str);
            $floatingValue = floatval($floatingValue);
            return round($floatingValue);
        } else {
            return 0;
        }
        
    }
}