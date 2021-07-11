<?php 
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<span class="show_fields m--align-left" <?php echo $modal_style; ?> >
    <?php if(!empty( $all_listing_data )): ?>
	<a class="m--align-left margin-left m--font-bold toggle-show-listing-sum" href="javascript:void(0)">
    <span>&Sigma;</span><?php if(!empty( $all_listing_data ))echo dashboard_lang('_SUM'); ?>
    </a> 
    <?php endif;?>
    <a class="m--align-left margin-left m--font-bold" href="#" type="text"
		data-toggle="modal" data-target="#show_checkbok_field">
           <?php echo dashboard_lang('_SHOW_FIELDS_WITH_ORDER'); ?>
    </a>
	<div id="show_checkbok_field" class="modal fade" role="dialog"
		aria-hidden="true" aria-labelledby="myModalLabel">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="myModalLabel">
                        <?php echo dashboard_lang('_SELECT_FIELDS_TO_SHOW_IN_LIST'); ?>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
						<span aria-hidden="true">&times;</span><span class="sr-only"><?php echo dashboard_lang('_CLOSE'); ?></span>
					</button>
				</div>
				<div class="modal-body dashboard_modal_body">
					<div class="list_wrapper">
						<label><?php echo dashboard_lang('_LIST_OF_AVAILABLE_FIELDS'); ?></label>
						<ul id="sortable1" class="droptrue">
                                <div class="search_wrapper" style="display: block; position: relative;">
                                    <input type="text" class="form-control" style="width: 100%"  placeholder="<?=dashboard_lang('_SEARCH_TEXT'); ?>" />
                                    <button type="submit" class="btn btn-accent m-btn--icon   m--align-right">
                                        <i class="fa fa-close" style="line-height:16px;"></i>
                                    </button>
                                
                                </div>
                            <?php

							//call a helper function to insert/update listing_fields table

							$listing_fields_data = implode(",",$listing_field);
							$ordering_fields_data = implode(",",$ordering_fields);
							
                            $ordering_fields_data = serialize($ordering_fields);
                            
							updateListingFields($listing_fields_data, $ordering_fields_data, $table_name);

                            if (isset($all_field) && count($all_field) > 0) :
                                
                                foreach ($all_field as $field) {
                                    if ($field['type'] != "hidden") {
                                        $fieldName = (string) @$field['name'];
                                        $fieldType = (string) @$field['type'];
                                        $showShortToggoleClass = 'sort_toggling';
                                        if( $fieldType == 'eav'){
                                            $showShortToggoleClass = '';
                                        }
                                       
                                        if (! in_array($fieldName, $listing_field)) :                                            
                                            echo '<li class="ui-state-default" data-sort="" data-value="'. $fieldName .'">';
                                            $prependTableName = (string) @$field['prepend_table_name'];
                                            $translatedFieldName = '_'.strtoupper($fieldName);
                                            if(!empty($prependTableName)){
                                                $translatedFieldName = '_'.strtoupper($prependTableName.'_'.$fieldName);
                                            }
                                            echo dashboard_lang($translatedFieldName);
                                            $up_down_class = "fa-circle";
                                            echo "<a href='#' class='arrow down pull-right ".$showShortToggoleClass."'><i class='fa ".$up_down_class." aria-hidden='true' style='color:#D3D3D3;'></i></a></li>";											                                        
										endif;                                                                             
                                    }
                                }  
                            endif;
                            ?>
                        </ul>
					</div>

					<div id="div_show_field" class="list_wrapper">
						<label><?php echo dashboard_lang('_SELECTED_FIELDS'); ?></label>
						<ul id="sortable2" class="dropfalse">
                                <div class="search_wrapper" style="display: block; position: relative;">
                                    <input type="text" class="form-control" style="width: 100%"  placeholder="<?=dashboard_lang('_SEARCH_TEXT'); ?>" />
                                    <button type="submit" class="btn btn-accent m-btn--icon   m--align-right">
                                        <i class="fa fa-close" style="line-height:16px;"></i>
                                    </button>
                                
                                </div>
                            <?php 
                            if (isset($all_field) && count($all_field) > 0) :
                                foreach ($listing_field as $field) :
                                    $fieldType =  @$all_field[$field]['type'];
                                    $showShortToggoleClass = 'sort_toggling';
                                    if( $fieldType == 'eav'){
                                        $showShortToggoleClass = '';
                                    }
                                    $up_down_class = "fa-circle";
                                    $lightGrayColor = "style='color:#D3D3D3'";
                                    $sort_value = "";
                                    if(isset($ordering_fields[$field]) && !empty($ordering_fields[$field])){
                                        $sort_value = $ordering_fields[$field];
                                        $lightGrayColor ="";
                                        $up_down_class = ($sort_value == "asc") ? 'fa-arrow-up' : 'fa-arrow-down';
                                    }
                                ?>
                                    <li class="ui-state-highlight" data-sort="<?php echo $sort_value;?>" data-value="<?php echo $field; ?>">
                                        <?php                                        
                                        $prependTableName = (string) @$all_field[$field]['prepend_table_name'];
                                        $translatedFieldName = '_'.strtoupper($field);
                                        if(!empty($prependTableName)){
                                            $translatedFieldName = '_'.strtoupper($prependTableName.'_'.$field);
                                        }
                                        echo dashboard_lang($translatedFieldName);  
                                        ?>
                                        
                                        <a href="#" class="arrow down pull-right  <?php echo $showShortToggoleClass; ?> "><i  class="fa <?php echo $up_down_class; ?>" aria-hidden="true" <?php echo $lightGrayColor; ?> ></i></a>
                                    </li>
                                    
                                    <?php
                                endforeach;

                            endif;
                            ?>
                        </ul>
					</div>

					<div style="clear: both;"></div>
					<br />
                    
                    <div class="row">
                        <div class="col-5">
                            &nbsp;
                        </div>
                        <div class="col-6">
                            <span class="m--margin-left-5">
                                <?php echo dashboard_lang("_CLICK"); ?> <i class="fa fa-circle" aria-hidden="true" style="color:#D3D3D3"></i>
                                <?php echo dashboard_lang("_TO_SORT"); ?> <i class="fa fa-arrow-up" aria-hidden="true" style="color: #2a8df0 !important;"></i> 
                                <i class="fa fa-arrow-down" aria-hidden="true" style="color: #2a8df0 !important;"></i>
                            </span>
                        </div>
                    </div>            


				</div>
				<div class="modal-footer">
					<button type="submit" name="show_field_add" id="show_field_add"
						class="btn btn-accent m-btn  ">
                                <?php echo dashboard_lang('_SAVE'); ?>
                    </button>
					<button type="submit" name="show_field_reset" id="show_field_reset"
						class="btn btn-default m-btn  ">
                                <?php echo dashboard_lang('_RESET'); ?>
                    </button>
				</div>
			</div>
		</div>
	</div>
</span> 
<div class="left m--align-left m--pull-right m--margin-right-15">
    <button id="left-button" class="btn btn-accent m-btn--icon"><i class="fa fa-angle-left"></i> <?php echo dashboard_lang("_SCROLL_LEFT"); ?></button>
    <button id="right-button" class="btn btn-accent m-btn--icon"><?php echo dashboard_lang("_SCROLL_RIGHT"); ?> <i class="fa fa-angle-right"></i></button>
</div>
<script>
jQuery(document).ready(function() {
    jQuery("#right-button").click(function() {
        event.preventDefault();
        jQuery("#table-listing-items").animate(
            {
                scrollLeft: "+=300px"
            },
            "slow"
        );
    });

    jQuery("#left-button").click(function() {
        event.preventDefault();
        jQuery("#table-listing-items").animate(
            {
                scrollLeft: "-=300px"
            },
            "slow"
        );
    });
});
</script>