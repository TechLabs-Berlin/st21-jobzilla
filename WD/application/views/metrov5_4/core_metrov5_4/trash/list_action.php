<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<li class="m-portlet__nav-item button_row pull-left m--align-left">
	
</li>
<li class="m-portlet__nav-item button_row pull-right m--align-right">	
    <?php
    if ($add_delete_permissions['delete_permission'] == '1'):
        echo render_button ('trash-permanently', 'trash-permanently', 'btn btn-danger pull-left margin-left m-btn m-btn--icon   m--margin-left-10', 'button' , dashboard_lang('_DELETE_PERMANENTLY'), 'remove');
    endif; 
    echo render_button ('un-trash', 'un-trash', 'btn btn-success  pull-left margin-left m-btn m-btn--icon   m--margin-left-10', 'button' , dashboard_lang('_UN_TRASH'), 'retweet ');
    ?>

	
	
</li>
