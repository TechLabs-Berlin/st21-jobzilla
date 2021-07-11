<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Messages_model extends CI_Model
{

    public $_prefix;

    var $_message_table = 'message';

    var $_dashboad_login = 'dashboard_login';

    var $_user_notification = 'user_notification';

    var $_message_conversation_details = 'message_conversation_details';

    function __construct()
    {
        parent::__construct();
        $this->_prefix = $this->config->item('prefix');
        
        $this->message_tbl = $this->_prefix . $this->_message_table;
        $this->dashboard_login_tbl = $this->_prefix . $this->_dashboad_login;
        $this->user_notification_tbl = $this->_prefix . $this->_user_notification;
        $this->conversion_details_tbl = $this->_prefix . $this->_message_conversation_details;
        // load model
        $this->load->model('portal/Amazon_SQS_Model');
    }

    function get_all_notification_by_user_id($user_id)
    {
        $message_tbl = "{$this->_prefix}message";
        $dashboard_login_tbl = "{$this->_prefix}dashboard_login";
        $user_notification_tbl = "{$this->_prefix}user_notification";
        $conversion_details_tbl = "{$this->_prefix}message_conversation_details";
        
        $this->db->select(" DISTINCT($this->message_tbl.id), $this->message_tbl.message_entity, $this->message_tbl.entity_id,{$this->user_notification_tbl}.post_datetime ");
        $this->db->from($this->user_notification_tbl);
        $this->db->join($this->conversion_details_tbl, "{$this->user_notification_tbl}.msg_conversation_id = {$this->conversion_details_tbl}.id");
        $this->db->join($this->message_tbl, "{$this->message_tbl}.id = {$this->conversion_details_tbl}.messages_id");
        $this->db->join($this->dashboard_login_tbl, "{$this->dashboard_login_tbl}.id = {$this->user_notification_tbl}.user_id");
        $this->db->where("$this->user_notification_tbl.user_id", $user_id);
        $this->db->where("$this->user_notification_tbl.is_notified", 0);
        $this->db->order_by("{$this->user_notification_tbl}.post_datetime", "DESC");
        
        $all_message_query = $this->db->get();
        $result = $all_message_query->result();
        
        $all_notification_list = array();
        
        foreach ($result as $result) {

            $message_id = $result->id;
            $conversation_details = $this->get_last_conversation_details_by_message_id($message_id);
            $data['message_id'] = $message_id;
            $data['details_message_id'] = $result->id;
            $data['user_name'] = $conversation_details['first_name'] . " " . $conversation_details['last_name'];
            $data['image'] = $conversation_details['image'];
            $data['messages'] = $conversation_details['message_conversation_details'];
            $post_datetime = $conversation_details['post_datetime'];
            $data['time_difference'] = $this->difference_between_current_and_given_time($post_datetime);
            $data['user_id'] = $conversation_details['user_id'];
            $data['message_entity'] = $result->message_entity;
            $data['entity_id'] = $result->entity_id;
            
            $all_notification_list[] = $data;
        }
        
        return $all_notification_list;
    }

    function get_last_conversation_details_by_message_id($messages_id)
    {
        $this->db->select("$this->conversion_details_tbl.*,  $this->dashboard_login_tbl.first_name, $this->dashboard_login_tbl.last_name, $this->dashboard_login_tbl.image");
        $this->db->from($this->conversion_details_tbl);
        $this->db->join($this->dashboard_login_tbl, "{$this->dashboard_login_tbl}.id = {$this->conversion_details_tbl}.user_id");
        $this->db->where("{$this->conversion_details_tbl}.messages_id", $messages_id);
        $this->db->order_by("{$this->conversion_details_tbl}.id", "DESC");
        $this->db->limit(1, 0);
        
        $query = $this->db->get();
        
        $result = $query->result_array();
        
        return $result[0];
    }

    function difference_between_current_and_given_time($post_datetime)
    {
        $time = $post_datetime;
        
        $time = time() - $time; // to get the time since that moment
        $time = ($time < 1) ? 1 : $time;
        $tokens = array(
            31536000 => dashboard_lang('_YEAR'),
            2592000 => dashboard_lang('_MONTH'),
            604800 => dashboard_lang('_WEEK'),
            86400 => dashboard_lang('_DAY'),
            3600 => dashboard_lang('_HOUR'),
            60 => dashboard_lang('_MINUTE'),
            1 => dashboard_lang('_SECOND')
        );
        
        foreach ($tokens as $unit => $text) {
            if ($time < $unit)
                continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
        }
    }

    function total_messages()
    {
        $this->db->select("{$this->_prefix}message.*, {$this->_prefix}dashboard_login.first_name , {$this->_prefix}dashboard_login.last_name, {$this->_prefix}dashboard_login.image");
        $this->db->from("{$this->_prefix}message ");
        $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}message.user_id = {$this->_prefix}dashboard_login.id");
        $this->db->order_by("$this->_prefix}message.id", "DESC");
        
        $query = $this->db->get();
        
        $result = $query->result();
        $user_id = $this->session->userdata('user_id');
        $all_messages_list = array();
        
        foreach ($result as $result) {

            $data['post_date_time'] = $result->post_datetime;
            $data['msg_title'] = $result->msg_title;
            $data['id'] = $result->id;
            $data['files_exists'] = $this->check_if_any_files_exists($result->id);
            $data['notification_exists'] = $this->check_if_any_notification_exists($result->id, $user_id);
            $last_conversation_details = $this->get_last_conversion_details($result->id);
            $data['user_name'] = $last_conversation_details['first_name'] . " " . $last_conversation_details['last_name'];
            $data['last_conversion_time'] = $last_conversation_details['post_datetime'];
            if ($data['last_conversion_time'] == null || empty($data['last_conversion_time'])) {} else {

                $all_messages_list[] = $data;
            }
        }
        
        $sort_col = array();
        
        foreach ($all_messages_list as $key => $row) {
            $sort_col[$key] = $row['last_conversion_time'];
        }
        
        array_multisort($sort_col, SORT_DESC, $all_messages_list);
        
        return count($all_messages_list);
    }

    function get_all_messages_list()
    {
        $str = $this->session->userdata('search_str');
        if (strlen($str) > 0) {

            $this->db->select(" {$this->_prefix}message.*, {$this->_prefix}dashboard_login.first_name , {$this->_prefix}dashboard_login.last_name, {$this->_prefix}dashboard_login.image");
            $this->db->from("{$this->_prefix}message");
            $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}message.user_id = {$this->_prefix}dashboard_login.id");
            $this->db->join("{$this->_prefix}message_conversation_details", "{$this->_prefix}message_conversation_details.messages_id = {$this->_prefix}message.id ", "left");
            $this->db->like("({$this->_prefix}message.msg_title", $str);
            $this->db->or_like("{$this->_prefix}dashboard_login.first_name", $str);
            $this->db->or_like("{$this->_prefix}dashboard_login.last_name", $str);
            $this->db->or_like("{$this->_prefix}message_conversation_details.message_conversation_details", $str);
            $this->db->order_by("{$this->_prefix}message.id", "ASC");
        } else {

            $this->db->select("{$this->_prefix}message.*, {$this->_prefix}dashboard_login.first_name , {$this->_prefix}dashboard_login.last_name, {$this->_prefix}dashboard_login.image");
            $this->db->from("{$this->_prefix}message");
            $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}message.user_id = {$this->_prefix}dashboard_login.id");
            $this->db->order_by("{$this->_prefix}message.id", "ASC");
        }
        
        $query = $this->db->get();
        $result = $query->result();
        $user_id = $this->session->userdata('user_id');
        $all_messages_list = array();
        
        foreach ($result as $result) {

            $data['post_date_time'] = $result->post_datetime;
            $data['msg_title'] = $result->msg_title;
            $data['message_entity'] = $result->message_entity;
            $data['entity_id'] = $result->entity_id;
            $data['id'] = $result->id;
            $data['files_exists'] = $this->check_if_any_files_exists($result->id);
            $data['notification_exists'] = $this->check_if_any_notification_exists($result->id, $user_id);
            $last_conversation_details = $this->get_last_conversion_details($result->id);
            $data['user_name'] = $last_conversation_details['first_name'] . " " . $last_conversation_details['last_name'];
            $data['last_conversion_time'] = $last_conversation_details['post_datetime'];
            
            if ($data['last_conversion_time'] == null || empty($data['last_conversion_time'])) {} else {

                $all_messages_list[] = $data;
            }
        }
        
        $sort_col = array();
        
        foreach ($all_messages_list as $key => $row) {
            $sort_col[$key] = $row['last_conversion_time'];
        }
        
        array_multisort($sort_col, SORT_DESC, $all_messages_list);
        
        return $all_messages_list;
    }

    function get_search_words($word, $limit)
    {
        $limit = 10;
        $finalResult = array();
        $str = $word;
        $tableField = array(
            'dashboard_login.last_name',
            'dashboard_login.first_name',
            'message.msg_title',
            'message_conversation_details.message_conversation_details'
        );
        $all_result = array();
        
        if (strlen($str) > 0) {

            foreach ($tableField as $field) {

                $this->db->reset_query();
                
                $this->db->select("DISTINCT ($this->_prefix $field) AS value ");
                $this->db->from("{$this->_prefix}message");
                $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}message.user_id = {$this->_prefix}dashboard_login.id");
                $this->db->join("{$this->_prefix}message_conversation_details", "{$this->_prefix}message_conversation_details.messages_id = {$this->_prefix}message.id", "left");
                $this->db->like("{$this->_prefix}message.msg_title", $str);
                $this->db->or_like("{$this->_prefix}dashboard_login.first_name", $str);
                $this->db->or_like("{$this->_prefix}dashboard_login.last_name", $str);
                $this->db->or_like("{$this->_prefix}message_conversation_details.message_conversation_details", $str);
                
                $query_result = $this->db->get()->result();
                
                foreach ($query_result as $result) {

                    $finalResult[] = $result;
                }
            }
        }
        
        return $finalResult;
    }

    function get_last_comment($id = 0)
    {
        $this->db->select('*');
        $this->db->from("{$this->_prefix}message_conversation_details");
        $this->db->where("messages_id", $id);
        $this->db->order_by("id", "DESC");
        $this->db->limit(1, 0);
        
        $res = $this->db->get()->result();
        
        if (sizeof($res) > 0) {

            return $res[0]->message_conversation_details;
        } else {
            return "";
        }
    }

    function get_all_messages_list_with_entity($entity_type, $entity_id)
    {
        $this->db->select("{$this->_prefix}message.*, {$this->_prefix}dashboard_login.first_name , {$this->_prefix}dashboard_login.last_name, {$this->_prefix}dashboard_login.image");
        $this->db->from("{$this->_prefix}message");
        $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}message.user_id = {$this->_prefix}dashboard_login.id");
        $this->db->where("{$this->_prefix}message.message_entity", "$entity_type");
        $this->db->where("{$this->_prefix}message.entity_id", "$entity_id");
        $this->db->order_by("{$this->_prefix}message.id", "DESC");
        
        $query = $this->db->get();
        $result = $query->result();
        $user_id = $this->session->userdata('user_id');
        $all_messages_list = array();
        
        foreach ($result as $result) {

            $data['post_date_time'] = $result->post_datetime;
            $data['msg_title'] = $result->msg_title;
            $data['id'] = $result->id;
            $data['last_comment'] = $this->get_last_comment($result->id);
            $data['discussion_count'] = $this->get_total_discussion_count($result->id);
            $data['files_exists'] = $this->check_if_any_files_exists($result->id);
            $data['notification_exists'] = $this->check_if_any_notification_exists($result->id, $user_id);
            $last_conversation_details = $this->get_last_conversion_details($result->id);
            $data['user_name'] = $last_conversation_details['first_name'] . " " . $last_conversation_details['last_name'];
            $data['last_conversion_time'] = $last_conversation_details['post_datetime'];
            $data['image'] = $last_conversation_details['image'];
            if ($data['last_conversion_time'] == null || empty($data['last_conversion_time'])) {} else {

                $all_messages_list[] = $data;
            }
        }
        
        $sort_col = array();
        
        foreach ($all_messages_list as $key => $row) {
            $sort_col[$key] = $row['last_conversion_time'];
        }
        
        array_multisort($sort_col, SORT_DESC, $all_messages_list);
        
        return $all_messages_list;
    }

    function get_total_discussion_count($msg_id)
    {
        $query = $this->db->get_where("{$this->_prefix}message_conversation_details", array(
            'messages_id' => $msg_id
        ));
        return $query->num_rows();
    }

    function get_contract_number_from_message_id($id)
    {
        $this->db->select('entity_id');
        $entity_id = $this->db->get_where("{$this->_prefix}message", array(
            'id' => $id
        ))->result_array()[0]['entity_id'];
        return $this->get_contract_number($entity_id);
    }

    function get_contract_number($entity_id)
    {
        $contract_details = $this->db->get_where("{$this->_prefix}contracts", array(
            'id' => $entity_id
        ))->result_array()[0];
        return $contract_details['contract_number'];
    }

    function get_last_conversion_details($message_id = 0)
    {
        $this->db->select("{$this->_prefix}message_conversation_details.post_datetime, {$this->_prefix}dashboard_login.*,{$this->_prefix}message_conversation_details.id");
        $this->db->from("{$this->_prefix}message_conversation_details");
        $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}message_conversation_details.user_id = {$this->_prefix}dashboard_login.id");
        $this->db->where("{$this->_prefix}message_conversation_details.messages_id", $message_id);
        $this->db->order_by("{$this->_prefix}message_conversation_details.id", "DESC");
        $this->db->limit(1, 0);
        
        $result = $this->db->get()->result_array();
        if( $result){
            return $result[0];
        }
       
    }

    function check_if_any_notification_exists($messages_id, $user_id)
    {
        $this->db->select("*");
        $this->db->from("{$this->_prefix}message_conversation_details");
        $this->db->join("{$this->_prefix}user_notification", "{$this->_prefix}user_notification.msg_conversation_id = {$this->_prefix}message_conversation_details.id");
        $this->db->where("{$this->_prefix}message_conversation_details.messages_id", $messages_id);
        $this->db->where("{$this->_prefix}user_notification.user_id", $user_id);
        $this->db->where("{$this->_prefix}user_notification.is_notified", 0);
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {

            return 1;
        } else {

            return 0;
        }
    }

    function check_if_any_files_exists($message_id)
    {
        $this->db->select("*");
        $this->db->from("{$this->_prefix}message_conversation_details");
        $this->db->join("{$this->_prefix}conversation_files", "{$this->_prefix}message_conversation_details.id = {$this->_prefix}conversation_files.msg_conversation_id");
        $this->db->where("{$this->_prefix}message_conversation_details.messages_id", $message_id);
        
        $query = $this->db->get();
        if ($query->num_rows() > 0) {

            return 1;
        } else {

            return 0;
        }
    }

    function remove_notification($message_id)
    {
        $this->db->select("id");
        $query = $this->db->get_where("{$this->_prefix}message_conversation_details", array(
            "messages_id" => $message_id
        ));
        $result = $query->result();
        
        $user_id = $this->session->userdata('user_id');
        foreach ($result as $result) {

            $msg_conversion_id = $result->id;
            
            $this->db->where("msg_conversation_id", $msg_conversion_id);
            $this->db->where("user_id", $user_id);
            $this->db->update("{$this->_prefix}user_notification", array(
                'is_notified' => 1
            ));
        }
    }

    function add_message($data)
    {
        $table_name = $this->_prefix . "message";
        $this->db->insert($table_name, $data);
        $msg_id =  $this->db->insert_id();
        
        $this->Events_model->executeTableEntryEvent('add_entry', $table_name, $data, $msg_id );
        
        return $msg_id;
    }

    function update_message($updateMessageArr = array('id'))
    {
        $table_name = $this->_prefix . "message";
        $this->db->where('id', $updateMessageArr['message_id']);
        $updateArr = array(
            'message_thread_id' => $updateMessageArr['message_id'],
            'msg_text' => $updateMessageArr['message_id'],
            'attachment' => 0
        );
        if (isset($updateMessageArr['have_attachment']) && $updateMessageArr['have_attachment']) {
            $updateArr['attachment'] = 1;
        }
        $this->db->update($table_name, $updateArr);
        return true;
    }

    function add_message_conversation($data)
    {
        $table_name = $this->_prefix . "message_conversation_details";
        $this->db->insert($table_name, $data);
        $msg_conversion_id =  $this->db->insert_id();
        
        $this->Events_model->executeTableEntryEvent('add_entry', $table_name, $data, $msg_conversion_id );
        
        return $msg_conversion_id;
    }

    function add_conversion_files($files_info)
    {
        $table_name = $this->_prefix . "conversation_files";
        $this->db->insert($table_name, $files_info);
        $files_id =  $this->db->insert_id();
        
        $this->Events_model->executeTableEntryEvent('add_entry', $table_name, $files_info, $files_id );
        
        return $files_id;
    }

    function all_people_list($table_name = '')
    {
        $current_user_account_id = get_account_id( 1 ) ;
        $app_owner_role = $this->config->item("#APP_OWNER_ROLE");
        
        $this->db->select("DISTINCT({$this->_prefix}dashboard_login.id),{$this->_prefix}dashboard_login.first_name, {$this->_prefix}dashboard_login.last_name,{$this->_prefix}dashboard_login.email");
        $this->db->from("{$this->_prefix}dashboard_login");
        $this->db->join("{$this->_prefix}permissions_row", "{$this->_prefix}dashboard_login.role = {$this->_prefix}permissions_row.role");
        $this->db->where("{$this->_prefix}dashboard_login.account_id", $current_user_account_id);
        $this->db->where("{$this->_prefix}dashboard_login.is_deleted", 0);
        $this->db->where("{$this->_prefix}permissions_row.is_deleted", 0);
        $this->db->order_by("{$this->_prefix}dashboard_login.first_name", 'ASC');
        if ( strlen($app_owner_role) > '0' ) {

         $this->db->where("{$this->_prefix}dashboard_login.role != '$app_owner_role'");
     }

     if (! empty($table_name)) {
        $this->db->where("({$this->_prefix}permissions_row.menu='*' OR {$this->_prefix}permissions_row.menu='{$table_name}')", NULL, FALSE);
    }

    $query = $this->db->get();
    $data = array();
    $return_data = array();
    $current_user_id = $this->session->userdata('user_id');
    foreach ($query->result_array() as $user) {

        if ($current_user_id != $user['id']) {

            $data['first_name'] = $user['first_name'];
            $data['last_name'] = $user['last_name'];
            $data['email'] = $user['email'];
            $data['id'] = $user['id'];
            $return_data[] = $data;
        }
    }
    return $return_data;
}
function add_notification($notification_option, $people_list, $msg_conversation_id, $table_name)
{
    $users = array();
    $message_id = $this->get_message_id_from_conversion_id($msg_conversation_id);
    $check_message_exists = $this->check_message_id_exists($message_id);
    $message_thread['user_group'] = 0;

    $sender_id = $this->session->userdata("user_id");
    
    if ($notification_option == 'all') {
        
        $all_people_list = $this->all_people_list($table_name);
        $users = array();
        foreach ($all_people_list as $people) {
            
            $users[] = $people['id'];
        }
        
        $message_thread['user_group'] = "*";
    }
    if ($notification_option == 'specific_people') {

        $user_group = array();

        foreach ($people_list as $people) {
            
            if ( !in_array ( $people, $users ) ) {

                $user_group[] = "#" . $people . "#";
                $users[] = $people;
            }
        }

        if ( !in_array ( $sender_id, $users ) ) {

            $user_group[] = "#" . $sender_id . "#";
        }
        
        $message_thread['user_group'] = implode(',', $user_group);
    }
    
    if ($message_thread['user_group'] == null) {
        
        $message_thread['user_group'] = 0;
    }
    
    $users = array_unique ($users);
    
    for ($count = 0; $count < sizeof($users); $count ++) {
        
        $data['msg_conversation_id'] = $msg_conversation_id;
        $data['is_notified'] = 0;
        $data['is_email_notified'] = 0;
        $data['post_datetime'] = date($this->config->item('#EDIT_VIEW_DATE_TIME_FORMAT'));
        $data['user_id'] = $users[$count];
        $table_name = $this->_prefix . "user_notification";
        $this->db->insert($table_name, $data);
        
        $notification_id = $this->db->insert_id();
        $this->Events_model->executeTableEntryEvent('add_entry', $table_name, $data, $notification_id );
    }
    
    // push data to SQS for email notification
      $this->Amazon_SQS_Model->pushDataToSqsForEmailNotification();
    
    if ($check_message_exists) {
        
        $this->db->where('messages_id', $message_id);
        $this->db->update($this->_prefix . 'message_thread_notification', $message_thread);
    } else {
        $message_thread['messages_id'] = $message_id;
        $this->db->insert($this->_prefix . 'message_thread_notification', $message_thread);
        $notification_id = $this->db->insert_id();
        
        $this->Events_model->executeTableEntryEvent('add_entry', $table_name, $message_thread, $notification_id );
    }
}

  public function get_userId($message_id)
 	{
 		$ci= &get_instance();
 		$ci->db->select('*');
 		$ci->db->from('message');
 		$ci->db->where('id', $message_id);
 		$ci->db->where('is_deleted', 0);
 		$result = $ci->db->get()->row_array();
 		return  $result['user_id'];
 	
     }
     
