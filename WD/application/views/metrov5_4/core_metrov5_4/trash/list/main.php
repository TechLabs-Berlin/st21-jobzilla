<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$column_width_px = FALSE;
$checkbox_width = "width='4%'";
$column_width_class = "";
$table_width = "width='100%'";
$get_xml_attr_width = "";
$field_counter = 0;
$total_attr_width = 0;

if(isset($xmlData['column_width_px']) AND (string)$xmlData['column_width_px'] == "1"){
    
    $column_width_px = TRUE;
    $checkbox_width = "style='width: 10px;'";
    $column_width_class = "column-width";
    //calculate total xml attr width
    foreach ($listing_field as $field) {
        if(isset($all_field[$field]["column_width"]) AND $all_field[$field]["column_width"] > 0 ){
            $total_attr_width += $all_field[$field]["column_width"];
            $field_counter++;
        }
    }
    //calculate table width
    $per_column_width = $this->config->item('#MAX_COLUMN_WIDTH');
    $total_listing_field = count($listing_field) - $field_counter;
    $total_table_width = ($total_listing_field * $per_column_width) + $total_attr_width + 10;
    $table_width = "style='width: ".$total_table_width."px'";
}
    
$checkboxwidthInPercent="4";

?>
    <table <?php echo $table_width;?> class="table table-striped dataTable table-bordered table-hover m-datatable__table" id="table-listing-items">
    <?php if(!$no_permission):?>
        <thead class="m-datatable__head">
            <tr role="row" class="m-datatable__row">
                <th data-field="RecordID" class="m-datatable__cell--center m-datatable__cell m-datatable__cell--check">
                	<span <?php echo $checkbox_width;?> >
                		<label class="m-checkbox m-checkbox--single m-checkbox--all m-checkbox--solid m-checkbox--brand">
                			<input type="checkbox" id="selectall" />
                			<span></span>
                		</label>
                	</span>
                </th>
                <?php

                if (is_array($listing_field) && count($listing_field) > 0) {
                    $filedCount = count($listing_field);
                    $fieldWidth = (int) ((100-$checkboxwidthInPercent) / $filedCount);
                }
               
       
                foreach ($listing_field as $field) {     
                    
                     //field attributes
                     $prependTableName = (string)$all_field[$field]["prepend_table_name"];
                    
                     $fieldWidthPercentage = "width='$fieldWidth%'";
                     //reset xml attr width
                     $get_xml_attr_width = "";
 
                     //check column width px set or not                   
                     if($column_width_px){
                         $fieldWidthPercentage = "";
                         
                         //check max width from xml attribute
                         if(isset($all_field[$field]["column_width"]) AND $all_field[$field]["column_width"] > 0 ){
                             $get_xml_attr_width = "style='width: ".$all_field[$field]["column_width"]."'";
                         }
                         
                     }
                     
                     $cur_sorting = "";
                     $next_sorting ="";
                     if( isset($ordering_fields[$field]) && !empty($ordering_fields[$field])){
                         $cur_sorting = $ordering_fields[$field];
                         if($cur_sorting == "asc"){
                             $next_sorting = 'desc';
                         }else{
                             $next_sorting = 'asc';
                         }
                     }else{
                         $next_sorting = 'desc';
                     }

                     
                   
                    ?>
                           <th class="m-datatable__cell m-datatable__cell--sort table-header-for-sort test-table" data-sort="<?php echo @$cur_sorting ?>" rowspan="1" data-next="<?php echo @$next_sorting ?>" colspan="1" data-field="<?php echo ($field); ?>" data-title="<?php echo ($field); ?>" >
                            <span style="width: 150px;">
                            	<?php 
                            	$fieldName = '_'.strtoupper($field);
                            	if(!empty($prependTableName)){
                            	    $fieldName = '_'.strtoupper($prependTableName.'_'.$field);
                            	}
                            	echo dashboard_lang($fieldName);  
                            	if( !empty( $cur_sorting ) ){ 
                                	if( $cur_sorting == 'asc' ) { 
                                		echo '<i class="la la-arrow-up"></i>';
                                	 } else { 
                                		echo '<i class="la la-arrow-down"></i>'; 
                                	 }
                               	 } ?>
                            </span>
                            
                        </th>
                <?php } ?>
            </tr>
        </thead>
    <?php endif;?>
    <tbody class="m-datatable__body">
    <?php 
    
    $fileOrImage = array();
    $dateTooltip = array();
    $i = -1;
    foreach ($all_field as $single_field) {
        if ($single_field['type'] == "file" || $single_field['type'] == "image") {
            $fileOrImage[] = (string) $single_field['name'];
        }
        if ($single_field['type'] == "datetime") {
            $dateTooltip[] = (string) $single_field['name'];
        }
    }
    
    if (is_array($list) && count($list) > 0) {
    	
        foreach ($list as $value) {
        	
            if(!$no_permission):
                ?>
                <?php $i++; ?>
                <tr data-row="<?php echo $i; ?>" class="m-datatable__row <?php echo ($i % 2 ? 'm-datatable__row--even' : ''); ?>" >
                    <td data-href="" class="m-datatable__cell--center m-datatable__cell m-datatable__cell--check" data-field="RecordID">
                    	<span <?php echo $checkbox_width;?> >
                    		<label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand">
                    			<input type="checkbox" value="<?php echo $value->{$primary_key}; ?>" class="check_uncheck_all" name="check_all" id="check_all" />
                    			<span></span>
                    		</label>
                    	</span>
                    </td>
                    <?php
                    foreach ($listing_field as $field) {  
                        
                        $fieldWidthPercentage = "width='$fieldWidth%'";  
                        //reset xml attr width
                        $get_xml_attr_width = "";
                        //check column width px set or not                   
                        if($column_width_px){
                            $fieldWidthPercentage = "";
                            
                            //check max width from xml attribute
                            if(isset($all_field[$field]["column_width"]) AND $all_field[$field]["column_width"] > 0 ){
                                $get_xml_attr_width = "style='width: ".$all_field[$field]["column_width"]."'";
                            }
                            
                        }                        
                        
                     ?>
                            <td <?php echo $fieldWidthPercentage." ".$get_xml_attr_width; ?> data-href='<?php echo $site_url . "dbtables/$table_name/trash_detail" . "/" . $value->{$primary_key}; ?>' class="m-datatable__cell--sorted m-datatable__cell field-type-<?php echo $all_field [$field] ['type']." ".$column_width_class; ?>" >
                                <span rel="tooltip" data-toggle="m-tooltip" data-trigger="hover" data-placement="bottom" data-html="true" class="data_listing" style="width: 156px;"
                                      title='<?php
                                      if( $all_field [$field] ['type'] == 'file' AND !empty($value->{$field})){
                                          echo dashboard_lang("_CLICK_HERE_TO_DOWNLOAD_THIS_FILE");
                                      }
                                      else{
                                          if(array_key_exists($field, $all_field)){
                                              echo dashboard_show_field($all_field [$field], $value->{$field}, $tooltip= 1, $value->id, $table_name);
                                          }
                                      }
                                      ?>'> 
                                      <?php
	                                    if(array_key_exists($field, $all_field)){
	                                        echo dashboard_show_field($all_field [$field], $value->{$field}, $tooltip= 0, $value->id, $table_name);
	                                    }
                                    ?>
                                </span>
                            </td>    
                        <?php 
                    } ?>
                </tr>
            <?php endif;?>
        <?php
        }
    } else {     ?>
        <tr class="m-datatable__row">
            <td data-href="" colspan="<?php echo count($listing_field) + 1; ?>">
                <div class="alert alert-block alert-info fade show m-alert m-alert--air" role="alert">
                    <button type="button" class="close" style="padding-top:0.2rem;" data-dismiss="alert" aria-label="Close"></button>
                    <?php echo dashboard_lang("_NO_DATA_FOUND"); ?>
                </div>
            </td>
        </tr>
    <?php
    }
    if($no_permission):
        ?>
        <tr class="m-datatable__row">
            <td data-href="" colspan="<?php echo count($listing_field) + 1; ?>">
                <div class="alert alert-block alert-info fade show m-alert m-alert--air" role="alert">
                    <button type="button" class="close" style="padding-top:0.2rem;" data-dismiss="alert" aria-label="Close"></button>
                    <?php echo dashboard_lang("_NO_DATA_FOUND"); ?>
                </div>
            </td>
        </tr>
    <?php endif;?>
    </tbody>
    </table>
