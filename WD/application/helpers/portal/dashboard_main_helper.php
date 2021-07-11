<?php
/*
 * @author boo2mark
 *
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

require_once FCPATH . 'application/helpers/portal/Object.php';
require_once FCPATH . 'application/helpers/portal/table_helper.php';
require_once FCPATH . 'application/helpers/portal/user_helper.php';

class Dashboard_main_helper
{

    public static $_instance;

    public static function get_instance()
    {
        if (! isset(self::$_instance)) {
            self::$_instance = new Dashboard_main_helper();
        }
        return self::$_instance;
    }

    /*
     * get the variable
     */
    public function get($var)
    {
        if (isset($this->{$var})) {
            return $this->{$var};
        } else {
            return NULL;
        }
    }

    /*
     * set the variable
     */
    public function set($var, $value)
    {
        $this->{$var} = $value;
    }

    /*
     * initiate language loads
     */
    public static function init_languages_config()
    {
        $CI = & get_instance();
        
        $languages_config = array();
        
        $languages_table = $CI->config->item('prefix') . 'languages';
        
        $languages = $CI->db->get_where($languages_table, array(
            'active' => 1
        ))->result_array();
        
        if (isset($languages) && is_array($languages) && ! empty($languages)) {
            foreach ($languages as $language) {
                $languages_config[$language['code']] = $language['name'];
            }
        }
        
        $CI->config->set_item('site_languages', $languages_config);
    }

    /*
     * initiate translation loads
     */
    public static function init_translation($language)
    {
        $CI = & get_instance();
        $_table_prefix = $CI->config->item("prefix");
                
        $language_detail = $CI->db->get_where($_table_prefix . 'languages', array(
            'name' => $language
        ))->row_array();

        if (isset($language_detail) && isset($language_detail['id']) && $language_detail) {
            $transtaions = $CI->db->get_where($_table_prefix . 'translations', array(
                'language_id' => $language_detail['id'],
                'is_deleted' => 0
            ))->result_array();
          
            if (isset($transtaions) && is_array($transtaions) && ! empty($transtaions)) {
                foreach ($transtaions as $transtaion) {
                    $CI->lang->language[$transtaion['language_key']] = $transtaion['language_value'];
                }
            }
        }
    }

    /*
     * initial settings  loads
     */
    static function init_settings()
    {
        $CI = & get_instance();
        $_table_prefix = $CI->config->item("prefix");
        
        $account_id = get_default_account_id();
        $user_account_id = get_account_id();
        
        // NO ACCOUNT id, because it's shared
        $settings = $CI->db->get_where($_table_prefix . 'settings', array(
            "is_deleted" => 0
        ))->result_array();
        
        // Getting tenant settings based on account_id
        $settings_tenant = $CI->db->get_where($_table_prefix . 'settings_tenant', array(
            'account_id' => $user_account_id,
            "is_deleted" => 0
        ))->result_array();
               
        
        // common (app_owner) settings will be loaded last
        if (isset($settings) && is_array($settings) && ! empty($settings)) {
            foreach ($settings as $variable) {
                $CI->config->set_item($variable['setting'], $variable['value']);
            }
        }
        
        // tenant settings will be loaded first
        if (isset($settings_tenant) && is_array($settings_tenant) && ! empty($settings_tenant)) {
            foreach ($settings_tenant as $item) {
                $CI->config->set_item($item['key'], $item['value']);
            }
        }
    }
}

/*
 * renders the label in a form
 */
function boo2_render_form_label($fieldName, $description, $required = '', $lable_class = "", $info_btn="") 
{ 
    $position = "top"; 
    $text = dashboard_lang("_" . strtoupper($fieldName)); 
    $html = "<label class='m--pull-left control-label $lable_class' name='" . $fieldName . "' data-placement='" . $position . "' >"; 
    $html .= "<span class='label-".$fieldName."'>$text</span>"; 
    if ((bool) $required == 1) { 
        $html .= "<span class='required' aria-required='true'> * </span>"; 
    }     
    $html .= $info_btn; 
    $html .= '</label>'; 
    return $html; 
}

