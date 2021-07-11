<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<div class="modal fade"  id="add-relational-table-record" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-md" >
        <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="modal_title"></h4>
            <?php
                echo  render_button ('', '', 'close', 'button' , '', '', 'data-dismiss="modal" aria-hidden="true"');
                ?>
        
            </div>
            <div class="modal-body" >
                <iframe id="iframe" src="" width="100%" height="750px"></iframe>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-hidden="true"></div>