function prepare_email_notification($notification_option, $people_list, $msg_conversation_id)
{
    $return_url = explode("?", $_SERVER['REQUEST_URI'])[1];
    $mail_return_url = explode('=', $return_url)[1];
    $users = array();
    if ($notification_option == 'all') {

        $all_people_list = $this->all_people_list();
        $users = array();
        foreach ($all_people_list as $people) {

            $users[] = $people->id;
        }
    }
    if ($notification_option == 'specific_people') {

        $users = $people_list;
    }

    $posted_data_details = $this->posted_user_details($msg_conversation_id);
    $entity = $posted_data_details['entity'];
    $this->load->config('vdk');
    $sending_url = $this->config->item($entity . "_details_url");
    $subject = dashboard_lang("_RE") . " : " . $posted_data_details['msg_title'];
    $msg_data['return_url'] = $mail_return_url;
    $msg_data['website_name'] = $this->config->item('site_name');
    $msg_data['user_list'] = $this->get_users_list($users);
    $msg_data['posted_data_details'] = $posted_data_details;

    $message = $this->load->view($this->template_name.'/core_'.$this->template_name.'/messages/email_messages', $msg_data, true);
    for ($count = 0; $count < sizeof($users); $count ++) {

        $data['msg_conversation_id'] = $msg_conversation_id;
        $data['is_notified'] = 0;
        $data['is_email_notified'] = 0;
        $data['post_datetime'] = time();
        $data['user_id'] = $users[$count];

        $sending_email_address = $this->get_email_address_from_user_id($data['user_id']);
        $new_message = $message . $this->get_users_list($users);

        $this->load->model('Mail_Model');
        $from_name = $posted_data_details['posted_user_name'] . " (" . $this->config->item('message_site_name') . ")";
        $from_email = $this->Mail_Model->get_from_mail_address();

        $file = $this->get_files_from_conversation($msg_conversation_id);
        $this->Mail_Model->send_mail($sending_email_address, $from_name, $from_email, $new_message, $subject, $file, '', '', '');
    }
}

