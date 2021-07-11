<div class="inbox message-details">
    <div class="inbox-body">
        <div class="inbox-header">
            <h1 class="pull-left"><?php echo $contract_number; ?></h1>
            
        </div>
        <div class="inbox-content" style="min-height: 240px;">
            <div class="inbox-header inbox-view-header">
                <h1 class="pull-left"><a class="btn green go-back-btn" href="<?php echo base_url();?>dbtables/message/listing"> <?php echo dashboard_lang('_GO_BACK_TO_ALL_DISCUSSIONS');?> </a> </h1>
                <div class="pull-right">  </div>
            </div>
            <?php foreach ($message_details as $details) { ?>
            <div class="inbox-view-info">
                <div class="row">
                    <div class="col-md-7"> <img class="inbox-author" width="45" src="<?php if ($details['image'] =='') { echo $this->config->item('default_avater_url');} else { echo CDN_URL.$details['image'];}?>">
                        <span class="sbold"> <?php echo $details['first_name']." ".$details['last_name']; ?></span>
                        </span> on <?php echo format_date_time($details['post_datetime']); ?>

                        <?php
                        $CI = &get_instance();
                        $CI->load->model('Messages_model');
                        $user_id = get_default_account_id();
                        $user_can_delete_msg = $CI->Messages_model->check_user_can_delete_msg($user_id, $details['id'], $details['user_id']);
                        if ($user_can_delete_msg){
                        ?>
                        <a href="javascript:void(0);" data-id="<?php echo $details['msg_conversation_id'];?>"  id="delete_msg_conversation">
                            <?php echo dashboard_lang('_DELETE');?>
                        </a>
                        <?php } ?>
                    </div>
                    <div class="col-md-5 inbox-info-btn">

                    </div>
                </div>
            </div>
            <div class="inbox-view" style="margin-bottom:15px;">
                <?php echo $details['message_conversation_details']; ?>
            </div>

            <?php if ($details['file_location'] != null) { ?>

            <div class="inbox-attached">

                <div class="margin-bottom-25">
                    <?php
                    $file_type = $details['file_type'];
                    $check_image = strpos($file_type ,'image');
                    if ($check_image !== false){

                        echo "<img src='".CDN_URL.$details['file_location']."'>";

                    }
                    ?>

                    <div><a href="<?php echo base_url();?><?php echo $details['file_location']; ?>" download> <?php echo dashboard_lang('_DOWNLOAD');?> </a> </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php } ?>
		
	<form action="<?php echo base_url();?>dbtables/message/add_message_discussion" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="message_id" value="<?php echo  $message_id;?>" >
         <div id="holder">
            <textarea class="form-control" name="msg_discussion" rows="4" cols="38"> </textarea>

            <?php echo dashboard_lang('_DRAG_FILE_HERE');?> <?php echo dashboard_lang('_OR');?> <a href="javascript:void(0);" id="upload_link"> <?php echo dashboard_lang('_CLICK_HERE_TO_UPLOAD_FILE'); ?></a>
            <br> <span class="upload_file_name"> </span>
            <input type="file" style="display:none;" name="userfile" id="upload_file" class="message-deatils-upload-btn">

        </div>
        <br/>
        <div id="status">

        </div>
        <input type="hidden" name="is_came_from_entity" value="false" style="margin-bottom:15px;margin-top:15px;">
            <div>
                <?php
                $check_all_people_selected =  check_all_people_selected ($message_id);
                $list_of_people = get_list_of_thread_people ($message_id);
                ?>

                <input type="radio" name="notification_option" class="notification" id="" value="specific_people" checked> <?php echo dashboard_lang('_SEND_NOTIFICATION_TO_SPECIFIC_PEOPLE');?>
                <br/>
                <a href='javascript:void(0);' id='select_all'> <?php echo dashboard_lang('_SELECT_ALL');?></a>|<a href='javascript:void(0);' id='select_none'><?php echo dashboard_lang('_SELECT_NONE');?> </a>
                <br/>
                <div class="specific_people">
                    <?php foreach ($user_list as $user_list) {?>
                        <input type="checkbox" class="people_checkbox form-control" name="people[]" value="<?php echo $user_list->id; ?>"  <?php if (in_array($user_list->id , $list_of_people)) {echo "checked";}?>> <?php echo $user_list->name; ?>

                    <?php } ?>
                </div>
            </div>
            <input type="submit" value="<?php echo dashboard_lang('_SUBMIT');?>" class="btn green">


        </form>




    </div>
</div>
</div>

<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/drag_and_drop.js"></script>

<script>


    $('.notification').on('change',function(){

        var checked_value =  $('input:radio[name=notification_option]').filter(":checked").val();

        if(checked_value == 'all') {

            $('.specific_people').hide();
        } else {

            $('.specific_people').show();
        }

    });
    $('#upload_file').on('change',function(){

        var file_name = $('#upload_file').val();
        var file_array = file_name.toString().split('fakepath');
        var new_file = file_array[file_array.length-1];
        $('.upload_file_name').html(" "+new_file.substring(1, new_file.length));

    });


    $('#upload_link').click(function(){

        $('#upload_file').click();
    });

    $('#select_all').on('click',function(){

        $('.people_checkbox').each(function(){

            $('div.checker span').addClass('checked');
			 $(this).attr('checked','checked');
        });

    });

    $('#select_none').on('click',function(){

        $('.people_checkbox').each(function(){

            $('div.checker span').removeClass('checked');
			$(this).removeAttr('checked');
        });

    });

    $('#delete_msg_conversation').on('click',function(e){


    });

</script>

<?php
function format_date_time ( $date_time) {

    //$time = strtotime($date_time);
    $time_diff = time() - $time;
    if ($time_diff > 86400) {

        return  date('D, M d Y', $date_time);

    }else {

        $get_time_difference = difference_between_current_and_given_time ( $date_time);
        return $get_time_difference.dashboard_lang("_AGO");
    }

}

function difference_between_current_and_given_time ( $post_datetime) {


   // $time = strtotime($post_datetime);

    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? 1 : $time;
    $tokens = array (
         31536000 => dashboard_lang("_YEAR"),
            2592000 => dashboard_lang("_MONTH"),
            604800 => dashboard_lang("_WEEK"),
            86400 => dashboard_lang("_DAY"),
            3600 => dashboard_lang("_HOUR"),
            60 => dashboard_lang("_MINUTE"),
            1 => dashboard_lang("_SECOND")        
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    }


}
?>
