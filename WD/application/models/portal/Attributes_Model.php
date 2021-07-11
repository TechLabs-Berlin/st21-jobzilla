<?php
/*
 * @author boo2mark
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Attributes_Model extends CI_Model
{

    private $_categoryTable = 'categories';

    private $_attributeCategoriesTable = 'attribute_categories';

    private $_attributeValuesTable = 'attribute_values';

    private $_attributeValueCategories = 'attribute_value_categories';

    private $_valuesTable = 'values';

    public function __construct()
    {
        parent::__construct();
    }

    public function format_parent($all_parent, $id)
    {
        $self_cat = $this->get_cat_name_by_id($id);
        $str = '';
        for ($i = count($all_parent) - 1; $i >= 0; $i --) {
            $str .= $all_parent[$i] . " > ";
        }
        $str .= dashboard_lang($self_cat);
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
         
        if ($result[0]['parent'] == 0 || sizeof($result) == 0) {} else {
             
            $this->db->select('id,parent,name');
            $query = $this->db->get_where($this->_categoryTable, array(
                'id' => $result[0]['parent'],
                'is_deleted' => 0
            ));
            $parent = $query->result_array();
            if ( sizeof($parent) > 0 ) {
                 
                $return_item[] = dashboard_lang($parent[0]['name']);
                $parent = $this->get_root_item($parent[0]['id']);
                $return_item = array_merge($return_item, $parent);
            }
    
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


      public function getAllCategories()
    {
        $user = BUserHelper::get_instance();
        
        $result = $this->db->get_where($this->_categoryTable, [
            "is_deleted" => 0,
            'account_id' => get_account_id()
        ])->result_array();
        
        for ( $count = 0; $count < sizeof($result); $count++ ) {
            
           $all_parent = $this->get_root_item($result[$count]['id']);
           $root_path = $this->format_parent($all_parent, $result[$count]['id']);
   
           $result[$count]['name'] = $root_path;
        }
        
        
        return $result;
    }

    public function getAllCategoriesByAttribute($aId)
    {
        $items = [];
        
        $result = $this->db->get_where($this->_attributeCategoriesTable, [
            "is_deleted" => 0,
            "attribute_id" => $aId
        ])->result_array();
        
        foreach ($result as $eachItem) {
            $items[] = $eachItem["category_id"];
        }
        
        return $items;
    }

    public function getAllValuesByAttribute($aId)
    {
        $this->db->select('av.* , v.value_name ');
        $this->db->where('av.is_deleted', 0);
        $this->db->where('attribute_id', $aId);
        $this->db->join($this->_valuesTable . ' as v', 'v.id = av.value_id', 'inner');
        $this->db->order_by('av.sort', 'ASC');
        $result = $this->db->get($this->_attributeValuesTable . ' as av ')->result_array();
        
        return $result;
    }

    public function saveAttributeCategories($id)
    {
        $this->db->where("attribute_id", $id);
        $this->db->delete($this->_attributeCategoriesTable);
        
        $categories = $this->input->post("categories");
        
        foreach ($categories as $category) {
            
            $insertData = [
                "attribute_id" => $id,
                "category_id" => $category
            ];
            
            $this->db->insert($this->_attributeCategoriesTable, $insertData);
        }
        
        
        
        $this->db->where('attribute_id', $id );
        
        $this->db->where_not_in('category_id', $categories );
        
        $this->db->delete($this->_attributeValueCategories);
        
    }

    public function saveAttributeValueCategories($id , $attribute_id)
    {
        $this->db->where("attribute_value_id", $id);
        $this->db->delete($this->_attributeValueCategories);
        
        $categories = $this->input->post("categories");
        
        foreach ($categories as $category) {
            
            $insertData = [
                "attribute_value_id" => $id,
                'attribute_id'=> $attribute_id,
                "category_id" => $category
            ];
            
            $this->db->insert($this->_attributeValueCategories, $insertData);
        }
    }

    public function saveAttributeValues()
    {
        $attribute_id = $this->input->post('attribute_id');
        $value = $this->input->post("value");
        $sort = $this->input->post('sort');
        $attribute_value_id = $this->input->post('attribute_value_id');
        $valueType = $this->input->post("value_type");
        

        if ( $valueType == 'number') {
            
             $value = number_format( floatval( str_replace(",", ".", $value) ), 2 );
        }
        
        $results = $this->db->get_where($this->_valuesTable, [
            "is_deleted" => 0,
            "value_name" => $value
        ])->result_array();
        
        if (is_array($results) and count($results)) {
            $value_id = $results[0]['id'];
        } else {
            $this->db->insert($this->_valuesTable, array(
                'value_name' => $value,
                'sort' => 0
            ));
            $value_id = $this->db->insert_id();
        }
        
        $insertData = [
            "attribute_id" => $attribute_id,
            "value_id" => $value_id,
            'sort' => $sort
        ];
        
        if ($attribute_value_id > 0) {
            $this->db->update($this->_attributeValuesTable, $insertData, array(
                'id' => $attribute_value_id
            ));
            
            $this->saveAttributeValueCategories($attribute_value_id , $attribute_id );
        } else {
            $this->db->insert($this->_attributeValuesTable, $insertData);
            
            $this->saveAttributeValueCategories($this->db->insert_id() , $attribute_id);
        }
    }

    public function chckTypeIsNumber( $attributesId = 0 ) {
        
        $this->db->select("value_type");
        
        $result = $this->db->get_where("attributes", [
            "id" => $attributesId,
            "value_type" => "number"
        ])->result_array();
        
        if ( sizeof($result) > 0 ) {
            
            return true;
        }else {
            return false;
        }
    }
    public function attributesValuesModal($id = 0)
    {
        $viewData = array();
        
        $viewData['selected_categories'] = array(); 
        
        $category = $this->input->post('cat_ids');
        $categories = isset($category)?$category: array();
      

        if ( sizeof($categories) == '1' && $categories[0] == '-1') {
            print_r('tt');
            $categories = null;
        }else if (  sizeof($categories) == '0'  ) {
            $categories[0] = -1231232;
            print_r('ss');
        }
        
        $this->db->select('name, id ');
        
        $this->db->where_in('id', $categories);
        
        $allCategories = $this->db->get_where($this->_categoryTable, ["is_deleted" => '0' ] )->result_array();
        
        if ($id) {
            
            $this->db->select('av.* , v.value_name ');
            $this->db->where('av.id', $id);
            $this->db->join($this->_valuesTable . ' as v', 'v.id = av.value_id', 'inner');
            $this->db->order_by('av.sort', 'ASC');
            $result = $this->db->get($this->_attributeValuesTable . ' as av ')->result_array();
            
            if (is_array($result) && count($result)) {
                $viewData['value'] = $result[0]['value_name'];
                $viewData['sort'] = $result[0]['sort'];
                $viewData['attribute_value_id'] = $id;
                
                $this->db->select('c.name , c.id');
                
                $this->db->where('attribute_value_id', $id);
                $this->db->join($this->_categoryTable . ' as c', 'c.id = avc.category_id', 'inner');
                $result = $this->db->get($this->_attributeValueCategories . ' as avc ')->result_array();
                
                foreach ($result as $singleItem ){
                    $viewData['selected_categories'][] = $singleItem['id'];
                }
                
                $viewData['categories'] = $allCategories;
                
               
            }
            
            $viewData['value_type'] = $this->input->post('value_type');
        } else {
            
            
            $viewData['categories'] = $allCategories;
            
            $viewData['selected_categories'] = $categories ; 
            
            $viewData['value_type'] = $this->input->post('value_type');
        }
        
        $viewData["allSavedCat"] = $this->getSavedCategories( $id );
        
        $this->load->view("metrov5_4/attributes/modals/attributesValuesModal", $viewData);
    }
    
    
    public function getSavedCategories ( $id ) {
        
        $results = $this->db->get_where("attribute_value_categories", ["attribute_value_id" => $id ]) ->result_array();
        
        $responseData = [];
        foreach ( $results as $item ) {
            
            $responseData[] = $item["category_id"];
        }
            
        return $responseData;
    }

    public function getAttributeValuesList($id)
    {
        $this->load->view("metrov5_4/attributes/edit/edit_tab", array(
            'id' => $id
        ));
    }

    public function getEAVCategories($avID)
    {
        $this->db->select('c.name ');
        
        $this->db->where('attribute_value_id', $avID);
        $this->db->join($this->_categoryTable . ' as c', 'c.id = avc.category_id', 'inner');
        $result = $this->db->get($this->_attributeValueCategories . ' as avc ')->result_array();
        
        $returnString = array();
        
        if (is_array($result) && count($result)) {
            foreach ($result as $cat) {
                $returnString[] = dashboard_lang($cat['name']);
            }
            
            $returnString = implode(', ', $returnString);
        } else {
            $returnString = '';
        }
        
        $viewData["allSavedCat"] = $this->getSavedCategories( $avID );
        
        if ( in_array( -1 , $viewData["allSavedCat"]) ) {
            
            $returnString = dashboard_lang("_ALL");
        }
        
        return $returnString;
    }
    
    

    public function deleteAttributeValues() 
    { 
        $id = $this->input->post('attribute_value_id'); 
        $value_id = $this->input->post('value_id'); 
        $attribute_id = $this->input->post('attribute_id'); 
 
        $query = $this->db->get_where('country_values_categories', array('attribute_id' => $attribute_id, 'value_id' => $value_id))->result_array(); 
      
        if($query){ 
            echo 'false'; 
        }else{ 
            $this->db->delete($this->_attributeValuesTable, array(  'id' => $id  )); 
            echo 'true'; 
        } 
 
        
    } 


    public function renderAttributeValuesFromCat ( $categoryId, $countryId, $type ) {
        
        $data["values"] = $this->getAttributesValuesLists ( $categoryId );
        $data["valuesNamesLists"] = $this->allValuesLists();
        $data["valuesLists"] = $this->getSelectedValues( $countryId );
        $data["category_id"] = $categoryId;
        $data["type"] = $type;

        
        $this->load->view( "metrov5_4/attributes/eav-view-selection/main", $data );
    }
    
    public function getAttributesValuesLists(  $id ) {
        
        $allAtrributes = $this->allAttributes ( $id );
        
        for ( $count = 0; $count < sizeof($allAtrributes); $count++ ) {
            
            if ( $allAtrributes[$count]["allow_category"] == '1' ) {
                
                $allAtrributes[$count]["values_lists"] = $this->getAttributesValuesCat  (  $allAtrributes[$count]["id"] , $id );
            }else {
                $allAtrributes[$count]["values_lists"] = [];
            }
        }
        
        return $allAtrributes;

    }

    public function allAttributes  ( $categoryId = 0 ) {
       
        $result = $this->db->get_where("attributes", [
            "is_saved" => 1,
            "is_deleted" => 0,
            "account_id" => get_account_id()
        ])->result_array();
        
        $this->db->select("category_id,attribute_id");
        $allAttrCategories = $this->db->get_where("attribute_categories", [
         "is_deleted" => 0,   
         "account_id" =>  get_account_id(),
            
        ])->result_array();
        
        $attrCatLists = [];
        
        foreach ( $allAttrCategories as $categories ) {
            
            $attrCatLists[ $categories["attribute_id"] ][] = $categories["category_id"];
        }
 
        
        for ( $count =0; $count < sizeof($result); $count++) {
            
            if ( !isset($attrCatLists[ $result[$count]["id"] ]) ) {
                
                $result[$count]["allow_category"] = 0;
            }else {
                if ( in_array( "-1" , $attrCatLists[ $result[$count]["id"] ] ) ) {
                    
                    $result[$count]["allow_category"] = 1;
                }else if ( in_array( $categoryId , $attrCatLists[ $result[$count]["id"] ] ) ) {
                    $result[$count]["allow_category"] = 1;
                }else {
                    $result[$count]["allow_category"] = 0;
                }   
            }
        }
        
        return $result;
    }

    public function allValuesLists () {
        
        $allValuesLists = [];
        
        $result = $this->db->get_where("values", [
           "is_deleted" => 0,
           "account_id" => get_account_id()
        ])->result_array();
       
        foreach ( $result as $items ) {
            
            $allValuesLists[ $items["id"] ] = $items["value_name"];
        }
        
        return $allValuesLists;
       
    }

    public function getSelectedValues ( $countryId ) {
        
        $allValuesLists = [];
        
        $result = $this->db->get_where("country_values_categories", [
            "country_id" => $countryId
        ])->result_array();
         
        foreach ( $result as $items ) {
        
            $allValuesLists[ $items["type"] ][ $items["category_id"] ][ $items["attribute_id"] ][] = $items["value_id"];
        }
        
        return $allValuesLists;
        
    }
    
    public function getCategoriesLists()
   {
 
       $query = $this->db->query("SELECT * FROM  ".$this->_categoryTable." WHERE id = '1' AND is_deleted='0' LIMIT 5");
       $query_1 = $this->db->query("SELECT * FROM  ".$this->_categoryTable." WHERE id > '1' AND is_deleted='0' AND account_id =" . get_account_id());
       $result_1 = $query->result_array();
       $result_2 = $query_1->result_array();
   
       $result = array_merge($result_1, $result_2);
       
       $options = "<option value='0'>".dashboard_lang("_PLEASE_SELECT")."</option>";
       
       for ($count = 0; $count < sizeof($result); $count ++) {
   
           $all_parent = $this->get_root_item($result[$count]['id']);
           $root_path = $this->format_parent($all_parent, $result[$count]['id']);
   
           $result[$count]['name'] = $root_path;
           $options .= "<option value='".$result[$count]['id']."'>".$result[$count]['name']."</option>";
       }

       return $options;
   }
   

    public function getAttributesValuesCat( $attributeId, $categoryId ) {
        
        $this->db->select("distinct(values.id) as value_id");
        $this->db->from("attribute_values");
        $this->db->join("attribute_value_categories", "attribute_value_categories.attribute_value_id = attribute_values.id");
        $this->db->join("values", "attribute_values.value_id = values.id AND values.is_deleted = 0");
        $this->db->where("attribute_values.attribute_id", $attributeId);
        $this->db->where("attribute_values.is_deleted", 0);
        $this->db->where("( attribute_value_categories.category_id = -1 OR attribute_value_categories.category_id = ".intval($categoryId).")");
        
        $result = $this->db->get()->result_array();

        $allValues = [];
        
        foreach ( $result as $items ) {
            
            $allValues[] = $items["value_id"];
        }
        
        
        return $allValues;
         
        
    }


}