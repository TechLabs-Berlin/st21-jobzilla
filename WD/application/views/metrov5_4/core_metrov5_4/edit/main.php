<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<div class="row edit-details-item">
  <div class="col-md-12">
    <div class="m-portlet light bordered">                    
     <?php 
     if( file_exists(FCPATH.'application/views/metrov5_4/'.$class_name.'/helper_render_edit_button.php') ){
         
         require_once (FCPATH.'application/views/metrov5_4/'.$class_name.'/helper_render_edit_button.php');
         
     }else{
         
         require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/helper_render_edit_button.php');
     
     }?>     <div class="m-portlet__body">
                  <div class="row">
                    <div class="col-lg-6">                         
                      <?php
                      $groupData = array();
                      $form_orientation = @$data_load['form_orientation'];
                      $field_column = @$data_load['field_column'];
                      $data = array();
                      $data['form_orientation'] = $form_orientation;
                      $data['field_column'] = $field_column;
                      
                      foreach ($data_load->field as $value) {

                          $value['field_table_name'] = $class_name;
                          
                          if($value['hidden_in_edit'] == 1){
                              continue;
                          }
    
                        if (isset($value['group']) && $value['group']) {
    
                          $groupData[(string) $value['group']][] = $value;
                          
                        } else {

                           //render fields
                            echo B_form_helper::render_field( $value, @$data_edit[(string) $value["name"]] , $table_permissions_field, '', $form_orientation, $field_column);
                            
                            //check loookup auto suggest
                            if((string)$value["autosuggest"] == 1){
                                $lookup_autosuggest_field = $value["name"];                               
                            }                            

                        }
                      }
    
                      $data['super_admin'] = $super_admin;
                      $data['tab_data'] = $groupData;
                      $data['data_edit'] = @$data_edit;
                      $data['edit_field_array'] = $edit_field_array;
                      $data['class_name'] = $class_name;
                      $data['template'] = $template;
                      ?>
                    </div>
                  </div>
                  </div>
                   <?php if(count($groupData)>0){ 
                                if(file_exists(FCPATH.'application/views/metrov5_4/'.$class_name.'/edit/edit_tab.php')){
                                    $this->load->view('metrov5_4/'.$class_name.'/edit/edit_tab', $data);
                                }else{
                                    $this->load->view('metrov5_4/core_metrov5_4/edit_tab', $data);
                                }
                                
                         } ?>

                </div>
              </div>

    <input type="hidden" name="saveAndClose" id="saveAndClose" value="">
    <input type="hidden" name="saveAndNext" id="saveAndNext" value="">
    <input type="hidden" name="activeTab" id="activeTab" value="">
    <input type="hidden" name="drop_down_id" id="drop_down_id" value="">
    <input type="hidden" name="ref_table" id="ref_table" value="">
    <input type="hidden" name="ref_key" id="ref_key" value="">
    <input type="hidden" name="ref_value" id="ref_value" value="">
    <input type="hidden" name="iframeView" id="iframeView" value="<?php echo empty($_GET['iframeView'])?'':'1';?>">
</div>
