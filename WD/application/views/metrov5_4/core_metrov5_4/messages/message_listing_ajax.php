<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
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
            <a class="go-to-msg-detail" data-messsage-id="<?php echo $messages['id']; ?>" class="go_to_message_details" href="javascript:void(0);">
            <td class="message-img-title">
                
                     <?php 
                        $user_helper = BUserHelper::get_instance();
                        $message_user_image = $user_helper->render_profile_picture($messages['image']);             

                      ?>              
                    <span class="msg-userpic m--img-rounded m--marginless m--img-centered  img-circle m--pull-left" style="background-image:url(
                    <?php
                      echo $message_user_image;        
                    ?>);"> 
                    </span>
               
            </td>
            <td class="message-username">
               
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
            <td class="" style="padding:0;">
            </td>
                </a>
        </tr>
    <?php } ?>
    </tbody>
</table>

