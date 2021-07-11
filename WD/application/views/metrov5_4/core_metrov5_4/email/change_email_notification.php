<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('metrov5_4/core_metrov5_4//email/header');?>

<tr>
    <td class="dear_user" valign="top" colspan="2" style="padding-top:50px;color:#333333;font-size:14px;padding-bottom: 15px;"><?php echo dashboard_lang("_DEAR"). " ".$first_name.",";?></td>
</tr>

<tr>
    <td valign="top" colspan="2" style="color:#333333;padding-bottom:22px;">
        <?php echo dashboard_lang('_YOUR_EMAIL_CHANGED_FROM')." ".$old_email_address." ".dashboard_lang("_TO")." ".$new_email_address; ?>
    </td>
</tr>

<tr>
    <td valign="top" colspan="2" style="color:#333333; padding-top: 30px; padding-bottom:30px;">
        <?php echo dashboard_lang('_CHANGE_EMAIL_NOTIFICATION_MESSAGE'); ?>
    </td>
</tr>


<?php $this->load->view('metrov5_4/core_metrov5_4/email/footer');?>