function format_date_time($date_time)
{
    return date($this->config->item('#EDIT_VIEW_DATE_TIME_FORMAT'), strtotime($date_time));
}

function get_users_list($users)
{
    $users = array_unique($users); 
	$users = array_values($users);
    $list = "";
    $last = sizeof($users) - 1;
    $allUsers = [];

    for ($count2 = 0; $count2 <= sizeof($users); $count2 ++) {

            // if ( strlen($users[$count2]) > '0' ) {

            //     if ($count2 == $last) {

            //         $user_info = $this->get_user_name_from_user_id($users[$count2]);
            //         $allUsers[] = @$user_info['first_name'] . " " . @$user_info['last_name'] . " ";
            //     } else {

            //         $user_info = $this->get_user_name_from_user_id($users[$count2]);
            //         $allUsers[] = @$user_info['first_name'] . " " . @$user_info['last_name'] . ", ";
            //     }
            // }

        $user_info = $this->get_user_name_from_user_id($users[$count2]);
        $user_name = @$user_info['first_name'] . " " . @$user_info['last_name'];
        if(trim($user_name)!='')
            $allUsers[]= $user_name;

    }  

    return implode(", ", $allUsers);
}

function get_email_address_from_user_id($id)
{
    $this->db->select('email');
    $this->db->from("{$this->_prefix}dashboard_login");
    $this->db->where("id", $id);

    return $this->db->get()->result_array()[0]['email'];
}

