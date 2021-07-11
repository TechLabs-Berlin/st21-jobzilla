<?php
/*
 * Author: Ashrafuzzaman Sujan
 */

if ( !defined('BASEPATH') ) exit('No direct script access allowed');

class AjaxBasedFormDataSavingModel extends Base_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function saveFormData($table, $fieldName, $fieldValue, $primaryKeyValue, $radioValue=null, $primaryKeyName = "id")
    {
        $return = array();
        $updateArr = array();
        $currentUser = BUserHelper::get_instance();
        $htmlInputType = trim( @$_POST["htmlTag"] );

        // get xml object for table
        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . $table . ".xml";
        libxml_use_internal_errors(true);
        $xmlData = simplexml_load_file($xmlFile);
        $xmlObjectArray = B_form_helper::get_xml_object_array($xmlData);

        $fieldValidation = (string) $xmlObjectArray[$fieldName]['validation'];
        $source = (string) $xmlObjectArray[$fieldName]['source'];
        $subDirectory = (string) $xmlObjectArray[$fieldName]['sub_directory'];
        $fieldType = (string) $xmlObjectArray[$fieldName]['type'];
        $allowedFiletypes = (string) $xmlObjectArray[$fieldName]['allowed_file_types'];
        $validationStatus = $this->checkValidity($table, $fieldName, $fieldValue, $primaryKeyValue, $primaryKeyName, $fieldValidation, $fieldType, $allowedFiletypes, $htmlInputType);
        
        if ($validationStatus['status']) {
            $id = $primaryKeyValue;
            if (strlen(@$_FILES[$fieldName]['name']) > 0) {
                        
                if (isset($source) and $source == "s3") {

                    $fileName = preg_replace("/[^a-zA-Z0-9.]+/", "", $_FILES[$fieldName]['name']);
                    if ( intval($id) == '0' ) {
                        $fileName = time() . "/" . $fileName;
                    }else {
                        $fileName = $id . "/" . $fileName;
                    }
                    
                    if (! empty($subDirectory)) {
                        $fileName = $subDirectory . "/" . $fileName;
                    }
                    $s3Array = $this->Amazon_s3_model->uploadFileToS3($table, $fileName, $fieldName);
                    if ($s3Array['status']) {
                        $fieldValue = $s3Array['filePath'];
                        if (! empty($id)) {
                            $this->Amazon_s3_model->deleteFileFromS3($table, $fieldName, $subDirectory, $id);
                        }
                        $return['fieldType'] = $fieldType;
                        $return['htmlInputType'] = $htmlInputType;
                    } else {
                        $errorMessage = dashboard_lang("_UPLOAD_FAILED");
                        $return['status'] = 0;
                        $return['message'] = $errorMessage;
                        return $return;
                    }
                } else {
                    $return['status'] = 0;
                    $return['message'] = dashboard_lang("_PLEASE_USE_S3_TO_UPLOAD_FILE");
                    return $return;
                }
            }
            if ( strtolower($htmlInputType) === "checkbox" && ( $fieldType == "image" || $fieldType == "file" ) ) {

                // default response message 
                $return["status"] = 0;
                $return["message"] = dashboard_lang("_" . strtoupper($fieldType) . "_DELETION_FAILED");

                // remove file or image from S3
                $status = $this->Amazon_s3_model->deleteFileFromS3($table, $fieldName, $subDirectory, $id);
                $return["filename"] = @$status["filename"];
                $return["filepath"] = @$status["filepath"];

                if ( @$status["status"] ) {

                    // if removed from S3, then also remove from DB
                    $this->db->where("id", $id);
                    $this->db->update($table, array($fieldName => ""));

                    // updated response array
                    $return["status"] = 1;
                    $return["message"] = dashboard_lang("_" . strtoupper($fieldType) . "_SUCCESSFULLY_DELETED");
                }
                // event logging data
                $dataArr = array(
                    $fieldName => $return["filename"]
                );
                // function to add event log
                $this->Events_model->executeTableEntryEvent('delete_file_or_image_entry', $table, $dataArr, $primaryKeyValue);

                // return the response
                return $return;
            }

            $valuesArray = $this->convertValue($xmlObjectArray, $table, $fieldName, $fieldValue, $radioValue);

            $updateArr[$fieldName] = $valuesArray['dbConvertedValue'];
            $this->db->where($primaryKeyName, $primaryKeyValue);
            $status = $this->db->update($table, $updateArr);

            if ($status) {
                $return['status'] = 1;
                $return['message'] = dashboard_lang('_UPDATED_SUCCESSFULLY');
                $return['value'] = $valuesArray['fieldConvertedValue'];
                $return['fieldType'] = $fieldType;
                $return['pathinfo'] = pathinfo( $valuesArray['fieldConvertedValue'] );
                $return['sql'] = $this->db->last_query();
                $updateArr[$fieldName] = $valuesArray['historyValue'];
                if (in_array( $fieldType, array("single_checkbox", "radio", "file", "image") )) $updateArr["fieldType"] = $fieldType;
                $this->Events_model->executeTableEntryEvent('ajax_change_entry', $table, $updateArr, $primaryKeyValue);
            } else {
                $error = $this->db->error();
                $return['status'] = 0;
                $return['message'] = @$error['message'];
                $return['sql'] = $this->db->last_query();
            }
        } else {
            $return = $validationStatus;
        }

        return $return;
    }

    public function checkValidity($table, $fieldName, $fieldValue, $primaryKeyValue, $primaryKeyName, $fieldRequired, $fieldType, $allowedFiletypes, $htmlInputType)
    {
        $response = array(
            'status' => 1,
            'message' => ''
        );

        /**
         * check if required data are coming empty
         */
        if (stripos($fieldRequired, 'required') !== false) {

            if(strlen(trim($fieldValue))==0 ){
                
                $response["status"] = 0;
                $response["message"] = dashboard_lang("_FILL_UP_REQUIRED_FIELD");
            }
        }

         /**
         * check to be uploaded file validity
         */
        if ( ( $fieldType == "file" || $fieldType == "image" ) && strtolower($htmlInputType) != "checkbox") {
            $ext = pathinfo( $_FILES[$fieldName]['name'], PATHINFO_EXTENSION );
            $allowedFiletypesArr = array_filter(explode("|", $allowedFiletypes), "trim");
            if ( ! in_array($ext, $allowedFiletypesArr) && count($allowedFiletypesArr) > 0 ) {
                $response["status"] = 0;
                $errorMessage = $response["message"]? '<br>': '';
                $errorMessage .= dashboard_lang("_FILE_TYPE_IS_NOT_ALLOWED");
                $errorMessage .= "<br>". dashboard_lang("_ALLOWED_FILE_TYPES_ARE") . ' <em><strong>' . implode(', ', $allowedFiletypesArr) . '</strong></em>';
                $response["message"] .= $errorMessage;
            }
        }

        // check if there is any custom validation is required or not
        $method_name = 'validate_' . $table . '_' . $fieldName;
        if (method_exists('Planning_Model', $method_name)) {
            $response = $this->{'validate_' . $table . '_' . $fieldName}($fieldName, $fieldValue, $primaryKeyValue, $primaryKeyName);
        }

        return $response;
    }

    public function convertValue($xmlObjectArray, $table, $fieldName, $fieldValue, $radioValue=null)
    {
        $valuesArray = array();
        $dbConvertedValue = $fieldValue;
        $fieldConvertedValue = $fieldValue;
        $valuesArray['historyValue'] = $fieldValue;
        if ($xmlObjectArray[$fieldName]) {
            $type = (string) $xmlObjectArray[$fieldName]['type'];
            switch ($type) {
                case 'lookup':
                    $key = (string) $xmlObjectArray[$fieldName]['key'];
                    $value = (string) $xmlObjectArray[$fieldName]['value'];
                    $valueArray = explode(',', $value);
                    $ref_table = (string) $xmlObjectArray[$fieldName]['ref_table'];

                    $tempFieldValue = "";
                    $this->db->select($value);
                    $result = $this->db->get_where($ref_table, array( $key => $fieldValue ))->row_array();
                    foreach($valueArray as $i => $dbfield) {
                        $tempFieldValue .= $result[$dbfield];
                        if ( $i > 0 ) $tempFieldValue .= " ".$result[$dbfield];
                    }

                    $valuesArray['historyValue'] = $tempFieldValue;
                    if ( intval( $xmlObjectArray[$fieldName]['is_translated'] ) == 1 ) $valuesArray['historyValue'] = dashboard_lang('_' . strtoupper($tempFieldValue));
                    unset($tempFieldValue);
                    break;
                case 'select':
                    $options = (array) $xmlObjectArray[$fieldName]->option;
                    $valuesArray['historyValue'] = $options[$fieldValue];
                    if( stripos($options[$fieldValue], "_") === 0 ) $valuesArray['historyValue'] = dashboard_lang( strtoupper($options[$fieldValue]) );
                    break;
                case 'radio':
                    $options = (array) $xmlObjectArray[$fieldName]->option;
                    $valuesArray['historyValue'] = $radioValue;
                    break;
                case 'single_checkbox':
                    $valuesArray['historyValue'] = intval( $fieldValue ) > 0? strtolower( dashboard_lang("_CHECKED") ): strtolower( dashboard_lang("_UNCHECKED") );
                    break;
                case 'datetime':
                    $dbConvertedValue = strtotime($fieldValue);
                    if (! empty($dbConvertedValue)) {
                        $detaultDateFormate = $this->config->item("#EDIT_VIEW_DATE_FORMAT");
                        $fieldConvertedValue = date($detaultDateFormate, $dbConvertedValue);
                    }
                    $valuesArray['historyValue'] = @$fieldConvertedValue? $fieldConvertedValue: '';
                    break;
                case 'money':
                    $dbConvertedValue = B_form_helper::customeMoneyToDecimal($fieldValue);
                    if (strlen($dbConvertedValue) == 0) {
                        $fieldConvertedValue = '';
                        $dbConvertedValue = NULL;
                    } else {
                        $fractionDigits = (string) $xmlObjectArray[$fieldName]['fraction_digits'];
                        if (strlen($fractionDigits) > 0) {
                            $fieldConvertedValue = B_form_helper::customeMoneyFormate($dbConvertedValue, $fractionDigits);
                        } else {
                            $fieldConvertedValue = B_form_helper::customeMoneyFormate($dbConvertedValue);
                        }
                    }
                    $valuesArray['historyValue'] = $fieldConvertedValue;
                    break;
                case 'file':
                    $dbConvertedValue = $fieldValue;
                    $fieldConvertedValue = $fieldValue;
                    $valuesArray['historyValue'] = pathinfo( $fieldValue, PATHINFO_BASENAME ) . strtolower( dashboard_lang("_UPLOADED") );
                    break;
                case 'image':
                    $dbConvertedValue = $fieldValue;
                    $fieldConvertedValue = $fieldValue;
                    $valuesArray['historyValue'] = pathinfo( $fieldValue, PATHINFO_BASENAME ) . strtolower( dashboard_lang("_UPLOADED") );
                    break;
                break;
            }
        }

        $valuesArray['dbConvertedValue'] = $dbConvertedValue;
        $valuesArray['fieldConvertedValue'] = $fieldConvertedValue;
        return $valuesArray;
    }
}