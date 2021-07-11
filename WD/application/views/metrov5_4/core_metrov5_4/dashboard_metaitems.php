<?php if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<?php

$CI = &get_instance();
$currentAccountId = $CI->session->userdata("current_account_id");
if ( isset($currentAccountId) && strlen($currentAccountId) > 0 ) {

    $tenant = new BAccount ( $currentAccountId );
    $faviconUrl = $tenant->favicon;
}else {

    $currentUser = BUserHelper::get_instance();
    $faviconUrl = $currentUser->tenant->favicon ;
}

if( strlen( $faviconUrl ) > 0 ){

    if ( strpos( $faviconUrl, "amazonaws" ) !== false ) {
        $site_favicon = $faviconUrl ;
    }else {
        $site_favicon = CDN_URL.'uploads/tenant_favicon/'.$faviconUrl ;
    }
}
?>

<link href="<?php echo $site_favicon ; ?>" rel="shortcut icon" type="image/vnd.microsoft.icon" />
<title><?php echo $site_title; ?></title>


