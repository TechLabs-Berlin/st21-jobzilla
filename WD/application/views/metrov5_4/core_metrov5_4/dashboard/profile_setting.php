<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>

<form enctype="multipart/form-data" role="form" name="change_pass" id="change_pass" method="post" action="">
	<!--  new code -->
	<div class="row">
		<div class="col-md-12">
			<div class="portlet light bordered">
				<div class="portlet-title">
					<div class="caption font-dark"> <span class="caption-subject bold uppercase"><?php echo dashboard_lang('_DASHBOARD_PROFILE_SETTINGS') ?></span> </div>
				</div>
				<!--End panel-heading-->

				<div class="portlet-body">
					<div class="row table-toolbar">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="button_row pull-right">
								<button class="btn green pull-left margin-left" type="submit" name="submit"><?php echo dashboard_lang('_DASHBOARD_LISTING_SAVE');?> <i class="fa fa-save"></i> </button>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="row">
										<div class="col-lg-12">
											<div class="row">
												<div class="col-lg-6">
													<?php if($this->session->flashdata("change_password_message")):?>
														<div class="alert alert-info" role="alert"> <?php echo $this->session->flashdata("change_password_message");?> </div>
													<?php endif; ?>
													<?php if($this->session->flashdata("change_password_error")):?>
														<div class="alert alert-danger" role="alert"> <?php echo $this->session->flashdata("change_password_error");?> </div>
													<?php endif; ?>
													<div class="form-group"> <?php echo dashboard_lang('_FIRST_NAME'); ?>
														<input class="form-control" type="text" name="first_name" value="<?php echo $data['first_name']?>" size="50" />
														<br>
														<?php echo dashboard_lang('_LAST_NAME'); ?>
														<input class="form-control" type="text" name="last_name" value="<?php echo $data['last_name']?>" size="50" />
													</div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="form-group">
                                                                <input type="file" name="fileToUpload" id="fileToUpload">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-12">
													<div class="form-group">
														<span class="img-circle pull-left" style=" margin-right:115px; margin-bottom: 10px; width:133px; height:133px; background-image:url(<?php echo CDN_URL.$this->session->userdata('image'); ?>); background-size:cover; background-repeat:no-repeat; background-position:center center;"></span>
													</div>
                                                    </div>
                                                    </div>
													
													<div class="form-group"> <?php echo dashboard_lang('_PASSWORD_CHANGE_EMAIL') ."&nbsp;". $data['email']; ?> </div>
													<div class="form-group"> <?php echo dashboard_lang('_CHOOSE_LANGUAGE'); ?>
														<select name="language" class="form-control">
															<option>Choose A language </option>
															<?php foreach ($language_list as $language_list) {?>
																<option value="<?php echo $language_list->name; ?>" <?php if ( $language_list->name == $user_selected_language) {echo "selected";} ?>> <?php echo $language_list->name; ?></option>
															<?php } ?>
														</select>
													</div>
													<?php
													/*
                                                    foreach($data_load->field as $value){

                                                            $valueType = (string) $value["type"];
                                                            $nameField = (string) $value["name"];

                                                            echo '<div class="form-group">';

                                                                if(! in_array($nameField, $edit_field_array)){
                                                                    echo boo2_render_span($value, @$data_edit[(string)$value["name"]]);
                                                                }else{
                                                                    echo boo2_render_form($value, @$data_edit[(string)$value["name"]]);
                                                                }

                                                             echo "</div>";

                                                    }
                                                    */
													?>
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
				</div>
				<!-- /.portlet-body -->

			</div>
			<!-- /.portlet light bordered -->
		</div>
		<!-- /.col-md-12 -->
	</div>
	<!-- /.row -->

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