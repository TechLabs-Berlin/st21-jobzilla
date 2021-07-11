<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$site_title = $this->config->item('site_title');
$this->load->helper ( 'dashboard_list' );
$this->load->helper ( 'dashboard_main' );
$this->load->helper ( 'dashboard_menu' );
?>
<div>
	<h1><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_FAILED') . '"' . $failed_reason . '"'; ?></h1>
	
</div>
