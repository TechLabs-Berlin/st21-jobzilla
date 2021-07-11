<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<form id="template-form" class="pull-left" action ="<?php echo base_url();?>portal/system/save_template_form_data" method="POST">
  
  <input type='hidden' name='template_table_name' id='template_table_name' value=''>
  <input type='hidden' name='template_new_save_name' id="template_new_save_name" value=''>
  <input type="hidden" name="template_action_type" id="template_action_type" value="">
  <input type='hidden' name="template_id" id="template_id"  value=''>
  <input type='hidden' name='template_edit_name' id='template_edit_name' value=''>
  <input type='hidden' name='template_new_name' id="template_edit_name" value="">
  <input type="hidden" name="template_listing_fields" id="template_listing_fields" value="<?php echo implode("," ,$listing_field);?>" >
  <input type="hidden" name="template_ordering_fields" id="template_ordering_fields" value='<?php echo is_array($ordering_fields) ?  json_encode($ordering_fields) : '' ;?>' >
  <input type='hidden' name="template_for_all_user" id="template_for_all_user"  value=''>
</form>


<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/listing_save_search.js"> </script>


