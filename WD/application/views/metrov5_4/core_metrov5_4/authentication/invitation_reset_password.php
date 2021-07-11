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
					<h3 class="m-login__title"><?php echo dashboard_lang('_SET_PASSWORD'); ?>  <?php echo (isset($account) && !empty($account) && isset($account['name'])) ? "- {$account['name']}" : ''; ?></h3>
				</div>
				<form class="m-login__form m-form signup-form" action="<?php echo base_url();?>invitation/checkResetPassword" method="post" >
				
				    <?php if(isset($_GET['slug'])):?>
                        <input type="hidden" name="user_slug" value="<?php echo $_GET['slug']; ?>" />
                    <?php endif; ?>
                    
					<div class="form-group m-form__group">
						<input class="form-control m-input m-login__form-input--last" id="new_password" type="password" placeholder="<?php echo dashboard_lang('_ENTER_PASSWORD'); ?>" name="new_password">
					</div>
					<div class="form-group m-form__group">
						<input class="form-control m-input m-login__form-input--last" autocomplete="off" id="re_password" type="password" placeholder="<?php echo dashboard_lang('_RETYPE_PASSWORD'); ?>" name="re_password">
					</div>
						
					<input type='hidden' name='user_id' value='<?php echo $user_id; ?>'>
				   
					<div class="m-login__form-action">
						<?php
                         echo  render_button ('reset_submit', 'reset_submit', 'btn btn-focus m-btn m-btn--pill m-btn--custom    m-login__btn m-login__btn--primary', 'button' , dashboard_lang('_SUBMIT'), '' , 'value="1"');
                        ?>
					</div>
				</form>
			</div>



<?php $this->load->view('metrov5_4/core_metrov5_4/authentication/footer');?>

<script type="text/javascript">
$("#reset_submit").on("click",  function(){ 

	var password = $("#new_password").val();
	var re_password = $("#re_password").val();
	
    if( !checkStrongPassword(password, re_password) ){    	
		return false; 
    }else{
        console.log('fine');
        $('.signup-form').submit(); 
    }
    
});
</script>