if (! function_exists('form_dropdown_auto')) {

    /**
     * Drop-down Menu with auto suggest
     *
     * @param mixed $name            
     * @param mixed $selected            
     * @param mixed $options            
     * @param mixed $ref_table            
     * @param mixed $key            
     * @param mixed $value            
     * @param mixed $extra            
     * @return string
     */
    function form_dropdown_auto($name = "", $selected = '', $options = array(), $ref_table = '', $key = '', $value = '', $order_by = '', $order_on = '', $extra = '', $field_status = '')
    {
        
        // $dashboard_dropdown = ($field_status == "disabled") ? '' : '';
        $disable_class = ($field_status == "disabled") ? 'hide-drop-down-icon' : '';
        $disable_style = ($field_status == "disabled") ? ' style="-webkit-appearance: none;-moz-appearance: none;" ' : '';
        $class = ($field_status == "disabled") ? '' : $name;
        
        $option_name = "";
        $option_value = "";
        
        if (isset($selected) && ! empty($selected)) {
            foreach ($options as $keys => $values) {
                if ($selected == $keys) {
                    $option_name = $values;
                    $option_value = $keys;
                }
            }
            
            $html = '<select ref-table="' . $ref_table . '" ref-key="' . $key . '" ref-value="' . $value . '" name="' . $name . '" id="' . $name . '" order_by="' . $order_by . '" order_on="' . $order_on . '" class="' . $class . ' ' . $disable_class . $extra . ' form-control" ' .$disable_style. $field_status . '>
        <option value=" ' . $option_value . ' ">' . $option_name . '</option>
        </select>';
        } else {
            
            $html = '<select ref-table="' . $ref_table . '" ref-key="' . $key . '" ref-value="' . $value . '" name="' . $name . '" id="' . $name . '" order_by="' . $order_by . '" order_on="' . $order_on . '" class="' . $class . ' ' . $disable_class . $extra . ' form-control" ' .$disable_style. $field_status . '>
        <option value="">' . dashboard_lang('_SELECT_FROM_DROPDOWN') . '</option>
        </select>';
        }
        
        return $html;
    }
}

/*
 * renders image in a form
 */
function boo2_render_image($src_image, $img_upload_path, $sub_directory, $name, $field_status, $ajaxSavingClass="", $width=100 , $height=0, $noCheckboxDelete=0)
{
     

    $html = '<div>';
    if ((stripos($src_image, "http://") !== false) || (stripos($src_image, "https://") !== false)) {
        $html .= '<a class="fancybox" href="' . $src_image . '"> <img ' ;
        if($width>0){
            $html .= 'width="'.$width.'" ';
        }  
        if($height>0){
            $html .= 'height="'.$height.'" ';
        }
        $html .= 'src="' . $src_image . '"> </a>';
        if ( $noCheckboxDelete == 0 ) {
            $html .= '<input type="checkbox" name="' . $name . '" class="delete-checkbox '. $ajaxSavingClass .'" value="1" ' . $field_status . '/> ' . dashboard_lang('_DELETE_THIS_IMAGE');
        }
    } else {
        if (! empty($src_image)) {
            $image_path = CDN_URL . $img_upload_path . $src_image;
            if (isset($sub_directory) and ! empty($sub_directory)) {
                $image_path = CDN_URL . $img_upload_path . $sub_directory . "/" . $src_image;
            }
            $html .= '<a class="fancybox" href="' . $image_path . '"> <img ';
            if($width>0){
                $html .= 'width="'.$width.'" ';
            }  
            if($height>0){
                $html .= 'height="'.$height.'" ';
            }
            $html .= 'src="' . $image_path . '"> </a>';
            if ( $noCheckboxDelete == 0 ) {
                $html .= '<input type="checkbox" class="delete-checkbox '. $ajaxSavingClass .'" name="' . $name . '" value="1" ' . $field_status . '/> ' . dashboard_lang('_DELETE_THIS_IMAGE');
            }
        }
    }
    $html .= '</div>';
    return $html;
}

/*
 * renders image in a form
 */
function boo2_render_file($src_file, $file_upload_path, $name, $field_status, $ajaxSavingClass="", $validation = "")
{
    $html = '<div>';
    if ((stripos($src_file, "http://") !== false) || (stripos($src_file, "https://") !== false)) {
        $ext = explode('/', $src_file);
        $file_name = end($ext);
        $html .= '<a href="' . $src_file . '">' . $file_name . '</a>';

        if( strpos ( $validation, "required" ) === false){

            $html .= '<input type="checkbox" class="delete-checkbox '. $ajaxSavingClass .'" name="' . $name . '" value="1" ' . $field_status . '/>' . dashboard_lang('_DELETE_THIS_FILE');
        }
        
    } else {
        if (! empty($src_file)) {
            $html .= '<span style="width: 100px !important; overflow: hidden; height: 25px; float: left;">';
            $html .= '<a href="' . base_url() . $file_upload_path . $src_file . '" target="_blank">' . $src_file . '</a>';
            $html .= '</span>';

            if( strpos ( $validation, "required" ) === false){
                $html .= '<input type="checkbox" class="delete-checkbox '. $ajaxSavingClass .'" name="' . $name . '" value="1" ' . $field_status . '/>' . dashboard_lang('_DELETE_THIS_FILE');
            }
        }
    }
    
    $html .= '</div>';
    return $html;
}

/*
 * this function renders form for add/edit data
 */
