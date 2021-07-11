<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/listing.js"></script>
<script src="<?php echo CDN_URL;?>media_metrov5_4/portal_core/js/jquery.tokenize.js"></script>
<form name="hidden-form" class="hidden-form-listing"
	action="<?php echo $site_url.$listing_path; ?>" method="post">
	<input type="hidden" name="table_sort_field" value=""
		class="table_sort_field" /> <input type="hidden"
		name="table_sort_direction" value="" class="table_sort_direction">
</form>