function get_files_from_conversation($msg_conversation_id)
{
    $query = $this->db->get_where("{$this->_prefix}conversation_files", array(
        "msg_conversation_id" => $msg_conversation_id
    ));
    if ($query->num_rows() > 0) {

        return $query->result_array()[0]['file_location'];
    } else {
        return "";
    }
}

function get_user_name_from_user_id($id)
{
    if (strlen($id) == '0') {
        $id = 0;
    }

    $result = array();

    $this->db->select('first_name, last_name');
    $result = $this->db->get_where("{$this->_prefix}dashboard_login", array(
        "id" => $id
    ))->result_array();

    if (sizeof($result) > 0) {
        $result = $result[0];
    }

    return $result;
}

function posted_user_details($msg_conversion_id)
{
    $return_data = array();

    $select_fields = array(
        "{$this->_prefix}dashboard_login.*",
        "{$this->_prefix}message_conversation_details.post_datetime",
        "{$this->_prefix}message_conversation_details.user_id",
        "{$this->_prefix}message_conversation_details.message_conversation_details",
        "{$this->_prefix}message_conversation_details.messages_id",
        "{$this->_prefix}message_conversation_details.is_reply",
        "{$this->_prefix}message.msg_title",
        "{$this->_prefix}message.message_entity",
        "{$this->_prefix}message.entity_id",
    );

    $this->db->select(implode(",", $select_fields));
    $this->db->from("{$this->_prefix}message_conversation_details");
    $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}message_conversation_details.user_id = {$this->_prefix}dashboard_login.id");
    $this->db->join("{$this->_prefix}message", "{$this->_prefix}message.id = {$this->_prefix}message_conversation_details.messages_id");
    $this->db->where("message_conversation_details.id", $msg_conversion_id);

    $query = $this->db->get();

    $result = $query->result_array();

    if (sizeof($result) > 0) {

        $return_data['user_email'] = $result[0]['email'];
        $return_data['posted_user_pic'] = $result[0]['image'];
        $return_data['entity'] = $result[0]['message_entity'];
        $return_data['msg_title'] = $result[0]['msg_title'];
        $return_data['entity_id'] = $result[0]['entity_id'];
        $return_data['posted_user_name'] = $result[0]['first_name'] . " " . $result[0]['last_name'];
        $return_data['posted_post_datetime'] = date($this->config->item('#EDIT_VIEW_DATE_TIME_FORMAT'), $result[0]['post_datetime']);
        $return_data['posted_datetime'] = date("D, M Y", $result[0]['post_datetime']);
        $return_data['message_conversation_details'] = $result[0]['message_conversation_details'];
        $return_data['messages_id'] = $result[0]['messages_id'];
        $return_data['posted_user_id'] = $result[0]['user_id'];
        $return_data['is_reply'] = $result[0]['is_reply'];
    }

    return $return_data;
}