function form_email($data = '', $value = '', $extra = '')
{
    $defaults = array(
        'type' => 'email',
        'name' => is_array($data) ? '' : $data,
        'value' => $value
    );
    
    return '<input ' . _parse_form_attributes($data, $defaults) . _attributes_to_string($extra) . " />\n";
}

/*
 * renders text box in form
 */
function form_text($data = '', $value = '', $extra = '')
{
    $defaults = array(
        'type' => 'text',
        'pattern' => '([0-9]+\,)*[0-9]+(\.[0-9]+)*',
        'title' => 'example : 23,456 or 12,320.50',
        'name' => is_array($data) ? '' : $data,
        'value' => $value
    );
    
    return '<input ' . _parse_form_attributes($data, $defaults) . _attributes_to_string($extra) . " />\n";
}

/*
 * renders basic form
 */
function boo2_render_form($xmlObject, $selected = '')
{
    $CI = & get_instance();
    $type = (string) $xmlObject['type'];
    $name = (string) $xmlObject['name'];
    $required = (bool) $xmlObject['required'];
    $description = (string) $xmlObject['description'];
    
    $edit = 1;
    $lookup_default_value = (string) $xmlObject['default_value'];
    if (! (isset($required) and (bool) $required == 1)) {
        $required = 0;
    }
    
    $outupt_html = "";
    if ($type == 'hidden') {} else {
        
        $old_name = $name;
        $name = '_' . strtoupper($name);
        
        $outupt_html .= boo2_render_form_label(dashboard_lang($name), '', $required);
        
        if (($type == 'image') && ($selected != '')) {
            $img_upload_path = $CI->config->item('img_upload_path');
            $outupt_html .= boo2_render_image($selected, $img_upload_path, $old_name, $edit);
        } elseif (($type == 'file') && ($selected != '')) {
            $file_upload_path = $CI->config->item('file_upload_path');
            $outupt_html .= boo2_render_file($selected, $file_upload_path, $old_name);
        }
    }
    
    $type = $xmlObject['type'];
    $required = $xmlObject['required'];
    $name = (string) $xmlObject['name'];
    
    $extra = (isset($required) && (bool) $required) == 1 ? ' required' : '';
    
    switch ($type) {
        
        case "input":
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control' . $extra
            );
            $show_currency = (integer) $xmlObject['show_currency_symbol'];
            $default_currency = $xmlObject['symbol'];
            
            if ($show_currency > 0) {
                $outupt_html .= '<div class="input-group">';
            }
            
            $selected_value = "";
            if ($selected == "") {
                $selected_value = (string) $xmlObject['default_value'];
            } else {
                $selected_value = (string) $selected;
            }
            
            $currency_show = $default_currency ? $default_currency : $CI->config->item('default_currency_symbol');
            
            if ($show_currency > 0) {
                $outupt_html .= '<span class="input-group-addon">' . $currency_show . '</span>';
                
                $outupt_html .= form_input($data, $selected_value, '');
                
                $outupt_html .= '</div>';
            } else {
                $outupt_html .= form_input($data, $selected_value, '');
            }
            
            break;
        
        case "money":
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control money' . $extra
            );
            $show_currency = (integer) $xmlObject['show_currency_symbol'];
            $default_currency = $xmlObject['symbol'];
            
            if ($show_currency > 0) {
                $outupt_html .= '<div class="input-group">';
            }
            $align = 'style="text-align: right;" onkeypress=""';
            $selected_value = "";
            if ($selected == "") {
                $selected_value = (string) $xmlObject['default_value'];
            } else {
                $selected_value = (string) $selected;
            }
            
            $CI = &get_instance();
            if (strlen($CI->uri->segment('4')) > 0) {
                
                $default_format_from_config = strtolower($CI->config->item('#DEFAULT_MONEY_FORMAT'));
                
                if ($default_format_from_config == 'us') {
                    
                    $selected_value = format_price('us', $selected_value);
                } else {
                    
                    $selected_value = convert_eu_format($selected_value);
                    $selected_value = format_price('eu', $selected_value);
                }
            }
            
            $currency_show = $default_currency ? $default_currency : $CI->config->item('default_currency_symbol');
            
            if ($show_currency > 0) {
                $outupt_html .= '<span class="input-group-addon">' . $currency_show . '</span>';
                
                $outupt_html .= form_input($data, $selected_value, '', $align);
                
                $outupt_html .= '</div>';
            } else {
                $outupt_html .= form_input($data, $selected_value, '', $align);
            }
            
            break;
        
        case "email":
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control' . $extra
            );
            $show_currency = (integer) $xmlObject['show_currency_symbol'];
            $default_currency = $xmlObject['symbol'];
            
            if ($show_currency > 0) {
                $outupt_html .= '<div class="input-group">';
            }
            
            $selected_value = "";
            if ($selected == "") {
                $selected_value = (string) $xmlObject['default_value'];
            } else {
                $selected_value = (string) $selected;
            }
            
            $currency_show = $default_currency ? $default_currency : $CI->config->item('default_currency_symbol');
            
            if ($show_currency > 0) {
                
                $outupt_html .= '<span class="input-group-addon">' . $currency_show . '</span>';
                $outupt_html .= form_email($data, $selected_value, '');
                $outupt_html .= '</div>';
            } else {
                $outupt_html .= form_email($data, $selected_value, '');
            }
            
            break;
        
        case "text":
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control' . $extra
            );
            $show_currency = (integer) $xmlObject['show_currency_symbol'];
            $default_currency = $xmlObject['symbol'];
            
            if ($show_currency > 0) {
                $outupt_html .= '<div class="input-group">';
            }
            
            $selected_value = "";
            if ($selected == "") {
                $selected_value = (string) $xmlObject['default_value'];
            } else {
                $selected_value = (string) $selected;
            }
            
            $currency_show = $default_currency ? $default_currency : $CI->config->item('default_currency_symbol');
            
            if ($show_currency > 0) {
                
                $outupt_html .= '<span class="input-group-addon">' . $currency_show . '</span>';
                $outupt_html .= form_text($data, $selected_value, '');
                $outupt_html .= '</div>';
            } else {
                $outupt_html .= form_text($data, $selected_value, '');
            }
            
            break;
        
        case "auto":
            $options = array();
            $options[""] = dashboard_lang('_SELECT_FROM_DROPDOWN');
            
            $data = array(
                'name' => $name,
                'id' => 'selectid',
                'class' => 'form-control select2'
            );
            $extra = "type='select'";
            
            $outupt_html .= form_input($data, (string) $selected, $extra);
            
            break;
        
        case "textarea":
            $rows = (int) $xmlObject['rows'];
            
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control' . $extra,
                'rows' => ($rows != "") ? $rows : "10"
            );
            
            $outupt_html .= form_textarea($data, (string) $selected, $extra);
            
            break;
        
        case 'editor':
            
            $db_helper = Dashboard_main_helper::get_instance();
            $db_helper->set('load_ckeditor', 1);
            
            $data = array(
                'name' => $name,
                'id' => 'editor1',
                'class' => 'ckeditor' . $extra
            );
            $outupt_html .= form_textarea($data, (string) $selected);
            break;
        case 'color':
            $db_helper = Dashboard_main_helper::get_instance();
            
            $db_helper->set('load_colorpicker', 1);
            
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control colorpicker' . $extra
            );
            
            $outupt_html .= form_input($data, (string) $selected, $extra);
            
            break;
        
        case "radio":
            $total_options = ($xmlObject->count());
            
            for ($i = 0; $i < $total_options; $i ++) {
                $attributes = ($xmlObject->option[$i]->attributes());
                if ($selected == (string) $attributes['key']) {
                    $checked = true;
                } else {
                    $checked = false;
                }
                $data = array(
                    'name' => $name,
                    'id' => $name,
                    'value' => (string) $attributes['key'],
                    'checked' => $checked
                );
                $langkey_option = (string) $xmlObject->option[$i];
                $langkey_option = '_' . strtoupper($langkey_option);
                
                $outupt_html .= '<div class="radio">';
                $outupt_html .= '<label>' . form_radio($data) . nbs() . dashboard_lang($langkey_option) . '</label>';
                $outupt_html .= "</div>";
            }
            
            break;
        
        case "select":
            $options = array();
            $options[$lookup_default_value] = dashboard_lang('_SELECT_FROM_DROPDOWN');
            $total_options = ($xmlObject->count());
            for ($i = 0; $i < $total_options; $i ++) {
                
                $attributes = $xmlObject->option[$i]->attributes();
                $langkey_option = (string) $xmlObject->option[$i];
                $options[(string) $attributes['key']] = dashboard_lang($langkey_option);
            }
            
            $extra = "id='{$name}' class='{$name} form-control dashboard-dropdown {$extra}'";
            $outupt_html .= form_dropdown($name, $options, $selected, $extra);
            
            break;
        
        case "custom_selecdt":
            
            $options = array();
            $options[$lookup_default_value] = dashboard_lang('_SELECT_FROM_DROPDOWN');
            
            $options = getALLMenu();
            
            $extra = "id='{$name}' class='{$name} form-control dashboard-dropdown {$extra}'";
            $outupt_html .= form_dropdown($name, $options, $selected, $extra);
            
            break;
        
        case "lookup":
            
            $options = array();
            $options[$lookup_default_value] = dashboard_lang('_SELECT_FROM_DROPDOWN');
            $ref_table = (string) $xmlObject['ref_table'];
            $key = (string) $xmlObject['key'];
            $value = (string) $xmlObject['value'];
            $orderBy = $xmlObject['order_by'] ? (string) $xmlObject['order_by'] : $value;
            $orderOn = $xmlObject['order_on'] ? (string) $xmlObject['order_on'] : 'ASC';
            $additional_and = (string) $xmlObject['where_and'];
            $autosuggest = (bool) $xmlObject['autosuggest'];
            if (isset($autosuggest) && $autosuggest == 1) {
                
                $tableName = $CI->config->item("prefix") . $ref_table;
                $query = $CI->dashboard_model->lookup($tableName, $key, $value, $orderBy, $orderOn, $additional_and);
                foreach ($query->result() as $row) {
                    $options[$row->{$key}] = $row->{$value};
                }
                
                $outupt_html .= form_dropdown_auto($name, $selected, $options, $ref_table, $key, $value, $extra);
            } else {
                
                $tableName = $CI->config->item("prefix") . $ref_table;
                $query = $CI->dashboard_model->lookup($tableName, $key, $value, $orderBy, $orderOn, $additional_and);
                foreach ($query->result() as $row) {
                    $options[$row->{$key}] = $row->{$value};
                }
                $required_class = "";
                if (isset($required) && intval($required) > 0) {
                    $required_class = "required";
                }
                $extra = "id='{$name}' ref-table='" . $ref_table . "' ref-key='" . $key . "' ref-value='" . $value . "'  class='{$name} form-control dashboard-dropdown {$extra} {$required_class}'";
                $outupt_html .= form_dropdown($name, $options, $selected, $extra);
            }
            
            break;
        
        case "datetime":
            
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control dashboard_datetime' . $extra
            );
            if (is_null($selected)) {
                $selected = time();
            }
            
            $outupt_html .= form_input($data, date("Y-m-d", $selected), $extra);
            
            break;
        
        case "date":
            
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control dashboard_datetime' . $extra
            );
            
            $outupt_html .= form_input($data, $selected, $extra);
            
            break;
        
        case "image":
            
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => $extra
            );
            
            $extra = "";
            
            $outupt_html .= form_upload($data, (string) $selected, $extra);
            break;
        
        case "file":
            
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => $extra
            );
            
            $extra = "";
            $outupt_html .= form_upload($data, (string) $selected, $extra);
            break;
        
        case 'hidden':
            
            $outupt_html .= form_hidden($name, (string) $selected);
            break;
        
        case "password":
            
            $data = array(
                'name' => $name,
                'id' => $name,
                'class' => 'form-control' . $extra
            );
            $show_currency = (integer) $xmlObject['show_currency_symbol'];
            $default_currency = $xmlObject['symbol'];
            
            if ($show_currency > 0) {
                $outupt_html .= '<div class="input-group">';
            }
            
            $selected_value = "";
            if ($selected == "") {
                $selected_value = (string) $xmlObject['default_value'];
            } else {
                $selected_value = (string) $selected;
            }
            
            $currency_show = $default_currency ? $default_currency : $CI->config->item('default_currency_symbol');
            
            if ($show_currency > 0) {
                $outupt_html .= '<span class="input-group-addon">' . $currency_show . '</span>';
                
                $outupt_html .= boo2_form_password($data, $selected_value, $extra);
                
                $outupt_html .= '</div>';
            } else {
                $outupt_html .= boo2_form_password($data, $selected_value, $extra);
            }
            
            break;
        
        default:
            $outupt_html = "";
    }
    
    return $outupt_html;
}
/*
 * renders password form
 */
