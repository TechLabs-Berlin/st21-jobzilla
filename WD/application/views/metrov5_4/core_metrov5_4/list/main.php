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
    $total_table_width = ($total_listing_field * (int)$per_column_width) + $total_attr_width + 10;
    $table_width = "style='width: ".$total_table_width."px'";
}
$statusLists = array();
if (in_array("status_".$table_name."_id", $listing_field) ) {
    $statusLists = getCustomStatusListsByName ( "status_".$table_name, "status" ); 
}


$checkboxwidthInPercent="4";
$maxDefaultColWidth = $this->config->item("#CORE_LISTVIEW_COLUMN_WIDTH_MAX_DEFAULT");
$totalTableWidth = getTotalTableWidth( $listing_field, $table_name );

?>
    <table <?php echo $table_width;?> class="m-datatable__table" id="table-listing-items" style="display: block; height: auto; <?php echo $totalTableWidth; ?>" >
    <?php if(!$no_permission):?>
        <thead class="m-datatable__head custom-thead">
            <tr role="row" class="m-datatable__row">
                <th data-field="RecordID" class="m-datatable__cell--center m-datatable__cell m-datatable__cell--check">
                	<span style="width: 40px;" >
                		<label class="m-checkbox m-checkbox--single m-checkbox--all m-checkbox--solid m-checkbox--brand">
                			<input type="checkbox" id="selectall" />
                			<span class="thead_check_view"></span>
                		</label>
                	</span>
                </th>
                <?php
                
                if (is_array($listing_field) && count($listing_field) > 0) {
                    $filedCount = count($listing_field);
                    $fieldWidth = (int) ((100-$checkboxwidthInPercent) / $filedCount);
                }

                //var_dump($ordering_fields);

                foreach ($listing_field as $field) {
                    
                    //field attributes
                    $prependTableName = (string) @$all_field[$field]["prepend_table_name"];

                    $fieldType = (string) @$all_field[$field]["type"];

                    // column specific Width from xml
                    //$specificColWidth = (string) @$all_field[$field]["column_width"];
                    $specificColWidth = getTableColumnWidth ( $table_name, $field);

                    if ( strlen($specificColWidth) > 0 ) {
                        $defaultColWidth = $specificColWidth;
                    } else {
                        $defaultColWidth = $maxDefaultColWidth;
                    }
                    
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
                    
                    if($fieldType == 'eav'){
                        $next_sorting = "";
                        $cur_sorting = "";
                    }
                   

                    ?>
                        <th class="m-datatable__cell m-datatable__cell--sort table-header-for-sort test-table" data-sort="<?php echo @$cur_sorting ?>" rowspan="1" data-next="<?php echo @$next_sorting ?>" colspan="1" data-field="<?php echo ($field); ?>" data-title="<?php echo ($field); ?>" >
                            <span style="width: <?php echo $defaultColWidth; ?>;">
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
                            
                            <?php
                                $totalValueCount = @$all_field[$field]["total_value_count"];
                                $showCurrencySymbol = @$all_field[$field]["show_currency_symbol"];
                                if( $totalValueCount ):?>
                                <span class="toggle-view-sum-all-data m--pull-right text-right m-badge" style="display: none !important;">
                                    <?php echo getTotalColumnValue( $totalValueCount, $fieldType, $all_listing_data, $field, $showCurrencySymbol);
                                    ?>
                                </span>
                             <?php endif;?>
                             </span>
                            
                        </th>
                <?php } ?>
            </tr>
        </thead>
    <?php endif;?>

    <tbody class="m-datatable__body custom-tbody m--font-bold">
    <?php
    echo form_open('', array('method'=>'post', 'enctype'=>'multipart/form-data' , 'role'=> 'form', 'id'=>'multi_select_form')) ; 
    echo "<tr class='m-datatable__row m-datatable__row--hover'>
            <td class='m-datatable__cell m-datatable__cell--check' style='border-top:0;'><span style='width:40px'></span></td>";
    $multi_select_array = $this->session->userdata('multi_select_array');
    
    $prefix = $this->config->item("prefix");
    $ms_track = 0;    
    
    foreach ($listing_field as $field) {
    
        $allow_filter = $this->config->item('#ALLOW_FILTER_PER_COLUMN');
        if ($allow_filter) {
            $fieldType = @$all_field[$field]["type"];
            
            // previous code for use operator
           // $useOperator = (string)@$all_field[$field]["use_operator"];
           $useOperator='';
            if($fieldType=='number' || $fieldType=='datetime' || $fieldType=='money'){
                $useOperator = 1;
            }

            $session_value = $this->session->userdata($table_name . "_" . $field);
            
            if (strlen($session_value) > 0) {
    
                $display_property = 'inline';
                $width = "90%";
            } else {
                $width = "100%";
                $display_property = 'none';
            }    
            
            if ($fieldType == 'lookup') { 
                $lookup = "data-lookup='1' ref-table='" . @$all_field[$field]["ref_table"] . "' data-key='" . @$all_field[$field]["key"] . "'  data-value='" . @$all_field[$field]["value"] . "'";
                
            }elseif($fieldType == 'eav'){
            	
            	$attribute_table_name = (!empty($all_field[$field]['ref_attribute_table_name'])) ? @$table_name."_".@$all_field[$field]["name"]."_eav_view" : '';
            	
            	$eav_table_name = @$table_name."_".@$all_field[$field]["name"]."_eav_view";
            	$eav_key = "eav_object_id";
            	$eav_value = "eav_object_data, eav_object_attribute";
            	
            	$lookup = "data-lookup='1' attr-table='".$attribute_table_name."' ref-table='" . @$eav_table_name . "' data-key='" . @$eav_key . "'  data-value='" . @$eav_value . "'";
            	//echo $lookup; die("hi");
            } else {    
                $lookup = "data-lookup='0'";
    
            }
    
            $select_options_data = '';
            $range_suffix = '_498756425end';
            
            $options_range_end='';
    
            if ($fieldType == 'select' || $fieldType == 'radio') {
    
                $options_array = array();
                $select_data = render_select_options ( $all_field ,$field);
                $options = $select_data['options'];
                $select = $select_data['select'];
          
    
            }else {
               
                $select = "data-select='0' data-select-options='$select_options_data'";
                $selected_value = explode(",",$this->session->userdata($table_name . "_" . $field));
                $options = '';
                foreach ( $selected_value as $value ) {    
                    $options .= "<option value='$value' selected> $value</option>";    
                }
                
            }

            if ( $fieldType == 'single_checkbox' ) {
                $selected_value = explode(",",$this->session->userdata($table_name . "_" . $field));
                $options = '';
                foreach ( $selected_value as $value ) {  
                    if(!empty($value)){
                        $options .= "<option value='$value' selected> ".dashboard_lang('_YES')." </option>";   
                    }else{
                        $options .= "<option value='$value' selected> ".dashboard_lang('_NO')." </option>";  
                    }  
                }

            }
    
            if ( $fieldType == 'datetime' ) {                   
                $saved_data_from = render_saved_data ( $field );
                $saved_data_to = render_saved_data ( $field.$range_suffix );
                $saved_data_from = @$saved_data_from[0];
                $saved_data_to = @$saved_data_to[0];
            }
    
            if ( $fieldType == 'number' ) {
                $saved_data_from = render_saved_data ( $field );
                $saved_data_to = render_saved_data ( $field.$range_suffix );
                $saved_data_from = @$saved_data_from[0];
                $saved_data_to = @$saved_data_to[0];
                
                $select = "data-select='0' data-select-options='$select_options_data'";
                $selected_value = explode(",", $this->session->userdata($table_name . "_" . $field.$range_suffix));

                $options_range_end = '';
                if ( is_array($saved_data_to) && sizeof($saved_data_to) > 0 ) {
                    foreach ( $selected_value as $value ) {
                        if ( strlen($value) > 0 ) {
                            $options_range_end .= "<option value='$value' selected> $value</option>";
                        }
                        
                    }
                }
            }
    
            if ($fieldType == 'money') {
               $options = render_money_options ( $field ) ;
               
               $select = "data-select='0' data-select-options='$select_options_data'";
               $selected_value = explode(",", $this->session->userdata($table_name . "_" . $field.$range_suffix));
               $saved_data_to = render_saved_data ( $field.$range_suffix );
               $saved_data_to = @$saved_data_to[0];
               
               $options_range_end = '';
               if ( is_array($saved_data_to) && sizeof($saved_data_to) > 0 ) {
                   foreach ( $selected_value as $value ) {
                       if ( strlen($value) > 0 ) {
                           $options_range_end .= "<option value='$value' selected> $value</option>";
                       }
               
                   }
               }
            }
            
            $operatorsDropDown = '';
            $operatorsDropdownParentClass = "";
            if(!empty($useOperator) && $useOperator == '1'){
                $operatorsDropDown = renderOperatorsDropDown ( $field );
                $selectedOperator = getSelectedOperator($field);
                $operatorsDropdownParentClass = "operator-holder"; 
                if(!empty($session_value)){
                    $operatorsDropdownParentClass .= " disableInputBox";
                }
            }         
         
        // column specific Width from xml
        $specificColWidth = (string) @$all_field[$field]["column_width"];
        $specificColWidth = getTableColumnWidth ( $table_name, $field);

        if ( strlen($specificColWidth) > 0 ) {
            $defaultColWidth = $specificColWidth;
        } else {
            $defaultColWidth = $maxDefaultColWidth;
        }

        echo "<td class='m-datatable__cell search-in-column ".$operatorsDropdownParentClass."'>                        
            <span style='width:{$defaultColWidth}; float:left;'>";
            echo $operatorsDropDown;
            if($fieldType == 'datetime'):
                echo "<span>";
                echo "<input type='".$fieldType."' class='datetimeClass dateFiltering search_in from-date' style='display: inline; height:25px; width:100px;'  value='".$saved_data_from."' data-table-name='" . $table_name . "' data-field-name='" . $field . "' >";
            else:                    
                echo "<select input_type='".$fieldType."' multiple='multiple' style='display: inline;height:20px; width:40px;' type='text' name='' $lookup $select class='tokenize listing_field_search' data-table-name='" . $table_name . "' data-field-name='" . $field . "'>
                $options
                </select>";    
            endif;
             
            if(@$selectedOperator =='><' ):
                if($fieldType == 'datetime'):
                    echo "<input type='".$fieldType."' class='datetimeClass dateFiltering search_in to-date' style='display: inline; height:25px; width:100px;'  value='".$saved_data_to."' data-table-name='" . $table_name . "' data-field-name='" . $field.$range_suffix . "' >";
                    echo "</span>";
                else:                    
                    echo "<select input_type='".$fieldType."' multiple='multiple' style='display: inline;height:20px; width:40px;' type='text' name='' $lookup $select class='tokenize listing_field_search' data-table-name='" . $table_name . "' data-field-name='" . $field.$range_suffix . "'> $options_range_end
                    </select>";
                endif;
                
                // $selectedOperator='';
            endif;  

            if(@$selectedOperator =='><' ):   
                echo "<i class='fa fa-undo pull-left filter-icon reset-rang-input-data'></i></span></td>";
            else:   
                echo "<i class='fa fa-search pull-left filter-icon'></i></span></td>";
            endif;
    
        }else {
    
            echo "<td> </td>";
        }
    }
    foreach ($listing_field as $field) {
    
        if(array_key_exists($field, $all_field)){
    
            if ($all_field[$field]['multi_select'][0] == 1) {
    
                $ms_track++;
                $ref_table = @$all_field[$field]["ref_table"];
                $ref_table_col_name = @$all_field[$field]["value"];
                if ($ref_table) {
                    $field_name = $field;
                } else {
                    $field_name = "`" . $prefix . $table_name . "`.`" . $field . "`";
                }
    
                $multi_select_values = listing_multi_select_dropdown($table_name, $field_name, $ref_table, $ref_table_col_name, $multi_select_array);
                
                // column specific Width from xml
                $specificColWidth = (string) @$all_field[$field]["column_width"];
                if ( strlen($specificColWidth) > 0 ) {
                    $defaultColWidth = $specificColWidth;
                } else {
                    $defaultColWidth = $maxDefaultColWidth;
                }

                    ?>
                    <td class="m-datatable__cell" style='border-top:0;'>
                        <span style='width:<?php echo $defaultColWidth; ?>;'>
                        <?php if ($ms_track == 1) { ?>
                            <!-- generate only one time for multi-select track -->
                            <input type="hidden" name="multi_select_track" value="1" >
                        <?php } ?>
                        <select class="multi_select form-control" name="<?php echo $field; ?>" id="<?php echo $field . "_multi_select" ?>" >
                            <option value="<?php echo $this->config->item('please_select'); ?>"  ><?php echo dashboard_lang("_SELECT_FROM_DROPDOWN") ?></option>
                            <?php
                            foreach ($multi_select_values as $value) {
    
                                $option_value = @$value[$field];
    
                                if ($ref_table) {
                                    $option_value = $value["name"];
                                }
    
                                if ($option_value) {
                                    ?>
    
                                    <option value="<?php echo $option_value; ?>"  <?php
                                    if ($option_value == @$multi_select_array[$field]) {
                                        echo "selected";
                                    }
                                    ?> ><?php echo $option_value; ?></option>
    
                                <?php
                                }
                            }
                            ?>
                        </select>
                        </span>
                    </td>
                <?php
            }
        }    
    }
    echo "</tr>";
    echo form_close();
    
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
                    	<span style="width:40px;" >
                        	<label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand">
                        		<input type="checkbox" value="<?php echo $value->{$primary_key}; ?>" class="check_uncheck_all" name="check_all" id="check_all" />
                        		<span class="check_view"></span>
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
                        
                        $ex_css_class = "";
                        
                        //add aditional class when mody or number field
                        if( @$all_field[$field]["type"] == "number" OR @$all_field[$field]["type"] == "money"){
                            $ex_css_class = "text-right pull-right";
                            
                        }
						
						//generate detail view url
                        $detailViewURL = $site_url . $edit_path . "/" . @$value->$primary_key;
                        if(isset($report) && $report == true){
                            $detailViewURL = "";
                        }
                            
                        // column specific Width from xml
                        $specificColWidth = (string) @$all_field[$field]["column_width"];
                        $specificColWidth = getTableColumnWidth ( $table_name, $field);
                        if ( strlen($specificColWidth) > 0 ) {
                            $defaultColWidth = $specificColWidth;
                        } else {
                            $defaultColWidth = $maxDefaultColWidth;
                        }

                     ?>
                            <td data-href='<?php echo $detailViewURL; ?>' id="ident<?php echo @$value->{$primary_key}; ?>" class="m-datatable__cell--sorted m-datatable__cell field-type-<?php echo @$all_field [$field] ['type'].' '.$field; ?> ">
                                <span style="width:<?php echo $defaultColWidth; ?>;" class="cwrapper">
                                   <?php                                        
                                    if(array_key_exists($field, $all_field)){
                                        if ( $field == 'status_'.$table_name.'_id' ) {
                                            if ( isset($statusLists[$value->{$field}] )) {

                                                echo '<span class="m-badge m-badge--wide" style="color: '.$statusLists[$value->{$field}]["text_color"].'; background-color: '.$statusLists[$value->{$field}]["background_color"].';">'.dashboard_lang ( strtoupper($value->{$field}) ).'</span>';
                                            }else {

                                            }
                                        }else{
                                            echo dashboard_show_field($all_field [$field], $value->{$field}, $tooltip= 0, $value->id, $table_name);
                                        }
                                     
                                    }
                                    ?>
                                    <?php 
                                    $listing_max_text = $this->config->item("#LISTING_FIELD_ELLIPSIS_LENGTH");
                                    if(isset($value->$field) && $listing_max_text < strlen($value->$field)):?>
                                    <span class="ctooltip">
                                     <?php
                                      if( $all_field [$field] ['type'] == 'file' AND !empty($value->{$field})){
                                          echo dashboard_lang("_CLICK_HERE_TO_DOWNLOAD_THIS_FILE");
                                      } else if($all_field [$field] ['type'] != 'image'){
                                          if(array_key_exists($field, $all_field)){
                                              echo dashboard_show_field($all_field [$field], $value->{$field}, $tooltip= 1, $value->id, $table_name);
                                          }
                                      }
                                      ?>
                                    </span>
                                    <?php endif;?>
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
                    <button type="button" class="close" style="padding-top:0.2rem;" data-dismiss="alert"></button>
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
                    <button type="button" class="close" style="padding-top:0.2rem;" data-dismiss="alert"></button>
                    <?php echo dashboard_lang("_NO_DATA_FOUND"); ?>
                </div>
            </td>
        </tr>
    <?php endif;?>
    </tbody>
    </table>
   
