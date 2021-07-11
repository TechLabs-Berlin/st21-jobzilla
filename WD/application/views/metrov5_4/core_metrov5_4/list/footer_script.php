<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

 $range_suffix = '_498756425end';

  echo form_open('', array('method'=>'post', 'enctype'=>'multipart/form-data' , 'role'=> 'form', 'id'=>'listing_form'));  ?>
    <input type="hidden" name="table_name"   id="table_name" class="input_field" value="<?php echo $table_name;?>">
    <?php  foreach ($listing_field as $field) {?>
        <?php 
        if(isset($all_field)){
            if (sizeof($all_field) !== 0) {
                if ( @$all_field [$field] ['type'] == 'datetime') {
                    $datetime = " datetime='1' ";
                }else {
                    $datetime = " datetime='0' ";
                }    
            }
        }?>
        <input type="hidden" name="<?php echo $table_name;?>_<?php echo $field;?>" class="input_field" id="input_<?php echo $field;?>">
        <input type="hidden" name="<?php echo $table_name;?>_<?php echo $field.$range_suffix;?>" class="input_field" id="input_<?php echo $field.$range_suffix;?>">
        <input type="hidden" name="<?php echo $table_name;?>_<?php echo $field;?>_operator" class="input_field_operator" id="input_<?php echo $field;?>_operator">

    <?php } ?>
<?php echo form_close(); ?>

<style>
    .column-width{
    width:<?php  echo $this->config->item('#MAX_COLUMN_WIDTH');?>;
    }
</style>

<script type="text/javascript">
    var showFieldAddUrl = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . $ajax_update_user_selection; ?>";
    var odderFieldAddUrl = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . $ajax_update_user_order; ?>";
    var showFieldResetUrl = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . $ajax_reset_user_selection; ?>";
    var orderFieldResetUrl = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . $ajax_reset_user_order; ?>";
    var deleteUrl = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . $delete_method; ?>";
    var trashUrl = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . 'trash_operation'; ?>";
    var copyUrl = "<?php echo $site_url . $controller_sub_folder . '/' . $class_name . '/' . $copy_method; ?>";
    var table_name = "<?php echo $table_name; ?>";
    var save_name_can_not_empty = "<?php echo dashboard_lang('_SAVE_NAME_CAN_NOT_BE_EMPTY'); ?>";
    var you_must_choose_a_template_before_save = "<?php echo dashboard_lang("_YOU_MUST_CHOOSE_A_TEMPLATE_BEFORE_SAVE"); ?>";
    var record_deleted_successfully = "<?php echo dashboard_lang('_VIEW_DELETED_SUCCESSFULLY');?>";
    var record_edited_successfully = "<?php echo dashboard_lang('_RECORD_EDITED_SUCCESSFULLY');?>";
    var permission_denied_to_delete_record = "<?php echo dashboard_lang('_PERMISSION_DENIED_TO_DELETE_RECORD');?>";
    var error_on_record_update = "<?php echo dashboard_lang('_ERROR_ON_RECORD_UPDATE');?>";
    var field_mandatory_msg = "<?php echo dashboard_lang('_ALL_FIELDS_ARE_MANDATORY');?>" ;
    var range_suffix = "<?php echo $range_suffix;?>" ;	
</script>


<input type="hidden" id="current_searching_field" value="">

