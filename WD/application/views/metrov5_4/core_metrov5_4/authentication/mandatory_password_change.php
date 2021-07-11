<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->load->view('metrov5_4/core_metrov5_4/authentication/header');
?>
<style>
.display-hide { display:none; }
</style>
<div class="m-login__signin" style="">
    <div class="m-login__head">
        <h3 class="m-login__title"><?php echo dashboard_lang('_TIME_TO_RESET_PASSWORD_YOUR_PASSWORD_FOR_THE_BETTER_SECURITY'); ?></h3>
    </div>
    <form class="m-login__form m-form signup-form" action="" method="post" >
    
        <div class="m-alert m-alert--outline alert alert-dismissible <?php if (@$alert_success): echo 'alert-success'; else: echo 'alert-danger'; endif; ?> <?php if (!isset($message)): echo 'display-hide';  endif; ?>" role="alert">			
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>			
            <span>
                <?php echo @$message;?>
            </span>		
        </div>  
        
        
        <div class="form-group m-form__group">
            <input class="form-control m-input m-login__form-input--last" id="new_password" type="password" placeholder="<?php echo dashboard_lang('_ENTER_PASSWORD'); ?>" name="new_password">
        </div>
        
        <div class="col-md-9 pull-right text-danger">
            <?php echo form_error('password'); ?>
        </div>
        
        <div class="form-group m-form__group">
            <input class="form-control m-input m-login__form-input--last" autocomplete="off" id="re_password" type="password" placeholder="<?php echo dashboard_lang('_RETYPE_PASSWORD'); ?>" value="" name="re_password">
        </div>
        
        <div class="col-md-9 pull-right text-danger">
            <?php echo form_error('password'); ?>
        </div>	  
        
        <div class="m-login__form-action">
            <?php
            echo  render_button ('reset_submit', 'reset_submit', 'btn btn-focus m-btn m-btn--pill m-btn--custom    m-login__btn m-login__btn--primary colored-big-button', 'submit' , dashboard_lang('_CHANGE_PASSWORD'), '' , 'value="1"');
            // echo  render_button ('skip', 'skip', 'btn btn-focus m-btn m-btn--pill m-btn--custom    m-login__btn m-login__btn--primary m--margin-left-10', 'button' , dashboard_lang('_SKIP'), '' , '');
            ?>
        
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