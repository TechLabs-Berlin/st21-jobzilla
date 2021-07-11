<style>
    .msg_comment p {
        margin-top:10px;
        margin-bottom: -10px;
    }
</style>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/custom/css/custom_messages.css" rel="stylesheet">
<link href="<?php echo CDN_URL; ?>media_metrov5_4/custom/css/components.css" rel="stylesheet">
<?php


if (isset($_GET['return_url']) && strlen($_GET['return_url'])>9){

    $return_url = $_GET['return_url'];

}else {

    $return_url = base_url()."dbtables/message/listing";
}


?>
<script>
    var confirm_text  = "<?php echo dashboard_lang("_ARE_YOU_SURE?")?>";
    var name_text = "<?php echo '( '.dashboard_lang('_NONE').' ) '; ?>";
    var comment_will_email_text = "<?php echo dashboard_lang('_YOUR_COMMENT_WILL_BE_EMAILED_TO');?>";
    var uri_segment_five = '<?php echo $this->uri->segment(5);?>';
    var baseURL = "<?php echo base_url();?>";
    var enterMsgDescription = "<?php echo dashboard_lang("_PLEASE_ENTER_MESSAGE_DESCRIPTION")?>";
    var msgTitle = "<?php echo dashboard_lang("_PLEASE_FILL_OUT_TITLE_FIELD")?>";
</script>
<style>
    .green.go-back-btn {
        padding: 6px 12px;
        line-height: 22px !important;
    }
</style>
<!--start inbox message-details-->
<div class="inbox message-details">
    <!--start inbox body-->
    <div class="inbox-body">
        <div class="inbox-header">

            <h1 class="pull-left"><a class="btn green go-back-btn" href="<?php echo base_url();?>dbtables/message/listing"><i class="fa fa-caret-left"></i><?php echo dashboard_lang('_GO_BACK_TO_ALL_DISCUSSIONS');?> </a> </h1>
            <form action="index.html" class="form-inline pull-right">
                <div class="input-group input-medium">

			  <span class="input-group-btn">

			  </span> </div>
            </form>
        </div>
        <!--End inbox-header-->
        <!--start inbox-content-->
        <div class="inbox-content" style="min-height:220px;">
            <div class="row">
                <div class="col-md-12">
                    <div class="inbox-header inbox-view-header">
                        <h1 class="pull-left" style="font-weight:bold;"><?php echo ucfirst($contract_number); ?></h1>
                        <div class="pull-right">  </div>
                    </div>
                    <div class="author-date-time"> <?php echo dashboard_lang('_POSTED_BY');?> <?php echo @$message_posted_details['first_name']." ".@$message_posted_details['last_name']; ?>  <?php echo format_date_time(@$message_posted_details['post_datetime']); ?>    </div>
                </div>
            </div>
            <?php
            $CI = &get_instance();
            $CI->load->model('Messages_model');
            $user_id = $this->session->userdata('user_id');
            $msg_count = 0;
            foreach ($message_details as $details) { ?>
                <?php $msg_count = $msg_count +1;?>
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-md-8">
                        <div class="inbox-view-info" <?php if ($msg_count == 2) {?>style="margin-top: 5px;" <?php } ?>>
                            <div class="inbox-author-photo">
                            <?php $user_helper = BUserHelper::get_instance($details['user_id']); ?>                                
                                <span class="img-circle pull-left" style=" margin-right:10px; width:50px; height:50px; background-image:url(
        						<?php
        						  echo $user_helper->render_profile_picture ( $details['image'] );        
        						?>); background-size:cover; background-repeat:no-repeat; background-position:center center;"> </span>
                            </div>

                            <div class="inbox-discussion-message">
							  <span class="inbox-author-name">
							  <?php if($msg_count > 1){echo $details['first_name']." ".$details['last_name'];}?></span>
							  
                                <?php

                                $user_can_delete_msg = $CI->Messages_model->check_user_can_delete_msg($user_id, $details['messages_id'], $details['comment_user_id']);

                                ?>
                                <div class="inbox-view" style="margin-bottom: 5px;">
                                    <span class='msg_comment' style='font-size:14px; color:#2f353b;display: block;'><?php echo html_entity_decode( $details['message_conversation_details'] ); ?> </span>
                                    <span style='font-size:13px; color:#888888;'><?php echo dashboard_lang('_POSTED')." ".format_date_time($details['post_datetime']);?> </span>

                                    <?php

                                    if ($user_can_delete_msg && strlen($details['conversation_id']) >0 && $msg_count > 1){
                                        ?>
                                        <a href="javascript:void(0);" data-id="<?php echo $details['conversation_id'];?>"  class="delete_msg_conversation">
                                            <?php echo dashboard_lang('_DELETE');?>
                                        </a>
                                    <?php } ?>
                                </div>

                                <?php 
                                if(isset($details['file_details'])){

                                    $fdetails = $details['file_details'];

                                    foreach ($fdetails as $f) {
                                        
                                        if ( isset($f['file_location']) && $f['file_location'] != null) { ?>

                                            <div class="inbox-attached">
                                                <div class="margin-bottom-25">

                                                    <?php
                                                    
                                                     $file_type = $f['file_type'];
                                                    $is_image = strpos($file_type,'image');
                                                    if ($is_image !== false){

                                                        echo "<img class='fancybox' ' style='margin-bottom:10px;width:190px; '  src='".CDN_URL.$f['file_location']."'>";
                                                    }else {

                                                        echo "<br/> <div class='flie-upload-area'><i class='fa fa-file'></i>  ".explode('/',$f['file_location'])[1]."</div>";
                                                    }

                                                    ?>

                                                    <div> <a style="text-decoration:underline;color:#888888;font-size:13px;margin-top:3px;display:block;margin-left:0;" href="<?php echo $f['file_location'];?>" download><?php echo dashboard_lang('_DOWNLOAD');?> </a> </div>
                                                </div>
                                            </div>
                                        <?php } 
                                    }
                                }