if (! function_exists('boo2_form_password')) {

    function boo2_form_password($data = '', $value = '', $field_status = '')
    {
        if (! is_array($data)) {
            $data = array(
                'name' => $data
            );
        }
        $data['type'] = 'password';
        
        $html = form_input($data, $value = "", $field_status);
        $html .= '
            <span class="input-group-btn hide_tooltip"> 
                <a class="info-button pull-right" data-toggle="tooltip" data-placement="right" title="Password info">
                <span class="badge badge-success"> i </span>
                </a>
            </span></div>';
        
        $html .= '
            <div class="form-group input-group field_password" id="field_id_re_password">
                <label class="pop_over control-label" name="Password" data-toggle="tooltip" data-placement="top" title="">' . dashboard_lang("_PLEASE_RETYPE_YOUR_PASSWORD") . '</label>
                <input type="password" name="re_password" value="" id="re_password" class="form-control" autocomplete="off">';
        
        return $html;
    }
}

function convert_eu_format($money)
{
    return str_replace(".", ",", $money);
}

/*
 * Span render if no need to edit field
 */
function boo2_render_span($xmlObject, $selected = "")
{
    $CI = & get_instance();
    $type = (string) $xmlObject['type'];
    $name = (string) $xmlObject['name'];
    $description = (string) $xmlObject['description'];
    $edit = 0;
    
    $output_html = "";
    if ($type == 'hidden') {} else {
        
        $name = '_' . strtoupper($name);
        $description = $name . '_INFO';
        $output_html .= boo2_render_form_label(dashboard_lang($name), dashboard_lang($description));
    }
    
    switch ($type) {
        
        case "hidden":
            $output_html .= form_hidden($name, (string) $selected);
            break;
        
        case "image":
            $img_upload_path = $CI->config->item('img_upload_path');
            $output_html .= boo2_render_image($selected, $img_upload_path, "", $edit);
            break;
        
        case "file":
            $output_html .= "<br/><span class='form-span'> " . (string) @$selected . "</span>";
            break;
        
        case "input":
            $output_html .= '<input type="text" id="disabledTextInput" placeholder="' . (string) @$selected . '" class="form-control" disabled>';
            break;
        case "money":
            $output_html .= '<input type="text" id="disabledTextInput" placeholder="' . (string) @$selected . '" class="form-control" style="text-align:right;" disabled>';
            break;
        
        case "email":
            $output_html .= '<input type="email" id="disabledTextInput" placeholder="' . (string) @$selected . '" class="form-control" disabled>';
            break;
        
        case "password":
            $output_html .= '<input type="password" id="disabledTextInput" placeholder="' . (string) @$selected . '" class="form-control" disabled>';
            break;
        
        case "datetime":
            $output_html .= '<input type="text" id="disabledTextInput" placeholder="' . date("Y-m-d", (string) @$selected) . '" class="form-control" disabled>';
            break;
        
        case "textarea":
            $output_html .= '<textarea name="" cols="40" rows="10" id="" class="form-control" disabled>' . (string) @$selected . '</textarea>';
            break;
        
        case "editor":
            $output_html .= '<span> ' . (string) @$selected . '</span>';
            break;
        
        case "lookup":
            $options = array();
            $ref_table = (string) $xmlObject['ref_table'];
            $key = (string) $xmlObject['key'];
            $value = (string) $xmlObject['value'];
            $orderBy = $xmlObject['order_by'] ? (string) $xmlObject['order_by'] : $value;
            $orderOn = $xmlObject['order_on'] ? (string) $xmlObject['order_on'] : 'ASC';
            
            $tableName = $CI->config->item("prefix") . $ref_table;
            $query = $CI->dashboard_model->lookup($tableName, $key, $value, $orderBy, $orderOn);
            
            foreach ($query->result() as $row) {
                $options[$row->{$key}] = $row->{$value};
            }
            
            $output_html .= '<select  ref-table="' . $ref_table . '" ref-key="' . $key . '" ref-value="' . $value . '" name="" id="" class="form-control dashboard-dropdown" tabindex="-1" title="" disabled>';
            $output_html .= '<option value="" selected="selected">' . $options[$selected] . '</option> </select>';
            break;
        
        case "select":
            $options = array();
            
            $total_options = ($xmlObject->count());
            for ($i = 0; $i < $total_options; $i ++) {
                $attributes = $xmlObject->option[$i]->attributes();
                $options[(string) $attributes['key']] = (string) $xmlObject->option[$i];
            }
            
            $output_html .= '<select name="" id="" class="form-control dashboard-dropdown" tabindex="-1" title="" disabled>';
            $output_html .= '<option value="" selected="selected">' . dashboard_lang($options[$selected]) . '</option> </select>';
            break;
        
        default:
            $output_html .= "<br/><span class='form-span'> " . (string) @$selected . "</span>";
    }
    return $output_html;
}

