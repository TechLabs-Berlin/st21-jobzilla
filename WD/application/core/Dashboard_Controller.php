<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard_controller extends CommonController
{

    public $tablePrefix;

    public $tableName;
    public $_selectedIds;
    public $_xmldata;

    function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->library('session');
        $this->load->library('portal/template');
        $this->load->library('portal/messages');
        
        
        $this->load->helper('portal/boo2');
        $this->load->helper('portal/dashboard_main');
        $this->load->helper('portal/dashboard_menu');
        $this->load->helper('language');
        $this->load->helper('portal/messages');
        $this->load->helper('portal/sql');
        $this->load->helper('portal/utility');
        
        $this->load->model("portal/Table_permission_model");
        $this->load->model("portal/Permissions_model");
        
        $user = get_user_id();
        $this->_selectedIds = array();
        $user_role = get_user_role();
        
        if (! $user) {
            
            $requestUrl = uri_string();
            $isGetParameter =  $_SERVER['QUERY_STRING'];
            if($isGetParameter){
                $requestUrl = $requestUrl."?".$isGetParameter;
            }
            $this->CI = & get_instance();
            // check ignore url or not
            $ignoreAddress = $this->CI->config->item('ignore_address');
            
            // check if it's a URL with a file extension
            $hasFileExtension = empty( pathinfo( $requestUrl )['extension'] )? false: true;
            
            if ( strlen($requestUrl) > 0 and ! in_array($requestUrl, $ignoreAddress) and ! $hasFileExtension ) {
                $this->session->set_userdata('request_url', $requestUrl);
            }
            redirect(base_url() . $this->config->item('login_url'));
        }
        
        $this->load->model("../core/dashboard_model");
        $this->load->model("portal/System_model");
        $this->load->model("portal/Amazon_s3_model");
        
        // check if extra authorization was granted to app_owner or not, 
        // if granted, then, currently selected account id will be added in session
        if ( hasAppOwnerExtraAuthorization() ) $this->session->set_userdata("current_account_id", get_account_id());

        // template
        $this->template->set_template($this->config->item('template_name'));
        $this->tablePrefix = $this->config->item("prefix");
        $this->template_name = $this->config->item('template_name');
    }


    public function getAddPermission()
    {
         $tableName= $this->input->post('ref_table');
         $action= $this->input->post('action');
         $tableTranslation= strtoupper($tableName);
         $permission = $this->dashboard_model->get_user_add_delete_permissions($tableName, $action);
         if( $permission){
            if($permission['add_permission']== '1'){
                echo $permission['add_permission'];
            }else{
                echo dashboard_lang('_YOU_HAVE_NO_PERMISSION_TO_ADD_'.$tableTranslation);
            }
         }else{
            $permission['add_permission']= 1;
            echo $permission['add_permission'];

         }
    }


    public function load_only_edit($id = 0)
    {
        $this->session->set_userdata('chat_script', 1);
        
        $this->edit($id);
    }

    public function get_specific_field_name()
    {
        $this->dashboard_model->get_specific_field_name();
    }

    public function getFieldName(  $key, $tableName ) {
         
        $string = str_replace( $tableName."_", "", $key );
        $string = str_replace( "_operator", "", $string );
        $string = str_replace( "_498756425end", "", $string );
        
        return $string;
        
    }

    public function getOperatorValue ( $tableName, $fieldName ) {
        
        $operatorValue = $_POST[$tableName."_".$fieldName."_operator"];
        
        return $operatorValue;
    }

    protected function save_search_data_to_session()
    {
        $table_name = $this->input->post('table_name');
        $search = $this->input->post('search');
        
        if (isset($table_name) && strlen($table_name)) {
            
            $data = array();
            foreach ($_POST as $key => $value) {
                
                $fieldName = $this->getFieldName(  $key, $table_name );
                
                if (strlen($value) > 0) {
                    $has_table_name = strpos($key, $table_name);
                    $has_operator = strpos($key, "_operator");
                    
                    if ($has_table_name !== false) {
                        
                        if ( $has_operator === false ) {

                            $operatorValue = $this->getOperatorValue ( $table_name, $fieldName );    
                            if ( strlen($value) > 0 &&  strlen($operatorValue) > 0) {
                                
                                $parseData = explode(",", $value);
                                $data[$key] = $parseData[sizeof($parseData)-1];
                            }else {
                                $data[$key] = $value;
                            }
                            
                        }else {
                            $data[$key] = $value;
                        }
                        
                    }
                } else {
                    
                    $this->session->unset_userdata($key);
                }
            }
            $this->session->set_userdata($data);
        }
        
        if (strlen($search)) {
            foreach ($_POST as $key => $value) {
                $table_name = $this->tablePrefix . $this->uri->segment('2');
                $all_column_list = $this->dashboard_model->get_all_column_name($table_name);
                for ($count = 0; $count < sizeof($all_column_list); $count ++) {
                    
                    $column = $all_column_list[$count];
                }
            }
        }
    }


    public function get_template_id($table_name)
    {
        $user_id = $this->session->userdata('user_id');
        $result = $this->db->get_where("listing_fields", array(
            'table_name' => $table_name,
            "user_id" => $user_id,
            "is_deleted" => 0
        ))->result_array();
        
        if (sizeof($result) > 0) {
            
            return $result[0]['template_id'];
        } else {
            
            return 0;
        }
    }
    
    /*
     * perform listing
     */
    public function listing()
    {
   
        $showAllRecords = FALSE;
        $mainListing = TRUE;
        $this->output->enable_profiler(TRUE);
        // get listing data
        $data = $this->getListingData($showAllRecords, $mainListing);
        
        //get all listing data
        $data['all_listing_data'] = [];
        $xmlData = $this->xml_parsing();
        $totalColumnCount = @$xmlData['total_column_count'];
        if( $totalColumnCount ){

            $data['all_listing_data'] = $this->getListingData(TRUE, FALSE)['list'];
        }


        $theme_name = $this->config->item('template_name');
        $viewPath = $theme_name . '/' . $this->current_class_name . "/" . $this->config->item("listing_file_name");
        
        if (! $this->view_exists($viewPath)) {
            $viewPath = $this->getCoreViewPath('list');
        }
        
        $listing_field_ellipsis_length = intval($this->config->item("listing_field_ellipsis_length"));
        if ($listing_field_ellipsis_length == 0)
            $listing_field_ellipsis_length = 20;
        
        $data['listing_field_ellipsis_length'] = $listing_field_ellipsis_length;
        
        $current_user_role = get_user_role();
        
        $allowed_tables = get_user_viewable_tables($current_user_role);
        $not_allowed_tables = get_user_not_viewable_tables($current_user_role);
        
        if (sizeof($data['permission_array']) > 0) {
            foreach ($data['permission_array'] as $key => $value) {
                if (! in_array($key, $allowed_tables)) {
                    $allowed_tables[] = $key;
                }
            }
        }

        $this->session->set_userdata('allowed_tables', $allowed_tables);
        
        // $ses_data = $this->session->userdata('allowed_tables');
        $tbl_name = $this->tablePrefix . $this->uri->segment(2);

        if( $this->have_access_permission('listing', $not_allowed_tables) ){
            //get offset from url
            $offset = intval($this->uri->segment("4"));
            $total_items_count = @$data['total_items_count'];
            if(!empty($total_items_count) && $total_items_count <= $offset ){
                $controller_sub_folder = $this->config->item('controller_sub_folder');
                $listViewUrl = site_url() . '/' . $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("listing_view");
                redirect($listViewUrl);
            }    
            $this->template->write_view('content', $viewPath, $data);
            $this->template->render();
        }
        
    }
    
    /*
     * perform trash listing
     */
    public function trash()
    {
        $showAllRecords = FALSE;
        $mainListing = TRUE;
        $trash = TRUE;
        
        // get listing data
        $data = $this->getListingData($showAllRecords, $mainListing, $trash);
        
        $viewPath = $this->config->item('template_name') . "/core_" . $this->config->item('template_name') . "/trash/list";
        if (! $this->view_exists($viewPath)) {
            $viewPath = $this->getCoreViewPath('list');
        }
        
        $listing_field_ellipsis_length = intval($this->config->item("listing_field_ellipsis_length"));
        if ($listing_field_ellipsis_length == 0)
            $listing_field_ellipsis_length = 20;
        
        $data['listing_field_ellipsis_length'] = $listing_field_ellipsis_length;
        
        $current_user_role = get_user_role();
        
        $allowed_tables = get_user_viewable_tables($current_user_role);
        // Save data into a session array
        $this->session->set_userdata('allowed_tables', $allowed_tables);
        
        $ses_data = $this->session->userdata('allowed_tables');
        $tbl_name = $this->tablePrefix . $this->uri->segment(2);
        
        if ($ses_data) {
            if (in_array('*', $ses_data) || in_array($tbl_name, $ses_data)) {
                
                $this->template->write_view('content', $viewPath, $data);
                $this->template->render();
            } else {
                
                $url = base_url() . "dashboard/home";
                $error_msg['redirect_time'] = $redirect_time = $this->config->item('no_permission_listing_view_redirect');
                header("Refresh: $redirect_time; URL=$url");
                $error_msg['heading'] = dashboard_lang('_ACCESS_DENIED');
                $error_msg['message'] = dashboard_lang('_YOU_HAVE_NO_PERMISSION_TO_ACCESS_THIS_PAGE');
                
                $this->load->view('errors/html/error_403', $error_msg);
            }
        }
    }

    
    // public function export($fileType = "", $offset = 0, $ids = "" )
    public function export()
    {
        $mainListing = TRUE;

        $ids = $this->input->post('ids');
        $fileType = $this->input->post('type');
        $offset = $this->input->post('offset');

        if ($fileType == 'pdf') {
            $showAllRecords = FALSE;
        } else {
            $showAllRecords = TRUE;
        }
        if(!empty($ids)){
             //rest the offset
             $offset = 0;
             //make id array from string
             $selectedIds = explode("-", $ids);
             //push ids to member array
             $this->_selectedIds = $selectedIds;
         }
        
        // get listing data
        $data = $this->getListingData($showAllRecords, $mainListing, '', $offset, $fileType);
        $tableName = $data['table_name'];
        $totalItems = $data['list'];
        $xmlData = $data['xmlData'];
        $xmlObjectArray = $data['all_field'];
        $listingFields = $data['listing_field'];
        $primaryKey = $data['primary_key'];
        
        $totalItems = $this->update_cell_value($tableName, $xmlObjectArray, $listingFields, $totalItems);
        $tableName = dashboard_lang("_" . strtoupper($tableName));
        
        $this->exprot_table($tableName, $primaryKey, $xmlObjectArray, $totalItems, $fileType);
    }

    protected function update_cell_value($tableName, $xmlObjectArray, $listingFields, $totalItems)
    {
        $allSelectKey = array();
        $options = array();
        
        foreach ($listingFields as $fieldName) {
            
            $row = $xmlObjectArray[$fieldName];
            $fieldType = (string) $xmlObjectArray[$fieldName]['type'];
            if ($fieldType === "select") {
                $total_options = ($row->count());
                for ($i = 0; $i < $total_options; $i ++) {
                    
                    $attributes = $row->option[$i]->attributes();
                    $langkey_option = (string) $row->option[$i];
                    $options[(string) $row['name']][(string) $attributes['key']] = dashboard_lang($langkey_option);
                }
            }
        }
        
        // Get array of key for select fields only
        
        $allSelectKey = $options;
        
        // update process start here
        
        foreach ($totalItems as &$row) {
            
            foreach ($listingFields as $fieldName) {
                
                $fieldType = (string) $xmlObjectArray[$fieldName]['type'];
                
                switch ($fieldType) {
                    case $fieldType == 'select':
                        $select_value = (string) $row->$fieldName;
                        if (array_key_exists($select_value, $allSelectKey[$fieldName])) {
                            $row->$fieldName = $allSelectKey[$fieldName][$select_value];
                        }
                        break;
                    case $fieldType == 'eav':
                        
                        $eavRefTbl = (string) $xmlObjectArray[$fieldName]['ref_attribute_table_name'];
                        $attribute_table_index = 0;
                        
                        $tooltip = 1;
                        $attributes_table = $eavRefTbl;
                        $view_name = $tableName . "_" . $fieldName . "_eav_view";
                        $where_array = array(
                            $view_name . '.eav_object_id' => $row->id,
                            $view_name . '.is_deleted' => 0
                        );
                        
                        if (isset($attributes_table) && ! empty($attributes_table)) {
                            $where_array[$view_name . '.is_deleted'] = 0;
                        }
                        
                        $query = $this->db->get_where($view_name, $where_array);
                        
                        $text = '';
                        $count = $query->num_rows();
                        foreach ($query->result_array() as $data) {
                            $count --;
                            if (! empty($attributes_table)) {
                                
                                if ($tooltip == 1) {
                                    $text .= $data["eav_object_data"];
                                    if (isset($data["eav_object_attribute"])) {
                                        $text .= "-" . $data["eav_object_attribute"];
                                    }
                                } else {
                                    $text .= '<li class="eav_value">' . $data["eav_object_data"];
                                    $text .= (! empty($data["eav_object_attribute"])) ? "-" . $data["eav_object_attribute"] : '';
                                    $text .= '</li>';
                                }
                            } else {
                                if ($tooltip == 1) {
                                    $text .= $data["eav_object_data"];
                                } else {
                                    $text .= '<li class="eav_value">' . $data["eav_object_data"] . '</li>';
                                }
                            }
                            
                            if ($count > 0 && $tooltip == 1) {
                                $text .= ", ";
                            }
                        }
                        
                        $attribute_table_index ++;
                        
                        if (strlen($text) > 0) {
                            $row->$fieldName = $text;
                        }
                        
                        break;
                    case $fieldType == 'password':
                        $row->$fieldName = "##########";
                        break;
                    case $fieldType == 'single_checkbox':
                        if (! empty($row->$fieldName)) {
                            $row->$fieldName = dashboard_lang("_YES");
                        }else{
                            $row->$fieldName = dashboard_lang("_NO");
                        }
                        break;
                }
            }
        }
        
        return $totalItems;
    }
    
    /*
     * Table data export functionality
     */
    protected function exprot_table($table_name, $primaryKey, $xmlObjectArray, $rows, $exportFileType = "")
    {
        if ($exportFileType == 'pdf') {
            error_reporting(0);
            
            $this->load->library('portal/m_pdf');
            $pdf = $this->m_pdf->load();
            $viewArray = array(
                'tableName' => $table_name,
                'rows' => $rows,
                'xmlObjectArray' => $xmlObjectArray,
                'primaryKey' => $primaryKey
            );
            // generate the PDF!
            $html = $this->load->view($this->template_name . "/core_" . $this->template_name . "/export/pdf_layout", $viewArray, true);
            $headerData = $this->load->view($this->template_name . "/core_" . $this->template_name . "/export/pdf_header", array(
                'tableName' => $table_name
            ), true);
            
            $pdf->SetHTMLHeader($headerData);
            // $pdf->SetHTMLFooter($data);
            
            $pdf->AddPage('', // L - landscape, P - portrait
'', '', '', '', 15, // margin_left
15, // margin right
30, // margin top
15, // margin bottom
8, // margin header
0, // margin footer
'', '', '', '', '', '', '', '', '', 'A4-L');
            $pdf->WriteHTML($html);
            $pdf->Output($table_name . ".pdf", "D");
        } elseif ($exportFileType == 'excel') {
            
            require_once APPPATH . "/third_party/PHPExcel.php";
            error_reporting(E_ALL);
            $head = (array) $rows[0];
            $head = array_keys($head);
            $primaryFieldkey = array_search($primaryKey, $head);
            unset($head[$primaryFieldkey]);
            $allowedFieldsLists = [];
            foreach ( $head as $eachColumn ) {

                if ( strpos ( $eachColumn, "_fkid" ) === false ) {

                    $headArray[] = $eachColumn;
                    $allowedFieldsLists[] = $eachColumn;
                }
            }

            foreach ($headArray as $key => $item) {
                $prependTableName = (string) @$xmlObjectArray[$item]['prepend_table_name'];
                $is_translated = (string) @$xmlObjectArray[$item]['is_translated'];
                $translatedFieldName = '_' . strtoupper($item);
                if (! empty($prependTableName)) {
                    $translatedFieldName = '_' . strtoupper($prependTableName . '_' . $item);
                }
                if (! empty($is_translated)) { 
                    $translatedFieldName = strtoupper($item);
                }

                $headArray[$key] = dashboard_lang($translatedFieldName);
            }
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            
            $rowCount = 1;
            foreach ($headArray as $keyc => $column) {
                $col_name = $this->get_col_name($keyc);
                $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, $column);
            }
            $headArray = $allowedFieldsLists;
            $rowCount = 2;
            foreach ($rows as $key => $row) {
                $row = (array) $row;
                foreach ($headArray as $keyc => $column) {
                    $fieldType = $xmlObjectArray[$column]['type'];
                    $col_name = $this->get_col_name($keyc);
                    
                    $is_translated = $xmlObjectArray[$column]['is_translated'];
                     if (! empty($is_translated)) {
                        $row[$column] = dashboard_lang( strtoupper($row[$column]));
                    }
                    // set date formate
                    if ($fieldType == "datetime") {
                        if (! empty($row[$column])) {
                            // Get server time zone offset in seconds and add or subtract from main value
                            $timeZoneOffset = date('Z', $row[$column]);
                            $date = $row[$column] + $timeZoneOffset;
                            $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, PHPExcel_Shared_Date::PHPToExcel($date));
                            
                            $objPHPExcel->getActiveSheet()
                                ->getStyle($col_name . $rowCount)
                                ->getNumberFormat()
                                ->setFormatCode('dd mmm yyyy');
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, "");
                        }
                    } elseif ($fieldType == "money") {
                        if (strlen($row[$column]) > 0) {
                            $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, $row[$column]);
                            $defaultCurrency = $this->config->item("#DEFAULT_MONEY_FORMAT");
                            $objPHPExcel->getActiveSheet()
                                ->getStyle($col_name . $rowCount)
                                ->getNumberFormat()
                                ->setFormatCode('#,##0.00;[Red]#,##0.00');
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, '');
                        }
                    }elseif ($fieldType == "number") {
                        if (strlen($row[$column]) > 0) {
                            $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, $row[$column]);
                            $objPHPExcel->getActiveSheet()
                                ->getStyle($col_name . $rowCount)
                                ->getNumberFormat()
                                ->setFormatCode('#,##');
                        } else {
                            $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, '');
                        }
                    } elseif ($fieldType == "image") {
                        $value = $row[$column];
                        if (! empty($value)) {
                            $imageFolder = $this->config->item('img_upload_path');
                            $subDirectory = (string) $xmlObjectArray[$column]['sub_directory'];
                            if (isset($subDirectory) and ! empty($subDirectory)) {
                                $imageFolder .= $subDirectory . "/";
                            }
                            $filePath = $imageFolder . $value;
                            if ((stripos($value, "http://") == false) || (stripos($value, "https://") == false)) {
                                if ($value != "" && file_exists(FCPATH . $filePath)) {
                                    $value = CDN_URL . $filePath;
                                }
                            }
                        }
                        
                        $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, $value);
                    } elseif ($fieldType == "file") {
                        $value = $row[$column];
                        $imageFolder = $this->config->item('img_upload_path');
                        if (! empty($value)) {
                            $value = CDN_URL . $imageFolder . $value;
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, $value);
                    } elseif ($fieldType == "textarea") {
                        $value = $row[$column];
                        if((string)$xmlObjectArray[$column]['addon'] === "editor") {
                            $htmlWizard = new PHPExcel_Helper_HTML;
                            $value = $htmlWizard->toRichTextObject(mb_convert_encoding(html_entity_decode($row[$column]), 'HTML-ENTITIES', 'UTF-8'));
                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, $value);
                    } else {
                        //$objPHPExcel->getActiveSheet()->SetCellValue($col_name . $rowCount, $row[$column]);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit($col_name . $rowCount, $row[$column], PHPExcel_Cell_DataType::TYPE_STRING);
                    }
                }
                
                $rowCount ++;
            }
            $objPHPExcel->getActiveSheet()
                ->getDefaultColumnDimension()
                ->setWidth(15);
            
            ob_clean();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $table_name . '.xlsx"');
            header('Cache-Control: max-age=0');
            
            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->save('php://output');
            
            exit();
        }
    }
    
    /*
     * Get excel column name
     */
    protected function get_col_name($row_index)
    {
        $col_name = "";
        
        if ($row_index < 26) {
            $col_name = chr(65 + $row_index);
        } elseif ($row_index > 25 and $row_index < 52) {
            $col_name = chr(65) . chr(64 + ($row_index - 25));
        } elseif ($row_index > 51 and $row_index < 78) {
            $col_name = chr(66) . chr(64 + ($row_index - 51));
        } elseif ($row_index > 77 and $row_index < 104) {
            $col_name = chr(67) . chr(64 + ($row_index - 77));
        } elseif ($row_index > 103 and $row_index < 130) {
            $col_name = chr(68) . chr(64 + ($row_index - 103));
        } else {
            $col_name = "";
        }
        
        return $col_name;
    }
    
    /*
     * check whether view exists
     */
    protected function view_exists($view_name)
    {
        $file_path = APPPATH . 'views/' . $view_name . '.php';
        return file_exists($file_path);
    }
    
    /*
     * get path for the core view
     */
    protected function getCoreViewPath($type)
    {
        $core_view = $this->config->item('core_view');
        
        return $this->config->item('template_name') . '/' . $core_view[$type];
    }
    
    /*
     * order by query
     */
    protected function _getOrderBy($primary_key)
    {
        $tableName = $this->tablePrefix . $this->config->item("user_show_listing_field_table");
        
        $field_to_sort = $this->input->post('table_sort_field', '');
        
        $sorting_direction = $this->input->post('table_sort_direction', '');
        $return = array(
            'field',
            'direction'
        );
        
        $set_session = array();
        
        if (strlen($field_to_sort)) {
            
            
            
            $fieldToSortArray = explode(",", $field_to_sort);
            $fieldDirectionToSortArray = explode(",", $sorting_direction);

            
            
            $order_fields = array();
            
            if (strlen($field_to_sort) > 0) {
                
                foreach($fieldToSortArray as $key => $value){
                    $order_fields[$value] = $fieldDirectionToSortArray[$key];
                }
                // $order_fields[$field_to_sort] = $sorting_direction;
                 $order_fields = serialize($order_fields);
            } else {
                $order_fields = array();
            }
            
            $this->query_toAddOrUpdate($tableName, $order_fields);
        } else {
        
                $return['table_sort_field'] = $primary_key;
                $return['default_table_sort_field'] = $primary_key;
                $return['table_sort_direction'] = 'DESC';
        
         }
        return $return;
    }

    public function query_toAddOrUpdate($tableName, $order_fields)
    {
        $xmlData = $this->xml_parsing();
        $tableNameFixed = $this->tablePrefix . (string) $xmlData['name'];
        
        // set session data
        $data = array(
            'user_id' => get_user_id(),
            'table_name' => $tableNameFixed,
            'order_fields' => $order_fields
        );
        
        $this->db->select("user_id");
        $this->db->where("user_id", get_user_id());
        $this->db->where("table_name", $tableNameFixed);
        $query = $this->db->get($tableName);
        
        if ($query->num_rows() > 0) {
            $whereArray = array(
                "user_id" => get_user_id(),
                "table_name" => $tableNameFixed
            );
            
            $result_return = $this->dashboard_model->edit($tableName, $data, $whereArray);
        } else {
            $result_return = $this->dashboard_model->add($tableName, $data);
        }
    }
    
    /*
     * saves users choice for sorting any field
     */
    protected function save_userstate($field_name, $direction)
    {
        $this->session->set_userdata('userstate.' . $this->tableName . '.' . $field_name, $direction);
    }
    
    /*
     * retrives user's choice for any particular field
     */
    protected function get_userstate($field_name)
    {
        $saved_value = $this->session->userdata('userstate.' . $this->tableName . '.' . $field_name);
        if (strlen($saved_value)) {
            return $saved_value;
        } else {
            return $this->config->item('all_order_by_value');
        }
    }
    
    /*
     * perform add and edit action
     */
    public function edit($id = 0)
    {
        $superUserPermission = $this->dashboard_model->getSuperUserPermission();
        $dashboard_helper = Dashboard_main_helper::get_instance();
        
        if ($superUserPermission) {
            
            $dashboard_helper->set("super_user", 1);
            $super_user = 1;
        } else {
            
            $dashboard_helper->set("super_user", 0);
            $super_user = 0;
        }
        
        $controller_sub_folder = $this->config->item('controller_sub_folder');
        
        $this->load->helper('html');
        $this->load->helper('form');
        
        $xmlData = $this->xml_parsing();
        
        $tableName = $this->tablePrefix . $xmlData['name'];
        
        $editPath = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item('edit_view');
        // if this view is ajax based view
        $this->ajaxBasedView($id, $tableName, $editPath);

        // initialize data array
        $data = array();
        $data_edit = array();
        $position_data = array();
        // config values
        $viewOnlyActionText = $this->config->item("view_only_action_text");

        // xml data
        $tableNameFixed = $this->tablePrefix . (string) $xmlData["name"];
        $primary_key = (string) $xmlData['primary_key'];
        $created_on = (string) $xmlData['fieldname_created_on'];
        $modified_on = (string) $xmlData['fieldname_modified_on'];
        $save_update_history = (string) $xmlData["save_update_history"];
        $shared_status = intval($xmlData['shared_status']);
        $show_copy = intval($xmlData['show_copy']);
        
        if (isset($show_copy) and $show_copy) {
            $data['show_copy'] = $show_copy;
        }
        
        if (isset($shared_status) and $shared_status == 1) {
            $this->account_id = get_default_account_id();
        } else {
            $this->account_id = get_account_id();
        }                
        
        $post = $this->input->post();
        $action = $this->input->get("action");        

        // get permission arrays
        $userPermissionDataArray = $this->getUserPermissionDataArray($tableNameFixed);
        $tableFieldsPermission = $this->dashboard_model->get_table_permissions_field($this->current_class_name, $action);

        // get record position data
        if (isset($id) and $id > 0) {
            $position_data = $this->saveAndNext($id);
        }

        $requiredButNoValue = false;
        $formattedPostData = array();
        
        // check post data
        if (isset($post) && $post && ($action != $viewOnlyActionText)) {
            
            $saveAndClose = $this->input->post('saveAndClose');
            $saveAndNext = $this->input->post('saveAndNext');
            $activeTab = $this->input->post('activeTab');
            $record_id = $this->input->post('id');
            
            // record update status checker
            $update_status = 0;
            if (isset($record_id) and $record_id > 0) {
                $update_status = 1;
            }
            
            // delete record lock data
            $this->dashboard_model->deleteLockData($id, $tableName);
            
            // Call custom data formatting function            
            $formattedPostData = $this->formDataFormatting($xmlData, $tableFieldsPermission, $super_user, $requiredButNoValue, $id);
            
            if ( ! $requiredButNoValue ) {
            
                // perform operations before save
                $this->beforeSaveOperation($formattedPostData, $id);
            
                // Call savePostData function to save form posted data
            
                $return_data = $this->savePostData($formattedPostData, $id, $primary_key, $modified_on, $tableName, $controller_sub_folder, $created_on, $save_update_history);
            
                $id = $return_data['pkey_id'];
            
                $this->afterSaveOperation($tableName, $id, $update_status);
                
                $eav_data_list = $this->check_eav_data_exists();
                $this->dashboard_model->update_eav_table($eav_data_list, $tableName, $id);
                    
                $edit_path = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item('edit_view');
                if (isset($id) and $id > 0) {
                    
                    // save next functionality start here
                    if (isset($saveAndNext) and $saveAndNext) {
                        $next_record_id = @$position_data['next_record_id'];
                        
                        if (isset($next_record_id) and $next_record_id > 0) {
                            $id = $next_record_id;
                        } else {
                            $message_success = dashboard_lang("_LAST_ITEM_REACHED");
                            $this->session->set_flashdata('flash_message', $message_success);
                            $this->session->set_userdata('dashboard_application_message_type', 'error');
                        }
                    }
                    
                    $edit_path .= '/' . $id;
                    
                    if (isset($activeTab) and $activeTab > 0) {
                        $edit_path .= '/' . $activeTab;
                    }
                    
                    if (isset($saveAndClose) and $saveAndClose) {
                        
                        $default_per_page = 10;
                        $position = @$position_data['current_position'];
                        $per_page = $this->session->userdata('per_page@' . $this->current_class_name);
                        if (! isset($per_page)) {
                            $this->session->set_userdata('per_page@' . $this->current_class_name, $default_per_page);
                            $per_page = $default_per_page;
                        }
                        
                        $record_position = get_record_position_in_page($position, $per_page);
                        
                        $edit_path = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item('listing_view') . "/$record_position";
                    }
                }
                
                redirect(rtrim(site_url(), '/') . '/' . $edit_path);
            }
        }
        
        if ($id > 0) {
            
            $this->output->enable_profiler(TRUE);
            
            // check a record is available or not if not then send to listing view
            
            $this->recordIsAvailable($id, $tableName, $action);
            
            $this->db->select("*");
            $this->db->where($primary_key, $id);
            if (! isSuperUser()) {
                $this->db->where('account_id', $this->account_id);
            }
            // soft-delete
            $this->db->where($tableName . '.is_deleted !=', 1);
            $queryEditData = $this->db->get($tableName);
            
            if ($queryEditData->num_rows() > 0) {
                
                if ( $requiredButNoValue ) {
                        
                    $data_edit = $formattedPostData;
                            
                } else {

                    foreach ($queryEditData->result() as $row) {
                        foreach ($xmlData->field as $value) {
                            
                            if ((string) $value['name'] != 'eav') {
                                
                                $fieldName = (string) $value['name'];
                                $data_edit[$fieldName] = '';
                                if (isset($row->$fieldName) and $row->$fieldName != "") {
                                    $data_edit[$fieldName] = $row->$fieldName;
                                }
                            }
                        }
                    }
                }
                /*
                 * if save_update_history turned on
                 * then pass the created/modified data
                 * to view
                 */
                if ($save_update_history) {
                    
                    $data_edit['created_at'] = @$row->created_at;
                    $data_edit['modified_at'] = @$row->modified_at;
                    $data_edit['created_by'] = @$row->created_by;
                    $data_edit['modified_by'] = @$row->modified_by;
                }
            } else {
                $edit_path = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item('edit_view');
                redirect(rtrim(site_url(), '/') . '/' . $edit_path);
            }
            
            $data['data_edit'] = $data_edit;
            $data['position_data'] = $position_data;
            $data['id'] = $id;
        
        } else if ( $requiredButNoValue ) {

            $data['data_edit'] = $formattedPostData;

        }
        
        $viewPathAction = $this->current_class_name . "/" . $this->config->item("editing_action_file_name");
        
        if (! $this->view_exists($viewPathAction)) {
            $viewPathAction = $this->getCoreViewPath('edit_action');
        }
        
        $view_additional = $this->current_class_name . "/" . $this->config->item("edit_form_bottom");
        if ($this->view_exists($view_additional)) {
            $data['view_additional'] = $view_additional;
        }
        // hide all three save btn whene all fields have view permission
        $data['hide_save_btn'] = 0;
        if (sizeof($userPermissionDataArray) == 1 and (isset($userPermissionDataArray['*']) and $userPermissionDataArray['*'] == 1)) {
            $data['hide_save_btn'] = 1;
        } elseif($action == $viewOnlyActionText){
            $data['hide_save_btn'] = 1;
        }
        
        $data['viewPathAction'] = $viewPathAction;
        $data['edit_field_array'] = $userPermissionDataArray;
        $data['data_load'] = $xmlData;
        
        $data['form_name'] = $this->current_class_name;
        $data['class_name'] = $this->current_class_name;
        $data['delete_method'] = $this->config->item("delete_view");
        $data['copy_method'] = $this->config->item("copy_view");
        
        $data['edit_path'] = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("edit_view");
        
        $data['listing_path'] = $this->current_class_name . "/" . $this->config->item("listing_view");
        $data['delete_lock_table_path'] = $this->current_class_name . "/" . $this->config->item("lock_table_view") . "/" . $id;        
        $data['table_permissions_field'] = $tableFieldsPermission;
        $data['add_delete_permissions'] = $this->dashboard_model->get_user_add_delete_permissions($this->current_class_name, $action);
        $data['additional_data'] = $this->getAdditionalData($id);
        $check_user_add_delete_permission = $this->dashboard_model->check_user_add_delete_permission($id, $data['add_delete_permissions']);
        
        $viewPath = $this->template_name . '/' . $this->current_class_name . "/" . $this->config->item("edit_view");
        
        if (! $this->view_exists($viewPath)) {
            $viewPath = $this->getCoreViewPath('edit');
        }
        
        if ($this->have_access_permission('edit', '', $userPermissionDataArray, $id)) {
            
            $this->template->write_view('content', $viewPath, $data);
            $this->template->render();
        }
        
    }
    
    /*
     * perform add and view only
     */
    public function viewonly($id = 0)
    {
        $this->readonly_view('viewonly', $id);
    }
    
    /*
     * perform add and trash detail action
     */
    public function trash_detail($id)
    {
        $this->readonly_view('trash_detail', $id);
    }

    public function readonly_view($view_type, $id = 0)
    {
        $superUserPermission = $this->dashboard_model->getSuperUserPermission();
        $dashboard_helper = Dashboard_main_helper::get_instance();
        
        if ($superUserPermission) {
            
            $dashboard_helper->set("super_user", 1);
            $super_user = 1;
        } else {
            
            $dashboard_helper->set("super_user", 0);
            $super_user = 0;
        }
        
        $controller_sub_folder = $this->config->item('controller_sub_folder');
        
        $this->load->helper('html');
        $this->load->helper('form');
        
        $xmlData = $this->xml_parsing();
        
        $tableName = $this->tablePrefix . $xmlData['name'];
        
        $primary_key = (string) $xmlData['primary_key'];
        $created_on = (string) $xmlData['fieldname_created_on'];
        $modified_on = (string) $xmlData['fieldname_modified_on'];
        $save_update_history = (string) $xmlData["save_update_history"];
        
        /*
         * GET user permission data
         */
        $tableNameFixed = $this->tablePrefix . (string) $xmlData["name"];
        
        $userPermissionDataArray = $this->getUserPermissionDataArray($tableNameFixed);
        $userPermissionDataArray['*'] = 1;
        $data_edit = array();
        
        $position_data = array();
        
        // get record position data
        if (isset($id) and $id > 0) {
            $position_data = $this->saveAndNext($id);
        }
        
        if ($id > 0) {
            
            // check a record is available or not if not then send to listing view
            if ($view_type == 'trash_detail')
                $this->recordIsAvailable($id, $tableName);
            
            $this->db->select("*");
            $this->db->where($primary_key, $id);
            if (! isSuperUser()) {
                $this->db->where('account_id', $this->account_id);
            }
            // soft-delete
            if ($view_type == 'trash_detail') // $view_type = 'viewonly' 'trash_detail'
                $this->db->where($tableName . '.is_deleted =', 1);
            else
                $this->db->where($tableName . '.is_deleted =', 0);
            $queryEditData = $this->db->get($tableName);
            
            if ($queryEditData->num_rows() > 0) {
                
                foreach ($queryEditData->result() as $row) {
                    foreach ($xmlData->field as $value) {
                        
                        if ((string) $value['name'] != 'eav') {
                            
                            $fieldName = (string) $value['name'];
                            $data_edit[$fieldName] = '';
                            if (! empty($row->$fieldName)) {
                                $data_edit[$fieldName] = $row->$fieldName;
                            }
                        }
                    }
                }
            } else {
                $edit_path = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item('edit_view');
                redirect(rtrim(site_url(), '/') . '/' . $edit_path);
            }
            
            $data['data_edit'] = $data_edit;
            $data['position_data'] = $position_data;
            $data['id'] = $id;
        }
        
        $viewPathAction = $this->template_name . '/' . $this->current_class_name . "/" . $this->config->item("editing_action_file_name");
        if (! $this->view_exists($viewPathAction)) {
            $viewPathAction = $this->getCoreViewPath('edit_action');
        }
        
        $view_additional = $this->template_name . '/' . $this->current_class_name . "/" . $this->config->item("edit_form_bottom");
        if ($this->view_exists($view_additional)) {
            $data['view_additional'] = $view_additional;
        }
        
        $data['viewPathAction'] = $viewPathAction;
        $data['edit_field_array'] = $userPermissionDataArray;
        $data['data_load'] = $xmlData;
        
        $data['form_name'] = $this->current_class_name;
        $data['class_name'] = $this->current_class_name;
        $data['delete_method'] = $this->config->item("delete_view");
        $data['copy_method'] = $this->config->item("copy_view");
        
        
        $edit_path_view = 'trash_detail';
        
        $data['edit_path'] = $this->config->item('template_name') . '/' . $controller_sub_folder . '/' . $this->current_class_name . "/" . $edit_path_view;
        
        $listing_path_view = 'viewonly';
        if ($view_type == 'trash_detail') { 
            $listing_path_view = 'listing';
        }
        $data['listing_path'] = $this->current_class_name . "/" . $listing_path_view;
        
        $data['delete_lock_table_path'] = $this->current_class_name . "/" . $this->config->item("lock_table_view") . "/" . $id;
        $data['table_permissions_field'] = array(
            array(
                'field_name' => '*',
                'permission' => 1
            )
        );
        
        $data['add_delete_permissions'] = $this->dashboard_model->get_user_add_delete_permissions($this->current_class_name);
        $check_user_add_delete_permission = $this->dashboard_model->check_user_add_delete_permission($id, $data['add_delete_permissions']);
        
        // $view_type can be 'viewonly' 'trash_detail'
        
        if ($view_type == 'trash_detail') {
            $viewPath = $this->config->item('template_name') . "/core_" . $this->config->item('template_name') . "/trash" . "/" . $this->config->item("edit_view");
        } else {
            $viewPath = $this->config->item('template_name') . "/" . $this->config->item('core_view')["viewonly"];
        }
        
        $viewPath = $this->config->item('template_name') . "/core_" . $this->config->item('template_name') . "/trash" . "/" . $this->config->item("edit_view");
        
        if (! $this->view_exists($viewPath)) {
            $viewPath = $this->getCoreViewPath('edit');
        }
        $data['view_type'] = $view_type;
        $data['controller_sub_folder'] = $tableName;
        
        $ses_data = $this->session->userdata('allowed_tables');
        $tbl_name = $this->tablePrefix . $this->uri->segment(2);
        if ($ses_data) {
            
            if ((in_array('*', $ses_data) or in_array($tbl_name, $ses_data)) and $id > 0) {
                
                $this->template->write_view('content', $viewPath, $data);
                $this->template->render();
            } else {
                
                $error_msg['heading'] = dashboard_lang('_ACCESS_DENIED');
                $error_msg['message'] = dashboard_lang('_YOU_HAVE_NO_PERMISSION_TO_ACCESS_THIS_PAGE');
                $this->load->view('errors/html/error_403', $error_msg);
            }
        }
    }
    
    /*
     * function to make custom formatted data
     * return data array
     */
    protected function formDataFormatting($xmlData, $userPermissionDataArray, $super_user, &$requiredButNoValue, $id = 0)
    {
        $data = array();
        $fieldDefaultValue = array();
        
        foreach ($xmlData->field as $value) {
            
            /*
             * Exclude data if hidden_in_edit = 1
             */
            if ($value['hidden_in_edit'] == 1) {
                continue;
            }
            
            $fieldName = (string) $value['name'];
            $fieldType = (string) $value['type'];
            $fieldRequired = (string) $value['validation'];
            $subDirectory = (string) $value['sub_directory'];
            $source = (string) $value['source'];
            
            $this->fieldValidation( $fieldName, $fieldRequired, $requiredButNoValue );
            
            // $have_permission = $this->checkFieldPermission($fieldName, $userPermissionDataArray, $super_user);
            // apply new permission system
            $have_permission = B_form_helper::check_user_permission($userPermissionDataArray, $fieldName, true);

            
            //ignore hidden field 
            if(  $fieldType  == 'hidden' && $fieldName != 'id'){
                $have_permission = false;
            }
            
            $default_value = (string) $value['default_value'];
            if (substr($default_value, 0, 1) === "#") {
                $default_value = $this->config->item($default_value);
                if (! isset($default_value) || (isset($default_value) && $default_value == '')) {
                    $default_value = "";
                }
            } else {
                if ($fieldType == "datetime") {
                    $default_value = strtotime($default_value);
                }
            }
            
            $fieldDefaultValue[$fieldName] = $default_value;
            
            if ((isset($fieldType) and (($fieldType == "image") || ($fieldType == "file"))) and $have_permission) {
                
                if (@$_FILES[$fieldName]) {
                    
                    $table_name = $this->tablePrefix . (string) $xmlData['name'];
                    $fileName = preg_replace("/[^a-zA-Z0-9.]+/", "", $_FILES[$fieldName]['name']);
                    
                    if (strlen($fileName) > 220) {
                        $fileName = substr($fileName, 0, 220);
                    }
                    
                    if ($_FILES[$fieldName]['name'] != "" && ($fieldType != "file")) {
                        $image_ext = explode('.', $_FILES[$fieldName]['name']);
                        $image_ext = end($image_ext);
                        
                        $imageExtension = strtolower($image_ext);
                        $allowed_settings = (string) $value['allowed_file_types'];
                        if (strlen($allowed_settings) > 0) {
                            $config_allows_file_type = explode("|", $allowed_settings);
                        } else {
                            $config_allows_file_type = explode("|", $this->config->item('img_file_allowed_types'));
                        }
                        
                        if (! in_array($imageExtension, $config_allows_file_type)) {
                            
                            $controller_sub_folder = $this->config->item('controller_sub_folder');
                            $this->session->set_flashdata('flash_message', dashboard_lang('_DADHBOARD_IMAGE_UPLOAD_TYPE_ERROR'));
                            $this->session->set_userdata("dashboard_application_message_type", "error");
                            redirect(base_url() . $controller_sub_folder . '/' . $this->current_class_name . '/' . $this->config->item('edit_view') . '/' . ($id ? $id : ''));
                            exit();
                        }
                    }
                    
                    if (strlen($_FILES[$fieldName]['name']) > 0) {
                        
                        if (isset($source) and $source == "s3") {
                            
                            if ( intval($id) == '0' ) {
                                $fileName = time() . "/" . $fileName;
                            }else {
                                $fileName = $id . "/" . $fileName;
                            }
                            
                            if (! empty($subDirectory)) {
                                $fileName = $subDirectory . "/" . $fileName;
                            }
                            $s3Array = $this->Amazon_s3_model->uploadFileToS3($table_name, $fileName, $fieldName);
                            if ($s3Array['status']) {
                                $data = array_merge($data, array(
                                    $fieldName => $s3Array['filePath']
                                ));
                                
                                if (! empty($id)) {
                                    $this->Amazon_s3_model->deleteFileFromS3($table_name, $fieldName, $subDirectory, $id);
                                }
                            } else {
                                $data = array_merge($data, array(
                                    $fieldName => ""
                                ));
                            }
                        } else {
                            $uploadedfileurl = $this->do_upload($table_name, $fileName, $fieldName, $fieldType, $id, (string) $value['allowed_file_types'], $subDirectory);
                            $data = array_merge($data, array(
                                $fieldName => $uploadedfileurl
                            ));
                        }
                    }
                    
                    $deleted_flag = $fieldName;
                    $flag_value = $this->input->post($deleted_flag);
                    if ($flag_value == 1) {
                        
                        if (isset($source) and $source == "s3") {
                            
                            $this->Amazon_s3_model->deleteFileFromS3($table_name, $fieldName, $subDirectory, $id);
                        }
                        $data = array_merge($data, array(
                            $fieldName => ''
                        ));
                    }
                }
            } elseif ((isset($fieldType) and ($fieldType == "money")) and $have_permission) {
                $money = $this->input->post($fieldName);
                $default_format_from_config = strtolower($this->config->item('#DEFAULT_MONEY_FORMAT'));
                
                if ( $default_format_from_config == 'us' ) {
                    if (isset($money) && strlen($money) > 0) {
                        $data[$fieldName] = str_replace(",", "", $money);
                    } else {
                        $data[$fieldName] = null;
                    }
                }else {
                    
                    if (isset($money) && strlen($money) > 0) {
                        $data[$fieldName] = str_replace(".", "", $money);
                        $data[$fieldName] = str_replace(",", ".",  $data[$fieldName] );
                        
                    } else {
                        $data[$fieldName] = null;
                    }
                }
                

            } elseif ((isset($fieldType) and ($fieldType == "messages")) and $have_permission) {
                
            } elseif ((isset($fieldType) and ($fieldType == "history")) and $have_permission) {

            } elseif ((isset($fieldType) and ($fieldType == "datetime")) and $have_permission) {
                
                $data = array_merge($data, array(
                    $fieldName => (trim($this->input->post($fieldName)) == '') ? null : strtotime(trim($this->input->post($fieldName)))
                ));
            } elseif ((isset($fieldType) and ($fieldType == "password")) and $have_permission) {
                
                $password = $this->input->post($fieldName);
                
                if (! empty($password)) {
                    
                    $data = array_merge($data, array(
                        $fieldName => (trim($this->input->post($fieldName)) == '') ? $fieldDefaultValue[$fieldName] : trim(md5($this->input->post($fieldName)))
                    ));
                }
            } elseif ((isset($fieldType) and ($fieldType == "checkbox")) and $have_permission) {
                
                $current_table = $this->tablePrefix . $this->current_class_name;
                
                $selectedCheckbox = $this->input->post($fieldName);
                $main_table = $this->tablePrefix . (string) $value['main_table'];
                $main_table_field = (string) $value['main_table_field'];
                $ref_table_field = (string) $value['ref_table_field'];
                
                // call checkbox data save/update function
                
                $this->dashboard_model->save_checkbox_data($current_table, $fieldName, $selectedCheckbox, $main_table, $main_table_field, $ref_table_field, $id);
            } elseif ($have_permission) {
                
                $field_data = $this->input->post($fieldName);
                
                if (gettype($field_data) != "array") {
                    
                    $data = array_merge($data, array(
                        $fieldName => (trim($field_data) == '') ? $fieldDefaultValue[$fieldName] : trim($field_data)
                    ));
                }
            }
        }
        
        return $data;
    }
    
    /*
     * Function for checking user field permissions
     */
    protected function checkFieldPermission($fieldName, $userPermissionDataArray, $super_user)
    {
        $permissionStatus = false;
        if ($super_user) {
            $userPermissionDataArray['*'] = 2;
        }
        
        if ((array_key_exists($fieldName, $userPermissionDataArray) and $userPermissionDataArray[$fieldName] == 2) or (! isset($userPermissionDataArray[$fieldName]) and (isset($userPermissionDataArray['*']) and $userPermissionDataArray['*'] == 2))) {
            
            $permissionStatus = true;
        } else {
            
            $permissionStatus = false;
        }
        
        return $permissionStatus;
    }
    
    /*
     * function to save post data
     * return primary key id and status 0/1
     */
    protected function savePostData($data, $id = 0, $primary_key, $modified_on, $tableName, $controller_sub_folder, $created_on, $save_update_history = '0')
    {
        $pkey_id = 0;
        $error = 0;
        
      
        if ($id > 0) {
            
            unset($data[$primary_key]);
            
            if (@$modified_on) {
                
                $data[$modified_on] = time();
            }
            
            if ($save_update_history == '1') {
                
                $data['modified_at'] = time();
                $data['modified_by'] = $this->session->userdata('user_id');
            }
            
            if (count($data)) {
                
                if (isset($this->account_id)) {
                    $data['account_id'] = $this->account_id;
                    $this->db->where('account_id', $this->account_id);
                }
                
                $this->db->where($primary_key, $id);
                $this->db->where('account_id', $this->account_id);
                
                $result = $this->db->update($tableName, $data);
                
                if ($result) {

                    $this->Events_model->executeTableEntryEvent('update_entry', $tableName, $data, $id);
                    
                    $message_success = dashboard_lang("_RECORD_EDITED_SUCCESSFULLY");
                    $this->session->set_flashdata('flash_message', $message_success);
                } else {
                    
                    $error = ($this->db->error());
                    $message_success = dashboard_lang("_ERROR_ON_RECORD_UPDATE") . ' ' . $error['message'];
                    $this->session->set_flashdata('flash_message', $message_success);
                    $this->session->set_userdata('dashboard_application_message_type', 'error');
                    $error = 1;
                }
            }
            
            $pkey_id = $id;
        } else {
            
            if ($save_update_history == '1') {
                
                $data['created_at'] = time();
                $data['modified_at'] = time();
                
                $data['created_by'] = $this->session->userdata('user_id');
                $data['modified_by'] = $this->session->userdata('user_id');
            }
            
            unset($data[$primary_key]);
            if (@$created_on) {
                
                $data[$created_on] = time();
                $data[$modified_on] = time();
            }
            
            if (count($data)) {
                
                if (isset($this->account_id)) {
                    $data['account_id'] = $this->account_id;
                }
                
                if ($tableName == $this->tableName . 'dashboard_login') {
                    $default_lang = $this->config->item("#LANGUAGE_DEFAULT");
                    if (! empty($default_lang)) {
                        $data['language'] = $default_lang;
                    } else {
                        $data['language'] = "english";
                    }
                }
                
                $result = $this->db->insert($tableName, $data);
                
                if ($result) {
                    
                    $id = $this->db->insert_id();
                    
                    $this->Events_model->executeTableEntryEvent('add_entry', $tableName, $data, $id);
                    
                    $message_success = dashboard_lang("_RECORD_INSERTED_SUCCESSFULLY");
                    $this->session->set_flashdata('flash_message', $message_success);
                    
                    if (isset($data['eav'])) {
                        $this->db->update($tableName, array(
                            'eav' => $id
                        ), array(
                            $primary_key => $id
                        ));
                    }
                } else {
                    
                    $error = ($this->db->error());
                    $message_success = dashboard_lang("_ERROR_ON_RECORD_INSERT") . ' ' . $error['message'];
                    $this->session->set_flashdata('flash_message', $message_success);
                    $this->session->set_userdata('dashboard_application_message_type', 'error');
                    $error = 1;
                }
            }
            
            $pkey_id = $id;
        }
        
        return array(
            'pkey_id' => $pkey_id,
            'status' => $error
        );
    }
    
    /*
     * perform copy
     */
    public function copy()
    {
        $xmlData = $this->xml_parsing();
        $tableName = $this->tablePrefix . $xmlData['name'];
        $idsArray = $this->input->post('ids');
        $totalCopyRows = count($idsArray);
        $copyField = $this->copy_field($xmlData);
        $copyResult = $this->dashboard_model->copy($tableName, $idsArray, $copyField);
        
        if ($copyResult) {
            
            if ($totalCopyRows == 1) {
                $message_success = sprintf(dashboard_lang('_RECORD_COPIED_SUCCESSFULLY'), $totalCopyRows);
            } elseif ($totalCopyRows > 1) {
                $message_success = sprintf(dashboard_lang('_RECORDS_COPIED_SUCCESSFULLY'), $totalCopyRows);
            } else {}
            
            $this->session->set_flashdata('flash_message', $message_success);
        }
        
        return $copyResult;
    }

    public function delete_lock_table($id = 0)
    {
        $xmlData = $this->xml_parsing();
        
        $tableName = $this->tablePrefix . $xmlData['name'];
        
        $this->dashboard_model->deleteLockData($id, $tableName);
        
        $controller_sub_folder = $this->config->item('controller_sub_folder');
        
        $listing_path = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("listing_view");
        $record_position = $this->uri->segment(5);
        if (isset($record_position) and ! empty(isset($record_position))) {
            $listing_path .= "/$record_position";
        }
        
        redirect($listing_path);
    }
    
    /*
     * perform delete
     */
    public function delete()
    {
        $xmlData = $this->xml_parsing();
        $hardDelete = intval($xmlData['hard_delete']);
        $tableName = $this->tablePrefix . (string) $xmlData['name'];
        $primary_key = (string) $xmlData['primary_key'];
        $ids = $this->input->post('ids');
        $totalDeletedRows = count($ids);
        $totalUndelatedIds = 0;
        $totalDelatedIds = 0;
        $linkedTable = '';
        $errorMessage = "";
        
        $this->_performBeforeDelete($ids, $hardDelete, $tableName);
        
        foreach ($ids as $key => $value) {
            
            $deleteResult = SqlHelper::delete($tableName, $primary_key, $value, $hardDelete);

            if ($deleteResult['deleted']) {
                $totalDelatedIds += 1;                
                $this->Events_model->executeTableEntryEvent("delete_entry", $tableName, array(), $value);
            } else {
                $totalUndelatedIds += 1;
                if (is_array($deleteResult['linkedTable']) && count($deleteResult['linkedTable'])) {
                    $linkedTable = implode(', ', $deleteResult['linkedTable']);
                }
                // add error message
                $errorMessage = @$deleteResult['error_message'];
            }
        }
        
        // db error message
        if(!empty($errorMessage)){
            $tempErrorMessage = dashboard_lang("_DELETE_OPERATION_FAIL_FOR_THIS_DATABASE_ERROR");
            $errorMessage = $tempErrorMessage . ": ". $errorMessage;
        }
        
        if ($totalDelatedIds > 0) {
            
            if ($totalDelatedIds == 1) {
                $message_success = sprintf(dashboard_lang('_RECORD_DELETED_SUCCESSFULLY'), $totalDelatedIds);
            } else {
                $message_success = sprintf(dashboard_lang('_RECORDS_DELETED_SUCCESSFULLY'), $totalDelatedIds);
            }
            
            if ($totalUndelatedIds > 0) {
                $message_success .= " " . sprintf(dashboard_lang('_UNDELETED_ITEMS_MESSAGE'), $totalUndelatedIds);
            }
        }        
        
        if ($totalDelatedIds == 0 and $totalUndelatedIds > 0) {
            $permission_msg = dashboard_lang("_PERMISSION_DENIED_TO_DELETE_RECORD");
            if ($linkedTable) {
                $tableTranslatedStr = dashboard_lang("_TABLE");
                if ( count($deleteResult['linkedTable']) > 1 ) {
                    $tableTranslatedStr = dashboard_lang("_TABLES");
                }
                $permission_msg = dashboard_lang("_DELETE_FAILED") . ". " . dashboard_lang("_USED_IN") . " " .  ($linkedTable) . " " . strtolower($tableTranslatedStr);
            }
            if(!empty($errorMessage)){
                $permission_msg = $errorMessage; 
            }
            $this->session->set_flashdata('flash_message', $permission_msg);
            $this->session->set_userdata("dashboard_application_message_type", "error");
        } else {
            if(!empty($errorMessage)){
                $message_success .= "<br>" . $errorMessage;
            }
            $this->session->set_flashdata('flash_message', $message_success);
        }
        
        $this->_performAfterDelete($ids, $hardDelete, $tableName);
        
        return $deleteResult['deleted'];
    }
    
    /*
     * --------------------
     */
    public function check_field_uniqueness()
    {
        $tableName = $this->input->post('table_name');
        $fieldName = $this->input->post('field_name');
        $field_value = $this->input->post('field_value');
        $id = $this->input->post('id');
        $response = $this->dashboard_model->checkUniqueFieldValue($tableName, $fieldName, $field_value, $id);
        echo json_encode($response);
    }
    
    /*
     * perform before delete
     */
    protected function _performBeforeDelete($ids, $hardDelete = 0, $tableName = "")
    {}
    
    /*
     * perform after delete
     */
    protected function _performAfterDelete($ids, $hardDelete = 0, $tableName = "")
    {}
    
    /*
     * perform delete
     */
    public function trash_operation()
    {
        $xmlData = $this->xml_parsing();
        $tableName = $this->tablePrefix . (string) $xmlData['name'];
        $primary_key = (string) $xmlData['primary_key'];
        $ids = $this->input->post('ids');
        $un_trash = $this->input->post('un_trash');
        $totalDeletedRows = count($ids);
        $totalUndelatedIds = 0;
        $totalDelatedIds = 0;
        $fileFieldsList = array();
        
        if (isset($un_trash) && $un_trash == 0) {
            $xmlData = $this->xml_parsing();
            $fileFieldsList = $this->System_model->getFileFieldList($xmlData);
            //call trigger function
            $this->_performBeforeDelete($ids, 1, $tableName);
        }
        
        foreach ($ids as $key => $value) {
            
            $deleteResult = SqlHelper::trash($tableName, $primary_key, $value, $un_trash, $fileFieldsList);
            if ($deleteResult) {
                $totalDelatedIds += 1;
                if ($un_trash == 0) {
                    $sucessfullyDeletedIds[] = $value;
                }
            } else {
                $totalUndelatedIds += 1;
            }
        }
        
        if ($totalDelatedIds > 0) {
            
            if (isset($un_trash) and $un_trash == 1) {
                if ($totalDelatedIds == 1) {
                    $message_success = sprintf(dashboard_lang('_SOFT_DELETED_RECORD_RESTORED'), $totalDelatedIds);
                } else {
                    $message_success = sprintf(dashboard_lang('_SOFT_DELETED_RECORDS_RESTORED'), $totalDelatedIds);
                }
                
                if ($totalUndelatedIds > 0) {
                    $message_success .= " " . sprintf(dashboard_lang('_UNRESTORED_ITEMS_MESSAGE'), $totalUndelatedIds);
                }
            } elseif (isset($un_trash) and $un_trash == 0) {
                if ($totalDelatedIds == 1) {
                    $message_success = sprintf(dashboard_lang('_RECORD_PERMANENTLY_DELETED'), $totalDelatedIds);
                } else {
                    $message_success = sprintf(dashboard_lang('_RECORDS_PERMANENTLY_DELETED'), $totalDelatedIds);
                }
                
                if ($totalUndelatedIds > 0) {
                    $message_success .= " " . sprintf(dashboard_lang('_UNDELETED_ITEMS_MESSAGE'), $totalUndelatedIds);
                }
            }
        }
        
        if ($totalDelatedIds == 0 and $totalUndelatedIds > 0) {
            
            $permission_msg = dashboard_lang("_PERMISSION_DENIED_TO_DELETE_RECORD");
            $this->session->set_flashdata('flash_message', $permission_msg);
            $this->session->set_userdata("dashboard_application_message_type", "error");
        } else {
            
            $this->session->set_flashdata('flash_message', $message_success);
        }
        
        /*
         * call performAfterDelete
         */
        
        if($un_trash == 0){
            $this->_performAfterDelete($sucessfullyDeletedIds, 1, $tableName);
        }
        
        return $deleteResult;
    }
    
    /*
     * User select FROM show field for listing data view
     */
    function ajax_update_user_selection()
    {
        $tableName = $this->tablePrefix . $this->config->item("user_show_listing_field_table");
        
        $result_return = "";
        
        $fields = $this->input->post('fields');
        
        $field_names = implode(",", $fields);
        
        $xmlData = $this->xml_parsing();
        $tableNameFixed = $this->tablePrefix . (string) $xmlData['name'];
        
        $data = array(
            'user_id' => get_user_id(),
            'table_name' => $tableNameFixed,
            'list_fields' => $field_names
        );
        
        $this->db->select("user_id");
        $this->db->where("user_id", get_user_id());
        $this->db->where("table_name", $tableNameFixed);
        $query = $this->db->get($tableName);
        
        if ($query->num_rows() > 0) {


            $whereArray = array(
                "user_id" => get_user_id(),
                "table_name" => $tableNameFixed
            );
            $this->db->where($whereArray);
            $result_return = $this->db->update($tableName, $data);

        } else {
            $result_return = $this->db->insert($tableName, $data);
        }
        $this->ajax_update_user_order();
        return $result_return;
    }
    
    /*
     * User select FROM show field for ordering data view
     */
    function ajax_update_user_order()
    {
        $tableName = $this->tablePrefix . $this->config->item("user_show_listing_field_table");
        $result_return = "";
        $sort = $this->input->post('sort');
        
        $alludata = $this->session->all_userdata();
        
        foreach ($alludata as $key => $alludatea) {
            
            $match_key = 'userstate.' . $this->current_class_name;
            $is_matched = strpos($key, $match_key);
            if ($is_matched !== false) {
                
                $this->session->unset_userdata($key);
            }
        }
        
        $unset_sort_keys = array(
            'table_sort_field',
            'table_sort_direction'
        );
        
        $this->session->unset_userdata($unset_sort_keys);
        
        if (sizeof($sort) > '0') {
            $set_session = array();
            foreach ($sort as $key => $value) {
                $sortFieldname = $key;
                $sortDirection = $value;
                break;
            }
            
        }
        
        $order_fields = array();
        
        if (count($sort) > 0) {
            foreach ($sort as $key => $value) {
                $order_fields[$key] = $value;
            }
            $order_fields = serialize($order_fields);
        } else {
            $order_fields = '';
        }
        
        $xmlData = $this->xml_parsing();
        $tableNameFixed = $this->tablePrefix . (string) $xmlData['name'];
        
        $this->query_toAddOrUpdate($tableName, $order_fields);
    }
    
    /*
     * User reset FROM show field for listing data view
     */
    function ajax_reset_user_selection()
    {
        $tableName = $this->tablePrefix . $this->config->item("user_show_listing_field_table");
        $user_id = $this->input->post('user');
        $data = array(
            'list_fields' => ''
        );
        $xmlData = $this->xml_parsing();
        $tableNameFixed = $this->tablePrefix . (string) $xmlData['name'];
        
        $whereArray = array(
            "user_id" => get_user_id(),
            "table_name" => $tableNameFixed
        );
        
        $result_return = $this->dashboard_model->edit($tableName, $data, $whereArray);
        $this->ajax_reset_user_order();
        return $result_return;
    }
    
    /*
     * User reset FROM show field for ordering data view
     */
    function ajax_reset_user_order()
    {
        $result_return = '';
        $tableName = $this->tablePrefix . $this->config->item("user_show_listing_field_table");
        $user_id = $this->input->post('user');
        $data = array(
            'order_fields' => ''
        );
        
        $xmlData = $this->xml_parsing();
        $tableNameFixed = $this->tablePrefix . (string) $xmlData['name'];
        
        $whereArray = array(
            "user_id" => get_user_id(),
            "table_name" => $tableNameFixed
        );
        
        $result_return = $this->dashboard_model->edit($tableName, $data, $whereArray);
        
        return $result_return;
    }
    
    /*
     * parse xml data
     */
    protected function xml_parsing()
    {
        if (empty($this->_xmldata)) {
            $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . $this->current_class_name . ".xml";
            libxml_use_internal_errors(true);
            $this->_xmldata = simplexml_load_file($xmlFile);
          
            if ($this->_xmldata === false) {
                /*
                 * xml parsing error, shown error message in below
                 */
                foreach (libxml_get_errors() as $error) {
                    echo dashboard_lang('_DASHBOARD_XML_PERSING_ERROR') . "\t", $error->message;
                }
                exit();
            }
            $this->tableName = $this->tablePrefix . $this->_xmldata['name'];
            
            return $this->_xmldata;
        } else {
            return $this->_xmldata;
        }
    }
    
    /*
     * copy fields
     */
    private function copy_field($xmlObject)
    {
        $copyFields = "";
        
        foreach ($xmlObject->field as $value) {
            if (isset($value['copy']) && (bool) $value['copy'] == 1) {
                $copyFields = (string) $value['name'];
            }
        }
        
        return $copyFields;
    }
    
    /*
     * Search auto suggest words here
     */
    public function get_search_words($word)
    {
        $limit = $this->config->item('search_auto_suggest_limit');
        $max_length = $this->config->item('search_auto_suggest_maxlength');
        
        $xmlData = $this->xml_parsing();
        $tableName = $this->tablePrefix . $xmlData['name'];
        $tableField = $this->get_show_field();
        
        $filter_by_field = (int) $xmlData['filter_by_field'];
        $additional_condition = array();
        
        if ($filter_by_field) {
            
            // get the current user
            $current_user = BUserHelper::get_instance();
            
            // get the related field
            $related_field = (string) $xmlData['related_field'];
            
            // get the external field
            $external_field = (string) $xmlData['external_field'];
            
            // make the additional condition in AND format
            if ($current_user->user->{$external_field}) {
                
                $additional_condition['AND'] = $tableName . '.' . $related_field . ' = ' . $this->db->escape($current_user->user->{$external_field});
            }
        }


        $lookUpData['lookUpFields'] = [];
        $lookUpData['refTable'] = [];
        $lookUpData['refSelectField'] = [];
        
        foreach($xmlData->field as $value) {
            if ($value['type'] == 'lookup') {

                $lookUpData['lookUpFields'][] = (string)$value['name'];
                $lookUpData['refTable'][(string)$value['name']] = (string)$value['ref_table'];
                $lookUpData['refTableKey'][(string)$value['name']] = (string)$value['key'];
                $lookUpData['refSelectField'][(string)$value['name']] = (string)$value['value'];
            }
        }
        
        $list = $this->dashboard_model->get_words($lookUpData,$this->account_id, $tableName, $tableField, $word, $limit, $additional_condition);
        
        if ($list) {
            foreach ($list as &$item) {
                if (strlen($item['value']) > $max_length) {
                    $item['value'] = substr($item['value'], 0, $max_length) . '...';
                }
            }
            
            echo json_encode($list);
        } else {
            
            echo json_encode(array());
        }
    }
    
    /*
     * Show field will return fields form database or xml
     */
    private function get_show_field()
    {
        $tableNameSelectFields = $this->tablePrefix . $this->config->item("user_show_listing_field_table");
        
        $this->db->select("list_fields");
        $xmlData = $this->xml_parsing();
        $tableNameFixed = $this->tablePrefix . (string) $xmlData['name'];
        
        $whereArray = array(
            "user_id" => get_user_id(),
            "table_name" => $tableNameFixed
        );
        $this->db->where($whereArray);
        $querySelectFields = $this->db->get($tableNameSelectFields);
        
        if ($querySelectFields->num_rows() > 0) {
            $row = $querySelectFields->row();
            if (strlen($row->list_fields) > 0) {
                $tableFieldDb = explode(",", $row->list_fields);
                $tableField = $tableFieldDb;
            }
        } else {
            
            foreach ($xmlData->field as $value) {
                if (isset($value['show']) && intval($value['show']) > 0) {
                    $tableField[] = (string) $value['name'];
                }
            }
        }
        
        return $tableField;
    }
    
    /*
     * to get the pagination config
     */
    protected function _getPaginationConfig($per_page, $total_items_count, $trash)
    {
        $controller_sub_folder = $this->config->item('controller_sub_folder');
        $template_name = $this->config->item('template_name');
        if ($template_name == 'metrov5' || $template_name == 'metrov5_4') {
            $config = $this->_getPaginationConfigMetroV5($per_page, $total_items_count, $trash, $controller_sub_folder);
        } else {
            
            $config['uri_segment'] = 4;
            
            $config['base_url'] = site_url() . '/' . $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("listing_view");
            if (isset($trash) and $trash) {
                $config['base_url'] = site_url() . '/' . $controller_sub_folder . '/' . $this->current_class_name . "/trash";
            }
            $config['total_rows'] = $total_items_count;
            $config['per_page'] = $per_page;
            
            $config['cur_tag_open'] = '<li class="paginate_button active"><a href="#">';
            $config['cur_tag_close'] = '</a></li>';
            
            $config['first_tag_open'] = '<li class="paginate_button">';
            $config['first_tag_close'] = '</li>';
            
            $config['prev_tag_open'] = '<li class="paginate_button">';
            $config['prev_tag_close'] = '</li>';
            
            $config['full_tag_open'] = '<ul class="pagination">';
            $config['full_tag_close'] = '</ul>';
            
            $config['num_tag_open'] = '<li class="paginate_button">';
            $config['num_tag_close'] = '</li>';
            $config['next_tag_open'] = '<li class="paginate_button">';
            $config['next_tag_close'] = '</li>';
            $config['last_tag_open'] = '<li class="paginate_button">';
            $config['last_tag_close'] = '</li>';
        }
        
        return $config;
    }
    
    /*
     * function to upload
     * image
     */
    protected function do_upload($table_name, $filename, $fieldName, $fieldType = "", $id = 0, $allowed_file_type, $subDirectory)
    {
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
        
        $config['overwrite'] = false;
        $config['file_name'] = $filename;
        
        $this->load->library('upload');
        $this->upload->initialize($config);
        
        if (! $this->upload->do_upload($fieldName)) {
            $error = array(
                'error' => $this->upload->display_errors()
            );

            $controller_sub_folder = $this->config->item('controller_sub_folder');
            $file_upload_error = $this->upload->display_errors();
            $this->session->set_flashdata('flash_message', $file_upload_error);
            
            $this->session->set_userdata("dashboard_application_message_type", "error");
            redirect(base_url() . $controller_sub_folder . '/' . $this->current_class_name . '/' . $this->config->item('edit_view') . '/' . ($id ? $id : ''));
            exit();
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
            } else {
                $filename = "";
            }
            
            return $filename;
        }
    }
    
    protected function userGroupPermission()
    {
        $xmlData = $this->xml_parsing();
        $tableName = (string) $xmlData["name"];
        $userQuery = $this->dashboard_model->getUserGroupQuery($tableName);
        if ($userQuery->num_rows() > 0) {
            
            foreach ($userQuery->result() as $row) {
                
                if ($row->field_name == '*') {
                    $this->session->set_userdata($this->current_class_name . "_user_view_permission", $row->field_name);
                    $this->session->set_userdata($this->current_class_name . "_user_edit_permission", $row->edit);
                } else {
                    
                    if ($row->edit > 0) {
                        $this->session->set_userdata($this->current_class_name . "_user_edit_permission", $row->edit);
                    }
                    $this->session->set_userdata($this->current_class_name . "_user_view_permission", $row->field_name);
                }
            }
        }
        
        return true;
    }
    
    /*
     * function to render a page WHERE no permission
     * given
     */
    public function permission()
    {
        $viewPath = $this->current_class_name . "/" . $this->config->item("no_permission");
        
        if (! $this->view_exists($viewPath)) {
            $viewPath = $this->getCoreViewPath('permission');
        }
        
        $data["no_permission_message"] = dashboard_lang("_NO_PERMISSION");
        
        $this->template->write_view('content', $viewPath, $data);
        
        $this->template->render();
    }
    
    /*
     * function to provide permission
     */
    protected function getUserPermissionDataArray($tableNameFixed, $permission = "", $edit = "")
    {
        $userPermittedFields = array();
        
        $selectField = "*";
        $whereArray = array(
            "role" => get_user_role(),
            "table" => $tableNameFixed,
            "is_deleted" => 0
        );
        
        if ($edit != "") {
            $where = array(
                "permission" => $permission
            );
            $whereArray = array_merge($where, $whereArray);
        }
        
        $tableNameSelect = $this->tablePrefix . "table_permissions";
        
        $userDataQuery = $this->dashboard_model->get($tableNameSelect, $selectField, $whereArray);
        
        foreach ($userDataQuery->result() as $value) {
            $userPermittedFields[$value->field_name] = $value->permission;
        }
        
        return $userPermittedFields;
    }
    
    /*
     * check a record is available or not if not then send to listing view
     */
    protected function recordIsAvailable($id, $tableName, $action="")
    {
        // give the record access, when view-only mode
        if($action == $this->config->item("view_only_action_text")){
            return TRUE;
        }
        // check the record status
        $status = $this->dashboard_model->getRecordStatus($id, $tableName);
        if (! $status) { 
            // check user permission    
            $currentUserPermission = $this->Table_permission_model->getEditPermission($tableName);
            // if user has edit permission then, user can only view the record data
            if ($currentUserPermission == '2') {            
                $userId = $this->dashboard_model->getLockTableUserId($id, $tableName);                
                $controllerSubFolder = $this->config->item('controller_sub_folder');
                $flashMessage = "";                
                $editingByOtherUesrMessage = ", ".dashboard_lang("_SO_YOU_CAN_JUST_VIEW_IT");
                $baseUrl = base_url();
                $redirectURL = "{$baseUrl}{$controllerSubFolder}/{$this->current_class_name}/edit/{$id}/?action=view-only";
                if ($userId > 0) :
                    $userInfo = new BUser($userId);
                    $nameArray[] = @$userInfo->first_name;
                    $nameArray[] = @$userInfo->last_name;
                    $userName = implode(" ", $nameArray);
                    if(!empty($userName)) :
                        $flashMessage = $userName. " ". dashboard_lang('_IS_EDITING_THIS_RECORD') . $editingByOtherUesrMessage;
                    else :
                        $flashMessage = dashboard_lang('_SOMEONE_ELSE_IS_EDITING_THIS_RECORD') . $editingByOtherUesrMessage;
                    endif;
                else :
                    $flashMessage = dashboard_lang('_SOMEONE_ELSE_IS_EDITING_THIS_RECORD') . $editingByOtherUesrMessage;
                endif;
                $this->session->set_flashdata('flash_message', $flashMessage);
                $this->session->set_userdata("dashboard_application_message_type", "error");
                redirect($redirectURL);
            } else {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }

    protected function beforeSaveOperation(& $data, $id = 0)
    {}
    
    /*
     * afterSaveOperation() function get three patameters $tableName, $id and $update_status and return 0/1
     * using $tableName and $id we can get all data that we need and $update_status help us to controll our operation
     * $update_status == 0 means record is just inserted and 1 means record has updated
     *
     */
    protected function afterSaveOperation($tableName, $id, $update_status)
    {
        if ( $_POST["iframeView"] == '1' ) {
            $saveAndClose = $this->input->post('saveAndClose');

        if(isset($saveAndClose) && $saveAndClose ){
            redirect("dbtables/$tableName/edit/$id?iframeView=1&iframeSaveAndClose=1");
        }
            redirect("dbtables/$tableName/edit/$id?iframeView=1");
        }
    }
    
    /*
     * Search auto suggest lookup here
     */
    public function get_lookup_auto_suggest()
    {
        $limit = $this->config->item('search_auto_suggest_limit');
        
        $getTable = $this->input->get('tbl');
        $tableName = $this->tablePrefix . $getTable;
        
        $word = $this->input->get('q');
        $id = $this->input->get('id');
        
        $key = $this->input->get('key');
        $val = $this->input->get('val');
        
        $orderBy = $val;
        $orderOn = "ASC";
        
        $xmlData = $this->xml_parsing();
        foreach ($xmlData->field as $value) {
            $fieldType = (string) $value['type'];
            if (isset($value['type']) && $fieldType == 'lookup' && isset($value['autosuggest']) && $value['autosuggest'] == 1) {
                $keyXml = (string) $value['key'];
                $valueXml = (string) $value['value'];
                $refTableXml = (string) $value['ref_table'];
                if ($keyXml == $key && $valueXml == $val && $refTableXml == $getTable) {
                    $orderBy = (string) $value['order_by'] ? (string) $value['order_by'] : $valueXml;
                    $orderOn = (string) $value['order_on'] ? (string) $value['order_on'] : 'ASC';
                }
            }
        }
        
        $tableField = array(
            $key . ' as id',
            $val . ' as name'
        );
        
        if ($id != "") {
            $wordWhere = array(
                $key => $id
            );
        }
        
        if ($word != "") {
            $wordWhere = array(
                $val => $word
            );
        }
        
        $list = $this->dashboard_model->get_autosuggest_lookup($tableName, $tableField, $wordWhere, $orderBy, $orderOn, $limit);
        
        if ($list) {
            echo json_encode($list);
        } else {
            echo json_encode(array());
        }
        
        return '';
    }
    
    /*
     * function to add some additional condition in the WHERE clause
     */
    protected function db_getWhereCondition($shared_status, $filter_conditions)
    {
        $conditions = '';
        $where_array = array();
        if (isset($this->account_id)) {
            $where_array['AND'] = $this->tableName . '.account_id = ' . $this->account_id;
        }
        
        if ($shared_status == 2) {
            $where_array['OR'] = $this->tableName . '.account_id = ' . get_default_account_id();
        }
        if(is_array($this->_selectedIds) && sizeof($this->_selectedIds) > 0){
             $where_array['IN'][] = array("field_name"=>"{$this->tableName}.id","data"=> $this->_selectedIds);
         }
        $conditions = $where_array;
        return $conditions;
    }

    function search_all_fields()
    {
        $data = $this->input->post('data');
        $table_name = $this->tablePrefix . $this->input->post('table_name');
        $current_field_name = $this->input->post('current_field_name');
        $lookup = $this->input->post('lookup');
        
        $this->dashboard_model->search_all_fields($data, $table_name, $current_field_name, $lookup);
    }

    function search_own_field()
    {
        $this->dashboard_model->search_multi_select_own_field();
    }

    public function check_eav_data_exists()
    {
        $xmlData = $this->xml_parsing();
        
        $eav_field_list = array();
        foreach ($xmlData->field as $value) {
            
            $type = (string) $value['type'];
            if ($type == 'eav') {
                
                $data['insert_table_forign_key'] = (string) $value['insert_table_forign_key'];
                $data['insert_table'] = (string) $value['insert_table'];
                $data['insert_table_reference_key'] = (string) $value['insert_table_reference_key'];
                $data['name'] = (string) $value['name'];
                
                $eav_field_list[] = $data;
            }
        }
        
        return $eav_field_list;
    }

    public function get_lookup_autosuggest()
    {
        $search_string = $this->input->get('q');
        $ref_table = $this->input->get('ref_table');
        $ref_key = $this->input->get('ref_key');
        $ref_value = $this->input->get('ref_value');
        $order_by = $this->input->get('order_by');
        $order_on = $this->input->get('order_on');
        $data = array();
        $result = $this->dashboard_model->get_lookup_autosuggest($search_string, $ref_table, $ref_key, $ref_value, $order_by, $order_on);
        $response = array();
        
        if (sizeof($result) > 0) {
            
            $data['id'] = 0;
            $data['text'] = dashboard_lang('_PLEASE_SELECT');
            $response[] = $data;
            
            foreach ($result as $res) {
                $data = array();
                $data['id'] = $res['select_key'];
                $data['text'] = $res['select_value'];
                $response[] = $data;
            }
        } else {
            $data['id'] = $this->config->item('dropdown_in_ajax_value');
            $data['text'] = "<a href='javascript:void(0);' onclick='render_popup (id)'>" . dashboard_lang('_ADD_NEW') . "</a>";
            $response[] = $data;
        }
        echo json_encode($response);
    }
    
    // Get listing data
    protected function getListingData($showAllRecords = FALSE, $mainListing = TRUE, $trash = FALSE, $offset = 0, $fileType='')
    {
        $this->load->helper('portal/dashboard_list');
        $superUserPermission = $this->dashboard_model->getSuperUserPermission();
        $this->save_search_data_to_session();
        $dashboard_helper = Dashboard_main_helper::get_instance();
        $dashboard_helper->set("super_user", 0);
        $no_permission = false;
        $super_user = 0;
        
        if ($superUserPermission) {
            
            $dashboard_helper->set("super_user", 1);
            $super_user = 1;
        } else {
            
            $tableName = $this->tablePrefix . $this->current_class_name;
            $userAccess = $this->dashboard_model->getUserGroupQuery($tableName);
            $super_user = 0;
            if ($userAccess->num_rows() > 0) {
                $dashboard_helper->set("super_user", 0);
                $super_user = 0;
            }
        }
        
        $controller_sub_folder = $this->config->item('controller_sub_folder');
        $template_id = 0;
        $tableName = $this->tablePrefix . $this->current_class_name;
        
        $template_id = $this->get_template_id($tableName);
        
        /*
         * input: group_name , table_name return: list of fields which will be visible in the system
         */
        
        $this->load->library('pagination');
        
        $listingPath = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("listing_view");
        if ($trash) {
            $listingPath = $controller_sub_folder . '/' . $this->current_class_name . "/trash";
        }
        $this->session->set_userdata("current_url", $listingPath);
        
        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . $this->current_class_name . ".xml";
        
        $this->config->load('dashboard_override', FALSE, TRUE);
        $xmlData = $this->xml_parsing();
        $data['xmlData'] = $xmlData;
        $primary_key = (string) $xmlData['primary_key'];
        $shared_status = intval($xmlData['shared_status']);
        $show_copy = intval($xmlData['show_copy']);
        
        $data['show_copy'] = FALSE;
        
        if (isset($show_copy) and $show_copy) {
            $data['show_copy'] = TRUE;
        }
        
        if ($primary_key == "") {
            
            show_error(dashboard_lang('_PRIMARY_KEY_MISSING'));
            
            return FALSE;
        }
        
        $per_page = $this->input->post("per_page");
        $perPegeArray = $this->config->item("list_per_page");
        
        if (intval($per_page) > 0) {
            $this->session->set_userdata('per_page@' . $tableName, $per_page);
        }
        
        $sessionPerPage = $this->session->userdata('per_page@' . $tableName);
        
        if (isset($sessionPerPage) && $sessionPerPage != "" && (($sessionPerPage == $per_page) || ($per_page == ""))) {
            $per_page = $this->session->userdata('per_page@' . $tableName);
        } else {
            
            if ($template_id > 0) {
                
                $template_data = $this->System_model->get_template($template_id)->result(); // ['session_data'];
                $template_per_page = (json_decode($template_data[0]->session_data)->per_page);
                
                if (intval($template_per_page) > 0) {
                    
                    $this->session->set_userdata('per_page@' . $tableName, $template_per_page);
                    $per_page = $template_per_page;
                } else {
                    $per_page = $this->config->item("#ITEMS_PER_PAGE");
                }
            } else {
                $per_page = $this->config->item("#ITEMS_PER_PAGE");
            }
        }
        
        $limit_items = $per_page;

        //check maximun number of record for export pdf 
        $limit_pdf_items = $this->config->item('#EXPORT_MAXIMUM_NUMBER_OF_RECORDS_FOR_PDF');
        if( !empty($limit_pdf_items) && $per_page > $limit_pdf_items && $fileType == 'pdf'){
            $this->session->set_userdata('export_pdf_error', 'pdf_eror_show');
            $listing_path = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("listing_view");
            redirect( base_url().$listing_path);
        }
        
        // check show all records
        
        if ($showAllRecords) {
            $limit_items = 0;
        }
        
        $tableName = $this->tablePrefix . (string) $xmlData['name'];
        
        $tableNameFixed = $this->tablePrefix . (string) $xmlData['name'];
        
        $tableField = array();
        $tableFieldOrder = array();
        $order_by = $this->_getOrderBy($primary_key);
        
        $orderByField = @$order_by['table_sort_field'];
        if (array_key_exists('default_table_sort_field', $order_by)) {
            $defaultorderByField = $order_by['default_table_sort_field'];
        }
        $orderByValue = @$order_by['table_sort_direction'];
        
        /*
         * GET user permission data
         */
        $userPermissionDataArray = $this->getUserPermissionDataArray($tableNameFixed);
        
        $tableNameSelectFields = $this->tablePrefix . $this->config->item("user_show_listing_field_table");
        
        $selectField = "list_fields, order_fields";
        $whereArray = array(
            "user_id" => get_user_id(),
            "table_name" => $tableNameFixed
        );
        
        $whereArray['account_id'] = get_default_account_id();
        
        if (isset($shared_status) && $shared_status == 1) {
            $this->account_id = get_default_account_id();
        } else {
            $this->account_id = get_account_id();
        }
        
        $querySelectFields = $this->dashboard_model->get($tableNameSelectFields, $selectField, $whereArray);
        
        if ($querySelectFields->num_rows() > 0) {
            
            $row = $querySelectFields->row();
            
            if (strlen($row->list_fields)) {
                $tableFieldDb = explode(",", $row->list_fields);
                
                /*
                 * check any field is deleted from xml file
                 * if delete then remove from listing_field
                 */
                
                $xmlField = array();
                foreach ($xmlData->field as $value) {
                    $xmlField[(string) $value['name']] = $value;
                }
                
                foreach ($tableFieldDb as $key => $field) {
                    if (! array_key_exists($field, $xmlField) || (int) $xmlField[$field]['hidden_in_list'] == 1 || (isset($userPermissionDataArray[$field]) && $userPermissionDataArray[$field] < 1)) {
                        unset($tableFieldDb[$key]);
                    }
                }
                // end checking xml file with listing field
                
                $data['listing_field'] = $tableFieldDb;
                
                $tableField = array_merge($tableField, $tableFieldDb);
            } else {
                
                foreach ($xmlData->field as $value) {
                    if (isset($value['show']) && intval($value['show']) > 0 && $value['type'] != 'messages' ) {
                        $tableField[intval($value['show'])] = (string) $value['name'];
                    }
                }
                
                ksort($tableField);
                $data['listing_field'] = $tableField;
            }
            
            if (strlen($row->order_fields)) {
                
                $tableFieldOrderDb = @unserialize($row->order_fields);
                if (is_array($tableFieldOrderDb) && count($tableFieldOrderDb)) {
                    foreach ($tableFieldOrderDb as $field => $sort_value) {
                        if (! in_array($field, $data['listing_field'])) {
                            unset($tableFieldOrderDb[$field]);
                        }
                    }
                }
                $data['ordering_fields'] = $tableFieldOrderDb;
                
                $allOrderby = serialize($tableFieldOrderDb);
            } else {
                
                foreach ($xmlData->field as $value) {
                    if ((isset($value['sort']) && $value['sort'] == 1) and in_array($value['name'], $data['listing_field'])) {
                        $tableFieldOrder[(string) $value['name']] = 'desc';
                    }
                }
                
                $data['ordering_fields'] = $tableFieldOrder;
                // $allOrderby = implode(",", $tableFieldOrder);
                $allOrderby = serialize($tableFieldOrder);
            }
        } else {
            
            foreach ($xmlData->field as $value) {
                if (isset($value['show']) && intval($value['show']) > 0 && $value['type'] != 'messages'  && $value['type'] != 'history') {
                    $tableField[intval($value['show'])] = (string) $value['name'];
                }
                if (isset($value['sort']) && $value['sort'] == 1 && $value['type'] != 'messages' && $value['type'] != 'history') {
                    $tableFieldOrder[(string) $value['name']] = 'desc';
                }

               
            }
            
            ksort($tableField);
            $data['listing_field'] = $tableField;
            $data['ordering_fields'] = $tableFieldOrder;
            
            $allOrderby = serialize($tableFieldOrder);
        }
        
        $allField = array();
        
        foreach ($xmlData->field as $value) {
            if ($value['hidden_in_list'] == 1) {
                continue;
            }
            
            $data_value = (string) $value['name'];
            $type = (string) $value['type'];
            
            if ($super_user) {
                
                if ($type != 'messages' && $type != 'history') {
                    
                    $allField[$data_value] = $value;
                }
            } else {
                
                if (is_array($userPermissionDataArray) && count($userPermissionDataArray) > 0) {
                    
                    if (array_key_exists($data_value, $userPermissionDataArray)) {
                        
                        if ($userPermissionDataArray[$data_value] > 0) {
                            
                            if ($type != 'messages' && $type != 'history') {
                                
                                $allField[$data_value] = $value;
                            }
                        }
                    } else {
                        
                        if (array_key_exists('*', $userPermissionDataArray)) {
                            
                            if ($userPermissionDataArray['*'] > 0) {
                                
                                if ($type != 'messages' && $type != 'history') {
                                    
                                    $allField[$data_value] = $value;
                                }
                            }
                        }
                    }
                } 

                else {
                    
                    $no_permission = true;
                }
            }
        }
        
        if ( $no_permission )
        {
            $error_msg = array();
            $error_msg['heading'] = dashboard_lang('_ACCESS_DENIED');
            $error_msg['message'] = dashboard_lang('_YOUR_USERGROUP_HAS_NO_PERMISSION_TO_ACCESS_THIS_PAGE');
            
            echo $this->load->view('errors/html/error_403', $error_msg, true);
            exit;
        }
        
        

        $allOrderbyArray = unserialize($allOrderby);
        $fields_sorting_saved_values = array();
        
        if (is_array($allOrderbyArray) && count($allOrderbyArray)) {
            foreach ($allOrderbyArray as $field => $sort_value) {
                $object = new stdClass();
                
                $object->direction = $sort_value;
                $fields_sorting_saved_values[$field] = $object;
            }
        }
        $orderByDirection = ($orderByValue != "") ? $orderByValue : $this->config->item("all_order_by_value");
        
        $start = $this->uri->segment(4);
        if (! empty($offset)) {
            $start = $offset;
        }
        if ($start == NULL)
            $limit_start = 0;
        else
            $limit_start = $start;
            
            /* search start */
        
        $search = rtrim(trim($this->input->post("search")), '.');
        $reset = $this->input->post("reset");
        $additional_condition = $this->db_getWhereCondition($shared_status, array());
        
        $filter_by_field = (int) $xmlData['filter_by_field'];
        if ($filter_by_field) {
            
            // get the current user
            $current_user = BUserHelper::get_instance();
            
            // get the related field
            $related_field = (string) $xmlData['related_field'];
            
            // get the external field
            $external_field = (string) $xmlData['external_field'];
            
            // make the additional condition in AND format
            if ($current_user->user->{$external_field}) {
                if (isset($additional_condition['AND'])) {
                    if (is_array($additional_condition['AND'])) {
                        $additional_condition['AND'][] = $tableName . '.' . $related_field . ' = ' . $this->db->escape($current_user->user->{$external_field});
                    } else {
                        $additional_condition['AND'] .= ' AND ' . $tableName . '.' . $related_field . ' = ' . $this->db->escape($current_user->user->{$external_field});
                    }
                } else {
                    $additional_condition['AND'] = $tableName . '.' . $related_field . ' = ' . $this->db->escape($current_user->user->{$external_field});
                }
            }
        }
        
        if (isset($reset) && $reset) {
            $this->session->unset_userdata('search_' . $this->current_class_name);
            $search = "";
            redirect(base_url() . $listingPath);
        }
        if (isset($search) && $search) {
            $this->session->set_userdata('search_' . $this->current_class_name, $search);
            $limit_start = 0;
        }
        $search = $this->session->userdata('search_' . $this->current_class_name);
        $condition = ($search != "") ? $search : "";
        
        /* search end */
        
        /* multi select search start */
        $multi_select_post = $this->input->post();
        
        $please_select_txt = $this->config->item('please_select');
        
        $multi_select_array["multi_select_array"] = array();
        
        // check if the post values only for multi-select
        if (@$multi_select_post['multi_select_track']) {
            
            /*
             * delete 'multi_select_track' key FROM array,
             * because it is not necessary for multi-select array
             * its only use for multi-select track
             */
            unset($multi_select_post['multi_select_track']);
            
            $this->session->set_userdata("table_name", $tableNameFixed);
            
            foreach ($multi_select_post as $key => $value) {
                
                if ($value) {
                    
                    if ($key == "search") {
                        // when search str available, then set empty array to multi select dropdown sort
                        $multi_select_array["multi_select_array"] = array();
                    } else {
                        // when sort using multi select dropdown
                        $multi_select_array["multi_select_array"][$key] = $value;
                        // set search condition to empty
                        $condition = "";
                    }
                }
                $this->session->set_userdata($multi_select_array);
            }
        }
        
        $multi_select_array = $this->session->userdata('multi_select_array');
        
        if ($this->_xmldata . $this->uri->segment(2) != $this->session->userdata('table_name')) {
            $multi_select_array = array();
        }
        
        /* multi select search end */
        if (array_key_exists('default_table_sort_field', $order_by)) {
            if ($order_by['table_sort_field'] == $order_by['default_table_sort_field']) {
                if (is_array($data['ordering_fields']) && array_key_exists(0, $data['ordering_fields'])) {
                    $orderByField = $data['ordering_fields'][0];
                }
                $orderByDirection = 'desc';
            }
        }
        
        if (! in_array($orderByField, $data['listing_field'])) {
            $orderByDirection = "id";
            $orderByField = "asc";
        }
        
        $allFieldTemp = $allField;
        $allFieldTemp['table-attributes'] = $this->getTableAttributes();

        

        $total_items = $this->dashboard_model->listing($allFieldTemp, $tableField, $condition, $multi_select_array, $fields_sorting_saved_values, $orderByDirection, $orderByField, $limit_start, $limit_items, $additional_condition, $trash);
        
        
        $dashboard_helper = Dashboard_main_helper::get_instance();
        $total_items_count = $dashboard_helper->get('listing_total_items');
        $pagination_config = $this->_getPaginationConfig($per_page, $total_items_count, $trash);
        
        $this->pagination->initialize($pagination_config);
        
        $data['paging'] = $this->pagination->create_links();
        
        $viewPathAction = $this->template_name . '/' . $this->current_class_name . "/" . $this->config->item("listing_action_file_name");
        
        if (! $this->view_exists($viewPathAction))
            $viewPathAction = $this->current_class_name . "/" . $this->config->item("listing_action_file_name");
        if (! $this->view_exists($viewPathAction)) {
            $viewPathAction = $this->getCoreViewPath('list_action');
        }
        
        $footer_path = $this->template_name . '/' . $this->current_class_name . "/" . $this->config->item("listing_footer_file_name");
        
        if (! $this->view_exists($footer_path))
            $footer_path = $this->current_class_name . "/" . $this->config->item("listing_footer_file_name");
        if (! $this->view_exists($footer_path)) {
            $footer_path = $this->getCoreViewPath('list_footer');
        }
        
        // check record's add delete permission
        
        if ($this->current_class_name == "message") {
            $data['add_delete_permissions'] = array(
                'add_permission' => '1',
                'delete_permission' => '1'
            );
        } else {
            $data['add_delete_permissions'] = $this->dashboard_model->get_user_add_delete_permissions($this->current_class_name);
        }
        
        $disallowedFields = $this->Table_permission_model->listsPermissionFields($this->current_class_name, 0);
        foreach ($allField as $key => $value) {
            
            if (in_array($key, $disallowedFields)) {
                
                unset($allField[$key]);
            }
        }

        if(empty($allField)){
            // $data['listing_field'] = array();

        }
        
        $data['selected_template_id'] = $template_id;
        $data['total_items_count'] = $pagination_config['total_rows'];
        $data['permission'] = count($userPermissionDataArray);
        $data['permission_array'] = $userPermissionDataArray;
        $data['viewPathAction'] = $viewPathAction;
        $data['viewPathFooter'] = $footer_path;
        $data['per_page_show'] = $per_page;
        $data['search'] = $condition;
        $data['all_field'] = @$allField;
        $data['no_permission'] = $no_permission;
        $data['table_name'] = $this->current_class_name;
        $data['sorting_options'] = $order_by;
        $data['list'] = $total_items;
        $data['edit_path'] = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("edit_view");
        $data['delete_path'] = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("delete_view");
        $data['listing_path'] = $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("listing_view");
        $data['class_name'] = $this->current_class_name;
        $data['delete_method'] = $this->config->item("delete_view");
        $data['copy_method'] = $this->config->item("copy_view");
        $data['ajax_update_user_selection'] = $this->config->item("ajax_update_user_selection");
        $data['ajax_reset_user_selection'] = $this->config->item("ajax_reset_user_selection");
        $data['ajax_update_user_order'] = $this->config->item("ajax_update_user_order");
        $data['ajax_reset_user_order'] = $this->config->item("ajax_reset_user_order");
        $data['all_saved_template'] = $this->System_model->get_all_saved_template($tableName);
        $data['primary_key'] = $primary_key;
        
        return $data;
    }

    protected function getTableAttributes()
    {
        $AttributesArray = array();
        $xmlData = $this->_xmldata;
        foreach ($xmlData->attributes() as $key => $value) {
            $AttributesArray[$key] = (string) $value;
        }
        
        return $AttributesArray;
    }

    public function saveAndNext($id = 0)
    {
        $showAllRecords = TRUE;
        $mainListing = FALSE;
        // get listing data
        $listing_data = $this->getListingData($showAllRecords, $mainListing);
        
        // custome functionality start from here
        
        $all_rows = $listing_data['list'];
        $total_rows = $listing_data['total_items_count'];
        
        $counter = 1;
        $position = 0;
        foreach ($all_rows as $single_row) {
            if ($single_row->id == $id) {
                $position = $counter;
            }
            
            $counter ++;
        }
        
        // get next record id
        
        $data_array = array();
        $next_record_id = 0;
        $flag = 0;
        foreach ($all_rows as $row) {
            $data_array[] = $row->id;
        }
        foreach ($data_array as $key => $value) {
            
            if ($flag) {
                $next_record_id = $value;
                $flag = 0;
            }
            
            if ($value == $id) {
                $flag = 1;
            }
        }
        
        $position_data = array();
        $position_data['current_position'] = $position;
        $position_data['next_record_id'] = $next_record_id;
        $position_data['total_rows'] = $total_rows;
        
        return $position_data;
    }
    
    /*
     * Get additonal data for edit view
     */
    protected function getAdditionalData($id)
    {}
    
    /*
     * get unique number for auto_inc_number field
     */
    public function checkUniqueNumber()
    {
        $rowId = $this->input->post('rowId');
        $number = $this->input->post('number');
        $fieldName = $this->input->post('fieldName');
        $tableName = $this->input->post('tableName');
        $response = $this->dashboard_model->checkUniqueNumber($number, $rowId, $tableName, $fieldName);
        echo json_encode($response);
    }
    
    /*
     * if a view is ajax base then first insert a row then redirect to edit viwe
     */
    protected function ajaxBasedView($id, $tableName, $editPath)
    {
        // sample code
        
        /*
         * if($id == 0){
         * $insertData = array(
         * 'is_saved' => 0
         * );
         * $this->db->insert($tableName,$insertData);
         * $insert_id = $this->db->insert_id();
         * $url = base_url().$editPath."/".$insert_id;
         * redirect($url);
         * }
         */
    }

    public function getTrashBtnStatus()
    {
        $response = array(
            'showTrashBtn' => 0
        );
        
        $tableName = $this->input->post('table_name');
        if (empty($tableName)) {
            $tableName = $this->current_class_name;
        }
        
        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . $tableName . ".xml";
        
        $this->config->load('dashboard_override', FALSE, TRUE);
        
        $xmlData = $this->xml_parsing();
        
        $data['xmlData'] = $xmlData;
        
        $primary_key = (string) $xmlData['primary_key'];
        $shared_status = intval($xmlData['shared_status']);
        
        if (isset($shared_status) and $shared_status == 1) {
            $this->account_id = get_default_account_id();
        } else {
            $this->account_id = get_account_id();
        }
        
        $additionalCondition = $this->db_getWhereCondition($shared_status, array());
        $tableName = $this->tablePrefix . $tableName;
        $showTrashBtn = $this->dashboard_model->getTrashBtnStatus($tableName, $additionalCondition);
        if ($showTrashBtn) {
            $response['showTrashBtn'] = 1;
        }
        echo json_encode($response);
    }
    
    /*
     * function for pagination in metrov5
     *
     */
    protected function _getPaginationConfigMetroV5($per_page, $total_items_count, $trash, $controller_sub_folder)
    {
        $config['uri_segment'] = 4;
        $config['attributes'] = array(
            'class' => 'm-datatable__pager-link m-datatable__pager-link-number'
        );
        $config['base_url'] = site_url() . '/' . $controller_sub_folder . '/' . $this->current_class_name . "/" . $this->config->item("listing_view");
        if (isset($trash) and $trash) {
            $config['base_url'] = site_url() . '/' . $controller_sub_folder . '/' . $this->current_class_name . "/trash";
        }
        $config['total_rows'] = $total_items_count;
        $config['per_page'] = $per_page;
        
        $config['cur_tag_open'] = '<li><span class="m-datatable__pager-link m-datatable__pager-link-number m-datatable__pager-link--active">';
        $config['cur_tag_close'] = '</span></li>';
        
        $config['first_tag_open'] = '<li class="m-datatable__pager-link m-datatable__pager-link-number">';
        $config['first_tag_close'] = '</li>';
        
        $config['prev_tag_open'] = '<li class="m-datatable__pager-link m-datatable__pager-link-number">';
        $config['prev_tag_close'] = '</li>';
        
        $config['full_tag_open'] = '<ul class="m-datatable__pager-nav">';
        $config['full_tag_close'] = '</ul>';
        
        $config['num_tag_open'] = '<li class="m-datatable__pager-link m-datatable__pager-link-number">';
        $config['num_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="m-datatable__pager-link m-datatable__pager-link--next">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="m-datatable__pager-link m-datatable__pager-link--last">';
        $config['last_tag_close'] = '</li>';
        
        return $config;
    }

    public function check_permitted_fields_are_viewable($fields = null)
    {
        $allFieldsStatus = ''; // 0 = hidden, 1 = visible, 2 = editable
        $ses_data = $this->session->userdata('allowed_tables');
        $tbl_name = $this->tablePrefix . $this->uri->segment(2);
        
        if ($fields == null) {
            
            if ($ses_data && (in_array('*', $ses_data) || in_array($tbl_name, $ses_data))) {
                return true;
            }
            return false;
        }
        
        if (is_array($fields)) {
            
            if (isset($fields['*']) && count($fields) == 1 && $fields['*'] == '0')
                return false;
            
            if (isset($fields['*'])) {
                $allFieldsStatus = $fields['*'];
                unset($fields['*']);
            }
            // get all fields of a specific table
            $fieldsForPermission = $this->Permissions_model->getTableXMLData(  $this->current_class_name);
            // count the fields
            $allfieldsCount = 0;
            foreach($fieldsForPermission as $singleField){
                if( !$singleField['primary'] ){
                    $allfieldsCount++;
                }
            }
            // count the fields which has none permission
            $fieldsCountWhichHasNonePermission = 0;
            foreach($fields as $field){
                if($field == 0){
                    $fieldsCountWhichHasNonePermission++;
                }
            }
           
            if ($allFieldsStatus != 0 && ( $allfieldsCount == $fieldsCountWhichHasNonePermission)){
                return false;
            } 

            return true;            
        }
        
        return false;
    }

    /**
     * ## for listing
     * 
     * @param $not_allowed_tables ##
     *            for edit
     * @param
     *            $userPermissionDataArray
     * @param
     *            $id
     */
    function have_access_permission($caller_function, $not_allowed_tables = '', $userPermissionDataArray = null, $id = 0)
    {
        $deny = false;
        
        $ses_data = $this->session->userdata('allowed_tables');
        
        $tbl_name = $this->tablePrefix . $this->uri->segment(2);
        
        if ($ses_data && $caller_function == 'listing') {
            if ((in_array('*', $ses_data) || in_array($tbl_name, $ses_data)) && ! in_array($tbl_name, (array) $not_allowed_tables)) {                
                $deny = false;
            } else {
                $deny = true;
            }
        } elseif ($ses_data && $caller_function == 'edit') {
            
            // have_add_permision($this->current_class_name) ) &&
            if ((in_array('*', $ses_data) or in_array($tbl_name, $ses_data)) && $this->check_permitted_fields_are_viewable($userPermissionDataArray)) {
                
                $deny = false;
            } else {
                $deny = true;
            }
        }
        
        if ($deny) {
            
            $url = base_url() . "dashboard/home";
            $error_msg['redirect_time'] = $redirect_time = $this->config->item('no_permission_listing_view_redirect');
            header("Refresh: $redirect_time; URL=$url");
            $error_msg['not_show_time_msg'] = false;
            $error_msg['heading'] = dashboard_lang('_ACCESS_DENIED');
            $error_msg['message'] = dashboard_lang('_YOU_HAVE_NO_PERMISSION_TO_ACCESS_THIS_PAGE');
            
            $this->load->view('errors/html/error_403', $error_msg);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Render money formatted value accroding to setttings
     *
     */
    public function formatMoneyFields() {
        
        $value = $this->input->post("value");

        $defaultMoneyFormat = strtolower( $this->config->item("#DEFAULT_MONEY_FORMAT") );
        
        $value = str_replace(",", ".", $value);

        $response = B_form_helper::customeMoneyFormate ( $value );
        
        echo json_encode( ["value" => $response] );
        
    }

    /**
     * check every required field
     * 
     */
    protected function fieldValidation($fieldName, $fieldRequired, &$requiredButNoValue)
    {
        if (stripos($fieldRequired, 'required') !== false) {

            $fieldValue = $this->input->post($fieldName);
            if(strlen(trim($fieldValue))==0 && isset($_POST[$fieldName])){ 

                $controller_sub_folder = $this->config->item('controller_sub_folder');
                $message_success = dashboard_lang("_FILL_UP_REQUIRED_FIELD");
                $this->session->set_flashdata('flash_message', $message_success);
                $this->session->set_userdata('dashboard_application_message_type', 'error');
                $requiredButNoValue = true;
            }
        }
    }

    /**
     * history tab related codes start
     */
    public function renderHistoryTab()
    {
        $data['limit'] =   $this->config->item('#CORE_HISTORY_LOG_SHOW_LIMIT');
        $data['id'] = $this->input->post('id');
        $data['histories']= getAllHistory($this->current_class_name, $data['id'], $data['limit']);
        $data['view'] = "";
        if ( file_exists(FCPATH.'application/views/'.$this->template_name . '/'.$this->current_class_name.'/history-tab/main') ) {
            
            $data['view'] = $this->load->view($this->template_name.'/'.$this->current_class_name.'/history-tab/main', $data, true);
        } else {
            
            $data['view'] = $this->load->view($this->template_name.'/core_'. $this->template_name . '/history-tab/main', $data, true);
        }
        unset( $data['histories'] );

        echo json_encode( $data );
    }

    public function renderHistoryModal ()
    {
        $data['id'] = $this->input->post('id');
        
        $data['histories'] = getAllHistory( $this->current_class_name, $data['id'] );
        $data['ismodal'] = true;
        $data['view'] = "";
        if ( file_exists(FCPATH.'application/views/'.$this->template_name . '/'.$this->current_class_name.'/history-tab/modal.php') ) {
            
            $data['view'] = $this->load->view($this->template_name.'/'.$this->current_class_name.'/history-tab/modal', $data, true);
        } else {
            
            $data['view'] = $this->load->view($this->template_name.'/core_'. $this->template_name . '/history-tab/modal', $data, true);
        }
        unset ($data['histories']);
        
        echo json_encode($data);
    }
    // history tab related code ends here
}

class CommonController extends CI_Controller
{

    protected $account_id;

    function __construct()
    {
        parent::__construct();
        
        $this->account_id = get_default_account_id();
        
        $this->config->load('dashboard');
        $this->config->load('social');
        $this->config->load('dashboard_override', FALSE, TRUE);
        Dashboard_main_helper::init_settings();
        $this->load->model('portal/language_model');
        
        $this->load->helper('language');
        
        // load language
        Dashboard_main_helper::init_languages_config();
        
        $site_language = $this->language_model->get_user_default_language();
        
        // $config_site_language = $this->config->item('site_languages')[$this->config->item('#LANGUAGE_DEFAULT')];
        
        Dashboard_main_helper::init_translation($site_language);
    }
}

/* End of file: Dashboard_Controller.php */
/* Location: application/core/Dashboard_Controller.php */