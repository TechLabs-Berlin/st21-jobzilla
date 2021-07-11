<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$base_url = base_url();
$site_url = rtrim(site_url(), '/').'/';
$controller_sub_folder = $this->config->item('controller_sub_folder');
$dashboard_helper = Dashboard_main_helper::get_instance();
$dashboard_helper->set('edit_path', $edit_path);
$dashboard_helper->set('id', @$id);
$super_admin = $dashboard_helper->get('super_user');
$template= $this->config->item('template_name');
if(file_exists(FCPATH.'application/views/'.$template . '/'.$class_name.'/edit/header_script.php')){
    require_once(FCPATH.'application/views/'.$template . '/'.$class_name.'/edit/header_script.php');
}else{
    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/edit/header_script.php');
}
$form_orientation_class = "";
$form_orientation = @$data_load['form_orientation'];
if(isset($form_orientation) AND $form_orientation == "horizontal"){
    $form_orientation_class = "form-horizontal";
}
echo form_open('', array('method'=>'post', 'enctype'=>'multipart/form-data' , 'role'=> 'form', 'id' => 'main_form','class'=>$form_orientation_class,'autocomplete'=>'off'));
if(file_exists(FCPATH.'application/views/'.$template . '/'.$class_name.'/edit/main.php')){
    require_once(FCPATH.'application/views/'.$template . '/'.$class_name.'/edit/main.php');
}else{
    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/edit/main.php');
}
echo form_close(); 
if(file_exists(FCPATH.'application/views/'.$template . '/'.$class_name.'/edit/modal.php')){
    require_once(FCPATH.'application/views/'.$template . '/'.$class_name.'/edit/modal.php' );
}else{
    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/edit/modal.php' );
}
?>
<input type="hidden" id="id" value="<?php echo @$id ;?>">
<input type="hidden" id="table_name" value="<?php echo @$class_name ;?>">
<input type="hidden" id="ref_key" value="">
<input type="hidden" id="ref_value" value="">
<?php 
if(file_exists(FCPATH.'application/views/'.$template . '/'.$class_name.'/edit/footer_script.php')){
    
    require_once(FCPATH.'application/views/'.$template . '/'.$class_name.'/edit/footer_script.php' );
    
}else{
    
    require_once(FCPATH.'application/views/metrov5_4/core_metrov5_4/edit/footer_script.php' );
    
}
?>
