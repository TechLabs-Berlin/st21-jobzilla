<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class FileUploaderModel extends Base_Model
{

    var $_file_table = 'files';

    /*
     * constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('portal/Amazon_s3_model');
    }

    /*
     * upload a file
     */
    public function doUpload($filename, $fieldName, $fieldType = "", $id = 0, $allowed_file_type = 0, $subDirectory = '')
    {
        // initialize settings and arrays
        $returnArray = array(
            'status' => 0,
            'message' => dashboard_lang('_ERROR_ON_FILE_UPLOAD')
        );
        
        $img_upload_path = $this->config->item("img_upload_path");
        if (isset($subDirectory) and ! empty($subDirectory)) {
            $img_upload_path .= $subDirectory . "/";
        }
        $img_upload_max_size = $this->config->item("img_upload_max_size");
        $img_upload_max_width = $this->config->item("img_upload_max_width");
        $img_upload_max_height = $this->config->item("img_upload_max_height");
        $file_upload_max_size = $this->config->item("file_upload_max_size");
        if (strlen($allowed_file_type) != '0') {
            
            $img_file_allowed_types = $allowed_file_type;
        } else {
            $img_file_allowed_types = $this->config->item("img_file_allowed_types");
        }
        
        $file_allowed_types = $this->config->item('file_allowed_types');
        $uploadpath = $img_upload_path;
        
        $uploadpathWithFC = FCPATH . $img_upload_path;
        
        if (! is_dir($uploadpathWithFC)) {
            mkdir($uploadpathWithFC, 0777);
        }
        
        $config = array();
        $config['upload_path'] = $uploadpath;
        $config['allowed_types'] = '*';
        
        if ($fieldType == "image") {
            $config['max_size'] = $img_upload_max_size;
            $config['max_width'] = $img_upload_max_width;
            $config['max_height'] = $img_upload_max_height;
            $config['allowed_types'] = $img_file_allowed_types;
        }
        if ($fieldType == "file") {
            $config['max_size'] = $file_upload_max_size;
            $config['allowed_types'] = $file_allowed_types;
        }
        
        $config['overwrite'] = FALSE;
        // set the maximum filename increment
        $config['max_filename_increment'] = $this->config->item("max_filename_increment");
        $config['file_name'] = $filename;
        
        $this->load->library('upload');
        $this->upload->initialize($config);
        
        if (! $this->upload->do_upload($fieldName)) {
            
            $returnArray['message'] = $this->upload->display_errors();
        } else {
            $dataUpload = array(
                'upload_data' => $this->upload->data()
            );
            
            if ($fieldType == "image") {
                $thumbImageSizeHeight = $this->config->item("img_thumbnail_size_height");
                $thumbImageSizeWidth = $this->config->item("img_thumbnail_size_width");
                
                /*
                 * check if thumb exists
                 * if not then create thumb folder
                 */
                $thumb_path = $uploadpathWithFC . 'thumbs';
                if (! is_dir($thumb_path)) {
                    mkdir($thumb_path, 0777);
                }
                
                $config_thumb = array(
                    'image_library' => 'gd2',
                    'source_image' => $dataUpload['upload_data']['full_path'],
                    'new_image' => $dataUpload['upload_data']['file_path'] . "thumbs/",
                    'maintain_ratio' => TRUE,
                    'create_thumb' => TRUE,
                    'thumb_marker' => '',
                    'height' => $thumbImageSizeHeight,
                    'width' => $thumbImageSizeWidth
                );
                $this->load->library('image_lib');
                $this->image_lib->initialize($config_thumb);
                
                if (! $this->image_lib->resize()) {
                    $controller_sub_folder = $this->config->item('controller_sub_folder');
                    $this->session->set_flashdata('flash_message', $this->image_lib->display_errors());
                    
                    redirect(base_url() . $controller_sub_folder . '/' . $this->current_class_name . '/' . $this->config->item('edit_view') . '/' . ($id ? $id : ''));
                    exit();
                } else {
                    $this->image_lib->clear();
                }
            }
            
            if (! empty($dataUpload['upload_data']['file_name'])) {
                $filename = $dataUpload['upload_data']['file_name'];
                $returnArray['status'] = 1;
                $returnArray['message'] = $filename;
            }
        }
        
        return $returnArray;
    }

    /*
     * insert uploaded information into files
     * table
     */
    public function updateFileDb($id = 0, $filename = "", $description = "")
    {
        $user = BUserHelper::get_instance();
        $return = 0;
        
        if (! empty($user->user->id)) {
            if ($id > 0) {
                $updateArray = array(
                    'description' => $description,
                    'upload_time' => time(),
                    'uploaded_by' => $user->user->id
                );
                if (! empty($filename)) {
                    $updateArray['filename'] = $filename;
                }
                $this->db->update($this->_file_table, $updateArray, array(
                    'id' => $id
                ));
                $return = $id;
            } else {
                
                $insertArray = array(
                    'filename' => $filename,
                    'description' => $description,
                    'upload_time' => time(),
                    'uploaded_by' => $user->user->id
                );
                $this->db->insert($this->_file_table, $insertArray);
                $return = $this->db->insert_id();
            }
        }
        return $return;
    }

    /*
     * delete files
     */
    public function deleteFile($id)
    {
        $this->db->where('id', $id);
        $result = $this->db->delete($this->_file_table);
        
        return $result;
    }

    public function doUploadFile ( $tempFile, $save_type, $pId, $file_name )
    {
	    // all special character will be removed from file

		$fileinfo = $this->getFileExtension( $file_name);
		
		$filename = $fileinfo['filename'].'.'.$fileinfo['extension'];
		
        $time =time();
        $fileName = "{$save_type}/{$pId}/{$time}/" . preg_replace( '/[^A-Za-z0-9\-.]/', '_', $filename );
	    $fieldName = '';
	    $data = $this->Amazon_s3_model->sendLocalFileToAWS ( $tempFile, $fileName, $fieldName );
	    
	    return $data;
    }

    function getFileExtension ( $url )
	{
	    $pieces            = explode('/', $url);
	    $file['basename']  = $pieces[count($pieces)-1];
	    $pieces            = explode('.', $file['basename']);
	    $file['extension'] = $pieces[count($pieces)-1];
	    $file['filename']  = str_replace('.'.$file['extension'], '', $file['basename']);
	
	    return $file;
	}

}

