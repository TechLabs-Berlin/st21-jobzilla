<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->load->view('core_metrov5/authentication/header');
?>

<!-- BEGIN LOGIN -->
<div class="content">
	<!-- BEGIN REGISTRATION FORM -->
	<form class="signup-form" action="" method="post" role="form">
            <div class="border-btm"></div>
            <h3 class="form-title"><?php echo dashboard_lang('_TERMS_OF_SERVICE');?></h3>
		<p><?php echo dashboard_lang('_TERMS_OF_SERVICE_BODY');?></p>
                <div class="form-actions">
                    <a href="<?php echo base_url() . "dashboard/index"; ?>"  id="register-back-btn" type="button" class="btn grey-salsa btn-outline"><?php echo dashboard_lang('_BACK_TO_LOGIN');?></a>
                </div>
</div>
<?php $this->load->view('core_metrov5_4/authentication/footer');?>