<script type="text/javascript">

    $('document').ready(function(){
    	setTimeout(function(){ 
     		$("td.operator-holder").find(".tokenize:last").find("input").attr("class", "search");
         }, 100);

    	$('.m-datatable__pager-link.m-datatable__pager-link--last > a').html('>>');
    	$('.m-datatable__pager-link.m-datatable__pager-link-number > a[rel="start"]').html('<<');
        var current_searching_field = $('#current_searching_field').val();
		
        $('.tokenize').tokenize({
            displayDropdownOnFocus:true,
            debounce: 300,
            datas: baseURL+"dbtables/<?php echo $table_name;?>/search_own_field?table_name=<?php echo $table_name;?>",
            autosize: true,
            onAddToken :function(value, text, e){

                submit_form();
            },
            onRemoveToken: function(value, text, e){
                submit_form();
            }
        });

        $(".search_in").click(function(){
			$(this).closest(".m-datatable__cell").find(".operatorsDD").removeClass("operatorsDDHide");			
        });

        $(".dateFiltering").click(function(){
			$(this).closest(".m-datatable__cell").find(".operatorsDD").removeClass("operatorsDDHide");			
        });

        $(".dateFiltering").on("change", function() {
            submit_form();
        });

        $(".reset-rang-input-data").on("click", function() {
            $(this).closest(".operator-holder").find(".from-date").val("");
            $(this).closest(".operator-holder").find(".to-date").val("");
            submit_form();
        });
        
        // increase width of clickable area
        $(".search_in").attr('size',10);



        
        var hold_scroll_position = false;

        if( $(".listing_field_search ul.TokensContainer").each(function(){
                if($(this).find('li').length>1) hold_scroll_position = true;
        }));

        if( hold_scroll_position == true )   {
            $('#table-listing-items').scrollLeft(<?=isset($_POST['scrollPosition'])? $_POST['scrollPosition']:0 ?>);            
        } 
        $('#table-listing-items').scroll(function(){
            if($('#listing_form input[name=scrollPosition]').length<1)
             $('#listing_form').append("<input type='hidden' name='scrollPosition' value='"+$(this).scrollLeft()+"'>");
         else $('#listing_form input[name=scrollPosition]').val($(this).scrollLeft());
        });


    });

    

    function clear_datetime_field(item){

        // $('select.listing_field_search[datetime=1]').empty();
        // $('select.listing_field_search[datetime=1]').siblings('.listing_field_search').find('.TokensContainer').html('<li class="TokenSearch" ><input class="search_in" size="5"></li>');
        // item.siblings('.listing_field_search').find('.TokensContainer').html('<li class="TokenSearch" ><input class="search_in" size="5"></li>').find('.search_in').val(item.val());
        item.siblings('.listing_field_search').find('.TokensContainer').find('li:first').remove().delay(500,function(){

        item.parent('span').children('input').val(item.val());
        item.empty();
        });
    }


    function submit_form() {


        //              code for datetime start 
        var submitting_form =[] ;

        var $datetime_item=$('select.listing_field_search[datetime=1]').each(function(index){

                var datetime_val = $(this).val()[0];
               
                // console.log(datetime_val);
                if(datetime_val != '' &&  datetime_val != undefined){

                    if(isNaN(datetime_val)){
                        var correct_item = datetime_val.split('_');
                        if(correct_item.length < 2 )
                        {
                           clear_datetime_field($(this));
                            submitting_form.push( false );            
                        }
                        
                    }else {
                        if( datetime_val.length < 10 ){
                            // console.log( datetime_val.length, "returning for length");
                            clear_datetime_field($(this));
                            submitting_form.push( false );               
                        }
                    }
                }
                
        });


        for (var i = 0; i < submitting_form.length; i++) {
            if( submitting_form[i]== false){                
                return false;
            } 
        }
        
        //              code for datetime end

        $('select.listing_field_search').each(function(){

            var field_name = $(this).attr('data-field-name');
            var operator = $(this).closest(".search-in-column").find(".operatorsDD").val();
            var value =  $(this).val();
            var operator = $(this).closest(".search-in-column").find(".operatorsDD").val();
            $('input#input_'+field_name).val(value);
            $('input#input_'+field_name+'_operator').val(operator);
        });

        $('input.dateFiltering').each(function(){
            var field_name = $(this).attr('data-field-name');
            var operator = $(this).closest(".search-in-column").find(".operatorsDD").val();
            var value =  $(this).val();
            if (value == null) {
                value = '';
            }else {
                value = value.toString();
            }
            $('input#input_'+field_name).val(value.split("_")[0]);
            $('input#input_'+field_name+'_operator').val(operator);
        });

       $('#table_name').val('<?php echo $table_name;?>');
       $('#listing_form').submit();

    }
    
    $('select.operatorsDD').on('change', function(){
    	if($(this).siblings(".dateFiltering").length > 0){
            var searchValue = $(this).siblings(".dateFiltering").val(); 
        } else{
            var searchValue = $(this).siblings(".tokenize").val();
        }    			     
		if(searchValue.length > 0){
			submit_form();
		} else{
            // fild the from input field
            var element = $(this).siblings(".dateFiltering").attr("data-field-name");
            if($(this).val() != "><"){
                $(this).siblings("input[data-field-name='"+element+range_suffix+"']").css("visibility", "hidden");
            } else{
                $(this).siblings("input[data-field-name='"+element+range_suffix+"']").css("visibility", "visible");
            }
        }   	
    });
    
    $('input.listing_field_search').on('keyup',function(){

        var data = {};
        var lookup = {};
        var ajax_select_element = $(this).attr('data-field-name');

        var current_field_lookup = $(this).attr('data-lookup');
        if (current_field_lookup == '1') {

            var current_field_name = $(this).attr('data-value');
            var table_name= $(this).attr('ref-table');

        }else {
            var current_field_name = $(this).attr('data-field-name');
            var table_name= $(this).attr('data-table-name');
        }

        var value = $(this).val();
        if (value.length > 0){

            $('input.listing_field_search').each(function(){

                var field_name = $(this).attr('data-field-name');
                if (value.length > 0){

                    var is_lookup = $(this).attr('data-lookup');
                    if (is_lookup == '1' ){

                        lookup[field_name] = $(this).attr('ref-table')+","+$(this).attr('data-value')+","+$(this).attr('data-key')+","+$(this).val();
                    }else {

                        data[field_name]  = $(this).val();
                    }
                }

            });

            jQuery.ajax({
                type: "POST",
                url: baseURL+"dbtables/"+ controller_name +"/search_all_fields",
                data:'data='+JSON.stringify(data)+"&table_name="+table_name+"&current_field_name="+current_field_name+"&lookup="+JSON.stringify(lookup)+"&main_table_name="+controller_name +"&main_field_name="+ajax_select_element,
                beforeSend: function(){

                },
                success: function(data){
                    $('ul.'+ajax_select_element).show();
                    $('ul.'+ajax_select_element).html('').append(data);
                }
            });

        }else {

            $('ul.'+current_field_name).hide();
        }
    });

    $('body').on('click','li.description',function(){

        var current_value = $(this).attr('data-value');
        var current_field_name = $(this).attr('data-db-field-name');
        $('input.input_field').val('');

        $('input.listing_field_search').each(function(){
            var field_name = $(this).attr('data-field-name');
            var value =  $(this).val();
            if (value.length > 0){
                if (field_name == current_field_name) {
                    $('input#input_'+field_name).val(current_value);
                }else {
                    $('input#input_'+field_name).val(value);
                }
            }
        });
        $('input#input_'+current_field_name).val(current_value);
        $('#table_name').val('<?php echo $table_name;?>');
        $('#listing_form').submit();
    });

    $('i.fa-remove').on('click',function(){
        var current_field_name = $(this).attr('data-field');
        $('input.listing_field_search').each(function(){
            var field_name = $(this).attr('data-field-name');
            var value =  $(this).val();
            if (value.length > 0){
                $('input#input_'+field_name).val(value);
            }
        });
        $('#input_'+current_field_name).val('');
        $('#listing_form').submit();

    });
    
    $(document).on('click',function(event) {
        if ( !$(event.target).hasClass('description')) {
            $("ul.show_cateory_list").hide();
        }
    });
    
    $('[rel="tooltip"]').tooltip();

   
    setTimeout(function() {
        $('#alert-div-hide-export').hide('fast');
    }, 10000);
  
    
</script>
