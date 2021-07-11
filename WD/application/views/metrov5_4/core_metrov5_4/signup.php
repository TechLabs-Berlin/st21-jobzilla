<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<?php $this->load->view('core_metrov5_4/guest/header');?>
<!-- BEGIN LOGIN -->
<div class="content">
	<!-- BEGIN REGISTRATION FORM -->
	<form class="signup-form" action="" method="post" role="form">
	<?php if(isset($_GET['slug'])):?>
	    <input type="hidden" name="user_slug" value="<?php echo $_GET['slug']; ?>" />
	<?php endif; ?>
		<h3 class="form-title"><?php echo dashboard_lang('_SIGN_UP');?><?php echo (isset($account) && !empty($account) && isset($account['name'])) ? "- {$account['name']}" : ''; ?></h3>
		<p><?php echo dashboard_lang('_ENTER_YOUR_ACCOUNT_DETAILS_BELOW');?></p>
				
				<div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" for="name"><?php echo dashboard_lang('_NAME');?></label>
                    <div class="input-icon">
                        <i class="fa fa-font"></i>
                        <input class="form-control placeholder-no-fix" type="text" placeholder="Name" name="name" value="<?php echo set_value('name'); ?>" />
						<div class="col-md-9 pull-right text-danger">
                             <?php echo form_error('name'); ?>
						</div>						
					</div>
                </div>

				<div class="form-group">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label visible-ie8 visible-ie9" for="email"><?php echo dashboard_lang('_EMAIL');?></label>
                    <div class="input-icon">
                        <i class="fa fa-envelope"></i>
                        <input class="form-control placeholder-no-fix" type="text" placeholder="Email" name="email" value="<?php echo set_value('email'); ?>" /> </div>
						<div class="col-md-9 pull-right text-danger">
							<?php echo form_error('email'); ?>
						</div>
                </div>


				<div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" for="password"><?php echo dashboard_lang('_PASSWORD');?></label>
                    <div class="input-icon">
                        <i class="fa fa-lock"></i>
                        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="register_password" placeholder="Password" name="password" value="<?php echo set_value('password'); ?>" /> 
					</div>
					<div class="col-md-9 pull-right text-danger">
                        <?php echo form_error('password'); ?>
                    </div>
                </div>
				


			    <div class="form-group">
                    <label>
                        <input type="checkbox" name="tnc" /><?php echo dashboard_lang('_I_AGREE_TO_THE');?>
                        <a href="<?php echo base_url() . "dashboard/terms_of_service"; ?>"><?php echo dashboard_lang('_TERMS_OF_SERVICE');?></a>&nbsp;<?php echo dashboard_lang(' _AND');?>
                        <a href="<?php echo base_url() . "dashboard/privacy_policy"; ?>"><?php echo dashboard_lang('_PRIVACY_POLICY');?> </a>
                    </label>
                    <div id="register_tnc_error"> </div>
                </div>
                <div class="form-actions">
                    <a href="<?php echo base_url() . "dashboard/index"; ?>"  id="register-back-btn" type="button" class="btn grey-salsa btn-outline"> <?php echo dashboard_lang('_BACK');?> </a>
                    <button type="submit" id="register-submit-btn" class="btn green pull-right"><?php echo dashboard_lang('_SIGN_UP');?> </button>
                </div>
            </form>
	<!-- END LOGIN FORM -->


</div>

<?php $this->load->view('core_metrov5_4/guest/footer');?>
