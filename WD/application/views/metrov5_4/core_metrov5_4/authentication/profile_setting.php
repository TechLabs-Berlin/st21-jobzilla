<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/edit.css" rel="stylesheet">
<?php 
 $defaultAvaterUrl = $this->config->item("default_avater_url");
 $user_helper = BUserHelper::get_instance();
 $tenantData = $user_helper->tenant;
foreach($tenantData as $key => $row){
	if($key =="2fa"){
		$twofaOption = $row;
		break;
	}
}
$udata = $this->session->all_userdata();
$CI =& get_instance();
$is_2fa_enabled = $CI->User_roles_model->get_is_2fa_enabled( $udata );
$tenant_2fa = $CI->User_roles_model->get_specific_data( array( 'id' => $udata['account_id'] ), '2fa', 'accounts' );
$tenant2fa = @$tenant_2fa[0]['2fa'];

$permission_array = array(
    'role'=>'super_admin',
    'field_name'=>'*',
    'permission'=>2
);
$tab1_class = '';
$tab2_class = '';
$tab3_class = '';
$tab4_class = '';
$tab5_class = '';
switch ($selected_tab){
    case 0: 
        $tab1_class = 'active';
        break;
    case 1: 
        $tab2_class = 'active';
        break;
    case 2: 
        $tab3_class = 'active';
        break;
    case 3:
        $tab4_class = 'active';
        break;
    case 3:
        $tab5_class = 'active';
        break;
    default:
        $tab1_class = 'active';
        
}
$table_permissions_field[0] = $permission_array;

$xmlFile = FCPATH . $this->config->item("xml_file_path") . DIRECTORY_SEPARATOR ."profile_settings" . ".xml";
$data_load = simplexml_load_file($xmlFile);
$xmlObjectArray = B_form_helper::get_xml_object_array($data_load);

$defaultCountryId = intval($this->config->item('#CORE_DASHBOARD_LOGINS_DEFAULT_COUNTRY'));

if( $defaultCountryId > 0 && empty(trim($data['mobile_prefix']))){

	$countryInfo = getDataFromTbl('countries', '*', array("id" => $defaultCountryId,"is_deleted" => 0), '', true);
	$data['mobile_prefix'] = $countryInfo->dialing_codes;
}

if( empty(trim($data['language']))){

	$defaultLanguage = $this->config->item('#LANGUAGE_DEFAULT');
	$data['language'] = $defaultLanguage;
}

