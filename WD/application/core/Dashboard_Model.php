
<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    
    exit('No direct script access allowed');

class Dashboard_model extends CI_Model
{

    public $tablePrefix;
    private $rangeSuffix ='_498756425end';

    function __construct()
    {
        parent::__construct();
        $this->tablePrefix = $this->config->item("prefix");
    }

    /*
     * perform get data
     */
    function get($tableNameSelectFields, $selectField, $whereArray = array())
    {
        $this->db->select($selectField);
        
        if (is_array($whereArray) && count($whereArray) > 0) {
            
            $this->db->where($whereArray);
        }
        
        $selectData = $this->db->get($tableNameSelectFields);
        
        return $selectData;
    }

    function get_search_key($maintableName, $tableFieldName, $tableFieldType="")
    {
        $get_key = $this->session->userdata($maintableName . "_" . $tableFieldName);
        if (strlen($get_key) > 0) {
            if($tableFieldType == "datetime"){
                $get_key = strtotime($get_key);
            } 
            return $this->db->escape_str($get_key);
        } else {
            
            return '';
        }
    }

    function search_multi_select_own_field()
    {
        $field_name = $this->input->get('field_name');
        if( stripos($field_name, $this->rangeSuffix ) ){
            $field_name = str_replace($this->rangeSuffix, '',  $this->input->get('field_name') );
        }
        $table_name = $this->tablePrefix . $this->input->get('table_name');
        $search = $this->db->escape_str($this->input->get('search'));
        $lookup = $this->input->get('lookup');
        $datetime = $this->input->get('datetime');
        $input_type = $this->input->get('input_type');
        $data_select = $this->input->get('data_select');
        
        $max_text = $this->config->item("max_search_text");
        
        $shared_status = 0;
        $is_translated = 0;
        $additional_condition = $range_end_value = "";
        
        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . $table_name . ".xml";
        libxml_use_internal_errors(true);
        $xmldata = simplexml_load_file($xmlFile);
        if ($xmldata === false) {
            /*
             * xml parsing error, shown error message in below
             */
            foreach (libxml_get_errors() as $error) {
                echo dashboard_lang('_DASHBOARD_XML_PERSING_ERROR') . "\t", $error->message;
            }
            exit();
        } else {
            $xmlObjectArray = B_form_helper::get_xml_object_array($xmldata);
            $additional_condition = $xmlObjectArray[$field_name]['where_and'];
            $is_translated = $xmlObjectArray[$field_name]['is_translated'];
            $shared_status = (int)$xmldata['shared_status'];
            $use_operator= intval( $xmlObjectArray[$field_name]['use_operator'] );

            if($input_type=='number' || $input_type=='datetime' || $input_type=='money'){
                $use_operator = 1;
            }

            //add look-up condition if found
            $filter_by_field = (int) $xmldata['filter_by_field'];
            
            if ($filter_by_field) {
            
                // get the current user
                $current_user = BUserHelper::get_instance();
            
                // get the related field
                $related_field = (string) $xmldata['related_field'];
            
                // get the external field
                $external_field = (string) $xmldata['external_field'];
            
                // make the additional condition in AND format
                if ($current_user->user->{$external_field}) {
                    $conditionValue = $current_user->user->$external_field;
                    if(empty($additional_condition)){
                        $additional_condition .= $related_field.":".$conditionValue;
                    } else{
                        $additional_condition .= ",".$related_field.":".$conditionValue;
                    }
                }
            }
        }
        
        if ($shared_status) {
            $account_id = get_default_account_id();
        } else {
            $account_id = get_account_id();
        }

        
        $response = array();       
          
        if(!empty($use_operator) && $use_operator = 1){
            $fieldSessionData = $this->get_search_key($table_name, $field_name);
            $range_end_value = $this->get_search_key($table_name, $field_name.$this->rangeSuffix);
            $selectedOperator = getSelectedOperator($field_name);
            if(!empty($fieldSessionData) && $selectedOperator =='><'){
                $range_start_value = $fieldSessionData;
            }elseif(!empty($fieldSessionData)){
                echo json_encode($response);
                exit();
            }
        } else{
            $response[0]['text'] = dashboard_lang("_EMPTY");
            $response[0]['value'] = "empty";
        }
        
        if($input_type == "single_checkbox"){
            $response[0] = array( "text" => dashboard_lang("_YES"), "value" => 1 );
            $response[] = array( "text" => dashboard_lang("_NO"), "value" => 0 );
            echo json_encode($response);

        }elseif ($lookup == '1') {
            
            $ref_table = $this->tablePrefix . $this->input->get('ref_table');
            $data_key = $this->input->get('data_key');
            $data_value = $this->input->get('data_value');
            $data_value_array = explode(',', $data_value);
            $data_value = $data_value_array[0];
            
            if ($input_type == 'eav') {
                
                $attribute_table_name = $this->input->get('attr_table');
                
                $this->db->select('DISTINCT(ref_tbl.eav_object_data)');
                $this->db->from($ref_table . ' ' . 'AS ref_tbl');
                
                $this->db->like('ref_tbl.eav_object_data', $search, 'both');
                $this->db->where('ref_tbl.is_deleted', '0');
                $this->db->where('ref_tbl.account_id', $account_id);
                $this->db->limit(10);
                $all_result = $this->db->get()->result_array();
                
                $data = array();
                
                foreach ($all_result as $result) {
                    
                    if (strlen($result['eav_object_data']) > $max_text) {
                        $data['value'] = substr($result['eav_object_data'], 0, $max_text);
                    } else {
                        $data['value'] = $result['eav_object_data'];
                    }
                    
                    $data['text'] = $result['eav_object_data'];
                    
                    $response[] = $data;
                }
                
                if (! empty($attribute_table_name)) {
                    
                    $this->db->select('DISTINCT(ref_tbl.eav_object_attribute)');
                    $this->db->from($ref_table . ' ' . 'AS ref_tbl');
                    
                    $this->db->like('ref_tbl.eav_object_attribute', $search, 'both');
                    $this->db->where('ref_tbl.is_deleted', '0');
                    $this->db->where('ref_tbl.account_id', $account_id);
                    
                    $this->db->limit(10);
                    $all_result = $this->db->get()->result_array();
                    
                    $data = array();
                    
                    foreach ($all_result as $result) {
                        
                        if (strlen($result['eav_object_attribute']) > $max_text) {
                            $data['value'] = substr($result['eav_object_attribute'], 0, $max_text);
                        } else {
                            $data['value'] = $result['eav_object_attribute'];
                        }
                        
                        $data['text'] = $result['eav_object_attribute'];
                        
                        $response[] = $data;
                    }
                }
            } else {
                
                $reftbl_shared_status = intval($xmlObjectArray[$field_name]['reftbl_shared_status']);
                
                if (isset($reftbl_shared_status) and $reftbl_shared_status == 1) {
                    $ref_tbl_account_id = get_default_account_id();
                } elseif ((isset($reftbl_shared_status) and $reftbl_shared_status == 2)) {
                    $ref_tbl_account_id = 0;
                } else {
                    $ref_tbl_account_id = get_account_id();
                }
                
                $this->db->select("DISTINCT(ref_tbl.$data_value)");
                $this->db->from($ref_table . ' ' . 'AS ref_tbl');
                $this->db->join($table_name, $table_name . "." . $field_name . "=ref_tbl." . $data_key . " AND $table_name.is_deleted = 0 AND $table_name.account_id = $account_id");
                $this->db->like('ref_tbl.' . $data_value, $search, 'both');
                $conditional_data = array(
                    'ref_tbl.is_deleted' => '0',
                    'ref_tbl.account_id' => $ref_tbl_account_id
                );
                
                if (! empty($additional_condition)) {
                    $additional_and = explode(',', $additional_condition);
                    foreach ($additional_and as $id => $text) {
                        $filter_array = explode(':', $text);
                        $conditional_data[$filter_array[0]] = $filter_array[1];
                    }
                }
                $this->db->where($conditional_data);
                
                $this->db->limit(10);
                
                $all_result = $this->db->get()->result_array();
                $data = array();

                
                foreach ($all_result as $result) {
                    
                    if (strlen($result[$data_value]) > $max_text) {
                        $data['value'] = substr($result[$data_value], 0, $max_text);
                    } else {
                        $data['value'] = $result[$data_value];
                    }
                    if ($is_translated == 1) {
                        $data['text'] = dashboard_lang(strtoupper($result[$data_value]));
                    } else {
                        $data['text'] = $result[$data_value];
                    }
                    
                    $response[] = $data;
                }
            }
            echo json_encode($response);
        } else 
            if ($datetime == '1') {
                
                $this->db->select("DISTINCT ($field_name)");
                $this->db->from($table_name);
                $this->db->where('is_deleted', 0);
                $this->db->where('account_id', $account_id);
                if( strlen(@$range_start_value) ){
                    $this->db->where("{$field_name} >=", $range_start_value );
                }elseif(strlen($range_end_value)){
                    $this->db->where("{$field_name} <=", $range_end_value );
                }
                $all_result = $this->db->get()->result_array();
                
                $data = array();
                $default_date_format = $this->config->item('#DEFAULT_DATE_FORMAT');
                foreach ($all_result as $result) {
                    
                    if (strlen($search) == '0') {
                        
                        $formatted_date = date("$default_date_format", $result[$field_name]);
                        $data['value'] = $result[$field_name] . "_" . $formatted_date;
                        $data['text'] = $formatted_date;
                        $response[] = $data;
                    } else {
                        $formatted_date = date("$default_date_format", $result[$field_name]);
                        $check_match = stripos($formatted_date, $search);
                        
                        if ($check_match !== false) {
                            
                            $data['value'] = $result[$field_name] . "_" . $formatted_date;
                            $data['text'] = $formatted_date;
                            $response[] = $data;
                        }
                    }
                }
                
                echo json_encode($response);
            } else 
                if ($data_select == '1') {
                    
                    $data = array();
                    
                    $this->db->select("DISTINCT (`$field_name`)");
                    $this->db->where('is_deleted', 0);
                    $this->db->where('account_id', $account_id);
                    $all_result = $this->db->get($table_name)->result_array();
                    $data_select_options = $this->input->get('data_select_options');
                    
                    $all_exploded_options = explode(',', $data_select_options);
                    for ($count = 0; $count < sizeof($all_exploded_options); $count ++) {
                        
                        if ($all_exploded_options[$count] != '') {
                            
                            $option_parse = explode('@', $all_exploded_options[$count]);
                            if (strlen($option_parse[1]) > 0) {
                                $options_array[$option_parse[1]] = $option_parse[0];
                            }
                        }
                    }
                    
                    foreach ($all_result as $result) {
                        
                        if (isset($options_array[$result[$field_name]]) and strlen($options_array[$result[$field_name]]) > 0) {
                            
                            $data['value'] = $result[$field_name];
                            $data['text'] = dashboard_lang($options_array[$result[$field_name]]);
                            
                            if (strlen($search) > 0) {
                                
                                $match_search = strpos(strtolower($data['text']), strtolower($search));
                                if ($match_search !== false) {
                                    
                                    $response[] = $data;
                                }
                            } else {
                                
                                $response[] = $data;
                            }
                        }
                    }
                    
                    echo json_encode($response);
                } else {
                    
                    $this->db->select("DISTINCT (`$field_name`)");
                    $this->db->where('is_deleted', 0);
                    $this->db->where('account_id', $account_id);
                    $this->db->like($field_name, $search, "both");
                    $this->db->limit(10);
                    $all_result = $this->db->get($table_name)->result_array();
                    
                    $data = array();
                    
                    foreach ($all_result as $result) {
                        
                        if (strlen($result[$field_name]) > 0) {
                            
                            if (strlen($result[$field_name]) > $max_text) {
                                $data['value'] = substr($result[$field_name], 0, $max_text);
                            } else {
                                $data['value'] = $result[$field_name];
                            }
                            
                            if ($input_type == 'money') {
                                
                                $format_money = strtolower($this->config->item('#DEFAULT_MONEY_FORMAT'));
                                $data['text'] = $result[$field_name];
                                if ($format_money == 'eu') {
                                    
                                    $data['text'] = str_replace(".", ',', $result[$field_name]);
                                }
                                if ($format_money == 'us') {
                                    
                                    $data['text'] = str_replace(",", '.', $result[$field_name]);
                                }
                            } else {
                                
                                $data['text'] = $result[$field_name];
                            }
                            
                            $response[] = $data;
                        }
                    }
                    
                    echo json_encode($response);
                }
    }

