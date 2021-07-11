<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>



















<?php 
$event_log_list= $data['logs'];
// var_dump($data);
  if(count($event_log_list)>0) { ?>
			<?php foreach ($event_log_list as $log) { ?>

				<div class="m-list-timeline__items">
					<!-- <div class="panel-heading">Panel Heading</div> -->
					<div class="m-list-timeline__item"> 
							<span class="m-list-timeline__badge -m-list-timeline__badge--state-success"></span>
							


								<?php $action= trim(strtolower($log->action)); ?> 

								<?php 
								if( $action=='user_login' ||  $action=='user_logout')
								{
									$display='';
								}
								else{
									$display  = dashboard_lang('_RECORD' ).' ' ;
								}
								?> 
								<?php $display .= dashboard_lang('_'.strtoupper($log->action) ).' ' ?> 
								<?php if( $action!='user_login' &&  $action!='user_logout'){ 
										$display .= dashboard_lang('_IN' ).' ';
										$display .= dashboard_lang('_'.strtoupper($log->table_name) ).' ';
									 } 
								?>
								
								<?php $show_short= $display;?>

								<?php $datetime = date($data['edit_date_format'],  $log->time)?>
								<?php $display .= dashboard_lang('_ON' ).' ' .$datetime ?> 
								<?php $get_time_difference = difference_between_current_and_given_time($log->time) . " " . dashboard_lang("_AGO").' ';?>

								<?php
									$not_linkable_table_name = ['message','message_conversation_details'];
									$linkable_actions= ['add','edit'];
									if( ! in_array( $log->table_name, $not_linkable_table_name) && in_array($action , $linkable_actions) ){ ?>
										<span class="m-list-timeline__text"  data-toggle="m-tooltip" title=''
										data-original-title="Popover title">
											<a href="<?php echo base_url().'dbtables/'.$log->table_name.'/edit/'.$log->row_id ?>">
												<?=$show_short?>
											</a>
										</span>

								<?php
									} else {?>

										<span class="m-list-timeline__text"  data-toggle="m-tooltip" title='' data-original-title="Popover title">
											<?=$show_short?>
										</span>
									<?php
									} ?>
						
						<!-- <br> -->
						<?php #echo ($log->message);?> 
						<!-- 
						<br>
						<br><div class="log_time">Time</div> -->
					 <?php #echo $log->time;?>

						<span class="m-list-timeline__time">
							<?=$get_time_difference?>
						</span>
					</div>
					<!-- <div class="panel-footer"></div> -->
				</div>
			<?php }?>	
<?php } else {?>
				
			<div class="m-list-timeline__item message-item" >
				<span class="m-list-timeline__time">
					No Event Log Found
				</span>
			</div>
	
	
<?php } ?>