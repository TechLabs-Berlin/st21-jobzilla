<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="col-xl-5 m--align-right">
	<?php foreach($additional_buttons as $button ) { ?>
	<a href="javascript:;" class="btn btn-success m-btn m-btn--icon   <?php echo @$button->class; ?>" <?php echo isset($button->id)?'id="'.$button->id.'"':''; ?> >
		<span>
    		<i class="fa fa-save"></i>
    		<span><?php echo dashboard_lang('_'. strtoupper(@$button->name) ); ?></span>
    	</span>
	</a>
	<?php } ?>
	<a href="<?php  echo $site_url.'/'.$edit_path; ?>">		
        <?php
            echo  render_button ('', '', 'btn btn-success m-btn m-btn--icon   m--align-left margin-left', 'button' , dashboard_lang('_ADD'), 'plus');
        ?>
	</a>
	<?php echo render_button ('copy', 'copy', 'btn btn-success m-btn m-btn--icon   m--align-left margin-left', 'button' , dashboard_lang('_COPY'), 'copy');?>
	<?php echo render_button ('delete', 'delete', 'btn btn-danger m-btn m-btn--icon   m--align-left margin-left', 'button' ,  dashboard_lang('_DELETE'), 'remove');?>
</div>
