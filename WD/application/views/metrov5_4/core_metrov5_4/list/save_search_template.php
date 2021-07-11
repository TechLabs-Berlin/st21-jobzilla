<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
$conSuperAdminRole = $this->config->item("super_user_role");
$all_permission = $this->dashboard_model->get_table_permissions_field("save_search_templates");
$templateForAllPermission = B_form_helper::check_user_permission($all_permission, "for_all");
$templateForAllDisabled = "";
if($templateForAllPermission == 1):
$templateForAllDisabled = "disabled='disabled'";
endif;
$template = $this->config->item('template_name');
$isAllowed = checkIfROleAllowedToEditTemplate ();

?>
<li class="save-search-template m-portlet__nav-item m--align-left pull-left">

   
      <select class="select2 form-control template-dropdown-list dashboard-dropdown m-input pull-left" name="">
        <option value="0"><?php echo dashboard_lang('_PLEASE_SELECT');?></option>        
         <?php 
         $search_view_name = '';
         $selected_view_id = 0 ;
         $template_edit_flag = 1;
         $template_ckb_flag = 0;
         foreach ( $all_saved_template as $each_template ){ 
                if ( $each_template['id'] == $selected_template_id )
                { 
                     $selected = "selected";
                     $search_view_name = $each_template['template_name']; 
                     $selected_view_id = $selected_template_id;
                     $template_ckb_flag = $each_template['for_all'];
                     if($each_template['for_all'] == 1 && $conSuperAdminRole != get_user_role()){
                         $template_edit_flag = 0;
                     }
                } else {
                    
                     $selected = "";
                } 
            
         ?>
           <option value="<?php echo $each_template['id']; ?>" <?php echo $selected; ?>> <?php echo $each_template['template_name']; ?> </option>
        <?php } ?>

      </select>
  
    
    <span class="options pull-left">
      <a href="#"><i aria-hidden="true" class="flaticon-settings"></i></a>
      <div id="view-save-options" class="m--font-bold" style="display:none;">
        <ul>
          <?php if($selected_view_id && $template_edit_flag){ ?><li><a href="#save-success" data-toggle="modal"><?php echo dashboard_lang('_SAVE');?></a></li><?php } ?>
          <li><a href="#save-as" data-toggle="modal"><?php echo dashboard_lang('_SAVE_AS');?></a></li>
          <?php if($selected_view_id && $template_edit_flag && $isAllowed){ ?><li><a href="#list-view-edit" data-toggle="modal"><?php echo dashboard_lang('_EDIT');?></a></li><?php } ?>
          <?php if($selected_view_id && $template_edit_flag && $isAllowed){ ?><li><a href="#list-view-delete" data-toggle="modal"><?php echo dashboard_lang('_DELETE');?></a></li><?php } ?>
        </ul>
      </div>
    </span>
      

      <style>
        li.save-search-template .select2-container {
            float: left;
            margin-right: 10px;
            text-align: left;
        }
    </style>