?>
                            </div>
                        </div><!--end inbox-view-info 1-->
                    </div>
                   
                </div>

                
            <?php } ?>
            <div class="append_message_conversation">

            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?php $user_helper = BUserHelper::get_instance(); ?>
                    <?php $sendMessagePermission = userIdMatch($user_helper->user->id, $message_id) ?>
                    
                    <form class="message-deatils-form" action="<?php echo base_url();?>dbtables/message/add_message_discussion?return_url=<?php echo $return_url; ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="message_id" value="<?php echo  $message_id;?>" >
                        <input type="hidden" name="notification_option" value="specific_people" >

                        <div id="holder">
                            <div class="message-details-author">                                
                                <span class="img-circle pull-left" style=" margin-right:10px; width:50px; height:50px; background-image:url(
        						<?php
        						  echo $user_helper->user_image;        
        						?>); background-size:cover; background-repeat:no-repeat; background-position:center center;"> </span>
                            </div>
                            <div class="checkeditor-message-details tab-content">
                                <div class="visible-comment-box">
                                    <p class="comment-placeholder"><?php echo dashboard_lang('_ADD_A_COMMENT_OR_UPLOAD_A_FILE');?></p>
                                    <div class="visible-submit-details">
                                        <textarea class="form-control ckeditor add_message_comment" style="display: inline;" name="msg_discussion" id="editor1" rows="4" cols="30"> </textarea>
                                        <div class="msg-details-file-upload-area file-upload-area">
                                            <div class="row progress-bar-block" style="display:none;">
                                                <div class="col-md-12">
                                                    <div class="myProgress">
                                                        <div class="progress-bar"></div>
                                                    </div>
                                                </div>
                                                </div>
                                            <div class="msg-details-file-upload-inner">
                                                <i class="fa fa-paperclip"></i>
                                                <?php echo dashboard_lang('_DRAG_FILE_HERE');?> <?php echo dashboard_lang('_OR');?> <a href="javascript:void(0);" id="upload_link"> <?php echo dashboard_lang('_CLICK_HERE_TO_UPLOAD_FILE'); ?></a>
                                                <br/> 
                                                <span class="">
                                                   
                                                    <span class="delete_file" style="vertical-align:middle;">&nbsp; <a href="javascript:void(0);" id="remove_file"> </a></span>
                                                        <input type="file" style="display:none;" data-entity-id="<?php echo  $message_id;?>" name="userfile" id="upload_file" class="message-deatils-upload-btn upload-input-file"></span>
                                                        <div class="all-msg-files"></div>
                                            </div>
                                        </div>

                                        <div id="status"> </div>

                                        <div>
                                            <?php
                                            $lastConversations  = end($message_details) ;
                                            $check_all_people_selected =  check_all_people_selected ($message_id);
                                            $lastId = $lastConversations['comment_user_id'];
                                            $list_of_people = get_list_of_thread_people ($message_id, $lastId);

                                            $users_list = $CI->Messages_model->get_users_list ($list_of_people);

                                            ?>

                                            <span class="change"> <?php echo dashboard_lang('_YOUR_COMMENT_WILL_BE_EMAILED_TO'); ?>  <?php if(!empty($users_list)){echo  $users_list;} else{ echo "( ".dashboard_lang('_NONE')." )";} ?>  
                                                <a href="javascript:void(0);" style="text-decoration:underline;"> 
                                                (<?php echo dashboard_lang('_CHANGE');?>) </a> </span>

                                            <div class="choose_people">
                                                <span style="font-weight:bold;font-size:14px;display:block;margin-top:15px;"> <?php echo dashboard_lang('_EMAIL_THIS_MESSAGE_TO_PEOPLE_ON_THE_PROJECT');?> </span>

                                                <a href='javascript:void(0);' id='select_all'> <?php echo dashboard_lang('_SELECT_ALL');?></a>&nbsp;|&nbsp;<a href='javascript:void(0);' id='select_none'><?php echo dashboard_lang('_SELECT_NONE');?> </a>

                                                <br/>
                                                <div class="specific_people">
                                                    <div class="row">
                                                        <div class="col-sm-12">

                                                            <?php
                                                            $logged_in_id = $this->session->userdata('user_id');                                                            
                                                            foreach ($user_list as $user_list) {
                                                                if($user_list['id'] != $logged_in_id){
                                                                    ?>

                                                                    <div class="col-sm-4" style="min-height:25px;line-height:20px;">
                                                                        <input type="checkbox" data-name="<?php echo $user_list['first_name']." ".$user_list['last_name']; ?>" class="people_checkbox " name="people[]" value="<?php echo $user_list['id']; ?>"  
                                                                        <?php if (in_array($user_list['id'] , @$list_of_people)) {echo "checked";}?>> <?php $spec_pep_name = $user_list['first_name'].' '.$user_list['last_name']; echo  substr($spec_pep_name,0,20)?>
                                                                    </div>

                                                                <?php } } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="submit" data-message-id="<?php echo  $message_id;?>" value="<?php echo dashboard_lang('_SUBMIT');?>" class="btn green submit-message">

                                    </div>
                                </div>
                            </div>
                        </div> <!--End holder-->
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                    </form>
                  
                </div>
            </div>
        </div>
        <!--End inbox-content-->
    </div>
    <!--End inbox body-->
