<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="discuss-with-btn">
            <button class="btn btn-accent add_message add-message-back-btn" type="button"><?php echo dashboard_lang('_BACK');?></button>
        </div>
<div class="msg-user-info">
		<?php 
		
		  $user_helper = BUserHelper::get_instance();
		  $propciname = $user_helper->user->image;
		  $message_user_image = $user_helper->render_profile_picture($propciname);                
          $yesToAll = $this->config->item('#MODULE_MESSAGE_SEND_NOTIFICATION_TO_ALL');
         ?>	
        
        <span class="msg-userpic m--img-rounded m--marginless m--img-centered  img-circle m--pull-left" style="background-image:url(<?php echo $message_user_image;?>);"> </span>
        <span class="msg-username m--margin-left-15"><?php echo $user_helper->user->first_name." ".$user_helper->user->last_name;?></span>
</div>   
        <form id="add_message" action="<?php echo base_url();?>dbtables/message/insert_message" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="message_type" value="<?php echo $messages_type;?>" >
            <input type="hidden" name="entity_id" value="<?php echo $entity_id; ?>">
            <textarea type="text" id = "message_title" class="form-control msg_title" rows="1" cols="47" name="msg_title" placeholder="<?php echo dashboard_lang('_TITLE');?>" required></textarea>
            <br/>
            <div id="holder">
                <textarea class="form-control ckeditor add-desc-message" id="editor1" name="description" placeholder="<?php echo dashboard_lang('_DESCRIPTION');?>" id="editor" rows="4" cols="38"> </textarea>
                <?php $this->load->view("metrov5_4/core_metrov5_4/messages/sub_views/file-upload-area", array("entity_id" => $entity_id));?>
            </div>

            <div id="status">

            </div>
            <div class="check-box-with-btn-area m-radio-list m--margin-top-30">
                <?php
                $check_all_people_selected =  check_all_people_selected ($message_id);
                $list_of_people = get_list_of_thread_people ($message_id);                
                $msgOption1 = "";
                $msgOption2 = "";
                $msgOption3 = "";
                $msgOption4 = "";
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
                <?php }?>
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
                           $insertData['user_list'] = $user_list;
                           $insertData['list_of_people'] = [];
                           $this->load->view("metrov5_4/core_metrov5_4/messages/sub_views/notification-users-list", $insertData);
                       ?>
                    </div> 
                </div>

                <label class="m-radio">
				    <input type="radio" name="notification_option" class="notification" id="notification" name="notification_option" class="notification form-control" id="notification" value="no_people" <?php echo $msgOption3;?>> 
                    <?php echo dashboard_lang('_DO_NOT_EMAIL_ANYONE');?>
                    <span></span>
                </label>
                <label class="m-radio">
				    <input type="radio" name="notification_option" class="notification" id="notification" name="notification_option" class="notification form-control" id="notification" value="external_people" <?php echo $msgOption4;?>> 
                    <?php echo dashboard_lang('_SEND_MAIL_TO_EXTERNAL_PEOPLE');?>
                    <span></span>
                </label>
                <div class="external_people" style="display:none;">
                  
                        <input type="text" name="external_people" id="external_people" value="" class="form-control">
                  
                    
                </div>
                
            </div>
            <input type="button"  class='btn btn-accent m-btn add_message_button m--margin-top-30' value="<?php echo dashboard_lang('_SUBMIT'); ?>">


        </form>

<script>
    CKEDITOR.replace( 'editor1' );
</script>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/drag_and_drop.js"></script>
