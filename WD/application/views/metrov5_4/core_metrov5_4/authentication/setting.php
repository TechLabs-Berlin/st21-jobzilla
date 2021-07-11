<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>

<form role="form" name="change_pass" id="change_pass" method="post" action="">
  <!--  new code -->
  <div class="row">
    <div class="col-md-12">
      <div class="m-portlet light bordered">
        <div class="m-portlet__head m--font-boldest">
        	<div class="m-portlet__head-caption">
          			<span class="m-portlet__head-text reset-password"> <?php echo dashboard_lang('_CHANGE_ACCOUNT_PASSWORD') ?> </span>
                  <div class="table-toolbar no-bottom-margin pull-right button-holder-padding">
                    <div class="button_row pull-right">
                      <button class="btn btn-success pull-right margin-left password_save_button" type="submit" name="submit"><?php echo dashboard_lang('_SAVE');?> <i class="fa fa-save"></i> </button>
                    </div>
                  </div>
             </div>
        </div>
        <!--End panel-heading-->
        <div class="m-portlet__body ">
          <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                  <?php if(!empty($change_password_message)):?>
                  <div class="alert alert-block alert-success fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <p><?php echo $change_password_message;?></p>
                  </div>
                  <?php endif; ?>
                  <?php if(!empty($change_password_error)):?>
                  <div class="alert alert-block alert-danger fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <p><?php echo $change_password_error ;?></p>
                  </div>
                  <?php endif; ?>
                  <div class="form-group m--font-bolder"> <?php echo dashboard_lang('_PASSWORD_CHANGE_EMAIL').'&nbsp; <a href="mailto:'.$data['email'].'">'. $data['email'] .'</a>';?> </div>
                  
                  <div class="form-group m--font-bold"> <?php echo dashboard_lang('_NEW_PASSWORD'); ?>
                    <input type="password" class="form-control" name="new_password" id="new_password"  />
                  </div>
                  <div class="form-group m--font-bold"> <?php echo dashboard_lang('_RETYPE_PASSWORD'); ?>
                    <input type="password" class="form-control" name="retype_password" id="retype_password"  />
                  </div>
                  <div class="form-group generate_password_link text-info" style="display: none;">
                      <a href="https://identitysafe.norton.com/password-generator" target="blank"><?php echo dashboard_lang('_GENERATE_STRONG_PASSWORD'); ?></a>
                    </div>
            </div>
            <!-- /.col-lg-12 --> 
          </div>
        </div>
      </div>
      <!-- /.portlet light bordered --> 
    </div>
    <!-- /.col-md-12 --> 
  </div>
  <!-- /.row -->
  
</form>
<?php require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/authentication/password_validation_script.php');?>
<script>
var passMatchAlert = "<?php echo dashboard_lang('_TWO_PASSWORD_NOT_MATCH')?>";
$('button.password_save_button').on('click',function(){

    var new_password = $('#new_password').val();
    var retype_password = $('#retype_password').val();

    var status = checkStrongPassword(new_password, retype_password);
	return status;
});
</script>