?>
<div>
<form enctype="multipart/form-data" role="form" name="profile_settings" id="profile_settings" method="post" action="">
  <!--  new code -->
  <div class="row ">
    <div class="col-md-12">
      <div class="m-portlet light bordered">
        <div class="m-portlet__head portlet-title">
          <div class="m-portlet__head--caption" style="display: flex;"> 
          	<span class="m-portlet__head-title m--margin-top-20" style="display:inline;">
          		<h3 class="m-portlet__head-text"> <?php echo dashboard_lang('_PROFILE_SETTINGS') ?> </h3>
          	</span> 
         
			<span style="display:inline;margin-left:5px !important; margin-top:15px !important; color: white;">
				<button onclick="goBack()" class="btn btn-success m-btn m-btn--icon on-click-form-submit" type="button" data-submit="1" > <i class="fa fa-angle-left" aria-hidden="true"></i> <?php echo dashboard_lang('_BACK'); ?> </button>
			</span>
          </div>
        	<div class="m-portlet__head-tools">
            	<div class="m-portlet__nav button_row m--margin-top-15">
            		<?php echo  render_button ('submit', '', 'btn btn-success m-btn m-btn--icon   pull-right margin-left', 'submit' , dashboard_lang('_SAVE'), 'save',''); ?>
                </div>  	
            </div>  
        </div>
        
    
      <!--End panel-heading-->
      <div class="m-portlet__body portlet-body">
      	<div class="m-portlet m-portlet--tabs">
      		<div class="m-portlet__head">
      			<div class="m-portlet__head-tools">
      				<ul class="nav nav-tabs m-tabs-line m-tabs-line--primary m-tabs-line--2x">
      					<li class="nav-item m-tabs__item">
      						<a class="nav-link m-tabs__link <?php echo $tab1_class; ?>" data-position="0" data-toggle="tab" href="#personal" role="tab" aria-expanded="false">
                                <i class="flaticon-user-settings"></i> <?php echo dashboard_lang('_PERSONAL_INFO'); ?>
                            </a>
      					</li>
      					<li class="nav-item m-tabs__item" role="presentation">
      						<a class="nav-link m-tabs__link <?php echo $tab2_class; ?>" data-position="1" data-toggle="tab" href="#avatar" role="tab" aria-expanded="false">
                                <i class="la la-user"></i> <?php echo dashboard_lang('_CHANGE_PROFILE_PICTURE'); ?>
                            </a>
      					</li>
						<li class="nav-item m-tabs__item" role="presentation">
      						<a class="nav-link m-tabs__link <?php echo $tab3_class; ?>" data-position="3" data-toggle="tab" href="#changePassword" role="tab" aria-expanded="false">
                                <i class="la la-key"></i> <?php echo dashboard_lang('_CHANGE_PASSWORD'); ?>
                            </a>
      					</li>
      					<li class="nav-item m-tabs__item" role="presentation">
      						<a class="nav-link m-tabs__link <?php echo $tab4_class; ?>" data-position="4" data-toggle="tab" href="#bgImage" role="tab" aria-expanded="false">
                                <i class="fa fa-image" aria-hidden="true"></i> <?php echo dashboard_lang('_CHANGE_BACKGROUND_IMAGE'); ?>
                            </a>
      					</li>
      					<li class="nav-item m-tabs__item" role="presentation">
      						<a class="nav-link m-tabs__link <?php echo $tab5_class; ?>" data-position="5" data-toggle="tab" href="#email_address" role="tab" aria-expanded="false">
                                <i class="la la-envelope"></i> <?php echo dashboard_lang('_CHANGE_EMAIL_ADDRESS'); ?>
                            </a>
      					</li>
      				</ul>
      			</div>	
      		</div>
      		<div class="m-portlet__body">
      			<div class="row m--font-bolder">
      				<div class="col-lg-12">
      					<?php if($this->session->flashdata("change_password_message")):?>
                        <div class="alert alert-block alert-success alert-dismissible fade show m-alert m-alert--air" role="alert">
                          <button type="button" class="close" data-dismiss="alert"></button>
                            <?php echo $this->session->flashdata("change_password_message");?>
                        </div>
                        <?php endif; ?>
                        <?php if($this->session->flashdata("change_password_error")):?>
                        <div class="alert alert-block alert-danger alert-dismissible fade show m-alert m-alert--air" role="alert">
                          <button type="button" class="close" data-dismiss="alert"></button>
                            <?php echo $this->session->flashdata("change_password_error");?>
                        </div>
                        <?php endif; ?>
                        <div class="tab-content">
							
                        	<div class="tab-pane <?php echo $tab1_class; ?>" id="personal">
							
							<div class="row">
									<div class="col-xl-6 col-lg-6 col-md-6">
                        		<?php															
																											
                    				echo B_form_helper::render_field( $xmlObjectArray['first_name'], @$data['first_name'] , $table_permissions_field);
									echo B_form_helper::render_field( $xmlObjectArray['last_name'], @$data['last_name'] , $table_permissions_field);
									echo B_form_helper::render_field( $xmlObjectArray['mfa_status'], @$data['mfa_status'] , $table_permissions_field);
									
                    			?>
									</div>
								</div>
								<div class="row m--padding-left-15 m--padding-right-45">
									<div class="col-xl-6 col-lg-6 col-md-6 phone-number-border m--margin-top-15 m--margin-bottom-15" >
										
										<div class="row m--margin-top-15 m--margin-bottom-15" >
											<div class="col-xl-12 col-lg-12 col-md-12"> 
												<span ><?php echo dashboard_lang('_PHONE_NUMBER');?></span>
											</div>
										</div>
										<div class="row m--margin-top-15 m--margin-bottom-15">
											<div class="col-xl-3 col-lg-3 col-md-3"> 
												<?php echo B_form_helper::render_field($xmlObjectArray['country_code'], @$data['mobile_prefix'] , $table_permissions_field); ?> 
											</div>
											<div class="col-xl-1 col-lg-2 col-md-2 m--margin-top-35 m--align-right"> 
												<span class="add-mobile-prefix"><?php echo $data['mobile_prefix']?></span>
											</div>
											<div class="col-xl-8 col-lg-7 col-md-7"> 
												<?php echo B_form_helper::render_field( $xmlObjectArray['mobile_postfix'], @$data['mobile_postfix'] , $table_permissions_field);   ?> 
											</div>
										</div>
									</div>
									
									<?php if($tenant2fa){?>
									<div id="numberVarificationContainer" class="col-xl-6 col-lg-6 col-md-6" data-link="" style="margin-top: 1.8rem;" > 
										<?php echo  render_button ('numberVarification', 'numberVarification', 'btn btn-accent m-btn--icon  ', 'button' , dashboard_lang("_VERIFY_AND_SAVE"), ''); ?>
										<span id="verificationMessage"></span>
										<span> <?php echo B_form_helper::render_info_button('VERIFY_MOBILE_NUMBER', 'abc', ''); ?> </span>
									</div>
									<?php }?>
								</div>
								<div class="row">
									<div class="col-xl-6 col-lg-6 col-md-6"><?php echo B_form_helper::render_field( $xmlObjectArray['language'], @$data['language'] , $table_permissions_field); ?> </div>
								</div>
								<!-- <div class="row">
									<div class="col-xl-6 col-lg-6 col-md-6"><?php echo B_form_helper::render_field( $xmlObjectArray['alternate_email'], @$data['alternate_email'] , $table_permissions_field); ?> </div>
								</div>

								<div class="row">
								<div class="col-12 m--margin-top-15">
									<h5><?php echo dashboard_lang('_SIGNATURE');?></h5>
									<p class="m--font-bold"><?php echo dashboard_lang('_PORTAL_SIGNATURE_DEFAULT_TEXT')?>
									</p>  
								</div>
								</div>
								<div class="row">
									
									<div class="col-xl-6 col-lg-6 col-md-6"><?php echo B_form_helper::render_field( $xmlObjectArray['signature_text'], @$data['signature_text'] , $table_permissions_field); ?> </div>
								</div>

								<div class="row">
									<div class="col-xl-6 col-lg-6 col-md-6"><?php echo B_form_helper::render_field( $xmlObjectArray['signature_image'], @$data['signature_image'] , $table_permissions_field); ?> </div>
								</div> -->
								
                        	</div>
                        	<div class="tab-pane <?php echo $tab2_class; ?>" id="avatar">
                        		<div class="form-group">
                                    <label class="ps-label control-label"><?php echo dashboard_lang('_CHANGE_PROFILE_PICTURE'); ?></label>
                                    <input type="file" name="image" id="image" accept="image/*">
                                </div>
                                <div class="form-group">
                                	<div class="profie-img-area"> <span id="profileImgSpan" class="m--img-rounded m--marginless m--img-centered img-circle pull-left" style=" margin-right:115px; width:133px; height:133px; background-image:url(
                						<?php
                						$fbimage = $this->session->userdata ('fbimage');
                						$removeBtn = '';
                						if ( !empty( $fbimage ) ) {
                							echo $fbimage;
                							$removeBtn = '<a href="javascript:undefined;" data-img-from="fb" data-img-url="'.$fbimage.'" class="btn btn-danger m-btn m-btn--icon btn-sm m-btn--icon-only  m-btn--pill   remove-profile-icon-btn"> <i class="fa fa-remove"></i> </a>';
                						} else {
                						    $propicurl = $user_helper->user_image;
											echo $propicurl;
                						    if ( @getimagesize($propicurl) ) {
												if($defaultAvaterUrl == $propicurl){
													$removeBtn = '<a href="javascript:undefined;" data-img-from="" data-img-url="" class="btn btn-danger m-btn m-btn--icon btn-sm m-btn--icon-only  m-btn--pill   remove-profile-icon-btn" style="display:none;"> <i class="fa fa-remove"></i> </a>';
												}else{
													$removeBtn = '<a href="javascript:undefined;" data-img-from="custom" data-img-url="'.$user_helper->user_image.'" class="btn btn-danger m-btn m-btn--icon btn-sm m-btn--icon-only  m-btn--pill   remove-profile-icon-btn"> <i class="fa fa-remove"></i> </a>';
												}
                						    }
                						}
                
                						?>); background-size:cover; background-repeat:no-repeat; background-position:center center;"> </span>
                						<?php if ( strlen( $removeBtn ) > 0 ) { echo $removeBtn; }?> 
                					</div>
                                </div>
                        	</div>
							<div class="tab-pane <?php echo $tab3_class; ?>" id="changePassword">                               
		
								<div class="row">
									<div class="col-xl-6 col-lg-6 col-md-6">
										<div class="form-group m-form__group m--font-bolder  field_password" id="field_id_password">
											<label class="m--pull-left control-label " name="password" data-placement="top">
												<span class="label-password"><?php echo dashboard_lang('_PASSWORD'); ?></span>
												<span class="required" aria-required="true"> * </span>
												<?php echo B_form_helper::render_info_button( "profile_settings_password", "", false,  '', '' );?>
											</label>
											<div style="clear:left;">
												<input type="password" name="password" value="" id="password" placeholder="" class="form-control m-input ">
											</div>
										</div>
										<div class="form-group m-form__group m--font-bolder  field_re_password" id="field_id_re_password">
											<label class="m--pull-left control-label " name="re_password" data-placement="top">
												<span class="label-re-password"><?php echo dashboard_lang('_RETYPE_PASSWORD'); ?></span>
												<span class="required" aria-required="true"> * </span>
												<?php echo B_form_helper::render_info_button( "profile_settings_ew_password", "", false,  '', '' );?>
											</label>
											<div style="clear:left;">
												<input type="password" name="re_password" value="" id="re-password" placeholder="" class="form-control m-input ">
											</div>
										</div>
										<div class="form-group input-group">
										<button type="button" class="btn btn-success m-btn   pull-left" id="update-password">
										<?php echo dashboard_lang("_UPDATE")?> <i class="fa fa-save"></i>
										</button> 
                				  </div>

									</div>
								</div>
								
                        	</div>

                        	<div class="tab-pane <?php echo $tab4_class; ?>" id="bgImage">                               
								<div class="row">
									<div class="col-xl-12 col-lg-12 col-md-12">
										<p><?php echo dashboard_lang('_SELECT_A_BACKGROUND_AND_COLOR_PALLETE'); ?></p>
									</div>
								</div>
								<div class="row">
    								<?php 
    								$bgTargetDir = $this->config->item("background_image_upload_path");
    								$userBgImagePath = getBackgroundImage();
    								$bgImages = getDataFromTbl('bg_images', '*', array("is_deleted" => 0, "account_id" => $this->session->userdata('account_id')));
    								foreach ($bgImages as $bgImage):
                                    $bgImage = $bgImage->background_image;
    								$bgImagePath = $bgTargetDir.$bgImage;
    								$bgImageUrl = CDN_URL.$bgImagePath;
    								if ( stripos($bgImage, 'https://') !== false || stripos($bgImage, 'http://') !== false ) {
    								    $bgImagePath = $bgImage;
    								    $bgImageUrl = $bgImagePath; 
    								}
    								
    								$activeClass = "";
    								if($bgImagePath == $userBgImagePath){
    								    $activeClass = "active-bg";
    								}
    								?>
									<div class="col-xl-3 col-lg-3 col-md-3" style="margin-bottom: 30px;">
										<a href="javascript:void(0)" class="img-preview pull-left bgImage <?php echo $activeClass;?>" data-image="<?php echo $bgImage;?>" data-imagepath="<?php echo $bgImageUrl;?>" style="background-repeat: no-repeat; background-size: cover; background-position: center center; background-image: url(<?php echo $bgImageUrl;?>);"></a>
									</div>	
									<?php endforeach;?>							
								</div>
                                
                        	</div>
                        	<?php 
                            $user_helper = BUserHelper::get_instance();
                            $userEmail =  $user_helper->user->email;
                            ?>
                        	<div class="tab-pane <?php echo $tab5_class; ?>" id="email_address">
                        		<div class="form-group m-form__group field_email" id="field_id_email">
                                  	<label class="pop_over control-label" name="Email address" data-toggle="m-tooltip" data-placement="top" title=""><?php echo dashboard_lang("_ENTER_YOUR_NEW_EMAIL")?>
                                  		<span class="required" aria-required="true"> * </span><?php  echo B_form_helper::render_info_button("new_email", "", false);?>
                                  	</label>
                                  	<div style="clear: left;">
                                  		<input type="text" name="email" value="<?php echo $userEmail; ?>" id="new_email" placeholder="john.doe@example.com" class="form-control m-input required email">
                					</div>	
                				  </div>
                				  
                				  <div class="form-group input-group">
                				  	<button type="button" class="btn btn-success m-btn   pull-left" id="send_email_change_link">
                					<?php echo dashboard_lang("_CHANGE_EMAIL")?> <i class="fa fa-envelope"></i>
                					</button> 
                				  </div>
                        	</div>
                        </div>
      				</div>
      			</div>
      		</div>
      	</div>
        
      </div>
      
      <!-- /.portlet-body --> 
    </div>
    <!-- /.portlet light bordered --> 
  </div>
  <!-- /.col-md-12 -->
  </div>
  <!-- /.row -->
  <input type="hidden" name="selected_tab" id="selected_tab" value="<?php echo @$selected_tab; ?>" />
  <input type="hidden" name="mobile_number" class="phone" id="mobile_number" value="<?php echo @$data['mobile_number'];?>">
  <input type="hidden" name="mobile_prefix" id="mobile_prefix" value="<?php echo @$data['mobile_prefix']; ?>">

