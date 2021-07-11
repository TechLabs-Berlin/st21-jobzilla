<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.25/jquery.fancybox.min.css" />
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/edit.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript">  
  document.cookie="baseURL = "+baseURL;
  var siteURL = "<?php echo $site_url; ?>"
  var userId = "<?php echo get_user_id(); ?>";
  <?php
  $tab_segment = 0;
  $segment = $this->uri->segment(6);
  // echo $segment; die();
  if(!empty($segment) && is_numeric($segment)){
      $tab_segment = $segment;
  }else{
      $tab_segment = $this->uri->segment(5);
  }
  
  ?>
  var segment5 = "<?php echo $tab_segment; ?>";
  var controller_name = "<?php echo $class_name; ?>";
  var controller_sub_folder = "<?php echo $controller_sub_folder; ?>";
  var selectItemCheckbox = "<?php echo dashboard_lang("_PLEASE_SELECT_AN_ITEM") ?>";
  var confirmDeleteAlert = "<?php echo dashboard_lang("_ARE_YOU_SURE_TO_DELETE") ?>";
  var confirmCopyAlert = "<?php echo dashboard_lang("_ARE_YOU_SURE_TO_COPY") ?>";
  var required = "<?php echo dashboard_lang("_REQUIRED") ?>";
  var date_picker_position = '<?php echo $this->config->item('date_picker_position')?>';
</script>
<script type="text/javascript" src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/2fa.js"></script>
<div id="ajax_load" style="display: none">
  <img src="<?php echo CDN_URL; ?>img/ajax-loader.gif" />
</div>
