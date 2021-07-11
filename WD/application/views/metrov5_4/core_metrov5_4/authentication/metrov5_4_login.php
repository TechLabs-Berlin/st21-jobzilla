<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/authentication/header.php');
?>
<script>
var error_msg = '<button class="close" data-close="alert"></button>';
error_msg += "<span><?php echo dashboard_lang ('_DASHBOARD_LOGIN_ERROR_MESSAGE');?></span>";
var BASE_URL = "<?php echo base_url();?>";
</script>
<!-- BEGIN LOGIN -->
<div id="ajax_loader" style="position: fixed;
    top: 0px;
    background: rgba(0, 0, 0, 0.25);
    height: 100%;
    width: 100%;
    z-index: 99999;display: none;">
	<img style="position: absolute;
    top: 50%;
    left: 50%;
    margin-left: -16px;
    margin-top: -16px;
    z-index: 9999999;" alt="" src="<?php echo CDN_URL;?>img/ajax-loader.gif">
</div>

<?php 
	$accentColor = $this->config->item('accent_color');
?>

<style>
	.m-login__container {
		border: <?php echo $accentColor;?> 1px solid;
	}
	button#submitLoginBtn, button#mLoginForgetPasswordSubmit, button#reset_submit{
		background-color: <?php echo $accentColor;?>; 
	}
	.m-checkbox.m-checkbox--light>span{
		border: 2px solid <?php echo $accentColor;?> !important;
	}
</style>
			