</div>
<link href="<?php echo CDN_URL;?>media_metrov5_4/portal_core/css/messages.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo CDN_URL;?>media_metrov5_4/ckeditor/ckeditor.js"></script>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/drag_and_drop.js"></script>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/add_message.js" type="text/javascript"></script>


<script>
var removeAllText = "<?php echo dashboard_lang("_REMOVE_ALL"); ?>";

    $(".visible-submit-details").hide();
    $(".visible-comment-box").click(function () {
        $(".visible-comment-box .visible-submit-details").show();
        $(".visible-comment-box .comment-placeholder").hide();
    });

    $('.choose_people').hide();
    $('.delete_file').hide();
    $('#remove_file').hide();
    $('.change').on('click',function(){

        $('.choose_people').show();
        $('.change').hide();
    });

    $('#remove_file').on('click',function(){

        $('#upload_file').val('');
        $('.upload_file_name').html('');
        $('#remove_file').hide();

    });

    $('.notification').on('change',function(){

        var checked_value =  $('input:radio[name=notification_option]').filter(":checked").val();

        if(checked_value == 'all') {

            $('.specific_people').hide();
        } else {

            $('.specific_people').show();
        }

    });
   



        $('body').on('change','#upload_file',function(e){

            var file_name = $('body #upload_file').val();
            var file_array = file_name.toString().split('fakepath');
            var new_file = file_array[file_array.length-1];
            var str = new_file.substring(0, new_file.length);
            var parsed_file_name = str.replace(/\u005C/, "");
            $('body .upload_file_name').html(parsed_file_name);
            $('body #remove_file').show();
            $('body .delete_file').show();

            var reader = new FileReader();
            reader.onload = function (event) {
                $('body i.fa-2x').hide();
                $('body #upload_image').show().attr('src',event.target.result);
            }
            reader.readAsDataURL(e.target.files[0]);

        });


        //  $('body').on('click','#upload_link',function(){

        //     $('body #upload_file').click()[0];
        // });
      
      

    // $('#upload_file').on('change',function(){

    //     var file_name = $('#upload_file').val();
    //     var file_array = file_name.toString().split('fakepath');
    //     var new_file = file_array[file_array.length-1];
    //     $('.upload_file_name').html(" "+new_file.substring(1, new_file.length));
    //     $('a#remove_file').show();        

    // });


    // $('#upload_link').click(function(e){

    //     $('#upload_file').click();
    //     e.preventDefault();
    // });