function get_user_details()
{
    $user_id = $this->session->userdata('user_id');

    $query = $this->db->get_where("{$this->_prefix}dashboard_login", array(
        "id" => $user_id
    ));
    $result = $query->result_array();

    return $result[0];
}

function get_message_id_from_conversion_id($msg_conversion_id)
{
    $this->db->select("messages_id");
    $this->db->from("{$this->_prefix}message_conversation_details");
    $this->db->where("id", $msg_conversion_id);
    $this->db->where("is_deleted", 0);

    $query = $this->db->get();
    $result = $query->result_array();

    return $result[0]['messages_id'];
}

function check_all_people_selected($message_id)
{
    $this->db->select('id');
    $query = $this->db->get_where("{$this->_prefix}message_thread_notification", array(
        "messages_id" => $message_id,
        "user_group" => "*",
        "is_deleted" => 0
    ));

    if ($query->num_rows() > 0) {

        return true;
    } else {

        return false;
    }
}

function check_message_id_exists($message_id)
{
    $this->db->select("id");
    $query = $this->db->get_where("{$this->_prefix}message_thread_notification", array(
        "messages_id" => $message_id,
        "is_deleted" => 0
    ));

    if ($query->num_rows() > 0) {

        return true;
    } else {

        return false;
    }
}

