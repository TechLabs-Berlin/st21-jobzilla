<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$dateFormat = $this->config->item('#DEFAULT_DATE_FORMAT');
?>
<table border="0" cellpadding="5" style="font-family: Arial; font-size: 13px; width:100%;">
  <tbody>
    <tr>
      <td width="250" align="left"> <?php echo $this->config->item('#APPLICATION_NAME') . ' - ' . @$tableName;?></td>
      <td valign="bottom" align="right"> {PAGENO} <?php echo dashboard_lang("_OF");?> {nbpg} </td>
    </tr>
    <tr>
      <td colspan="2"><hr style="border-color:#666666;"/></td>
    </tr>
  </tbody>
</table>