<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<div class="copyright">

</div>
<!-- END LOGIN -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->

<script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/scripts.bundle.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->


<!-- END PAGE LEVEL SCRIPTS -->


<script type="text/javascript">

$(document).ready(function(){

	$(document).on("click","#reset_submit",function(){
		var pass1 = $("#new_password").val();
		var pass2 = $("#re_password").val();

		if(pass1 != pass2){
			  alert("<?php echo dashboard_lang('_RE_PASS_NOT_MATCH'); ?>");
			  return false;
		}
		
	});
	
});

</script>

<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>
