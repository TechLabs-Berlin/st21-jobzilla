<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->load->view('metrov5_4/core_metrov5_4/authentication/header');
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
<div class="m-login__signin" style="">
				<div class="m-login__head">
					<h3 class="m-login__title"><?php echo dashboard_lang('_RESET_PASSWORD'); ?>  <?php echo (isset($account) && !empty($account) && isset($account['name'])) ? "- {$account['name']}" : ''; ?></h3>
				</div>
				<form class="m-login__form m-form signup-form" action="" method="post" >
				
				 <?php if(isset($_GET['slug'])):?>
                      <input type="hidden" name="user_slug" value="<?php echo $_GET['slug']; ?>" />
                  <?php endif; ?>
                   <div class="m-alert m-alert--outline alert alert-dismissible <?php if (@$alert_success): echo 'alert-success'; else: echo 'alert-danger'; endif; ?> <?php if (!isset($message)): echo 'display-hide';  endif; ?>" role="alert">			
					    <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>			
					    <span>
                          <?php echo @$message;?>
                        </span>		
					</div>  
                   <?php if ( !@$form_hide): ?>    
                    
					<div class="form-group m-form__group">
						<input class="form-control m-input m-login__form-input--last" id="new_password" type="password" placeholder="<?php echo dashboard_lang('_ENTER_PASSWORD'); ?>" name="new_password">
					</div>
					
					<div class="col-md-9 pull-right text-danger">
                          <?php echo form_error('password'); ?>
                    </div>
                    
					<div class="form-group m-form__group">
						<input class="form-control m-input m-login__form-input--last" autocomplete="off" id="re_password" type="password" placeholder="<?php echo dashboard_lang('_RETYPE_PASSWORD'); ?>" value="<?php echo set_value('password'); ?>" name="re_password">
					</div>
					
					<div class="col-md-9 pull-right text-danger">
                          <?php echo form_error('password'); ?>
                    </div>	
				   
				    <?php endif; ?>       
				    
					<div class="m-login__form-action">
					 <?php if (!@$btn_hide): ?>
                     <?php
                        echo  render_button ('reset_submit', 'reset_submit', 'btn btn-focus m-btn m-btn--pill m-btn--custom    m-login__btn m-login__btn--primary', 'submit' , dashboard_lang('_SUBMIT'), '' , 'value="1"');
                     ?>
                      <?php endif; ?>


                    <?php if (isset($message) && @$btn_hide): ?> 
                       <a href="<?php echo base_url() . "dashboard/index"; ?>"  id="register-back-btn" type="button" class="btn btn-lg btn-success btn-block"> <?php echo dashboard_lang('_GO_LOGIN_PAGE');?> </a>
                    <?php endif; ?>
                    
					</div>
					
				</form>
</div>
<script type="text/javascript">
jQuery('#reset_submit').on('click' , function(e){
	var password = $("#new_password").val();
    var re_password = $("#re_password").val();
    if( !checkStrongPassword(password, re_password) ){
    	console.log('invalid ');
		return false; 
    }else{
        return true; 
    }
    
});
</script>
<?php $this->load->view('metrov5_4/core_metrov5_4/authentication/footer');?>
<style>
.display-hide { display:none; }
</style>