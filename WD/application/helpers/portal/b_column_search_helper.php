<?php
/*
 * @author boo2mark
 *
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

function render_selected_options($type, $all_field, $field_name)
{
    if ($type == 'money') {
        
        return render_money_options($field_name);
    }
    
    if ($type == 'datetime') {
        
        return render_datetime_options($field_name);
    }
}

function render_saved_data($field_name)
{
    $CI = &get_instance();
    $date = '';
    $selected_value = explode(",", $CI->session->userdata($CI->uri->segment('2') . "_" . $field_name));
    if(!empty($selected_value)){
        $date = $selected_value;
    }

    return $date;
}

function render_money_options($field_name)
{
    $CI = &get_instance();
    $selected_value = explode(",", $CI->session->userdata($CI->uri->segment('2') . "_" . $field_name));
    $options = '';
    foreach ($selected_value as $value) {
        
        $format_money = strtolower($CI->config->item('#DEFAULT_MONEY_FORMAT'));
        $display_value = $value;
        if ($format_money == 'us') {
            
            $display_value = str_replace(",", '.', $value);
        } else {
            
            $display_value = str_replace(".", ',', $value);
        }
        $options .= "<option value='" . $value . "' selected> " . $display_value . "</option>";
    }
    
    return $options;
}

function render_datetime_options($field_name)
{
    $CI = &get_instance();
    $datetime = " datetime='1' ";
    $selected_value = explode(",", $CI->session->userdata($CI->uri->segment('2') . "_" . $field_name));
    $options = '';
    $date_format = $CI->config->item('#DEFAULT_DATE_FORMAT');
    foreach ($selected_value as $value) {
        
        $value = intval($value);
        if ($value != '0') {
            $options .= "<option value='" . $value . "' selected> " . date($date_format, $value) . "</option>";
        }
    }
    
    return $options;
}

function render_select_options($all_field, $field)
{
    $CI = &get_instance();
    $select = "data-select='1' data-select-options=''";
    $select_options_data = '';
    $return_data = array();
    
    foreach ($all_field[$field]->option as $option) {
        
        $key = (string) $option;
        $value = (string) $option['key'];
        $options_array[$value] = $key;
        $select_options_data = $select_options_data . "$key@$value,";
    }
    $select = "data-select='1' data-select-options='$select_options_data'";
    $selected_value = explode(",", $CI->session->userdata($CI->uri->segment('2') . "_" . $field));
    $options = '';
    foreach ($selected_value as $value) {
        
        if (isset($options_array[$value])) {
            $options .= "<option value='$value' selected>" . dashboard_lang($options_array[$value]) . "</option>";
        } else {
            $options .= "<option value='$value' selected> </option>";
        }
    }
    
    $return_data['options'] = $options;
    $return_data['select'] = $select;
    
    return $return_data;
}

function renderOperatorsDropDown($fieldName, $class='')
{
    $CI = &get_instance();    
    $defaultOperator = $CI->config->item("#DEFAULT_OPERATOR_FOR_COLUMN_FILTERING");
    $selectedOperator = getSelectedOperator($fieldName);  
    $filterData = $CI->session->userdata($CI->uri->segment('2') . "_" . $fieldName);
    if(empty($filterData)){
        $class = "operatorsDDHide";
    }    
    $CI->db->select('id, operator');
    $CI->db->where('is_deleted', 0);  
    $operatorsData = $CI->db->get("column_filter_operators")->result();
    
    $html = '<select class="operatorsDD '.$class.'" id="'.$fieldName.'-operatorDD">';
    $options = '';
    foreach ($operatorsData as $operatorRow) {  
        $selected = '';
        if(!empty($selectedOperator)){
            if($operatorRow->operator == $selectedOperator){
                $selected = 'selected';
            }            
        } else {
            if($operatorRow->operator == $defaultOperator){
            $selected = 'selected';
            }
        }
        $options .= "<option value='" . $operatorRow->operator . "' ".$selected."> " . $operatorRow->operator . "</option>";
    }
    $html .= $options;
    $html .= "</select>";
    return $html;
}
function getSelectedOperator($fieldName){
    $CI = &get_instance();
    return $selectedOperator = $CI->session->userdata($CI->uri->segment('2') . "_" . $fieldName . "_operator");
}

function getTotalColumnValue( $totalValueCount, $fieldType, $all_listing_data, $field, $showCurrencySymbol ){

    $CI = &get_instance();
    $coreBaseCurrency = $CI->config->item('#CORE_BASE_CURRENCY');
    $CI->db->select('iso2');
    $CI->db->where('id', $coreBaseCurrency);
    $CI->db->where('is_deleted', 0);   
   
    $currencySymbol = $CI->db->get('currencies')->row_array()['iso2']; 

    if( $totalValueCount && $showCurrencySymbol ){
        return $currencySymbol.B_form_helper::customeMoneyFormate(@array_sum(array_column($all_listing_data, $field)));
    }
    else if( $totalValueCount ){
        return B_form_helper::customeMoneyFormate(@array_sum(array_column($all_listing_data, $field)));
    } 

}