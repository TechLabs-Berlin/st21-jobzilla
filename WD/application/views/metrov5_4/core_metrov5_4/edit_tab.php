<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
$first_time = true;
?>
<link href="<?php echo CDN_URL; ?>media_metrov5_4\custom\css\questionaires.css" rel="stylesheet" type="text/css"/>
<div class="row">
    <div class="col-lg-12">
        <div class="m-portlet all-tabs">
            <div class="m-portlet__body">
                <ul class="nav nav-tabs  m-tabs-line m-tabs-line--success" role="tablist">        	
                <?php
                $output_tab_div = "";
            
                $counter = 1;
            
                foreach ($tab_data as $key => $value) {
                
                $class = ($first_time) ? ' active ' : '';
                $extraClass = empty($form_orientation) || $form_orientation != "horizontal"? "":" tab-content-shift-right";
                $key_id = str_replace(" ", "-", trim($key));
                $output_tab_div .= '<div class="tab-pane ' . $class . $extraClass . '" id="dashboard_' . $key_id . '">';
                
                ?>
                    <li class=" nav-item m-tabs__item portal_tab" id="tab_<?php echo $counter; ?>" data-id="<?php echo $counter; ?>">
                        <a class="nav-link m-tabs__link <?php echo $class;?>" href="#dashboard_<?php echo $key_id; ?>" data-toggle="tab"><?php echo dashboard_lang($key); ?></a>		    		
                    </li>
                <?php
                $first_time = false;
                if ( $key === "_HISTORY" ) {

                    $viewData['limit'] =   $this->config->item('#CORE_HISTORY_LOG_SHOW_LIMIT');
                    $viewData['id'] = $data_edit['id'];
                    $viewData['histories']= getAllHistory($this->current_class_name, $viewData['id'], $viewData['limit']);
                    $historyView = "";
                    if ( file_exists(FCPATH.'application/views/'.$template . '/'.$class_name.'/history-tab/main') ) {
                        
                        $historyView = $this->load->view($template.'/'.$class_name.'/history-tab/main', $viewData, true);
                    } else {
                        
                        $historyView = $this->load->view($template.'/core_'. $template . '/history-tab/main', $viewData, true);
                    }

                    $output_tab_div .= '<div class="col-lg-12">' . $historyView . '</div>';
                } else {

                    $output_tab_div .= '<div class="col-lg-6">';

                    foreach ($value as $object) {
                        
                        $nameField = (string) $object["name"];
                        $feildType = (string) $object["type"];
                        
                        if ($feildType != 'hidden') {
                            $output_tab_div .= '<div class="form-group ' . 'field_' . $nameField . '" id ="' . 'field_id_' . $nameField . '">';
                        }
                        
                        $output_tab_div .= B_form_helper::render_field($object, @$data_edit[(string) $object["name"]], $table_permissions_field, '');
                        
                        if ($feildType != 'hidden') {
                            $output_tab_div .= "</div>";
                        }
                    }
                    $output_tab_div .= "</div>";
                }
                
                $output_tab_div .= "</div>";
                
                $counter ++;
                }
            
                ?>                        
                </ul>
                <div class="tab-content">
                    <?php  echo $output_tab_div; ?>
                </div>
            </div>
            
                
            
        </div>
    </div>
</div>
<!-- /.panel-body -->
