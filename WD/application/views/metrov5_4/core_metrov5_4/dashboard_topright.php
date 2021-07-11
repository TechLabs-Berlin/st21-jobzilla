<?php if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' ); ?>
<?php $user_helper = BUserHelper::get_instance(); 
$showQuickActions = $this->config->item("#SHOW_QUICK_ACTIONS");
$showNotificationBell = $this->config->item("#SHOW_NOTIFICATIONS_BELL");
$bgTheme = Bg_theme::getSelectedBgTheme();
if ( $bgTheme == "_DARK" ) { ?>
    <style>
        .m-topbar .m-topbar__nav.m-nav>.m-nav__item.m-topbar__user-profile>.m-nav__link .m-topbar__username .m-link, .m-topbar .m-topbar__nav.m-nav>.m-nav__item.m-topbar__user-profile>.m-nav__link .m-topbar__welcome, .branch-name, .m-footer .m-footer__copyright {
            color: #333 !important;
        }
    </style>
<?php } ?>
<ul class="m-topbar__nav m-nav m-nav--inline">
	<?php if(!empty($showNotificationBell) && $showNotificationBell == 1):?>
	<li class="m-nav__item m-topbar__notifications m-topbar__notifications--img m-dropdown m-dropdown--large m-dropdown--header-bg-fill m-dropdown--arrow m-dropdown--align-center 	m-dropdown--mobile-full-width" data-dropdown-toggle="click" data-dropdown-persistent="true">
		<a href="#" class="m-nav__link m-dropdown__toggle" id="m_topbar_notification_icon"> 
    		<span class="m-nav__link-badge m-badge m-badge--dot m-badge--dot-small m-badge--danger"></span>
    			<span class="m-nav__link-icon"> 
    			   <span class="m-nav__link-icon-wrapper">
    			     <i class="flaticon-music-2"></i>
    			   </span>
    		   </span>
    	</a>
    	<div class="m-dropdown__wrapper" id="notification-menu-bar">
    		<?php
    		  if($this->config->item ( 'messages' ) != 0){
                $html = render_all_notification_list ($this->session->userdata('user_id'));
            	echo $html;
              }
            ?>
        </div> 
	</li>
	<?php endif; ?>
	<?php if(!empty($showQuickActions) && $showQuickActions == 1):?>
	<li class="m-nav__item m-topbar__quick-actions m-topbar__quick-actions--img m-dropdown m-dropdown--large m-dropdown--header-bg-fill m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push m-dropdown--mobile-full-width m-dropdown--skin-light"  data-dropdown-toggle="click">
		<a href="#" class="m-nav__link m-dropdown__toggle">
			<span class="m-nav__link-badge m-badge m-badge--dot m-badge--info m--hide"></span>
			<span class="m-nav__link-icon">
			   <span class="m-nav__link-icon-wrapper"> <i class="flaticon-share"></i> </span>
			</span>
		</a>
		<div class="m-dropdown__wrapper">
			<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
			<div class="m-dropdown__inner">
				<div class="m-dropdown__header m--align-center" style="background: url(<?php echo CDN_URL;?>media_metrov5_4/assets/app/media/img/misc/quick_actions_bg.jpg); background-size: cover;">
					<span class="m-dropdown__header-title">
						Quick Actions
					</span>
					<span class="m-dropdown__header-subtitle">
						Shortcuts
					</span>
				</div>
				<div class="m-dropdown__body m-dropdown__body--paddingless">
					<div class="m-dropdown__content">
						<div class="m-scrollable" data-scrollable="false" data-max-height="380" data-mobile-max-height="200">
							<div class="m-nav-grid m-nav-grid--skin-light">
								<div class="m-nav-grid__row">
									<a href="#" class="m-nav-grid__item">
										<i class="m-nav-grid__icon flaticon-file"></i>
										<span class="m-nav-grid__text">
											Generate Report
										</span>
									</a>
									<a href="#" class="m-nav-grid__item">
										<i class="m-nav-grid__icon flaticon-time"></i>
										<span class="m-nav-grid__text">
											Add New Event
										</span>
									</a>
								</div>
								<div class="m-nav-grid__row">
									<a href="#" class="m-nav-grid__item">
										<i class="m-nav-grid__icon flaticon-folder"></i>
										<span class="m-nav-grid__text">
											Create New Task
										</span>
									</a>
									<a href="#" class="m-nav-grid__item">
										<i class="m-nav-grid__icon flaticon-clipboard"></i>
										<span class="m-nav-grid__text">
											Completed Tasks
										</span>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</li>
	<?php endif;?>
	
	<li class="m-nav__item m-topbar__user-profile m-topbar__user-profile--img  m-dropdown m-dropdown--medium m-dropdown--arrow m-dropdown--header-bg-fill m-dropdown--align-right m-dropdown--mobile-full-width m-dropdown--skin-light" data-dropdown-toggle="click">
		<a href="#" class="m-nav__link m-dropdown__toggle"> 
			<span class="m-topbar__welcome m--hidden-tablet m--hidden-mobile"><?php echo dashboard_lang('_HELLO');?>&nbsp;</span>
			<span class="m-topbar__username m--hidden-tablet m--hidden-mobile m--padding-right-15">
				<span class="m-link">
					<?php echo @$user_helper->user->first_name ;?>
				</span>
			</span>
			<span class="m-topbar__userpic">
				
				<span class="m--img-rounded m--marginless m--img-centered img-circle pull-left" style=" margin-right:10px;margin-top:-10px;width:45px; height:45px; background-image:url(<?php echo $user_helper->user_image; ?>); background-size:cover; background-repeat:no-repeat; background-position:center center;"></span> 
				
			</span> 
			
		</a>
		<div class="m-dropdown__wrapper">
			<span
				class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
			<div class="m-dropdown__inner">
			
				<div class="m-dropdown__header m--align-center" style="background: url(<?php echo CDN_URL; ?>media_metrov5_4/assets/app/media/img/misc/user_profile_bg.jpg); background-size: cover;">
					<div class="m-card-user m-card-user--skin-dark">
						<div class="m-card-user__pic">
							<span class="m--img-rounded m--marginless m--img-centered img-circle pull-left" style=" margin-right:10px;margin-top:-10px;width:80px; height:80px; background-image:url(<?php echo $user_helper->user_image; ?>); background-size:cover; background-repeat:no-repeat; background-position:center center;"></span>
						</div>
						<div class="m-card-user__details">
							<span class="m-card-user__name m--font-weight-500"> <?php echo @$user_helper->user->first_name .' '.@$user_helper->user->last_name; ?> </span>
							<a href="mailto:<?php echo $user_helper->user->email; ?>" class="m-card-user__email m--font-weight-300 m-link">
								<?php echo $user_helper->user->email; ?> 								
							</a><br>
							<?php
							$showUserRole = strtolower($this->config->item('#CORE_USER_ROLE_SHOW'));
							$userRole = strtoupper( get_user_role()); 
							if( $showUserRole == 'yes'){  ?>
								<span class="m-badge custom-user-role-badge"><?php echo dashboard_lang('_'.$userRole);?></a>
							<?php  }?>	
						
						</div>
					</div>
				</div>
				<div class="m-dropdown__body">
					<div class="m-dropdown__content">
						<ul class="m-nav m-nav--skin-light">
							<li class="m-nav__section m--hide">
								<span class="m-nav__section-text"> Section </span>
							</li>
							<li class="m-nav__item">
								<a href="<?php echo $site_url.'dashboard/home';?>" class="m-nav__link"> 
									<i class="m-nav__link-icon la la-home"></i>
									<span class="m-nav__link-text"> <?php echo dashboard_lang('_HOME')?> </span>
								</a>
							</li>
							<li class="m-nav__item">
								<a href="<?php echo $site_url.'dashboard/profile_settings';?>" class="m-nav__link"> 
									<i class="m-nav__link-icon flaticon-user-settings"></i> 
    								<span class="m-nav__link-title"> 
    									<span class="m-nav__link-wrap"> 
    										<span class="m-nav__link-text"> <?php echo dashboard_lang('_PROFILE_SETTINGS'); ?> </span>
    									</span>
    								</span>
								</a>
							</li>
							<li class="m-nav__item">
								<a href="<?php echo $site_url.'dashboard/change_password';?>" class="m-nav__link"> 
									<i class="m-nav__link-icon flaticon-lock"></i>
									<span class="m-nav__link-text"> <?php echo dashboard_lang('_CHANGE_PASSWORD')?> </span>
								</a>
							</li>
							
							<li class="m-nav__separator m-nav__separator--fit"></li>
							<li class="m-nav__item">
								<a href="<?php echo $site_url.'dashboard/logout';?>" class="btn m-btn--pill btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder"> <?php echo dashboard_lang('_LOGOUT')?> </a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</li>
	
</ul>
