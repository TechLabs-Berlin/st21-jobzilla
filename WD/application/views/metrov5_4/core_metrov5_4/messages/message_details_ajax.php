<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php

if (isset($_GET['return_url']) && strlen($_GET['return_url'])>9){

    $return_url = $_GET['return_url'];

}else {

    $return_url = base_url()."dbtables/message/listing";
}
$CI = &get_instance();

?>
<!--start inbox message-details-->
<div class="inbox message-details">
<!--start inbox body-->
    <div class="inbox-body">
		<div class="inbox-header">
        <a class="btn btn-accent m-btn go-back-btn" style="color: #fff !important;" href="javascript:void(0);">
            <?php echo dashboard_lang('_GO_BACK_TO_ALL_DISCUSSIONS');?> 
        </a>
        <form action="index.html" class="form-inline pull-right">
			<div class="input-group input-medium">

			  <span class="input-group-btn">

			  </span> </div>
		  </form>
		</div>
		<!--End inbox-header-->
		<!--start inbox-content-->
        <div class="inbox-content">
		    <div class="row">
				<div class="col-xl-12 col-lg-12 col-md-12"> 
					 <div class="inbox-header inbox-view-header">
					  <h3 class="m--pull-left" style="font-weight:bold;"><?php echo ucfirst($contract_number); ?></h3>
					</div>
			    </div>
			</div> 
            <div class="row">
				<div class="col-xl-12 col-lg-12 col-md-12"> 
					<div class="author-date-time"> <?php echo dashboard_lang('_POSTED_BY');?> 
					    <?php echo @$message_posted_details['first_name']." ".@$message_posted_details['last_name']; ?>  
					    <?php  echo format_date_time(@$message_posted_details['post_datetime']); ?> 
					    <?php echo user_can_delete_msg_thread ( @$message_posted_details['id'] ); ?>  
					</div>
			    </div>
			</div>
			  <?php
			
			  $CI->load->model('Messages_model');
			  $user_id = $this->session->userdata('user_id');
			  $msg_count = 0;
			  foreach ($message_details as $details) { ?>
			  <?php $msg_count = $msg_count +1;?>
			<div class="row">
				<div class="col-xl-12 col-lg-12 col-md-12">  
					<div class="inbox-view-info">             
                        <div class="row">
                            <div class="col-xl-1 col-lg-1 col-md-1">
						        <div class="inbox-author-photo">
							<?php  $user_helper = BUserHelper::get_instance($details['comment_user_id']); ?>
                             <span class="msg-userpic m--img-rounded m--marginless m--img-centered  img-circle m--pull-left" style="background-image:url(
        						<?php
        						  echo $user_helper->render_profile_picture ( $details['image'] );        
        						?>);"> </span>
						        </div>
                            </div>

    						<div class="col-xl-11 col-lg-11 col-md-11">
                                <?php
                                 $commentData = [];
                                 $commentData['name'] = $details['first_name']." ".$details['last_name'];
                                 $commentData['user_id'] = $user_id;
                                 $commentData['message_id'] = $details['messages_id'];
                                 $commentData['name'] = $details['first_name']." ".$details['last_name'];
                                 $commentData['comment_user_id'] = $details['comment_user_id'];
                                 $commentData['message_conversation_details'] = $details['message_conversation_details'];
                                 $commentData['post_datetime'] = $details['post_datetime'];
                                 $commentData['conversation_id'] = $details['conversation_id'];
                                 $commentData['check_msg_count'] = 1;
                                 $commentData['msg_count'] = $msg_count;
                                 $commentData['file_details'] = $details['file_details'];
                                 
                                $list_of_people = get_sent_to_people ($details['conversation_id']);

                                $sent_to = $CI->Messages_model->get_users_list ($list_of_people);

                                 $commentData['sent_to'] = $sent_to;
                                 
                                 $this->load->view("metrov5_4/core_metrov5_4/messages/sub_views/each-msg-comment", $commentData);
                                 
                                ?>
                            </div>
                        </div>
					</div><!--end inbox-view-info 1-->
				</div>
				<div class="col-md-4 inbox-info-btn">
				  <?php if ( $msg_count  == '1') { ?>
                     <h6><b><?php echo dashboard_lang("_DISCUSS_THIS_MESSAGE");?></b></h6>
                  <?php } ?>    
				</div>
			</div>
		
              
			  <?php } ?>
            <div class="append_message_conversation">

            </div>
	       <div class="row">
               <div class="col-xl-1 col-lg-1 col-md-1">
			   		<div class="inbox-author-photo">
						<?php  $user_helper = BUserHelper::get_instance(get_user_id()); ?>
                             <span class="msg-userpic m--img-rounded m--marginless m--img-centered  img-circle m--pull-left" style="background-image:url(
        						<?php
        						  echo $user_helper->render_profile_picture ( $user_helper->user_image );        
        						?>);"> 
        					 </span>
					</div>
                </div>
		        <div class="col-xl-11 col-lg-11 col-md-11">
				    <form class="message-deatils-form" action="<?php echo base_url();?>dbtables/message/add_message_discussion?return_url=<?php echo $return_url; ?>" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="message_id" value="<?php echo  $message_id;?>" >
						<input type="hidden" name="notification_option" value="specific_people" >
					   
						<div id="holder">
							  	<?php /*?><?php $user_helper = BUserHelper::get_instance(); ?>							  
							   <div class="message-details-author">
							  		<span class="img-circle pull-left" style=" margin-right:10px; width:50px; height:50px; background-image:url(
            						<?php
            						  echo $user_helper->user_image;        
            						?>); background-size:cover; background-repeat:no-repeat; background-position:center center;"> </span>
							  </div><?php */?>
							   <div class="checkeditor-message-details">
							   <div class="visible-comment-box">
								 <p class="comment-placeholder"><?php echo dashboard_lang('_ADD_A_COMMENT_OR_UPLOAD_A_FILE');?></p>
							  <div class="visible-submit-details">
							  <textarea class="form-control ckeditor add_message_comment" style="display: inline;" name="msg_discussion" id="editor1" rows="4" cols="30"> </textarea>
							  <?php $this->load->view("metrov5_4/core_metrov5_4/messages/sub_views/file-upload-area", array("entity_id" => $message_details[0]['entity_id']));?>

						  <div id="status"> </div>

							  <?php
							  $check_all_people_selected =  check_all_people_selected ($message_id);
                              $list_of_people = get_list_of_thread_people ($message_id);
                              
                              $allUsersLists = [];
                              foreach ( $list_of_people as $eachUser ) {

                                if ( intval( $eachUser) > 0 && !in_array( $eachUser, $allUsersLists ) ) {
            
                                    $allUsersLists[] = $eachUser;
                                }
                            }
                              $lastConversations  = end($message_details) ;
							  $lastId = $lastConversations['comment_user_id'];
                              $list_of_people = get_list_of_thread_people ($message_id, $lastId);

							  ?>

							  <span class="change"> <?php echo dashboard_lang('_YOUR_COMMENT_WILL_BE_EMAILED_TO');  ?>  <?php if(!empty($users_list)){echo  $users_list;} else{ echo "( ".dashboard_lang("_NONE")." )";} ?>  <a href="javascript:void(0);" style="text-decoration:underline;"> (<?php echo dashboard_lang('_CHANGE');?>) </a> </span>
							  
							  <div class="specific_people" style="display:none;">
								  <div class="row user-selecion">
								     <div class="col-xl-12 col-lg-12 col-md-12">
								     	<span style="font-weight:bold;font-size:14px;display:block;margin-top:15px;"> <?php echo dashboard_lang('_EMAIL_THIS_MESSAGE_TO_PEOPLE_ON_THE_PROJECT');?> </span>
								     	<a href='javascript:void(0);' id='select_all'> <?php echo dashboard_lang('_SELECT_ALL');?></a>&nbsp;|&nbsp;
								  		<a href='javascript:void(0);' id='select_none'><?php echo dashboard_lang('_SELECT_NONE');?> </a>
								     </div>
								  </div>
								  
							      <div class="row selected-users-lists">
                                    <?php  
                                     $insertData['user_list'] = $user_list;
                                     $insertData['list_of_people'] = $list_of_people;

                                     $this->load->view("metrov5_4/core_metrov5_4/messages/sub_views/notification-users-list", $insertData);
                                     ?>
								  </div>	   
							  </div>
							<input type="button" data-message-id="<?php echo  $message_id;?>" value="<?php echo dashboard_lang('_SUBMIT');?>" class="btn btn-accent m-btn m--margin-top-30 submit-message">
								
								</div>
								</div>
							</div>
						</div> <!--End holder-->
					</form>
				</div>
			</div>
		</div>
		<!--End inbox-content-->
	</div>
	<!--End inbox body-->
