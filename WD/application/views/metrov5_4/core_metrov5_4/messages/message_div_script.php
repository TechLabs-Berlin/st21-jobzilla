<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
$('.portal_tab a').on('click',function(){
 
	var href = $(this).attr('href');
	if ( href == '#dashboard__DISCUSSIONS' && $('.tab-content').find(".tab-pane").children("div").hasClass("col-lg-6") ) 
		$('.tab-content').find(".tab-pane").children("div").removeClass('col-lg-6').addClass('col-lg-12');
	else if ( href !== '#dashboard__HISTORY' )
		$('.tab-content').find(".tab-pane").children("div").removeClass('col-lg-12').addClass('col-lg-6');
	
});