/*
 * User id return
 */
function get_user_id()
{
    $CI = & get_instance();
    $user_id_config = $CI->config->item('user_id');
    $user_id = $CI->session->userdata($user_id_config);
    return $user_id;
}

/*
 * User role
 */
function get_user_role()
{
    $CI = & get_instance();
    $user_role_config = $CI->config->item('user_role');
    $user_role = $CI->session->userdata($user_role_config);
    return $user_role;
}

/*
 * Get user current language
 */
function get_current_user_lang()
{
    $CI = & get_instance();
    $CI->load->model('language_model');
    $site_language = $CI->language_model->get_user_default_language();
    return $site_language;
}

/*
 * Get user opening view
 */
function get_user_opening_view($user_role)
{
    $CI = & get_instance();
    
    $user_roles_table = $CI->config->item('prefix') . "user_roles";
    
    $CI->db->select("opening_view");
    $data = $CI->db->get_where("{$user_roles_table}", array(
        "slug" => "$user_role"
    ))->row();
    if (isset($data->opening_view)) {
        $opening_view = $data->opening_view;
    } else {
        $opening_view = "";
    }
    return $opening_view;
}

/*
 * check a user have add permission or not
 *
 */
function have_add_permision($currentTableName)
{
    $CI = & get_instance();
    $addPermission = 0;
    $tableName = $CI->config->item('prefix') . 'permissions_row';
    
    $CI->db->select('add_permission,menu');
    $CI->db->group_start();
    $CI->db->where('menu', "*");
    $CI->db->or_where('menu', $currentTableName);
    $CI->db->group_end();
    $CI->db->where('role', get_user_role());
    $CI->db->where('is_deleted', 0);
    
    $query = $CI->db->get($tableName);
    $rows = $query->num_rows();
    if ($rows > 0) {
        
        if ($rows == 1) {
            $data = $query->row();
            $addPermission = $data->add_permission;
        } else {
            $data = $query->result();
            foreach ($data as $row) {
                if ($row->menu != '*') {
                    $addPermission = $row->add_permission;
                }
            }
        }
    }
    
    return $addPermission;
}

