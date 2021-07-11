<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="m-portlet__head portlet-title">
  <div class="m-portlet__head-caption caption font-dark"> 
      <span class="caption-subject bold uppercase">
        <?php
            echo dashboard_lang('_RECORD_EDIT')." ";
            echo dashboard_lang('_'.strtoupper($form_name));          
         ?>
       </span> 
       
       <span class="record_position bold uppercase">
        
       </span>
       <?php $goback=($view_type=='viewonly')?'listing':'trash'; ?>
      <a href="<?php echo $site_url . $controller_sub_folder . "/$class_name/".$goback?>"class="btn green"><i aria-hidden="true" class="fa fa-angle-left"></i> <?php echo dashboard_lang('_BACK_TO_LIST_PAGE'); ?> </a> 
    
   
   </div>



       <div class="m-portlet__head-tools table-toolbar m--margin-top-15 no-bottom-margin pull-right button-holder-padding">
        <div class="m-portlet__nav button_row pull-right">

          <?php if($view_type=='viewonly'){ ?>
          
              <button class="btn btn-success m-btn m-btn--icon  " onclick="javascript: document.location.reload(true);"><?=dashboard_lang("_REFRESH")?></button> 
          
          <?php } else { ?>
          
            
              <?php   echo render_button ('un-trash', 'single-un-trash', 'btn btn-success  pull-left margin-left m-btn m-btn--icon   m--margin-left-10', 'button' , dashboard_lang('_UN_TRASH'), 'retweet '); ?>



          <?php } ?>

        </div>
      </div>

<!-- 
  <div class="table-toolbar no-bottom-margin pull-right button-holder-padding">
    <div class="button_row pull-right">


    
    </div>
  </div> -->
</div>
