<?php
$main_file_details = pathinfo( $file_location );
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

							    <div class="inbox-attached" style="margin-bottom:30px; margin-top:10px;">
                                     <div class='flie-upload-area'>
                                         <?php  if ( $is_image ) { ?>
                                         <a href="<?php echo $file_location;?>" download>
                                            <img src="<?php echo $file_location;?>" width="100" alt=""/>
                                         </a> 
                                         <?php }else { ?>
                                         <a target="_blank" href="<?php echo $file_location;?>" download>
                                             <i class='<?php echo $fa_icon; ?> file-icon'></i>
                                             <span class='file-name'><?php echo $mainFileName; ?></span>
                                         </a>
                                         <?php } ?>
                                         
                                         <a style="display:block;"><img alt="" /></a>
                                      </div>      
								</div>