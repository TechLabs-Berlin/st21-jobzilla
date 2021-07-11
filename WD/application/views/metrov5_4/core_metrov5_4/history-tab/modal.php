
<div class="modal-dialog modal-lg" >
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"> <?php echo dashboard_lang("_HISTORY"); ?></h4>
            <?php echo  render_button ('', '', 'close', 'button' , '', '', 'data-dismiss="modal" aria-hidden="true"'); ?>
        </div>
        <div class="modal-body" >

            <div class="history-section section-panel">
    
                <span class="field-value">
                    <div class="m-list-timeline">
                        <div class="m-list-timeline__items">
                            <?php 
                            if ( file_exists(FCPATH.'application/views/'.$this->template_name . '/'.$this->current_class_name.'/history-tab/main.php') ) {
            
                                $this->load->view($this->template_name.'/'.$this->current_class_name.'/history-tab/main', array( "ismodal" => @$ismodal, "histories" => $histories ));
                            } else {
                                
                                $this->load->view($this->template_name.'/core_'. $this->template_name . '/history-tab/main', array( "ismodal" => @$ismodal, "histories" => $histories ));
                            }
                            ?>
                        </div>
                    </div>
                    
                </span>
            </div>
        
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger m-btn m-btn--icon" data-dismiss="modal" ><?php echo dashboard_lang("_CANCEL"); ?></button>
        </div>
    </div>
</div>

