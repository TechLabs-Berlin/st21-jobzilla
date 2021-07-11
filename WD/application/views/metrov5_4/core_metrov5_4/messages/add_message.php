<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="inbox-body compose-message">
    <?php     $yesToAll = $this->config->item('#MODULE_MESSAGE_SEND_NOTIFICATION_TO_ALL');   ?>
    <link href="<?php echo CDN_URL; ?>media_metrov5_4/custom/css/custom_messages.css" rel="stylesheet">
    
    <div style="" class="m-portlet inbox-content tab-content">
        
        <div class="m-portlet__head discuss-with-btn">
			 <a href="<?php echo base_url();?>dbtables/message/listing" class="btn btn-brand m-btn green add_message add-message-back-btn" type="button"><i class="fa fa-caret-left"></i><?php echo dashboard_lang('_BACK');?></a> <br/>
        </div>
        
        <div class="m-portlet__body">
        <div class="msg-user-info">
    		<?php $user_helper = BUserHelper::get_instance(); ?>		
             
            <span class="msg-userp m--img-rounded m--marginless m--img-centered img-circle pull-left" style="background-image:url(<?php echo $user_helper->user_image;?>);"></span>
            <span class="msg-username m--margin-left-15"><?php echo $user_helper->user->first_name." ".$user_helper->user->last_name;?></span>
        </div>
            
        <form id="add_message" action="<?php echo base_url();?>dbtables/message/insert_message" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="message_type" value="<?php echo @$messages_type;?>" >
            <input type="hidden" name="entity_id" value="<?php echo @$entity_id; ?>">
            <input type="hidden" name="is_came_from_contract" value="false" >
            <input type="hidden" name="message_entity" value="discussions" />
            <input type="hidden" name="return_url" value="return_url=<?php echo base_url();?><?php echo @$current_url;?><?php echo $this->uri->segment(@$id_position); ?>/message">
            <input type="hidden" name="rental_object" value="<?php if(@$is_copy_contract) { echo  @$rental_object;}else if(strlen(@$contract_details[0]['object_id']) >1) {echo @$contract_details[0]['object_id'];}else { echo @$rental_object; } ?>" >
            <input type="hidden" name="start_date" value="<?php if(@$is_copy_contract) { echo  $start_date;}else if(strlen(@$contract_details[0]['start_date']) >1) {echo @$contract_details[0]['start_date'];}else { echo @$start_date; } ?>" >
            <textarea type="text" id="message_title" class="form-control msg_title" rows="1" cols="47" name="msg_title" placeholder="<?php echo dashboard_lang('_TITLE');?>" required></textarea>
            <br/>
            <div id="holder">
                <textarea class="form-control ckeditor add-desc-message" id="editor1" name="description" placeholder="<?php echo dashboard_lang('_DESCRIPTION');?>" id="editor" rows="4" cols="38"> </textarea>
                <div class="file-upload-area m--margin-top-10">
                <div class="row progress-bar-block" >
                	  <div class="col-md-12">
                		 <div class="myProgress">
                			<div class="progress-bar"></div>
                		 </div>
                	  </div>
            	    </div>
                    <i class="fa fa-paperclip"></i>
                    <?php echo dashboard_lang('_DRAG_FILE_HERE');?> <?php echo dashboard_lang('_OR');?> <a href="javascript:void(0);" id="upload_link"> <?php echo dashboard_lang('_CLICK_HERE_TO_UPLOAD_FILE'); ?></a><br/>
                   <span class="upload-file-entry-messages ">
                        <image id="upload_image" src="" style="display: none;" width="50"> 
                        <i style="display: none;" class="fa fa-file fa-2x"></i> 
                        
                        <input type="file" style="display:none;" name="userfile[]" data-entity-id="99" id="upload_file" class="message-deatils-upload-btn upload-input-file">
                        <div class="all-msg-files"></div>
                    </span>
                </div>
            </div>

            <div id="status">

            </div>
            <div class="check-box-with-btn-area m-radio-list m--margin-top-30">
                <?php
                $check_all_people_selected =  check_all_people_selected (@$message_id);
                $list_of_people = get_list_of_thread_people (@$message_id);
                $msgOption1 = "";
                $msgOption2 = "";
                $msgOption3 = "";
                $spacificPeoplesList = "none";
                $msgSendOptions = $this->config->item("#MESSAGES_DEFAULT_SENDING");
                switch ($msgSendOptions){
                    case "all":
                        $msgOption1 = "checked";
                        break;
                    case "specific_people":
                        $msgOption2 = "checked";
                        $spacificPeoplesList = "block";
                        break;
                    case "no_people":
                        $msgOption3 = "checked";
                        break;
                }
                ?>
                 <?php if( $yesToAll == 1){?>
                 <label class="m-radio">
				    <input type="radio" name="notification_option" class="notification" id="notification" value="all" <?php echo $msgOption1;?>> 
                    <?php echo dashboard_lang('_SEND_NOTIFICATION_TO_ALL');?>
                    <span></span>
                </label>
                <?php } ?>
                <label class="m-radio">
				    <input type="radio" name="notification_option" class="notification" id="notification" value="specific_people" <?php echo $msgOption2;?>> 
                    <?php echo dashboard_lang('_SEND_NOTIFICATION_TO_SPECIFIC_PEOPLE');?>
                    <span></span>
                </label>

                <div class="specific_people" style="display:<?php echo $spacificPeoplesList;?>;">
                    <div class="row user-selecion">
                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <a href='javascript:void(0);' id='select_all'> <?php echo dashboard_lang('_SELECT_ALL');?></a>&nbsp;|&nbsp;
                            <a href='javascript:void(0);' id='select_none'><?php echo dashboard_lang('_SELECT_NONE');?> </a>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        if(sizeof($user_list)>0){
                        $user_lists=array_chunk($user_list, ceil(count($user_list) / 4));

                        ?>

                        <div  class="col-xl-3 col-lg-4 col-md-4">
                        <?php if(isset($user_lists[0])): foreach ($user_lists[0] as $user_list) { ?>
                            <label class="m-checkbox"  title="<?php echo $user_list['email'] ?>">
                            <input data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" type="checkbox" class="people_checkbox" name="people[]" data-value="<?php echo $user_list['id']; ?>" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $list_of_people)) {echo "checked";}?>> 
                                <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
                                <span></span>
                                                </label>
                        <?php } endif;?>
                        </div>

                        <div  class="col-xl-3 col-lg-4 col-md-4">
                        <?php if(isset($user_lists[1])): foreach ($user_lists[1] as $user_list) { ?>
                            <label class="m-checkbox"  title="<?php echo $user_list['email'] ?>">
                            <input data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" type="checkbox" class="people_checkbox" name="people[]" data-value="<?php echo $user_list['id']; ?>" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $list_of_people)) {echo "checked";}?>> 
                                <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
                                <span></span>
                                                </label>
                        <?php } endif;?>
                        </div>

                        <div  class="col-xl-3 col-lg-4 col-md-4">
                        <?php if(isset($user_lists[2])): foreach ($user_lists[2] as $user_list) { ?>
                            <label class="m-checkbox"  title="<?php echo $user_list['email'] ?>" >
                            <input data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" type="checkbox" class="people_checkbox" name="people[]" data-value="<?php echo $user_list['id']; ?>" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $list_of_people)) {echo "checked";}?>> 
                                <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
                                <span></span>
                                                </label>
                        <?php } endif;?>
                        </div>

                        <div  class="col-xl-3 col-lg-4 col-md-4">
                        <?php if(isset($user_lists[3])): foreach ($user_lists[3] as $user_list) { ?>
                            <label class="m-checkbox"  title="<?php echo $user_list['email'] ?>">
                            <input data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" type="checkbox" class="people_checkbox" name="people[]" data-value="<?php echo $user_list['id']; ?>" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $list_of_people)) {echo "checked";}?>> 
                                <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
                                <span></span>
                                                </label>
                        <?php } endif;?>
                        </div>

                        <?php }
                        ?>
                    </div>
                </div>

                <label class="m-radio">
				    <input type="radio" name="notification_option" class="notification" id="notification" name="notification_option" class="notification form-control" id="notification" value="no_people" <?php echo $msgOption3;?>> 
                    <?php echo dashboard_lang('_DO_NOT_EMAIL_ANYONE');?>
                    <span></span>
                </label>
                
            </div>
            <input type="submit"  class='btn btn-accent m-btn add_message_button m--margin-top-30' value="<?php echo dashboard_lang('_SUBMIT'); ?>">


        </form>
        </div>
    </div>

