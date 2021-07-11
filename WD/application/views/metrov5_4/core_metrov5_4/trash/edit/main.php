<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<div class="row edit-details-item">
  <div class="col-md-12">
    <div class="m-portlet light bordered">                    
     <?php 
     if( file_exists(FCPATH.'application/views/metrov5_4/'.$class_name.'/helper_render_edit_button.php') ){
         
         require_once (FCPATH.'application/views/metrov5_4/'.$class_name.'/helper_render_edit_button.php');
         
     }else{
         
         require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/trash/helper_render_edit_button.php');
     
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
</div>
<style type="text/css">
  .discuss-with-btn .messages_list,
  .inbox-content > div:last-child,
  .delete_msg_thread,
  .add_rows_sample_values,
  .delete_msg_conversation{
    display: none;
  }
</style>


<link href="<?=site_url()?>media_metrov5_4/custom/css/components.css" rel="stylesheet" type="text/css"/>

<?php if($view_type=='trash_detail'){ ?>

  <script type="text/javascript">

        var trashUrl = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . 'trash_operation'; ?>";    

        var confirmDeleteAlert = "<?php echo dashboard_lang("_ARE_YOU_SURE_TO_DELETE") ?>";
        var confirmRestoreAlert = "<?php echo dashboard_lang("_ARE_YOU_SURE_TO_RESTORE") ?>";


        jQuery(document).ready(function($){

            $("#single-un-trash").on("click", function(){

                var checkAllValues = [<?php echo $id?>];
                var confirmDelete = confirm(confirmRestoreAlert);

                if (confirmDelete == true) {

                  $('#ajax_load').show();

                  $.ajax({
                    url:trashUrl,
                    type: 'post',
                    data: {ids: checkAllValues, un_trash:1 },
                    success:function(result){
                      $('#ajax_load').hide();
                      window.location.href = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . 'trash'; ?>";  
                    }
                  });

                } else {
                  return false;
                }

              

            });
        });
  </script>
<?php } ?>
