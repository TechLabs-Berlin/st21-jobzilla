<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<link href="<?php echo CDN_URL; ?>media_metrov5_4/custom/css/components.css" rel="stylesheet" type="text/css"/>
<script>
    var confirm_text  = "<?php echo dashboard_lang("_ARE_YOU_SURE?")?>";
    var name_text = "<?php echo '( '.dashboard_lang('_NONE').' ) '; ?>";
    var comment_will_email_text = "<?php echo dashboard_lang('_YOUR_COMMENT_WILL_BE_EMAILED_TO');?>";
    var uri_segment_five = '<?php echo $this->uri->segment(5);?>';
    var baseURL = "<?php echo base_url();?>";
    var msgType = "<?php echo $messages_type;?>";
    var entity_id = "<?php echo $this->uri->segment($id_position); ?>";
    var enterMsgDescription = "<?php echo dashboard_lang("_PLEASE_ENTER_MESSAGE_DESCRIPTION")?>";
    var msgTitle = "<?php echo dashboard_lang("_PLEASE_FILL_OUT_TITLE_FIELD")?>";
</script>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/messages.js" type="text/javascript"></script>
<link href="<?php echo CDN_URL;?>media_metrov5_4/portal_core/css/messages.css" rel="stylesheet" type="text/css"/>
