<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>


<div>
	<h1>Welcome to upgrade dashboard version</h1>

	<div>
		<p><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_CONFIRM'); ?></p>		

		<p><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_RESPONSIBLE'); ?></p>
		
		<p><b><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_BACKUPS'); ?></b></p>
		
		<p><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_CLICK'); ?></p>
		
		<p><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_CURRENT_VERSION').': '.$current_version ;?></p>
		<p><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_LIVE_VERSION').': '.$live_version ;?></p>

		<?php if( ( (float) $live_version ) > ( (float) $current_version) ){ ?>
		<a href="<?php echo $url; ?>" id="dashboard_upgrade">
			<button
				type="button" class="btn btn-primary">
				<?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_TO_LATEST'); ?>
			</button>
		</a>
		<?php } else{ ?>
		<p><?php echo dashboard_lang('_DASHBOARD_VERSION_UPGRADE_NO_UPGRADE'); ?></p>
		<?php } ?>
	</div>
	
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
	$("#dashboard_upgrade").click(function(){
		  $.ajax({
				  url:"<?php echo base_url(); ?>upgrade/self_upgrade",
				  success:function(result){
			    	window.location.href='<?php echo $url ; ?>';
			  	  }
		  });
	});
});
</script>
