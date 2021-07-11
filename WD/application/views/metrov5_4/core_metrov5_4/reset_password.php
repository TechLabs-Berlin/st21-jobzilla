<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<?php $this->load->view('core_metrov5_4/guest/header');?>
<div class="content">
    <!-- BEGIN REGISTRATION FORM -->
    <form class="signup-form" action="" method="post" role="form">
    <?php if(isset($_GET['slug'])):?>
        <input type="hidden" name="user_slug" value="<?php echo $_GET['slug']; ?>" />
    <?php endif; ?>
        <h3 class="form-title"><?php echo dashboard_lang('_RESET_PASSWORD');?></h3>

        <div class="alert <?php if ($alert_success): echo 'alert-success'; else: echo 'alert-danger'; endif; ?> <?php if (!isset($message)): echo 'display-hide';  endif; ?>">
            <button class="close" data-close="alert"></button>
            <?php echo $message; ?>

        </div>

        <?php if (!$form_hide): ?>

        <p><?php echo dashboard_lang('_ENTER_YOUR_NEW_PASSWORD');?></p>             
                
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" for="password"><?php echo dashboard_lang('_NEW_PASSWORD');?></label>
            <div class="input-icon">
                <i class="fa fa-lock"></i>
                <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="new_password" placeholder="<?php echo dashboard_lang('_NEW_PASSWORD');?>" name="new_password" value="<?php echo set_value('password'); ?>" /> 
            </div>
            <div class="col-md-9 pull-right text-danger">
                <?php echo form_error('password'); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" for="password"><?php echo dashboard_lang('_RE_PASSWORD');?></label>
            <div class="input-icon">
                <i class="fa fa-lock"></i>
                <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="re_password" placeholder="<?php echo dashboard_lang('_RE_PASSWORD');?>" name="re_password" value="<?php echo set_value('password'); ?>" /> 
            </div>
            <div class="col-md-9 pull-right text-danger">
                <?php echo form_error('password'); ?>
            </div>
        </div> 

        <?php endif; ?>           
        
        <div class="form-actions">

            <?php if (!$btn_hide): ?>    
            <button type="submit" name="reset_submit" id="reset_submit" value="1" class="btn btn-lg btn-success btn-block">
                <?php echo dashboard_lang('_RESET_PASS_SUBMIT'); ?>
            </button>
            <?php endif; ?>
             <br>
            <?php if (isset($message) && $btn_hide): ?> 
            <a href="<?php echo base_url() . "dashboard/index"; ?>"  id="register-back-btn" type="button" class="btn btn-lg btn-success btn-block"> <?php echo dashboard_lang('_GO_LOGIN_PAGE');?> </a>
            <?php endif; ?>
        </div>
    </form>
    <!-- END LOGIN FORM -->


</div>


<?php $this->load->view('core_metrov5_4/guest/footer');?>