</form>
</div>
<?php 
$template = $this->config->item('template_name');
$this->load->view($template. "/dashboard_login/authy_modal_popup", $data); ?>
<style>
	.active-bg{
	border: #000 12px solid;
}
	a.img-preview {
    width: 100%;
    height: 180px;
    float: left;
}
.check_color {
	color: #34bfa3;
}
.phone-number-border{
	border: 1px solid;
    border-color: #ccc;
}

</style>
<script>
	var id = "<?php echo @$data['id']; ?>";
	var required_field_validation_msg = '<?php echo dashboard_lang("_THIS_FIELD_IS_REQUIRED"); ?>';
	var email_validation_msg = '<?php echo dashboard_lang("_ENTER_A_VALID_EMAIL_ADDRESS"); ?>';
	var phone_validation_msg = '<?php echo dashboard_lang("_ENTER_VALID_PHONE_NUMBER"); ?>';
	var is_2fa_enabled = "<?php echo $is_2fa_enabled; ?>";
	var verified_mobile_number = "<?php echo @$data['mobile_number']; ?>";
    var edit_link_label = "<?php echo dashboard_lang('_EDIT'); ?>";
    var verify_link_label = "<?php echo dashboard_lang('_VERIFY_AND_SAVE'); ?>";
    var url_verification = "<?php echo base_url("dashboard/verify_code"); ?>";
    var verification_alert = "<?php echo dashboard_lang("_MOBILE_NUMBER_IS_ALREADY_VERIFIED"); ?>";
    var verified_alert_msg = "<?php echo dashboard_lang("_YOUR_MOBILE_NUMBER_IS_VERIFIED"); ?>"
    var phone_verified_on = "<?php echo dashboard_lang('_VERIFIED_ON') . " " . date( "d F Y H:i", $data['phone_verified_on'] ); ?>";
    var phone_verified_on_timestamp = "<?php echo intval( $data['phone_verified_on'] ); ?>";
    var phone_validation_msg = '<?php echo dashboard_lang("_ENTER_VALID_PHONE_NUMBER"); ?>';
    var codeFieldEmptyAlertMsg = '<?php echo dashboard_lang("_CODE_FIELD_IS_EMPTY"); ?>';
	var profilePicDeleteAlert = '<?php echo dashboard_lang('_ARE_YOU_SURE_TO_DELETE_THE_PROFILE_PICTURE'); ?>';
	var phone_number_Verification_alert_msg = "<?php echo dashboard_lang('_PLEASE_VERIFY_YOUR_MOBILE_NUMBER'); ?>";
	var is_phone_verified = "<?php echo $is_phone_verified; ?>";
	var update_password = "<?php echo base_url("dashboard/updatePassowrd"); ?>";
	var mfaDefaultOptions = "<?php echo trim(strtolower( $this->config->item("#PORTAL_DEFAULT_MFA_OPTIONS")) );?>";

	function goBack() {
	
		var homePage = "<?php echo base_url();?>dbtables/surveys/listing";
		window.location.href = homePage;
	}
	
	$('body').on('keypress', 'input#mobile_postfix', function (event) {
	var inputValue = event.which;
	var mobilePostfixValue = $("input#mobile_postfix").val();
	if ( inputValue == 48 && mobilePostfixValue.length < 1) {
		event.preventDefault();
	}else if ( inputValue > 47 && inputValue < 58) {
		//
	}else{
		event.preventDefault();
	}
});

	jQuery(document).ready(function($) {

		// disabled specific mfa options
        if(mfaDefaultOptions == ""){
            $('#mfa_status option:nth-child(2)').prop('disabled', !$('#mfa_status option:nth-child(2)').prop('disabled'));
            $('#mfa_status option:nth-child(3)').prop('disabled', !$('#mfa_status option:nth-child(3)').prop('disabled'));            
            $('select').select2();
        } else if(mfaDefaultOptions == "sms"){
            $('#mfa_status option:nth-child(3)').prop('disabled', !$('#mfa_status option:nth-child(3)').prop('disabled'));
            $('select').select2();
        } else if(mfaDefaultOptions == "app"){
            $('#mfa_status option:nth-child(2)').prop('disabled', !$('#mfa_status option:nth-child(2)').prop('disabled'));
            $('select').select2();
        }

		$('#mobile_postfix').change(function () {

			var field_value = $(this).val().replace(/^0+(?=\d)/,'');
			var mobileNumber = $('select#country_code').val() + field_value;
			$("#mobile_number").val(mobileNumber);

			var field_name = jQuery(this).closest(".form-group").find("label").attr('title');
			if ( field_value.trim().length > 0 ) {
				
				var patt1 = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{5,7}$/im;
				var result = mobileNumber.match(patt1);
				if(!result){
					
					jQuery(this).closest(".form-group").addClass('has-danger');
					jQuery(this).parent().find('.form-control-feedback').remove();
					var error_msg = "<div id='"+field_name+"' class='form-control-feedback'>"+phone_validation_msg+"</div>";
					jQuery(this).after(error_msg);
					
				} else{

					$('#mobile_postfix').val( field_value );
					$("#mobile_number").val($('select#country_code').val() + $("#mobile_postfix").val())
					jQuery(this).closest(".form-group").removeClass('has-danger');
					jQuery(this).parent("div").children("div.form-control-feedback").remove();
				}
			} else {

				jQuery(this).closest(".form-group").removeClass('has-danger');
				jQuery(this).parent("div").children("div.form-control-feedback").remove();
				$('#mobile_postfix').val( field_value );
				$("#mobile_number").val($('select#country_code').val() + $("#mobile_postfix").val())

			}
			});
		$('select#country_code').change(function () {
			$("#mobile_number").val($(this).val() + $("#mobile_postfix").val())
			$("#mobile_prefix").val($(this).val());	
		});
		
		$(".img-preview").click(function(e){
			e.preventDefault();
			$(".img-preview").css("border","none");
			$(this).css("border","#000 12px solid");
		});

		// phone verification render starts
	    if( is_phone_verified == 1 ){
			$('div#numberVarificationContainer').attr("data-link", 'edit');
			$('button#numberVarification').text(edit_link_label);
			$('span#verificationMessage').html("<i class='fa fa-check-circle check_color' aria-hidden='true'></i>" + " <label id='verified_on_time'>" + phone_verified_on + "</label>");
		} else {
			$('span#verificationMessage').html("");
			$('div#numberVarificationContainer').attr('data-link', 'save');
			$('button#numberVarification').text(verify_link_label);
		}
		// phone verification render ends
	

	var required = "<?php echo dashboard_lang('_REQUIRED'); ?>";

	$("#profile_settings").submit(function(e){

		var validation = true ;
		var message = "";
		var mfaStatus = $("#mfa_status").val();

		$('input').each(function(){

			var is_req = $(this).hasClass('required');
			var field_value = $(this).val();
			var field_type = $(this).attr('type');
			var is_email = $(this).hasClass('email');
			var is_phone = $(this).hasClass('phone');
			var tab_id = $(this).parents('.tab-pane.active').attr('id');
			var field_name = jQuery(this).closest(".form-group").find("label").attr('title');
			
			if (is_req == true){

				var field_value = $(this).val();
				
				if (field_value.length  < 1){

					jQuery(this).closest(".form-group").addClass('has-danger');
					message = field_name+" field is "+required +", ";
					$(this).css('border-color', '#f4516c');
					
					
					$('.m-tabs-line a[href="#'+ tab_id +'"]').tab('show');
					
					var error_msg = "<div id='"+field_name+"' class='form-control-feedback'>"+required_field_validation_msg+"</div>";
					$(this).html(error_msg);
					e.preventDefault();
				} else {
					if(is_email){
						if( isValidEmailAddress( field_value ) ) {
							jQuery(this).closest(".form-group").removeClass('has-danger');
							$(this).css('border-color', '#ebedf2');
							jQuery(this).parent("div").children("div.form-control-feedback").remove();
						}
						else{
							jQuery(this).closest(".form-group").addClass('has-danger');
							$(this).css('border-color', '#f4516c');
							var error_msg = "<div id='"+field_name+"' class='form-control-feedback'>"+email_validation_msg+"</div>";
							jQuery(this).parent("div").children("div.form-control-feedback").remove();
							jQuery(this).after(error_msg);
							$('.m-tabs-line a[href="#'+ tab_id +'"]').tab('show');
							
							e.preventDefault();
						}
					}
					
				}


			}		
		});
		
		// console.log(is_phone_verified, is_2fa_enabled, mfaStatus);
		if(is_phone_verified != 1 && is_2fa_enabled == 1 && mfaStatus == "1"){
			console.log(is_phone_verified, is_2fa_enabled);
			alert(phone_number_Verification_alert_msg);
			e.preventDefault();
		}
		$('#mobile_postfix').attr('disabled', false);
		$('#country_code').attr('disabled', false);
	});


	$("input").on("keyup", function () {

		var is_req = $(this).hasClass('required');
		var field_value = $(this).val();
		var field_type = $(this).attr('type');
		var is_email = $(this).hasClass('email');
		
		if (is_req == true){

			var field_value = $(this).val();
			var field_name = jQuery(this).closest(".form-group").find("label").attr('title');
			if (field_value.length > 0){


				//email and phone validation begins

				jQuery(this).closest(".form-group").removeClass('has-danger');
				jQuery(this).parent("div").children("div.form-control-feedback").remove();
				$(this).css('border-color', '#ebedf2');
				
				if(is_email){
					if( isValidEmailAddress( field_value ) ) {
						jQuery(this).closest(".form-group").removeClass('has-danger');
						$(this).css('border-color', '#ebedf2');
						jQuery(this).parent("div").children("div.form-control-feedback").remove();
					}
					else{
						jQuery(this).closest(".form-group").addClass('has-danger');
						$(this).css('border-color', '#f4516c');
						var error_msg = "<div id='"+field_name+"' class='form-control-feedback'>"+email_validation_msg+"</div>";
						jQuery(this).parent("div").children("div.form-control-feedback").remove();
						jQuery(this).after(error_msg);
					}
				}
				

				//email and phone validation ends
			
			}else{
				jQuery(this).closest(".form-group").addClass('has-danger');
				$(this).css('border-color', '#f4516c');
				var error_msg = "<div id='"+field_name+"' class='form-control-feedback'>"+required_field_validation_msg+"</div>";
				jQuery(this).parent("div").children("div.form-control-feedback").remove();
				$(this).after(error_msg);
			}


		}

	});

	$(".dashboard-dropdown").select2();

	/*
	 * js code for 
	 * bootstrap popover
	 */
    $('[data-toggle="popover"]').popover({
        placement : 'right',
        trigger: 'click',
        html: true
    });

    //send new email link

    $('button#send_email_change_link').on('click', function(){

    	var email = $('#new_email').val();    	

		if(email.length > 0) {	

			if( isValidEmailAddress( email ) ) {
				
				$('#ajax_load').show();
			    
	    		$.ajax({
	    
	    			url: "<?php echo base_url();?>dashboard/change_email",
	    			data: { email : email },
	    			dataType: 'json',
	    			type: "POST",
	    			success: function (response) {
	    
	    				
	    				$('#ajax_load').hide();
	    
	    				if (response.status == '1') {	    
	    					toastr.success(response.message);
	    				}
	    				else{
	    					toastr.error(response.message);
	    				}
	    			}
	    		});
	    		
			}else{
				toastr.error('<?php echo dashboard_lang("_ENTER_A_VALID_EMAIL_ADDRESS"); ?>');
			}	
		}
		else{			
			toastr.error("<?php echo dashboard_lang('_ENTER_YOUR_NEW_EMAIL_ADDRESS'); ?>");
		}
    	
    });

	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	  var next_position = $(e.target).attr("data-position") // activated tab
	  $('#selected_tab').val(next_position);
	});

	$(".tab-content").on( "click", "#numberVarification", function () {
		var saveOrEdit = $('div#numberVarificationContainer').attr('data-link');
		if(saveOrEdit == "save"){
			var field_ctx = $('#mobile_postfix');
			var field_name = field_ctx.attr('name');
			var field_value = $('#mobile_number').val();
			var patt1 = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{5,7}$/im;
			var result = field_value.match(patt1);
			if(!result){
				$('.mobile_number_alert_msg').remove();
				$("#field_id_mobile_postfix").removeClass('has-danger');
				field_ctx.siblings("div.form-control-feedback").remove();
				$("#field_id_mobile_postfix").addClass('has-danger');
				var error_msg = '<div class="form-control-feedback '+ field_name +'">'+phone_validation_msg+'</div>';
				field_ctx.after(error_msg);
			}else{
				$("#field_id_mobile_postfix").removeClass('has-danger');
				field_ctx.siblings("div.form-control-feedback").remove();
				verification_ajax_request( 'verify');
			}
		}else if(saveOrEdit == 'edit'){
			$('#mobile_postfix').attr('disabled', false);
			$('#country_code').attr('disabled', false);
			$("button#numberVarification").text(verify_link_label);
			$('div#numberVarificationContainer').attr('data-link', 'save');
			$('span#verificationMessage').html("");
			is_phone_verified = 0;

		}
	});

	$( ".tab-content" ).on( "click", "#submit_code", function () {
		$secretCode = $("#field_id_code>#code").val();
		if( $secretCode.length > 0 ) {
			verification_ajax_request( "submit_code" );
		} else {
			$( ".tab-content" ).find('p#modal_alert_msg').html("<div class='alert alert-danger alert-dismissible fade show m-alert m-alert--square m-alert--air' role='alert'> <button type='button' class='close' data-dismiss='alert' aria-label='Close'></button>"+codeFieldEmptyAlertMsg+"</div>");
		}
	});

	$('body').on('click', '.remove-profile-icon-btn', function () {
	    if ( confirm( profilePicDeleteAlert ) ) {
			var el = $(this);
			var imgUrl = el.attr('data-img-url');
			var imgFrom = el.attr('data-img-from');

			var postUrl = baseURL + 'dashboard/removePropic';
			var postData = { imgUrl: imgUrl, imgFrom: imgFrom };
			var sendpropicChangeReq = $.ajax({ url: postUrl, data: postData, method: 'post', dataType: 'json' });
			sendpropicChangeReq.done( function ( response ) {
				if ( response.success == '1' ) {

					el.hide(function() {
						$('span#profileImgSpan').hide('slow', function () {
							$('span.m-topbar__userpic').children().css('background-image', '');
							$('div.m-card-user__pic').children().css('background-image', '');
							$('span#profileImgSpan').parent().remove();
							toastr.success( response.msg );
						});
					});
					location.reload();
				} else {
					$('span#profileImgSpan').show('slow', function () {
						el.show('slow');
						$('span.m-topbar__userpic').children().css('background-image', imgUrl);
						$('div.m-card-user__pic').children().css('background-image', imgUrl);
					});
					toastr.error( response.msg );
				}
			});

			sendpropicChangeReq.fail( function ( x, y, z ) {
			});
	    }
	});

    
});

	$(document).on('click', function (e) {
	    $('[data-toggle="popover"],[data-original-title]').each(function () {
	        //the 'is' for buttons that trigger popups
	        //the 'has' for icons within a button that triggers a popup
	        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {                
	            (($(this).popover('hide').data('bs.popover')||{}).inState||{}).click = false  // fix for BS 3.3.6
	        }

	    });
	});
