<?php
$main_file_details = pathinfo( $file_path );
$image_ext = array("jpg", "gif", "png", "jpeg", "JPG", "bitmap");
$xlsx_ext = array("xls", "csv", "xlsx");
$docs_ext = array("docs", "doc", "DOCX", "DOCS", "docx");
$txt_ext = array("txt", "text");
$pdf_ext = array( "pdf", "PDF" );

$is_image = false;

if ( in_array($main_file_details['extension'], $image_ext) ) {
    $fa_icon = "fa fa-file-image-o";
    $is_image = true;
}else if ( in_array($main_file_details['extension'], $xlsx_ext)) {
    $fa_icon = "fa fa-file-excel-o";
}else if (in_array($main_file_details['extension'], $txt_ext)) {
    $fa_icon = "fa fa-file-text";
}else if (in_array($main_file_details['extension'], $docs_ext)){
    $fa_icon = "fa fa-file-word-o";
}else if (in_array($main_file_details['extension'], $pdf_ext)){
    $fa_icon = "fa fa-file-pdf-o";
} else {
    $fa_icon = "fa fa-file";
}
 
$mainFileName = $main_file_details['basename'];
$fileNameLength = $this->config->item("#MAX_UPLOADED_FILE_NAME_LENGTH");

if ( strlen($mainFileName) > $fileNameLength ) {
    
    $mainFileName = substr($mainFileName, 0, $fileNameLength)." ...";
}

?>

<div class="upload-file-entry-messages" style="margin-top:10px;">
     <?php // If Image then show img tag, then otherwise show file icon ?>
     
     <?php if ( $is_image ) { ?>
        <img src="<?php echo base_url();?><?php echo $file_path;?>" width="80" alt="<?php echo dashboard_lang("_MSG_IMAGE");?>">
     <?php }else{ ?>
       <i class="<?php echo $fa_icon;?> fa-2x"></i> 
       <span class="upload_file_name"><?php echo $mainFileName; ?></span> 
     <?php } ?>
     
     <span class="delete_file">
          &nbsp;<a href="javascript:void(0);" data-file-path="<?php echo $file_path;?>" class="remove_msg_files"><i class="fa fa-remove"></i></a>
     </span>
     <input type="hidden" class="all_uploaded_files" name="all_files" value="<?php echo $file_path;?>">
</div>