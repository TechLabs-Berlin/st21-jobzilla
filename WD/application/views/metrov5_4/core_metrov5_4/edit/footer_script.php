<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$default_money_format = strtolower($this->config->item('#DEFAULT_MONEY_FORMAT'));
?>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.25/jquery.fancybox.min.js" ></script>
<script type="text/javascript" src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/edit.js" ></script>
<script type="text/javascript" src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/add-relational-tables-record.js" ></script>
<script type="text/javascript">
    var action = "<?php echo $this->input->get("action"); ?>";
    var class_name = "<?php echo $class_name ;?>";
    var placeHolder= "<?php echo dashboard_lang('_SELECT_FROM_DROPDOWN') ?>";
    var id = '<?php echo @$id; ?>';    
	var deleteUrl = "<?php echo $base_url . $controller_sub_folder . '/' . $class_name . '/' . $delete_method; ?>";
	var deleteMsg = "<?php echo $base_url . $controller_sub_folder . '/' . $listing_path; ?>";
	var copyUrl = "<?php echo $base_url . $controller_sub_folder . '/' . $class_name . '/' . $copy_method; ?>";
	var copyMsg= "<?php echo $base_url . $controller_sub_folder . '/' . $listing_path; ?>";
	var passwordMsg= "<?php echo dashboard_lang("_TWO_PASSWORD_NOT_MATCH"); ?>";
	var passwordRetypeMsg = "<?php echo dashboard_lang("_PLEASE_RETYPE_YOUR_PASSWORD") ?>";
    var required = "<?php echo dashboard_lang('_REQUIRED'); ?>";
    var add_new_msg = "<?php echo dashboard_lang('_ADD_NEW'); ?>";
    var done_text = "<?php echo dashboard_lang('_DONE'); ?>";
    var error_text = "<?php echo dashboard_lang('_ERROR'); ?>";    
    var allow_add_in_drop_down_select = '<?php echo $this->config->item('allow_add_in_drop_down_select')?>';
    var default_money_format = '<?php echo $default_money_format;?>';
    var max_money = '<?php echo dashboard_lang("_MAX_AMOUNT_ENTERED_TO_MONEY_FIELD"); ?>';
    var field = '<?php echo dashboard_lang("_FIELD"); ?>';
    var email_validation_msg = '<?php echo dashboard_lang("_ENTER_VALID_EMAIL_ADDRESS"); ?>';
    var number_validation_msg = '<?php echo dashboard_lang("_ENTER_VALID_NUMBER"); ?>';
    var phone_validation_msg = '<?php echo dashboard_lang("_ENTER_VALID_PHONE_NUMBER"); ?>';
    var required_field_validation_msg = '<?php echo dashboard_lang("_THIS_FIELD_IS_REQUIRED"); ?>';
    var common_validation_msg = '<?php echo dashboard_lang("_YOU_HAVE_SOME_FORM_ERRORS_PLEASE_CHECK_BELOW"); ?>';    
    var iframeView = "<?php echo !empty($_GET["iframeView"])?'1':'';?>";
    var iframeSaveAndClose = "<?php echo !empty($_GET["iframeSaveAndClose"])?'1':'';?>";
    var searchString = "<?php echo !empty($_GET["searchString"])?$_GET["searchString"]:'';?>";
    var refFieldName = "<?php echo !empty($_GET["fieldName"])?$_GET["fieldName"]:'';?>";  
    var addNewOption = '<?php echo dashboard_lang("_ADD"); ?>'; 
    var deleteImageStr = "<?php echo dashboard_lang("_DELETE_THIS_IMAGE"); ?>";
    var deleteFileStr = "<?php echo dashboard_lang("_DELETE_THIS_FILE"); ?>";
    
    function isNumber(evt, element) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        <?php
       
          if ($default_money_format == 'us') {
    
            echo "var condition = (charCode != 190 || $(element).val().indexOf('.') != -1)  && (charCode != 110 || $(element).val().indexOf('.') != -1)  && charCode > 47 && ((charCode < 48 && charCode != 8) || (charCode > 57 && charCode < 96) || charCode > 105);";
    
          } else {
    
             echo  "var condition = (charCode != 188 || $(element).val().indexOf(',') != -1)  && charCode > 47 && ((charCode < 48 && charCode != 8) || (charCode > 57 && charCode < 96) || charCode > 105);";
    
          }
          
        ?>

        if (condition)
            return false;
        return true;
    }

    
</script>

<script src="<?php echo CDN_URL;?>media_metrov5_4/ckeditor/ckeditor.js"></script>

