                <div class="file-upload-area">
                    <div class="row progress-bar-block" style="display:none;">
                	  <div class="col-md-12">
                		 <div class="myProgress">
                			<div class="progress-bar"></div>
                		 </div>
                	  </div>
            	    </div>
                    <i class="fa fa-paperclip"></i>
                    <?php echo dashboard_lang('_DRAG_FILE_HERE');?> <?php echo dashboard_lang('_OR');?> <a href="javascript:void(0);" class="upload-file"> <?php echo dashboard_lang('_CLICK_HERE_TO_UPLOAD_FILE'); ?></a><br/>
                    <input type="file" style="display:none;" data-entity-id="<?php echo $entity_id;?>" class="upload-input-file" name="userfile" class="message-deatils-upload-btn">
                    <div class="all-msg-files"></div> 
                </div>