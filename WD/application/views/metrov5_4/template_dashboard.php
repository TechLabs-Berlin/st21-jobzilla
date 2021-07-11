<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
$data ['base_url'] = base_url ();
$data ['site_url'] = site_url ();
$data ['site_title'] = $this->config->item ( 'site_title' );
$data ['site_logo'] = $this->config->item ( 'site_logo' );
$data ['site_favicon'] = $this->config->item ( 'site_favicon' );
$data ['content'] = @$content;
$this->load->helper ( 'dashboard_list' );
$this->load->helper ( 'dashboard_main' );
$this->load->helper ( 'dashboard_menu' );
$template= $this->config->item('template_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet"
	href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

<!-- MetisMenu CSS -->
<link
	href="<?php echo CDN_URL; ?>dashboardmedia/css/plugins/metisMenu/metisMenu.min.css"
	rel="stylesheet">

<!-- DataTables CSS -->
<link
	href="<?php echo CDN_URL; ?>dashboardmedia/css/plugins/dataTables.bootstrap.css"
	rel="stylesheet">

<!-- Custom CSS -->
<link
	href="<?php echo CDN_URL; ?>dashboardmedia/css/sb-admin-2.css"
	rel="stylesheet">

<!-- Custom Fonts -->
<link
	href="<?php echo CDN_URL; ?>dashboardmedia/font-aweosome-4.1.0/css/font-awesome.min.css"
	rel="stylesheet" type="text/css">

<link rel="stylesheet"
	href="<?php echo CDN_URL; ?>dashboardmedia/css/typeahead.css">
<link rel="stylesheet"
	href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">


<script
	src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"
	type="text/javascript"></script>

<!-- Latest compiled and minified JavaScript -->
<script
	src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"
	type="text/javascript"></script>

<!-- Metis Menu Plugin JavaScript -->
<script
	src="<?php echo CDN_URL; ?>dashboardmedia/js/plugins/metisMenu/metisMenu.min.js"
	type="text/javascript"></script>

<!-- Custom Theme JavaScript -->
<script
	src="<?php echo CDN_URL; ?>dashboardmedia/js/sb-admin-2.js"
	type="text/javascript"></script>

<script
	src="<?php echo CDN_URL;?>dashboardmedia/js/typeahead.bundle.min.js"
	type="text/javascript"></script>
<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"
	type="text/javascript"></script>

	

<!-- Common CSS -->
<link
	href="<?php echo CDN_URL; ?>dashboardmedia/css/common.css"
	rel="stylesheet">
<?php
if (dashboard_view_exists ( 'core_override/dashboard_metaitems' )) {
	
	$this->load->view ( 'core_override/dashboard_metaitems', $data );
} else {
	
	$this->load->view ( 'core_'.$template.'/dashboard_metaitems', $data );
}
?>

</head>
<body>
	<div id="wrapper">
		<!-- Navigation -->
		<nav class="navbar navbar-default navbar-static-top" role="navigation"
			style="margin-bottom: 0">
			<div class="navbar-header">

                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                
                <div>
					<?php
                        if (dashboard_view_exists ( 'core_override/dashboard_header' )) {
                            $this->load->view ( 'core_override/dashboard_header', $data );
                        } else {
                           $this->load->view ( 'core_'.$template.'/dashboard_header', $data );
                        }
                    ?>
                </div>
                            
                
			</div>
			<!-- End navbar-header -->

			<ul class="nav navbar-top-links navbar-right">
				<?php
				if (dashboard_view_exists ( 'core_override/dashboard_topright' )) {
					
					$this->load->view ( 'core_override/dashboard_topright', $data );
				} else {
					
					$this->load->view ( 'core_'.$template.'/dashboard_topright', $data );
				}
				?>
			</ul>
			<!-- /.navbar-top-links -->

			<div class="navbar-default sidebar" role="navigation">
				<?php
				if (dashboard_view_exists ( 'core_override/dashboard_leftmenu' )) {
					
					$this->load->view ( 'core_override/dashboard_leftmenu', $data );
				} else {
					
					$this->load->view ( 'core_'.$template.'/dashboard_leftmenu', $data );
				}
				?>
			</div>
		</nav>
		<!-- End nav -->

		<!-- Page Content -->
		<div id="page-wrapper">			
			<?php if($this->session->flashdata('flash_message')):
					$className = "alert-success";
			
					if($this->session->userdata('dashboard_application_message_type') == 'error'){
						$this->session->set_userdata('dashboard_application_message_type', '');
						$className = "alert-danger";
					}
			?>
			<div class="alert alert-block <?php echo $className; ?> fade in">
				<button type="button" class="close" data-dismiss="alert"></button>                    
                 <p><?php echo $this->session->flashdata('flash_message'); ?></p>                       
            </div>
			
			<!-- End Heading -->
			<?php endif;?>		
			<?php
			if (dashboard_view_exists ( 'core_override/dashboard_maincontent' )) {
				
				$this->load->view ( 'core_override/dashboard_maincontent', $data );
			} else {
				
				$this->load->view ( 'core_'.$template.'/dashboard_maincontent', $data );
			}
			?>			
		</div>
		
		

	</div>
	<!-- End wrapper -->

</body>
</html>