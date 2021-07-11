<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<script type="text/javascript">
function checkStrongPassword(new_password, retype_password){

    var remove_space_new_pass =  new_password.split(" ").join("");
    if(!new_password){
    	showMessage('<?php echo dashboard_lang('_ENTER_NEW_PASSWOED');?>')
        return false;
    }else if(!retype_password){
    	showMessage('<?php echo dashboard_lang('_ENTER_RETYPE_PASSWOED');?>')
        return false;
        
    }else if (new_password !== retype_password){
    	showMessage('<?php echo dashboard_lang('_NEW_PASSWORD_AND_RETYPE_PASSWOED_NOT_MATCH');?>')
        return false;

    }else if ( new_password.length < <?php echo $this->config->item('password_character_limit');?> ) {

    	showMessage('<?php echo $this->config->item('password_character_limit')." ".dashboard_lang('_CHARACTER_PASSWORD_REQUIRED');?>')
        return false;

    }else if (remove_space_new_pass.length < <?php echo $this->config->item('password_character_limit');?>) {

    	showMessage('<?php echo $this->config->item('password_character_limit')." ".dashboard_lang('_CHARACTER_PASSWORD_REQUIRED');?>')
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

        pattern = /~|[\[!@#$%^&*`?()/\-_\\"\'\=+{};:,<.>\]]/;
        if(!pattern.test(new_password)){
        	alert_text += '<?php echo dashboard_lang ('_PLEASE_ENTER_AT_LEAST_ONE_SPECIAL_CHARACTER');?>'+'\n';
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
</script>
