<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
			<span class="m-dropdown__arrow m-dropdown__arrow--center"></span>
			<div class="m-dropdown__inner">
				<div class="m-dropdown__header m--align-center"
					style="background: url(<?php echo base_url(); ?>media_metrov5_4/assets/app/media/img/misc/notification_bg.jpg); background-size: cover;">
					<span class="m-dropdown__header-title"> <?php  if(sizeof($notification_list)>0){ echo sizeof($notification_list); } ?></span> <span
						class="m-dropdown__header-subtitle"> <?php  echo dashboard_lang("_USER_NOTIFICATIONS"); ?> </span>
				</div>
				<div class="m-dropdown__body">
					<div class="m-dropdown__content">
						<ul class="nav nav-tabs m-tabs m-tabs-line m-tabs-line--brand"
							role="tablist">
							<li class="nav-item m-tabs__item"><a
								class="nav-link m-tabs__link active" data-toggle="tab"
								href="#topbar_notifications_notifications" role="tab"> <?php echo dashboard_lang('_MESSAGES');?> </a>
							</li>

							<!-- user event log tab -->
							<li class="nav-item m-tabs__item"><a
								class="nav-link m-tabs__link event_log_tab_in_notification_area" data-toggle="tab"
								href="#topbar_event_log_notifications" role="tab"> <?php echo dashboard_lang('_LOG');?> </a>
							</li><!-- end user event log tab -->

						</ul>
						<div class="tab-content">
						<?php  if(sizeof($notification_list)>0) { ?>
							<div class="tab-pane active" id="topbar_notifications_notifications" role="tabpanel">
								<div class="m-scrollable" data-scrollable="true"
									data-max-height="250" data-mobile-max-height="200">
									<div class="m-list-timeline m-list-timeline--skin-light">
										<div class="m-list-timeline__items">
										<?php foreach ($notification_list as $notification) { ?>
											<?php 
                                                if($notification['message_entity'] == "discussions"){
                                                    $url = base_url()."dbtables/message/details/".$notification['details_message_id'];
                                                }else{
                                                    $tab_position = get_tab_position($notification['message_entity']);
                                                    $url = base_url()."dbtables/".$notification['message_entity']."/edit/".$notification['entity_id']."/".$tab_position."/".$notification['details_message_id']."?s=l";
                                                }
                                            ?>
											<div class="m-list-timeline__item message-item" data-message-id="<?php echo $notification['message_id']; ?>">
												<span class="m-list-timeline__badge m-list-timeline__badge--state1-success"></span>
												<a href="<?php echo $url;?>" class="m-list-timeline__text"><?php echo html_entity_decode($notification['messages']);?></a>
												<span class="m-list-timeline__time"><?php echo $notification['time_difference'];?> <?php echo dashboard_lang('_AGO');?></span>
											</div>
										<?php }?>
										</div>
									</div>
								</div>
							</div>
						<?php } else { ?>
							<div class="tab-pane active" id="topbar_notifications_notifications" role="tabpanel">
								<div class="m-stack m-stack--ver m-stack--general" style="min-height: 180px;">
									<div class="m-stack__item m-stack__item--center m-stack__item--middle">
										<span class=""> <?php echo dashboard_lang('_ALL_CAUGHT_UP_NO_NEW_NOTIFICATIONS'); ?> </span>
									</div>
								</div>
							</div>
						<?php } ?>

						<!-- TAB FOR EVENT LOG -->
							<div class="tab-pane" id="topbar_event_log_notifications" role="tabpanel">
								<div class="m-scrollable" data-scrollable="true"
									data-max-height="250" data-mobile-max-height="200">
									<div class="m-list-timeline m-list-timeline--skin-light">
										<div class="m-list-timeline m-list-timeline--skin-light event_log_palet">
										
										</div>
										<div class="m-list-timeline__items">
											<br>
											<a href="#" id="notification_area_more_event_log" data-offset='0' data-limit='5' class="m--align-center"><?php echo dashboard_lang('_MORE_EVENT_LOG');?></a>
										</div>
									</div>
								</div>
							</div> <!-- TAB FOR EVENT LOG END-->

						</div>
					</div>
				</div>
			</div>
