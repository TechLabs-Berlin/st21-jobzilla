<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<li>
    <ul class="dropdown-menu-list scroller" style="height: 275px;" data-handle-color="#637283">
       <?php foreach ($notification_list as $notification_list) { ?>
        <li class="message msg-<?php echo $notification_list['message_id'];?>" data-message-id="<?php echo $notification_list['message_id'];?>">
            <a href="<?php echo base_url();?>dbtables/message/details/<?php echo $notification_list['details_message_id'];?>">
            
            	<span class="photo">
            		<?php
            		  $user_instance = BUserHelper::get_instance();
            		  $message_user_image = $this->config->item("default_avater_url"); ?>
            		  
            		  <img src="<?php echo $message_user_image; ?>" class="img-circle" alt="">
            	</span>
    			<span class="subject">
    				<span class="from">
    					<?php echo $notification_list['user_name'];?>
    					<span class="time"><?php echo $notification_list['time_difference'];?> <?php echo dashboard_lang('_AGO');?></span>
    				</span>
    				<br>
    				<span class="message"><?php echo $notification_list['messages'];?></span>
    			</span>
    							
            </a>
        </li>
    
        <?php } ?>
    
    </ul>
</li>