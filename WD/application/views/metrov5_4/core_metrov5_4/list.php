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
$report = false;
if(!empty($xmlData['report']) && $xmlData['report'] == 1){
    $report = true;
}
if(file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/header_script.php')){
    require_once(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/header_script.php' );
}else{
    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/header_script.php' );
}
?>
<?php if($this->session->userdata('export_pdf_error') == 'pdf_eror_show'):
	$this->session->unset_userdata('export_pdf_error');?>
	<div class="user-alert-section " id="alert-div-hide-export">
		<div class="alert alert-danger alert-dismissible fade show m-alert m-alert--air">                 
				<?php echo dashboard_lang('_ERROR_MAXIMUM_NUMBER_OF_RECORDS_FOR_PDF');?>                
		</div>
	</div>
<?php endif;?>

<div class="listing-page">
    <div class="m-portlet m-portlet--mobile">
    	<div class="m-portlet__head">
    		<div class="m-portlet__head-caption">
    			<div class="m-portlet__head-title">
    				<h3 class="m-portlet__head-text">
    					<?php 
    					$listViewName = $report == true? dashboard_lang('_REPORTS_TITLE_PREFIX') : dashboard_lang('_TABLE_DATA');
						if($listViewName){
							echo $listViewName ." ". dashboard_lang('_'.strtoupper($table_name)); 
						}else{
							echo dashboard_lang('_'.strtoupper($table_name));
						}
						?>
    				</h3>
					<?php 
						$user_instance = BUserHelper::get_instance();
						$allTenants = getDataFromId("accounts", 0, "is_deleted");
						if(!empty($user_instance->user_role->extra_authorization)):?>
							<div id="switch-tenants" class="m--padding-left-15">
								<select name="" id="switch-tenants-options" class="form-control" <?php echo ( !is_null( @$hide_save_btn ) )? "disabled": ""; ?>>
									<?php foreach($allTenants as $tenant):
									$selected = '';
									if ( $tenant->id == getActiveTenantId() ) $selected = 'selected';
									echo "<option value='{$tenant->id}' {$selected}>{$tenant->name}</option>";
									endforeach;
									?>
								</select>
								<span style="" class="m--padding-left-10 m--valign-middle m--pull-right">
									<a style="color:white;" class="btn btn-success m-btn m-btn--icon m-btn--icon-only m-btn--pill   m--pull-left custom-info-button" data-trigger="click" data-html="true" data-animation="true" data-toggle="popover" data-content="<?php echo dashboard_lang('_APPOWNER_TENANT_SELECTION_INFO_TEXT'); ?>" data-original-title="" title="">
										<i class="fa fa-info"></i>
									</a>
								</span>
							</div>            
					<?php endif; ?>
    			</div>
    		</div>
    		<div class="m-portlet__head-tools">
    			<ul class="m-portlet__nav action_row">
					<?php if(file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/save_search_template.php')){ ?>
    				<?php $this->load->view($template . '/' . $table_name . '/list/save_search_template', array('all_saved_template' => $all_saved_template, 'listing_field' =>$listing_field , 'ordering_fields' => $ordering_fields, 'selected_template_id' => $selected_template_id)); ?>
					<?php }else{ ?>
					<?php $this->load->view($template . '/core_' . $template . '/list/save_search_template', array('all_saved_template' => $all_saved_template, 'listing_field' =>$listing_field , 'ordering_fields' => $ordering_fields, 'selected_template_id' => $selected_template_id)); ?>
					<?php } ?>
    				<?php if( (!isset($xmlData['hard_delete']) OR (intval($xmlData['hard_delete']) != 1)) && ($add_delete_permissions['delete_permission'] == '1')): ?>                 	
                	<li class="m-portlet__nav-item pull-left">
                    	<div class="trsh-btn-with-info pull-right">
                            <div class="btn btn-default m-btn--icon   trash_btn m--align-left">
                            	<span  data-toggle="m-tooltip" data-trigger="hover" data-placement="top" data-html="true" class="" data-original-title="<?php echo dashboard_lang('_INFO_VIEW_DELETED_ITEMS');?>">
                                <a href="<?php echo base_url().$controller_sub_folder."/".$table_name.'/trash';?>"><i aria-hidden="true" class="fa fa-retweet"></i></a></span>
                            </div>
                                	
                        </div>
                     </li>
                     <?php endif;?>
                     
                     <?php 
     					if(file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/list_action.php')){						
     						$this->load->view("{$template}/{$table_name}/list/list_action", array('site_url' => $site_url, 'edit_path' => $edit_path));
     					}else{
     						$this->load->view($viewPathAction, array('site_url' => $site_url, 'edit_path' => $edit_path));
     					} 
 					?>
                     	
    			</ul>
    		</div>
    	</div>
    	<div class="m-portlet__body">
    		<div class="m-form m-form--label-align-left m--margin-bottom-30">
    			<div class="row align-items-center">
    				<div class="col-xl-4 col-lg-4 col-md-4 pull-left m--font-bold">
    					<?php if(file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/total_count.php')){
                            require_once(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/total_count.php');
                        }else{
                            require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/total_count.php');
                        } 
                        ?>
    				</div>
    				
    				<div class="col-xl-8 col-lg-8 col-md-8 pull-right">
    					<?php 
                        if(file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/searchbar.php')){
                            require_once(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/searchbar.php');
                        }else{
                            require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/searchbar.php');
                        }?>
    				</div>
    			</div>
    		</div>
    		
    		<div class="m_datatable m-datatable m-datatable--default m-datatable--loaded" id="base_column_width">
    			<?php 
                    if(file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/main.php')){
                        require_once(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/main.php');
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
    		<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<?php $this->load->view($viewPathFooter, array('base_url' => $base_url, 'site_url' => $site_url, 'listing_path' => $listing_path)); ?>
				</div>
			</div>
    		
    	</div>
    </div>
</div>
<?php if(file_exists(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/footer_script.php')){
    require_once(FCPATH.'application/views/'.$template.'/'.$table_name.'/list/footer_script.php' );
}else{
    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/list/footer_script.php' );
}?>