    /*
     * perform data listing
     */
     function listing($xmlData, $tableField = array("id"), $condition = "", $multi_select_array = array(), $allOrderBy, $orderByDirection, $orderByField, $limit_start = 0, $limit_items = 10, $additional_condition = '', $trash = false)
    {      
        $tableAttributes = $xmlData['table-attributes'];
        $tableNameForSession = $tableAttributes['name'];
        $disableGroup = (int) @$tableAttributes['disable_group'];
        $maintableName = $this->config->item("prefix") . $tableAttributes['name'];
        $condition = $this->db->escape_str($condition);
        $linked_queries = array();
        $primary_key = $tableAttributes['primary_key'];
        
        $optr = $this->config->item('default_quoted_indetifier');
        
        if (strlen($orderByDirection)) {
            
            $object = new stdClass();
            $object->direction = $orderByDirection;
            $array_key = (string) $orderByField;
            $allOrderBy = array_merge(array(
                
                $array_key => $object
            ), $allOrderBy);
        }
        
        foreach ($tableField as $fieldName) {
        
            if (isset($xmlData[$fieldName])) {
        
                $value = $xmlData[$fieldName];
                $tableFieldName = (string) $value['name'];
                $exact_match = intval($value['exact_match']);
                $use_table_fields = intval($value['use_table_fields']);
                $use_operator= intval( $value['use_operator'] );
                if($value['type'] == 'number' || $value['type'] == 'money' || $value['type'] == 'datetime'){
                    $use_operator =1;
                }
                $lookup = false;
                
                if (isset($value['type']) && $value['type'] == 'lookup' && $use_table_fields != 1) {
                    $tableNameJoin = $this->config->item("prefix") . (string) $value['ref_table'];
                    $reference_table_name = $tableFieldName . '_ref';
                    $joinTableKey = (string) $value['key'];
                    $joinTableValue = (string) $value['value'];
                    $fieldSeperator = (string) $value['allow_seperator'];

                    if ( strlen($fieldSeperator) == '0' ) {

                        $fieldSeperator = "";
                    }
                    
                    $value_array = explode(',', $joinTableValue);
                    $joinTableValue = $value_array[0];
                    $joinCondition = $reference_table_name . '.' . $joinTableKey . ' = ' . $maintableName . '.' . $tableFieldName;
                    
                    if (count($value_array) > 1) {
                        $selectValue = "CONCAT_WS(" ."' ".$fieldSeperator." '," . "{$reference_table_name}.{$value_array[0]}, {$reference_table_name}.{$value_array[1]}) AS {$tableFieldName}";
                    } else {
                        $selectValue = "{$reference_table_name}.{$joinTableValue} AS {$tableFieldName}";
                    }
                    
                    $this->db->select($maintableName . "." . $tableFieldName . " as " . $tableFieldName . "_fkid");
                    $this->db->select($selectValue);
                    $this->db->join($tableNameJoin . ' ' . $reference_table_name, $joinCondition, 'LEFT');
                    $lookup = true;
                    
                    $get_search_key = $this->get_search_key($tableNameForSession, $tableFieldName);
                    
                    $search_query = '';
                    if (strlen($get_search_key) > 0) {
                        
                        $parse_search_key = explode(",", $get_search_key);
                        
                        for ($count = 0; $count < sizeof($parse_search_key); $count ++) {
                            
                            if ($parse_search_key[$count] != '') {
                                
                                if ($parse_search_key[$count] == 'empty') {
                                    
                                    $search_match_string = " = " . "''";
                                    $search_query = $search_query . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $search_match_string;
                                    $search_query .= " OR {$optr}{$maintableName}{$optr}.{$optr}{$tableFieldName}{$optr} IS NULL";
                                } else {
                                    
                                    if (isset($exact_match) and $exact_match) {
                                        $search_match_string = " = " . "'" . $parse_search_key[$count] . "'";
                                    } else {
                                        $search_match_string = " like " . "'%" . $parse_search_key[$count] . "%'";
                                    }
                                    
                                    $search_query = $search_query . $optr . $reference_table_name . $optr . '.' . $optr . $joinTableValue . $optr . $search_match_string;
                                }
                                
                                if ($count != sizeof($parse_search_key) - 1) {
                                    
                                    $search_query = $search_query . " OR ";
                                }
                            }
                        }
                        
                        $column_filtering[] = ("(" . $search_query . ")");
                    } elseif (isset($condition) && ! empty($condition)) {
                        
                        $condition = str_replace("empty", "", $condition);
                        $linked_queries[] = ($optr . $reference_table_name . $optr . '.' . $optr . $joinTableValue . $optr . ' like ' . "'%" . $condition . "%'");
                    }
                } elseif (isset($value['type']) && $value['type'] == 'eav') {
                    
                    $tableNameJoin = $maintableName . "_" . $tableFieldName . "_eav_view";
                    $reference_table_name = $tableFieldName . '_ref';
                    
                    $joinTableKey = "eav_object_id";
                    $joinTableValue = "eav_object_data";
                    
                    if (isset($value['ref_attribute_table_name']) && ! empty($value['ref_attribute_table_name'])) {
                        $joinTableAttr = "eav_object_attribute";
                    } else {
                        $joinTableAttr = "";
                    }
                    
                    $joinCondition = $reference_table_name . '.' . $joinTableKey . '=' . $maintableName . '.id';
                    $selectValue = "{$reference_table_name}.{$joinTableValue} AS {$tableFieldName}";
                    
                    $this->db->select($selectValue);
                    $this->db->join($tableNameJoin . ' ' . $reference_table_name, $joinCondition, 'LEFT');
                    
                    $get_search_key = $this->get_search_key($tableNameForSession, $tableFieldName);
                    
                    $search_query = '';
                    if (strlen($get_search_key) > 0) {
                        
                        $parse_search_key = explode(",", $get_search_key);
                        
                        $count = 0;
                        foreach ($parse_search_key as $search_key) {
                            $count ++;
                            $search_key_end = $this->get_search_key($tableNameForSession, $tableFieldName .$this->rangeSuffix);
                            if ($search_key != '' OR strlen($search_key_end) ) {
                                
                                if($use_operator)
                                    $operator=$this->get_search_key($tableNameForSession, $tableFieldName . '_operator');
                                
                                //if operator then do this 
                                    if($use_operator && $operator !=''){ 
                                        if($operator =='><'){
                                        //this is for range operator
                                        $operator_start = '>=';
                                        $operator_end = '<=';
                                        if( strlen($parse_search_key[$count])){
                                            $search_query = $search_query . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator_start . " '".$parse_search_key[$count] . "' ";
                                            if( strlen( $search_key_end ) ){
                                                $search_query .= ' AND ' . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator_end . " '".$search_key_end . "' ";
                                            }
                                        }else if(strlen( $search_key_end )){
                                             $search_query .=  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator_end . " '".$search_key_end . "' ";
                                         }
                                    }else{
                                         if(strlen($parse_search_key[$count]))
                                             $search_query .=  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator . " '".$parse_search_key[$count] . "' ";
                                     }
                                }else{
                                    //else do normal like
                                    
                                    $search_query = $search_query . $optr . $reference_table_name . $optr . '.' . $optr . $joinTableValue . $optr . ' like ' . "'%" . $search_key . "%'";
                                    //endelse
                                }
                                //endif 
                                if (! empty($joinTableAttr)) {
                                    $search_query = $search_query . ' OR ' . $optr . $reference_table_name . $optr . '.' . $optr . $joinTableAttr . $optr . ' like ' . "'%" . $search_key . "%'";
                                }
                                if ($count < count($parse_search_key)) {
                                    $search_query = $search_query . ' OR ';
                                }
                            }
                        }
                        
                        $column_filtering[] = ("(" . $search_query . ")");
                    } elseif (isset($condition) && ! empty($condition)) {
                        
                        $first_condition = $optr . $reference_table_name . $optr . '.' . $optr . $joinTableValue . $optr . ' like ' . "'%" . $condition . "%'";
                        $second_condition = "";
                        if (! empty($joinTableAttr)) {
                            $second_condition = ' OR ' . $optr . $reference_table_name . $optr . '.' . $optr . $joinTableAttr . $optr . ' like ' . "'%" . $condition . "%'";
                        }
                        
                        //$linked_queries[] = ($first_condition . $second_condition);
                    }
                }/* elseif (isset($value['type']) && $value['type'] == 'number') {
                    
                    $this->db->select($maintableName . '.' . $tableFieldName);
                    
                    $get_search_key = $this->get_search_key($tableNameForSession, $tableFieldName);
                    
                    $search_query = '';
                    if (strlen($get_search_key) > 0) {
                        
                        $parse_search_key = explode(",", $get_search_key);
                        for ($count = 0; $count < sizeof($parse_search_key); $count ++) {
                            
                            if ($parse_search_key[$count] != '') {
                                if ($parse_search_key[$count] == 'empty') {
                                    
                                    $search_match_string = " = " . "''";
                                    $emptyNum = true;
                                } else {
                                    $emptyNum = false;
                                    $search_match_string = " = " . "'" . $parse_search_key[$count] . "'";
                                }
                                
                                if ($count == sizeof($parse_search_key) - 1) {
                                    
                                    $search_query = $search_query . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $search_match_string . " ";
                                    if ( !empty( $emptyNum ) ) $search_query .= ' OR ' . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " IS NULL ";
                                } else {
                                    
                                    $search_query = $search_query . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $search_match_string . " OR ";
                                    if ( !empty( $emptyNum ) ) $search_query .= $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " IS NULL OR ";
                                }
                            }
                        }
                        
                        $column_filtering[] = ("(" . $search_query . ")");
                    }
                } */ elseif (isset($value['type']) && $value['type'] == 'select') {
                    
                    $this->db->select($maintableName . '.' . $tableFieldName);
                    
                    $get_search_key = $this->get_search_key($tableNameForSession, $tableFieldName);
                    
                    $search_query = '';
                    if (strlen($get_search_key) > 0) {
                        
                        $parse_search_key = explode(",", $get_search_key);
                        for ($count = 0; $count < sizeof($parse_search_key); $count ++) {
                            
                            if ($parse_search_key[$count] != '') {
                                if ($parse_search_key[$count] == 'empty') {
                                    
                                    $emptySelect = true;
                                    $search_match_string = " = " . "''";
                                } else {
                                    
                                    $emptySelect = false;
                                    $search_match_string = " = " . "'" . $parse_search_key[$count] . "'";
                                }
                                
                                if ($count == sizeof($parse_search_key) - 1) {
                                    
                                    $search_query = $search_query . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $search_match_string . " ";
                                    if ( !empty( $emptySelect ) ) $search_query .= ' OR ' . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " IS NULL ";
                                } else {
                                    
                                    $search_query = $search_query . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $search_match_string . " OR ";
                                    if ( !empty( $emptySelect ) ) $search_query .= $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " IS NULL OR ";
                                }
                            }
                        }
                        
                        $column_filtering[] = ("(" . $search_query . ")");
                    }
                } else {
                    
                    // here we declare and define variables for the regex on 
                    // default or xml defined format 
                    // whether this format has time format character or not
                    $defaultDateFormat = null;
                    $pattern = null;
                    if($value["type"] == "datetime"){
                        $pattern = "/h|i|s|u|v/i";
                        $defaultDateFormat = $this->config->item('#DEFAULT_DATE_FORMAT');
                        if (strlen(@$value['date_format']) > 0) {
                            $defaultDateFormat = @$value['date_format'];
                        }
                    }
                    $this->db->select($maintableName . '.' . $tableFieldName);
                    
                    $get_search_key = $this->get_search_key($tableNameForSession, $tableFieldName, @$value['type']);
                    $search_key_end  =$this->get_search_key($tableNameForSession, $tableFieldName . $this->rangeSuffix , @$value['type']);
                    $search_query = '';

                    
                    if (strlen($get_search_key) OR strlen($search_key_end) ) {

                        $parse_search_key = explode(",", $get_search_key);
                                   
                        for ($count = 0; $count < sizeof($parse_search_key); $count ++) {
                            
                           
                            $operator='';

                            if($use_operator) 
                                $operator=$this->get_search_key($tableNameForSession, $tableFieldName . '_operator');
                            
                             if( $use_operator  && $operator != '' ){
                                    
                                  if( $parse_search_key[$count] != '' OR strlen($search_key_end) ){

                                        if ( $operator!=='' )
                                            {
                                                if($operator =='><'){
                                                    //this is for range operator
                                                    $operator_start = '>';
                                                    $operator_end = '<';
                                                    if( strlen($parse_search_key[$count]) ){
                                                       //add condition for start date 
                                                       if($value["type"] == "datetime" && !preg_match($pattern, $defaultDateFormat)){
                                                            $get_key = $this->session->userdata($tableNameForSession . "_" . $tableFieldName);
                                                            $parse_search_key[$count] = strtotime($get_key . " 23:59");
                                                       }
                                                        $search_query =  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator_start . " ".$parse_search_key[$count] . " ";
                                                        if( strlen( $search_key_end ) ){
                                                            if($value["type"] == "datetime" && !preg_match($pattern, $defaultDateFormat)){
                                                                $get_key = $this->session->userdata($tableNameForSession . "_" . $tableFieldName . $this->rangeSuffix);
                                                                $search_key_end = strtotime($get_key . " 00:00");
                                                            }
                                                            $search_query .= ' AND ' . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator_end . " ".$search_key_end . " ";
                                                        }
                                                    }else if(strlen( $search_key_end )){
                                                        //add condition for end date 
                                                        if($value["type"] == "datetime" && !preg_match($pattern, $defaultDateFormat)){
                                                            $get_key = $this->session->userdata($tableNameForSession . "_" . $tableFieldName . $this->rangeSuffix);
                                                            $search_key_end = strtotime($get_key . " 00:00");
                                                        }
                                                         $search_query .=  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator_end . " ".$search_key_end . " ";
                                                     }
                                                }else if($operator =='<>'){
                                                    if(strlen($parse_search_key[$count])){

                                                        if($value["type"] == "datetime" && !preg_match($pattern, $defaultDateFormat)){
                                                            $get_key = $this->session->userdata($tableNameForSession . "_" . $tableFieldName);
                                                            $parse_search_key[$count] = strtotime($get_key . " 00:00");
                                                            $search_query =  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " < ".$parse_search_key[$count] . " ";
                                                            $parse_search_key[$count] = strtotime($get_key . " 23:59");
                                                            $search_query .=  ' OR '.$optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " > ".$parse_search_key[$count] . " ";
                                                        }else{

                                                            $search_query =  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " " . $operator . " ".$parse_search_key[$count] . " ";
                                                        }

                                                    }
                                                }else{
                                                    if($value["type"] == "datetime"){
                                                        
                                                        if($operator == "="){
                                                            $tzOffset = date('Z');
                                                            if(date("I", $parse_search_key[$count])){
                                                                $tzOffset += $tzOffset;
                                                            }
                                                            $search_query .= ('DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(' . $tzOffset . '), interval ' . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . ' second),"%d %b %Y") like ' . "'%" . date("d M Y", $parse_search_key[$count]) . "%'");
                                                        }else if(preg_match($pattern, $defaultDateFormat)){     
                                                            $search_query .=  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator . " ".$parse_search_key[$count] . " ";                          
                                                        }else{
                                                            if($operator == ">" || $operator == "<="){
                                                                $get_key = $this->session->userdata($tableNameForSession . "_" . $tableFieldName);
                                                                $parse_search_key[$count] = strtotime($get_key . " 23:59");                                
                                                            }else if($operator == ">=" || $operator == "<"){
                                                                $get_key = $this->session->userdata($tableNameForSession . "_" . $tableFieldName);
                                                                $parse_search_key[$count] = strtotime($get_key . " 00:00");                                
                                                            }
                                                            $search_query .=  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator . " ".$parse_search_key[$count] . " ";
                                                        }
                                                    } else
                                                     if(strlen($parse_search_key[$count]))
                                                         $search_query .=  $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . $operator . " ".$parse_search_key[$count] . " ";
                                                 }
                                        }else{
                                            $search_query = $search_query . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr .  " LIKE '".$parse_search_key[$count] . "'";
                                        }
                                    }
                            }else{

                                if ($parse_search_key[$count] != '') {
                                    
                                    if ($parse_search_key[$count] == 'empty') {
                                        
                                        $emptyFieldValue = true;
                                        $search_match_string = " = " . "''";
                                    } else {
                                        
                                        //$parsedData = 
                                        //$emptyFieldValue = false;
                                       /// $search_match_string = " LIKE " . "'%" . $parse_search_key[$count] . "%'";
                                    }
                                    
                                    $parsedData = explode(" ",$parse_search_key[$count]);
                                    $searchQuery = [];
                                    foreach ( $parsedData as $eachItem ) {
                                        
                                        if ( strlen($eachItem) > 0 && $eachItem != 'empty') {
                                            
                                            $searchQuery[] = "( ". $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " LIKE " . "'%" . $eachItem . "%'" . " ) ";
                                        }else if ( strlen($eachItem) > 0 && $eachItem == 'empty') {
                                            
                                            $searchQuery[] = "( ". $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " = '' "." ) ";
                                        }
                                    }
                                    
                                    if ($count == sizeof($parse_search_key) - 1) {
                                        
                                         $search_query = $search_query ."( ".implode(" AND ", $searchQuery)." ) ";
                                        if ( !empty( $emptyFieldValue ) ) $search_query .= ' OR ' . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " IS NULL ";
                                    } else {
                                        
                                        $search_query = $search_query ."( ".implode(" AND ", $searchQuery)." ) ". " OR ";
                                        if ( !empty( $emptyFieldValue ) ) $search_query .= $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " IS NULL OR ";
                                    }
                                }

                           }
                        }

                        $column_filtering[] = ("(" . $search_query . ")");

                    } elseif (isset($condition) && $condition) {
                        
                        if ($value['type'] == "datetime") {
                            // on search load
                            if ( strlen($condition) > 0 ) {
                                $linked_queries[] = ('DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(0), interval ' . $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . ' second),"%d %b %Y") like ' . "'%" . $condition . "%'");
                            }
                            
                        } else {
                            $parsedData = explode(" ",$condition);
                            $searchQuery = [];
                            foreach ( $parsedData as $eachItem ) {
                            
                                if ( strlen($eachItem) > 0 ) {
                            
                                    $searchQuery[] = "( ". $optr . $maintableName . $optr . '.' . $optr . $tableFieldName . $optr . " LIKE " . "'%" . $eachItem . "%'" . " ) ";
                                }
                            }
                           $linked_queries[] = "( ".implode(" AND ", $searchQuery)." )";
                        }
                    }
                }
            }
            
            if ( !empty($tableFieldName) && array_key_exists($tableFieldName, $allOrderBy)) {
                
                if ($lookup) {
                    
                    $allOrderBy[$tableFieldName]->fullName = $reference_table_name . '.' . $joinTableValue;
                } else {
                    
                    $allOrderBy[$tableFieldName]->fullName = $maintableName . '.' . $tableFieldName;
                }
            }
        }
        
        foreach ($allOrderBy as $key => $value) {
            
            if (isset($value->fullName) && $value->fullName) {
                
                $this->db->order_by($value->fullName, $value->direction);
            }
        }
        
        /*
         * additional condition added by any specific application which requires some extra condition
         */
        
        if (is_array($additional_condition)) {
            
            if (count($additional_condition)) {
                
                if (isset($additional_condition['AND'])) {
                    
                    if (is_array($additional_condition['AND']) && count($additional_condition['AND'])) {
                        
                        $this->db->where('( ' . implode(' AND ', $additional_condition['AND']) . ' )');
                    } else 
                        if (strlen($additional_condition['AND'])) {
                            
                            $this->db->where($additional_condition['AND']);
                        }
                }
                
                if (isset($additional_condition['OR'])) {
                    
                    if (is_array($additional_condition['OR']) && count($additional_condition['OR'])) {
                        
                        $this->db->or_where('( ' . implode(' OR ', $additional_condition['OR']) . ' )');
                    } else 
                        if (strlen($additional_condition['OR'])) {
                            
                            $this->db->or_where($additional_condition['OR']);
                        }
                }
                if (isset($additional_condition['IN'])) {
                         
                         if (is_array($additional_condition['IN']) && count($additional_condition['IN'])) {
                             foreach($additional_condition['IN'] as $whereInArray){
                                 $this->db->where_in($whereInArray['field_name'], $whereInArray['data']);
                             }                       
                         } 
                     }
            }
        }
        
        if (count($linked_queries)) {
            
            $this->db->where(' ( ' . (implode(' OR ', $linked_queries)) . ' ) ', null, false);
        }
        
        if (isset($column_filtering) and count($column_filtering) > 0) {
            
            $this->db->where(' ( ' . (implode(' AND ', $column_filtering)) . ' ) ', null, false);
        }
        
        // soft-delete
        
        $acount_id = $this->session->userdata('account_id');
        if (isset($trash) and $trash) {
            $this->db->where($maintableName . '.is_deleted !=', 0);
        } else {
            $this->db->where($maintableName . '.is_deleted !=', 1);
        }
        
        $this->db->select($maintableName . '.' . $primary_key);
        $this->db->distinct();
        
        if($disableGroup != 1){
            $this->db->group_by($maintableName . '.' . $primary_key);
        }
        
        $tempdb = clone $this->db;
        
        if ($limit_items == "all") {
            $query = $this->db->get($maintableName);
        } else {
            $query = $this->db->get($maintableName, $limit_items, $limit_start);
        }
        
        $last_query = $this->db->last_query();

        $dashboard_helper = Dashboard_main_helper::get_instance();
        
        if (($limit_position = strrpos($last_query, 'LIMIT')) !== false) {
            
            $total_items = $tempdb->get($maintableName)->num_rows();
            $dashboard_helper->set('listing_total_items', $total_items);
        } else {
            
            $dashboard_helper->set('listing_total_items', $query->num_rows());
        }
        
        return $query->result();
    }
    