/*
 * show current version
 */
function show_current_version()
{
    $xml_path = FCPATH . APPPATH . 'core/dashboard.xml';
    if (file_exists($xml_path)) {
        $xml_data = simplexml_load_file($xml_path);
        echo '<small>' . lang('_DASHBOARD_VERSION_CURRENT') . ($xml_data->version) . '</small> <a href="' . base_url() . 'upgrade/confirm">' . lang('_DASHBOARD_VERSION_UPGRADE') . '</a>';
    }
}

/*
 * Render dashboard language
 */
function dashboard_lang($string = '')
{
    $string = trim($string);
    $langtext = lang($string);
    if (strlen($langtext)) {
        return $langtext;
    }
    
    $not_found_translated_string = format_string($string);
    $CI = &get_instance();
    $CI->load->model('dashboard_model');
    $inserted_string = "";

    if( key_exists( $string, $CI->lang->language) ){
        return $CI->lang->language[$string];
    }
    
    if ($string[1] != '_') {
        
        $inserted_string = $CI->dashboard_model->insert_translations($string, $not_found_translated_string);
    }
    $CI->lang->language[$string] = $inserted_string;
    if (strlen($inserted_string) != 0) {
        
        return $inserted_string;
    } else {
        
        return $string;
    }
}

/*
 * formats language translatable test
 */
