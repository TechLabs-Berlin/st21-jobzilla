<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/authentication/header.php');
?>
<!-- BEGIN LOGIN -->
<div class="content">
	<!-- BEGIN REGISTRATION FORM -->
	
    <div class="border-btm"></div>
	<?php if(isset($_GET['slug'])):?>
	    <input type="hidden" name="user_slug" value="<?php echo $_GET['slug']; ?>" />
	<?php endif; ?>
		
		<div class="m-login__head">
		    <h3 class="m-login__title"><?php echo dashboard_lang('_SIGN_UP');?>  <?php echo (isset($account) && !empty($account) && isset($account['name'])) ? "- {$account['name']}" : ''; ?></h3>
		</div>
        
		<div class="alert <?php if( $this->session->userdata("alert_success") == 1){ echo "alert-success ";} else{ echo "alert-danger ";} if ( !($this->session->userdata('sign_up_message') ) ){ echo 'hide'; } else{echo "show";} ?> alert-dismissible fade" role="alert">
			<?php if ($this->session->userdata("sign_up_message")): ?>
			<span>
				<?php echo $this->session->userdata("sign_up_message");
				$this->session->unset_userdata("sign_up_message");
				$this->session->unset_userdata("alert_success");

				?>
			</span>
            <?php endif; ?>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>

		<form class="m-login__form m-form signup-form" action="" method="post" role="form">
			<div class="form-group m-form__group">
				<input class="form-control m-input" type="text" placeholder="<?php echo dashboard_lang('_COMPANY_NAME');?>" name="company_name" id="company_name" value="<?php echo set_value('company_name'); ?>">
			</div>	
			<div class="form-group m-form__group">
				<input class="form-control m-input" type="text"  placeholder="<?php echo dashboard_lang('_FIRST_NAME');?>" name="first_name" id="first_name" value="<?php echo set_value('first_name'); ?>">					    
			</div>
			
			<div class="form-group m-form__group">
				<input class="form-control m-input" type="text" placeholder="<?php echo dashboard_lang('_LAST_NAME');?>" name="last_name" id="last_name" value="<?php echo set_value('last_name'); ?>">
			</div>
			
			<?php $email_error = form_error('email'); ?>
			<div class="form-group m-form__group <?php echo (strlen($email_error)>0)?'has-error':''; ?>">
				<input class="form-control m-input" type="text" placeholder="Email" name="email" value="<?php echo set_value('email'); ?>" autocomplete="off">
				<?php echo $email_error; ?>
			</div>
			<?php $mobile_error = form_error('mobile_number'); ?>
			<div class="form-group m-form__group <?php echo (strlen($mobile_error)>0)?'has-error':''; ?>">
				<input class="form-control m-input" type="text" placeholder="<?php echo dashboard_lang('_MOBILE_NUMBER');?>" name="mobile_number" id="mobile_number" value="<?php echo set_value('mobile_number'); ?>" autocomplete="off">
				<?php echo $mobile_error; ?>
			</div>
			
			<div class="g-recaptcha m--margin-top-30" data-sitekey="<?php echo $this->config->item("recaptcha_site_key");?>"></div>
			<div class="row form-group m-form__group m-login__form-sub">
				<div class="col m--align-left">
					<label class="m-checkbox m-checkbox--light">
					<input type="checkbox" name="tnc" checked="checked" >
					<?php echo dashboard_lang('_I_AGREE_TO_THE');?>
						<a href="<?php echo base_url() . "dashboard/terms_of_service"; ?>"><?php echo dashboard_lang('_TERMS_OF_SERVICE');?></a>&nbsp;<?php echo dashboard_lang(' _AND');?>
						<a href="<?php echo base_url() . "dashboard/privacy_policy"; ?>"><?php echo dashboard_lang('_PRIVACY_POLICY');?> </a>
						<span> </span>
					</label>
					<span class="m-form__help"></span>
				</div>
			</div>
			<div class="m-login__form-action">
				
				<?php
					echo  render_button ('', 'm_login_signup_submit', 'btn m-btn m-btn--pill m-btn--custom  m-login__btn m-login__btn--primary colored-big-button', 'submit' , dashboard_lang('_SIGN_UP'), '','');
					echo  render_button ('', 'm_login_signup_submit', 'btn m-btn m-btn--pill m-btn--custom  m-login__btn m-login__btn--primary colored-big-button signup-back-btn', 'button' , dashboard_lang('_BACK'), '','');
				?>

			</div>
			
			<?php

			$msg = $this->config->item('encryption_text');
			$encrypted_string = $this->encryption->encrypt($msg);

			?>

			<input type="hidden" name="token" value="<?php echo $encrypted_string; ?>" />
		</form>