<div class="m-login__signin">
	<div class="m-login__head">
		<h3 class="m-login__title"><?php echo dashboard_lang('_LOGIN_TO_YOUR_ACCOUNT'); ?>  <?php echo (isset($account) && !empty($account) && isset($account['name'])) ? "- {$account['name']}" : ''; ?></h3>
	</div>
	<form class="m-login__form m-form" action="" method="post" >
		<?php if ($this->session->userdata("login_error")): ?>
			<div class="m-alert m-alert--outline alert alert-danger alert-dismissible" role="alert">			
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>			
				<span>
					<?php echo $this->session->userdata("login_error");
                     $this->session->unset_userdata("login_error"); ?>
                </span>		
			</div>
		<?php endif; ?>
		<?php $fa_alert_text = $this->session->flashdata("failed_attempt_alert"); 
			if( isset( $fa_alert_text ) ) { ?>
				<div class="m-alert m-alert--outline alert alert-danger alert-dismissible" role="alert">			
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>			
					<span><?php echo $fa_alert_text; ?></span>		
				</div>	
			<?php } ?>
			<div class="form-group m-form__group">
				<input class="form-control m-input"   type="text" placeholder="<?php echo dashboard_lang('_EMAIL_ADDRESS'); ?>" name="email" autocomplete="off" value="<?php echo @$this->session->userdata("user_creation_email");
				@$this->session->unset_userdata("user_creation_email");?>">
			</div>
			<div class="form-group m-form__group">
				<input class="form-control m-input m-login__form-input--last" type="password" placeholder="<?php echo dashboard_lang('_PASSWORD'); ?>" name="password">
			</div>
			<div class="form-group m-form__group">
				<?php
                    if ($this->session->userdata("blocked_ip")):
                        $this->session->unset_userdata("login_error"); ?>
                        <div class="g-recaptcha" data-sitekey="<?php echo $this->config->item("recaptcha_site_key");?>"></div>
                    <?php endif; ?>     
			</div>
			<div class="row m-login__form-sub">
				<div class="col-lg-8 m--align-left m-login__form-left">
					<?php $rememberVal = $this->config->item('#HIDE_REMEMBER_ME_LOGIN');
                        if($rememberVal == 1){ ?>
							<label class="m-checkbox  m-checkbox--light m--pull-left">
								<input type="checkbox" name="remember" value="1" /> <?php echo dashboard_lang('_KEEP_ME_LOGGED_IN'); ?> 
								<span></span>
							</label>
							<span style="" class=" m--padding-left-10 m--valign-middle m--pull-left"> 
								<a style="color:white;" class="btn btn-success m-btn m-btn--icon m-btn--icon-only m-btn--pill m--pull-left custom-info-button" data-trigger="click" data-html="true" data-animation="true" data-toggle="popover" data-content="<?php echo dashboard_lang("_REMEMBER_ME_LOGIN_CHECKBOX_INFO");?>" data-original-title="" title="">
									<i class="fa fa-info"></i>
								</a>
							</span>
					<?php } ?>
				</div>
				<div class="col m--align-right m-login__form-right">
					<a href="javascript:;" id="m_login_forget_password" class="m-link"><?php echo dashboard_lang('_PASSWORD_RESET'); ?></a>
				</div>
			</div>
					        <?php

                                $msg = $this->config->item('encryption_text');
                                $encrypted_string = $this->encryption->encrypt($msg);
                    
                            ?>
					 <input type="hidden" name="token" value="<?php echo $encrypted_string; ?>" />
					<div class="m-login__form-action">
						<?php echo  render_button ('', 'submitLoginBtn', 'btn btn-focus m-btn m-btn--pill m-btn--custom    m-login__btn m-login__btn--primary', 'submit' , dashboard_lang('_LOGIN'), '');?>
					</div>
				</form>
			</div>
			<div class="m-login__signup">
				<div class="m-login__head">
					<h3 class="m-login__title"><?php echo dashboard_lang("_SIGN_UP");?></h3>
					<div class="m-login__desc"><?php echo dashboard_lang("_ENTER_YOUR_DETAILS_TO_CREATE_YOUR_ACCOUNT");?></div>
				</div>
				<form class="m-login__form m-form" action="">
					<div class="form-group m-form__group">
						<input class="form-control m-input" type="text" placeholder="Fullname" name="fullname">
					</div>
					<div class="form-group m-form__group">
						<input class="form-control m-input" type="text" placeholder="Email" name="email" autocomplete="off">
					</div>
					<div class="form-group m-form__group">
						<input class="form-control m-input" type="password" placeholder="Password" name="password">
					</div>
					<div class="form-group m-form__group">
						<input class="form-control m-input m-login__form-input--last" type="password" placeholder="Confirm Password" name="rpassword">
					</div>
					<div class="row form-group m-form__group m-login__form-sub">
						<div class="col m--align-left">
							<label class="m-checkbox m-checkbox--light">
							<input type="checkbox" name="agree"><?php echo dashboard_lang("_I_AGREE_THE");?> <a href="#" class="m-link m-link--focus"><?php echo dashboard_lang("_TERMS_AND_CONDITIONS");?></a>.
							<span></span>
							</label>
							<span class="m-form__help"></span>
						</div>
					</div>
					<div class="m-login__form-action">
						<button id="m_login_signup_submit" class="btn m-btn m-btn--pill m-btn--custom   m-login__btn m-login__btn--primary"><?php echo dashboard_lang("_SIGN_UP");?></button>&nbsp;&nbsp;
						<button id="m_login_signup_cancel" class="btn m-btn m-btn--pill m-btn--custom   m-login__btn"><?php echo dashboard_lang("_CANCEL");?></button>
					</div>
				</form>
			</div>
			<?php $this->session->unset_userdata('forget_pass'); ?>
			<div class="m-login__forget-password" >
			    <?php  $p = $this->session->userdata("alert_success"); ?>
				<div class="m-login__head">
					<h3 class="m-login__title"><?php echo dashboard_lang('_FORGET_PASSWORD');?></h3>
					 <input type="hidden" name="forget_pass" value="forget_pass_submit" />
					 <?php if($p != 1){ ?>
					     <div class="m-login__desc"><?php echo dashboard_lang('_ENTER_YOUR_EMAIL_ADDRESS_BELOW_TO_RESET_YOUR_PASSWORD'); ?></div>
					 <?php } ?>
				</div>
				
				<div class="m-alert m-alert--outline alert alert-info m--margin-top-30 <?php if (!($this->session->userdata("change_password_message"))){ echo 'display-hide'; } ?>" role="alert">			
					   <?php $this->session->unset_userdata("alert_success"); ?>
					    <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>			
					    <span>
					    <?php
                          echo $this->session->userdata("change_password_message");
                        ?>
                        </span>		
			     </div>
			     
				<form class="m-login__form m-form" action="<?php echo base_url() . "dashboard/reset_password"; ?>" method="post" style="">
					<div class="form-group m-form__group">
						<input class="form-control m-input <?php if ( !empty($this->session->userdata("change_password_message")) ) { echo "display-hide"; } ?>" type="text" placeholder="Email" name="reset_email" id="m_email" autocomplete="off">
					</div>
					<div class="m-login__form-action">
						<?php
						$submitBtnStyle = "";
						if(!empty($this->session->userdata("change_password_message"))){
							$submitBtnStyle="style='display:none !important;'";
						}
						echo  render_button ('', 'mLoginForgetPasswordSubmit', 'btn m-btn m-btn--pill m-btn--custom   m-login__btn m-login__btn--primary', 'submit' , dashboard_lang('_SUBMIT'), '' ,$submitBtnStyle);
                        echo  render_button ('', 'm_login_forget_password_cancel', 'btn m-btn m-btn--pill m-btn--custom   m-login__btn', 'button' , dashboard_lang('_BACK'), '' ,'');
                         ?>
					</div>
				</form>
			</div>
			<?php $this->session->unset_userdata("change_password_message"); ?>
			<?php
            $create_account_default_value = strtolower($this->config->item('#CREATE_ACCOUNT'));
            if ($create_account_default_value == 'yes' ) { ?>
            
			<div class="m-login__account">
				<span class="m-login__account-msg portal-account-mgs">
				 <?php echo dashboard_lang('_DONT_HAVE_ACCOUNT_YET'); ?>
				</span>&nbsp;&nbsp;
				
				 <?php if(isset($user_slug)):?>
                    <a class='m-link m-link--light m-login__account-link custom-link' href="<?php echo base_url() . "dashboard/signup"."/?slug=".$user_slug; ?>"  id="signup"><?php echo dashboard_lang('_CLICK_HERE_TO_CREATE_AN_ACCOUNT'); ?></a>
                <?php else: ?>
                    <a class='m-link m-link--light m-login__account-link custom-link' href="<?php echo base_url() . "dashboard/signup";?>"  id="signup" ><?php echo dashboard_lang('_CLICK_HERE_TO_CREATE_AN_ACCOUNT'); ?></a>
                <?php endif;?>
			    
			</div>
			
			<?php }?>
		</div>	
	</div>
</div>				
		

</div>

        <style>
          	.display-hide { display:none; }
            
			.g-recaptcha {
            	margin: 20px 0 20px 56px;
            }

			.custom-info-button {
				width: 22px !important;
				height: 22px !important;
			}
			

        </style>
        <script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/scripts.bundle.js" type="text/javascript"></script>
        <script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/snippets/pages/user/login.js" type="text/javascript"></script>
		<script>
		$(function () {
			$("#m_login_forget_password_cancel").on("click", function () {
				$(this).siblings("#mLoginForgetPasswordSubmit").show();
				$(this).closest(".m-login__form.m-form").find("[name='reset_email']").removeClass("display-hide");
				$(this).closest(".m-login__forget-password").find(".alert.alert-info").addClass("display-hide");
				$(this).closest(".m-login__forget-password").find(".alert.alert-info > span").empty();
			});

			$('[data-toggle="popover"]').popover({
				container: 'body',
				placement: 'right',
				trigger: 'click',
				html: true
			});
		});
		</script>
    </body>

</html>