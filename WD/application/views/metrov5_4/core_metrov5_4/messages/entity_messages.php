<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="msg-ajax-loader" style="display: none;">
    <div class="m-loader m-loader--brand" style="width: 30px; display: inline-block;"></div>
</div>
  <div class="message_details">
  <?php      $yesToAll = $this->config->item('#MODULE_MESSAGE_SEND_NOTIFICATION_TO_ALL');   ?>
     </div>
    <div class="messages_list" >
        <div class="discuss-with-btn">
            <button class="btn btn-accent m-btn messages_list" type="button"><?php echo dashboard_lang('_ADD_MESSAGE');?></button>
        </div>

        <table class="table table-striped table-advance table-hover">
            <thead>
            </thead>
            <tbody>

            <?php foreach ($all_message_list as $messages) { ?>

                <?php
                $lenght = $this->config->item('message_title_length');
                if(strlen($messages['msg_title']) > $lenght ){
                    $msg_title = substr($messages['msg_title'],0,$lenght)." ...";
                } else{
                    $msg_title = $messages['msg_title'];
                }
                ?>

                <tr data-messageid="1" data-messsage-id="<?php echo $messages['id']; ?>" class="clickable-row <?php  if($messages['notification_exists']) { echo "unread"; } ?> " data-href='javascript:void(0);'>
                    <a  data-messsage-id="<?php echo $messages['id']; ?>" class="go_to_message_details" href="javascript:void(0);">
                    <td class="message-img-title">
                  
                    
                    <?php
                    
                        $user_helper = BUserHelper::get_instance();
                        $message_user_image = $user_helper->render_profile_picture($messages['image']);
        
                    ?> 
                	<span class="msg-userpic m--img-rounded m--marginless m--img-centered  img-circle m--pull-left" style="background-image:url(
        			<?php
        			  echo $message_user_image;        
        			?>);"> </span>
                 
                    </td>
                    <td class="message-username" valign="middle">
                        <?php echo $messages['user_name']; ?>
                    </td>
                    <td class="message-title-desc"> 
                        <?php echo "<span class='msg_title'><u>".$msg_title."</u></span></a>";echo "<div class='last_comment'>".substr($messages['last_comment'],0,$this->config->item('str_length'))."</div>";  ?>
                    </td>
                    <td class="message-file">
                       <?php if ( $messages['files_exists']) { ?><i class="fa fa-paperclip"></i> <?php } ?>
                    </td>
                    <td class="conversation-time">
                        <?php echo format_date_time ($messages['last_conversion_time']); ?>
                    </td>
                    <td align="left" class="conversation-count">
                        <span class="m-badge m-badge--info"><?php echo $messages['discussion_count']-1; ?></span>
                    </td>
                    <td class="" style="padding:0;"></td>
                    </a>
                </tr>
            <?php } ?>
            </tbody>
        </table>


    </div>
    </form>
    <div class="add_message" style="display: none;">
        <div class="discuss-with-btn">
            <button class="btn btn-brand m-btn add_message add-message-back-btn" type="button"><?php echo dashboard_lang('_BACK');?></button>
        </div>
		<?php 
		  $user_helper = BUserHelper::get_instance();
		  $img_pro = $user_helper->user->image;
		  if( file_exists( FCPATH . "uploads/profile_image/" . $img_pro ) && strlen( $img_pro ) > 0 ){
		      $img_path = "uploads/profile_image/$img_pro";
		      $def_img = $img_pro;
		  } else {
		      $def_img = $this->config->item("default_avater_url");
		      $img_path = $def_img;
		  }
		?>
		<span class="msg-userpic m--img-rounded m--marginless m--img-centered  img-circle m--pull-left" style="background-image:url(
        			<?php
        			   echo base_url( $img_path );       
        			?>);"> </span>
         <?php echo $user_helper->user->first_name." ".$user_helper->user->last_name;?>
        <form id="add_message" action="<?php echo base_url();?>dbtables/message/insert_message" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="message_type" value="<?php echo $messages_type;?>" >
            <input type="hidden" name="entity_id" value="<?php echo $this->uri->segment($id_position); ?>">
            <input type="hidden" name="is_came_from_contract" value="false" >
            <input type="hidden" name="return_url" value="return_url=<?php echo base_url();?><?php echo $current_url;?><?php echo $this->uri->segment($id_position); ?>/message">
            <input type="hidden" name="rental_object" value="<?php if( @$is_copy_contract) { echo  @$rental_object; }else if(strlen( @$contract_details[0]['object_id']) >1) {echo @$contract_details[0]['object_id'];}else { echo  @$rental_object; } ?>" >
            <input type="hidden" name="start_date" value="<?php if( isset($is_copy_contract) AND $is_copy_contract) { if(isset($start_date)): echo $start_date; endif; }else if( isset($contract_details[0]['start_date']) AND !empty($contract_details[0]['start_date'])) {echo $contract_details[0]['start_date'];}else { if(isset($start_date)): echo $start_date; endif; } ?>" >
            <textarea class="form-control msg_title" rows="1" cols="47" name="msg_title" placeholder="<?php echo dashboard_lang('_TITLE');?>" required></textarea>
            <br/>
            <div id="holder">
                <textarea class="form-control ckeditor add-desc-message" name="description" placeholder="<?php echo dashboard_lang('_DESCRIPTION');?>" id="editor1" rows="4" cols="38"> </textarea>
                <div class="file-upload-area">
                    <i class="fa fa-paperclip"></i>
                    <?php echo dashboard_lang('_DRAG_FILE_HERE');?> <?php echo dashboard_lang('_OR');?> <a href="javascript:void(0);" id="upload_link"> <?php echo dashboard_lang('_CLICK_HERE_TO_UPLOAD_FILE'); ?></a>
                   <span class="upload-file-entry-messages">
                    <image id="upload_image" src="" style="display: none;" width="50"> <i style="display: none;" class="fa fa-file fa-2x"></i> <span class="upload_file_name"> </span> <span class="delete_file">&nbsp; <a href="javascript:void(0);" id="remove_file"> <?php echo dashboard_lang('_REMOVE_FILE');?></a></span>
                        <input type="file" style="display:none;" name="userfile" id="upload_file" class="message-deatils-upload-btn">
                        </span>
                </div>
            </div>

            <div id="status">

            </div>
            <div class="check-box-with-btn-area">
                <?php
                
                $msgOption1 = "";
                $msgOption2 = "";
                $msgOption3 = "";
                $spacificPeoplesList = "none";
                
                if(isset($message_id)){
                    $check_all_people_selected =  check_all_people_selected ($message_id);
                    $list_of_people = get_list_of_thread_people ($message_id);                    
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
                }
                ?>
               <?php if( $yesToAll == 1){?> <input type="radio" name="notification_option" class="notification" id="notification" value="all"  <?php echo $msgOption1;?>><?php echo dashboard_lang('_SEND_NOTIFICATION_TO_ALL');?>  <?php } ?>
                <br/>
                <input type="radio" name="notification_option" class="notification" id="notification"   value="specific_people" <?php echo $msgOption2;?>><?php echo dashboard_lang('_SEND_NOTIFICATION_TO_SPECIFIC_PEOPLE');?>

                <br/>

                <div class="specific_people" style="display:<?php echo $spacificPeoplesList;?>;">
                <div class="row">
                           <div class="col-sm-6">
                    <a href='javascript:void(0);' id='select_all'> <?php echo dashboard_lang('_SELECT_ALL');?></a>&nbsp;|&nbsp;<a href='javascript:void(0);' id='select_none'><?php echo dashboard_lang('_SELECT_NONE');?> </a> <br/>
                    
                    <?php             
                   $people_list = array();
                   if(isset($list_of_people) AND sizeof($list_of_people) > 0){
                       $people_list = $list_of_people;
                   }
                   
                    foreach ($user_list as $user_list) {?>
                      <div class="col-sm-4" style="min-height:25px;padding-left:5px;">
                        <input type="checkbox" class="people_checkbox" name="people[]" value="<?php echo $user_list['id']; ?>"  <?php if (in_array($user_list['id'] , $people_list)) {echo "checked";}?>> <?php echo $user_list['first_name']." ".$user_list['last_name']; ?>
					  </div>
                    <?php } ?>
                </div>
                 </div>
                  </div>

                <input type="radio" name="notification_option" class="notification" id="notification" name="notification_option" class="notification form-control" id="notification" value="no_people" <?php echo $msgOption3;?>><?php echo dashboard_lang('_DO_NOT_EMAIL_ANYONE');?>

                <br/>
            </div>
            <input type="button"  class='btn green add_message_button' value="<?php echo dashboard_lang('_SUBMIT'); ?>">


        </form>
    </div>



<link href="<?php echo CDN_URL; ?>media_metrov5_4/custom/css/components.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/custom/css/custom_messages.css" rel="stylesheet" type="text/css"/>
