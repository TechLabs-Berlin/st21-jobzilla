<?php
/*
 * @author Ashrafuzzaman Sujan
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class kyprolis_helper
{

    public static function saveUpdateQuestionaireTranslation($language, $id, $type, $text='')
    {
        $CI = & get_instance();
        $result = false;
        $langKey = "";
        if (! empty($language) && ! empty($id)) {
            $langKey = "_".$id."_".strtoupper($type);
            $CI->db->select('id');
            $CI->db->where('language_key', $langKey);
            $CI->db->where('is_deleted', 0);
            $query = $CI->db->get('translations');
            if ($query) {
                $data = $query->row();
                if (isset($data->id)) {
                    $CI->db->where("id", $data->id);
                    $result = $CI->db->update("translations", array('language_value' => $text));
                }else{
                    $dataArr = array(
                        'language_id' => $language,
                        'language_key' => $langKey,
                        'language_value' => $text
                    );
                    $result = $CI->db->insert("translations", $dataArr);
                }
            }
        }
    
        return $result;
    }   
    
    static function customeMoneyFormateOnlyDecimalSeparator($value = 0)
    {
        $CI = & get_instance();
        $default_format_from_config = strtolower($CI->config->item('#DEFAULT_MONEY_FORMAT'));
        if ($default_format_from_config == 'us') {
            $selected_value = number_format ( $value , 2 , '.', '');
        } else {
            $selected_value = number_format ( $value , 2 , ',', '');
        }
        return $selected_value;
    }
    
    public static function isFrontEndUser(){        
        $CI = get_instance();
        $CI->config->load('kyprolis');
        $isFrontEndUser = false;
        $userRole = get_user_role();
        $userRoleData = getDataFromId('user_roles', $userRole, 'slug', true);
        $configTableName = $CI->config->item('custom_layout_table');
        if(isset($userRoleData->opening_view) && $userRoleData->opening_view === $configTableName){
            $isFrontEndUser = true;
        }
        return $isFrontEndUser;
    }
}