function get_list_of_thread_people($message_id, $id = 0)
{
    $user = array();
    $userID = $this->session->userdata("user_id");
    $this->db->select("user_group");
    $query = $this->db->get_where("{$this->_prefix}message_thread_notification", array(
        "messages_id" => $message_id,
        "is_deleted" => 0
    ));
    if ($query->num_rows() > 0) {
        
        $result = $query->result_array();
        $user_group = $result[0]['user_group'];
        if ($user_group == "0") {
            
            $user = $user;
        } else 
            if ($user_group == "*") {
                
                $all_people = $this->all_people_list("");
                foreach ($all_people as $people) {
                    
                    $user[] = $people['id'];
                }
                
                $user =  $user;
            } else {
                $user_group = str_replace('#', ",", $user_group);
                $user =  explode(",", $user_group);
            }
    } else {
        
        $user = $user;
    }

    if($id != $userID){
        array_push(  $user, $id);
    }

    foreach($user as $k => $val) { 
        if($val == $userID) { 
            unset($user[$k]); 
        }
    } 
    return   $user;
}

function get_sent_to_people($msg_conversation_id)
{
     
    $this->db->select("user_id");
    $this->db->from("user_notification");
    $this->db->where("msg_conversation_id", $msg_conversation_id);
    $this->db->where("is_deleted", 0);
    $records = $this->db->get()->result_array();
    $results =array_column($records, 'user_id');
    return $results;
}

function get_user_details_from_msg_id($msg_id)
{
    $this->db->select("*");
    $this->db->from("{$this->_prefix}message");
    $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}dashboard_login.id = {$this->_prefix}message.user_id");
    $this->db->join("{$this->_prefix}contracts", "{$this->_prefix}contracts.id = {$this->_prefix}message.entity_id");
    $this->db->where("{$this->_prefix}message.id", $msg_id);

    $query = $this->db->get();
    return $query->result_array()[0];
}

function check_user_can_delete_msg($user_id, $msg_id, $conversion_user_id)
{
    $get_user_role = null;
    $this->db->select('role');
    $user_role_data = $this->db->get_where("{$this->_prefix}dashboard_login", array(
        "id" => $user_id
    ))->result_array();
    if (sizeof($user_role_data) > 0) {
        $get_user_role = $user_role_data[0]['role'];
    }

    if ($get_user_role == 'super_admin') {

        return true;
    } else 
    if ($conversion_user_id == $user_id) {

        return true;
    } else {

        return false;
    }
}

function get_msg_user_details($id)
{
    return $this->db->get_where("{$this->_prefix}dashboard_login", array(
        "id" => $id
    ))->result_array()[0];
}