function isValidEmailAddress(emailAddress) {
	var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
	return pattern.test(emailAddress);
};

$(".bgImage").on('click',function(){
	var image = $(this).attr("data-image");
	var url = $(this).attr("data-imagepath");
	$('#ajax_load').show();
	$.ajax({
	  url : baseURL +"dashboard/setBgImage", 
      type: "POST", 
      data: {image:image}, 
      datatype:"json",
      success:function(response){
    	  var data = JSON.parse(response);
    	  $('#ajax_load').hide();
    	  if (data.status == '1') {        	  
    	  	$("body").css("background-image", "url("+url+")")
    	  }
	  }		
	});

});

function verification_ajax_request( type ){
	var sent_numbers = "";
	
	var request = $.ajax({
		url: url_verification,
		method: "POST",
		data: { type: type, mobile_number: $('#mobile_number').val(), code: $('#code').val(), mobile_postfix : $("#mobile_postfix").val(), mobile_prefix : $("#country_code").val()},
		dataType: "json"
	});
		 
	request.done(function( msg ) {

		if( msg.status == 'alert' ){
			$('#mobile_postfix').attr('disabled', true);
			$('#country_code').attr('disabled', true);
			$("button#numberVarification").text(edit_link_label);
			$('div#numberVarificationContainer').attr('data-link', 'edit');
			$('span#verificationMessage').html(msg.msg);
			alert(msg.alert_msg);
			is_phone_verified = 1;
			verified_mobile_number = $('#mobile_number').val();
		} else if( msg.status == 'modal' ){
			$('span#verificationMessage').after(msg.html_render);
			$("#code_verification_modal").modal("show");
		} else if ( msg.status == '1' ){
			$('#mobile_postfix').attr('disabled', true);
			$('#country_code').attr('disabled', true);
			$("button#numberVarification").text(edit_link_label);
			$('div#numberVarificationContainer').attr('data-link', 'edit');
			$('span#verificationMessage').html(msg.msg);
			$("#code_verification_modal").modal("hide");
			$('.check_color').remove();
			is_phone_verified = 1;
			verified_mobile_number = $('#mobile_number').val();
			alert( verified_alert_msg );
		} else if( msg.status == '0' ) {
			$("span#verificationMessage").html("");
			$("span#verificationMessage").html(msg.msg);
		} else {
			$("#code_verification_modal").modal("hide");
			$("#verify_mbl_number").remove();
			$("button#numberVarification").text(edit_link_label);
			$('div#numberVarificationContainer').attr('data-link', 'edit');
		}
	});
		 
	request.fail(function( jqXHR, textStatus ) {
		alert( "Request failed: " + textStatus );
	});
	
}
	
	
	