/*

        function upload_msg_files( file_class_name ) {

                var form = new FormData(); 
                var entity_id = $(file_class_name).attr("data-entity-id");
                
                form.append("upload_file", $(file_class_name)[0].files[0]);
                form.append("entity_id", entity_id);
                
                $("#ajax_load").show();
                
                $.ajax({
                    url: baseURL+'dbtables/message/upload_msg_files',
                    type: 'POST',
                    dataType: 'json',
                    maxNumberOfFiles: 1,
                    autoUpload: false,
                    xhr: function() {
                        myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener(
                                'progress', 
                                function (e) { // ***** I mean here. **** //
                                        
                                    $(".progress-bar-block").show();
                                        
                                    if (e.lengthComputable) {

                                        var percentComplete = Math.round(e.loaded * 100 / e.total);

                                        $('.progress-bar').text(percentComplete.toString() + '%');
                                        $('.progress-bar').css("width",percentComplete.toString() + '%');
                                                
                                     }
                                }
                                , false);
                        }
                        return myXhr;
                    },
                    success: function(result) {

                        $("#ajax_load").hide();
                        
                        $(".progress-bar").html('');
                        $(".progress-bar").css('width', '0%');
                        $(file_class_name).val('');
                        
                        if ( result.status == '1' ) {
                             $(".all-msg-files").append(result.file_path); 
                        }else {
                            
                        }

                        $(".progress-bar-block").hide();
                    },
                    "error": function(x, y, z) {

                        $("#ajax_load").hide();
                        $(file_class_name).val('');
                        $('.progress-bar').text('');
                        $('.progress-bar').css("width", '0%');
                        $(".progress-bar-block").hide();

                        alert("Ana error has occured:\n" + JSON.stringify(x) + "\n" + JSON.stringify(y) + "\n" + JSON.stringify(z));
                    },
                    data: form,
                    cache: false,
                    contentType: false,
                    processData: false
                });
        }
        
*/



    $('#upload_link').click(function(){

        $('#upload_file').click();
    });

    $('body').on('click','#select_all',function(){
        $('body input.people_checkbox').each(function(){

            $(this).attr('checked','checked');
            $('body input.people_checkbox').prop('checked',true);

            $('div.checker span').addClass('checked');

        });

    });

    $('#select_none').on('click',function(){

        $('.people_checkbox').each(function(){

            $('div.checker span').removeClass('checked');
            $(this).removeAttr('checked');
        });

    });

    $('.delete_msg_conversation').on('click',function(e){

        var conversion_id = $(this).attr('data-id');
        var delete_url =  "<?php echo base_url();?>dbtables/message/delete_conversation";
        var show_delete_confirmation_msg = confirm("<?php echo dashboard_lang('_ARE_YOU_SURE'); ?>");

        if (show_delete_confirmation_msg){

            $.ajax({
                url: delete_url,
                type: 'post',
                data: "conversion_id="+conversion_id,
                success: function (result) {

                    if (result){
                        window.location.reload();
                    }
                }
            });

        }

    });




</script>
