<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Custom_b_form_helper
{

    /**
     * Returns user permission value of a field
     *
     * @param array $all_permission:
     *            possible values in array format
     *            'field_name'=>'name of the field'
     *            'permission'=>'permission value of that field'
     *            permission value 2(edit)|1(view)|0(none)
     *            
     * @param string $field_name
     *            checking permission of that specific field
     * @param bool $check_edit_permission
     *             we need only the edit permission or all permission
     * @return integer/bool
     */
    static function check_user_permission($all_permission, $field_name, $check_edit_permission = false)
    {
        $has_asterik = false;
        $has_field_permission = false;
        $permission_level = 0;
        $asterik_data = array();
        $permission_data = array();
        
        foreach ($all_permission as $permission) {
            
            if ($permission['field_name'] == '*') {
                
                $asterik_data['permission'] = $permission['permission'];
                $has_asterik = true;
            }
            if ($permission['field_name'] == $field_name) {
                
                $has_field_permission = true;
                $permission_data['permission'] = $permission['permission'];
            }
        }
        
        if ($has_field_permission) {            
            $permission_level = $permission_data['permission'];
        } else {
            if ($has_asterik) {                
                $permission_level = $asterik_data['permission'];
            } else {                
                $permission_level = 0;
            }
        }
        /**
         * check if we only need the edit permission or not
         * if yes we will return true/false
         * if no then we will return 0/1/2         * 
         */        
        if($check_edit_permission){
            if($permission_level == 2){
                return true;
            } else{
                return false;
            }
        }  else{
            return $permission_level;
        }          
    }

    /**
     *
     *
     *
     * Render a Form element (input/select/textarea/file) in Edit view
     * based on user permission
     *
     * @param array $xmlObject:
     *            possible values in array format
     *            'name'=>'name of the field'(mandatory)
     *            'type'=>'type of the field' (mandatory)
     *            'required'=>'whether this field is required ' (optional:default no)
     *            'default_value'=>'default value to show in form' (optional: default empty)
     *            'render_only_field' if 1 is given, no div tag or tooltip will be added else these will be added with input tag.
     *            
     * @param string $selected
     *            selected value in edit mode in string format
     * @param array $permissions
     *            possible values in array format
     *            'field'=>{field_name} | name of the field, if (*) then for all field
     *            'permission'=>{permission_on_field} | permission value 2(edit)|1(view)|0(none)
     *            
     * @return string
     */
    static function render_field($xmlObject, $selected = '', $permissions, $id = 0, $form_orientation = "", $field_cols = "")
    {
        $CI = & get_instance();
        // get xml attribute value
        $type = (string) $xmlObject['type'];
        $name = (string) $xmlObject['name'];
        $required = 0;
        $validation = (string) $xmlObject['validation'];
        $extra_html_class = (string) $xmlObject['extra_html_class'];
        $default_value = (string) $xmlObject['default_value'];
        $hide_label = filter_var($xmlObject['hide_label'], FILTER_VALIDATE_BOOLEAN);
        $hide_tooltip = filter_var($xmlObject['hide_tooltip'], FILTER_VALIDATE_BOOLEAN);
        $field_without_div = (string) $xmlObject['render_only_field'];
        
        $field_table_name = (string) $xmlObject['prepend_table_name'];
        
        $extra = " m-input " . $extra_html_class . " ";
        $field_status = "";
        $lable_column = 0;
        $lable_class = "";
        $ext_div_start = "";
        $info_btn = "";
        
        if (isset($default_value) and ! empty($default_value)) {
            if (substr($default_value, 0, 1) === "#") {
                $default_value = $CI->config->item($default_value);
            } else {
                if ($type == "datetime") {
                    $default_value = strtotime($default_value);
                }
            }
        } else {
            $default_value = "";
        }
        
        if ($selected == "") {
            $selected = $default_value;
        }
        
        $field_permission = self::check_user_permission($permissions, $name);
        
        if ($field_permission == '0') {
            return '';
        }
        
        if ($field_permission == '1') {
            $field_status = 'disabled';
        }
        
        $validation_element = explode(",", $validation);
        
        if (in_array("required", $validation_element)) {
            
            $required = 1;
        }
        
        $output_html = "";
        $row_class = "";
        
        if ($type != 'hidden') {
            
            if (! empty($form_orientation) && $form_orientation == "horizontal") {
                // calculate total rows of label
                $lable_cols = 12 - $field_cols;
                $lable_class = "col-$lable_cols";
                $ext_div_start = "<div class='col-$field_cols'>";
                $row_class = " row ";
            } else{
                $ext_div_start = '<div style="clear:left;">';
            }            
            
            if ($type == "messages") {
                $output_html .= '<div class="form-group ' . 'field_' . $name . '" id ="' . 'field_id_' . $name . '">';
            } else {
                $output_html .= '<div class="form-group m-form__group m--font-bolder '.$row_class. ' field_' . $name . '" id ="' . 'field_id_' . $name . '">';
            } 
            
            /*
             * append table name if set in xml
             */
            if( strlen($field_table_name) > 0 ){
                $name = $field_table_name. '_' .$name;
            }
            
            $info_btn = self::render_info_button($name, $type, $hide_tooltip, '', $form_orientation);
            
            if ($hide_label !== true) {             
                $output_html .= boo2_render_form_label($name, '', $required, $lable_class, $info_btn);
            }
        }     
        
             
        
        if (! empty($validation)) {
            $extra .= str_replace(",", " ", $validation);
        }
        $function_name = 'render_' . strtolower($type);
        /*
         * check whether function written for this
         * type of field. Convention for function naming is
         * render_{type_name}
         */
        if (method_exists('B_form_helper', $function_name)) {
            if ($field_without_div === '1') {
                $output_html .= self::{$function_name}($xmlObject, $selected, $extra, $field_status);
            } else {
                $output_html .= $ext_div_start;
                $output_html .= self::{$function_name}($xmlObject, $selected, $extra, $field_status);
            }
        } else {
            $output_html = '';
        }
        
        if ($field_without_div !== '1') {
            if ($type != 'hidden') {              
                $output_html .= "</div></div>";
            }
        }
        
        return $output_html;
    }

    /**
     *
     *
     *
     * Render an input in Edit view
     *
     * @param array $xmlObject:
     *            possible values in array format
     *            'name'=>'name of the field'(mandatory)
     *            'default_value'=>'default value of the field' (optional)
     *            
     * @param string $selected
     *            selected value in edit mode in string format
     * @param string $extra
     *            extra attributes
     *            
     * @return string
     */
    
    /*
     * render input filed
     */
    static function render_input($xmlObject, $selected, $extra, $field_status)
    {
        $name = $xmlObject['name'];
        $placeholder = (string) $xmlObject['placeholder'];
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        $output_html = '';
        $data = array(
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'class' => 'form-control' . $extra
        );
        
        $output_html .= form_input($data, $selected, $field_status);
        
        return $output_html;
    }

    static function render_number($xmlObject, $selected, $extra, $field_status)
    {
        $output_html = '';
        $name = $xmlObject['name'];
        $placeholder = (string) $xmlObject['placeholder'];
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        
        $data = array(
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'class' => 'form-control text-right ' . $extra.' '.$name
        );
        
        $output_html .= form_input($data, $selected, $field_status);
        
        return $output_html;
    }

    /*
     * render auto increment number field
     */
    static function render_auto_inc_number($xmlObject, $selected, $extra, $field_status)
    {
        $name = (string) $xmlObject['name'];
        $tbl_name = (string) $xmlObject['tbl_name'];
        $readonly = filter_var($xmlObject['readonly'], FILTER_VALIDATE_BOOLEAN);
        $placeholder = (string) $xmlObject['placeholder'];
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        
        if (isset($readonly) and $readonly) {
            $field_status = 'readonly';
        }
        
        $output_html = '';
        $data = array(
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'class' => 'form-control auto-inc-number' . $extra
        );
        
        if (isset($selected) and $selected < 1) {
            $selected = get_auto_ins_number($tbl_name, $name);
        }
        
        $output_html .= form_input($data, $selected, $field_status);
        
        return $output_html;
    }

    /*
     * render info icon
     */
    public static function render_info_button($name, $type, $hide_tooltip, $extra_style = '', $form_orientation='')
    {
        $description_key = "_" . strtoupper($name) . "_INFO";
        $description = dashboard_lang($description_key);
        $extraClassName = "";
        
        if ($type == "hidden" || strlen($description) < 1) {
            $html = "";
        } else {
            
            if ($hide_tooltip == TRUE || $description_key == $description) {
                $extraClassName = 'hide_tooltip invisible';
            }
            
            if(!empty($form_orientation) && $form_orientation == "horizontal"){
                $extraClassName .= ' horzontal-info';
            }
            
            $html = '<span style="' . $extra_style . '" class="'.$extraClassName.' m--padding-left-10 m--valign-middle m--pull-right"> ';
            $html .= '<a style="color:white;" class="btn btn-success m-btn m-btn--icon m-btn--icon-only m-btn--pill m--pull-left custom-info-button" data-trigger="click" data-html="true" data-animation="true" data-toggle="popover" data-content="' . htmlspecialchars($description, ENT_QUOTES) . '">';
            $html .= '<i class="fa fa-info"></i></a>';
            $html .= '</span>';
        }
        
        return $html;
    }

    static function render_money($xmlObject, $selected, $extra, $field_status)
    {
        $output_html = '';
        $name = $xmlObject['name'];
        $fractionDigits = (string)$xmlObject['fraction_digits'];
        $placeholder = (string) $xmlObject['placeholder'];
		$thousandSeperator = (string) $xmlObject['thousand_seperator'];
		 
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        
        $data = array(
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'class' => 'form-control money' . $extra
        );
        $show_currency = (integer) $xmlObject['show_currency_symbol'];
        $default_currency = $xmlObject['symbol'];
        
        if ($show_currency > 0) {
            $output_html .= '<div class="input-group">';
        }
        
        $align = 'style="text-align: right;" onkeypress=""';
        
        $CI = &get_instance();
        $selected_value = ""; 
        
        if ( strlen($selected) > 0) {
            if(strlen($fractionDigits) > 0){
                $fractionDigits = (int)$fractionDigits;
                
				if ( $thousandSeperator == '1' ) {
					$selected_value = self::customeMoneyFormate($selected, $fractionDigits);
				}else {
					$selected_value = self::allowDecimalMoneyFomate($selected, $fractionDigits);
				}
				
            } else{
				
				if ( $thousandSeperator == '1' ) {
					$selected_value = self::customeMoneyFormate($selected);
				}else {
					$selected_value = self::allowDecimalMoneyFomate($selected);
				}
                
            }
        
        }
        
        $currency_show = $default_currency ? $default_currency : $CI->config->item('default_currency_symbol');
        
        if ($show_currency > 0) {
            
            $output_html .= '<span class="input-group-addon">' . $currency_show . '</span>';
            $output_html .= form_input($data, $selected_value, $field_status, $align);
            $output_html .= '</div>';
        } else {
            $output_html .= form_input($data, $selected_value, $field_status, $align);
        }
        
        return $output_html;
    }

    /*
     * renders textarea
     */
    static function render_textarea($xmlObject, $selected, $extra, $field_status)
    {
        $rows = (int) $xmlObject['rows'];
        $cols = (int) $xmlObject['cols'];
        $addon = (string) $xmlObject['addon'];
        $placeholder = (string) $xmlObject['placeholder'];
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        
        if ($addon == "editor") {
            $db_helper = Dashboard_main_helper::get_instance();
            $db_helper->set('load_ckeditor', 1);
            $extra .= ' ckeditor';
        }
        
        $name = $xmlObject['name'];
        $output_html = '';
        
        $data = array(
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'class' => 'form-control ' . $extra,
            'rows' => ($rows != "") ? $rows : "10",
            'cols' => ($cols != "") ? $cols : "40"
        );
        
        $output_html .= form_textarea($data, $selected, $field_status);
        
        return $output_html;
    }

    /*
     * renders email field
     */
    static function render_email($xmlObject, $selected, $extra, $field_status)
    {
        $output_html = '';
        $name = $xmlObject['name'];
        $placeholder = (string) $xmlObject['placeholder'];
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        $data = array(
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'class' => 'form-control ' . $extra
        );
        
        $output_html .= form_email($data, $selected, $field_status);
        
        return $output_html;
    }

    static function render_radio($xmlObject, $selected, $extra, $field_status)
    {
        $field_array = array();
        $name = (string) $xmlObject['name'];
        $default_value = (string) $xmlObject['default_value'];
        
        foreach ($xmlObject->option as $option) {
            
            $key = (string) $option['key'];
            
            $field_array[$key] = dashboard_lang($option);
        }
        
        $output_html = "<div class='m-radio-inline'>";
        
        foreach ($field_array as $key => $value) {
            
            $checked = "";
            
            if ( empty($selected) ) {
                
                $CI = &get_instance();
                if ( substr($default_value, 0, 1) === "#" && $key == $CI->config->item($default_value) ) {
                    $checked = "checked";
                } else if ( $key == $default_value ) {
                    $checked = "checked";
                }
            } else if ($key == $selected) {
                
                $checked = "checked";
            } else {
                
                $checked = "";
            }
            
            $output_html .= "<label class='m-radio'><input type='radio' name='" . $name . "' id='" . $name . "' class='" . $extra . "' lang_value='" . $value . "' value='" . $key . "' " . $checked . " " . $field_status . ">" . $value . "<span></span></label>";
        }
        
        $output_html .= "</div>";
        
        return $output_html;
    }

    /*
     * render checkbox
     */
    static function render_checkbox($xmlObject, $selected, $extra, $field_status)
    {
        $output_html = '';
        $name = $xmlObject['name'];
        $main_table = (string) $xmlObject['main_table'];
        $main_table_field = (string) $xmlObject['main_table_field'];
        $ref_table = (string) $xmlObject['ref_table'];
        $ref_table_field = (string) $xmlObject['ref_table_field'];
        
        $CI = get_instance();
        $CI->load->model('dashboard_model');
        $selected_ids = $CI->dashboard_model->get_selected_checkbox_data($selected, $name, $main_table, $main_table_field, $ref_table_field);
        $all_checkbox_data = $CI->dashboard_model->get_all_checkbox_data($ref_table);
        
        $output_html .= "<div class='checkbox_inner_area'>";
        
        foreach ($all_checkbox_data as $checkbox) {
            $flag = 0;
            foreach ($selected_ids as $row) {
                
                if ($row == $checkbox['id']) {
                    $flag = 1;
                }
            }
            
            $checkbox_name = dashboard_lang("_" . strtoupper($checkbox['name']));
            
            if ($flag) {
                $output_html .= "<label class='m-checkbox m--padding-left-25 m--padding-right-10'><input type='checkbox' name='" . $name . "[]' class='" . $extra . "' value='" . $checkbox['id'] . "' checked " . $field_status . ">" . $checkbox_name . "<span> </span></label>";
            } else {
                $output_html .= "<label class='m-checkbox m--padding-left-25 m--padding-right-10'><input type='checkbox' name='" . $name . "[]'  class='" . $extra . "' value='" . $checkbox['id'] . "' " . $field_status . ">" . $checkbox_name . "<span> </span></label>";
            }
        }
        
        $output_html .= "</div>";
        
        return $output_html;
    }

    /*
     * render single checkbox
     */
    static function render_single_checkbox($xmlObject, $selected, $extra, $field_status)
    {
        $output_html = '';
        $name = (string) $xmlObject['name'];
        $dynamic_value = (string) $xmlObject['dynamic_value'];        
        $checkboxText = dashboard_lang("_" . strtoupper($name) . "_TEXT");
        $checked = '';
        $default_value = 1;
        if (! empty($selected)) {
            $checked = 'checked';
        }
        if (! empty($dynamic_value) ) {
            if( empty($checked) ){
                $default_value = 0;
            }            
        }
        $output_html .= "<div class='m-checkbox-inline'>";
        $output_html .= "<label class='m-checkbox'><input type='checkbox' name='" . $name . "' class='" . $extra . "' id='" . $name . "' value='" . $default_value . "' " . $checked . ' ' . $field_status . ">" . $checkboxText . "<span > </span></label>";
        $output_html .= "</div>";
        
        return $output_html;
    }

    /*
     * renders drop down select
     */
    static function render_select($xmlObject, $selected, $extra, $field_status)
    {
        $CI = &get_instance();
        $options = array();
        $output_html = '';
        $lookup_default_value = (string) $xmlObject['default_value'];
        $name = (string) $xmlObject['name'];
        
        if (strlen($lookup_default_value) > 0) {
            if ($lookup_default_value[0] === '#') {
                $lookup_default_value = $CI->config->item($lookup_default_value);
            }
        }
        
        if ($field_status == 'disabled') {
            $options[$lookup_default_value] = dashboard_lang('_NO_ITEM_HAS_SELECTED');
        } else {
            $options[$lookup_default_value] = dashboard_lang('_SELECT_FROM_DROPDOWN');
        }
        
        $total_options = ($xmlObject->count());
        for ($i = 0; $i < $total_options; $i ++) {
            
            $attributes = $xmlObject->option[$i]->attributes();
            $langkey_option = (string) $xmlObject->option[$i];
            $options[(string) $attributes['key']] = dashboard_lang($langkey_option);
        }
        
        $dashboard_dropdown = ($field_status == "disabled") ? '' : 'dashboard-dropdown';
        $disable_style = ($field_status == "disabled") ? 'style="-webkit-appearance: none;-moz-appearance: none;"' : '';
        
        $extra = "id='{$name}' class='{$name} form-control {$dashboard_dropdown} {$extra}' $field_status $disable_style";
        $output_html .= form_dropdown($name, $options, $selected, $extra);
        
        return $output_html;
    }

    /*
     * renders Lookup
     */
    static function render_lookup($xmlObject, $selected, $extra, $field_status)
    {
        $options = array();
        $output_html = '';
        $lookup_default_value = (string) $xmlObject['default_value'];
        $name = (string) $xmlObject['name'];
        if (isset($xmlObject['dropdown_placeholder']) && $xmlObject['dropdown_placeholder'] == 0){
            $options = array();
        } else if ($field_status == 'disabled') {
            $options[" "] = dashboard_lang('_NO_ITEM_HAS_SELECTED');
        } else {
            $options[" "] = dashboard_lang('_SELECT_FROM_DROPDOWN');
        }
        $options = array();
        $ref_table = (string) $xmlObject['ref_table'];
        $key = (string) $xmlObject['key'];
        $value = (string) $xmlObject['value'];
        $valueArray = explode(',', $value);
        
        $orderBy = $xmlObject['order_by'] ? (string) $xmlObject['order_by'] : $valueArray[0];
        $orderOn = $xmlObject['order_on'] ? (string) $xmlObject['order_on'] : 'ASC';
        
        $additional_condition = (string) $xmlObject['where_and'];
        $autosuggest = intval($xmlObject['autosuggest']);
        $custom_function = (string) $xmlObject['custom_function'];
        $reftbl_shared_status = intval($xmlObject['reftbl_shared_status']);
        $external_field = (string) $xmlObject['external_field'];
        
        if (isset($reftbl_shared_status) and $reftbl_shared_status == 1) {
            $account_id = get_default_account_id();
        } elseif ((isset($reftbl_shared_status) and $reftbl_shared_status == 2)) {
            $account_id = 0;
        } else {
            $account_id = get_account_id();
        }
        
        $additional_and = array();
        
        if (isset($additional_condition) and ! empty($additional_condition)) {
            $additional_and = explode(',', $additional_condition);
        }
        
        if (strlen($external_field) > 0) {
            // this condition is for filter field
            $current_user = BUserHelper::get_instance();
            if ($current_user->user->{$external_field}) {
                $additional_and[] = $key . ':' . $current_user->user->{$external_field};
            }
        }
        
        $CI = &get_instance();
        if (isset($autosuggest) && $autosuggest == 1) {
            
            $tableName = $CI->config->item("prefix") . $ref_table;
            $query = $CI->dashboard_model->lookup($tableName, $key, $valueArray, $orderBy, $orderOn, $additional_and, $account_id);
            foreach ($query->result() as $row) {
                $options[$row->{"select_key"}] = $row->{"select_value"};
            }
            $output_html .= form_dropdown_auto($name, $selected, $options, $ref_table, $key, $value, $orderBy, $orderOn, $extra, $field_status);
        } else {
            
            if (isset($custom_function) and $custom_function == 'getAllPermittedRoles') {
                $options = getAllPermittedRoles($account_id);
            } else {
                $tableName = $CI->config->item("prefix") . $ref_table;
                $query = $CI->dashboard_model->non_ajax_lookup($tableName, $key, $valueArray, $orderBy, $orderOn, $additional_and, $account_id);
                
                foreach ($query->result() as $row) {
                    if ($xmlObject['is_translated'] == 1) {
                        $options[$row->{"select_key"}] = dashboard_lang(strtoupper($row->{"select_value"}));
                    } else {
                        $options[$row->{"select_key"}] = $row->{"select_value"};
                    }
                }
            }
            
            $dashboard_dropdown = ($field_status == "disabled") ? '' : 'dashboard-dropdown';
            $disable_style = ($field_status == "disabled") ? 'style="-webkit-appearance: none;-moz-appearance: none;"' : '';
            
            $extra = "id='{$name}' ref-table='" . $ref_table . "' ref-key='" . $key . "' ref-value='" . $value . "'  class='form-control {$dashboard_dropdown} {$extra}' $field_status $disable_style";
            $output_html .= form_dropdown($name, $options, $selected, $extra, $field_status);
        }
        
        return $output_html;
    }

    static function render_text($xmlObject, $selected, $extra, $field_status)
    {
        $output_html = '';
        $name = $xmlObject['name'];
        $placeholder = (string) $xmlObject['placeholder'];
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        
        $data = array(
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'class' => 'form-control ' . $extra
        );
        $show_currency = (integer) $xmlObject['show_currency_symbol'];
        $default_currency = $xmlObject['symbol'];
        
        if ($show_currency > 0) {
            $output_html .= '<div class="input-group">';
        }
        
        $CI = &get_instance();
        $currency_show = $default_currency ? $default_currency : $CI->config->item('default_currency_symbol');
        
        if ($show_currency > 0) {
            
            $output_html .= '<span class="input-group-addon">' . $currency_show . '</span>';
            $output_html .= form_text($data, $selected, $field_status);
            $output_html .= '</div>';
        } else {
            $output_html .= form_text($data, $selected, $field_status);
        }
        
        return $output_html;
    }

    static function render_auto($xmlObject, $selected, $extra)
    {
        $options = array();
        $name = $xmlObject['name'];
        $output_html = '';
        $options[""] = dashboard_lang('_SELECT_FROM_DROPDOWN');
        
        $data = array(
            'name' => $name,
            'id' => 'selectid',
            'class' => 'form-control select2'
        );
        $extra = "type='select'";
        $output_html .= form_input($data, (string) $selected, $extra);
        
        return $output_html;
    }

    /*
     * static function render_editor($xmlObject, $selected, $extra, $default_value) { $name = $xmlObject ['name']; $output_html = ''; $db_helper = Dashboard_main_helper::get_instance (); $db_helper->set ( 'load_ckeditor', 1 ); $data = array ( 'name' => $name, 'id' => 'editor-' . $name, 'class' => 'ckeditor ' . $extra ); if (empty ( $selected )) { $selected_value = $default_value; } else { $selected_value = $selected; } $output_html .= form_textarea ( $data, $selected_value, $extra ); return $output_html; }
     */
    static function render_image($xmlObject, $selected, $extra, $field_status)
    {
        $name = $xmlObject['name'];
        $sub_directory = $xmlObject['sub_directory'];
        $noCheckboxDelete = (int) @$xmlObject['no_checkbox_delete'];
        $output_html = '';
        $CI = &get_instance();
        $img_upload_path = $CI->config->item('img_upload_path');
        $ajaxSavingClass="";
        if ( stripos($extra, "ajax-save", 0) !== false ) $ajaxSavingClass = "ajax-save";

        $width = (int) $xmlObject['width'];
        
        $height = (int) $xmlObject['height'];

        $output_html .= boo2_render_image($selected, $img_upload_path, $sub_directory, $name, $field_status, $ajaxSavingClass, $width, $height, $noCheckboxDelete );
        $data = array(
            'name' => $name,
            'id' => $name,
            'class' => $extra
        );
        
        if(empty($field_status)){  // View mode this Choose File input Button not shown
            $output_html .= form_upload($data, (string) $selected, $extra, $field_status);
        }
        
        
        return $output_html;
    }

    static function render_file($xmlObject, $selected, $extra, $field_status)
    {
        $name = $xmlObject['name'];
        $validation = $xmlObject['validation'];
        $output_html = '';
        $CI = &get_instance();
        $img_upload_path = $CI->config->item('img_upload_path');
        $ajaxSavingClass="";
        if ( stripos($extra, "ajax-save", 0) !== false ) $ajaxSavingClass = "ajax-save";
        $output_html .= boo2_render_file($selected, $img_upload_path, $name, $field_status, $ajaxSavingClass, $validation);
        $data = array(
            'name' => $name,
            'id' => $name,
            'class' => $extra
        );
        if(empty($field_status)){     // View mode this Choose File input Button not shown
            $output_html .= form_upload($data, (string) $selected, $extra, $field_status);
        }
        return $output_html;
    }

    static function render_password($xmlObject, $selected, $extra, $field_status)
    {
        $name = $xmlObject['name'];
        $output_html = '';
        $data = array(
            'name' => $name,
            'id' => $name,
            'class' => 'form-control ' . $extra
        );
        
        $output_html .= boo2_form_password($data, $selected, $field_status);
        
        return $output_html;
    }

    static function render_datetime($xmlObject, $selected, $extra, $field_status)
    {
        $CI = &get_instance();
        $name = $xmlObject['name'];
        $time_picker = intval($xmlObject['time_picker']);
        $disable_past = intval($xmlObject['disable_past']);
        $placeholder = (string) $xmlObject['placeholder'];
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        
        $default_date_format = $CI->config->item('#EDIT_VIEW_DATE_FORMAT');
        $default_date_time_format = $CI->config->item('#EDIT_VIEW_DATE_TIME_FORMAT');
        
        if (! isset($selected) or empty($selected)) {
            $selected = "";
        } else {
            
            if (isset($time_picker) and $time_picker) {
                $selected = date($default_date_time_format, $selected);
            } else {
                $selected = date($default_date_format, $selected);
            }
        }
        
        if (isset($time_picker) and $time_picker) {
            $date_picker_class = "date-time-picker";
            $date_format = "dd-mm-yyyy hh:ii:ss";
        } else {            
            if($disable_past){
                $date_picker_class = "date-picker-disable-past";
            } else{
                $date_picker_class = "date-picker";
            }
            $date_format = "dd-mm-yyyy";
        }
        
        $output_html = "
        <div class='input-group date {$date_picker_class}' data-date-format='{$date_format}'>
        <input type='text' name='$name' id='$name' placeholder='$placeholder' class='form-control $extra' value='$selected' $field_status>
        <span class='input-group-addon' $field_status>
        <i class='la la-calendar glyphicon-th'></i>
        </span>
        </div>";
        
        return $output_html;
    }

    static function render_hidden($xmlObject, $selected, $extra, $field_status)
    {
        $name = $xmlObject['name'];
        $output_html = '';
        $output_html .= form_hidden($name, (string) $selected);
        return $output_html;
    }

    static function render_date($xmlObject, $selected, $extra, $field_status)
    {
        $name = $xmlObject['name'];
        $placeholder = (string) $xmlObject['placeholder'];
        if (isset($placeholder[0]) and $placeholder[0] == '_') {
            $placeholder = dashboard_lang($placeholder);
        }
        $output_html = '';
        $data = array(
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'class' => 'form-control dashboard_datetime' . $extra
        );
        
        $output_html .= form_input($data, $selected, $field_status);
        return $output_html;
    }

    static function render_color($xmlObject, $selected, $extra)
    {
        
        // not implemented yet
        return '';
    }

    static function render_custom_select($xmlObject, $selected, $extra, $field_status)
    {
        $name = $xmlObject['name'];
        $output_html = '';
        
        $options = getAllMenu();
        $options['*'] = dashboard_lang('_SELECT_ALL');
        
        $extra = "id='{$name}' class='{$name} form-control dashboard-dropdown {$extra}' $field_status";
        $output_html .= form_dropdown($name, $options, $selected, $extra);
        
        return $output_html;
    }

    static function render_custom_select_opening_view($xmlObject, $selected, $extra, $field_status)
    {
        $name = $xmlObject['name'];
        $output_html = '';
        
        if(empty($selected)){
            $CI = &get_instance();
            $selected=$CI->config->item('#CORE_DEFAULTVIEW');
        }

        $options = getAllViewsList();
        
        if ($field_status == 'disabled') {
            $options[' '] = dashboard_lang('_NO_ITEM_HAS_SELECTED');
        } else {
            $options[' '] = dashboard_lang('_SELECT_FROM_DROPDOWN');
        }
        
        $extra = "id='{$name}' class='{$name} form-control dashboard-dropdown {$extra}' $field_status";
        $output_html .= form_dropdown($name, $options, $selected, $extra);
        
        return $output_html;
    }

    /*
     * this function get a xml object and return separate xml objects for per field
     */
    static function get_xml_object_array($data_load)
    {
        $xmlObjectArray = array();
        
        foreach ($data_load->field as $value) {
            
            $field_name = (string) $value["name"];
            $xmlObjectArray[$field_name] = $value;
        }
        
        return $xmlObjectArray;
    }

    static function render_messages($xmlObject, $selected, $extra, $field_status)
    {
        $CI = &get_instance();
        $CI->load->library('Messages');
        
        $template_name =  $CI->config->item("template_name");
        
        $data['current_url'] = '';
        $data['messages_type'] = (string) $xmlObject['message_table_name'];
        $data['id_position'] = (string) $xmlObject['id_position'];
        $data['all_message_list'] = $CI->messages->get_all_messages_list_with_entity($data['messages_type'], $CI->uri->segment($data['id_position']));
        $data['user_details'] = $CI->messages->get_user_details();
        $data['message_details'] = array();
        $data['user_list'] = $CI->messages->all_people_list($data['messages_type']);
        
        $return_html = $CI->load->view($template_name.'/core_'.$template_name.'/messages/entity_messages', $data, TRUE);
        
        if ($CI->uri->segment($data['id_position']) > 0) {
            
            $return_html .= " " . $CI->load->view($template_name.'/core_'.$template_name.'/messages/entity_messages_script', $data, TRUE);
        }
        
        $messages_url_script = "<script> $(document).ready(function(){";
        $messages_url_script .= $CI->load->view($template_name.'/core_'.$template_name.'/messages/message_div_script', array(), TRUE);
        $message_id = $CI->uri->segment($data['id_position'] + 1);
        $get_params = $CI->input->get('s');
        
        if (strlen($message_id) > 0 && $get_params == 'l') {
            
            $messages_url_script .= "
        
          var message_id = " . $CI->uri->segment($data['id_position'] + 1) . ";
             $('a[href=" . '"' . "#" . $CI->config->item('dashboard_msg_tab_name') . '"' . "]').trigger('click');
             $('tr[data-messsage-id='" . "+message_id+'" . "]').trigger('click');
        ";
        }
        
        $messages_url_script .= "});
        
    </script>";
        
        return $return_html . $messages_url_script;
    }

    /*
     * This Function will generate EAV based on XML parameter
     * In Intermediate Table , There will be no Id field
     * In Reference Table , there must be id field
     * In Current table , there must be id field
     *
     */
    static function render_eav($xmlObject, $selected, $extra, $field_status)
    {
        $CI = &get_instance();
        $template_name = $CI->config->item('template_name');
        $output_html = '';
        
        $data['ref_table_name'] = $ref_table_name = "`" . (string) $xmlObject['ref_table'] . "`";
        
        $ref_attribute_table_name = (string) $xmlObject['ref_attribute_table_name'];
        
        $data['ref_attribute_table_name'] = (! empty($ref_attribute_table_name)) ? "`" . $ref_attribute_table_name . "`" : '';
        
        $data['ref_attribute_field_name'] = $ref_attribute_field_name = (string) $xmlObject['ref_attribute_field_name'];
        $data['insert_table_name'] = $insert_table_name = (string) $xmlObject['insert_table'];
        $data['insert_table_primary_key'] = $insert_table_primary_key = 'id';
        $data['ref_key'] = $ref_key = 'id';
        $data['ref_value'] = $ref_value = (string) $xmlObject['ref_value'];
        $data['ref_attribute'] = $ref_attribute = (string) $xmlObject['ref_attribute'];
        $data['reference_key'] = $reference_key = (string) $xmlObject['insert_table_reference_key'];
        $data['foreign_key'] = $foreign_key = (string) $xmlObject['insert_table_forign_key'];

        // added for multi select functionality
        $data['order_on'] = (string) $xmlObject['order_on'];
        $data['order_by'] = (string) $xmlObject['order_by'];
        $data['is_translated'] = (string) $xmlObject['is_translated'];
        $data['field_table_name'] = $field_table_name = (string) $xmlObject['field_table_name'];
        
        $CI->load->model('Dashboard_Model');
        $data['id'] = $id = $CI->uri->segment(4);
        $data['all_values_list'] = $CI->dashboard_model->get_all_values_by_id($insert_table_name, $reference_key, $foreign_key, $id, $insert_table_primary_key);
        $data['xmlObject'] = $xmlObject;

        if ( file_exists( APPPATH . 'views/' . $template_name . '/' . $field_table_name . '/eav/render_values_list.php' ) ){
           $output_html .= $CI->load->view($template_name . '/'.$field_table_name.'/eav/render_values_list', $data, TRUE);
         }else { 
           $output_html .= $CI->load->view($template_name . '/core_'.$template_name.'/eav/render_values_list', $data, TRUE);
	    }
        
        $stylingClass = "";
        $styleAttr = "";
        if( $template_name != "metro" ){
            $stylingClass = "m--margin--top-0 m--margin-right-10 m--margin-bottom-10 m--margin-left-10";
            $styleAttr = "";
        }
        
        // $output_html .= "<script> var ref_table_list_".$xmlObject['name']." = ".'"'.B_form_helper::render_ref_table_options($ref_table_name, $ref_key, $ref_value).'"'.";
        $output_html .= "<script> var ref_table_list_" . $xmlObject['name'] . " =  " . '"' . B_form_helper::render_ref_table_options($ref_table_name, $reference_key, $selected_value = 0, $ref_key, $ref_value, $ref_attribute, $ref_attribute_table_name, $ref_attribute_field_name) . '"' . ";
                                  $('document').ready(function(){

        $('.ref_select').select2({});
    });
                                 $('.add_btn_row > .col-md-12 > .add_rows_" . $xmlObject['name'] . "').on('click',function(){
                                  var count = parseInt($(this).attr('data-count'));
                                  count= count+1;
                                  var new_row =" . '"' . "<div class='col-md-12 m--margin-bottom-10" . '"+count+' . '"' . "' " . $styleAttr . "><select style='display:inline; width:330px;' name='" . $reference_key . "[]' class='form-control select2 ref_select col-md-6'></select> &nbsp;&nbsp;&nbsp;&nbsp; <button type='button' style='display: inline;' count='" . '"+count+' . '"' . "' class='btn btn-danger remove_rows' onclick='remove_rows_" . $xmlObject['name'] . "(" . '"+count+' . '"' . ")' value=''><i class='fa fa-remove'></i></button></div>" . '";' . "$('.eav_area" . $xmlObject['name'] . "').append(new_row);$('.eav_area" . $xmlObject['name'] . " .'+count+' .ref_select').html(ref_table_list_" . $xmlObject['name'] . "); $('.eav_area" . $xmlObject['name'] . " .'+count+' .ref_select').select2({}); $('.add_rows_" . $xmlObject['name'] . "').attr('data-count',count); }); function remove_rows_" . $xmlObject['name'] . "(count) { $('.eav_area" . $xmlObject['name'] . " .'+count).remove(); }  </script>";
        return $output_html;
    }

    static function render_ref_table_options($ref_table_name, $reference_key, $selected_value, $ref_key, $ref_value, $ref_attribute, $ref_attribute_table_name, $ref_attribute_field_name)
    {
        $CI = & get_instance();
        $CI->load->model('Dashboard_Model');
        return $CI->dashboard_model->render_table_option_with_selected_value($ref_table_name, $reference_key, $selected_value, $ref_key, $ref_value, $ref_attribute, $ref_attribute_table_name, $ref_attribute_field_name);
        // return $CI->dashboard_model->generate_options_list($table_name, $key, $value);
    }

    static function allowDecimalMoneyFomate($value, $fractionDigits=2)
    {
        $CI = & get_instance();
        $default_format_from_config = strtolower($CI->config->item('#DEFAULT_MONEY_FORMAT'));
    
        if ($default_format_from_config == 'us') {
            $selected_value = $value;
        } else {
            $selected_value = str_replace(".", ",", $value);
        }
    
        return $selected_value;
    }
    
    static function customeMoneyFormate($value, $fractionDigits=2)
    {
        
        $CI = & get_instance();
        $default_format_from_config = strtolower($CI->config->item('#DEFAULT_MONEY_FORMAT'));
        $formattedValue = "";
        if ($default_format_from_config == 'us') {
            $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
            $fmt->setTextAttribute(NumberFormatter::NEGATIVE_PREFIX, "-");
            $fmt->setTextAttribute(NumberFormatter::NEGATIVE_SUFFIX, "");
            $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, $fractionDigits);
            $formattedValue = $fmt->formatCurrency($value, "USD");
        } else {
            $fmt = new NumberFormatter('de_DE', NumberFormatter::CURRENCY);
            $fmt->setTextAttribute(NumberFormatter::NEGATIVE_PREFIX, "-");
            $fmt->setTextAttribute(NumberFormatter::NEGATIVE_SUFFIX, "");
            $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, $fractionDigits);
            $formattedValue = $fmt->formatCurrency($value, "EUR");
        }
        
        $formattedValue = preg_replace('/[^0-9-,"."]/', '', $formattedValue);
        
        return $formattedValue;
    }

    static function customeMoneyToDecimal($value)
    {
        $CI = & get_instance();
        $default_format_from_config = strtolower($CI->config->item('#DEFAULT_MONEY_FORMAT'));
        if ($default_format_from_config == 'us') {
            $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
            $curr = "USD";
            $value = "$" . $value;
            $selected_value = $fmt->parseCurrency($value, $curr);
        } else {
            $value = str_replace( ".", ",",$value);
            $fmt = new NumberFormatter('de_DE', NumberFormatter::CURRENCY);
            $curr = "EUR";
            $str = "\xc2\xa0$";
            $value = $value . $str;
            $selected_value = $fmt->parseCurrency($value, $curr);
            if ($selected_value[0] == "$") {
                $selected_value = substr($selected_value, 1);
            }
        }
        
        return $selected_value;
    }
}