</li>


 <!-- save success -->
      <div class="modal fade" id="save-success" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel"><?php echo dashboard_lang('_SAVE');?></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
            </div>
            <div class="modal-body m--align-left">
              <p><?php echo dashboard_lang('_SAVE_CURRENT_VIEW');?></p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-accent m-btn m-btn--icon   save-view-btn"><?php echo dashboard_lang('_OK');?></button>
              <button type="button" class="btn btn-metal m-btn m-btn--icon  " data-dismiss="modal"><?php echo dashboard_lang('_CLOSE');?></button>
            </div>
          </div>
        </div>
      </div>
      
      
      <!-- end here --> 
      <!-- save as -->
      <div class="modal fade" id="save-as" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel"><?php echo dashboard_lang('_SAVE_AS');?></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            
            <div class="modal-body m--align-left">
              <form>
                <div class="form-group">
                    <label for="savecurrentview"><?php echo dashboard_lang('_SAVE_CURRENT_VIEW_AS');?></label>
                    <input type="text" class="form-control" id="save_as_currentview" placeholder="" value="<?php echo $search_view_name; ?>">                    
                    
                </div>
                <?php                
                if($templateForAllPermission > 0):                
                ?>
                <div class="form-group">
                    <input type="checkbox" class="" id="list_view_template_for_all" placeholder="" <?php echo $templateForAllDisabled;?> > <span> <?php echo dashboard_lang("_LIST_VIEW_TEMPLATE_FOR_ALL");?> </span>                    
                </div>  
                <?php endif;?>              
                </form>
            </div>
            <div class="modal-footer">
              <button type="button"  class="btn btn-accent m-btn m-btn--icon   save-as-template-btn"><?php echo dashboard_lang('_SAVE');?></button>
              <button type="button" class="btn btn-metal m-btn m-btn--icon  " data-dismiss="modal"><?php echo dashboard_lang('_CLOSE');?></button>
            </div>
           
          </div>
        </div>
      </div>
      <!-- end here -->
      <!-- edit -->
      
      
      <div class="modal fade" id="list-view-edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel"><?php echo dashboard_lang('_EDIT');?></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
             <div class="modal-body m--align-left">             
                <div class="form-group">
                    <label for="savecurrentview"><?php echo dashboard_lang('_EDIT_VIEW_NAME');?></label>
                    <input type="text" class="form-control" id="newviewname"  value="<?php echo $search_view_name;?>" />                    
                </div>
                <?php                 
                if($templateForAllPermission > 0):
                ?>
                <div class="form-group">
                    <input type="checkbox" class="" id="list_view_template_for_all" placeholder="" <?php echo $template_ckb_flag?"checked":""; ?>  <?php echo $templateForAllDisabled;?> > <span> <?php echo dashboard_lang("_LIST_VIEW_TEMPLATE_FOR_ALL");?> </span>                    
                </div>  
                <?php endif;?>                              
            </div>            
            <div class="modal-footer">
              <button type="submit" id="rename_search_view" class="btn btn-accent m-btn m-btn--icon  "><?php echo dashboard_lang('_SAVE');?></button>
              <button type="button" class="btn btn-metal m-btn m-btn--icon  " data-dismiss="modal"><?php echo dashboard_lang('_CLOSE');?></button>
            </div>
          </div>
        </div>
      </div>
      <!-- end here -->
      <!-- delete -->
      <div class="modal fade" id="list-view-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel"><?php echo dashboard_lang('_DELETE_CONFIRMATION_MESSAGE');?></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
            </div>
            <div class="modal-body">
            <?php if($selected_view_id > 0 ){ ?>
              <p><?php echo dashboard_lang('_DELETE_VIEW');?> <?php echo $search_view_name ; ?> ?</p>
            <?php }else{ ?>
                <p><?php echo dashboard_lang('_NO_VIEW_SELECTED');?></p>         
            <?php } ?>
            </div>
            <div class="modal-footer">
              <?php if($selected_view_id > 0 ){ ?>
                <button type="button" id="deleted_search_view_id" data-viewid="<?php echo $selected_view_id; ?>" class="btn btn-danger m-btn m-btn--icon  "><?php echo dashboard_lang('_DELETE');?></button>
              <?php }?>
              <button type="button" class="btn btn-metal m-btn m-btn--icon  " data-dismiss="modal"><?php echo dashboard_lang('_CLOSE');?></button>
            </div>
          </div>
        </div>
      </div>
      <!-- end here -->








<script>
	var selected_view_id = '<?php echo $selected_view_id; ?>';
	jQuery( document ).ready(function() {
   	 	jQuery('.options a').click(function(e) {
			e.preventDefault();
         	jQuery('#view-save-options').toggle("slide-down");
         	if( $('span.options').parent().next().length < 1 ){
         		jQuery('#view-save-options').css('right', '0');
            }
    	});
	});
</script>
<?php $this->load->view($template.'/core_'.$template.'/list/save_template_form', array('listing_field' =>$listing_field, 'ordering_fields' => $ordering_fields,));?>