    public function parseSearchKey ( $search ) {
    
        $parsedData = explode(",", $search);
        $allKeys = [];
    
        foreach ( $parsedData as $eachKey ) {
    
            $parsedEachKey = explode(" ", $eachKey);
    
            if ( sizeof($parsedEachKey) > 1 ) {
    
                foreach ( $parsedEachKey as $keyItem ) {
    
                    $allKeys[] = trim($keyItem);
                }
    
            }
    
            $allKeys[] = trim($eachKey);
        }
    
        return $allKeys;
    }


    /*
     * perform add
     */
    function add($tableName, $data)
    {
        $data_insert = $this->db->insert($tableName, $data);
        return $data_insert;
    }

    /*
     * perform edit action
     */
    function edit($tableName, $data, $whereArray = array())
    {
        if (is_array($whereArray) && count($whereArray) > 0) {
            
            $this->db->where($whereArray);
        }
        
        $result_update = $this->db->update($tableName, $data);
        
        return $result_update;
    }

    /*
     * perform delete data
     */
    function delete($tableName, $fields, $primary_key = 'id')
    {
        // delete record relational messages
        $this->messages->deleteMessages($tableName, $fields);
        $this->db->where_in($primary_key, $fields);
        $result = $this->db->delete($tableName);
        return $result;
    }

