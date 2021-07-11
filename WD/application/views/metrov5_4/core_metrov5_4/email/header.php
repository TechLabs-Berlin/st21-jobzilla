<?php if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' ); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	
</head>
<body bgcolor="#f8f8f8" style="margin:0;padding:0;">
<div class="bg-area" style="background: #f2f3f8;padding-top:135px;padding-bottom:150px;">
<table width="580" class="bg-inner" style="margin:0 auto 0;background:#fff; -webkit-box-shadow:0 1px 15px 1px rgba(113,106,202,.08); box-shadow:0 1px 15px 1px rgba(113,106,202,.08);">
<tr>
<td style="margin:0;padding: 20px 20px 35px;">

<?php 

$logo = $this->config->item("#APPLICATION_LOGO");

$user_instance = BUserHelper::get_instance();

if( !empty($user_instance->tenant->email_logo) ){
    $logo_path = 'uploads/tenant_email_logo/' . $user_instance->tenant->email_logo;
    if (file_exists($logo_path) ) {
        
        $logo = $logo_path;
    }
    
}

if( !empty($user_instance->tenant->name) ){ 
    $app_name = $user_instance->tenant->name;
}else {
    $app_name = $this->config->item("#APPLICATION_NAME");
}

?>

<table class="email-reset-template m-portlet" width="580" border="0" cellpadding="0" cellspacing="0" style="margin:0 auto 0;font-family:Arial;font-size:14px;">
<tbody>
    <tr>
        <td class="dashboard-logo" align="center" valign="middle" height="150" colspan="2"><img width="200" height="" src="<?php echo CDN_URL.$logo;?>" alt="logo"></td>
        <!-- <td class="application-name" valign="top" align="right" style="color: #716aca;font-family:Arial;font-size: 26px;font-style: normal; font-weight: 400;padding-top: 22px;"><?php echo $app_name; ?></td> -->
 </tr>
