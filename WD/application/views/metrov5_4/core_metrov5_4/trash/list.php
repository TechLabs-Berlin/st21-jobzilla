<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$perPegeArray = $this->config->item("list_per_page");
$base_url = base_url();
$site_url = rtrim(site_url(), '/') . '/';
$controller_sub_folder = $this->config->item('controller_sub_folder');
$img_upload_path = $this->config->item('img_upload_path');
$dashboard_helper = Dashboard_main_helper::get_instance();
$dashboard_helper->set('edit_path', $edit_path);
$template = $this->config->item('template_name');
$table_name = $this->uri->segment(2);
$trash_dir_name = "trash";
$listing_path = $site_url.$controller_sub_folder."/".$table_name."/listing";
if (file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/header_script.php')) {
    require_once(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/header_script.php');
}else{
    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/header_script.php');
}
 
?>
<script type="text/javascript">
<!--
var is_searched = false;
//-->
</script>
<div class="trashed-items">
    <div class="m-portlet m-portlet--mobile">
        <div class="m-portlet__head">
            <div class="m-portlet__head-caption">
                <div class="m-portlet__head-title">
                    <h3 class="m-portlet__head-text">
    					<?php echo dashboard_lang('_TABLE_DATA') ." ". dashboard_lang('_'.strtoupper($table_name)); ?>
    				</h3>
    				<a href="<?php echo $listing_path; ?>" class="btn btn-accent m-btn m-btn--icon   m--margin-top-15 m--margin-left-10"><i aria-hidden="true" class="fa fa-angle-left"></i> <?php echo dashboard_lang("_BACK_TO_LIST_PAGE"); ?> </a>
                </div>
            </div>
            <div class="m-portlet__head-tools">
            	<ul class="m-portlet__nav action_row">	
                 	<?php
                 	
                    $action_data['edit_path'] = $edit_path;
                    $action_data['site_url'] = $site_url;
                    $action_data['listing_path'] = $site_url.$controller_sub_folder."/".$table_name."/listing";
                 	      $this->load->view('metrov5_4/core_metrov5_4/trash/list_action', $action_data); 
                 	      
                 	 ?>
                <!--End add edit delete button-->
                </ul>
            </div>
        </div>
                     
            <!--End panel-heading-->            
		<div class="m-portlet__body">
			<div class="m-form m-form--label-align-left m--margin-bottom-30">                
				<div class="row align-items-center">
					<div class="col-md-4 order-2 order-xl-1 pull-left">
                        <?php 
                        if(file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/total_count.php')){

                            require_once(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/total_count.php');
                        }else{
                            require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/total_count.php');
                        }
                        ?>
                    </div>
                    <div class="col-md-8 order-1 order-xl-2  pull-right">
                        <?php 
                        if(file_exists(FCPATH.'application/views/metrov5_4/'.$trash_dir_name.'/list/searchbar.php')){
                            require_once(FCPATH.'application/views/metrov5_4/'.$trash_dir_name.'/list/searchbar.php');
                        }else{
                            require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/searchbar.php');
                        }?>
                    </div>                
                </div>
			</div>
			<div class="m_datatable m-datatable m-datatable--default m-datatable--loaded" id="base_column_width">                       
            	<?php 
                if(file_exists(FCPATH.'application/views/metrov5_4/core_metrov5_4/'.$trash_dir_name.'/list/main.php')){
                    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/'.$trash_dir_name.'/list/main.php');
                }else{
                    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/main.php');
                }?>                            
                <div class="m-datatable__pager m-datatable--paging-loaded clearfix m--pull-right">
					<?php
                        if(!$no_permission){
                            echo $paging;
                        }
                    ?>
                </div>
			</div>
            <!--End pagination row-->
            <div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<?php $this->load->view($viewPathFooter, array('base_url' => $base_url, 'site_url' => $site_url, 'listing_path' => 'dbtables/'.$table_name.'/trash')); ?>
				</div>
			</div>
	        <!--End legal row-->
            
        </div>
    </div>
</div>
<?php if(file_exists(FCPATH.'application/views/metrov5_4/'.$trash_dir_name.'/list/footer_script.php')){
    require_once(FCPATH.'application/views/metrov5_4/'.$trash_dir_name.'/list/footer_script.php' );
}else{
    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/footer_script.php' );
}?>