    /*
     * perform delete data
     */
    function softDelete($tableName, $fields, $primary_key = 'id')
    {
        // soft delete record relational messages
        $this->messages->softDeleteMessages($tableName, $fields);
        
        $this->db->where_in($primary_key, $fields);
        $result = $this->db->update($tableName, array(
            'is_deleted' => 1
        ));
        return $result;
    }

    /*
     * perform copy items
     */
    function copy($tableName, $idsArray, $copy_field = "")
    {
        foreach ($idsArray as $id) {
            
            $this->db->select("*");
            $this->db->where("id", $id);
            $selectQuery = $this->db->get($tableName);
            $data = $selectQuery->row();
            
            unset($data->id);
            if (isset($copy_field) and ! empty($copy_field)) {
                
                $data->$copy_field = dashboard_lang('_COPY_DATA_TO_SAVE_DATABASE') . $data->{$copy_field};
            }
            $result = $this->db->insert($tableName, $data);
        }
        return $result;
    }

    /*
     * Table rows exist checking
     */
    public function tableTotalRows($tableName, $tableField = array("id"), $condition = "")
    {
        if (isset($condition) && $condition) {
            
            foreach ($tableField as $field) {
                
                $this->db->or_like($field, $condition);
            }
        }
        
        $queryResult = $this->db->get($tableName);
        return $queryResult->num_rows();
    }

