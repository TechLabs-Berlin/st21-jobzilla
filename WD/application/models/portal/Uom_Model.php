<?php
/*
 * author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Uom_Model extends CI_Model
{

    public $_table_prefix;
    public $uom = 'uom';
    public $uomConversions = 'uom_conversions';
    public $accountId;

    public function __construct()
    {
        parent::__construct();
        $this->_table_prefix = $this->config->item('prefix');
        $this->accountId = get_account_id(1);
    }

    public function getAllUomConversions($uomId) {

        $this->db->select([$this->uomConversions.".*", $this->uom.".uom"]);
        $this->db->from($this->uomConversions);
        $this->db->where($this->uomConversions.'.uom_id', $uomId);
        $this->db->where($this->uomConversions.'.account_id', $this->accountId);
        $this->db->where($this->uomConversions.'.is_deleted', 0);
        $this->db->join($this->uom, $this->uom.'.id = '.$this->uomConversions.'.uom_from', 'left' );
        $result = $this->db->get()->result_array();
    
        return $result;
    }

    public function getUom() {
        
        $this->db->where('account_id', $this->accountId);
        $this->db->where('is_deleted', 0);
        $result = $this->db->get($this->uom)->result_array();
        return $result;
    
    }


    public function getTableRow($id, $table) {
        
        $this->db->where('id', $id);
        $result = $this->db->get($table)->row_array();
        return $result;
    
    }

    public function deleteUomConversion() {
        
        $id = $this->input->post('id');
        $uomId = $this->input->post('uomId');

        $this->db->where('id', $id);
        $this->db->update( $this->uomConversions, ['is_deleted' => 1]);
    
    }

    public function renderUomConversions() {
    
        $uomId = $this->input->post('id');
        $data['uomConversions'] = $this->getAllUomConversions($uomId);
        $this->load->view('metrov5_4/uom/edit/uomConversions', $data);
    
    }


    public function renderUomConversionsModal() {
    
        $data['id'] = $this->input->post('id');
        $data['uomId'] = $this->input->post('uomId');

        $xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR . "$this->uomConversions.xml";
        libxml_use_internal_errors(true);
        $xmlData = simplexml_load_file($xmlFile);
        $xmlObjectArray = B_form_helper::get_xml_object_array($xmlData);
        $action = $this->input->get("action");
        $fieldPermissions = $this->dashboard_model->get_table_permissions_field ($this->uomConversions, $action);

        $data['xmlData'] = $xmlData;
        $data['xmlObjectArray'] = $xmlObjectArray;
        $data['fieldPermissions'] = $fieldPermissions;
        $data['uomConversion'] = $this->getTableRow($data['id'], $this->uomConversions);
        $data['uomFrom'] = $this->getUom();
        $data['mainUom'] = $this->getTableRow($data['uomId'], $this->uom);

        $this->load->view('metrov5_4/uom/edit/uomConversionsModal', $data);
    }


    public function saveUomConversion() {

        $data['id'] = $this->input->post('id');
        $postData['uom_id'] = $this->input->post('uomId');
        $postData['uom_from'] = $this->input->post('fromUom');
        $postData['factor'] = $this->input->post('factor');
        $money = $postData['factor'];
        if (isset($postData['factor']) && strlen($postData['factor']) > 0) {
            $default_format_from_config = strtolower($this->config->item('#DEFAULT_MONEY_FORMAT'));
                
                if ( $default_format_from_config == 'us' ) {
                    if (isset($money) && strlen($money) > 0) {
                        $postData['factor'] = str_replace(",", "", $money);
                    } else {
                        $postData['factor'] = null;
                    }
                }else {
                    
                    if (isset($money) && strlen($money) > 0) {
                        $postData['factor'] = str_replace(".", "", $money);
                        $postData['factor'] = str_replace(",", ".",  $postData['factor'] );
                        
                    } else {
                        $postData['factor'] = null;
                    }
                }
            
        } 
        
        $postData["account_id"] = $this->accountId;
        if ($data['id'] > 0) {
            $this->db->where('id',$data['id']);
            $this->db->update($this->uomConversions, $postData );
        } else {
            $this->db->insert($this->uomConversions, $postData );
        }

    }



}

?>
