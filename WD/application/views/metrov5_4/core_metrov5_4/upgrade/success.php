<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$site_title = $this->config->item('site_title');
$this->load->helper ( 'dashboard_list' );
$this->load->helper ( 'dashboard_main' );
$this->load->helper ( 'dashboard_menu' );
?>
<div>
	<h1><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_SUCCESS') . $success_upgrade_version; ?></h1>
	<div>
		<a href="<?php echo site_url(). $this->session->userdata('current_url'); ?>">
			<button	type="button" class="btn btn-primary">
				<?php echo dashboard_lang('_BACK'); ?>
			</button> 
		</a>
	</div>
</div>