    /*
     * Search auto suggest words here
     */
    public function get_words($lookUpData, $accountId, $tableName, $tableField = array("id"), $condition = "", $limit = 10, $additional_condition = array())
    {
        $finalResult = array();
        
        $condition = $this->db->escape_like_str($condition);
        $all_search_result = array();
        
        if (isset($condition) && $condition) {
            
            $querySelect = array();
            
            foreach ($tableField as $field) {
                
                if (strlen($field)) {
                    
                    if ( !in_array($field, $lookUpData['lookUpFields']) ) {

                        $this->db->reset_query();
                        
                        $this->db->select("$field as value");
                        $this->db->distinct($field);
                        $this->db->from($tableName);
                        $this->db->like($field, $condition);
                        $this->db->where('is_deleted', 0);
                        if (isset($additional_condition['AND'])) {
                            $this->db->where($additional_condition['AND']);
                        }
                        
                        $search_result = $this->db->get()->result_array();

                    } else if (in_array($field, $lookUpData['lookUpFields'])) {

                        $selectFields = $lookUpData['refSelectField'][$field];
                        $explode = explode(',', $selectFields);
                        $selectField = $explode[0];
                        $tableNameTmp = $lookUpData['refTable'][$field];
                        $key = $lookUpData['refTableKey'][$field];

                        $tableNameTmpAli = $lookUpData['refTable'][$field].rand(10,1000);
                        $tableNameAli = $tableName.rand(10,100);

                        $this->db->reset_query();
                        
                        $this->db->select("$tableNameTmpAli.$selectField as value");
                        $this->db->distinct("$tableNameTmpAli.$selectField");
                        $this->db->from("$tableNameTmp AS $tableNameTmpAli");
                        $this->db->join("$tableName AS $tableNameAli", "$tableNameAli.$field = $tableNameTmpAli.$key", 'left' );
                        $this->db->like("$tableNameTmpAli.$selectField", $condition);
                        $this->db->where("$tableNameAli.is_deleted", 0);
                        $this->db->where("$tableNameTmpAli.account_id", $accountId);
                        $this->db->where("$tableNameAli.account_id", $accountId);
                        
                        $search_result = $this->db->get()->result_array();
                    }


                    foreach ($search_result as $result) {
                        
                        $all_search_result[] = $result;
                    }
                }
            }
            
            $finalResult = array();
            foreach ($all_search_result as $value) {
                $finalResult[]['value'] = $value['value'];
            }
        }
        
        return $finalResult;
    }

    function get_autosuggest_lookup($tableName, $tableField, $wordWhere, $orderBy, $orderOn, $limit = 10)
    {
        $finalResult = array();
        
        if (is_array($wordWhere) && count($wordWhere) > 0) {
            
            $this->db->select($tableField);
            $this->db->like($wordWhere);
            $this->db->order_by($orderBy, $orderOn);
            $query = $this->db->get($tableName);
            $finalResult = $query->result();
        }
        
        return $finalResult;
    }

    /*
     * lookup select form building function
     */
    public function lookup($tableName, $key, $valueArray, $orderBy, $orderOn, $additional_and, $account_id)
    {
        $value = "CONCAT(" . implode('," ",', $valueArray) . ")";
        $select = "$key AS select_key, $value AS select_value";
        $this->db->select($select);
        if (isset($account_id) and $account_id != 0) {
            $data['account_id'] = $account_id;
        }
        foreach ($additional_and as $id => $text) {
            $filter_array = explode(':', $text);
            $data[$filter_array[0]] = $filter_array[1];
        }
        $data['is_deleted'] = 0;
        $this->db->where($data);
        $this->db->group_by('select_value');
        $this->db->order_by($orderBy, $orderOn);
        $query = $this->db->get($tableName);
        return $query;
    }

    
        /*
     * lookup select form building function
     */
    public function lookup_with_concat_multuple($tableName, $key, $valueArray, $orderBy, $orderOn, $additional_and, $account_id, $selected)
    {
        $value = "CONCAT_WS"."(' - ',  ". implode(",", $valueArray) . ")";

        $select = "$key AS select_key, $value AS select_value";
        $this->db->select($select);
        if (isset($account_id) and $account_id != 0) {
            $data['account_id'] = $account_id;
        }
        foreach ($additional_and as $id => $text) {
            $filter_array = explode(':', $text);
            $data[$filter_array[0]] = $filter_array[1];
        }
        $data['is_deleted'] = 0;
        
        $data["id"] = intval($selected);
        
        $this->db->group_by('select_value');
        $this->db->order_by($orderBy, $orderOn);
        $query = $this->db->get($tableName);

        return $query;
    }

    public function non_ajax_lookup($tableName, $key, $valueArray, $orderBy, $orderOn, $additional_and, $account_id)
    {
        foreach ( $valueArray as &$eachField ) {
            $eachField = '`'.$eachField.'`';
        }
        
        $value = "CONCAT(" . implode('," ",', $valueArray) . ")";
        $select = "$key AS select_key, $value AS select_value";
        $this->db->select($select);
        if ($account_id != 0) {
            $data['account_id'] = $account_id;
        }
        foreach ($additional_and as $id => $text) {
            $operator = "=";
            $filter_array = explode(':', $text);
            if(isset($filter_array[2])){
                $operator = $filter_array[2];
            }
            $conditionStr = "`".$filter_array[0]."` "." ".$operator." "."'".$filter_array[1]."'";
            $this->db->where($conditionStr, NULL, FALSE);;
        }
        $data['is_deleted'] = 0;
        $this->db->where($data);
        $this->db->group_by('select_value');
        $this->db->order_by($orderBy, $orderOn);
        $query = $this->db->get($tableName);
        return $query;
    }

    public function getUserGroupQuery($tableName)
    {
        $this->db->select("*");
        $this->db->where($this->tablePrefix . "menu", $tableName);
        $this->db->where("role", get_user_role());
        $query = $this->db->get($this->config->item('prefix') . "table_permissions");
        return $query;
    }

    public function getSuperUserPermission($edit = '')
    {
        $status = 0;
        
        $this->db->select("*");
        $this->db->where("table", "*");
        $this->db->where("field_name", "*");
        $this->db->where("is_deleted", 0);
        
        if ($edit) {
            
            $this->db->where("edit", $edit);
        }
        
        $this->db->where("role", get_user_role());
        $query = $this->db->get($this->tablePrefix . "table_permissions");
        $rows = $query->num_rows();
        
        if ($rows > 0) {
            
            $status = 1;
        } else {
            
            $status = 0;
        }
        
        return $status;
    }

    /*
     * Table rows exist or not checking
     */
    public function getTableRows($tableName, $selectField, $whereArray = "")
    {
        $this->db->select($selectField);
        
        if (is_array($whereArray) && count($whereArray) > 0) {
            
            $this->db->where($whereArray);
        }
        
        $selectData = $this->db->get($tableName);
        
        return $selectData->num_rows();
    }

