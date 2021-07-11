<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php echo  form_open('', array('method'=>'post', 'enctype'=>'multipart/form-data' , 'role'=> 'form' , 'id'=>'per_page_form', 'name'=>'per_page_form' ));  ?>

  <div class="m-datatable m-datatable--default m--pull-left">
  	<div class="m-datatable__pager m--margin-top-10">
  		<div class="m-datatable__pager-info" style="margin-top: -8px;">
  			<select name="per_page" id="per_page"
                    class="selectpicker m-datatable__pager-size form-control"  title="Select page size" data-width="70px" data-selected="10" tabindex="-98" style="display: none !important;">
                <?php foreach ($perPegeArray as $key => $value) { ?>
                    <option value="<?php echo $key; ?>"
                        <?php if ($key == $per_page_show) echo "selected"; ?>>
                        <?php echo $value; ?>
                    </option>
                <?php } ?>
            </select>
          <span class="m-datatable__pager-detail m--font-bolder">  
            <?php
            if($no_permission){
                $total_items_count = 0;
            }
        
            if ($total_items_count == 1) {
        
                $listing_per_page = dashboard_lang('_PER_PAGE_SINGLE_RECORD');
        
            } else if($total_items_count > 1 ){
        
                $listing_per_page = dashboard_lang('_PER_PAGE_MULTI_RECORDS');
            }
            else{
                
                $listing_per_page = dashboard_lang('_PER_PAGE_SINGLE_RECORD');
            }
        
            printf($listing_per_page, $total_items_count);
        
            ?>
    		</span>
  		</div>
  		
  		
  	</div>
  </div>
  
  
<?php echo form_close(); ?>
