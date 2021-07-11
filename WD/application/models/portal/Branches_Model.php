<?php
/*
 * @author Atiqur Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Branches_Model extends CI_Model
{

    /*
     * Function :: Create - for create new category
     */
    public $_account_id;
    public $_branches_table = 'branches';
    public $_event_log_table = 'event_log';
    public $_dashboard_login_table = 'dashboard_login';
    public $_countries = 'countries';
    public $_users_branches = 'users_branches';


    public function __construct()
    {
        parent::__construct();
        $this->_account_id = (int)get_account_id(1);
        $this->load->model('portal/Events_model');
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
        $res2 = $this->db->get($this->_event_log_table)->row_array();
    
    
        return array( "add" => $res1, "edit" => $res2 );
    
    }

    public function getDashboardUserInfo($id) {
    
        $this->db->select(['first_name', 'last_name']);
        $this->db->where('id', $id);
        $Q = $this->db->get($this->_dashboard_login_table);
    
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



    public function getUsersList() {

        $this->db->select(['id', 'first_name', 'last_name', 'email']);
        $this->db->where('is_deleted', 0);
        $this->db->where('account_id', $this->_account_id);
        $results = $this->db->get($this->_dashboard_login_table)->result_array();

        return $results;
    }

    public function getSelectedUsersIds($branchId) {

        $this->db->select('dashboard_login_id');
        $this->db->where('branches_id', $branchId);
        $this->db->where('is_deleted', 0);
        $this->db->where('account_id', $this->_account_id);
        $results = $this->db->get($this->_users_branches)->result_array();
        
        $ids = [];
        foreach ($results as $key => $res) {
            $ids[] =  $res['dashboard_login_id'];
        }

        return $ids;
    }

    public function deleteBranchesCustomersRowsByBrnachId($branchId) {

        $this->db->where('branches_id', $branchId);
        $this->db->delete($this->_users_branches);
    }

    public function saveUsersBranches($branchId, $userIds) {

        $this->deleteBranchesCustomersRowsByBrnachId($branchId);
        $existingUsers = array();

        foreach($userIds as $key => $user) {

            $data['branches_id'] = $branchId;
            $data['dashboard_login_id'] = $user;
            $data['account_id'] = $this->_account_id;
            
            $userExistdInOtherBranch = $this->userExistdInOtherBranches($user);

            if($userExistdInOtherBranch){
                
                $this->db->select('email');
                $this->db->where('id', $user);
                $this->db->where('is_deleted', 0);
                $this->db->where('account_id', $this->_account_id);
                $results = $this->db->get($this->_dashboard_login_table)->row_array();
                $existingUsers[] = $results['email'];
            }else{

                $this->db->insert($this->_users_branches, $data); 
            }

        }

        return $existingUsers;
    }

    public function userExistdInOtherBranches($user){

        $this->db->select('dashboard_login_id');
        $this->db->where('dashboard_login_id', $user);
        $this->db->where('is_deleted', 0);
        $this->db->where('account_id', $this->_account_id);
        $results = $this->db->get($this->_users_branches)->row_array();

        if( $results){
            return true;
        }else{
            return false;
        }
    }

    public function deleteAssignedUsers($branchId){
        $this->db->where('branches_id', $branchId);
        $results = $this->db->delete($this->_users_branches);
        
        if( $results){
            return true;
        }else{
            return false;
        }
    }

}

?>