    public function insert_translations($key, $value)
    {
        $this->load->model('language_model');
        $translation_table_name = $this->tablePrefix . 'translations';
        $site_language = $this->language_model->get_user_default_language();
        if (! $site_language) {
            
            $site_language = $this->config->item('#LANGUAGE_DEFAULT');
        }
        
        $account_id = get_default_account_id();
        $check_account_id = $this->check_account_id_is_empty($account_id);
        $language_id = $this->language_model->get_lanuage_id($site_language);
        
        /**
        *
        *   bellow code is commented because, no need to check database for $key . 
        *   if $key not found in lang->laguage array. then this code run. 
        *   lang->language array keep all KEY from database. :)
        */

        // $this->db->select("*");
        // $this->db->where('language_key', $key);
        // $this->db->where('language_id', $language_id);
        // $this->db->where('account_id', $check_account_id);
        // $this->db->where('is_deleted', 0);
        // $result = $this->db->get($translation_table_name)->result_array();
        
        // if (sizeof($result) > 0) {
            
        //     if (strlen($result[0]['language_value']) > 0) {
                
        //         return $result[0]['language_value'];
        //     } else {
                
        //         return $key;
        //     }
        // } else {
            
            $pieces = explode(' ', $value);
            $last_word = array_pop($pieces);
            
            if ($last_word == "info") {
                $data['language_id'] = $language_id;
                $data['language_key'] = $key;
                $data['language_value'] = "";
                $data['account_id'] = $check_account_id;
                $data['is_deleted'] = 0;
            } else {
                $data['language_id'] = $language_id;
                $data['language_key'] = $key;
                $data['language_value'] = $value;
                $data['account_id'] = $check_account_id;
                $data['is_deleted'] = 0;
            }
            
            $this->db->insert($this->tablePrefix . 'translations', $data);
            
            return $value;
        // }
    }

    function check_account_id_is_empty($account_id)
    {
        if ($account_id == null || $account_id == '' || $account_id == '0' || strlen($account_id) == '0') {
            
            return 1;
        } else {
            
            return $account_id;
        }
    }

    function search_all_fields($data, $table_name, $current_field_name, $lookup)
    {
        $optr = $this->config->item('default_quoted_indetifier');
        $all_data_value = json_decode($data, TRUE);
        $all_lookup_field = json_decode($lookup, TRUE);
        
        $main_table_name = $this->tablePrefix . $this->input->post('main_table_name');
        
        $main_field_name = $this->input->post('main_field_name');
        
        $search_string = "";
        
        foreach ($all_data_value as $key => $value) {
            
            if (strlen($value) > 0) {
                
                $search_string = $search_string . $optr . "$main_table_name" . $optr . ".{$optr}$key{$optr} LIKE '%$value%' AND";
            }
        }
        
        $lookup_join = "";
        
        $this->db->select("DISTINCT($table_name.$current_field_name)");
        $this->db->from("$main_table_name");
        
        foreach ($all_lookup_field as $key => $value) {
            
            $exploded_value = explode(",", $value);
            
            $lookup_table = $exploded_value[0];
            
            $lookup_name = $exploded_value[1];
            $lookup_key = $exploded_value[2];
            $searched_value = $exploded_value[3];
            
            $this->db->join("$lookup_table", "$lookup_table.$lookup_key = $main_table_name.$key");
            $this->db->like("$lookup_table.$lookup_name", $searched_value);
        }
        
        $this->db->where("$main_table_name.is_deleted", 0);
        
        $result = $this->db->get()->result_array();
        $response = "";
        foreach ($result as $table_info) {
            
            $response .= "<li data-value='$table_info[$current_field_name]' data-db-field-name='$main_field_name' class='description'>$table_info[$current_field_name]</li>";
        }
        echo $response;
    }

    function get_all_column_name($table_name)
    {
        $this->db->select('*');
        $this->db->where('TABLE_NAME', $table_name);
        $result = $this->db->get('INFORMATION_SCHEMA.COLUMNS')->result_array();
        $all_column = array();
        
        foreach ($result as $column) {
            
            $all_column[] = $column['COLUMN_NAME'];
        }
        
        return $all_column;
    }

    /* Get lookup autosuggestion data */
    public function get_lookup_autosuggest($search_string, $ref_table, $ref_key, $ref_value, $order_by, $order_on)
    {
        $data = array();
        $max_options = $this->config->item('max_autosuggest_items');
        ;
        $valueArray = explode(',', $ref_value);
        $value = "CONCAT_WS(" . implode(',"-",', $valueArray) . ")";
        $select = "$ref_key AS select_key, $value AS select_value";
        
        $this->db->select($select);
        $this->db->group_start();
        foreach ($valueArray as $key => $value) {
            $this->db->or_like($value, $search_string, "both");
        }
        $this->db->group_end();
        $this->db->where('is_deleted', 0);
        $this->db->limit($max_options);
        
        if (! empty($order_by)) {
            if (! empty($order_on)) {
                $order = $order_on;
            } else {
                $order = "asc";
            }
            
            $this->db->order_by($order_by, $order);
        }
        
        $this->db->group_by('select_value');
        
        $rows = $this->db->get($this->tablePrefix . $ref_table)->result_array();
        
        foreach ($rows as $row) {
            $data[] = $row;
        }
        return $data;
    }

    /* Get lookup autosuggestion data */
    public function get_lookup_autosuggest_with_seperator($search_string, $ref_table, $ref_key, $ref_value, $order_by, $order_on)
    {
        $data = array();
        $max_options = $this->config->item('max_autosuggest_items');
        ;
        $valueArray = explode(',', $ref_value);
        $value = "CONCAT_WS(' - ',  " .implode(",", $valueArray). ")";
        $select = "$ref_key AS select_key, $value AS select_value";
        
        $this->db->select($select);
        $this->db->group_start();
        foreach ($valueArray as $key => $value) {
            $this->db->or_like($value, $search_string, "both");
        }
        $this->db->group_end();
        $this->db->where('is_deleted', 0);
        $this->db->limit($max_options);
        
        if (! empty($order_by)) {
            if (! empty($order_on)) {
                $order = $order_on;
            } else {
                $order = "asc";
            }
            
            $this->db->order_by($order_by, $order);
        }
        
        $this->db->group_by('select_value');
        
        $rows = $this->db->get($this->tablePrefix . $ref_table)->result_array();
        
        foreach ($rows as $row) {
            $data[] = $row;
        }
        return $data;
    }

    public function get_specific_field_name()
    {
        $table_name = $this->tablePrefix . $this->input->post('table_name');
        $id = $this->input->post('id');
        $value = $this->input->post('value');
        $key = $this->input->post('key');
        
        $this->db->select("$value, $key");
        $this->db->where('id', $id);
        $result = $this->db->get($table_name)->result_array()[0];
        echo json_encode($result);
    }
    
    /**
     * Get table fields permission based on user role
     */
    public function get_table_permissions_field($table_name, $action="")
    {
        $userHelper = BUserHelper::get_instance();
        
        $this->db->select('role,field_name,permission');
        $this->db->from($this->tablePrefix . "table_permissions");
        $this->db->group_start();
        $this->db->where('table', '*');
        $this->db->or_where('table', $table_name);
        $this->db->group_end();
        $this->db->where('role', $userHelper->user->role);
        $this->db->where('is_deleted', 0);
        
        $data = $this->db->get()->result_array();

        // when user has only view permission, then they will not get add and delete permission
        if($action == $this->config->item("view_only_action_text")){
            $modifiedPermissions = array();
            foreach($data as $permissionRow){
                if((int)$permissionRow['permission'] == 2){
                    $permissionRow['permission'] = 1;
                }
                $modifiedPermissions[] = $permissionRow;
            }

            $data = $modifiedPermissions;
        }     
        
        return $data;
    }

    /**
     * Change table fields permission to show the fiels as view only mode
     */
    public function change_table_fields_permission($existingPermission)
    {
        $newPermissions = array();
        foreach($existingPermission as $permissionRow){
            if((int)$permissionRow['permission'] == 2){
                $permissionRow['permission'] = 1;
            }
            $newPermissions[] = $permissionRow;
        }
        return $newPermissions;
    }

    /**
     * Checking whether an user has add or delete permission
     * in a listing view
     *
     * @param $table_name 
     * @return array
     */
    public function get_user_add_delete_permissions($table_name, $action="")
    {
        $current_user_role = $this->session->userdata('user_role');
        $user_permission = array();
        $specific_table_permission = array();
        $all_menu_permission = array();
        $has_specific_table_permission = false;
        $has_menu_permission = false;
        $noActionPermission = array(
            'add_permission' => 0,
            'delete_permission' => 0
        );
        
        $this->db->select('menu, add_permission, delete_permission, export_permission');
        $this->db->where('role', $current_user_role);
        $this->db->where('is_deleted', 0);
        $all_permission = $this->db->get($this->tablePrefix . 'permissions_row')->result_array();
        
        foreach ($all_permission as $permission) {
            
            if ($permission['menu'] == $table_name) {
                
                $specific_table_permission['add_permission'] = $permission['add_permission'];
                $specific_table_permission['delete_permission'] = $permission['delete_permission'];
                $specific_table_permission['export_permission'] = $permission['export_permission'];
                
                $has_specific_table_permission = true;
            }
            
            if ($permission['menu'] == '*') {
                
                $all_menu_permission['add_permission'] = $permission['add_permission'];
                $all_menu_permission['delete_permission'] = $permission['delete_permission'];
                $all_menu_permission['export_permission'] = $permission['export_permission'];
                
                $has_menu_permission = true;
            }
        }
        // when user has only view permission, then they will not get add and delete permission
        if($action == $this->config->item("view_only_action_text")){
            return $noActionPermission;
        }

        if ($has_specific_table_permission) {            
            return $specific_table_permission;
        }
        
        if ($has_menu_permission) {            
            return $all_menu_permission;
        } else {            
            return $noActionPermission;
        }
    }

