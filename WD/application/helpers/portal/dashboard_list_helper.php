<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

    /*
 * render field in listing page based on type
 */
function dashboard_show_field($field_object, $value, $tooltip = 0, $id = '', $table_name = '')
{
    $CI = & get_instance();
    $CI->lang->load('dashboard');
    
    /*
     * check if eav
     */
    
    if ((string) $field_object['type'] == 'eav') {
        $view_name = $table_name . "_". $field_object['name'] . "_eav_view";
        $where_array = array(
            $view_name . '.eav_object_id' => $id,
            $view_name . '.is_deleted' => 0
        );
        
        if (isset($field_object['ref_attribute_table_name']) && ! empty($field_object['ref_attribute_table_name'])) {
            $where_array[$view_name . '.is_deleted'] = 0;
        }
        
        $query = $CI->db->get_where($view_name, $where_array);
        
        $text = '';
        $count = $query->num_rows();
        foreach ($query->result_array() as $row) {
            if(@$field_object['is_translated'] == 1){
                $row["eav_object_data"] = dashboard_lang($row["eav_object_data"]);
            }
            $count --;
            if (! empty($field_object['ref_attribute_table_name'])) {
                // echo $CI->db->last_query();
                if ($tooltip == 1) {
                    $text .= $row["eav_object_data"] . "-" . $row["eav_object_attribute"];
                } else {
                    $text .= '<li class="eav_value">' . $row["eav_object_data"];
                    $text .= (! empty($row["eav_object_attribute"])) ? "-" . $row["eav_object_attribute"] : '';
                    $text .= '</li>';
                }
            } else {
                if ($tooltip == 1) {
                    $text .= $row["eav_object_data"];
                } else {
                    $text .= '<li class="eav_value">' . $row["eav_object_data"] . '</li>';
                }
            }
            
            if ($count > 0 && $tooltip == 1) {
                $text .= ", ";
            }
        }
        return $text;
    }
    
    /*
     * check if image
     */
    if ((string) $field_object['type'] == 'image') {
        /*
         * check for file exists
         */
        $imageFolder = $CI->config->item('img_upload_path');
        $subDirectory = (string) $field_object['sub_directory'];
        if (isset($subDirectory) and ! empty($subDirectory)) {
            $imageFolder .= $subDirectory . "/";
        }
        $imageSizeShownWidth = $CI->config->item('img_thumbnail_size_width');
        $fileExist = $imageFolder . 'thumbs/' . $value;
        if ((stripos($value, "http://") !== false) || (stripos($value, "https://") !== false)) {
            if (((string) $field_object['type'] == 'image')) {
                echo '<img src="' . $value . '" width="' . $imageSizeShownWidth . '">';
            }
        } else {
            if ($value != "" && file_exists(FCPATH . $fileExist)) {
                echo '<img src="' . CDN_URL . $fileExist . '">';
            } else {
                $fileOriginalImage = $imageFolder . $value;
                if ($value != "" && file_exists(FCPATH . $fileOriginalImage)) {
                    return '<img src="' . CDN_URL . $fileOriginalImage . '" width="' . $imageSizeShownWidth . '">';
                }
            }
        }
    } elseif ((string) $field_object['type'] == 'file') {
        
        $imageFolder = $CI->config->item('img_upload_path');
        if (! empty($value)) {
            return '<a target="_blank" href="' . CDN_URL . $imageFolder . $value . '" download>' . dashboard_lang("_DOWNLOAD_THIS_FILE") . '</a>';
        }
    } elseif ((string) $field_object['type'] == 'single_checkbox') {

        if (! empty($value)) {
            return dashboard_lang("_YES");
        }else{
            return dashboard_lang("_NO");
        }
        
    }elseif (((string) $field_object['type']) == 'select') {
        /*
         * this is for select item
         */
        $optionsArray = array();
        $total_options = ($field_object->count());
        for ($i = 0; $i < $total_options; $i ++) {
            $langkey_option = (string) $field_object->option[$i];
            $attributes = $field_object->option[$i]->attributes();
            $optionsArray[(string) $attributes['key']] = dashboard_lang($langkey_option);
        }
        $final_value = (isset($optionsArray[$value]) ? $optionsArray[$value] : $value);
        
        if ($field_object['is_translated'] == 1 && ! empty($final_value) && $final_value != "*") {
            $final_value = dashboard_lang(strtoupper($final_value));
        }
        return $final_value;
    } elseif (((string) $field_object['type']) == 'radio') {
        /*
         * this is for select item
         */
        $optionsArray = array();
        $total_options = ($field_object->count());
        for ($i = 0; $i < $total_options; $i ++) {
            $langkey_option = (string) $field_object->option[$i];
            $attributes = $field_object->option[$i]->attributes();
            $optionsArray[(string) $attributes['key']] = dashboard_lang($langkey_option);
        }
        return (isset($optionsArray[$value]) ? $optionsArray[$value] : $value);
    } elseif (((string) $field_object['type']) == 'datetime') {
        /*
         * this is for date time
         */
        if ($value != 0) {
            $CI = &get_instance();
            $default_date_format = $CI->config->item('#DEFAULT_DATE_FORMAT');
            if (strlen(@$field_object['date_format']) > 0) {
                $default_date_format = $field_object['date_format'];
            }
            return date("$default_date_format", $value);
        }
    } elseif (((string) $field_object['type']) == 'money') {
        $fractionDigits = (string) $field_object['fraction_digits'];
        $selected_value = "";
        if ( strlen($value) > 0) {
            if(strlen($fractionDigits) > 0){
                $fractionDigits = (int)$fractionDigits;
                $selected_value = B_form_helper::customeMoneyFormate($value, $fractionDigits);
            } else{
                $selected_value = B_form_helper::customeMoneyFormate($value);
            }
        }
        return $selected_value;
    } elseif (((string) $field_object['type']) == 'custom_select') {
        if (isset($value) and ! empty($value)) {
            return dashboard_lang(strtoupper($value));
        }
    } elseif (((string) $field_object['type']) == 'custom_select_opening_view') {
        if (isset($value) and ! empty($value)) {
            return dashboard_lang("_" . strtoupper($value));
        }
    } elseif (((string) $field_object['type']) == 'checkbox') {} else {
        /*
         * this is every thing else
         */
        $listing_field_ellipsis_length = config_item("#LISTING_FIELD_ELLIPSIS_LENGTH");
        
        $value = strip_tags($value);
        $listing_tooltip = $value;
        
        if ($field_object['is_translated'] == 1 && ! empty($listing_tooltip) && $listing_tooltip != "*") {
            $listing_tooltip = dashboard_lang( strtoupper($listing_tooltip));
        }
        if (! $tooltip) {
            
            if ($listing_field_ellipsis_length < strlen($value)) {
                $listing_tooltip = substr($value, 0, $listing_field_ellipsis_length) . " ...";
            }
        }
        
        return $listing_tooltip;
    }
}

