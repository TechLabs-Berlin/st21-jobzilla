<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Messages
{

    /*
     * constructor
     */
    function __construct()
    {}

    /*
     * get all notification by a user
     */
    public function get_all_notification_by_user_id($user_id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->get_all_notification_by_user_id($user_id);
    }

    public function get_all_messages_list_with_entity($entity_type, $contract_id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->get_all_messages_list_with_entity($entity_type, $contract_id);
    }

    function total_messages()
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->total_messages();
    }

    function get_all_messages_list()
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->get_all_messages_list();
    }

    function get_a_specific_message_details($id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->get_a_specific_message_details($id);
    }

    function remove_notification($message_id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->remove_notification($message_id);
    }

    function add_message($data)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->add_message($data);
    }

    function update_message($updateMessageArr)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->update_message($updateMessageArr);
    }

    function add_message_conversation($message_conversion_details)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->add_message_conversation($message_conversion_details);
    }

    function add_conversion_files($files_info)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->add_conversion_files($files_info);
    }

    function all_people_list($table_name)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->all_people_list($table_name);
    }

    function add_notification($notification_option, $people_list, $msg_conversation_id, $table_name)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->add_notification($notification_option, $people_list, $msg_conversation_id, $table_name);
    }

    function get_contract_number_from_message_id($id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->get_contract_number_from_message_id($id);
    }

    function get_user_details()
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->get_user_details();
    }

    function get_list_of_thread_people($message_id, $comment_user_id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->get_list_of_thread_people($message_id, $comment_user_id);
    }

    function check_all_people_selected($message_id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->check_all_people_selected($message_id);
    }
    function get_sent_to_people($message_id)
	{
         $CI = & get_instance();
         $CI->load->model('portal/Messages_model');
         return $CI->Messages_model->get_sent_to_people($message_id);
     }
    function message_posted_details($message_id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->message_posted_details($message_id);
    }

    function get_all_notification($entity, $entity_id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->get_all_notification($entity, $entity_id);
    }

    public function delete_msg_thread()
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        return $CI->Messages_model->delete_msg_thread();
    }

    public function deleteMessages($tableName, $fields)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        $ids = explode(',', $fields);
        
        foreach ($ids as $key => $value) {
            
            $result = $CI->Messages_model->haveMessage($tableName, $value);
            
            if ($result['status']) {
                
                foreach ($result['record_ids'] as $row) {
                    
                    $CI->Messages_model->deleteRelationalData($row);
                }
            }
        }
    }

    public function softDeleteMessages($tableName, $fields)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        $ids = explode(',', $fields);
        
        foreach ($ids as $key => $value) {
            
            $result = $CI->Messages_model->haveMessage($tableName, $value);
            if ($result['status']) {
                
                foreach ($result['record_ids'] as $row) {
                    
                    $CI->Messages_model->softDeleteRelationalData($row);
                }
            }
        }
    }

    public function get_message_details_by_id($message_id)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        $data = $CI->Messages_model->get_message_details_by_id($message_id);
        return $data;
    }
    public function send_email_to_external_users($notification_option, $data, $uploaded_files)
    {
        $CI = & get_instance();
        $CI->load->model('portal/Messages_model');
        $data = $CI->Messages_model->send_email_to_external_users($notification_option, $data, $uploaded_files);
        return $data;
    }
}