$('#update-password').on('click', function(e){
	e.preventDefault();

	var password = $("#password").val();
	var re_password = $("#re-password").val();
	
	var status = checkStrongPassword(password, re_password);

	if ( status ) {

		$.ajax({
			url: update_password,
			method: "POST",
			data: { new_password: password, retype_password:re_password},
			success:function( response ){

				var obj = JSON.parse( response );
				if(obj.status){
					toastr.success(obj.msg);
				}else{
					toastr.error(obj.msg);
				}
			}
		});

	}

});

function checkStrongPassword(new_password, retype_password){

	var remove_space_new_pass =  new_password.split(" ").join("");
	if(!new_password){
		showMessage('Enter new passwoed')
		return false;
	}else if(!retype_password){
		showMessage('Enter retype passwoed')
		return false;
		
	}else if (new_password !== retype_password){
		showMessage('New password and retype passwoed not match')
		return false;

	}else if ( new_password.length < 9 ) {

		showMessage('9 Character password required')
		return false;

	}else if (remove_space_new_pass.length < 9) {

		showMessage('9 Character password required')
			return false;

	} else {
	var alert_text = '';
	var status = true;
	var pattern = '';
	
		pattern = /[A-Z]/;
		if(!pattern.test(new_password)){
			alert_text += 'Please enter at least one uppercase letter'+'\n';
			status = false;
			
		}

		pattern = /[a-z]/;
		if(!pattern.test(new_password)){
			alert_text += 'Please enter at least one lowercase letter'+'\n';
			status = false;
		}

		pattern = /[0-9]/;
		if(!pattern.test(new_password)){
			alert_text += 'Please enter at least one numeric value'+'\n';
			status = false;
		}

		pattern = /~|[\[!@#$%^&*`?()/\-_\\"\'\=+{};:,<.>\]]/;
		if(!pattern.test(new_password)){
			alert_text += 'Please enter at least one special character'+'\n';
			status = false;
		}

		if(!status){
			showMessage(alert_text);
			$("div.generate_password_link").show('slow');
			
		}

		return status;
	}
}
function showMessage ( msg )
{
    if ( typeof isHeader === 'undefined' )
        toastr.error(msg)
    else {
        var loginFormContainer = $(".m-login__form");
        var alertContainer = loginFormContainer.find(".alert-danger");
        if ( alertContainer.length > 0 && alertContainer.children("span").length > 0 ) {
            alertContainer.addClass("display-hide").css("display", "none");
            alertContainer.children("span").html(msg);
            alertContainer.slideDown("slow").removeClass("display-hide")
        } else if ( alertContainer.length > 0 && alertContainer.children("span").length < 1 ) {
            alertContainer.empty().css("display", "none");
            alertContainer.html("<button type='button' class='close' data-dismiss='alert' aria-label='Close'></button><span>"+msg+"</span>")
            alertContainer.slideDown("slow")
        } else {
            var alertContainerHtml = '<div class="m-alert m-alert--outline alert alert-dismissible alert-danger" role="alert" style="display: none;">'+			
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>'+
            '<span>'+msg+'</span>'
            '</div>';
            loginFormContainer.prepend(alertContainerHtml);
            loginFormContainer.find("div.alert").slideDown("slow")
        }
    }
}

$(document).on('change', '#country_code', function(){

	var dialing_code = $(this).val();
	$('.add-mobile-prefix').text(dialing_code);
})

$("input").on("change", function (event) {

var is_email = $(this).hasClass('email');
var field_value = $(this).val();
var field_type = $(this).attr('type');
var field_name = jQuery(this).closest(".form-group").find("label").attr('name');

jQuery(this).closest(".form-group").removeClass('has-danger');
jQuery(this).parent("div").children("div.form-control-feedback").remove();

		
	if (field_value.length > 0){
		jQuery(this).closest(".form-group").removeClass('has-danger');
		jQuery(this).parent("div").children("div.form-control-feedback").remove();
		if(is_email){
			if( isValidEmailAddress( field_value ) ) {
				jQuery(this).closest(".form-group").removeClass('has-danger');
				jQuery(this).parent("div").children("div.form-control-feedback").remove();
			}
			else{
				jQuery(this).closest(".form-group").addClass('has-danger');
				var error_msg = "<div id='"+field_name+"' class='form-control-feedback'>"+email_validation_msg+"</div>";
				jQuery(this).after(error_msg);
			}
		}
		
		
		 
	} 		


});

var mfaTokenVerified = false;
$('.mfa_status').on('change', function(){
	var current_value = $(this).val();
	if( current_value == 2 && mfaTokenVerified == false){
		$.post(baseURL+'dbtables/dashboard_login/getQRCode',{user_id: id}, function(response){
			data = JSON.parse( response );
			if( data.status == 1 ){
				$('#verify-the-token').removeClass('hidden');
				$('.authy-token-generated-by-app').removeClass('hidden');
				$('#authy-generated-qr-code').attr('src' , data.message.qr_code);
			}else{
				$('#verify-the-token').addClass('hidden');
				$('.authy-token-generated-by-app').addClass('hidden');
				$('.authy-qr-generation-error-message').html( data.message ); 
			}

			$('#authyModal').modal('show');
		});
	}else{
		// no operation required
	}	
});
$("body").on('click', '#verify-the-token', function () {
	var token = $("#token").val();
	$.post(baseURL+'dbtables/dashboard_login/verify_mfa_token',{user_id: id, token: token}, function(response){
		var obj = JSON.parse(response);
		// console.log(obj);
		if(obj.status == '1'){
			toastr.success(obj.message);
			$('#authyModal').modal('hide');
            mfaTokenVerified = true;
		}else{
			toastr.error(obj.message);
		}
	});
});

</script> 
