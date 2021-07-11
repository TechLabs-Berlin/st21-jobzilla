<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class B_field_helper
{

    static function render_field($field_object, $value, $tooltip = 0)
    {
        $type = (string) $field_object['type'];
        
        $function_name = 'render_' . strtolower($type);
        /*
         * check whether function written for this
         * type of field. Convention for function naming is
         * render_{type_name}
         */
        if (method_exists('B_field_helper', $function_name)) {
            return self::{$function_name}($field_object, $value);
        } else {
            return self::render_default_field($field_object, $value, $tooltip);
        }
    }

    static function render_image($field_object, $value)
    {
        $CI = & get_instance();
        $imageFolder = $CI->config->item('img_upload_path');
        $imageSizeShownHeight = $CI->config->item('img_thumbnail_size_height');
        $fileExist = $imageFolder . 'thumbs/' . $value;
        if ((stripos($value, "http://") !== false) || (stripos($value, "https://") !== false)) {
            if (((string) $field_object['type'] == 'image')) {
                echo '<img src="' . $value . '">';
            }
        } else {
            if ($value != "" && file_exists(FCPATH . $fileExist)) {
                echo '<img src="' . CDN_URL . $fileExist . '">';
            } else {
                $fileOriginalImage = $imageFolder . $value;
                if ($value != "" && file_exists(FCPATH . $fileOriginalImage)) {
                    echo '<img src="' . CDN_URL . $fileOriginalImage . '" height="' . $imageSizeShownHeight . '">';
                }
            }
        }
    }

    static function render_file($field_object, $value)
    {
        $CI = & get_instance();
        $imageFolder = $CI->config->item('img_upload_path');
        if (! empty($value)) {
            echo '<a target="_blank" href="' . CDN_URL . $imageFolder . $value . '" download>' . dashboard_lang("_DOWNLOAD_THIS_FILE") . '</a>';
        }
    }

    static function render_select($field_object, $value)
    {
        $optionsArray = array();
        $CI = & get_instance();
        $total_options = ($field_object->count());
        for ($i = 0; $i < $total_options; $i ++) {
            $langkey_option = (string) $field_object->option[$i];
            $attributes = $field_object->option[$i]->attributes();
            $optionsArray[(string) $attributes['key']] = dashboard_lang($langkey_option);
        }
        echo (isset($optionsArray[$value]) ? $optionsArray[$value] : $value);
    }

    static function render_datetime($field_object, $value)
    {
        if ($value != 0) {
            $CI = &get_instance();
            $default_date_format = $CI->config->item('#DEFAULT_DATE_FORMAT');
            echo date("$default_date_format", $value);
        }
    }

    static function render_money($field_object, $value)
    {
        $CI = &get_instance();
        $CI->load->helper('portal/dashboard_main');
        $default_format_from_config = strtolower($CI->config->item('#DEFAULT_MONEY_FORMAT'));
        if ($default_format_from_config == 'us') {
            
            echo $value = format_price('us', $value);
        } else {
            
            $value = convert_eu_format($value);
            echo $value = format_price('eu', $value);
        }
    }

    static function render_default_field($field_object, $value, $tooltip)
    {
        $CI = & get_instance();
        $listing_field_ellipsis_length = config_item("#LISTING_FIELD_ELLIPSIS_LENGTH");
        
        $value = strip_tags($value);
        $listing_tooltip = $value;
        if (! $tooltip) {
            
            if ($listing_field_ellipsis_length < strlen($value)) {
                $listing_tooltip = substr($value, 0, $listing_field_ellipsis_length) . " ...";
            }
        }
        
        echo $listing_tooltip;
    }
}