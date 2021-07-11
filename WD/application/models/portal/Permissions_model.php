<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Permissions_model extends CI_Model
{

    public $_tablePermissions = 'table_permissions';

    public $_tablePermissionsRow = 'permissions_row';

    public function getTableXMLData($tableName)
    {
        if( stripos($tableName, "_") === 0 ){
            $tableName = substr($tableName, 1 );
        }
        $tableName = strtolower( $tableName );
        $xml_data = array();
        $xml_file = FCPATH . "application/tables/" . $tableName . ".xml";
        
        if (file_exists($xml_file)) {
            $xml_data = simplexml_load_file($xml_file);
        }
        return $xml_data;
    }

    public function getAllPermissions($role, $table)
    {
        $this->db->select('*');
        $this->db->where('role', $role);
        $this->db->where('table', $table);
        $this->db->where('is_deleted', 0);
        $data = $this->db->get($this->_tablePermissions)->result();
        
        $responseData = array();
        
        foreach ($data as $item) {
            $responseData[$item->field_name] = $item->permission;
        }
        return $responseData;
    }

    public function update_user_role()
    {
        $id = $this->input->post('id');
        $role = $this->input->post('role');
        $menu = $this->input->post('menu');
        $results =$this->checkUniquePermissionRow( $role, $menu);
   
        if(sizeof($results)>0){  
            return 'false';
        }else{
            if($id>0){
               
                $this->db->where("id", $id);
                $this->db->update($this->_tablePermissionsRow, array(
                    "role" => $role
                ));
                return 'updated';
            }else {
                return 'true';
            }
           
        }
       
    }

    public function update_menu()
    {
        $id = $this->input->post('id');
        $menu = $this->input->post('menu');
        $role = $this->input->post('role');
        $results =$this->checkUniquePermissionRow( $role, $menu);
        if(sizeof($results)>0){
            return array('status' =>  'false');
        }else if($menu=='please_select'){
            return array('status' =>  'select_menu_manager');
        }else{
            if($id>0){
                $this->db->where("id", $id);
                 $this->db->update($this->_tablePermissionsRow, array(
                    "menu" => $menu
                ));

                $view = "";
                $fieldsForPermission = $this->getTableXMLData( @$menu );
                if( count($fieldsForPermission) ) {

                    $fieldPermissions = $this->getAllPermissions( @$role, @$menu );

                    $show_table = (string) $fieldsForPermission['name'];
                    $data_load = $this->getTableXMLData($this->_tablePermissionsRow);

                    $table_permissions_field = $this->dashboard_model->get_table_permissions_field($this->_tablePermissionsRow);
                    $all_view_permission = 	B_form_helper::check_user_permission($table_permissions_field, $data_load[0]->field['name']);
                    $extra_attr = "";
                    if ( $all_view_permission !== '0' && get_user_role() !='app_owner' && $show_table=='permissions_row' )	
                        $extra_attr = ' disabled="disabled"';

                    $subviewdata = array(
                        "options" => $this->getPermissionOptions(),
                        "fieldPermissions" => @$fieldPermissions,
                        "extra_attr" => $extra_attr,
                        "fieldsForPermission" => $fieldsForPermission,
                        "tableName" => $this->tableName,
                        "displayMode" => "none"
                    );
                    $view = $this->load->view($this->template_name . "/" . $this->current_class_name . "/sub_views/table_fields", $subviewdata, true);
                }

                return array('status' => 'updated', 'view' => $view );
            }else {
                return array('status' => 'true');
            }
          
        }
    }

    public function checkUniquePermissionRow( $role, $menu)
    {

        $this->db->select('*');
        $this->db->where("role", $role);
        $this->db->where("menu", $menu);
        $this->db->where("is_deleted", 0);
        $this->db->from($this->_tablePermissionsRow);
        return   $this->db->get()->result_array();
    }


    public function update_add_permission()
    {
        $id = $this->input->post('id');
        $add_permission = $this->input->post('add_permission');
        
        $this->db->where("id", $id);
        return $this->db->update($this->_tablePermissionsRow, array(
            "add_permission" => $add_permission
        ));
    }

    public function update_deleted_permission()
    {
        $id = $this->input->post('id');
        $delete_permission = $this->input->post('delete_permission');
        
        $this->db->where("id", $id);
        return $this->db->update($this->_tablePermissionsRow, array(
            "delete_permission" => $delete_permission
        ));
    }

    public function update_export_permission()
    {
        $id = $this->input->post('id');
        $export_permission = $this->input->post('export_permission');
        
        $this->db->where("id", $id);
        return $this->db->update($this->_tablePermissionsRow, array(
            "export_permission" => $export_permission
        ));
    }

    public function updateHideMenuPermission($role, $table)
    {
        $this->db->select("permission");
        $allFieldsPermissions = $this->db->get_where($this->_tablePermissions, array(
            "role" => $role,
            "table" => $table,
            "is_deleted" => 0
        ))->result_array();
        
        if (sizeof($allFieldsPermissions) > '0') {
            
            $menuShowPermission = 0;
            foreach ($allFieldsPermissions as $permission) {
                
                if ($permission['permission'] != '0') {
                    
                    $menuShowPermission = 1;
                }
            }
        } else {
            $menuShowPermission = 1;
        }
        
        $this->db->where("role", $role);
        $this->db->where("menu", $table);
        $this->db->where("is_deleted", 0);
        
        $this->db->update($this->_tablePermissionsRow, array(
            "show_left_menu" => $menuShowPermission
        ));
    }

    public function setPermission($role, $table, $fieldName, $permission)
    {
        $return = array();
        $this->db->select('*');
        $this->db->where('role', $role);
        $this->db->where('table', $table);
        $this->db->where('field_name', $fieldName);
        $this->db->where('is_deleted', 0);
        
        $data = $this->db->get($this->_tablePermissions)->result();
        
        if (count($data)) {
            if (count($data) > 1) {
                $return['status'] = 0;
                $return['message'] = dashboard_lang('_DUPLICATE');
            } else {
                $updatedData = array(
                    'role' => $role,
                    'table' => $table,
                    'field_name' => $fieldName,
                    'permission' => $permission
                );
                $this->db->update($this->_tablePermissions, $updatedData, array(
                    'id' => $data[0]->id
                ));
                $this->Events_model->executeTableEntryEvent('update_entry', $this->_tablePermissions, $updatedData, $data[0]->id);
                $return['status'] = 1;
            }
        } else {
            $updatedData = array(
                'role' => $role,
                'table' => $table,
                'field_name' => $fieldName,
                'permission' => $permission,
                'is_deleted' => 0
            );
            
            $this->db->insert($this->_tablePermissions, $updatedData);
            $id = $this->db->insert_id();
            $this->Events_model->executeTableEntryEvent('add_entry', $this->_tablePermissions, $updatedData, $id);
            $return['status'] = 1;
        }
        
        return $return;
    }

    public function getPermissionOptions ()
    {
        $options = array( 
            '' => dashboard_lang( '_PLEASE_SELECT' ), 
            '0' => dashboard_lang( '_NONE' ), 
            '1' => dashboard_lang( '_VIEW' ), 
            '2' => dashboard_lang( '_EDIT' ) 
        );
        return $options;
    }

    public function checkPermissionExists () {

        $userRoleId = $this->input->post("userRole");
        $ids = $this->input->post("ids");

        $roleExists = false;

        if ( sizeof($ids) > 0 ) { 

            $this->db->where(" id  IN (".implode(",", $ids).")");
            $this->db->where("is_deleted", 0);

            $result = $this->db->get("permissions_row")->result_array();

            foreach ( $result as $eachPermission ) {

                $checkExists = $this->db->get_where("permissions_row", [
                    "menu" => $eachPermission["menu"],
                    "role" => $userRoleId,
                    "is_deleted" => 0
                ])->result_array();

                if ( sizeof($checkExists) > 0 ) {

                    $roleExists = true;
                    $existsMenu = $eachPermission["menu"];
                }
            }
        }

        if ( $roleExists ) {

            echo json_encode ( ["success" => 0, "msg" => dashboard_lang("_USER_ROLE_ALREADY_EXISTS_FOR")." ".$existsMenu ] );
        }else{
            echo json_encode ( ["success" => 1] );
        }
    }

    public function copyPermission () {

        $userRoleId = $this->input->post("userRole");
        $ids = $this->input->post("ids");
        $permissionRowId = 0;

        if ( sizeof($ids) > 0 ) {

            $this->db->where(" id  IN (".implode(",", $ids).")");
            $this->db->where("is_deleted", 0);

            $result = $this->db->get("permissions_row")->result_array();

            foreach ( $result as $eachPermission ) {

                unset( $eachPermission["id"] );

                $oldRole =  $eachPermission["role"];
                $eachPermission["role"] = $userRoleId;

                $this->db->insert( "permissions_row", $eachPermission );
                $permissionRowId = $this->db->insert_id();

                if ( $permissionRowId > 0 ) {

                    $this->db->order_by("id", "desc");

                    $getTablePermissionDetais = $this->db->get_where("table_permissions", [
                        "role" => $oldRole,
                        "table" => $eachPermission["menu"],
                        "is_deleted" => 0
                    ])->result_array();

                    foreach ( $getTablePermissionDetais as $eachTablePermission ) {

                        unset( $eachTablePermission["id"] );

                        $eachTablePermission["role"] = $userRoleId;
                        
                        $this->db->insert("table_permissions", $eachTablePermission);
                    }
                }
            }

            // permissions_row_role
            $allSessionData = $this->session->all_userdata();

            foreach ( $allSessionData as $key => $value ) {

                if ( strpos ( $key, "permissions_row" ) !== false ) {

                    $this->session->unset_userdata( $key );
                }
            }

            $getRoleName = $this->db->get_where("user_roles", [
                "slug" => $userRoleId,
                "is_deleted" => 0
            ])->row_array();

            $role = $getRoleName["role_name"];

            $this->session->set_userdata("permissions_row_role", ",".$role);
        } 

        echo json_encode ( ["url" => base_url()."dbtables/permissions_row/edit/".$permissionRowId ] );
    }
}