<script>

    function render_popup (ref_table, ref_key, ref_value ) {   

    	$('#ref_key').val(ref_key);
    	$('#ref_value').val(ref_value);
    	
        var current_key = $('#current_key').val();
        var iframe_url = baseURL+"dbtables/"+ref_table+"/load_only_edit";
        $('#iframe').attr('src',iframe_url);
        $('#full').modal('show');
        setTimeout(function(){
            var selected_value = $('#'+current_key).val();
            $('#'+current_key).val(selected_value).trigger('change');
    
        },500);
    
    }


    function check_required_field () {

        var is_all_required = true;
        $('input.required').each(function(){

            var check_length = $(this).val();
            var fileType = $(this).attr('type');
            if(fileType = 'file'){

                var attr = $(this).prev().find('a:first').attr('href');
                
                if (typeof attr !== typeof undefined && attr !== false && attr.length > 1 && $(this).prev().find('input:first').val() == '1') {

                    check_length = attr.length;
                }

            }

            if (check_length.length == 0) {

                $('button.save_button').attr('disabled',true);
                is_all_required = false;
            }
        });

        if (is_all_required) {

            $('button.save_button').removeAttr('disabled');
        }

   }

    $(document).ready(function(){

        //checking auto suggest field exists or not

    	<?php if(!empty($lookup_autosuggest_field)){ 
    	    $lookup_auto_field_name = $lookup_autosuggest_field; 
    	}
    	else{
    	    $lookup_auto_field_name = "no-auto-field";
    	}
    	    
    	?>

    	//auto suggest field loading via select 2
    	 
        $( ".<?php echo $lookup_auto_field_name; ?>" ).select2({
            
            placeholder: placeHolder,
            ajax: {
                url: baseURL + controller_sub_folder + '/' + controller_name + '/get_lookup_autosuggest',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                	var ref_table = $(this).attr('ref-table');
                	var ref_key = $(this).attr('ref-key');
                	var ref_value = $(this).attr('ref-value');
                	var order_by = $(this).attr('order_by');
                	var order_on = $(this).attr('order_on');
                    return {                        
                        q: params.term,
                        ref_table: ref_table,
                        ref_key: ref_key,
                        ref_value: ref_value,
                        order_by : order_by,
                        order_on : order_on
                    };
                },
                processResults: function (data) {
                    // parse the results into the format expected by Select2.
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data
                    return {
                        results: data
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; },
            minimumInputLength: 0
        });


        $('.<?php echo $lookup_auto_field_name; ?>').on('change',function(){

        	var ref_table = $(this).attr('ref-table');
        	var ref_key = $(this).attr('ref-key');
        	var ref_value = $(this).attr('ref-value');

            var value = $('.<?php echo $lookup_auto_field_name; ?>').val();
            if (value == '<?php echo $this->config->item('dropdown_in_ajax_value');?>') {
                
                render_popup (ref_table, ref_key, ref_value);
            }
        });
 
        /*$('#iframe').load(function(){

            var iframe = $('#iframe').contents();
            iframe.find("a.green").hide();
            iframe.find('button#save_and_close').hide();
            iframe.find('button#add_new_item').hide();
            iframe.find('button#save_and_next').hide();
            iframe.find('button#copy').hide();
            iframe.find('div.page-header').hide();
            //iframe.find('.page-content').css("margin-left", "-10px");
            iframe.find('.page-content').css("margin-top", "-50px");
            iframe.find('#habla_panel_div').hide();
            iframe.find('button#delete').hide();
            iframe.find('button#add').hide();
            iframe.find("button.green").click(function(){
                $('#ajax_load').show();

                 setTimeout(function() {

                	 $('#ajax_load').hide();
                     $('#full').modal('hide');

                 }, 3300);
        });
        });*/
        
        
        $('#full').on('hidden.bs.modal', function () {

            var table_name = $('#iframe').contents().find('#table_name').val();
            var id = $('#iframe').contents().find('#id').val();
            var value = $('#ref_value').val();
            var key = $('#ref_key').val();
            $('#'+table_name).val("0").trigger("change");
            var current_key = $('#current_key').val();


            if (id.length > 0) {

                $.post(baseURL+'dbtables/<?php echo $class_name ;?>/get_specific_field_name',{table_name:table_name, id:id, value:value , key:key},function(response){

                    var parsed_response = JSON.parse(response);
                    
                    $('select[ref-table="'+table_name+'"]').append("<option value='"+parsed_response[key]+"'>"+parsed_response[value]+"</option>");
                    $('select[ref-table="'+table_name+'"]').val(parsed_response[key]).trigger("change");
                });

            } else {

           	  $('select[ref-table="'+table_name+'"]').val(0).trigger('change');
            }
            

        });


        check_required_field ();
        $('body').on('keyup','input',function(){

            check_required_field ();
        });

        $('body').on('change','input',function(){

            check_required_field ();
        });
       

    });
 
</script>
<script type="text/javascript" src="<?php echo CDN_URL; ?>media_metrov5_4/custom/js/history-tab.js" ></script>

