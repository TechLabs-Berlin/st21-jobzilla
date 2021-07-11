<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>     
		 
		<div class='form-group m-form__group m--font-bolder eav_area<?php echo $xmlObject['name']; ?>' id="field_id_<?php echo $xmlObject['name']; ?>">
			<div style="clear: left;">
				<select name="<?php echo $reference_key; ?>[]" class="form-control select2 dashboard-dropdown ref_select" multiple>
				 <?php 
				 	echo renderMultiSelectOptions( 
						$ref_table_name, 
						$ref_key, 
						$ref_value, 
						$all_values_list,
						$order_on,
						$order_by,
						$is_translated
					);
				 ?>
				</select>
		 	</div>
		 </div>
		  