</div>
<!--End inbox message-details-->
<script>
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    var instance = CKEDITOR.instances['editor1'];
    if(instance)
    {
    	instance.removeAllListeners();
    	CKEDITOR.remove(instance);
        CKEDITOR.replace( 'editor1' );
    }
</script>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/drag_and_drop.js"></script>
<script>

$(".visible-comment-box").click(function () {
    $(".visible-comment-box .visible-submit-details").show();
    $(".visible-comment-box .comment-placeholder").hide();
});

    $('.choose_people').hide();
    $('.delete_file').hide();
    $('#remove_file').hide();
    $('body .change').on('click',function(){

        $('body div.choose_people').show();
        $('body div.specific_people').show();
        $('body .change').hide();
    });

$("div.message-details").on("click", "a.delete_msg_conversation" , function(){
	
    var conversion_id = $(this).attr('data-id');

    var delete_url =  "<?php echo base_url();?>dbtables/message/delete_conversation";
    var show_delete_confirmation_msg = confirm("<?php echo dashboard_lang('_ARE_YOU_SURE'); ?>");
    var current_element = this;
    $('.msg-ajax-loader').show();

    if (show_delete_confirmation_msg){

        $.ajax({
            url: delete_url,
            type: 'post',
            data: "conversion_id="+conversion_id,
            success: function (result) {

                $('.msg-ajax-loader').hide();
                $(current_element).parent().parent().parent().parent().parent().remove()
            }
        });

    }
    $('.msg-ajax-loader').hide();

});


    $('body').on('click','#remove_file',function(){

        $('body #upload_file').val('');
        $('body .upload_file_name').html('');
        $('body #remove_file').hide();
        $('body #upload_image').hide();
        $('body i.fa-2x').hide();

    });
    
    $('body #select_all').on('click',function(){

        $('body input.people_checkbox').each(function(){

            $(this).attr('checked','checked');
            $('body input.people_checkbox').prop('checked',true);
            $(this).parent().addClass('checked');
        });

    });



    $('body #select_none').on('click',function(){

        $('body input.people_checkbox').each(function(){

            $(this).removeAttr('checked');
            $(this).parent().removeClass('checked');
        });

    });

    var checked_value =  $('input:radio[name=notification_option]').filter(":checked").val();


    if(checked_value == 'all') {

        $('.specific_people').hide();
    } if (checked_value == 'specific_people') {

        $('.specific_people').show();
    }


    $('body .notification').on('change',function(){

        var checked_value =  $('input:radio[name=notification_option]').filter(":checked").val();

        if(checked_value == 'all') {

            $('.specific_people').hide();
        } else {

            $('.specific_people').show();
        }

    });

    $('a.delete_msg_thread').on('click',function(){

    	var thread_id = $(this).attr('data-thread-id');
    	var confirm_delete = confirm("<?php echo dashboard_lang('_ARE_YOU_SURE?'); ?>");
        var thread_delete_url = "<?php echo base_url(); ?>dbtables/message/delete_msg_thread";    

        if ( confirm_delete ) {

       	 $('.msg-ajax-loader').show();
       	 
       	 $.ajax({
             url: thread_delete_url,
             type: 'post',
             data: "thread_id="+thread_id,
             success: function (result) {

                 $('.msg-ajax-loader').hide();
                 window.location.replace(  window.location.href );
             }
          });
         
         }    
   	   
     });



</script>


<!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<link href="<?php echo CDN_URL; ?>media_metrov5_4/custom/css/components.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo autoversion('media_metrov5_4/portal_core/css/custom.css'); ?>" rel="stylesheet" type="text/css"/>

<!-- END THEME STYLES -->
