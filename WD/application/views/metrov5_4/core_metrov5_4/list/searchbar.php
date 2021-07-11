<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php if (strlen($search) > 0 && !is_int( $search )) {  
    $formSubmissionSata = array('method'=>'post', 'class'=>'m-search_text_form', 'enctype'=>'multipart/form-data' , 'role'=> 'form', 'name'=>'search_text_form', 'style' => 'position: relative;');
    $data['modal_style'] = "style='float: right; position: relative;'";
} else {
    $formSubmissionSata = array('method'=>'post', 'class'=>'m-search_text_form', 'enctype'=>'multipart/form-data' , 'role'=> 'form', 'name'=>'search_text_form');
    $data['modal_style'] = "style='float: right;'";
}?>
<div class="form-group m-form__group" style="margin-right: 0;">
    
    <span class="m--align-left" style="float: right;">
        <div class="m-typeahead" >
            
            <?php echo form_open('', $formSubmissionSata); ?>
                
                <input style="" class="form-control m-input" type="search"
                       name="search" id="m_typeahead_2"
                       value="<?php echo htmlspecialchars(@$search , ENT_QUOTES); ?>"
                       placeholder="<?php echo dashboard_lang('_SEARCH_TEXT'); ?>"
                       autocomplete="off" />
                <!--strat search icon-->
                <span class="input-group-btn">
                	<button type="submit" class="btn btn-accent m-btn--icon   m--align-right"  style="height:35px;border-bottom-right-radius: 4px;border-top-right-radius: 4px;">
                        <i class="fa fa-search"  style="line-height:16px;"></i>
                    </button>
                    <button type="submit" name="reset" id="reset" <?php if (!strlen($search)) { ?>
                        style="display: none;" <?php } ?>
                            class="btn btn-accent m-btn--icon   m--align-right" value="1" style="margin-left: 5px;height:35px;line-height:16px;border-radius:4px;">
                        <?php echo dashboard_lang('_RESET'); ?>&nbsp;<i class="fa fa-repeat"></i>
                    </button>
                </span>
                <!--end search icon-->
            <?php echo form_close(); ?>
        </div>    
    </span>
    <?php
        
        $data['all_field'] = $all_field;
        $this->load->view( $template . '/core_' . $template . '/list_modal', $data);
    ?>
</div>
<script>
var is_searched = "<?php echo strlen($search);?>";
</script>
