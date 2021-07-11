<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class m_pdf {

	function m_pdf()
	{
		$CI = & get_instance();
		log_message('Debug', 'mPDF class is loaded.');
	}

	function load($param=NULL)
	{
		include_once APPPATH.'/third_party/mpdf/mpdf.php';

		if ($params == NULL)
		{
			$param = '"en-GB-x","A4","","",20,5,15,15,6,3';
		}

		return new mPDF($param);
	}

	function load_portait($param=NULL)
	{
	    include_once APPPATH.'/third_party/mpdf/mpdf.php';
	
	    if ($params == NULL)
	    {
	        $param = '"en-GB-x","A4-L","" ,"",20,20,15,15,6,3,""';
	    }
	    //new mPDF($mode, $format, $font_size, $font, $margin_left, $margin_right, $margin_top, $margin_bottom, $margin_header, $margin_footer, $orientation);
	    return new mPDF($param);
	}
}