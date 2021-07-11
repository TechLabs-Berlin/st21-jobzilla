<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
   
    <?php if ($add_delete_permissions['add_permission'] == '1'):?>
        <li class="m-portlet__nav-item button_row m--align-right m--pull-left"> 
            <a href="<?php  echo $site_url.$edit_path; ?>" class="btn_add m--align-left pull-left">
                <?php
                    echo  render_button ('', '', 'btn btn-accent m-btn--icon m--align-left pull-left', 'button' , dashboard_lang('_ADD'), 'plus');
                ?>
            </a>   
        </li>     
    <?php endif; ?>
    <?php if(isset($show_copy) AND $show_copy ) { ?>
        <li class="m-portlet__nav-item button_row m--align-right m--pull-left"> 
            <?php echo render_button ('copy', 'copy', 'btn btn-success m-btn--icon m--align-left pull-left', 'button' , dashboard_lang('_COPY'), 'copy'); ?>
        </li>  
    <?php } ?>
    <?php if ($add_delete_permissions['delete_permission'] == '1') { ?>
        <li class="m-portlet__nav-item button_row m--align-right m--pull-left">
            <?php echo render_button ('delete', 'delete', 'btn btn-danger m-btn--icon m--align-left  pull-left', 'button' , dashboard_lang('_DELETE'), 'remove'); ?>
        </li>
    <?php } ?>
    
    <?php if ($add_delete_permissions['export_permission'] == '1'): ?>  
        <li class="m-portlet__nav-item button_row m--align-right m--pull-left m--padding-right-0">    
            <div class="dropdown m--pull-right">       
                <?php
                $extra = 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"';
                echo render_button ('export', 'export', 'btn btn-accent dropdown-toggle', 'button' , dashboard_lang('_EXPORT'), 'download', $extra);
                $segment4 = $this->uri->segment("4");
                if(empty($segment4)){
                    $segment4 = 0;
                }
                // $xlsDownloadPath = $site_url."dbtables/".$class_name."/export/excel/0/";
                // $pdfDownloadPath = $site_url."dbtables/{$class_name}/export/pdf/{$segment4}/";
                $downloadPath = $site_url."dbtables/{$class_name}/export/";
                ?>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" x-placement="top-start" style="position: absolute; transform: translate3d(0px, -136px, 0px); top: 0px; left: 0px; will-change: transform;">         
                <a class="dropdown-item export-listing-data" data-type="excel" data-offset="0" data-url="<?php echo $downloadPath;?>" href="javascript:void(0)" id="export_xls"><i class="la la-download"></i> <?php echo dashboard_lang("_XLS");?></a>
                    <a class="dropdown-item export-listing-data" data-type="pdf" data-offset="<?php echo $segment4;?>" data-url="<?php echo $downloadPath;?>" href="javascript:void(0)" id="export_pdf"><i class="la la-download"></i> <?php echo dashboard_lang("_PDF");?></a>
                </div>

                <div class="row" hidden="hidden"> 
                    <form id="downloadForm" action="<?php echo $downloadPath;?>" method="POST" >
                        <input type="text" id="type" name="type" value=""/>
                        <input type="text" id="offset" name="offset" value=""/>
                        <input type="textarea" id="ids" name="ids" value=""/>
                    </form>
                </div>

            </div>
        </li>
    <?php endif; ?>
