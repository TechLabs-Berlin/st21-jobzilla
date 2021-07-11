<?php
/*
 * @author Ashrafuzzaman Sujan
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Import_mapping_model extends CI_Model
{
    
    public function upload_file()
    {
        
        $config['upload_path']          = './tmp/';
        $config['allowed_types']        = 'xlsx|xls|csv';
        $config['max_size']             = 1000000000;
        $config['overwrite']            = true;
    
        $this->load->library('upload', $config);
    
        if ( ! $this->upload->do_upload('import_file'))
        {
            $msg =  $this->upload->display_errors();
            $success = 0;
            $target_file = '';
            $file_name = '';
        }
        else
        {
            $data = $this->upload->data();
            $success = 1;
            $target_file = $data['full_path'];
            $file_name = $data['file_name'];
            $msg = '';
        }
    
    

        echo json_encode( array("success" => $success, "msg" => $msg, 'file_name' => $file_name, "file_path" => $target_file ) );
    }
    
    
    public function delete_file () {
        
         $file_path = $this->input->post('file_path');
         
         $delete_file = unlink($file_path);
         
         if ( file_exists($file_path) ) {
             
              $success = 0;
              $msg = dashboard_lang('_SOMETHING_WENT_WRONG_FILE_NOT_DELETED');
         }else {
              $success = 1;
              $msg = dashboard_lang('_FILE_DELETED');
         }
         
          echo json_encode( array("success" => $success, "msg" => $msg ) );
        
    }
    
    
    public function import_file(){
        
        $mapping_id = $this->input->post('mapping_id');
        $file = $this->input->post('file_path');
        
        $response = $this->mappings_model->importSheet($mapping_id, $file);
        $response['msg'] = dashboard_lang("_TOTAL_ROWS").' '.$response['total_row'].", ".dashboard_lang("_TOTAL_PROCESSED_ROWS")." ".$response['total_updated_row']. ".";
    
        echo json_encode( $response );
    }

    public function get_mappings_sample_file(){
        $fileHtml = "";
        $response = array('status' => 0);
        $mapping_id = $this->input->post('mapping_id');        
        $this->db->where("id", $mapping_id);
        $result = $this->db->get("mappings")->row_array();
        if(is_array($result)){
            $file_name = $result['import_file'];
            $filePath = FCPATH."tmp/".$file_name;
            // var_dump($filePath);
            if(file_exists($filePath)){
                $filePath = base_url()."tmp/".$file_name;
                $fileHtml =  "<span>".dashboard_lang("_UPLOADED_FILE").": </span>";
                $fileHtml .= "<a href='{$filePath}'>{$file_name}</a>";
                $response['status'] = 1;
            }
            
        } 
        $response['html'] = $fileHtml;   
        echo json_encode( $response );
    }
    
  
}
