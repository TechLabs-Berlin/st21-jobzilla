<?php
/*
 * @author Atiqur Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Customers_Model extends CI_Model
{

    /*
     * Function :: Create - for create new category
     */
    public $_account_id;
    public $_current_table_name = 'customers';
    public $_status_customers = 'status_customers';
    public $_contact_customers_table = 'contact_customers';
    public $_location_customers_table = 'location_customers';
    public $_quotations = 'quotations';
    public $_sales_orders = 'sales_orders';
    public $_deliveries = 'deliveries';
    public $_sales_invoices = 'sales_invoices';
    public $_currencies = 'currencies';
    public $_countries = 'countries';
    public $_custom_roles_table = 'custom_roles';
    public $_event_log_table = 'event_log';
    public $_dashboard_login_table = 'dashboard_login';
    public $_message = 'message';
    public $_mediaCategories = "media_categories";
    public $_customerMediaFiles = "customer_media_files";
    public $_message_thread_notification = "message_thread_notification";
    public $_message_conversation_details = "message_conversation_details";

    public $customer_entry = 'customer_entry';
    public $customerKeyPrefix = '_CUSTOMERS';
    public $customerSettingsKeyPrefix = '#CORE_MODULE_LOGISTICS_CUSTOMER';
    public $contactCustomerKeyPrefix = '_CONTACT_CUSTOMERS';
    public $locationCustomerKeyPrefix = '_LOCATION_CUSTOMERS';
    public $salutationsTable;
    public $_categoryTable = 'category_customers';


    public function __construct()
    {
        parent::__construct();
        $this->_account_id = get_account_id(1);
        $this->load->model('portal/Events_model');
        $this->load->model('Upload_file_Model');
        $this->load->helper('customer/customer');
        $this->load->helper('logistics/logistics');

        $this->_statusDefaultSettingsID = $this->config->item("#CORE_MODULE_LOGISTICS_CUSTOMER_DEFAULT_STATUS");
        $this->_statusFieldName = "status_customers_id";
        $this->_statusComesAfterTable = "statuses_customers_comes_after";
        $this->_statusId = "status_customers_id";
        $this->_statusMainTbl = "status_customers";
        $this->_settingsKeyPrefix = "#CORE_MODULE_LOGISTICS";

        $userData = BUserHelper::get_instance();
        $this->_userName = $userData->user->first_name.' '.$userData->user->last_name;
        $this->salutationsTable = "salutations";
        $this->_location_types = 'location_types';
    }

    public function getCustomerId() {
        
        $systemNumber = $this->config->item("#CORE_CUSTOMER_ID_OFFSET");
        
        if (!empty($systemNumber)) {
            
            $initialNum = $systemNumber;
            $sql = "SELECT max(customer_id) FROM ".$this->_current_table_name." WHERE customer_id >= '" . $initialNum . "'";
            $files = $this->db->query($sql)->row_array();
            $dbFileNumber = $files['max(customer_id)'];
            if ($dbFileNumber > $initialNum) {
                $fileNumber = $dbFileNumber + 1;
            } else {
                $fileNumber = $systemNumber + 1;
            }
            
        } else {
            
            $fileNumber = rand(1000000, 9999999);
        }
        
        return $fileNumber;
        
    }

    public function createNew() {

        $customer_id = $this->getCustomerId();
        $userData = BUserHelper::get_instance();
        $table = $this->_current_table_name;
        
        $defaultStatus = $this->defalutTenantStatus();
        
        $insertArray = array(
            'customer_id' => $customer_id, 
            'status_customers_id' => $defaultStatus, 
            'currencies_id' => 0, 
            'contact_customers_id' => 0, 
            'location_customers_id' => 0,
            'customer' => '', 
            'alternative_code' => '', 
            'email' => '', 
            'phone' => '', 
            'website' => '', 
            'coc' => '', 
            'vat' => '', 
            'bank' => '', 
            'doctext' => '', 
            'vats_id' => $this->config->item('#CORE_MODULE_LOGISTICS_CUSTOMERS_DEFAULT_VAT'),
            'currencies_id' => $this->config->item('#CORE_MODULE_LOGISTICS_CUSTOMERS_DEFAULT_CURRENCY'),
            'languages_id' => $this->config->item( $this->_settingsKeyPrefix.'_CUSTOMERS_DEFAULT_LANGUAGE'),
            'location_types_id' => intval( $this->config->item('#CORE_CUSTOMERS_DEFAULT_LOCATION_TYPE')),
            'custom_roles_id' => intval( $this->config->item('#CORE_CUSTOMERS_MAIN_CONTACT_DEFAULT_ROLE')),
            'invoice_custom_roles_id' => intval( $this->config->item('#CORE_CUSTOMERS_SECOND_CONTACT_DEFAULT_ROLE')),
           
            'use_as_invoice_contact' => 1, 
            'created' => time(), 
            'created_by' => $userData->user->first_name.' '.$userData->user->last_name,
            'account_id' => $this->_account_id, 
            'is_deleted' => 0, 
            'is_saved' => 0 
        );        

        $this->db->insert($table,$insertArray);

        $lastId = $this->db->insert_id();

        //for history show
        $msg = "_CUSTOMER_CREATED_BY"." ".$userData->user->first_name.' '.$userData->user->last_name;
        $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $lastId);
        
        //for created by show..
        $this->Events_model->executeTableEntryEvent('add_entry', $table, $insertArray, $lastId);

        if ( isset($_GET["iframeView"]) && $_GET["iframeView"] == '1' ) {
            redirect("dbtables/".$table."/edit/".$lastId."?iframeView=1&fieldName=customer&searchString=".$_GET["searchString"]);
        }else {
            redirect("dbtables/".$table."/edit/".$lastId);
        }
        
    }

    public function defalutTenantStatus(){

        $defaultStatus = $this->config->item("#CORE_MODULE_LOGISTICS_CUSTOMER_DEFAULT_STATUS");
        if( $defaultStatus > 0 ){

            $singleStatus = $this->db->select('*')
            ->where('id', $defaultStatus)
            ->where('is_deleted', 0)
            ->get($this->_statusMainTbl)->row_array();

            if( $singleStatus && $singleStatus['account_id'] == $this->_account_id){

                return $singleStatus['id'];
            }else{

                $statuses = $this->db->select('*')
                ->where('account_id', $this->_account_id)
                ->where('is_deleted', 0)
                ->get($this->_statusMainTbl)->result_array();

                return @$statuses[0]['id'];
            }
        }else{
            
            $statuses = $this->db->select('*')
            ->where('account_id', $this->_account_id)
            ->where('is_deleted', 0)
            ->get($this->_statusMainTbl)->result_array();

            return @$statuses[0]['id'];
        }
    }


    public function getThisClassXMLdata() {

        $class_name = $this->_current_table_name;

        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR ."{$class_name}.xml";
        libxml_use_internal_errors(true);

        $xmlData = simplexml_load_file($xmlFile);
        $xmlObjectArrayProject = B_form_helper::get_xml_object_array($xmlData);
        $tablePermissionsOfProjectFields = $this->dashboard_model->get_table_permissions_field ($class_name);
        
        $data['xmlData'] = $xmlData;
        $data['xmlObjectArrayProject'] = $xmlObjectArrayProject;
        $data['tablePermissionsOfProjectFields'] = $tablePermissionsOfProjectFields;

        return $data;
    }


    public function getFirstDataId($customerId, $tableType) {

        if ( $tableType === "contact") $table = $this->_contact_customers_table;
        if ( $tableType === "location") $table = $this->_location_customers_table;

        $this->db->where('customers_id', $customerId);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        $this->db->order_by('id', 'ASC');
        $this->db->limit(1);
        $res = $this->db->get($table)->row_array();

        if (!empty($res)) {
            return $res['id'];
        } else {
            return 0;
        }

    }

    public function countTotalOfTableById($table, $cusotmerId) {

        $this->db->select("COUNT(id) as total");
        $this->db->where('customers_id',  $cusotmerId);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        $res = $this->db->get($table)->result_array();

        return (int)$res[0]['total'];
    }

    public function getTotalMessageCount($table, $entityId, $userId) {

        $this->db->select("COUNT(id) as total");
        $this->db->where('user_id', $userId);
        $this->db->where('entity_id', $entityId);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        $res = $this->db->get($table)->result_array();

        return (int)$res[0]['total'];
    }

    public function getTabTotals() {        

        $response = [];
        $customerId = $this->input->post('id');
        $entityId = $customerId;
        $userData = BUserHelper::get_instance();
        $userId = $userData->user->id;
        
        $response['totalContacts'] = $this->countTotalOfTableById($this->_contact_customers_table, $customerId);
        $response['totalLocations'] = $this->countTotalOfTableById($this->_location_customers_table, $customerId); 
        $response['totalQuotations'] = $this->countTotalOfTableById($this->_quotations, $customerId);
        $response['totalSalesOrders'] = $this->countTotalOfTableById($this->_sales_orders, $customerId);
        // $response['totalDeliveries'] = $this->countTotalOfTableById($this->_deliveries, $customerId);
        $response['totalDeliveries'] = 0;
        $response['totalSalesInvoices'] = $this->countTotalOfTableById($this->_sales_invoices, $customerId);
        
        $history =  $this->eventLog($this->_current_table_name, $customerId, $this->customer_entry);
        $response['totalHistory'] = count($history);
        $response['totalMessages'] = $this->getTotalMessageCount( $this->_message, $entityId, $userId);
        $response['totalDocsCount'] = $this->countTotalOfTableById( $this->_customerMediaFiles, $customerId);

        echo json_encode($response); 

    }


    public function getDataById($id){

        $class_name = $this->_current_table_name;

        
        $this->db->where('id', $id);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        return $this->db->get($class_name)->row_array();
    }

    public function getStatusData() {

        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        
        return $this->db->get($this->_status_customers)->result_array();
    }

    public function getStatusName($statusId) {

        $this->db->where('id', $statusId);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        $row = $this->db->get($this->_status_customers)->row_array();
        
        if (!empty($row)) {
            return $row['status'];
        } else {
            return '';
        }
    }

    public function updateData($updateArray, $id) {

        $oldData = $this->getOldCustomersDetails ( $id );
        $changedData = $this->allowOnlyChangedData( $oldData, $updateArray );

        $table = $this->_current_table_name;
        $this->db->where('id', $id);
        $this->db->where('account_id', $this->_account_id);
        $this->db->update($table, $updateArray);

        $this->saveExecuteEvent($this->customer_entry, $table, $changedData, $id, "TRUE");

    }

    public function getOldCustomersDetails ( $id ) {

        $result = $this->db->get_where($this->_current_table_name, [
            "id" => $id
        ])->row_array();

        return $result;
    }


    public function allowOnlyChangedData( $oldData, $newData ) {

        $returnChangedData = [];

        foreach ( $newData as $field => $value ) {

            if ( $oldData[$field] != $value ) {

                $returnChangedData[ $field ] = $value;
            }
        }

        return $returnChangedData;
    }

    public function updateCustomerStatus() {

        $id = $this->input->post("id");
        $statusId = $this->input->post("statusId");

        $updateData = [
            "status_customers_id" => $statusId
        ];

        $oldData = $this->getOldCustomersDetails ( $id );
        $changedData = $this->allowOnlyChangedData( $oldData, $updateData );

        $this->db->where("id", $id);
        $this->db->update($this->_current_table_name, $updateData);
        
        $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $updateData, $id);


    }

    public function renderGeneralContactDetailModal() {

        $id = $this->input->post('id');
        $data_edit = $this->getDataById($id);
        $data = $this->getThisClassXMLdata();
        $data['data_edit'] = $data_edit;
        
        $this->load->view("metrov5_4/customers/edit/general_contact_detail_modal", $data);
        
    }

    public function saveGeneralContactDetail() {

        $id = $this->input->post('id');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $website = $this->input->post('website');
        $mobile_number = $this->input->post('mobile_number');

        $updateArray = array(
            'email' => $email,
            'phone' => $phone,
            'website' => $website,
            'mobile_number' => $mobile_number
        );
        
        $this->updateData($updateArray, $id);

        $data_edit = $this->getDataById($id);
        $data = $this->getThisClassXMLdata();
        $data['data_edit'] = $data_edit;
        
        $this->load->view("metrov5_4/customers/edit/general-detail", $data);
        
    }

    public function renderDetailsTab() {
        
        $id = $this->input->post('id');
        $data_edit = $this->getDataById($id);
        $data = $this->getThisClassXMLdata();
        $data['data_edit'] = $data_edit;
        
        $this->load->view("metrov5_4/customers/tabs-data/details-tab/main", $data);


    }

    public function getAllContactsByCustomer( $customer_id ) {
        
        $this->db->select($this->_contact_customers_table.".*, " . $this->salutationsTable . ".salutation");
        $this->db->where($this->_contact_customers_table . '.customers_id', $customer_id);
        $this->db->where($this->_contact_customers_table . '.account_id', $this->_account_id);
        $this->db->where($this->_contact_customers_table . '.is_deleted', 0);
        $this->db->join($this->salutationsTable, $this->salutationsTable . ".id = " . $this->_contact_customers_table . ".salutations_id AND " . $this->salutationsTable . ".is_deleted = 0 AND " . $this->salutationsTable . ".account_id = " . $this->_account_id, "left");
        return $this->db->get($this->_contact_customers_table)->result_array();
    }

    public function getLocationsByContact( $contactId ) {
        
        $this->db->where('contact_customers_id', $contactId);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        return $this->db->get($this->_location_customers_table)->result_array();
    }

    public function getContactRoleById( $roleId ) {
        
        $this->db->where('id', $roleId);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        return $this->db->get($this->_custom_roles_table)->row_array();
    }


    public function renderContactTab(){

        $data['id'] = $this->input->post('id');
        $allContacts = $this->getAllContactsByCustomerId($data['id']);

        $allContactsTmp = $allContacts;
        foreach ($allContactsTmp as $key => $conatct) {
            $locations = $this->getLocationsByContact($conatct['id']);
            $allContactsTmp[$key]['locations'] = $locations;
            
            $roleNames = $this->getContactRoleByContactId($conatct['id']);
            $allContactsTmp[$key]['custom_role'] = $roleNames;
        }

        $data['contacts'] = $allContactsTmp;
        $this->load->view('metrov5_4/customers/tabs-data/contacts-tab/main', $data);

    }

    public function getContactRoleByContactId( $contactId ){

        $this->db->join('roles_contact_customers', 'roles_contact_customers.custom_roles_id = custom_roles.id', 'left');
        $this->db->where('roles_contact_customers.contact_customers_id', $contactId);
        $this->db->where('custom_roles.is_deleted', 0);
        $result = $this->db->get('custom_roles' )->result_array();

        $roleNames = implode( ',', array_column($result, "custom_role"));

        return $roleNames;
    }

    public function getAllContactsByCustomerId( $customer_id ) {
        
        $this->db->select($this->_contact_customers_table.".*, " . $this->salutationsTable . ".salutation");
        $this->db->where($this->_contact_customers_table . '.customers_id', $customer_id);
        $this->db->where($this->_contact_customers_table . '.account_id', $this->_account_id);
        $this->db->where($this->_contact_customers_table . '.is_deleted', 0);
        $this->db->join($this->salutationsTable, $this->salutationsTable . ".id = " . $this->_contact_customers_table . ".salutations_id AND " . $this->salutationsTable . ".is_deleted = 0 AND " . $this->salutationsTable . ".account_id = " . $this->_account_id, "left");
        return $this->db->get($this->_contact_customers_table)->result_array();
    }

    public function getContactDataById($id) {

        $contact_customers = $this->_contact_customers_table;

        $this->db->where('id', $id);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        return $this->db->get($contact_customers)->row_array();

    }


    public function getContactDataByCustomerId($customerId) {

        $this->db->where('customers_id', $customerId);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        return $this->db->get($this->_contact_customers_table)->result_array();

    }

    public function renderContactModal(){

        $data['id'] = $this->input->post('id');
        $data['customer_id'] = $this->input->post('customer_id');

        $data['contact'] = $this->getContactDataById($data['id']);

        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . "contact_customers.xml";
        libxml_use_internal_errors(true);
        $xmlData = simplexml_load_file($xmlFile);
        $xmlObjectArray = B_form_helper::get_xml_object_array($xmlData);
        $action = $this->input->get("action");
        $fieldPermissions = $this->dashboard_model->get_table_permissions_field ( $this->_contact_customers_table , $action);

        $data['xmlData'] = $xmlData;
        $data['xmlObjectArray'] = $xmlObjectArray;
        $data['fieldPermissions'] = $fieldPermissions;
        $data["defaultSalutationsId"] = $this->config->item("#CORE_CONTACT_CUSTOMERS_DEFAULT_SALUTATION");
        $data["locations"] = $this->getCustomerLocationsLists ( $data['customer_id'] );

        $this->load->view('metrov5_4/customers/tabs-data/contacts-tab/contact-modal', $data);

    }

    public function getCustomerLocationsLists ( $customerId ) {

        $result = $this->db->get_where( $this->_location_customers_table, [
            "customers_id" => $customerId,
            "is_deleted" => 0
        ])->result_array();

        return $result;
    }

    public function saveContactData(){

        $post = $this->input->post();
        $customerId = $post['customer_id'];

        $postData = [
            "customers_id" => $post['customer_id'],
            "firstname" => $post['firstname'],
            "lastname" => $post['lastname'],
            "phone" => $post['phone'],
            "mobile_number" => $post['mobile_number'],
            "email" => $post['email'],
            "custom_roles_id" => $post['custom_roles_id'],
            "contact_customers_notes" => $post['contact_customers_notes'],
            "is_deleted" => 0,
            "account_id" =>  $this->_account_id,
            "salutations_id" => $post['salutations_id'],
        ];

        if($post['id'] =='0' || empty($post['id']) ){

            $this->db->insert ($this->_contact_customers_table, $postData );
            $id = $this->db->insert_id();

            $userData = BUserHelper::get_instance();
            $msg = "_CUSTOMER_CONTACT"." ".$postData['firstname']." ".$postData['lastname']." _ADDED_BY ". $userData->user->first_name.' '.$userData->user->last_name;
            $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $customerId);
          
        }else{

            $oldData = $this->getContactDataById ( $post['id'] );
            $changedData = $this->allowOnlyChangedData( $oldData, $postData );

            $this->db->where('id',$post['id']); 
            $this->db->update($this->_contact_customers_table, $postData); 


            $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $changedData, $customerId, "FALSE");
        }
    }

    public function deleteContact() {

        $id = $this->input->post('id');
        $customerId = $this->input->post('customer_id');
        $customerData = $this->getDataById($customerId);
        $oldData = $this->getContactDataById ( $id );
        
        if ($customerData['contact_customers_id'] === $id){
            echo 0;
        } else {
            $updateData['is_deleted']=1;
            $this->db->where('id',$id); 
            $this->db->update($this->_contact_customers_table, $updateData);

            $userData = BUserHelper::get_instance();
            $msg = "_CUSTOMER_CONTACT"." ".$oldData['firstname']." ".$oldData['lastname']." _DELETED_BY ".$userData->user->first_name.' '.$userData->user->last_name;
            $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $customerId);

            echo 1; 
        }
    }

    public function getAllLocationsByCustomer( $customer_id ) {

        $locationTmp = [];

        $this->db->select($this->_location_customers_table.'.*'.','.$this->_location_types.'.location_type');
        $this->db->join( $this->_location_types, $this->_location_types.'.id = '.$this->_location_customers_table.'.location_types_id', 'left');
        $this->db->where($this->_location_customers_table.".customers_id", $customer_id);
        $this->db->where($this->_location_customers_table.'.account_id', $this->_account_id);
        $this->db->where($this->_location_customers_table.'.is_deleted', 0);
        $locations = $this->db->get( $this->_location_customers_table)->result_array();

        $locationTmp = $locations;
        foreach ($locationTmp as $key => $location) {
            $contactData = $this->getContactDataById($location['contact_customers_id']); 
            
            $locationTmp[$key]['countryname'] = $this->getCountryNameById($location['country']); 
            $locationTmp[$key]['firstname'] = !empty($contactData) ? $contactData['firstname']." ".$contactData['lastname'] : ''; 
            $locationTmp[$key]['phone'] = !empty($contactData) ? $contactData['phone'] : ''; 
            $locationTmp[$key]['email'] = !empty($contactData) ? $contactData['email'] : ''; 

        }

        return $locationTmp;
    }

    public function getLocationDataById($id) {

        $location_customers = $this->_location_customers_table;

        $this->db->where('id', $id);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        return $this->db->get($location_customers)->row_array();

    }
    
    
    public function renderLocationTab(){

        $data['id'] = $this->input->post('id');
        $data['locations'] = $this->getAllLocationsByCustomer($data['id']);
        
        $this->load->view('metrov5_4/customers/tabs-data/locations-tab/main', $data);

    }

    public function renderLocationModal() {

        $data['id'] = $this->input->post('id');
        $data['customer_id'] = $this->input->post('customer_id');

        $data['locations'] = $this->getLocationDataById($data['id']);

        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . "location_customers.xml";
        libxml_use_internal_errors(true);
        $xmlData = simplexml_load_file($xmlFile);
        $xmlObjectArray = B_form_helper::get_xml_object_array($xmlData);
        $action = $this->input->get("action");
        $fieldPermissions = $this->dashboard_model->get_table_permissions_field ("location_customers", $action);

        $data['xmlData'] = $xmlData;
        $data['xmlObjectArray'] = $xmlObjectArray;
        $data['fieldPermissions'] = $fieldPermissions;

        $data['contacts'] = $this->getAllContactsByCustomer($data['customer_id']);


        $this->load->view('metrov5_4/customers/tabs-data/locations-tab/location_modal', $data);
    }

    public function saveLocationData(){

        $post = $this->input->post();
        $customerId = $post['customer_id'];

        $postData = [
            "contact_customers_id" => $post['contact_customers_id'],
            "location" => $post['location'],
            "addres1" => $post['addres1'],
            "addres2" => $post['addres2'],
            "zipcode" => $post['zipcode'],
            "city" => $post['city'],
            "country" => $post['country'],
            "is_deleted" => 0,
            "account_id" =>  $this->_account_id,
            "customers_id" => $customerId
            
        ];

        if($post['id'] =='0' || empty($post['id']) ){
            
            $this->db->insert ($this->_location_customers_table, $postData );
            $id = $this->db->insert_id();

            $userData = BUserHelper::get_instance();
            $msg = "_CONTACT_CUSTOMER_LOCATION"." ".$postData['location']." _ADDED_BY ". $userData->user->first_name.' '.$userData->user->last_name;
            $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $customerId);
            
        }else{

            $oldData = $this->getLocationDataById ( $post['id'] );
            $changedData = $this->allowOnlyChangedData( $oldData, $postData );
            
            $this->db->where('id',$post['id']); 
            $this->db->update($this->_location_customers_table, $postData); 
            
            $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $changedData, $customerId);
        }
    }

    public function deleteLocation(){

        $id = $this->input->post('id');
        $customerId = $this->input->post('customer_id');
        $customerData = $this->getDataById($customerId);
        $oldData = $this->getLocationDataById ( $id );
        
        if ($customerData['location_customers_id'] === $id){
            echo 0;
        } else {
            $updateData['is_deleted']=1;
            $this->db->where('id',$id); 
            $this->db->update($this->_location_customers_table, $updateData); 

            $userData = BUserHelper::get_instance();
            $msg = "_CONTACT_CUSTOMER_LOCATION"." ".$oldData['location']." _DELETED_BY ".$userData->user->first_name.' '.$userData->user->last_name;
            $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $customerId);

            echo 1;
        }


    }
    
    public function renderContactDropDown(){

        $customer_id = $this->input->post('customer_id');
        $location_id = $this->input->post('location_id');


        $contacts = $this->getContactDataByCustomerId($customer_id);
        $customer_data = $this->getDataById($customer_id);
        $location_data = $this->getLocationDataById($location_id);

        $response['contacts'] = $contacts;
        $lastItem =  end($contacts);
        $response['last_contacts'] =  $lastItem["id"];
        $response['customer_contact_id'] = $customer_data['contact_customers_id'];
        $response['customer_contact_id_modal'] = $location_data['contact_customers_id'];

        echo json_encode($response);

    }

    public function renderLocationDropDown(){

        $contact_id = $this->input->post('contact_id');
        $customer_id = $this->input->post('customer_id');
        
        $this->db->where("customers_id", $customer_id);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        $locations = $this->db->get($this->_location_customers_table)->result_array();

        $locations_data = $locations;
        $customer_data = $this->getDataById($customer_id);
        $lastItem =  end($locations);
        $response['last_locations'] =  $lastItem["id"];

        $response['locations'] = $locations_data;
        $response['location_customers_id'] = $customer_data['location_customers_id'];

        echo json_encode($response);

    }



    public function getContactRow($contactId) {

        $contactData = $this->getContactDataById($contactId);
        if(!empty($contactData)) {
            $role = $this->getContactRoleById($contactData['custom_roles_id']);
            $contactData['custom_role'] = $role['custom_role'];
        }
        return $contactData;
    }
    
    public function getContactDataToShow() {
        
        $contactId = $this->input->post('contact_id');

        $data['contactData'] = $this->getContactRow($contactId);

        $this->load->view("metrov5_4/customers/edit/contact_detail", $data);
    }




    public function getLocationRow($locationId) {

        $locationData = $this->getLocationDataById($locationId);
        if(!empty($locationData)) {
            $country = $this->getCountryNameById($locationData['country']);
            $locationData['country_name'] = $country;
        }
        return $locationData;
    }

    public function getLocationDataToShow() {

        $locationId = $this->input->post('location_id');
        
        $data['locationData'] = $this->getLocationRow($locationId);

        $this->load->view("metrov5_4/customers/edit/location_detail", $data);
    }



    public function saveExecuteEvent($entry_type, $table_name, $data, $id, $gdModal = 'TRUE'){

        $notInArray = array(
            'update', 
            'updated_by', 
            'account_id'
        );

        if(is_array($data)){

            foreach( $data as $key => $value ){

                if ( !in_array($key, $notInArray) ) {
                    $msg = array();
    
                    $changeTo = "_CHANGED_TO";
                    $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".$value;
    
                    if($key == 'currencies_id'){
    
                        $findValue = getTableColumnInfo('currencies',  'currency', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".$findValue;
                    
                    }else if($key == 'contact_customers_id'){
    
                        $findValue = getTableColumnInfo('contact_customers',  'firstname', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".$findValue;
    
                    }else if($key == 'location_customers_id'){
    
                        $findValue = getTableColumnInfo('location_customers',  'location', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".$findValue;
    
                    }else if($key == 'status_customers_id'){
    
                        $findValue = getTableColumnInfo('status_customers',  'status', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".dashboard_lang($findValue);

                    }else if($key == 'custom_roles_id'){
    
                        $findValue = getTableColumnInfo('custom_roles',  'custom_role', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->contactCustomerKeyPrefix)." ".$changeTo." ".$findValue." _IN_CONTACT_TAB";
                    
                    }
                    else if($key == 'vats_id'){
    
                        $findValue = getTableColumnInfo('vat',  'vat', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".$findValue;
                    
                    }
                    else if($key == 'location_types_id'){
    
                        $findValue = getTableColumnInfo('location_types',  'location_type', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".$findValue;
                    
                    }
                    else if($key == 'custom_roles_id'){
    
                        $findValue = getTableColumnInfo('custom_roles',  'custom_role', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".$findValue;
                    
                    }
                    else if($key == 'invoice_custom_roles_id'){
    
                        $findValue = getTableColumnInfo('custom_roles',  'custom_role', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->customerKeyPrefix)." ".$changeTo." ".$findValue;
                    
                    }else if($key == 'firstname'){
                        
                        $msg = formatKeyCustomer($key, $this->contactCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_CONTACT_TAB";
                    
                    }else if($key == 'lastname'){

                        $msg = formatKeyCustomer($key, $this->contactCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_CONTACT_TAB";
                    
                    }else if($key == 'contact_customers_notes'){

                        $msg = formatKeyCustomer($key, $this->contactCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_CONTACT_TAB";
                    
                    }else if($key == 'email' && $gdModal == 'TRUE'){

                        $msg = formatKeyCustomer($key, $this->contactCustomerKeyPrefix)." ".$changeTo." ".$value;
                    
                    }else if($key == 'phone'  && $gdModal == 'TRUE'){

                        $msg = formatKeyCustomer($key, $this->contactCustomerKeyPrefix)." ".$changeTo." ".$value;
                    
                    }else if($key == 'email' && $gdModal == 'FALSE'){

                        $msg = formatKeyCustomer($key, $this->contactCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_CONTACT_TAB";
                    
                    }else if($key == 'phone'  && $gdModal == 'FALSE'){

                        $msg = formatKeyCustomer($key, $this->contactCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_CONTACT_TAB";
                    
                    }else if($key == 'contact_customers_id'){

                        $findValue = getTableColumnInfo('contact_customers',  'firstname', 'id', $value);
                        $msg = formatKeyCustomer($key, $this->locationCustomerKeyPrefix)." ".$changeTo." ".$findValue." _IN_LOCATION_TAB";
                    
                    }else if($key == 'location'){

                        $msg = formatKeyCustomer($key, $this->locationCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_LOCATION_TAB";
                    
                    }else if($key == 'addres1'){

                        $msg = formatKeyCustomer($key, $this->locationCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_LOCATION_TAB";
                    
                    }else if($key == 'addres2'){

                        $msg = formatKeyCustomer($key, $this->locationCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_LOCATION_TAB";
                    
                    }else if($key == 'zipcode'){

                        $msg = formatKeyCustomer($key, $this->locationCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_LOCATION_TAB";
                    
                    }else if($key == 'city'){

                        $msg = formatKeyCustomer($key, $this->locationCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_LOCATION_TAB";
                    
                    }else if($key == 'country'){

                        $msg = formatKeyCustomer($key, $this->locationCustomerKeyPrefix)." ".$changeTo." ".$value." _IN_LOCATION_TAB";
                    }
                    

                    $userData = BUserHelper::get_instance();
                    $msg .=  " _BY ".$userData->user->first_name.' '.$userData->user->last_name;

                    $this->Events_model->executeTableEntryEvent( $entry_type, $table_name, $msg, $id );
                }
            }
        }else{

            
            $this->Events_model->executeTableEntryEvent( $entry_type, $table_name, $data, $id );
        }

    }



    public function eventLog($tableName, $rowId, $eventId) {

        $this->db->where('table_name', $tableName );
        $this->db->where('row_id', $rowId);
        $this->db->where('event_id', $eventId);
        $this->db->where('account_id', $this->_account_id);
        $this->db->where('is_deleted', 0);
        $this->db->order_by('time', 'DESC');

        $result = $this->db->get($this->_event_log_table)->result_array();

        return $result;
    }

    public function renderHistoryTab() {

        $rowId = $this->input->post('id');

        $data['history'] = $this->eventLog($this->_current_table_name, $rowId, $this->customer_entry);

        $this->load->view("metrov5_4/customers/tabs-data/historyTab/main", $data);

        
    }

    public function renderHistoryModal() {

        $rowId = $this->input->post('id');

        $data['history'] = $this->eventLog($this->_current_table_name, $rowId, $this->customer_entry);

        $this->load->view("metrov5_4/customers/tabs-data/historyTab/historyModal", $data);

        
    }

    

    public function getEventLogData( $table, $rowId, $action1 = 'add', $action2 = 'edit' ){
    
        $this->db->select(["user_id", "time"]);
        $this->db->where('row_id', $rowId );
        $this->db->where('table_name', $table );
        $this->db->where('action', $action1 );
        $this->db->order_by('id', 'desc' );
        $res1 = $this->db->get($this->_event_log_table)->row_array();
        
        $this->db->select(["user_id", "time"]);
        $this->db->where('row_id', $rowId );
        $this->db->where('table_name', $table );
        $this->db->where('action', $action2 );
        $this->db->order_by('id', 'desc' );
        $this->db->limit(2);
        $res2 = $this->db->get($this->_event_log_table)->result_array();

        $edit = [];
        if(count($res2) > 1)
            $edit = @$res2[0];
    
    
        return array( "add" => $res1, "edit" => $edit );
    
    }

    public function getDashboardUserInfo($id) {

        $CI = & get_instance();
    
        $CI->db->select(['first_name', 'last_name']);
        $CI->db->where('id', $id);
        $Q = $CI->db->get($this->_dashboard_login_table);
    
        $res = $Q->row_array();
    
        return $res;
    
    
    }


    
    public function getContryNameByIso(){
    
        $iso = $this->input->post('countryIso');
        
        $this->db->select('id');
        $this->db->where('iso',$iso); 
        $this->db->where('is_deleted', 0); 
        $q = $this->db->get($this->_countries);
        $res =  $q->row_array();

        echo json_encode($res);
    
    }

    public function getCountryNameById($id){
        
        $this->db->select('name');
        $this->db->where('id', $id); 
        $this->db->where('is_deleted', 0); 
        $q = $this->db->get($this->_countries);
        $res =  $q->row_array();
       
        if (!empty($res)){
            return $res['name'];
        } else {
            return '';
        }
    
    }


    public function getDataFromTable($table, $field, $fieldData) {

        $this->db->where("$field", $fieldData);
        $this->db->where("account_id", $this->_account_id);
        $this->db->where("is_deleted", 0);
        $res = $this->db->get($table)->result_array();

        return $res;

    }

    public function renderQuotationsTab () {

        $data['id'] = $this->input->post('id');
        $allData = $this->getDataFromTable($this->_quotations, 'customers_id', $data['id']);

        $allDataTmp = $allData;
        foreach ($allDataTmp as $key => $data) {
            $currency = $this->getDataFromTable($this->_currencies, 'id', $data['currencies_id']);
            if(!empty($currency)) {$iso2 = $currency[0]['ISO2']; } else {$iso2=''; }

            if($data['quotation_date']) {
                $allDataTmp[$key]['quotation_date'] = date('d M Y', $data['quotation_date']);
            }
            if($data['vat_total']) {
                $allDataTmp[$key]['vat_total'] = renderMoneyFormattedAmount( $iso2, $data['vat_total']);
            }
            if($data['subtotal']) {
                $allDataTmp[$key]['subtotal'] = renderMoneyFormattedAmount( $iso2, $data['subtotal']);
            }
            if($data['total']) {
                $allDataTmp[$key]['total'] = renderMoneyFormattedAmount( $iso2, $data['total']);
            }
        }

        $data['quotations'] = $allDataTmp;
        $data['baseId'] = $this->input->post('id');
        $this->load->view('metrov5_4/customers/tabs-data/quotationsTab/main', $data);

    }

    public function deleteQuotations() {

        $id = $this->input->post('id');
        $customerId = $this->input->post('customer_id');
        $this->db->where('id', $id);
        $this->db->update($this->_quotations, array('is_deleted' => 1) );

        $userData = BUserHelper::get_instance();
        $msg = "_CUSTOMER_QUOTATION"." ".$oldData['firstname']." ".$oldData['lastname']." _DELETED_BY ".$userData->user->first_name.' '.$userData->user->last_name;
        $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $customerId);

    }


    public function renderSalesOrdersTab () {

        $data['id'] = $this->input->post('id');
        $allData = $this->getDataFromTable($this->_sales_orders, 'customers_id', $data['id']);

        $allDataTmp = $allData;
        foreach ($allDataTmp as $key => $data) {
            $currency = $this->getDataFromTable($this->_currencies, 'id', $data['currencies_id']);
            if(!empty($currency)) {$iso2 = $currency[0]['ISO2']; } else {$iso2=''; }

            if($data['sales_order_date']) {
                $allDataTmp[$key]['sales_order_date'] = date('d M Y', $data['sales_order_date']);
            }
            if($data['vat_total']) {
                $allDataTmp[$key]['vat_total'] = renderMoneyFormattedAmount( $iso2, $data['vat_total']);
            }
            if($data['subtotal']) {
                $allDataTmp[$key]['subtotal'] = renderMoneyFormattedAmount( $iso2, $data['subtotal']);
            }
            if($data['total']) {
                $allDataTmp[$key]['total'] = renderMoneyFormattedAmount( $iso2, $data['total']);
            }
        }

        $data['sales_orders'] = $allDataTmp;
        $data['baseId'] = $this->input->post('id');
        $this->load->view('metrov5_4/customers/tabs-data/salesOrdersTab/main', $data);

    }

    public function deleteSalesOrders() {

        $id = $this->input->post('id');
        $customerId = $this->input->post('customer_id');
        $this->db->where('id', $id);
        $this->db->update($this->_sales_orders, array('is_deleted' => 1) );

        $userData = BUserHelper::get_instance();
        $msg = "_CUSTOMER_SALES_ORDER"." ".$oldData['firstname']." ".$oldData['lastname']." _DELETED_BY ".$userData->user->first_name.' '.$userData->user->last_name;
        $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $customerId);

    }

    
    public function renderSalesInvoicesTab () {

        $data['id'] = $this->input->post('id');
        $allData = $this->getDataFromTable($this->_sales_invoices, 'customers_id', $data['id']);

        $allDataTmp = $allData;
        foreach ($allDataTmp as $key => $data) {
            $currency = $this->getDataFromTable($this->_currencies, 'id', $data['currencies_id']);
            if(!empty($currency)) {$iso2 = $currency[0]['ISO2']; } else {$iso2=''; }

            if($data['sales_invoice_date']) {
                $allDataTmp[$key]['sales_invoice_date'] = date('d M Y', $data['sales_invoice_date']);
            }
            if($data['vat_total']) {
                $allDataTmp[$key]['vat_total'] = renderMoneyFormattedAmount( $iso2, $data['vat_total']);
            }
            if($data['subtotal']) {
                $allDataTmp[$key]['subtotal'] = renderMoneyFormattedAmount( $iso2, $data['subtotal']);
            }
            if($data['total']) {
                $allDataTmp[$key]['total'] = renderMoneyFormattedAmount( $iso2, $data['total']);
            }
        }

        $data['sales_invoices'] = $allDataTmp;
        $data['baseId'] = $this->input->post('id');
        $this->load->view('metrov5_4/customers/tabs-data/salesInvoicesTab/main', $data);

    }

    public function deleteSalesInvoices() {

        $id = $this->input->post('id');
        $customerId = $this->input->post('customer_id');
        $this->db->where('id', $id);
        $this->db->update($this->_sales_invoices, array('is_deleted' => 1) );

        $userData = BUserHelper::get_instance();
        $msg = "_CUSTOMER_SALES_INVOICE"." ".$oldData['firstname']." ".$oldData['lastname']." _DELETED_BY ".$userData->user->first_name.' '.$userData->user->last_name;
        $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $customerId);

    }

    
    public function getAllPossibleStatus($id){

        if(empty(trim($id))){
            $status_id = $this->defalutTenantStatus();
        }elseif($id>0){

            $this->db->select("*");
            $this->db->from($this->_current_table_name);
            $this->db->where('id', $id);
         
            $resStatus = $this->db->get()->row_array();
            $status_id =  $resStatus[$this->_statusFieldName];
            
            if(!$status_id){
                $status_id = $this->defalutTenantStatus();
            }
        }

        $singleStatus = $this->db->select('*')
        ->where('id', $status_id)
        ->where('is_deleted', 0)
        ->get($this->_statusMainTbl)->row_array();

        if( $singleStatus && $singleStatus['account_id'] == $this->_account_id){

            $results = array();

            $this->db->select("*");
            $this->db->from($this->_statusComesAfterTable);
            $this->db->where($this->_statusId, $status_id);
            $this->db->where("account_id", $this->_account_id);
                
            $array = $this->db->get()->result_array();

            $results = array_column($array, "next_possible_statuses");
            $selectedStatus = array($status_id);
            $possibleStatus = array_merge($results,  $selectedStatus);

            $this->db->select("*");
            $this->db->from($this->_statusMainTbl);
            $this->db->where("account_id", $this->_account_id);

            if (in_array(-1, $results)) {
                $this->db->where('is_deleted', 0);
            }else{
                $this->db->where('is_deleted', 0);
                $this->db->where_in('id', $possibleStatus);
            }

            $this->db->order_by('sort', 'ASC');
                
            $records = $this->db->get()->result_array();
        }else{

            $records = $this->db->select('*')
            ->where('account_id', $this->_account_id)
            ->where('is_deleted', 0)
            ->get($this->_statusMainTbl)->result_array();
        }
        return $records;

   }

   public function getStatusListsByName( $tableName, $fieldName ) {

    $result = $this->db->get_where( $tableName, [
        "is_deleted" => 0,
        "account_id" => $this->_account_id 
    ])->result_array();


    $statusLists = [];

    foreach ( $result as $eachStatus ) {

        $statusLists[ $eachStatus[$fieldName] ] = $eachStatus;
    }

    return $statusLists;
}


    public function renderMediaImageModal() {

        $data['id'] = $this->input->post('id');
        $data['media_id'] = $this->input->post('media_id');
        $data['categories'] = $this->getAllFromTable($this->_mediaCategories);
        $data['media'] = $this->getSingleUsingWhere($this->_customerMediaFiles, 'id', $data['media_id']);
        $data['customerKeyPrefix'] = $this->customerKeyPrefix;
        
        $data['media_modal_data'] = $this->load->view('metrov5_4/customers/tabs-data/documents-tab/upload-media-image-modal', $data, true);
        
        
        echo json_encode($data);
    }

    public function getAllFromTable($tableName) {
        
        $this->db->select('*');
        $this->db->from($tableName);
        $this->db->where('is_deleted', 0);
        $this->db->where('account_id', get_account_id());
        $query = $this->db->get()->result_array();
        
        return $query;
    }

    public function getSingleUsingWhere($tableName, $colName, $colValue) {
        
        $this->db->select('*');
        $this->db->from($tableName);
        $this->db->where($colName, $colValue);
        $this->db->where('is_deleted', 0);
        
        return $this->db->get()->row_array();
    }

    public function uploadMultipleMediaImage() {
        
        $customers_id = $this->input->post("item_id");
        $category_id = $this->input->post("category_id");
        $response = $this->Upload_file_Model->doUpload('file', 'media-files', $customers_id);
        
        if ($response["status"]) {
            
            $id = $this->onlyInsertFiles($customers_id, $category_id, trim($response["filePath"]));
            $file_path = $response['filePath'];
            $status = 1;
            $template_name = $this->config->item("template_name");
            $file_views = $this->load->view($template_name . "/customers/tabs-data/media-tab/uploaded-file-views", array("file_path" => $file_path, "id" => $id, "customerSettingsKeyPrefix" => $this->customerSettingsKeyPrefix), TRUE);
            $msg = '';

            $msg = "_DOCUMENT_UPLOADED_BY"." ".$this->_userName;
            $this->Events_model->executeTableEntryEvent( $this->customer_entry, $this->_current_table_name, $msg, $customers_id );
            
        } else {
            $status = 0;
            $file_views = '';
            $msg = dashboard_lang('_FILE_UPLOADING_FAILED_PLEASE_TRY_AGAIN');
        }
        
        echo json_encode(array("status" => $status, "file_path" => $file_views, "msg" => $msg));
    }

    public function onlyInsertFiles($customers_id, $category_id, $filePath) {
        
        $insertData = ["customers_id" => $customers_id, "media_categories_id" => $category_id, "item_image" => $filePath, "use_as_profile_image" => 0, "uploaded_by" => get_user_id(),  "upload_time" => time(), "is_deleted" => 0, "account_id" => get_account_id(), ];
        $this->db->insert($this->_customerMediaFiles, $insertData);
        $id = $this->db->insert_id();
        
        return $id;
    }

    public function updateImageText(){
          
        $ids = $this->input->post('id');
        $selectedIds = [];
        $updateData['description'] =  $this->input->post('description');
        $updateData['media_categories_id'] =  $this->input->post('media_categories_id');
        $selectedIds = explode("-", $ids);

        foreach($selectedIds as $id){
            $update = $this->db->where('id', $id);
            $this->db->update('customer_media_files', $updateData);
        }

        echo json_encode($selectedIds);
        
    }

    public function renderMediaTab() {
        
        $data['id'] = $this->input->post('id');
        $data['images'] = $this->getMediaImages($data['id']);
        //$data['documents'] = $this->getProgressDocuments($data['id']);
        $data['customerKeyPrefix'] = $this->customerKeyPrefix;
        $data['customerSettingsKeyPrefix'] = $this->customerSettingsKeyPrefix;
        $data['media_tab_data'] = $this->load->view('metrov5_4/customers/tabs-data/documents-tab/main', $data, true);
        
        
        echo json_encode($data);
    }

    public function getMediaImages($item_id) {
        
        $fieldsLists = [$this->_mediaCategories . ".name as category_name", $this->_customerMediaFiles . ".*", $this->_mediaCategories . ".id as category_ids", $this->_dashboard_login_table . ".first_name", $this->_dashboard_login_table . ".last_name"];
        $this->db->select(implode(",", $fieldsLists));
        $this->db->from($this->_customerMediaFiles);
        $this->db->join($this->_mediaCategories, $this->_customerMediaFiles . ".media_categories_id = " . $this->_mediaCategories . ".id", "left");
        $this->db->join($this->_dashboard_login_table, $this->_customerMediaFiles . ".uploaded_by = " . $this->_dashboard_login_table . ".id", "left");
        $this->db->where($this->_customerMediaFiles . ".customers_id", $item_id);
        $this->db->where($this->_customerMediaFiles . ".is_deleted", 0);
        $this->db->order_by($this->_customerMediaFiles . ".upload_time", "DESC");
        $result = $this->db->get()->result_array();
        
        $responseData = [];
        $responseData[0][] = array();
        foreach ($result as $allItems) {
            
            if( empty( $allItems["category_ids"] )){
                
                if(empty( $responseData[0][0] )){

                    $responseData[0][0] = $allItems;
                }else{

                    $responseData[0][] = $allItems;
                }
                
            }else{

                $responseData[$allItems["category_ids"]][] = $allItems;
            }
            
        }
        if( empty( $responseData[0][0])){

            unset( $responseData[0]);
        }
        
        return $responseData;
    }

    public function deleteMediaImage(){
        
        $status = true;
        $id =  $this->input->post('id');
        $trucks_id =  $this->input->post('trucks_id');
        // get media data
        $mediaData = $this->getDataFromId($this->_customerMediaFiles, $id, "id", true);
        $s3FilePath = @$mediaData->item_image;
        $fileInfo = parse_url(trim($s3FilePath));
        
        if($status){
            
            $data['is_deleted']='1';
            $this->updateInfo($this->_customerMediaFiles,'id', $id, $data );
            
            $msg = "_DOCUMENT_DELETED_BY"." ".$this->_userName;
            $this->Events_model->executeTableEntryEvent( $this->customer_entry, $this->_current_table_name, $msg, $trucks_id );
        }
    }

    function getDataFromId($tableName, $recordId, $fieldName = "id", $singleRow = false, $returnArr = false, $sortField = "", $sortOrder = "asc", $isDeleted=true)
    {
        $CI = & get_instance();
        $CI->db->select('*');
        $CI->db->where($fieldName, $recordId);
        if($isDeleted){
            $CI->db->where('is_deleted', 0);
        }
        if (! empty($sortField)) {
            $CI->db->order_by($sortField, $sortOrder);
        }
        if ($returnArr) {
            return $CI->db->get($tableName)->result_array();
        } elseif ($singleRow) {
            return $CI->db->get($tableName)->row();
        } else {
            return $CI->db->get($tableName)->result();
        }
    }

    public function updateInfo($table, $colName, $colValue, $data) {
        
        $this->db->where($colName, $colValue);
        $this->db->update($table, $data);
    }


    public function rendereditMediaImageModal() {
        
        $data['id'] = $this->input->post('id');
        $data['media_id'] = $this->input->post('media_id');
        $data['categories'] = $this->getAllFromTable($this->_mediaCategories);
        $data['media'] = $this->getSingleUsingWhere($this->_customerMediaFiles, 'id', $data['media_id']);
      // $data['media_modal_data'] = $this->load->view('metrov5_4/trucks/tabs-data/documents-tab/media-image-modal', $data, true);
        $data['media_modal_data'] = $this->load->view('metrov5_4/customers/tabs-data/documents-tab/media-image-edit-modal', $data, true);
        
        echo json_encode($data);
    }


    public function saveMediaImageDescription(){
        $item_id = $this->input->post('id');
        $id = $this->input->post('media_id');
        $updateData['description'] =  $this->input->post('description');
        $updateData['media_categories_id'] =  $this->input->post('media_categories_id');
        
        $update = $this->db->where('id', $id);
        $this->db->update($this->_customerMediaFiles, $updateData);
        if($update){
            $json['status'] =1;
        }else{
            $json['status'] =0;
        }
        echo json_encode($json);
    }

    public function renderInternalNotes( $tableName, $selectWhere, $tableId ){

        $this->db->select('internal_notes');
        $this->db->where( $selectWhere,  $tableId);
        $result = $this->db->get( $tableName )->result_array();

        $response['notes'] = implode('&#013',  array_column($result, 'internal_notes' ));
        $response['subNotes'] = implode('_',  array_column($result, 'internal_notes' ));

        return $response;
    }


    public function copy( $id ){

        $userData = BUserHelper::get_instance();
        $customerData = $this->getTableDetails( $this->_current_table_name, $id );

        if( $customerData ){

            unset($customerData['id']);
            $customerData['customer_id'] = $this->getCustomerId();

            $this->db->insert( $this->_current_table_name, $customerData);

            $insertId = $this->db->insert_id();

            $this->insertNewDataInRefTbl( $this->_contact_customers_table, $id, $insertId, $customerData);
            $this->insertNewDataInRefTbl( $this->_location_customers_table, $id, $insertId, $customerData);
            $this->insertNewDataInRefTbl( $this->_quotations, $id, $insertId, $customerData);
            $this->insertNewDataInRefTbl( $this->_sales_orders, $id, $insertId, $customerData);
            $this->insertNewDataInRefTbl( $this->_sales_invoices, $id, $insertId, $customerData);
            $this->insertNewDataInRefTbl( $this->_customerMediaFiles, $id, $insertId, $customerData);
            $this->insertMessage( $this->_message, $id, $insertId);

            //for history show
            $msg = "_CUSTOMER_CREATED_BY"." ".$userData->user->first_name.' '.$userData->user->last_name;
            $this->saveExecuteEvent($this->customer_entry, $this->_current_table_name, $msg, $insertId);
            
            //for created by show..
            $this->Events_model->executeTableEntryEvent('add_entry', $this->_current_table_name, $customerData, $insertId);

            return $insertId;

        }
    }


    public function getTableDetails ($tableName, $id, $fieldName = 'id' ) {

        $result = $this->db->get_where( $tableName, [
            $fieldName => $id,
            "is_deleted" => 0,
            "account_id" => $this->_account_id,
        ])->row_array();

        return $result;
    }

    public function getTableDetailsArray ($tableName, $id, $fieldName = 'id' ) {

        $result = $this->db->get_where( $tableName, [
            $fieldName => $id,
            "is_deleted" => 0,
            "account_id" => $this->_account_id,
        ])->result_array();

        return $result;
    }

    public function insertNewDataInRefTbl ( $tableName, $id, $insertId, $customerData ) {

        $result = $this->db->get_where( $tableName, [
            "customers_id" => $id,
            "is_deleted" => 0,
            "account_id" => $this->_account_id,
        ])->result_array();

        foreach( $result as $data){

            $update = array();
            
            $primaryKeyValue = $data['id'];

            unset($data['id']);
            $data['customers_id'] = $insertId;

            $this->db->insert( $tableName, $data);

            if( $primaryKeyValue == $customerData['contact_customers_id'] &&  $tableName == $this->_contact_customers_table){

                $update['contact_customers_id'] = $this->db->insert_id();

                $this->db->where('id', $insertId);
                $this->db->update($this->_current_table_name,  $update);
            }

            if( $primaryKeyValue == $customerData['location_customers_id'] &&  $tableName == $this->_location_customers_table){

                $update['location_customers_id'] = $this->db->insert_id();

                $this->db->where('id', $insertId);
                $this->db->update($this->_current_table_name,  $update);
            }
        }

    }

    public function insertUserDataInRefTbl ( $tableName, $id, $insertId, $supplierData ) {

        $result = $this->db->get_where( $tableName, [
            "customers_id" => $id,
            "is_deleted" => 0,
            "account_id" => $this->_account_id,
        ])->result_array();

        foreach( $result as $data){

            $primaryKeyValue = $data['id'];
            unset($data['id']);

            $data['customers_id'] = $insertId;

            $this->db->insert( $tableName, $data);

            if( $primaryKeyValue == $supplierData['contact_customers_id']){

                $update['contact_customers_id'] = $this->db->insert_id();

                $this->db->where('id', $insertId);
                $this->db->update($this->_current_table_name,  $update);
            }

            if( $primaryKeyValue == $supplierData['location_customers_id']){

                $update['location_customers_id'] = $this->db->insert_id();

                $this->db->where('id', $insertId);
                $this->db->update($this->_current_table_name,  $update);
            }
        }

    }

    public function insertMessage ( $tableName, $detailsId, $insertId ) {

        $result = $this->db->get_where( $tableName, [
            "entity_id" => $detailsId,
            "is_deleted" => 0,
            "account_id" => $this->_account_id,
        ])->result_array();

        foreach( $result as $data){

            $messageId = $data['id'];
            unset($data['id']);

            $data['entity_id'] = $insertId;

            $this->db->insert( $tableName, $data);

            $insertMessageId = $this->db->insert_id(); 

            $this->messageSubTableDataInsert( $this->_message_thread_notification, $messageId, $insertMessageId );
            $this->messageSubTableDataInsert( $this->_message_conversation_details, $messageId, $insertMessageId );
            
        }

    }

    public function messageSubTableDataInsert( $tableName, $messageId, $insertMessageId ){

        $messageThreadData = $this->getTableDetailsArray( $tableName, $messageId, 'messages_id' );

        foreach( $messageThreadData as $threadData){

            unset($threadData['id']);
            $threadData['messages_id'] = $insertMessageId;

            $this->db->insert( $tableName, $threadData);
        }
    }


   public function search_category( $search_string )
   {
      
       $query = $this->db->query("SELECT * FROM  ".$this->_categoryTable." WHERE id = '1' AND is_deleted='0' LIMIT 5");
       $query_1 = $this->db->query("SELECT * FROM  ".$this->_categoryTable." WHERE name LIKE '%$search_string%' AND id > '1' AND is_deleted='0' AND account_id =" . $this->_account_id . " LIMIT 5");
       $result_1 = $query->result_array();
       $result_2 = $query_1->result_array();
   
       $result = $result_2;//array_merge($result_1, $result_2);
       
       for ($count = 0; $count < sizeof($result); $count ++) {
            
            $all_parent = $this->get_root_item($result[$count]['id']);
            $root_path = $this->format_parent($all_parent, $result[$count]['id']);
            
            $result[$count]['name'] = $root_path;
        }
      
        return array(
            'items' => $result
        );
   }
   
   public function format_parent($all_parent, $id)
   {
       $self_cat = $this->get_cat_name_by_id($id);
       $str = '';
       for ($i = count($all_parent) - 1; $i >= 0; $i --) {
           $str .= $all_parent[$i] . " > ";
       }
       $str .= $self_cat;
       return $str;
   }

   public function get_root_item($id)
   {
       $this->db->select('id,parent,name');
       $query = $this->db->get_where($this->_categoryTable, array(
           'id' => $id,
           'is_deleted' => 0
       ));
       $result = $query->result_array();
       $return_item = array();
       
       if ($result[0]['parent'] == 0) {

       } else {
           
           $this->db->select('id,parent,name');
           $query = $this->db->get_where($this->_categoryTable, array(
               'id' => $result[0]['parent'],
               'is_deleted' => 0
           ));
           $parent = $query->result_array();
           
           $return_item[] = $parent[0]['name'];
           
           $parent = $this->get_root_item($parent[0]['id']);
           
           $return_item = array_merge($return_item, $parent);
       }
       
       return $return_item;
   }

   public function get_cat_name_by_id($id)
    {
        $this->db->select('name');
        $query = $query = $this->db->get_where($this->_categoryTable, array(
            'id' => $id,
            'is_deleted' => 0
        ));
        $cate = $query->result_array();
        
        return $cate[0]['name'];
    }

    public function renderDefaultLocationModal() {

        $data['id'] = $this->input->post('id');

        $data['customer_id'] = $this->input->post('customer_id');

        $customerDetails = $this->getTableDetails( $this->current_class_name, $data['customer_id']);

        $data['locations'] = $this->getLocationDataById( $customerDetails['location_customers_id'] );

        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . "location_customers.xml";
        libxml_use_internal_errors(true);
        $xmlData = simplexml_load_file($xmlFile);
        $xmlObjectArray = B_form_helper::get_xml_object_array($xmlData);
        $action = $this->input->get("action");
        $fieldPermissions = $this->dashboard_model->get_table_permissions_field ("location_customers", $action);

        $data['xmlData'] = $xmlData;
        $data['xmlObjectArray'] = $xmlObjectArray;
        $data['fieldPermissions'] = $fieldPermissions;
        $data['locationCustomersId'] = $customerDetails['location_customers_id'];

        $data['contacts'] = $this->getAllContactsByCustomer($data['customer_id']);
        $data['customerLocations'] = $this->getTableDetailsArray( $this->_location_customers, $data['id'], 'customers_id' );


        $response['data'] = $data;
        $response['html'] = $this->load->view('metrov5_4/customers/modal-views/defaultLocationModal', $data, TRUE);

        return  $response;
    }


    public function updateLocationCustomer() {

        $customersId = $this->input->post('customersId');
        $data['location_customers_id'] = $this->input->post('locationCustomersId');

        $this->db->where( 'id', $customersId);
        $this->db->update( $this->current_class_name, $data);

        $response['query'] = $this->db->last_query();
        $response['location_customers_id'] = $data['location_customers_id'];

        return  $response;
    }

    public function renderDefaultContactModal() {

        $data['id'] = $this->input->post('id');
        $data['fromContact'] = $this->input->post('fromContact');

        
        $data['customer_id'] = $this->input->post('customer_id');

        $customerDetails = $this->getTableDetails( $this->current_class_name, $data['customer_id']);

        if($data['fromContact'] == "contact"){
            $fromContactId = $customerDetails['contact_customers_id'];
        }else{
            $fromContactId = $customerDetails['invoice_contact_customers_id'];
        }

        $data['contact'] = $this->getContactDataById( $fromContactId );

        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . "contact_customers.xml";
        libxml_use_internal_errors(true);
        $xmlData = simplexml_load_file($xmlFile);
        $xmlObjectArray = B_form_helper::get_xml_object_array($xmlData);
        $action = $this->input->get("action");
        $fieldPermissions = $this->dashboard_model->get_table_permissions_field ("contact_customers", $action);

        $data['xmlData'] = $xmlData;
        $data['xmlObjectArray'] = $xmlObjectArray;
        $data['fieldPermissions'] = $fieldPermissions;
        $data['contactCustomersId'] = $fromContactId;
        $data['invoiceContactCustomersId'] = $customerDetails['invoice_contact_customers_id'];

        $data['contacts'] = $this->getAllContactsByCustomer($data['customer_id']);
        $data['customerContacts'] = $this->getTableDetailsArray( $this->_contact_customers, $data['id'], 'customers_id' );


        $response['data'] = $data;
        $response['html'] = $this->load->view('metrov5_4/customers/modal-views/defaultContactModal', $data, TRUE);

        return  $response;
    }


    public function updateContactCustomer() {

        $fromContact = $this->input->post('fromContact');


        $customersId = $this->input->post('customersId');

        if( $fromContact == "contact"){

            $data['contact_customers_id'] = $this->input->post('contactCustomersId');
        }else{

            $data['invoice_contact_customers_id'] = $this->input->post('contactCustomersId');
        }
        

        $this->db->where( 'id', $customersId);
        $this->db->update( $this->current_class_name, $data);

        $response['query'] = $this->db->last_query();
        $response['contact_customers_id'] = @$data['contact_customers_id'];
        $response['invoice_contact_customers_id'] = @$data['invoice_contact_customers_id'];
        $response['fromContact'] = $fromContact;

        return  $response;
    }

    public function getContactDetails() {

        $contactId = $this->input->post('contactId');

        $result = $this->getContactDataById( $contactId );

        return $result;
    }

    public function getLocationDetails() {

        $locationId = $this->input->post('locationId');

        $result = $this->getLocationDataById( $locationId );

        return $result;
    }

   
}