    function check_user_add_delete_permission($id, $permissions)
    {
        if ($id == '0') {
            
            if ($permissions['add_permission'] == '0') {
                
                $error_msg['heading'] = dashboard_lang('_ACCESS_DENIED');
                $error_msg['message'] = dashboard_lang('_YOU_HAVE_NO_PERMISSION_TO_ACCESS_THIS_PAGE');
                $error_msg['not_show_time_msg'] = true;
                echo $this->load->view('errors/html/error_403', $error_msg, true);
                exit();
            }
        }
    }

    /*
     * Save checkbox data
     */
    public function save_checkbox_data($current_table, $fieldName, $selectedCheckbox, $main_table, $main_table_field, $ref_table_field, $id)
    {
        
        // get all checkbox data that is related to id
        $all_ref_ids = array();
        $all_update_ids = array();
        
        $this->db->select($ref_table_field);
        $this->db->where($main_table_field, $id);
        $this->db->where('field_name', $fieldName);
        $all_data = $this->db->get($main_table)->result_array();
        
        foreach ($all_data as $row) {
            $all_ref_ids[] = $row[$ref_table_field];
        }
        
        foreach ($selectedCheckbox as $key => $value) {
            
            if (in_array($value, $all_ref_ids)) {
                $update_data = array(
                    'is_deleted' => 0
                );
                
                $all_update_ids[] = $value;
                
                $this->db->where($main_table_field, $id);
                $this->db->where($ref_table_field, $value);
                $this->db->where('field_name', $fieldName);
                $this->db->update($main_table, $update_data);
            } else {
                
                $insert_data = array(
                    $main_table_field => $id,
                    $ref_table_field => $value,
                    'field_name' => $fieldName
                );
                
                $this->db->insert($main_table, $insert_data);
                $this->db->insert_id();
            }
        }
        
        foreach ($all_ref_ids as $key => $value) {
            
            if (! in_array($value, $all_update_ids)) {
                
                $update_data = array(
                    'is_deleted' => 1
                );
                
                $this->db->where($main_table_field, $id);
                $this->db->where($ref_table_field, $value);
                $this->db->where('field_name', $fieldName);
                $this->db->update($main_table, $update_data);
            }
        }
        
        // Update current table checkbox field value with his own row id
        
        $this->db->where('id', $id);
        $this->db->update($current_table, array(
            $fieldName => $id
        ));
    }

    /*
     * Get all Checkbox data
     */
    public function get_all_checkbox_data($ref_table, $ref_table_field="name")
    {
        $this->db->select('id');
        $this->db->select($ref_table_field);
        $account_id = get_default_account_id();
        $this->db->where('account_id', $account_id);
        $this->db->where('is_deleted', 0);
        $data = $this->db->get($this->tablePrefix . $ref_table)->result_array();
        
        return $data;
    }

    /*
     * get selected checkbox data from main table
     */
    public function get_selected_checkbox_data($id, $name, $main_table, $main_table_field, $ref_table_field)
    {
        $return_data = array();
        
        $account_id = get_default_account_id();
        $this->db->select($ref_table_field);
        $this->db->where('account_id', $account_id);
        $this->db->where('is_deleted', 0);
        $this->db->where($main_table_field, $id);
        $this->db->where('field_name', $name);
        $data = $this->db->get($this->tablePrefix . $main_table)->result_array();
        foreach ($data as $row) {
            $return_data[] = $row[$ref_table_field];
        }
        return $return_data;
    }
    
    /*
     * get lock table user_id
     * 
     * */
    
    public function getLockTableUserId( $id, $tableName )
    {
        $user_id = 0;
        $this->db->select('user_id,time_to_expire');
        $this->db->where('table_name', $tableName);
        $this->db->where('row_id', $id);
        $query = $this->db->get('lock_tables')->row();
        if( isset( $query->user_id ) ){
            $user_id = $query->user_id; 
        }
        return $user_id;
    }
    
    /*
     * get lock table user_id
     *
     * */
    public function getLockTableDetails( $id, $tableName )
    {
        
        $this->db->select('user_id,time_to_expire,id');
        $this->db->where('table_name', $tableName);
        $this->db->where('row_id', $id);
        
        return $this->db->get('lock_tables')->result_array();
        
    }

    /*
     * check a record status is lock or not if not then return true else return false
     */
    public function getRecordStatus($id, $tableName)
    {
        $this->load->model("portal/Table_permission_model");
        
        $tableName = $this->tablePrefix . $tableName;
        $lockTableName = $this->tablePrefix . 'lock_tables';
        $status = null;
        $current_timestamp = time();
        $maximum_time_to_edit_row = (int) $this->config->item('#CONCURRENT_USER_WAIT') * 60;
        
        $tableDetails = $this->getLockTableDetails($id, $tableName);
        if ( sizeof($tableDetails)  > 0) {
            $row_data = getDataFromId('dashboard_login', $tableDetails[0]['user_id'] ,'id', true);
      
            $updated_row_timestamp = $tableDetails[0]['time_to_expire'];
            
            $editing_user_permission = $this->Table_permission_model->getEditPermission( $tableName, $row_data->role );
            
            if ($tableDetails[0]['user_id'] == get_user_id()) {
                $status = TRUE;
            }else if ( $editing_user_permission != 2) {
                $status = TRUE;
            }  else 
                if ($updated_row_timestamp <= $current_timestamp) {
                    $status = TRUE;
                } else {
                    $status = FALSE;
                }
            
            if ($status && $editing_user_permission == '2') {
                
                $new_updated_timestamp = time() + $maximum_time_to_edit_row;
                $this->db->where('table_name', $tableName);
                $this->db->where('row_id', $id);
                $this->db->update($lockTableName, array(
                    'time_to_expire' => $new_updated_timestamp,
                    'user_id' => get_user_id()
                ));
            }
        } else {
            
            $current_time = time() + $maximum_time_to_edit_row;
            $editing_user_permission = $this->Table_permission_model->getEditPermission( $tableName );
            
            if ( $editing_user_permission == '2' ) {
                 
                $data = array(
                    'table_name' => $tableName,
                    'user_id' => get_user_id(),
                    'row_id' => $id,
                    'time_to_expire' => $current_time
                );
                
                $status = $this->db->insert($lockTableName, $data);
            }else {
                $status = true;
            }
            

        }
        
        return $status;
    }

    public function deleteLockData($id, $tableName)
    {
        $tableName = $this->tablePrefix . $tableName;
        $lockTableName = $this->tablePrefix . 'lock_tables';
        
        $this->db->where('table_name', $tableName);
        $this->db->where('row_id', $id);
        $this->db->where('user_id', get_user_id());
        
        $status = $this->db->delete($lockTableName);
        
        return $status;
    }

    function get_all_values_by_id($ref_table_name, $reference_key, $foreign_key, $id, $insert_table_primary_key)
    {
        $data = array();
        if (isset($id) && ! empty($id)) {
            
            $this->db->select('*');
            $this->db->where($foreign_key, $id);
            $this->db->where('is_deleted', 0);
            $results = $this->db->get($ref_table_name)->result_array();
            
            for ($count = 0; $count < sizeof($results); $count ++) {
                
                $reference_key = "{$reference_key}";
                $data[] = $results[$count]["{$reference_key}"];
            }
            
            return $data;
        } else {
            
            return array();
        }
    }

    function render_all_value($table_name)
    {
        $this->db->select('*');
        $this->db->where('is_deleted', 0);
        $result = $this->db->get($table_name)->result_array();
        
        return $result;
    }

