<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
require_once (FCPATH.'application/views/metrov5_4/core_metrov5_4/authentication/header.php');
?>

<?php $title = dashboard_lang('_USER_NOTIFICATION'); ?>

        <script src="<?php echo CDN_URL; ?>media_metrov5_4/assets/demo/default/base/scripts.bundle.js" type="text/javascript"></script>
        <script type="text/javascript">
        $(function() {

        	var isSuccess = "<?php echo $isSuccess; ?>";
            var msg = "<?php echo $msg; ?>";
            var title = "<?php echo $title; ?>";
            var actionLink = "<?php echo $actionLink; ?>";
            var actionText = "<?php echo $actionText; ?>";

            toastr.options = {
          		  "closeButton": false,
          		  "debug": false,
          		  "newestOnTop": false,
          		  "progressBar": false,
          		  "positionClass": "toast-top-center",
          		  "preventDuplicates": false,
          		  "onclick": null,
          		  "showDuration": "300",
          		  "hideDuration": "1000",
          		  "timeOut": 0,
          		  "extendedTimeOut": 0,
          		  "showEasing": "swing",
          		  "hideEasing": "linear",
          		  "showMethod": "fadeIn",
          		  "hideMethod": "fadeOut",
          		  "tapToDismiss": false
          		};
			if ( isSuccess !== undefined && isSuccess === '1' )
          		toastr.success(msg + "<br /><br /><a class='btn btn-outline-light btn-sm m-btn   m-btn--wide clear' href='"+actionLink+"'>"+ actionText +"</a>", title);
			else
				toastr.error(msg + "<br /><br /><a class='btn btn-outline-light btn-sm m-btn   m-btn--wide clear' href='"+actionLink+"'>"+ actionText +"</a>", title);
        });
        </script>

    </body>

</html>