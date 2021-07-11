<?php
/*
 * @author boo2mark
 *
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class SqlHelper
{

    public static function delete($table_name, $primary_field, $row_number, $hardDelete)
    {
        $CI = & get_instance();
        $return_status = array(
            'deleted' => FALSE
        );
        $foreign_key_status = FALSE;
        $lock_status = FALSE;
        $user_helper = BUserHelper::get_instance();
        
        // check foreign key status
        
        $CI->db->select('primary_table_field, reference_table, reference_table_field');
        $CI->db->where('primary_table', $table_name);
        
        $data_array = $CI->db->get('relational_tables')->result_array();
        foreach ($data_array as $row) {
            
            $primary_table_field = $row['primary_table_field'];
            
            $CI->db->select($table_name . "." . $primary_table_field);
            $CI->db->where($table_name . '.id', $row_number);
            $row_data = $CI->db->get($table_name)->row();
            $primary_field_value = $row_data->$primary_table_field;
            
            $CI->db->select('b.id')
                ->from($table_name . ' AS a')
                ->where("a." . $primary_table_field, $primary_field_value)
                ->where("b.is_deleted", 0)
                ->where('b.account_id', $user_helper->user->account_id)
                ->join($row['reference_table'] . ' AS b', 'a.' . $primary_table_field . ' = ' . 'b.' . $row['reference_table_field']);
            
            $rows = $CI->db->get()->num_rows();
            
            if ($rows > 0) {
                $foreign_key_status = TRUE;
                $return_status['linkedTable'][] = dashboard_lang('_' . strtoupper($row['reference_table']));
            }
        }
        
        // check record lock status
        
        $CI->db->select('user_id');
        $CI->db->where('table_name =', $table_name);
        $CI->db->where('row_id', $row_number);
        $CI->db->where('user_id !=', get_user_id());
        $CI->db->where('time_to_expire >', time());
        $query = $CI->db->get('lock_tables');
        
        if ($query->num_rows() > 0) {
            $lock_status = TRUE;
        }
        
        if (! ($foreign_key_status or $lock_status)) {
            
            if (isset($hardDelete) and $hardDelete == 1) {
                
                // delete record relational messages
                SqlHelper::deleteMessages($table_name, $row_number);
                
                $CI->db->where($primary_field, $row_number);
                $return_status['deleted'] = $CI->db->delete($table_name);
            } else {
                // soft delete record relational messages
                SqlHelper::softDeleteMessages($table_name, $row_number);
                // soft delete query start here
                $CI->db->where($primary_field, $row_number);
                $return_status['deleted'] = $CI->db->update($table_name, array(
                    'is_deleted' => 1
                ));
                // get the error message the query has failed
                if($return_status['deleted'] == false){
                    $return_status['error_message'] =  $error = $CI->db->error()['message'];
                }                
            }
        }
        
        return $return_status;
    }
    
    // trash record's operation
    public static function trash($table_name, $primary_field, $row_number, $un_trash, $fileFieldsList)
    {
        $CI = & get_instance();
        $return_status = FALSE;
        
        if (isset($un_trash) and $un_trash == 0) {
            //delete all files
            self::deleteFiles($table_name, $primary_field, $row_number, $fileFieldsList);
            $CI->db->where($primary_field, $row_number);
            $return_status = $CI->db->delete($table_name);
        } elseif (isset($un_trash) and $un_trash == 1) {
            
            $CI->db->where($primary_field, $row_number);
            $return_status = $CI->db->update($table_name, array(
                'is_deleted' => 0
            ));
        }
        
        return $return_status;
    }

    public static function deleteMessages($tableName, $id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        $result = $CI->Messages_model->haveMessage($tableName, $id);
        if ($result['status']) {
            foreach ($result['record_ids'] as $row) {
                $CI->Messages_model->deleteRelationalData($row);
            }
        }
    }

    public static function softDeleteMessages($tableName, $id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        $result = $CI->Messages_model->haveMessage($tableName, $id);
        if ($result['status']) {
            foreach ($result['record_ids'] as $row) {
                $CI->Messages_model->softDeleteRelationalData($row);
            }
        }
    }
    
    public static function deleteFiles($table_name, $primary_field, $row_number, $fileFieldsList){
        $CI = & get_instance();
        $status = false;
        $CI->load->model("portal/Amazon_s3_model");
        $rowData = getDataFromId($table_name, $row_number, $primary_field, false,true,'','',false);
        $rowData = @$rowData[0];
        foreach ($fileFieldsList as $field){
            $fieldName = $field['field_name'];
            $subDirectory = $field['sub_directory'];
            $source = $field['source'];
            if(!empty($rowData[$fieldName])){
                if($source == "s3"){
                    $returArray = $CI->Amazon_s3_model->deleteFileFromS3($table_name, $fieldName, $subDirectory, $row_number);
                    $status = $returArray['status'];
                } else{
                    $img_upload_path = $CI->config->item("img_upload_path");
                    $filePath = $rowData[$fieldName];
                    $filePathThumb = "thumbs/".$filePath;
                    if(!empty($subDirectory)){
                        $filePath = $subDirectory."/".$filePath;
                        $filePathThumb = $subDirectory."/".$filePathThumb;
                    }
                    $filePath = $img_upload_path."/".$filePath;
                    $filePathThumb = $img_upload_path."/".$filePathThumb;
                }
                if(file_exists($filePath)){
                    $status = unlink($filePath);
                    if($status && file_exists($filePathThumb)){
                        $status = unlink($filePathThumb);
                    }
                }                
                
            }
        }
        
        return $status;
    }

    public static function customDelete($table_name, $primary_field, $row_number, $hardDelete)
    {
        $CI = & get_instance();
        $return_status = array(
            'deleted' => FALSE
        );
        $foreign_key_status = FALSE;
        $lock_status = FALSE;
        $user_helper = BUserHelper::get_instance();
        
        // check foreign key status
        
        $CI->db->select('primary_table_field, reference_table, reference_table_field');
        $CI->db->where('primary_table', $table_name);
       
        
        $data_array = $CI->db->get('relational_tables')->result_array();

        foreach ($data_array as $row) {
            $primary_table_field = $row['primary_table_field'];
            if($row['reference_table'] == 'sales_orders'){
                $primary_table_field1 = 'sales_orders_id';
            }elseif($row['reference_table'] == 'purchase_orders'){
                $primary_table_field1 = 'purchase_orders_id';
            }
            elseif($row['reference_table'] == 'sales_invoices'){
                $primary_table_field1 = 'sales_invoices_id';
            }
            elseif($row['reference_table'] == 'requests'){
                $primary_table_field1 = 'requests_id';
            }
            elseif($row['reference_table'] == 'quotations'){
                $primary_table_field1 = 'quotations_id';
            }else{
                $primary_table_field1 = $primary_table_field;
            }
            
           
            
            $CI->db->select($table_name . "." . $primary_table_field);
            $CI->db->where($table_name . '.id', $row_number);
            $row_data = $CI->db->get($table_name)->row();
            $primary_field_value = $row_data->$primary_table_field;
           
            $CI->db->select('b.id')
                ->from($table_name . ' AS a')
                ->where("a." . $primary_table_field, $primary_field_value)
                ->where("b.is_deleted", 0)
                ->where('b.account_id', $user_helper->user->account_id)
                ->join($row['reference_table'] . ' AS b', 'a.' . $primary_table_field1 . ' = ' . 'b.' . $row['reference_table_field']);
            
            $rows = $CI->db->get()->num_rows();
            
            if ($rows > 0) {
                $foreign_key_status = TRUE;
                $return_status['linkedTable'][] = dashboard_lang('_' . strtoupper($row['reference_table']));
            }
        }
        

        // check record lock status
        
        $CI->db->select('user_id');
        $CI->db->where('table_name =', $table_name);
        $CI->db->where('row_id', $row_number);
        $CI->db->where('user_id !=', get_user_id());
        $CI->db->where('time_to_expire >', time());
        $query = $CI->db->get('lock_tables');
        
        if ($query->num_rows() > 0) {
            $lock_status = TRUE;
        }
        
        if (! ($foreign_key_status or $lock_status)) {
            
            if (isset($hardDelete) and $hardDelete == 1) {
                
                // delete record relational messages
                SqlHelper::deleteMessages($table_name, $row_number);
                
                $CI->db->where($primary_field, $row_number);
                $return_status['deleted'] = $CI->db->delete($table_name);
            } else {
                // soft delete record relational messages
                SqlHelper::softDeleteMessages($table_name, $row_number);
                // soft delete query start here
                $CI->db->where($primary_field, $row_number);
                $return_status['deleted'] = $CI->db->update($table_name, array(
                    'is_deleted' => 1
                ));
                // get the error message the query has failed
                if($return_status['deleted'] == false){
                    $return_status['error_message'] =  $error = $CI->db->error()['message'];
                }                
            }
        }
        
        return $return_status;
    }

}
