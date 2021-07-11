<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="m-portlet__head portlet-title">
  <div class="m-portlet__head-caption caption font-dark"> 
      <span class="m-portlet__head-text caption-subject m--font-boldest uppercase">
        <?php
            echo dashboard_lang('_RECORD_EDIT')." ";
            echo dashboard_lang('_'.strtoupper($form_name));          
         ?>
       </span> 
       
       <span class="record_position m--font-boldest uppercase">
        <?php
        if(isset($position_data) AND sizeof($position_data) > 0){
            $position_text = "( ".$position_data['current_position']." ".dashboard_lang("_OF")." ".$position_data['total_rows']." )";
            if(!empty($position_data['current_position'])){
                echo $position_text;
            }            
        }
         
        ?>
       </span>
              
       <?php 
           //additional functionality for back to listing page
            $default_per_page = 10;
            $position = @$position_data['current_position'];
            $per_page = $this->session->userdata('per_page@'.$this->current_class_name);
            if(!isset($per_page)){
                $this->session->set_userdata('per_page@'.$this->current_class_name, $default_per_page);
                $per_page = $default_per_page;
            }
            $record_position = get_record_position_in_page($position, $per_page);
           
       ?>
       
      <a href="<?php echo $site_url . $controller_sub_folder . '/'.$delete_lock_table_path.'/'.$record_position ; ?>" class="btn btn-accent m-btn m-btn--icon  "><i aria-hidden="true" class="fa fa-angle-left"></i> <?php echo dashboard_lang('_BACK_TO_LIST_PAGE'); ?> </a> 
   
   </div>
  <div class="m-portlet__head-tools table-toolbar no-bottom-margin pull-right button-holder-padding" style="margin-top: 4px !important;">
    <div class="m-portlet__nav button_row pull-right">
      <?php 
      
      if(isset($hide_save_btn) && $hide_save_btn != 1){
           echo render_button ('', 'save_the_form', 'btn btn-success m-btn--icon   pull-left margin-left save_button', 'submit' , dashboard_lang('_SAVE'), 'save');
          if( (isset($position_data) AND sizeof($position_data) > 0) AND ($this->config->item('#SHOW_SAVE_AND_NEXT_BUTTON') == "Yes") AND $position_data['current_position'] != $position_data['total_rows'] ){
              echo render_button ('', 'save_and_next', 'btn btn-success m-btn--icon   pull-left margin-left save_button', 'submit' , dashboard_lang('_SAVE_AND_NEXT'), 'save');
          }
          
          echo render_button ('', 'save_and_close', 'btn btn-success m-btn--icon   pull-left margin-left save_button', 'submit' , dashboard_lang('_SAVE_AND_CLOSE'), 'save');
         
          
      }
        
        
        if(isset($id) && ($id > 0)){
            if ($add_delete_permissions['add_permission'] == '1') { 
            
      ?>
              <a href="<?php  echo $site_url . $edit_path; ?>">
              <?php
                   echo  render_button ('', 'add_new_item', 'btn btn-accent m-btn--icon   pull-left margin-left', 'button' , dashboard_lang('_ADD'), 'plus');
              ?>
              </a>
              <?php                                 
        
             } 
             
             if(isset($show_copy) AND $show_copy){
             
                 echo  render_button ('copy', 'copy', 'btn btn-success m-btn--icon   pull-left margin-left', 'button' , dashboard_lang('_COPY'), 'copy');
             }
             
             if ($add_delete_permissions['delete_permission'] == '1') {
                echo  render_button ('delete', 'delete', 'btn btn-danger m-btn--icon   pull-left margin-left', 'button' ,  dashboard_lang('_DELETE'), 'remove');
        
             };
        
         } ?>
    </div>
  </div>
</div>
