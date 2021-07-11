<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/listing.css" rel="stylesheet">

<div class="portlet light bordered">
	    <div class="portlet-title">
	    	<div class="caption font-dark">
		        <span class="caption-subject bold uppercase"><?php echo dashboard_lang('_ALL_DISCUSSIONS'); ?></span>
		        
			</div>
	    </div>
	    <?php 
	        	$i = isset($start)?$start:0;
	        	$j = isset($start)?$start:1;
	        	$limit = $i+$per_page;
	        	$per_page_show = $this->session->userdata('per_page');
	      ?>
	    <div class="portlet-body" style="">
	    	<div class="row table-toolbar">
	    		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	    			<div class="button_row pull-right">
	    				<a href='<?php echo base_url();?>dbtables/message/add'> <button class='btn btn-primary pull-left margin-left'><?php echo dashboard_lang('_ADD_NEW_MESSAGE'); ?></button> </a>
	    			</div>
	    		</div>
	    	</div>
	    <div class="row padding_bottom_15px">
	    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
	    		
	              <?php echo form_open('dbtables/message/listing', array('class' => 'form-inline', 'id' => 'per_page_form'));?>
                            <label> <select name="per_page" id="per_page"
                                            class="form-control input-sm">
                                                <?php foreach ($perPegeArray as $key => $value) { ?>
                                        <option value="<?php echo $key; ?>"
                                                <?php if ($key == $per_page_show) echo "selected"; ?>>
                                                    <?php echo $value; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </label>
                            <?php

							if ($total_message == 1) {

								$listing_per_page = dashboard_lang('_PER_PAGE_SINGLE_RECORD');
							} else if($total_message > 1 ){

								$listing_per_page = dashboard_lang('_PER_PAGE_MULTI_RECORDS');
							}
							else{

							}
							printf($listing_per_page, $total_message);

                            ?>

                        </form>
                    
                 </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    	<div class="search_box pull-right margin-left">
	                     <?php if(strlen($this->session->userdata('search_str')) > 0): ?>
			      		<?php echo form_open('dbtables/message/listing', array('class' => 'form-inline pull-right message_search_form'));?>
			      		<input type="hidden" name="reset" value="yes">
			          	&nbsp;<button class="btn green" type="submit"> <?php echo dashboard_lang("_RESET")?> </button>
			           </form> 
			           <?php endif;?>
			      				<?php echo form_open('dbtables/message/listing', array('class' => 'form-inline pull-right message_search_form search_text_form'));?>
			        		<div class="input-group">
			       
			          			<input class="form-control input-sm typeahead" id="searchtext" type="text" name="message_search" placeholder="<?php echo dashboard_lang('_SEARCH');?>" <?php if(strlen($this->session->userdata('search_str')) > 0) echo 'value="'.$this->session->userdata('search_str').'"'; ?> class="form-control">
					          <span class="input-group-btn pull-left">
					          <button class="btn green" type="submit"> <i class="fa fa-search"></i> </button>
					          
					          </span> 
					          </div>
					      </form>
					      </div>
                    </div>
	      </div>
	      
	      <div class="row padding_bottom_15px">
	      	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	      		<div class="table-responsive">
			      <table width="100%" class="table table-striped table-advance table-hover inbox-list-table">
			        <thead>
			         <!--  <tr>
			            <th colspan="3"> <div class="checker">&nbsp;</div>
			
			            </th>
			            <th colspan="3" class="pagination-control"> <span class="pagination-info"> <?php //echo ($i+1)." -". $limit; ?> of <?php //echo sizeof($all_message_list); ?> </span>  </th> 
			          </tr>-->
			        </thead>
			        <tbody>
			        <?php
			        if(count($all_message_list) > 0):
				        for ($i; $i < $limit; $i++):
				       		if($i == $total_message){
				       			break;
				        	}

							if($all_message_list[$i]['message_entity'] == "discussions"){
								$url = base_url()."dbtables/message/details/".$all_message_list[$i]['id'];
							}else{
							    $msg_tab_position = $this->config->item("#MESSAGE_TAB_POSITION");
								$url = base_url()."dbtables/".$all_message_list[$i]['message_entity']."/edit/".$all_message_list[$i]['entity_id']."/".$all_message_list[$i]['id']."/".$msg_tab_position."?s=l";
							}
				        ?>
				          <tr data-messageid="1" class="<?php  if(@$all_message_list['notification_exists']) { echo dashboard_lang('_UNREAD'); } ?> ">
				            
				            <td class="view-message hidden-xs"><a href="<?php echo $url; ?>"><?php echo $all_message_list[$i]['user_name']; ?></a> </td>
				            <td class="view-message "> <a href="<?php echo $url; ?>"><?php $lenght = $this->config->item('message_title_length'); if(strlen($all_message_list[$i]['msg_title']) > $lenght ){echo substr($all_message_list[$i]['msg_title'],0,$lenght)." ..."; } else{ echo $all_message_list[$i]['msg_title']; } ?></a> </td>
				            <td class="view-message inbox-small-cells"><a href="#"> <?php if ( @$all_message_list['files_exists']) { ?><i class="fa fa-paperclip"></i> <?php } ?> &nbsp;</a></td>
				            <td class="view-message text-right"><?php echo format_date_time ( $all_message_list[$i]['last_conversion_time']); ?></td>
				          </tr>
				        <?php @$counter++; endfor; ?>
				        <?php else: ?>
				        <tr>
				        <td><p class="alert alert-success"><?php echo dashboard_lang("_YOUR_SEARCH_RESULT_IS_EMPTY")?></p></td>
				        </tr>
			        <?php endif;?>
			        </tbody>
			      </table>
			      </div>
	      		</div>
	      </div>
	    </div>
		<!--strat pagination row-->
		   <div class="row">
	                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	                        <div class="dataTables_paginate paging_simple_numbers inbox-pagination"
	                             id="dataTables-example_paginate">
	                                 <?php echo @$paging; ?>
	                        </div>
	                    </div>
	                </div>
	                <!--End pagination row-->
	 
  </div>


<script>

function dosearch(){
    jQuery('.message_search_form').submit();
}
$(document).ready(function(){



	$("#per_page").change(function(){
		$("#per_page_form").submit();
	});

    /*
     * typeahead js for searchbox
     */

    var url = "<?php echo base_url();?>dbtables/message/get_search_words";
    var search_auto_suggest_limit = "<?php echo $this->config->item('search_auto_suggest_limit') ; ?>";

    var search_tags = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        limit: search_auto_suggest_limit,
        prefetch: url+'/ab',
        remote: url+'/%QUERY'
    });

    search_tags.initialize();

    $("#searchtext").typeahead({
        hint: true,
        highlight: true,
        minLength: 1
    }, {
        name: 'search',
        displayKey: 'value',
        source: search_tags.ttAdapter()
    }).on('typeahead:selected typeahead:autocompleted', function(e, datum) {
        dosearch();
    });


    $("#searchtext").blur(function(e) {

    });
    $("#searchtext").keypress(function(e) {
        if (e.keyCode == 13) {
            dosearch();
            return false;
        }
    });
});
</script>
