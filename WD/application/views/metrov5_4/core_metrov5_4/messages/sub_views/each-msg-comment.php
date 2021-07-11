<?php 

$CI = &get_instance();
$CI->load->model('Messages_model');
$user_can_delete_msg = $CI->Messages_model->check_user_can_delete_msg($user_id, $message_id, $comment_user_id);

$checkUserCanDeleteMsg = false;

if (check_user_can_delete_msg() && $msg_count > 1 && $check_msg_count == '1') { 
    $checkUserCanDeleteMsg = true;
}else if ( $user_can_delete_msg && strlen($conversation_id) > 0 && $msg_count > 1 && $check_msg_count == '1') {
    $checkUserCanDeleteMsg = true;
}
$show_name = '';

 if($msg_count > 1 && $check_msg_count == '1'){
   $show_name = $name;
 }
 
 if( $check_msg_count == '0'){ 
     $show_name = $name;
 }
 
 ?> 


<div class="inbox-discussion-message">
 <span class="inbox-author-name"><?php echo $show_name;?></span>

 <div class="inbox-view">
	    <span class="msg_comment"><?php echo $message_conversation_details; ?></span>
		<span class="sent-to-info">   <?php  echo dashboard_lang("_SENT_TO")." "."<strong>".$sent_to ."</strong>"." ".format_date_time($post_datetime);?> </span>
	    <?php if ($checkUserCanDeleteMsg) { ?>
	       <a href="javascript:void(0);" data-id="<?php echo $conversation_id;?>"  class="delete_msg_conversation">
			 <?php echo dashboard_lang("_DELETE");?>
		   </a>
	    <?php } ?>
 </div>
 <?php foreach ( $file_details as $files ) {?>
      <?php $this->load->view("metrov5_4/core_metrov5_4/messages/sub_views/conversion-files", $files); ?>  
  <?php } ?>
</div>