</div>

<script>

    $(document).ready(function(){

       $(".signup-back-btn").on("click", function(){ 

             window.location = "<?php echo base_url();?>";
        });
        
        $('.signup-form').on('submit', function(e){
            e.preventDefault();
            if ($("input[type='checkbox'][name='tnc']:checked").length) {
                var new_password = $("#register_password").val();
				var company_name = $("#company_name").val();
				var first_name = $("#first_name").val();
				var last_name = $("#last_name").val();
                var retype_password = new_password;
				var nameStatus = checkCompanyOrUserName(company_name, first_name, last_name);
            	if(nameStatus){
					this.submit();
					// var passwordStatus = checkStrongPassword(new_password, retype_password);
					// if(passwordStatus){
					// 	this.submit();
					// }                	
            	}
            }
            else{
                alert("<?php echo dashboard_lang('_YOU_MUST_AGREE_WITH_THE_TERMS_AND_CONDITIONS'); ?>");
            }
        });

        function checkStrongPassword(new_password, retype_password){

    	    var remove_space_new_pass =  new_password.split(" ").join("");
    	    var pre_text = '<?php echo dashboard_lang ('_FOR_STRONG_PASSWORD'); ?>'+'\n';
    	    if (new_password !== retype_password){

    	        alert('<?php echo dashboard_lang('_NEW_PASSWORD_AND_RETYPE_PASSWOED_NOT_MATCH');?>')
    	        return false;

    	    }else if ( new_password.length < <?php echo $this->config->item('password_character_limit');?> ) {

    	        alert('<?php echo $this->config->item('password_character_limit')." ".dashboard_lang('_CHARACTER_PASSWORD_REQUIRED');?>')
    	        return false;

    	    }else if (remove_space_new_pass.length < <?php echo $this->config->item('password_character_limit');?>) {

    	             alert('<?php echo $this->config->item('password_character_limit')." ".dashboard_lang('_CHARACTER_PASSWORD_REQUIRED');?>')
    	             return false;

    	    } else {
    	       var alert_text = '';
    	       var status = true;
    	       var pattern = '';
    	       
    	    	pattern = /[A-Z]/;
    	        if(!pattern.test(new_password)){
    	        	alert_text += '<?php echo dashboard_lang ('_PLEASE_ENTER_AT_LEAST_ONE_UPPERCASE_LETTER'); ?>'+'\n';
    	            status = false;
    	            
    	        }

    	        pattern = /[a-z]/;
    	        if(!pattern.test(new_password)){
    	        	alert_text += '<?php echo dashboard_lang ('_PLEASE_ENTER_AT_LEAST_ONE_LOWERCASE_LETTER');?>'+'\n';
    	            status = false;
    	        }

    	        pattern = /[0-9]/;
    	        if(!pattern.test(new_password)){
    	        	alert_text += '<?php echo dashboard_lang ('_PLEASE_ENTER_AT_LEAST_ONE_NUMERIC_VALUE');?>'+'\n';
    	            status = false;
    	        }

    	        pattern = /~|[!@#$%^&*()\-_=+{};:,<.>]/;
    	        if(!pattern.test(new_password)){
    	        	alert_text += '<?php echo dashboard_lang ('_PLEASE_ENTER_AT_LEAST_ONE_SPECIAL_CHARACTER');?>'+'\n';
    	            status = false;
    	        }

    	        if(!status){
    	            alert(pre_text+alert_text);
    	            $("div.generate_password_link").show("slow");
    	            return status;
    	        }else{
    	        	return status;
    		    }

    	    }
    	}
		/**
		 * User must input company name or First name and Last name
		 */
		function checkCompanyOrUserName(company_name, first_name, last_name){
			var status = true;
			if(company_name.trim().length == 0){
				if(first_name.trim().length == 0 || last_name.trim().length == 0){
					alert('<?php echo dashboard_lang('_YOU_MUST_INPUT_COMPANY_NAME_OR_FIRST_NAME_AND_LAST_NAME');?>')
					status = false;
				}
			}
			return status;			
		}

		$('body').on('keypress', 'input#mobile_number', function (event) {
			var inputValue = event.which;
			var mobileNumber = $("input#mobile_number").val();
			if ( inputValue == 48 && mobileNumber.length < 1) {
				event.preventDefault();
			}else if ( (inputValue > 47 && inputValue < 58) || inputValue == 43) {
				//
			}else{
				event.preventDefault();
			}
		});
    });
</script>

<?php $this->load->view('metrov5_4/core_metrov5_4/authentication/footer');?>