    function render_table_option_with_selected_value($table_name, $selected_field, $selected_value, $ref_key, $ref_value, $ref_attribute, $ref_attribute_table_name, $ref_attribute_field_name)
    {
        $options = "<option value=0>" . dashboard_lang('_PLEASE_SELECT') . "</option>";
        
        if (! empty($ref_attribute_table_name)) {
            
            $this->db->select("$table_name.$ref_value, $table_name.$ref_key,  $ref_attribute_table_name.$ref_attribute_field_name AS attr_name");
            $this->db->from("$table_name");
            $this->db->join("$ref_attribute_table_name", "$ref_attribute_table_name.`id`=$table_name.$ref_attribute", "left");
            $this->db->where("$table_name.is_deleted", 0);
        } else {
            
            $this->db->select("$table_name.$ref_value, $table_name.$ref_key");
            $this->db->from("$table_name");
            $this->db->where("$table_name.is_deleted", 0);
        }
        
        $query = $this->db->get();
        
        foreach ($query->result_array() as $result) {
            $selected = ($result[$ref_key] == $selected_value) ? "selected" : '';
            if (! empty($ref_attribute_table_name)) {
                $options .= "<option value='" . $result[$ref_key] . "' $selected>" . $result[$ref_value];
                $options .= (! empty($result['attr_name'])) ? "-" . $result['attr_name'] : '';
                $options .= "</option>";
            } else {
                $options .= "<option value='" . $result[$ref_key] . "' $selected>" . $result[$ref_value] . "</option>";
            }
        }
        return $options;
    }

    function generate_options_list($table_name, $key, $value)
    {
        $query = $this->db->get_where($table_name, array(
            'is_deleted' => 0
        ));
        $results = $query->result_array();
        
        $options_lists = "<option>" . dashboard_lang('_SELECT_') . "</option>";
        foreach ($results as $result) {
            
            $key = "{$key}";
            $value = "{$value}";
            $options_lists = $options_lists . "<option value='" . $result[$key] . "'>" . $result[$value] . "</option>";
        }
        
        return $options_lists;
    }

    function delete_all_in_reference_table($table_name, $foriegn_key, $foriegn_key_value)
    {
        $this->db->delete($table_name, array(
            $foriegn_key => $foriegn_key_value
        ));
    }

    function update_eav_table($eav_data_list, $tableName, $id)
    {
        foreach ($eav_data_list as $eav) {
            $data = [];
            $this->db->where('id', $id);
            $this->db->update($tableName, array(
                $eav['name'] => $id
            ));
            
            $insert_table_reference_key = $eav['insert_table_reference_key'];
            $insert_table = $eav['insert_table'];
            $insert_table_forign_key = $eav['insert_table_forign_key'];
            $reference_table_values = $this->input->post($insert_table_reference_key);
            $this->delete_all_in_reference_table($insert_table, $insert_table_forign_key, $id);
            
            for ($count = 0; $count < sizeof($reference_table_values); $count ++) {
                
                $check_specific_value_exists = $this->check_data_exists_in_table($insert_table, $insert_table_reference_key, $reference_table_values[$count], $insert_table_forign_key, $id);
                if (! $check_specific_value_exists) {
                    
                    $data[$insert_table_reference_key] = $reference_table_values[$count];
                    $data[$insert_table_forign_key] = $id;
                    $data['account_id'] = get_account_id();
                    $this->db->insert($insert_table, $data);
                }
            }
        }
    }

    function check_data_exists_in_table($table_name, $value_field_name, $value, $foreign_key, $id)
    {
        $query = $this->db->get_where($table_name, array(
            $value_field_name => $value,
            $foreign_key => $id
        ));
        
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * get unique number for auto_inc_number field
     */
    function checkUniqueNumber($number, $rowId, $table, $field)
    {
        $response = array(
            'status' => 0,
            'message' => dashboard_lang('_DUPLICATE')
        );
        
        $result = $this->db->select("id, {$field}")
            ->where('is_deleted', 0)
            ->where($field, $number)
            ->where('id !=', $rowId)
            ->get($table)
            ->result();
        if (count($result) == 0) {
            $response = array(
                'status' => 1,
                'message' => dashboard_lang('_SUCCESS')
            );
        }
        
        return $response;
    }
    
    /*
     * get unique number for auto_inc_number field
     */
    function checkUniqueFieldValue($table, $field_name, $filed_value, $id=0)
    {
        $response = array(
            'status' => 1,
            'message' => dashboard_lang('_SUCCESS')
        );
        
        $this->db->select("id");
        $this->db->where($field_name, $filed_value);
        $this->db->where('is_deleted',0);
        if($id != 0){
            $this->db->where('id !=', $id);
        }
        $result = $this->db->get($table)->result();
            
        if (count($result) > 0 ) {
                $response = array(
                    'status' => 0,
                    'message' => dashboard_lang('_DUPLICATE')
                );
            
        }
        
        return $response;
    }
    
    /*
     * check a table has any trash item (soft-deletd item)
     * input: table name
     * output: true/false
     */
    function getTrashBtnStatus($tableName, $additionalCondition){
        
        $showTrashBtn = false;   
        
        $this->db->select("*");
        //for soft deleted data
        $this->db->where('is_deleted', 1);
        
        /*
         * additional condition added by any specific application which requires some extra condition
         */
        
        if (is_array($additionalCondition)) {
            
            if (count($additionalCondition)) {
                
                if (isset($additionalCondition['AND'])) {
                    
                    if (is_array($additionalCondition['AND']) && count($additionalCondition['AND'])) {
                        
                        $this->db->where('( ' . implode(' AND ', $additionalCondition['AND']) . ' )');
                    } else 
                        if (strlen($additionalCondition['AND'])) {
                            
                            $this->db->where($additionalCondition['AND']);
                        }
                }
                
                if (isset($additionalCondition['OR'])) {
                    
                    if (is_array($additionalCondition['OR']) && count($additionalCondition['OR'])) {
                        
                        $this->db->or_where('( ' . implode(' OR ', $additionalCondition['OR']) . ' )');
                    } else 
                        if (strlen($additionalCondition['OR'])) {
                            
                            $this->db->or_where($additionalCondition['OR']);
                        }
                }
            }
        }             
        
        $data = $this->db->get($tableName)->result();
        
        if(sizeof($data) > 0){
            $showTrashBtn = true;
        }
        
        return $showTrashBtn;
    }
	
	public function setBGImage($image=''){
        $response = array('status'=>0);
        $this->db->where("id", get_user_id());
        $status = $this->db->update($this->tablePrefix."dashboard_login", array('background_image'=>$image));
        if($status){
            $response['status'] = 1;
        }
        return $response;
    }

    public function updateSingleField($key , $value , $pk , $table )
    {
        $xmlObjectArray = B_form_helper::get_xml_object_array( $this->_xmldata );
        $fieldType = (string) $xmlObjectArray[$key]["type"];
        $validation = (string) $xmlObjectArray[$key]['validation'];
        $validation_element = explode(",", $validation);
        
        if (in_array("required", $validation_element) && strlen(trim(@$value)) == "0") {
            
            $this->db->select($key);
            $results = $this->db->get_where($table, array(
                "id" => $pk,
            ))->row_array();
            
            $this->output->set_status_header('411');

            return array("success" => false, "msg" => dashboard_lang("_THIS_FIELD_IS_REQUIRED"), "type" => $fieldType, "newValue" => $results[$key]);
        }

        if ( $fieldType === "input" ) {

            $this->db->where('id', $pk );
            $this->db->update($table , array( $key=>$value) );

            $data = array("success" => true, "type" => $fieldType, "results" => array("value" => $value));

        } else if ( $fieldType === "lookup" ) {

            $this->db->where('id', $pk );
            $this->db->update($table , array( $key=>$value) );

            $referenceTable = (string) $xmlObjectArray[$key]['ref_table'];
            $refkeyfield = (string) $xmlObjectArray[$key]['key'];
            $refvaluefield = (string) $xmlObjectArray[$key]['value'];
            $isTranslated = (string) $xmlObjectArray[$key]['is_translated'];
            $isStatusDd = (string) $xmlObjectArray[$key]['is_status_dropdown'];
    
            if($isStatusDd==="1"){
                $this->db->select("background_color,text_color");
            }
            $this->db->select($refkeyfield . " as key," . $refvaluefield . " as value");
            $results = $this->db->get_where($referenceTable, array(
                $refkeyfield => $value,
            ))->row_array();
            
            if ( $isTranslated === "1" ) {
                if ( strpos($results["value"], "_") === 0 ) {
                    $results["value"] = strtoupper( $results["value"] );
                } else {
                    $results["value"] = strtoupper( "_".$results["value"] );
                }
                $results["value"] = dashboard_lang($results["value"]);
            }
            $data = array("success" => true, "type" => $fieldType, "isStatusDd" => $isStatusDd, "results" => $results);

        } else if ( $fieldType === "date" || $fieldType === "datetime" ) {

            $value = strtotime(trim($value));
            if ( empty(trim($value)) ) {
                $value = "";
            }
            
            $this->db->where('id', $pk );
            $this->db->update($table , array( $key=> $value ) );

            $default_date_format = $this->config->item('#DEFAULT_DATE_FORMAT');
            if (strlen(@$xmlObjectArray[$key]['date_format']) > 0) {
                $default_date_format = (string) $xmlObjectArray[$key]['date_format'];
            }
            $newValue = "Empty";
            if (!empty($value)) {

                $newValue = date($default_date_format, $value);
            }
            
            $data = array("success" => true, "type" => $fieldType, "newValue" => $newValue);
        }
        return $data;
    }
}
/* End of file: Dahboard_Model.php */