function get_a_specific_message_details($messages_id)
{
    $account_id = get_account_id( 1 );
    if (strlen($messages_id) == '0') {

        $messages_id = 0;
    }

    $this->db->select("{$this->_prefix}message_conversation_details.*, {$this->_prefix}message_conversation_details.id as conversation_id, {$this->_prefix}message_conversation_details.post_datetime,
        {$this->_prefix}message.id, {$this->_prefix}message.user_id, {$this->_prefix}message.msg_title,{$this->_prefix}message.entity_id,
        {$this->_prefix}message.message_entity,{$this->_prefix}message.user_id as msg_user_id, {$this->_prefix}dashboard_login.first_name, 
        {$this->_prefix}dashboard_login.last_name,{$this->_prefix}dashboard_login.image, {$this->_prefix}message_conversation_details.user_id as comment_user_id");

    $this->db->from("(SELECT * FROM {$this->_prefix}message WHERE id='$messages_id' AND account_id='$account_id') as {$this->_prefix}message");
    $this->db->join("{$this->_prefix}message_conversation_details", "{$this->_prefix}message.id = {$this->_prefix}message_conversation_details.messages_id", "left");
    $this->db->join("{$this->_prefix}dashboard_login", "{$this->_prefix}message_conversation_details.user_id = {$this->_prefix}dashboard_login.id");
    $this->db->order_by("{$this->_prefix}message_conversation_details.post_datetime", "ASC");

    $query = $this->db->get();
    $result = $query->result_array();

    for ( $count = 0; $count < sizeof($result); $count++ ) {

      $result[$count]['file_details'] = $this->get_file_details( $result[$count]['conversation_id'] );
  }

  if (sizeof($result) > 1) {

    return $result;
}
if (sizeof($result) == '1' && strlen($result[0]['conversation_id']) == '0') {

    $user_details = $this->get_msg_user_details($result[0]['msg_user_id']);

    $result[0]['first_name'] = $user_details['first_name'];
    $result[0]['last_name'] = $user_details['last_name'];

    return $result;
} else {
    return $result;
}
}

public function get_file_details( $conversionId ) {

    return  $this->db->get_where("conversation_files", array(
        "msg_conversation_id" => $conversionId
    ))->result_array();
}

function delete_conversation($conversion_id)
{
    $this->db->where("id", $conversion_id);
    $this->db->delete("{$this->_prefix}message_conversation_details");

    $this->db->reset_query();
    $this->db->where("msg_conversation_id", $conversion_id);
    $this->db->delete("{$this->_prefix}user_notification");

    $this->db->reset_query();
    $this->db->where("messages_id", $conversion_id);
    $this->db->delete("{$this->_prefix}message_thread_notification");
    $this->Events_model->executeTableEntryEvent( "delete_entry", "{$this->_prefix}message_thread_notification", array(), $conversion_id );

    $rows = $this->db->get_where("{$this->_prefix}message_conversation_details", array(
        "id" => $conversion_id,
        "is_deleted" => 0
    ))->num_rows();
    if ($rows == '1') {

        return 0;
    } else {

        $this->load->model('Amazon_s3_model');
        $this->db->where("msg_conversation_id", $conversion_id);
        $result = $this->db->get("{$this->_prefix}conversation_files")->row();
        if( $result ){
            $this->Amazon_s3_model->deleteFileFromS3UsingUri( $result->file_location );
            
            $this->db->where("msg_conversation_id", $conversion_id);
            $this->db->delete("{$this->_prefix}conversation_files");

            $this->Events_model->executeTableEntryEvent( "delete_entry", "{$this->_prefix}message_conversation_details", array(), $conversion_id );

            return 1;
        }

    }
}

function message_posted_details($message_id)
{
    $prefix = $this->_prefix;

    $this->db->select("{$prefix}message.id, {$prefix}message.user_id, {$prefix}message.msg_title, {$prefix}message.message_entity, {$prefix}message.post_datetime, {$prefix}message.entity_id , 
        {$prefix}dashboard_login.image, {$prefix}dashboard_login.first_name,{$prefix}dashboard_login.last_name, {$prefix}dashboard_login.status");
    $this->db->from("{$prefix}message");
    $this->db->join("{$this->_prefix}dashboard_login", "{$prefix}dashboard_login.id = {$prefix}message.user_id", "left");
    $this->db->where("{$prefix}message.id", $message_id);

    $result = $this->db->get()->result_array();

    if (sizeof($result) > 0) {
        $result = $result[0];
    }

    $this->db->reset_query();
    $result['conversation_details'] = array();

    $this->db->select("*");
    $this->db->from("{$this->_prefix}message_conversation_details");
    $this->db->where("messages_id", $message_id);
    $this->db->order_by("id", "ASC");
    $this->db->limit(1, 0);

    $conversation_details = $this->db->get()->result_array();

    if (sizeof($result['conversation_details']) > 0) {

        $result['conversation_details'] = $conversation_details[0];
    }

    return $result;
}

function get_all_notification($entity, $entity_id)
{
    if (strlen($entity_id) == '0') {

        $entity_id = 0;
    }
    $user_id = $this->session->userdata('user_id');
    $this->db->select("id,user_id,msg_title,message_entity,post_datetime,entity_id");
    $this->db->from("{$this->_prefix}message");
    $this->db->where("message_entity", $entity);
    $this->db->where("entity_id", $entity_id);

    $result = $this->db->get()->result_array();

    $total_unread_count = 0;
    $total_read_count = 0;

    foreach ($result as $messages) {

        $message_id = $messages['id'];
        $this->db->reset_query();

        $this->db->select("DISTINCT({$this->_prefix}message_conversation_details.id)");
        $this->db->from("{$this->_prefix}message_conversation_details");
        $this->db->join("{$this->_prefix}user_notification", "{$this->_prefix}user_notification.msg_conversation_id = {$this->_prefix}message_conversation_details.id", "left");
        $this->db->where("{$this->_prefix}user_notification.is_notified", 0);
        $this->db->where("{$this->_prefix}message_conversation_details.messages_id", $message_id);
        $this->db->where("{$this->_prefix}user_notification.user_id", $user_id);

        $unread_count = $this->db->get()->num_rows();

        $total_unread_count = $unread_count + $total_unread_count;

        $this->db->reset_query();

        $this->db->select("DISTINCT({$this->_prefix}message_conversation_details.id)");
        $this->db->from("{$this->_prefix}message_conversation_details");
        $this->db->join("{$this->_prefix}user_notification", "{$this->_prefix}user_notification.msg_conversation_id = {$this->_prefix}message_conversation_details.id", "left");
        $this->db->where("{$this->_prefix}user_notification.is_notified", 1);
        $this->db->where("{$this->_prefix}message_conversation_details.messages_id", $message_id);
        $this->db->where("{$this->_prefix}user_notification.user_id", $user_id);

        $read_count = $this->db->get()->num_rows();

        $total_read_count = $read_count + $total_read_count;
    }

    return array(
        'read_count' => $total_read_count,
        'unread_count' => $total_unread_count
    );
}

function check_ajax_msg_load_in_url()
{
    $messages_id = $this->uri->segment('4');
    $get_params = $this->input->get('s');
    if (strlen($messages_id) > 0 && $get_params == 'l') {

        return true;
    } else {

        return false;
    }
}

function delete_msg_thread()
{
    $thread_id = $this->input->post('thread_id');
    
    $this->db->where("id", $thread_id);
    $this->db->delete("{$this->_prefix}message");

    $all_msg_comments = $this->db->get_where("{$this->_prefix}message_conversation_details", array("messages_id" => $thread_id))->result_array();

    foreach ($all_msg_comments as $comments) {
        $id = $comments['id'];
        $this->db->reset_query();
        $this->db->where("msg_conversation_id", $id);
        $this->db->delete("{$this->_prefix}conversation_files");

        $this->Events_model->executeTableEntryEvent( "delete_entry", "{$this->_prefix}conversation_files", array(), $id );

            // delete user notification 
        $this->db->reset_query();
        $this->db->where("msg_conversation_id", $id);
        $this->db->delete("{$this->_prefix}user_notification");

    }

        // delete main message
    $this->db->reset_query();
    $this->db->where("messages_id", $thread_id);
    $this->db->delete("{$this->_prefix}message_conversation_details");

    $this->Events_model->executeTableEntryEvent( "delete_entry", "{$this->_prefix}message_conversation_details", array(), $thread_id );

        // delete notification
    $this->db->reset_query();
    $this->db->where("messages_id", $thread_id);
    $this->db->delete("{$this->_prefix}message_thread_notification");

    $this->Events_model->executeTableEntryEvent( "delete_entry", "{$this->_prefix}message_thread_notification", array(), $thread_id );
}

    // function to check if a record have message or not
function haveMessage($tableName, $value)
{
    $return_data = array();
    $ids = array();
    $tableName = str_replace("`", "", $tableName);
    $return_data['status'] = 0;

    $this->db->select('id');
    $this->db->where('entity_id', $value);
    $this->db->where('message_entity', $tableName);
    $query = $this->db->get($this->_prefix . 'message');
    $rows = $query->num_rows();

    if ($rows > 0) {
        $return_data['status'] = 1;
        $total_records = $query->result_array();
        foreach ($total_records as $row) {
            $ids[] = $row['id'];
        }
        $return_data['record_ids'] = $ids;
    }

    return $return_data;
}

function deleteRelationalData($thread_id)
{
    $this->db->where("id", $thread_id);
    $this->db->delete("{$this->_prefix}message");

    $all_msg_comments = $this->db->get_where("{$this->_prefix}message_conversation_details", array(
        "messages_id" => $thread_id
    ))->result_array();
    foreach ($all_msg_comments as $comments) {

        $id = $comments['id'];
        $this->db->reset_query();
        $this->db->where("msg_conversation_id", $id);
        $this->db->delete("{$this->_prefix}conversation_files");

        $this->Events_model->executeTableEntryEvent( "delete_entry",  "{$this->_prefix}conversation_files", array(), $id );
    }

    $this->db->reset_query();

    $this->db->where("messages_id", $thread_id);
    $this->db->delete("{$this->_prefix}message_conversation_details");
    $this->db->delete("{$this->_prefix}message_thread_notification");

}

function softDeleteRelationalData($thread_id)
{
    $this->db->where('id', $thread_id);
    $this->db->update($this->_prefix . 'message', array(
        'is_deleted' => 1
    ));

    $all_msg_comments = $this->db->get_where("{$this->_prefix}message_conversation_details", array(
        "messages_id" => $thread_id
    ))->result_array();

    foreach ($all_msg_comments as $comments) {

        $id = $comments['id'];
        $this->db->where('msg_conversation_id', $id);
        $this->db->update($this->_prefix . 'conversation_files', array(
            'is_deleted' => 1
        ));
    }

    $this->db->where('messages_id', $thread_id);
    $this->db->update($this->_prefix . 'message_conversation_details', array(
        'is_deleted' => 1
    ));

    $this->db->where('messages_id', $thread_id);
    $this->db->update($this->_prefix . 'message_thread_notification', array(
        'is_deleted' => 1
    ));
}

public function get_message_details_by_id($message_id)
{
    $data = $this->db->get_where($this->_prefix . 'message', array(
        'id' => $message_id
    ))->result_array()[0];

    return $data;
}

public function upload_msg_files() {

    $entityId = $this->input->post("entity_id");
    $docs = $this->upload_file_from_item_docs ( "upload_file", "msg_files", $entityId );

    if  ( $docs['status'] ) {
        $status = 1;
        $file_path = $docs['filePath'];
        $template_name = $this->config->item("template_name");
        $file_views = $this->load->view($template_name."/core_".$template_name."/messages/sub_views/uploaded-file-views", array( "file_path" => $file_path ), TRUE);
        $msg = '';
    } else {
        $status = 0;
        $file_path = '';
        $msg = dashboard_lang('_FILE_UPLOADING_FAILED_PLEASE_TRY_AGAIN');
    }

    echo json_encode( array("status" => $status, "file_path" => $file_views, "msg" => $msg) );
}

public function upload_file_from_item_docs ( $fieldName, $save_type, $pId )
{
    $this->load->model('Amazon_s3_model');
    $fileUploadPath = $save_type.'/'.$pId.'_'.time().'/'.$_FILES[$fieldName]['name'];
    return $this->Amazon_s3_model->uploadFileToS3('', $fileUploadPath, $fieldName);
}

public function delete_files() {

    $file_path = $this->input->post("file_path");
    $this->load->model('Amazon_s3_model');
    echo json_encode( $this->Amazon_s3_model->deleteFileFromS3UsingUri($file_path) );
}

    public function send_email_to_external_users($notification_option, $data, $conversion_id) {


        $userData = BUserHelper::get_instance();

        $from_name = $userData->user->first_name.' '.$userData->user->last_name;
        $from_email = getLogisticsFromEmail();

        $header='';
        $message = $data['msg_comment'];
        $subject = $data['msg_title'];
        $to = explode(',', $data['external_people']);
        $file = $uploaded_files;

        $file = [];
            
        $this->db->select('*');
        $this->db->from('conversation_files');
        $this->db->where('msg_conversation_id',  $conversion_id);
        $this->db->where('is_deleted', 0);
        $convFiles = $this->db->get()->result_array();
        foreach($convFiles as $convFile){
                $file_details = pathinfo( $convFile['file_location'] );
                $extension = $file_details['extension'];
                $basename  = $file_details['basename'];
                $path      = 'tmp/'. trim($basename);
                file_put_contents($path , file_get_contents(trim($convFile['file_location'])));
                if( file_exists( $path )){
                    $file[] = $path;
                }

            }

        $this->load->model('portal/Mail_Model');
        $this->Mail_Model->send_external_mail($to, $from_name, $from_email, $header, $message, $subject, $file);

    }
}
