<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('metrov5_4/core_metrov5_4/email/header');?>

<tr>
    <td class="dear_user" valign="top" colspan="2" style="padding-top:50px; color:#333333; padding-bottom: 15px;"><?php echo dashboard_lang("_DEAR"). " ".$first_name.",";?></td>
</tr>

<tr>
    <td valign="top" colspan="2" style="color:#333333;padding-bottom: 22px;">
        <?php

        echo dashboard_lang('_NEW_USER_APPROVE_EMAIL_FIRST_LINE');

        ?>
    </td>
</tr>

<tr>
    <td valign="top" colspan="2" style="color:#fff;text-align: center;font-size: 20px;">
        <a onMouseOver="this.style.backgroundColor='#36a3f7';"  onMouseOut="this.style.backgroundColor='#36a3f0';" style="font-family:Arial;background:#36a3f7;text-decoration:none !important;text-align:center;border: medium none;color:#ffffff !important;font-size: 20px;font-weight: 700;width: 100%;display:block;padding: 12px 0;" class="reset-password-btn" target="_blank" href='<?php echo base_url()."dbtables/dashboard_login/edit/".$user_id;?>'><?php echo dashboard_lang('_CLICK_HERE_TO_ACTIVE_THE_USER'); ?></a>
    </td>
</tr>

<tr>
    <td valign="top" colspan="2" style="color:#333333;padding-top: 30px;padding-bottom:30px;">
        <?php

        echo dashboard_lang('_NEW_USER_APPROVE_EMAIL_LAST_LINE');
        ?>
    </td>
</tr>


<?php $this->load->view('metrov5_4/core_metrov5_4/email/footer');?>

   
