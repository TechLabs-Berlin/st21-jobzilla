<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('metrov5_4/core_metrov5_4/email/header');?>

<tr>
    <td class="dear_user" valign="top" colspan="2" style="padding-top:50px; color:#333333; padding-bottom: 15px;"><?php echo dashboard_lang("_DEAR"). " ".$name.",";?></td>
</tr>

<tr>
    <td valign="top" colspan="2" style="color:#333333;">
        <?php

        echo $title;

        ?>
    </td>
</tr>

<tr>
    <td valign="top" colspan="2" style="color:#333333; padding-top: 22px;">
        <?php //echo dashboard_lang('_LOGIN').": "."https://portal.boodev.co/dashboard"; ?>
    </td>
</tr>

<tr>
    <td valign="top" colspan="2" style="color:#333333;">
        <?php echo dashboard_lang('_DATE').": ".date(" d - m - Y h:i A", $date); ?>
    </td>
</tr>


<tr>
    <td valign="top" colspan="2" style="color:#333333;">

        <?php echo dashboard_lang('_ERROR').": "; print_r($response); ?>

    </td>

</tr>

<tr>
    <td valign="top" colspan="2" style="color:#333333;padding-top: 30px;padding-bottom:30px;">

    </td>
</tr>

<?php $this->load->view('metrov5_4/core_metrov5_4/email/footer');?>