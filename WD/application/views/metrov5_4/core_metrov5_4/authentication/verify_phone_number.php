<?php
 if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/authentication/header.php');
 ?>
 
 <!-- BEGIN LOGIN -->
 <div id="ajax_loader" style="position: fixed;
    top: 0px;
    background: rgba(0, 0, 0, 0.25);
    height: 100%;
    width: 100%;
    z-index: 99999;display: none;">
    <img style="position: absolute;
        top: 50%;
        left: 50%;
        margin-left: -16px;
        margin-top: -16px;
        z-index: 9999999;" alt="" src="<?php echo CDN_URL;?>img/ajax-loader.gif">
</div>
<?php $this->session->unset_userdata('forget_pass'); ?>
<div class="m-login__signin" style="">
    <div class="m-login__head">
        <h3 class="m-login__title"><?php echo dashboard_lang('_VERIFY_MOBILE_PHONE_NUMBER'); ?> </h3>
    </div>
    <form class="m-login__form m-form" action="<?php echo site_url("dashboard/verify_phone_number"); ?>" method="POST" enctype="multipart/form-data" style="margin-top:1.4rem;" >
    <!-- <div class="m-login__form m-form" style="margin-top:1.4rem;" > -->
        
        <?php $errorAlertText = $this->session->flashdata("sms_sent_error"); if( isset( $errorAlertText ) ) { ?>
            <div class="m-alert m-alert--outline alert alert-danger alert-dismissible" role="alert">			
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>			
            <span> <?php echo $errorAlertText; ?> </span>
        </div>
        <?php } ?>
        
        <?php $alert_text = $this->session->flashdata("sms_sent_success"); if( isset( $alert_text ) ) { ?>
            <div class="m-alert m-alert--outline alert alert-success alert-dismissible" role="alert">			
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>			
            <span> <?php echo $alert_text; ?> </span>
        </div>
        <?php } ?>
            
        <div class="form-group m-form__group">
                <select class="form-control dashboard-dropdown" name="mobile_prefix" id="mobile_prefix">
                    <?php echo $options; ?>
                </select>
            <input class="form-control m-input" id="mobile_postfix" name="mobile_postfix" style="display:inline-block; width: 50%;" type="text" placeholder="<?php echo dashboard_lang('_MOBILE_POSTFIX_NUMBER'); ?>" name="code" autocomplete="off" required>
        </div>
            <input name="type" type="hidden" value="verify">
        
        <div class="m-login__form-action">
            <?php echo  render_button ('', 'submitMobileNumber', 'btn btn-focus m-btn m-btn--pill m-btn--custom    m-login__btn m-login__btn--primary', 'type' , dashboard_lang('_SUBMIT'), '');?>
        </div>
        <!-- </div> -->
    </form>
</div>
 
 
 <script>
    $(function () {
        $('.dashboard-dropdown').select2({ width: '48%' });
    })
 </script>
    <?php require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/authentication/footer.php'); ?>