</div>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/add_message.js" type="text/javascript"></script>
<link href="<?php echo CDN_URL;?>media_metrov5_4/portal_core/css/messages.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/drag_and_drop.js"></script>
<script>
    var removeAllText = "<?php echo dashboard_lang("_REMOVE_ALL"); ?>";
    

    $('.notification').on('click',function(){

       var checked_value =  $('input:radio[name=notification_option]').filter(":checked").val();

        if(checked_value == 'specific_people') {

             $('.specific_people').show();
        } else {

            $('.specific_people').hide();
        }

    });

    $('#upload_file').on('change',function(){

        var file_name = $('#upload_file').val();
        var file_array = file_name.toString().split('fakepath');
        var new_file = file_array[file_array.length-1];
        $('.upload_file_name').html(" "+new_file.substring(1, new_file.length));
        $('a#remove_file').show();        

    });


    $('#upload_link').click(function(){

        $('#upload_file').click();
    });


    $('#select_all').on('click',function(){

        $('.people_checkbox').each(function(){

            $('div.checker span').addClass('checked');
            $(this).prop('checked',true);
        });

    });

    $('#select_none').on('click',function(){

        $('.people_checkbox').each(function(){

            $('div.checker span').removeClass('checked');
            $(this).prop('checked',false);
            
        });

    });

    $('a#remove_file').on('click',function(){
    	$('span.upload_file_name').text('');
    	$('input#upload_file').val('');
    	$(this).hide();
    });

    </script>
<script src="<?php echo CDN_URL;?>media_metrov5_4/ckeditor/ckeditor.js"></script>
<style>
    .msg_title {

        font-weight: bold;
        font-size:17px;
        margin-bottom:20px;

    }
    .inbox-compose-btn .inbox-compose-attachment .choose_people_group {


    }
    </style>
