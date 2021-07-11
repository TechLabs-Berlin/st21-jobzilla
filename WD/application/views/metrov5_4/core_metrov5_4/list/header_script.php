<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/listing.css" rel="stylesheet">
<link href="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/css/jquery.tokenize.css" rel="stylesheet">
<script type="text/javascript">
    var siteURL = "<?php echo $site_url; ?>";
    var controllerFolder = "<?php echo $controller_sub_folder; ?>";
    var controller_name = "<?php echo $table_name; ?>";
    var userId = "<?php echo get_user_id(); ?>";
    var selectItemCheckbox = "<?php echo dashboard_lang("_PLEASE_SELECT_AN_ITEM") ?>";
    var confirmDeleteAlert = "<?php echo dashboard_lang("_ARE_YOU_SURE_TO_DELETE") ?>";
    var confirmRestoreAlert = "<?php echo dashboard_lang("_ARE_YOU_SURE_TO_RESTORE") ?>";
    var confirmCopyAlert = "<?php echo dashboard_lang("_ARE_YOU_SURE_TO_COPY") ?>";
    var search_auto_suggest_limit = "<?php echo $this->config->item('search_auto_suggest_limit'); ?>";
</script>
<div id="ajax_load" style="display: none">
    <img         src="<?php echo CDN_URL; ?>img/ajax-loader.gif" alt="" />
</div>
