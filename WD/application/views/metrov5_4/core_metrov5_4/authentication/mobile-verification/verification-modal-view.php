<div class="modal fade show" role="dialog" id="code_verification_modal">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><?php echo dashboard_lang("_MOBILE_NUMBER_VERIFICATION"); ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<p><?php echo dashboard_lang("_ENTER_THE_CODE"); ?></p>
      	<p id="modal_alert_msg"></p>
      	<p><?php echo dashboard_lang("_VERIFICATION_CODE_SENT_TO"); ?> <?php if( isset( $hint_mbl_number) ){ echo $hint_mbl_number; }?></p>
        <div class="row">
        	<div class="col-lg-12">
        		<div class="form-group input-group field_code" id="field_id_code">
            		<label class="pop_over control-label " name="Code" data-toggle="tooltip" data-placement="top" title=""><?php echo dashboard_lang('_CODE'); ?></label>
            		<input type="text" name="code" id="code" placeholder="" class="form-control m-input">
            	</div>
        	</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success m-btn  " id="submit_code"> <?php echo dashboard_lang("_SUBMIT"); ?> </button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->