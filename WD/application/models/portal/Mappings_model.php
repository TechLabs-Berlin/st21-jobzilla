<?php
/*
 * @author Ashrafuzzaman Sujan
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Mappings_model extends CI_Model
{

    var $_mappingsTable = 'mappings';
    public $_account_id = '';

    public function __construct()
    {
        parent::__construct();
        
        $this->_account_id = get_account_id(1);
        $this->load->model('portal/SynchronizationProcess_Model');
    }

    public function checkUniqueName($name = '')
    {
        if (empty($name)) {
            return false;
        } else {
            
            $this->db->select('*');
            $this->db->where('name', $name);
            $data = $this->db->get('mappings')->result();
            if (sizeof($data) > 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function update_field_data($dataArr, $mapId, $table_name = '')
    {
        // if table name change then delete other table data and new data
        if (! empty($table_name)) {
            $this->db->select('target_table');
            $this->db->where('id', $mapId);
            $this->db->where('is_deleted', 0);
            $data = $this->db->get('mappings')->row();
            if (isset($data->target_table) && ! empty($data->target_table)) {
                if ($data->target_table != $table_name) {
                    $this->db->where('mappings_id', $mapId);
                    $this->db->delete('mapping_fields');
                }
            }
        }
        
        foreach ($dataArr as $row) {
            $dbArr = array(
                'field_name' => $row['field_name'],
                'column_position' => $row['column_position'],
                'conversion_type' => $row['conversion_type'],
                'index_yn' => $row['index_yn'],
                'mandatory_yn' => $row['mandatory_yn'],
                'exact_match_yn' => $row['exact_match_yn']
            );
            
            if (isset($row['column_position']) && $row['column_position'] == '-1') {
                $dbArr['conversion_type'] = ' ';
                $dbArr['index_yn'] = 0;
                $dbArr['mandatory_yn'] = 0;
                $dbArr['exact_match_yn'] = 0;
            }
            
            if (isset($row['conversion_type']) && $row['conversion_type'] != 'input') {
                $dbArr['exact_match_yn'] = 0;
            }
            
            $this->db->select('id');
            $this->db->where('mappings_id', $mapId);
            $this->db->where('field_name', $row['field_name']);
            $query = $this->db->get('mapping_fields');
            if ($query) {
                if ($query->num_rows() > 0) {
                    $dataRow = $query->row();
                    $this->db->where('id', $dataRow->id);
                    $this->db->update('mapping_fields', $dbArr);
                    // delete all values if column position is -1
                    if ($dbArr['column_position'] == '-1') {
                        $this->db->where('mapping_field_id', $dataRow->id);
                        $this->db->delete('conversion_values');
                    }
                } else {
                    $dbArr['mappings_id'] = $mapId;
                    $this->db->insert('mapping_fields', $dbArr);
                }
            }
        }
    }

    public function update_conversion_values($mapping_field_id, $id = 0, $updateArr = array('id'), $check_existing = FALSE)
    {
        if (empty($id)) {
            if ($check_existing) {
                $this->db->where('mapping_field_id', $mapping_field_id);
                $this->db->delete('conversion_values');
            }
            $insertArr = array(
                'mapping_field_id' => $mapping_field_id,
                'is_deleted' => 1
            );
            $this->db->insert('conversion_values', $insertArr);
            return $this->db->insert_id();
        } else {
            $this->db->where('id', $id);
            $result = $this->db->update('conversion_values', $updateArr);
            return $result;
        }
    }

    /*
     * get list of mappings
     */
    public function getMappingList($orderBy = 'name', $direction = 'desc')
    {
        $this->db->select('*');
        $this->db->order_by($orderBy, $direction);
        $mappings = $this->db->get($this->_mappingsTable);
        return $mappings;
    }

    public function importSheet($map_id, $inputFileName)
    {
        try {
            
            $targetTable = '';
            $overwrite = 0;
            $ignoreFirstRow = 1;
            $tabPosition = 0;
            $currentUserId = get_user_id();
            $currentTime = time();
            
            $this->db->select('*');
            $this->db->where('id', $map_id);
            $this->db->where('is_deleted', 0);
            $query = $this->db->get('mappings');
            
            if ($query) {
                
                $mappingData = $query->row();
                $targetTable = $mappingData->target_table;
                $overwrite = $mappingData->overwrite;
                $ignoreFirstRow = $mappingData->ignore_first_row;
                $tabPosition = $mappingData->tab_position;
                $hasIsSavedField = $this->hasIsSavedField($targetTable);
                // get fields
                $this->db->select('*');
                $this->db->where('mappings_id', $map_id);
                $query = $this->db->get('mapping_fields');
                $mappingFields = $query->result();
                
            } else {
                return false;
            }

            $dbAllFields = array();
            $lookupTables = array();
            $lookupTablesMap = array();
            $conversionValuesArray = array();
            
            if (isset($mappingFields) && sizeof($mappingFields) > 0) {
                foreach ($mappingFields as $fieldData) {
                    
                    if ($fieldData->column_position >= 0) {
                        $dbFields = array();
                        $mapping_id = $fieldData->id;
                        $dbFields['id'] = $mapping_id;
                        $dbFields['field_name'] = $fieldData->field_name;
                        $dbFields['column_position'] = $fieldData->column_position;
                        $dbFields['conversion_type'] = $fieldData->conversion_type;
                        $dbFields['mandatory_yn'] = $fieldData->mandatory_yn;
                        if ($overwrite) {
                            $dbFields['index_yn'] = $fieldData->index_yn;
                        }
                        if(!empty($fieldData->conversion_type) && $fieldData->conversion_type == "lookup"){
                            $lookupTables[$fieldData->field_name] = $mapping_id;
                        } elseif (!empty($fieldData->conversion_type) && $fieldData->conversion_type == "input") {
                            $this->db->select('*');
                            $this->db->where('mapping_field_id', $mapping_id);
                            $this->db->where('is_deleted', 0);
                            $query = $this->db->get('conversion_values');
                            $conversionValues = $query->result_array();
                            if(!empty($conversionValues)){
                                $conversionValuesArray[$fieldData->field_name] =  $conversionValues;
                            }                            
                        } elseif (!empty($fieldData->conversion_type) && $fieldData->conversion_type == "datetime") {
                            $this->db->select('*');
                            $this->db->where('mapping_field_id', $mapping_id);
                            $this->db->where('is_deleted', 0);
                            $query = $this->db->get('conversion_values');
                            $conversionValues = $query->row_array();
                            if(!empty($conversionValues)){
                                $conversionValuesArray[$fieldData->field_name] =  $conversionValues;
                            }                            
                        }
                        $dbAllFields[] = $dbFields;
                    }
                }
            }

            // create lookup table maps
            if(!empty($lookupTables)){
                $tempDataArray = array();
                $xmlObjectArray = $this->getFieldsAttributes($targetTable);                
                foreach($lookupTables as $fieldName => $mapping_id){
                    $this->db->select('*');
                    $this->db->where('mapping_field_id', $mapping_id);
                    $this->db->where('is_deleted', 0);
                    $query = $this->db->get('conversion_values');
                    $conversionValues = $query->row_array();
                    if(!empty($conversionValues)){
                        $lookupTableName = $conversionValues['from_value'];
                        $lookupTableFieldName = $conversionValues['to_value'];
                        // get fields attr to do specific operation. e.g ref_tbl shared status
                        $fieldsAttributes = $xmlObjectArray[$fieldName];
                        $reftblSharedStatus = 0;
                        if(isset($fieldsAttributes['reftbl_shared_status'])){
                            $isShared = intval($fieldsAttributes['reftbl_shared_status']);
                            if($isShared == 1){
                                $reftblSharedStatus = 1;
                            }
                        }

                        //  create or reuse the drop-down field map
                        if(empty($tempDataArray[$lookupTableName][$lookupTableFieldName][$reftblSharedStatus])){
                            $lookupTablesMap[$fieldName]["reftabledata"] = $this->getDropDownFieldMap($lookupTableName, $lookupTableFieldName, "id", $reftblSharedStatus);
                            $tempDataArray[$lookupTableName][$lookupTableFieldName][$reftblSharedStatus] = $fieldName;
                        } else{
                            $previousSameFieldName = $tempDataArray[$lookupTableName][$lookupTableFieldName][$reftblSharedStatus];
                            $lookupTablesMap[$fieldName]["reftabledata"] = $lookupTablesMap[$previousSameFieldName]["reftabledata"];
                        }
                        
                        // check eav field or not
                        if($fieldsAttributes['type'] == "eav"){
                            $lookupTablesMap[$fieldName]['eav_field_attr'] = $fieldsAttributes;
                        }

                    }                    
                }
            }

            require_once APPPATH . 'third_party/PHPExcel/IOFactory.php';
            
            if (! file_exists($inputFileName)) {
                $message = dashboard_lang("_ERROR_NO_EXCEL_FILE_FOUND");
                eventLog_helper::setLog('data_import_log', 'error_log', 'importSheet', 'file_error', $message);
                echo dashboard_lang("_ERROR_NO_EXCEL_FILE_FOUND");
                return false;
            }
            
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
            $totalRows = $objPHPExcel->setActiveSheetIndex($tabPosition)->toArray(null, true, true, true);
            $totalRowsNumber = sizeof($totalRows);            

            if (empty($ignoreFirstRow)) {
                $counter = 1;
            } else {
                $counter = 2;
            }
            
            
            $totalRow = $totalRowsNumber;
            $totalUpdatdRow = 0;
            
            for ($counter; $counter <= $totalRowsNumber; $counter ++) {
                
                $dbRowData = array();
                $dbIndexData = array();
                $tempEavData = array();
                
                $allowRow = true;
                foreach ($dbAllFields as $field) {
                    $column = $field['column_position'];
                    $cellValue = $objPHPExcel->setActiveSheetIndex($tabPosition)
                        ->getCellByColumnAndRow($column, $counter)
                        ->getFormattedValue();

                    $resultData = self::convertValue($field, $cellValue, $targetTable, $conversionValuesArray, $lookupTablesMap);
                    
                    if($resultData['status']){
                        $convertedValue = $resultData['modifiedValue'];
                        if (empty($field['mandatory_yn'])) {
                            $dbRowData[$field['field_name']] = $convertedValue;
                            if (isset($field['index_yn']) && $field['index_yn']) {
                                $dbIndexData[$field['field_name']] = $convertedValue;
                            }
                        } else {
                            if ($convertedValue != '') {
                                $dbRowData[$field['field_name']] = $convertedValue;
                                if (isset($field['index_yn']) && $field['index_yn']) {
                                    $dbIndexData[$field['field_name']] = $convertedValue;
                                }
                            }
                        }

                        // reset field data for eav field
                        if(!empty($lookupTablesMap[$field['field_name']]["eav_field_attr"])){
                            $tempEavData[$field['field_name']] = $dbRowData[$field['field_name']];
                            $dbRowData[$field['field_name']] = 0;
                        }
                    } else{
                        $allowRow = false;
                    }
                    
                }

                // set is_saved field value if needed
                if($hasIsSavedField){
                    $dbRowData['is_saved'] = 1;
                }

                if (sizeof($dbRowData) > 0 && $allowRow) {
                    $id = 0;
                    if (sizeof($dbIndexData) > 0) {
                        $this->db->select('id');
                        $this->db->where($dbIndexData);
                        $this->db->where("account_id", $this->_account_id);
                        $existingData = $this->db->get($targetTable)->result_array();
                        if (sizeof($existingData) > 0) {
                            $id = $existingData[0]['id'];
                            $this->db->where('id', $id);
                            // set data to additional fields
                            // $dbRowData['modified_at'] = $currentTime;
                            // $dbRowData['modified_by'] = $currentUserId;
                            $this->db->update($targetTable, $dbRowData);
                        } else {
                            // $dbRowData['created_at'] = $currentTime;
                            // $dbRowData['created_by'] = $currentUserId;
                            // $dbRowData['modified_at'] = $currentTime;
                            // $dbRowData['modified_by'] = $currentUserId;
                            $dbRowData['account_id'] = $this->_account_id;
                            $this->db->insert($targetTable, $dbRowData);
                            $id = $this->db->insert_id();
                        }
                    } else {
                        // $dbRowData['created_at'] = $currentTime;
                        // $dbRowData['created_by'] = $currentUserId;
                        // $dbRowData['modified_at'] = $currentTime;
                        // $dbRowData['modified_by'] = $currentUserId;
                        $dbRowData['account_id'] = $this->_account_id;                        
                        $this->db->insert($targetTable, $dbRowData);
                        $id = $this->db->insert_id();
                    }
                    
                    $totalUpdatdRow += 1;

                    foreach ($dbAllFields as $field) {
                        // insert data into intermediate tables
                        if(!empty($lookupTablesMap[$field['field_name']]["eav_field_attr"])){
                            $refTableIds = $tempEavData[$field['field_name']];
                            $insert_table = (string) $lookupTablesMap[$field['field_name']]["eav_field_attr"]["insert_table"];
                            $insert_table_forign_key = (string) $lookupTablesMap[$field['field_name']]["eav_field_attr"]["insert_table_forign_key"];
                            $insert_table_reference_key = (string) $lookupTablesMap[$field['field_name']]["eav_field_attr"]["insert_table_reference_key"];
                            foreach($refTableIds as $key => $reference_key_id){
                                $insertArray = array($insert_table_forign_key => $id, $insert_table_reference_key => $reference_key_id, "is_deleted" => 0, "account_id" => $this->_account_id);
                                $this->db->where($insertArray);
                                $insertTableArray = $this->db->get($insert_table)->row_array();
                                if(!is_array($insertTableArray)){
                                    $this->db->insert($insert_table, $insertArray);
                                }                                
                            }                            
                        }
                    }
                }
                // update the prograss-bar status                
                $successMsg = dashboard_lang("_DATA_IS_PROCESSING");
                $this->SynchronizationProcess_Model->updateSynchronizationStatus($totalRowsNumber, $counter, $deleteFile=false, $successMsg);
            }
            
            return array(
                'success' => 1,
                'msg' => '',
                'total_updated_row' => $totalUpdatdRow,
                'total_row' => $totalRow
            );
        } catch (Exception $exception) {
            
            return array(
                'success' => 0,
                'msg' => $exception->getMessage(),
                'total_updated_row' => $totalUpdatdRow,
                'total_row' => $totalRow
            )
            ;
        }
    }

    protected function convertValue($fieldArray, $value, $tableName, $conversionValuesArray, $lookupTablesMap)
    {
        $returnArr = array();
        $mappingFieldId = $fieldArray['id'];
        $conversionType = $fieldArray['conversion_type'];
        $fieldName = $fieldArray['field_name'];

        if ($value == '') {
            $errorType = 'required';
            $evalResult = $this->events_model->checkDataValidation($tableName, $conversionType, $errorType, $value);
            
            $returnArr['status'] = $evalResult['valid'];
            $returnArr['modifiedValue'] = $evalResult['modifiedValue'];
            return $returnArr;
        } else {
            
            if ($conversionType == 'select') {
                
            } elseif ($conversionType == 'input') {               
                // $this->db->select('*');
                // $this->db->where('mapping_field_id', $mappingFieldId);
                // $this->db->where('is_deleted', 0);
                // $query = $this->db->get('conversion_values');
                // $conversionValues = $query->result_array();
                $conversionValues = $conversionValuesArray[$fieldName];
                $valueMatched = false;
                if (sizeof($conversionValues) > 0) {
                    
                    foreach ($conversionValues as $values) {
                        if ($values['from_value'] == $value) {
                            $value = $values['to_value'];
                            $valueMatched = true;
                        }
                    }
                    
                    $errorType = 'matched';
                    $evalResult = $this->events_model->checkDataValidation($tableName, $conversionType, $errorType, $value, $valueMatched);      
                    $returnArr['status'] = $evalResult['valid'];
                    $returnArr['modifiedValue'] = $evalResult['modifiedValue'];
                    return $returnArr;
                }
            } elseif ($conversionType == 'datetime') {
                
                $sheetDateFormateArr = array();
                $dateArr = array();
                
                // $this->db->select('*');
                // $this->db->where('mapping_field_id', $mappingFieldId);
                // $this->db->where('is_deleted', 0);
                // $query = $this->db->get('conversion_values');
                // $conversionValues = $query->row();
                $conversionValues = $conversionValuesArray[$fieldName];
                // echo $this->db->last_query();
                // var_dump($conversionValues); die();
                if (sizeof($conversionValues) > 0) {
                    $sheetDateFormate = $conversionValues['from_value'];
                    $sheetDateArr = str_split($sheetDateFormate);
                    $separator = '';
                    if (in_array('/', $sheetDateArr)) {
                        $dateArr = explode('/', $value);
                        $sheetDateFormateArr = explode('/', $sheetDateFormate);
                        $separator = '/';
                    } elseif (in_array('.', $sheetDateArr)) {
                        $dateArr = explode('.', $value);
                        $sheetDateFormateArr = explode('.', $sheetDateFormate);
                        $separator = '.';
                    } elseif (in_array('-', $sheetDateArr)) {
                        $dateArr = explode('-', $value);
                        $sheetDateFormateArr = explode('-', $sheetDateFormate);
                        $separator = '-';
                    } elseif (in_array(' ', $sheetDateArr)) {
                        $dateArr = explode(' ', $value);
                        $sheetDateFormateArr = explode(' ', $sheetDateFormate);
                        $separator = ' ';
                    }
                    
                    $totalSeparator = substr_count($value, $separator);
                    if($totalSeparator == 2){
                        $valueMatched = true;
                    } else{
                        $valueMatched = false;
                    }
                    $errorType = 'matched';
                    $evalResult = $this->events_model->checkDataValidation($tableName, $conversionType, $errorType, $value, $valueMatched);
                    if($evalResult['valid'] == false){
                        $returnArr['status'] = $evalResult['valid'];
                        $returnArr['modifiedValue'] = $evalResult['modifiedValue'];
                        return $returnArr;
                    }
                    
                    $ddKey = array_search('dd', $sheetDateFormateArr);
                    $mmKey = array_search('mm', $sheetDateFormateArr);
                    $yyyyKey = array_search('yyyy', $sheetDateFormateArr);
                    $newDateFormate = @$dateArr[$ddKey] . '-' . @$dateArr[$mmKey] . '-' . @$dateArr[$yyyyKey];
                    $value = strtotime($newDateFormate);
                    
                } else {
                    $value = "";
                }
            } elseif ($conversionType == 'money') {
                $validString = false;
                if(preg_match('/^[0-9.,]+$/', $value) && strlen($value) > 10){
                    $validString = true;
                } else{
                    $validString = false;
                }
                $errorType = 'only_num';
                $evalResult = $this->events_model->checkDataValidation($tableName, $conversionType, $errorType, $value, $validString);
                if($evalResult['valid'] == false){
                    $returnArr['status'] = $evalResult['valid'];
                    $returnArr['modifiedValue'] = $evalResult['modifiedValue'];
                    return $returnArr;
                }
                $value = mappings_helper::stringToDecimal($value);
            } elseif ($conversionType == 'roundint') {
                $validString = false;
                if(preg_match('/^[0-9.,]+$/', $value) && strlen($value) > 10){
                    $validString = true;
                } else{
                    $validString = false;
                }
                $errorType = 'only_num';
                $evalResult = $this->events_model->checkDataValidation($tableName, $conversionType, $errorType, $value, $validString);
                if($evalResult['valid'] == false){
                    $returnArr['status'] = $evalResult['valid'];
                    $returnArr['modifiedValue'] = $evalResult['modifiedValue'];
                    return $returnArr;
                }
                //replace comma with dot (.) 
                $value = mappings_helper::roundingInt($value);
                //then do rounding 
            } elseif($conversionType == "lookup"){                
                $valueMatched = false;
                $valueArray = [];
                $modifiedValueArray = [];
                $eventResult = false;
                // check eav field or not
                if(!empty($lookupTablesMap[$fieldName]["eav_field_attr"])){
                    $valueArray = explode(",", $value);
                } else{
                    $valueArray[] = $value;
                }

                foreach($valueArray as $key => $value){

                    if(!empty($lookupTablesMap[$fieldName]["reftabledata"][$value])){
                        $value = $lookupTablesMap[$fieldName]["reftabledata"][$value];
                        $valueMatched = true;
                        $eventResult = true;
                    }
    
                    $errorType = 'matched';
                    $evalResult = $this->events_model->checkDataValidation($tableName, $conversionType, $errorType, $value, $valueMatched);   
                    $modifiedValueArray[] =  $evalResult['modifiedValue'];                    
                }

                $returnArr['status'] = $eventResult;
                // check eav field or not
                if(!empty($lookupTablesMap[$fieldName]["eav_field_attr"])){
                    $returnArr['modifiedValue'] = $modifiedValueArray;
                } else{
                    $returnArr['modifiedValue'] = $modifiedValueArray[0];
                }
                
                return $returnArr;
            }
            else {
                // return $value;
            }
            
            $returnArr['status'] = true;
            $returnArr['modifiedValue'] = $value;
            return $returnArr;
        }
    }

    public function updateColumnPositions($id, $filePath, $tabPosition = 0, $ignoreFirstRow = 1)
    {
        if (empty($filePath)) {
            return false;
        } else {
            
            // read the file
            
            require_once APPPATH . 'third_party/PHPExcel/IOFactory.php';
            
            if (! file_exists($filePath)) {
                $message = dashboard_lang("_ERROR_NO_EXCEL_FILE_FOUND");
                eventLog_helper::setLog('data_import_log', 'error_log', 'importSheet', 'file_error', $message);
                echo dashboard_lang("_ERROR_NO_EXCEL_FILE_FOUND");
                return false;
            }
            
            $inputFileType = PHPExcel_IOFactory::identify($filePath);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($filePath);
            $totalRows = $objPHPExcel->setActiveSheetIndex($tabPosition)->toArray(null, true, true, true);
            
            $columns = $totalRows[1];
            
            $insertArr = array();
            $existingColumns = array();
            $counter = 0;
            
            foreach ($columns as $key => $value) {
                $data = array();
                $data['mappings_id'] = $id;
                if (empty($ignoreFirstRow)) {
                    $data['column_name'] = '';
                } else {
                    $data['column_name'] = $value;
                }
                $data['column_position'] = $counter;
                $data['column_char'] = $key;
                $insertArr[] = $data;
                $counter ++;
            }
            
            $this->db->select('*');
            $this->db->where('mappings_id', $id);
            $this->db->where('is_deleted', 0);
            $existingData = $this->db->get('column_positions')->result();
            if (sizeof($existingData) > 0) {
                foreach ($existingData as $row) {
                    $existingColumns[$row->id] = $row->column_name;
                    $this->db->where('id', $row->id);
                    $this->db->update('column_positions', array(
                        'is_deleted' => 1
                    ));
                }
            }
            
            foreach ($insertArr as $row) {
                
                if (in_array($row['column_name'], $existingColumns)) {
                    $key = array_search($row['column_name'], $existingColumns);
                    $this->db->where('id', $key);
                    $this->db->update('column_positions', array(
                        'is_deleted' => 0
                    ));
                } else {
                    $this->db->insert('column_positions', $row);
                }
            }
        }
    }

    public function getColumnPositions($id)
    {
        $this->db->select('*');
        $this->db->where('mappings_id', $id);
        $this->db->where('is_deleted', 0);
        $this->db->order_by("column_char", "asc");
        return $this->db->get('column_positions')->result();
    }

    public function getTargetTableFields($targetTable, $id)
    {
        $resultArray = array();
        $this->db->select('*');
        $this->db->where('mappings_id', $id);
        $this->db->where('is_deleted', 0);        
        $dataObj = $this->db->get('mapping_fields')->result();
        $tempDataMap = array();
        if(!empty($dataObj)){
            foreach($dataObj as $fields){
                $tempDataMap[$fields->field_name] = $fields;
            }
        }
        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR ."{$targetTable}.xml";
        libxml_use_internal_errors(true);

        $xmlData = simplexml_load_file($xmlFile);
        $xmlObjectArray = B_form_helper::get_xml_object_array($xmlData);
        foreach($xmlObjectArray as $xmlFields){            
            $fieldName = (string) $xmlFields['name'];
            $resultArray[] = $tempDataMap[$fieldName];
        }
        return $resultArray;
    }

    public function get_all_tables(){
        $this->db->select('name');
        $this->db->where('is_deleted', 0);
        $this->db->order_by("name", "asc");
        return $this->db->get('views')->result_array();
    }

    public function getDropDownFieldMap($tableName, $keyFieldName="id", $valueFieldName="", $isShared=0){
        $this->db->where('is_deleted', 0);
        if($isShared){
            $this->db->where('account_id', 1);
        } else{            
            $this->db->where('account_id', $this->_account_id);
        }        
        $statusData = $this->db->get($tableName)->result_array();
        // echo $this->db->last_query(); die();
        $mapForDropDown = array();
        foreach ($statusData as $row){
            if(empty($valueFieldName)){
                $mapForDropDown[$row[$keyFieldName]] = $row;
            } else{
                $mapForDropDown[$row[$keyFieldName]] = @$row[$valueFieldName];
            }            
        }
        return $mapForDropDown;
    }

    function getFieldsAttributes($tableName){
        $isShared = 0;
        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR ."{$tableName}.xml";              
        libxml_use_internal_errors(true);
        $xmlData = simplexml_load_file($xmlFile);
        if ($xmlData === false) {
            /*
             * xml parsing error, shown error message in below
             */
            foreach (libxml_get_errors() as $error) {
                echo dashboard_lang('_DASHBOARD_XML_PERSING_ERROR') . "\t", $error->message;
            }
            exit();
        }
        
        $xmlObjectArray = B_form_helper::get_xml_object_array($xmlData);

        return $xmlObjectArray;
    }

    function hasIsSavedField($targetTable){
        return $this->db->field_exists ('is_saved', $targetTable);
    }
}
