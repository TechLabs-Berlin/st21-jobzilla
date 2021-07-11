<?php

$template = $this->config->item('template_name');
$this->load->view( $template.'/core_metrov5_4/email/header');?>

<tr>
    <td valign="top" colspan="2" style="color:#333333;padding-bottom:22px;">
       <?php echo $msg_body;?>
    </td>
</tr>
