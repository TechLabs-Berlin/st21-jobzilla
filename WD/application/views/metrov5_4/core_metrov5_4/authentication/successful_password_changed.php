<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->load->view('metrov5_4/core_metrov5_4/authentication/header');
?>
<style>
.display-hide { display:none; }
</style>
<div class="m-login__signin m-login__form m-form" style="">
    <div class="m-login__head">
        <h3 class="m-login__title"><?php echo dashboard_lang('_RESET_PASSWORD_SUCCESSFULLY_NOW_YOU_CAN_CONTINUE'); ?></h3>
    </div>
    <div class="m-login__form-action">
        <?php 
        echo render_button ('continue', 'continue', 'btn btn-focus m-btn m-btn--pill m-btn--custom    m-login__btn m-login__btn--primary colored-big-button', 'button' , dashboard_lang('_CONTINUE'), '' , '');
        ?>
    </div>
</div>
<script>
$(function () {
    $("#continue").on("click", function () {
        window.open("<?php echo @$redirect_url; ?>", "_SELF");
    })
})
</script>
<?php $this->load->view('metrov5_4/core_metrov5_4/authentication/footer');?>