<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<form role="form" name="change_pass" id="change_pass" method="post" action="">
<!--  new code -->
<div class="row">
	<div class="col-md-12">
		<div class="portlet light bordered">
			<div class="portlet-title">
				 <div class="caption font-dark">
					<span class="caption-subject bold uppercase"><?php echo dashboard_lang('_DASHBOARD_SETTINGS_CHANGE_PASSWORD') ?></span> 
				 </div>
			</div>
			<!--End panel-heading-->

			<div class="portlet-body">
				<div class="row table-toolbar">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="button_row pull-right">
							<button class="btn green pull-left margin-left" type="submit"><?php echo dashboard_lang('_DASHBOARD_LISTING_SAVE');?>
								<i class="fa fa-save"></i>
							</button>               
						</div>
					</div>
				</div>
			</div><!-- /.portlet-body -->
				
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">

			<div class="panel-body">
				<div class="row">
					<div class="col-lg-12">

							<div class="row">
								<div class="col-lg-6">
			
                                <?php if($this->session->flashdata("change_password_message")):?>
                                	<div class="alert alert-info" role="alert">
                                		<?php echo $this->session->flashdata("change_password_message");?>
                                	</div>
                                <?php endif; ?>
                                                            
                                <?php if($this->session->flashdata("change_password_error")):?>
                                	<div class="alert alert-danger" role="alert">
                                		<?php echo $this->session->flashdata("change_password_error");?>
                                	</div>
                                <?php endif; ?>	                               
                                
									<div class="form-group">
										 <?php echo dashboard_lang('_PASSWORD_CHANGE_EMAIL').' '.$data['email']; ?>
									</div>
									<div class="form-group">
										<?php echo dashboard_lang('_PASSWORD_CHANGE_CURRENT_PASSWORD'); ?> <input type="password" value="" class="form-control" name="current_password" id="current_password" required /> 
									</div>
									<div class="form-group">
										<?php echo dashboard_lang('_PASSWORD_CHANGE_NEW_PASSWORD'); ?> <input type="password" class="form-control" name="new_password" id="new_password" required />
									</div>
									<div class="form-group">
										<?php echo dashboard_lang('_PASSWORD_CHANGE_RETYPE_PASSWORD'); ?> <input type="password" class="form-control" name="retype_password" id="retype_password" oninput="check(this)" required />
									</div>					
									
								</div>
							</div>						
					
					</div>

				</div>
				<!-- /.row (nested) -->
			</div>
			<!-- /.panel-body -->
		</div>
		<!-- /.panel -->
	</div>
	<!-- /.col-lg-12 -->
</div>
	
		</div><!-- /.portlet light bordered -->
	</div><!-- /.col-md-12 -->
</div><!-- /.row -->

</form>
<script>
var passMatchAlert = "<?php echo dashboard_lang('_TWO_PASSWORD_MUST_MATCH')?>";					
function check(input) {
    if (input.value != document.getElementById('new_password').value) {
        input.setCustomValidity(passMatchAlert);
    } else {
        // input is valid -- reset the error message
        input.setCustomValidity('');
   }
}
</script>