/*
 * listing the multiple value selectable dropdown
 */
function listing_multi_select_dropdown($table_name, $col_name, $ref_table, $ref_table_col_name, $multi_select_array = array())
{
    $CI = & get_instance();
    
    $prefix = $CI->config->item("prefix");
    
    $account_id = $CI->session->userdata('user_account_id');
    
    $CI->db->where($table_name . '.is_deleted =', 0);
    $CI->db->where($table_name . '.account_id =', $account_id);
    
    if ($ref_table) {
        $CI->db->select($prefix . $ref_table . "`." . $ref_table_col_name . "`");
    } else {
        $CI->db->select($col_name);
    }
    $CI->db->distinct();
    
    if ($ref_table) {
        $CI->db->join($prefix . $ref_table, $prefix . $ref_table . ".id=" . $prefix . $table_name . "`." . $col_name . "`");
    }
    /*
     * Check is deleted 1 or 0
     */
    
    if ($multi_select_array) {
        foreach ($multi_select_array as $key => $value) {
            if ($value != $CI->config->item('please_select')) {
                
                if ($ref_table) {
                    
                    if ($key == $col_name) {
                        $CI->db->like($prefix . $ref_table . "." . $ref_table_col_name, $value);
                    } else {
                        $CI->db->like($prefix . $table_name . "." . $key, $value);
                    }
                } else {
                    
                    if ($col_name == "`" . $prefix . $table_name . "`.`" . $key . "`") {
                        $CI->db->like($prefix . $table_name . "." . $key, $value);
                    } else {}
                }
            }
        }
    }
    
    $result = $CI->db->get($prefix . $table_name);
    $last_query = $CI->db->last_query() . "<br>";
    return $result->result_array();
}

function render_amount_value($table_name, $field, $primary_id)
{
    $CI = &get_instance();
    $CI->load->model('Dashboard_Model');
    return $CI->Dashboard_Model->render_amount_value($table_name, $field, $primary_id);
}

function updateListingFields($listing_fields_data, $ordering_fields_data, $table_name)
{
    $status = 0;
    
    $CI = &get_instance();
    
    $table_full_name = $CI->config->item('prefix') . 'listing_fields';
    
    $user_id = get_user_id();
    $CI->db->select('id');
    $CI->db->where('table_name', $table_name);
    $CI->db->where('user_id', $user_id);
    $CI->db->where('is_deleted', 0);
    $query = $CI->db->get($table_full_name);
    $rows = $query->num_rows();
    
    if ($rows > 0) {
        
        $data = $query->row();
        $id = $data->id;
        
        $CI->db->select('id');
        $CI->db->where('id', $id);
        $CI->db->where('list_fields', $listing_fields_data);
        $CI->db->where('order_fields', $ordering_fields_data);
        $query = $CI->db->get($table_full_name);
        $rows = $query->num_rows();
        
        if ($rows < 1) {
            
            $result = $CI->db->update($table_full_name, array(
                'list_fields' => $listing_fields_data,
                'order_fields' => $ordering_fields_data
            ), array(
                'id' => $id
            ));
            
            if ($result) {
                $status = 1;
            }
        }
    } else {
        
        $result = $CI->db->insert($table_full_name, array(
            'table_name' => $table_name,
            'user_id' => $user_id,
            'list_fields' => $listing_fields_data,
            'order_fields' => $ordering_fields_data
        ));
        
        if ($result) {
            $status = 1;
        }
    }
    return $status;
}

function renderListColumnWidth ( $tableName, $field ) {

    $CI = &get_instance();

    $settingsValue = intval( $CI->config->item("#CORE_FIELDS_MAXWIDTHPX_".strtoupper($tableName)."_".strtoupper($field)));
    if ( $settingsValue > 0 ) {

        $columnSettings = $settingsValue;
    }else {
        $columnSettings = intval ( $CI->config->item("#CORE_FIELDS_DEFAULT_MAXWIDTHPX"));
        if ( $columnSettings == 0 ) {

            $columnSettings = 150;
        }
    }

    return $columnSettings;
}