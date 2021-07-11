<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php $this->load->helper('portal/user');
$user_instance = BUserHelper::get_instance(); 
$CI = &get_instance();
?>
<?php  $msg_count = 0; foreach ($message_details as $details) { ?>
	<?php if ($details['conversation_id'] == $current_conversation_id){?>
       <div class="inbox-view-info">
			<div class="row">
                        <div class="col-xl-1 col-lg-1 col-md-1">
						  <div class="inbox-author-photo">
						  	<?php $meesenger_pro_pic = $user_instance->render_profile_picture($details['image']); ?>
							<span class="msg-userpic m--img-rounded m--marginless m--img-centered  img-circle m--pull-left" style="background-image:url(
    						<?php echo $meesenger_pro_pic; ?>);"> </span>
						  </div>
                        </div>
				        <div class="col-xl-11 col-lg-11 col-md-11">
                                <?php
                                 $commentData = [];
                                 $commentData['name'] = $details['first_name']." ".$details['last_name'];
                                 $commentData['user_id'] = $this->session->userdata('user_id');
                                 $commentData['message_id'] = $details['messages_id'];
                                 $commentData['name'] = $details['first_name']." ".$details['last_name'];
                                 $commentData['comment_user_id'] = $details['comment_user_id'];
                                 $commentData['message_conversation_details'] = $details['message_conversation_details'];
                                 $commentData['post_datetime'] = $details['post_datetime'];
                                 $commentData['conversation_id'] = $details['conversation_id'];
                                 $commentData['check_msg_count'] = 0;
                                 $commentData['msg_count'] = $msg_count;
                                 $commentData['file_details'] = $details['file_details'];

                                 $list_of_people = get_sent_to_people ( $details['conversation_id']);
                                 $sent_to = $CI->Messages_model->get_users_list ($list_of_people);
                                 $commentData['sent_to'] = $sent_to;
                                 $this->load->view("metrov5_4/core_metrov5_4/messages/sub_views/each-msg-comment", $commentData);
                                 
                                ?>
                        </div>
               </div>
				<!--end inbox-view-info 1-->
		  </div>
		  <div class="col-md-4 inbox-info-btn">

		  </div>

        <?php } ?>
	<?php } ?>

<link href="<?php echo CDN_URL; ?>media_metrov5_4/custom/css/components.css" rel="stylesheet" type="text/css"/>