function format_string($string)
{
    $first_character = substr($string, 0, 1);
    if ($first_character == '_') {
        
        $string = substr($string, 1, strlen($string) - 1);
    }
    $removed_underscore = str_replace('_', ' ', $string);
    return ucfirst(strtolower($removed_underscore));
}

/*
 * render top action part
 */
function boo2_render_aditional($class)
{
    $CI = get_instance();
    
    $data[''] = '';
    return $CI->load->view($class . '/' . $class . '_additional', $data);
}

/*
 * render top action part
 */
function boo2_render_top($additional_buttons = array())
{
    $CI = get_instance();
    $dashboard_helper = Dashboard_main_helper::get_instance();
    $dashboard_helper->get('edit_path');
    $data['site_url'] = site_url();
    $data['edit_path'] = $dashboard_helper->get('edit_path');
    $data['additional_buttons'] = $additional_buttons;
    return $CI->load->view( $CI->config->item("template_name") .'/core_' . $CI->config->item("template_name") . '/helper_render_action', $data);
}

/*
 * render edit top action part
 */
function boo2_render_edit_top()
{
    $CI = get_instance();
    $dashboard_helper = Dashboard_main_helper::get_instance();
    $data['id'] = @$dashboard_helper->get('id');
    $data['base_url'] = base_url();
    $data['site_url'] = rtrim(site_url(), '/') . '/';
    $data['edit_path'] = $dashboard_helper->get('edit_path');
    return $CI->load->view( $CI->config->item("template_name") .'/core_' . $CI->config->item("template_name") . '/helper_render_edit_button', $data);
}

/*
 * render viewonly top action part
 */
function boo2_render_viewonly_top()
{
    $CI = get_instance();
    $dashboard_helper = Dashboard_main_helper::get_instance();
    $data['id'] = @$dashboard_helper->get('id');
    $data['base_url'] = base_url();
    $data['site_url'] = rtrim(site_url(), '/') . '/';
    $data['edit_path'] = $dashboard_helper->get('edit_path');
    return $CI->load->view( $CI->config->item("template_name") .'/core_' . $CI->config->item("template_name") . '/helper_render_viewonly_button', $data);
}

/*
 * check if view exists
 */
function dashboard_view_exists($view_name)
{
    $file_path = FCPATH . APPPATH . 'views/' . $view_name . '.php';
    return file_exists($file_path);
}

/*
 * require a view
 */
