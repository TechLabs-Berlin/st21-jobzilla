<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

function render_all_notification_list($user_id)
{
    $CI = & get_instance();
    $CI->load->library('portal/messages');
    $data['notification_list'] = $CI->messages->get_all_notification_by_user_id($user_id);
    $html = $CI->load->view($CI->config->item('template_name').'/core_' . $CI->config->item('template_name') . '/messages/notification_list', $data, TRUE);
    return $html;
}

/*
 * get list of people in a thread
 */
function get_list_of_thread_people($message_id, $comment_user_id = 0)
{
    $data = array();
    $CI = & get_instance();
    $CI->load->library('portal/messages');
    
    $data = $CI->messages->get_list_of_thread_people($message_id, $comment_user_id);
    
    return $data;
}

function get_sent_to_people($msg_conversation_id)
 {
     $data = array();
     $CI = & get_instance();
     $CI->load->library('portal/messages');
     
     $data = $CI->messages->get_sent_to_people($msg_conversation_id);
     
     return $data;
 }
function get_message_data_by_id($message_id)
{
    $CI = & get_instance();
    $CI->load->library('portal/messages');
    $data = $CI->messages->get_message_details_by_id($message_id);
    return $data;
}

/*
 * check if all user selected
 */
function check_all_people_selected($message_id)
{
    $CI = & get_instance();
    $CI->load->library('portal/messages');
    return $CI->messages->check_all_people_selected($message_id);
}

/*
 * check if user exists
 */
function check_user_exists($user, $user_array)
{
    if (in_array($user, $user_array)) {
        return true;
    } else {
        return false;
    }
}

/*
 * get user's details FROM message id
 */
function get_user_details_from_msg_id($msg_id)
{
    $CI = & get_instance();
    $CI->load->model('portal/Messages_model');
    return $CI->Messages_model->get_user_details_from_msg_id($msg_id);
}

/*
 * generic function to format date FROM unix timestamp
 */
function format_date_time($date_time)
{
    $time_diff = time() - $date_time;
    if ($time_diff > 86400) {
        
        return date('D, M d Y', $date_time);
    } else {
        
        $get_time_difference = difference_between_current_and_given_time($date_time);
        return $get_time_difference . " " . dashboard_lang("_AGO");
    }
}

/*
 * time difference
 */
function difference_between_current_and_given_time($post_datetime)
{
    // $time = strtotime($post_datetime);
    $time = time() - $post_datetime; // to get the time since that moment
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        31536000 => dashboard_lang("_YEAR"),
        2592000 => dashboard_lang("_MONTH"),
        604800 => dashboard_lang("_WEEK"),
        86400 => dashboard_lang("_DAY"),
        3600 => dashboard_lang("_HOUR"),
        60 => dashboard_lang("_MINUTE"),
        1 => dashboard_lang("_SECOND")
    );
    
    foreach ($tokens as $unit => $text) {
        if ($time < $unit)
            continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
    }
}

/*
 * get the url for message
 */
function get_message_url($entity)
{
    if ($entity == 'contracts') {
        
        return "contract/create_contact_action/";
    } else {
        
        return "dbtables/$entity/edit/";
    }
}

function user_can_delete_msg_thread($thread_id)
{
    $CI = & get_instance();
    $user_roles = $CI->session->userdata('user_role');
    
    if ($user_roles == 'super_admin') {
        
        $can_delete_any_msg = $CI->config->item('#SUPERS_CAN_DELETE_ANY_MESSAGES');
        if ($can_delete_any_msg == '0') {
            
            return '';
        } else {
            
            return "<a data-thread-id='" . $thread_id . "' href='javascript:void(0);' class='delete_msg_thread' >" . dashboard_lang('_DELETE') . "</a>";
        }
    } else {
        
        return '';
    }
}

function check_user_can_delete_msg()
{
    $CI = & get_instance();
    $user_roles = $CI->session->userdata('user_role');
    if ($user_roles == 'super_admin') {
        
        $can_delete_any_msg = $CI->config->item('#SUPERS_CAN_DELETE_ANY_MESSAGES');
        
        if ($can_delete_any_msg == '0') {
            
            return false;
        } else {
            
            return true;
        }
    } else {
        return false;
    }
}

function get_tab_position($table_name)
{
    $CI = & get_instance();
    $tab_position = 0;
    $xmlFile = FCPATH . $CI->config->item("xml_file_path") . DIRECTORY_SEPARATOR . $table_name . ".xml";
    $xmlData = simplexml_load_file($xmlFile) or die("Error: Cannot create object");
    foreach ($xmlData as $row) {
        if (isset($row['tab_position']) and $row['tab_position'] > 0) {
            $tab_position = $row['tab_position'];
        }
    }
    
    return $tab_position;
}

function userIdMatch($userId, $id)
{
    $CI = get_instance();
    $status = FALSE;
    
    $CI->db->select('user_id');
    $CI->db->where('id', $id);
    $data = $CI->db->get('message')->row();
    if (is_object($data)) {
        $dbUserId = $data->user_id;
    }
    
    if (! empty($dbUserId)) {
        
        if ($userId == $dbUserId) {
            return TRUE;
        }
        
        $CI->db->select('user_group');
        $CI->db->where('messages_id', $id);
        $CI->db->where('is_deleted', 0);
        $data = $CI->db->get('message_thread_notification')->row();
        if (is_object($data)) {
            $user_group = $data->user_group;
            if (! empty($user_group)) {
                if ($user_group == $userId or $user_group == '*') {
                    $status = TRUE;
                } else {
                    $users = explode(',', $user_group);
                    if (in_array($userId, $users)) {
                        $status = TRUE;
                    }
                }
            }
        }
    }
    
    return $status;
}