function dashboard_load_view($view_name)
{
    $file_path = FCPATH . APPPATH . 'views/' . $view_name . '.php';
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

function format_price($money_format, $price)
{
    if ($money_format == 'eu') {
        
        $orginal_symbol = ',';
        $replace_symbol = '.';
    }
    
    if ($money_format == 'us') {
        
        $orginal_symbol = '.';
        $replace_symbol = ',';
    }
    
    $check_decimal_exists = strpos($price, $orginal_symbol);
    if ($check_decimal_exists !== false) {
        
        $exploded_price = explode("$orginal_symbol", $price);
        $after_decimal_price = $exploded_price[1];
        if (strlen($after_decimal_price) > 2) {
            
            $decimal_price = (int) substr($after_decimal_price, 0, 2);
            $third_place_price = (int) substr($after_decimal_price, 2, 1);
            if ($third_place_price > 5) {
                
                $decimal_price = $decimal_price + 1;
                return $exploded_price[0] . "$orginal_symbol" . $decimal_price;
            } else {
                
                return $exploded_price[0] . "$orginal_symbol" . $decimal_price;
            }
        } else 
            if (strlen($after_decimal_price) == 1) {
                
                return $price . "0";
            } else {
                
                return $price;
            }
    } else {
        
        if (strlen($price) == '0') {
            
            return "0" . $orginal_symbol . "00";
        } else {
            
            return $price . $orginal_symbol . "00";
        }
    }
}

function getAllMenu()
{
    $CI = get_instance();
    $menu_data = array();
    $CI->db->select('m.name, v.name as viewname');
    $CI->db->order_by('m.sort');
    $CI->db->where('m.is_deleted', 0);
    $CI->db->where('m.account_id', get_default_account_id());
    
    $menu_table = $CI->config->item('prefix') . 'menu as m';
    $CI->db->from($menu_table);
    $CI->db->join('views as v', 'v.id=m.views_id');
    $menu_items = $CI->db->get()->result_array();
    
    $menu_data['please_select'] = dashboard_lang("_PLEASE_SELECT");
    foreach ($menu_items as $menu) {

        $menu_name_source = $menu['name']; 

        if( stripos($menu_name_source, '_') !== 0 ){
            $menu_name_source = "_".$menu_name_source ;
        }
        
        $menu_data[$menu['viewname']] = dashboard_lang( strtoupper($menu_name_source) );
    }
    
    return $menu_data;
}

function getAllViewsList()
{
    $CI = get_instance();
    $menu_data = array();
    $CI->db->select('name');
    $CI->db->order_by('name');
    $CI->db->where('have_view', 1);
    $CI->db->where('is_deleted', 0);
    $CI->db->where('account_id', get_default_account_id());
    $menu_table = $CI->config->item('prefix') . 'menu';
    $menu_items = $CI->db->get($menu_table)->result_array();
    
    foreach ($menu_items as $menu) {
        
        $menu_data[$menu['name']] = dashboard_lang('_' . strtoupper($menu['name']));
    }
    
    return $menu_data;
}

function getAllPermittedRoles($account_id)
{
    $CI = get_instance();
    $currentUserACL = BUserHelper::getCurrentUserACL();
    $user_roles_table = $CI->config->item('prefix') . 'user_roles';
    
    $role_data = array();
    $CI->db->select('role_name, slug');
    $CI->db->order_by('role_name');
    
    // Current User ACL condition added here
    $CI->db->where('access_level >=', $currentUserACL);
    
    $CI->db->where('is_deleted', 0);
    $CI->db->where('account_id', $account_id);
    $possible_user_roles = $CI->db->get($user_roles_table)->result_array();
    
    $role_data[""] = dashboard_lang("_SELECT");
    
    foreach ($possible_user_roles as $role) {
        
        $role_data[$role['slug']] = $role['role_name'];
    }
    
    return $role_data;
}

function get_auto_ins_number($tbl_name, $name, $config_key = '#AUTO_INCREMENT_NUMBER_START_FROM')
{
    $CI = get_instance();
    $start_number = 1;
    $max_num = 0;
    $setting_start_number = $CI->config->item($config_key);
    if (isset($setting_start_number) and $setting_start_number > 0) {
        $start_number = $setting_start_number;
    }
    
    $CI->db->select_max($name);
    $query = $CI->db->get($tbl_name);
    if ($query->num_rows() > 0) {
        $data = $query->row();
        if (isset($data->$name) and $data->$name > $start_number) {
            $max_num = $data->$name;
        } else {
            $max_num = $start_number;
        }
    } else {
        $max_num = $start_number;
    }
    
    for (;;) {
        
        $max_num += 1;
        $CI->db->select('id');
        $CI->db->where('number', $max_num);
        $CI->db->where('table_name', $tbl_name);
        $CI->db->where('field', $name);
        $query = $CI->db->get('temp_numbers');
        if (! $query->num_rows() > 0) {
            $data_array = array(
                'number' => $max_num,
                'table_name' => $tbl_name,
                'field' => $name
            );
            $result = $CI->db->insert('temp_numbers', $data_array);
            if ($result) {
                break;
            }
        }
    }
    
    return $max_num;
}
