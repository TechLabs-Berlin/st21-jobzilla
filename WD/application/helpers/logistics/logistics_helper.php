<?php 

function renderSingleLabelWithBreak ( $field ) {

    if ( strlen($field) == '0' ) {

        return "";
    }else {
        return "<span>".$field."</span> <br/>";
    }
}

function renderLabelWithBreak ( $field, $label = '' ) {

    if ( strlen($field) == '0' ) {

        return "";
    }else {
        return "<span>".$label.': '.$field."</span> <br/>";
    }
}

function renderSingleLabelWithOutBreak ( $field ) {

    if ( strlen($field) == '0' ) {

        return "";
    }else {
        return "<span>".$field."</span> ";
    }
}

function renderSingleLabelWithDoubleBreak ( $field ) {

    if ( strlen($field) == '0' ) {

        return "";
    }else {
        return "<span>".$field."</span> <br/> <br/>";
    }
}


function renderBranchName ( $field ) {

    if ( strlen($field) == '0' ) {

        return "";
    }else {
        return "<span class='doc-branch-name m--font-bolder'>".$field."</span> <br/> <br/>";
    }
}


function renderDoubleFieldWithBreak ( $field1, $field2 = '' ) {

    if ( strlen($field1) > 0 || strlen($field2) > 0 ) {

        return '<span>'.$field1.' '. $field2.' </span> <br/>';
    }else {

        return "";
    }
}

function renderBranchLogo ( $logo = '') {

    $CI = & get_instance();
    $settingsPrefix = $CI->config->item("settingsPrefix");
    $logoWidth = $CI->config->item($settingsPrefix."_LOGO_WIDTH");

    $user_instance = BUserHelper::get_instance();
    if( strlen( $user_instance->tenant->logo ) > 0 ):
        $site_logo = $user_instance->tenant->logo;
    endif;
    

    if ( strlen($logo) == '0' && strpos($site_logo, "amazonaws") !== false) {

        return "<img class='' width='".$logoWidth."' src='".$site_logo."' alt=''/>";
    }else if ( strlen($logo) == '0' && strpos($site_logo, "amazonaws") === false && strlen($site_logo) > 0 && strpos($site_logo, "tenant_logo") === false) {

        $site_logo = CDN_URL."uploads/tenant_logo/".$site_logo;
        
        return "<img class='' width='".$logoWidth."' src='".$site_logo."' alt=''/>";
    }else {
        return "<img class='' width='".$logoWidth."' src='".$logo."' alt=''/>";
    }
}


function timeFormat($time = 0, $type = 0, $local="en"){

    if($local == "nl"){
        setlocale(LC_TIME , 'nl_NL');
    }
    if(strlen($time) < 5){
        return null;
    }

    if($type == 1)
        return strftime("%d %b %Y", $time);
    else if($type == 2)
        return strftime("%d %b %Y", $time);

    return strftime("%d %b %Y %H:%M  ", $time);
}


function getTableColumnInfo($tablename,$column,$field,$id)
{
    $ci = & get_instance();
    $ci->db->select($column);
    $ci->db->where($field,$id);
    $result = $ci->db->get_where($tablename)->result_array();

    if(isset($result[0]))

    return  $result[0][$column];
}

function renderMoneyFormatForFields ( $currency, $value, $settings = '', $defaultDecimals = '' ) {

    $CI = & get_instance();

    if ( strlen($value) == '0' ) {

        return "";
    }

    if ( strlen($settings) > 0 && strlen(trim($defaultDecimals)) == 0 ) {
        $fractionDigits = intval ( $CI->config->item($settings) );
    }else if(strlen(trim($defaultDecimals)) > 0) {
        $fractionDigits = (int) trim($defaultDecimals);
    }else{
        $fractionDigits = 2;
    }

    $value = number_format($value,  $fractionDigits, ".", false);

    if ($currency == '&euro;') {

        return str_replace (".", ",", $value);
    } else {
        return $value;
    }
}

function renderMoneyFormattedAmount ( $currency, $value, $settings = '', $allowCurrency = true ) {

    $CI = & get_instance();
    
    if ( strlen($settings) > 0 ) {
        $fractionDigits = intval ( $CI->config->item($settings) );
    }else {
        $fractionDigits = 2;
    }

    if ($currency == '&euro;') {
        $fmt = new NumberFormatter('de_DE', NumberFormatter::CURRENCY);
        $fmt->setTextAttribute(NumberFormatter::NEGATIVE_PREFIX, "-");
        $fmt->setTextAttribute(NumberFormatter::NEGATIVE_SUFFIX, "");
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, $fractionDigits);
        $formattedValue = $fmt->formatCurrency($value, "EUR");
    } else {
        $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        $fmt->setTextAttribute(NumberFormatter::NEGATIVE_PREFIX, "-");
        $fmt->setTextAttribute(NumberFormatter::NEGATIVE_SUFFIX, "");
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, $fractionDigits);
        $formattedValue = $fmt->formatCurrency($value, "USD");
    }

    $formattedValue = preg_replace('/[^0-9-,"."]/', '', $formattedValue);

    if ($allowCurrency) {

        return $currency." ".$formattedValue;
    }else {
        return $formattedValue;
    }
   
}


function renderLineHeader( $settings, $fieldLabel, $class, $ibullet = FALSE ) {

    $CI = &get_instance();

    $settingsValue = strtolower ( $CI->config->item($settings) );

    if ( $settingsValue == 'true' ) {

        $fieldSettings = str_replace ("APPLY", "MINWIDTH", $settings);

        $minWidthSettings = $CI->config->item( $fieldSettings );
    
        if ( strlen($minWidthSettings) > 1 ) {
    
            $minWidth = ' style="width: '.$minWidthSettings.' "; ';
        }else {
            $minWidth = "";
        }

        $html  = '<th class="'.$class.'" '.$minWidth.'>';
        $html .=    dashboard_lang(str_replace('_LINES_HEADING', '', $fieldLabel));

        if( $ibullet ){

            $html .= Logistics_form_helper::render_info_button(ltrim($fieldLabel, '_'), FALSE, FALSE, '', 'horizontal');
        }
            

        $html .=  "</th>";

        return $html;

    }else {

        return "";
    }
}

function renderCustomLineHeader( $settings, $fieldLabel, $class, $ibullet = FALSE ) {

    $html  = '<th class="'.$class.'">';
    $html .=    dashboard_lang($fieldLabel);

    if( $ibullet ){

        $html .= Logistics_form_helper::render_info_button(ltrim($fieldLabel, '_'), FALSE, FALSE, '', 'horizontal');
    }
        
    $html .=  "</th>";

    return $html;
}

function renderLineHeaderShowHideIcon ( $settings, $fieldLabel, $class, $value, $field ) {

    $CI = &get_instance();

    $settingsValue = strtolower ( $CI->config->item($settings) );

    if ( $settingsValue == 'true' ) {

        if ( $value == '1' ) {
            $iconClass = "fa fa-eye";
        }else {
            $iconClass = "fa fa-eye-slash";
        }

        $html  = '<th class="'.$class.'">';
        $html .=   '<a data-field="'.$field.'" href="javascript:void(0);" class="show-hide-fields" data-value="'.$value.'" ><i class="'.$iconClass.'" aria-hidden="true"></i></a>';
        $html .=  "</th>";

        return $html;

    }else {

        return "";
    }
}

function renderCustomLineHeaderShowHideIcon ( $settings, $fieldLabel, $class, $value, $field ) {

    if ( $value == '1' ) {
        $iconClass = "fa fa-eye";
    }else {
        $iconClass = "fa fa-eye-slash";
    }

    $html  = '<th class="'.$class.'">';
    $html .=   '<a data-field="'.$field.'" href="javascript:void(0);" class="show-hide-fields" data-value="'.$value.'" ><i class="'.$iconClass.'" aria-hidden="true"></i></a>';
    $html .=  "</th>";

    return $html;
}

function rendeLineHeaderValue ( $settings, $lineXmlData, $value, $line_table_permissions_field, $class ) {

    $CI = &get_instance();

    $settingsValue = strtolower ( $CI->config->item($settings) );

    if ( $settingsValue == 'true' ) {

        $html  = '<td class="'.$class.'">';
        $html .=    '<div class="form-group">';
        $html .=        Logistics_form_helper::render_field( $lineXmlData, $value, $line_table_permissions_field);
        $html .=    '</div>';
        $html .=  "</td>";

        return $html;

    }else {

        return "";
    }

}

function rendeUomLineHeaderValue ( $settings, $lineXmlData, $value, $line_table_permissions_field, $class, $itemsId ) {

    $CI = &get_instance();

    $settingsValue = strtolower ( $CI->config->item($settings) );
    $lineXmlData["items_id"] = $itemsId;
    $lineXmlData["type"] = "uom_lookup";

    if ( $settingsValue == 'true' ) {

        $html  = '<td class="'.$class.'">';
        $html .=    '<div class="form-group">';
        $html .=        Logistics_form_helper::render_field( $lineXmlData, $value, $line_table_permissions_field);
        $html .=    '</div>';
        $html .=  "</td>";

        return $html;

    }else {

        return "";
    }

}


function formatStatusName ( $statusName ) {

    return str_replace(" ", "-", strtolower( $statusName)) ;
}


function getStatusListsByName ( $tableName, $fieldName) {

    $CI = &get_instance();
    $CI->load->model("logistics/Quotations_Model");

    $result = $CI->Quotations_Model->getStatusListsByName ($tableName, $fieldName);

    return $result;
}

function getStatusListsByStatusName ( $tableName, $fieldName ) {

    $CI = &get_instance();
    $CI->load->model("portal/Customers_Model");

    $result = $CI->Customers_Model->getStatusListsByName ($tableName, $fieldName);

    return $result;
}

function formatKey( $fieldName ) {

    $CI = &get_instance();

    $langKeyPrefix = $CI->config->item("langKeyPrefix");
    $langKey = $langKeyPrefix.strtoupper("_".$fieldName);

    return $langKey;
}

function formatMessages ( $message, $xmlData) {

    $CI = &get_instance();

    $langKeyPrefix = $CI->config->item("langKeyPrefix");
    $tableName = $CI->config->item("tableName");
    $copiedFrom =  $CI->config->item("copied_from");
    $copiedFrom =  substr ( $copiedFrom,  0, -1 );

    if ( strlen($copiedFrom) > 0 ) {

        $message = str_replace ("_".strtoupper($copiedFrom), dashboard_lang("_".strtoupper($copiedFrom)), $message);
    }
   
    $message = str_replace ($langKeyPrefix."_CREATED_FROM", dashboard_lang($langKeyPrefix."_CREATED_FROM"), $message);
    $message = str_replace ($langKeyPrefix."_CHECKED", dashboard_lang($langKeyPrefix."_CHECKED"), $message);
    $message = str_replace ($langKeyPrefix."_UNCHECKED", dashboard_lang($langKeyPrefix."_UNCHECKED"), $message);
    $message = str_replace ("_CHANGED_TO", dashboard_lang("_CHANGED_TO"), $message);
    $message = str_replace ($langKeyPrefix."_STATUS_SHOW", dashboard_lang($langKeyPrefix."_STATUS_SHOW"), $message);
    $message = str_replace ($langKeyPrefix."_INSERTED", dashboard_lang($langKeyPrefix."_INSERTED"), $message);
    $message = str_replace ($langKeyPrefix."_ON_QUOTATION_LINE_NO", dashboard_lang($langKeyPrefix."_ON_QUOTATION_LINE_NO"), $message);
    $message = str_replace ($langKeyPrefix."_QUOTATION_LINE_NO", dashboard_lang($langKeyPrefix."_QUOTATION_LINE_NO"), $message);
    $message = str_replace ($langKeyPrefix."_LINE_DELETED", dashboard_lang($langKeyPrefix."_LINE_DELETED"), $message);
    $message = str_replace ($langKeyPrefix."_YES", dashboard_lang($langKeyPrefix."_YES"), $message);
    $message = str_replace ($langKeyPrefix."_ON_SALES_ORDER_LINE_NO", dashboard_lang($langKeyPrefix."_ON_SALES_ORDER_LINE_NO"), $message);
    $message = str_replace ($langKeyPrefix."_ON_LINE_NO", dashboard_lang($langKeyPrefix."_ON_LINE_NO"), $message);
    $message = str_replace ($langKeyPrefix."_LINE_NO", dashboard_lang($langKeyPrefix."_LINE_NO"), $message);
    $message = str_replace ($langKeyPrefix."_DISCOUNT_AMOUNT", dashboard_lang($langKeyPrefix."_DISCOUNT_AMOUNT"), $message);
    $message = str_replace ($langKeyPrefix."_DISCOUNT_PERCENTAGE", dashboard_lang($langKeyPrefix."_DISCOUNT_PERCENTAGE"), $message);

    foreach ($xmlData as $xmlData) {
        
        $var =  $langKeyPrefix."_".strtoupper( $xmlData["name"]);
        $message = str_replace ( $var, dashboard_lang($var), $message);
       
    }

    $message = str_replace ($langKeyPrefix."_NO", dashboard_lang($langKeyPrefix."_NO"), $message);
    

    return $message;

}

function renderQuotationsLineColumn ( $applySettings, $showFieldValue, $class, $label ) {

    $CI = &get_instance();
    
    $value =  strtolower ( $CI->config->item($applySettings) );
    if ( $value == 'true' ) {

        if ( $showFieldValue == '1' ) {

            $html  =  '<th class="'.$class.'">';
            $html .=      dashboard_lang($label);
            $html .=  "</th>";
    
            return $html;

        }else {

            return "";
        }

    }else {
        return "";
    }
}


function renderCustomQuotationsLineColumn ( $applySettings, $showFieldValue, $class, $label ) {

    $html  =  '<th class="'.$class.'">';
    $html .=      dashboard_lang($label);
    $html .=  "</th>";

    return $html;
}

function renderQuotationsLinRow ( $applySettings, $showFieldValue, $class, $fieldValue ) {

    $CI = &get_instance();
    
    $value =  strtolower ( $CI->config->item($applySettings) );
    if ( $value == 'true' ) {

        if ( $showFieldValue == '1' ) {

            $html  =  '<td class="'.$class.'">';
            $html .=      $fieldValue;
            $html .=  "</td>";
    
            return $html;

        }else {

            return "";
        }

    }else {
        return "";
    }
}

function renderQuotationsLineColumnForPdf ( $applySettings, $showFieldValue, $class, $label, $align = '', $columnWidth = '' ) {

    $CI = &get_instance();
    
    $value =  strtolower ( $CI->config->item($applySettings) );
    if ( $value == 'true' ) {

        if ( $showFieldValue == '1' ) {

            $html  =  '<th width="'.$columnWidth.'" align="'.$align.'" class="'.$class.'" style="border-bottom: #ccc 2px solid;">';
            $html .=      dashboard_lang($label);
            $html .=  "</th>";

            return $html;

        }else {

            return "";
        }

    }else {
        return "";
    }
}

function renderCustomPdfColumn ( $applySettings, $showFieldValue, $class, $label, $align = '' , $columnWidth = '') {

    $html  =  '<th width="'.$columnWidth.'" align="'.$align.'" class="'.$class.'" style="border-bottom: #ccc 2px solid;">';
    $html .=      dashboard_lang($label);
    $html .=  "</th>";

    return $html;

}

function renderQuotationsLinRowForPdf ( $applySettings, $showFieldValue, $class, $fieldValue, $align = 'left' ) {

    $CI = &get_instance();
    
    $value =  strtolower ( $CI->config->item($applySettings) );
    if ( $value == 'true' ) {

        if ( $showFieldValue == '1' ) {

            $html  =  '<td align="'.$align.'" valign="top" class="'.$class.'" style="border-bottom: #ccc 1px solid;">';
            $html .=      $fieldValue;
            $html .=  "</td>";

            return $html;

        }else {

            return "";
        }

    }else {
        return "";
    }
}


function getTableColumnInfoByOrder($tableName, $column, $field, $id, $orderBy='asc')
{
    $ci = & get_instance();
    $ci->db->select($column);
    $ci->db->where($field, $id);
    $ci->db->order_by('id', $orderBy);
    $ci->db->limit(1);

    $result = $ci->db->get_where($tableName)->row_array();

    if(isset($result))

    return  $result;
}


function replaceComma ( $amount ) {

    return str_replace (",", ".", $amount);
}


function renderContactName ( $topText, $quotations, $tableName = '' ) {

    $userTable = rtrim( $tableName, 's');
    $replaceText = "{".$userTable.":contact}";

    return str_replace($replaceText,  $quotations["firstname"]." ".$quotations["lastname"], $topText);
}

function renderBottomTextBranchName ( $bottomText, $quotations ) {

    return str_replace("{company.company_name}",  $quotations["branch"], $bottomText);
}

function loadSubView ( $viewFile, $className, $viewFolderPath = 'sub-views' ) {

    if(file_exists(FCPATH.'application/views/metrov5_4/logistics/'.$className.'/'.$viewFolderPath .'/'.$viewFile.'.php')){ 

        return 'metrov5_4/logistics/'.$className.'/'.$viewFolderPath.'/'.$viewFile;
    }else {
        return 'metrov5_4/logistics/core/'.$viewFolderPath.'/'.$viewFile;
    }

}

function getTableRowData($tableName, $field, $id)
{
    $result = array();

    $ci = & get_instance();
    $ci->db->select("*");
    $ci->db->where($field, $id);
    $result = $ci->db->get_where($tableName)->row_array();

    return  $result;
}

function getSelectWhere($tableName, $field, $id)
{
    $result = array();

    $ci = & get_instance();
    $ci->load->model('Dashboard_Logistics_Model');
    $result = $ci->Dashboard_Logistics_Model->getSelectWhere( $tableName, $field, $id );

    return  $result;
}

function logisticsAutoVersion($url) {
        
    $modfiedTime = filemtime($url);
    
    return CDN_URL.$url."?v=".$modfiedTime;
    
}

function renderFiles ( $file, $settingsPrefix ) {

    $CI = &get_instance();
    $attachmentLength = intval($CI->config->item($settingsPrefix."_ATTACHMENTS_FILE_NAME_LENGTH"));

	if( $attachmentLength == '0') {
        $attachmentLength = 20;
    }

    if ( strlen($file) > $attachmentLength ) {

        return substr( $file, 0, $attachmentLength)." ...";
    }else {
        return $file;
    }   

}

function copyToPermissions( $tableName, $fieldName ){

    $CI = &get_instance();

    $CI->load->model('dashboard_model');

    $tablePermissionsOfProjectFields = $CI->dashboard_model->get_table_permissions_field (  $tableName );
    $permission = B_form_helper::check_user_permission($tablePermissionsOfProjectFields, $fieldName);

    return $permission;
    

}

function prd ( $data ) {

    echo "<pre>";print_r($data);die;
}

function pre ( $data ) {

    echo "<pre>";print_r($data);
}

function locationEmptyFieldCheck( $additional_location, $label){

    if( $additional_location > 0 ){

        return  $label;
    }else{

        return "";
    }
}

function renderTradeBoardField (  $lineXmlData, $value, $line_table_permissions_field, $class="" ) {

    $CI = &get_instance();

    $html  = '<td class="'.$class.'">';
    $html .=    '<div class="form-group">';
    $html .=        Logistics_form_helper::render_field( $lineXmlData, $value, $line_table_permissions_field);
    $html .=    '</div>';
    $html .=  "</td>";
    return $html;
    
}

function getStockLocations ( $id ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Stocklocations_Model");
    
    return $CI->Stocklocations_Model->getStockLocations( $id ) ;
}

function getPrinter() {

    $CI = & get_instance();
    $CI->load->model("logistics/Items_Model");
    
    return $CI->Items_Model->getPrinter( ) ;
}

function tradeboardUserTable( $docType = 'quotation'){

    $linePrefix = array("quotation" => "#CORE_MODULE_LOGISTICS_QUOTATION_LINES", "purchase_order" => "#CORE_MODULE_LOGISTICS_PURCHASE_ORDER_LINES", "sales_order" => "#CORE_MODULE_LOGISTICS_SALES_ORDER_LINES");

    $settingsPrefix = array("quotation" => "#CORE_MODULE_LOGISTICS_QUOTATIONS", "purchase_order" => "#CORE_MODULE_LOGISTICS_PURCHASE_ORDERS", "sales_order" => "#CORE_MODULE_LOGISTICS_SALES_ORDERS");

    $langKeyPrefix = array("quotation" => "_CORE_MODULE_LOGISTICS_QUOTATION", "purchase_order" => "_CORE_MODULE_LOGISTICS_PURCHASE_ORDER", "sales_order" => "_CORE_MODULE_LOGISTICS_SALES_ORDER");

    $data = array();
 
    if( $docType == 'quotation' || $docType == 'sales_order'){

        $data['userTable'] = 'customers';
        $data['rawTblName'] =  $docType;
        $data['mainTable'] = $docType.'s';
        $data['linesSettingsKeyPrefix'] = $linePrefix[$docType];
        $data['settingsPrefix'] = $linePrefix[$docType];
        $data['langKeyPrefix'] = $langKeyPrefix[$docType];
    }

    if( $docType == 'purchase_order'){

        $data['userTable'] = 'suppliers';
        $data['rawTblName'] =  $docType;
        $data['linesSettingsKeyPrefix'] =  $linePrefix[$docType];
        $data['settingsPrefix'] =  $settingsPrefix[$docType];
        $data['langKeyPrefix'] =  $langKeyPrefix[$docType];

    }

    return $data;

}

function renderTradeboardColumn ( $value ) {

    $CI = &get_instance();

    $settingsValue = intval( $CI->config->item("#TECHNATION_TRADEBOARD_MAX_CHARACTER_SHOW"));

    if ( strlen($value) > $settingsValue ) {

        return substr ( $value, 0, $settingsValue );
    }else {
        return $value;
    }
}

function getAllLogsFromTable(){

    $CI = & get_instance();
    $CI->load->model("logistics/Supplier_Upload_Form_Model");

    return $CI->Supplier_Upload_Form_Model->getTableData();

}

function getLogisticsFromEmail () {

    $CI = & get_instance();

    $verifiedDomains = $CI->config->item("#CORE_LOGISTIC_MODULES_VERIFIED_SENDING_EMAIL_DOMAINS");
    $userEmail = @BUserHelper::get_instance()->user->email;
    $alterNateUserEmail = @BUserHelper::get_instance()->user->alternate_email;
    $userEmailDomain = explode( "@", $userEmail)[1];
    $alternateUserEmailDomain = explode( "@", $alterNateUserEmail)[1];

    if( strpos( $verifiedDomains, $alternateUserEmailDomain ) !== false){
    
        return $alterNateUserEmail;

    } else if( strpos( $verifiedDomains, $userEmailDomain ) !== false){
    
        return $userEmail;
    }else {
        return $CI->config->item("#CORE_EMAIL_SENDER");
    }
}

function renderDeliveryAddressLabel ( $details ) {

    $fields = [
        'additional_firstname',
        'additional_lastname',
        'additional_location_location',
        'additional_location_addres1',
        'additional_location_addres2',
        'additional_location_zipcode',
        'additional_location_city',
        'additional_location_country',
    ];

    $renderFieldLabel = false;

    foreach ( $fields as $eachField ) {

        if ( strlen( trim($details[$eachField]) ) > 0 ) {

            $renderFieldLabel = true;
        }
    }

    return $renderFieldLabel;
}

function encodeBase64 ( $number ) {

    return rand ( 1000, 9999 ).base64_encode( $number );
}

function decodeBase64 ( $str ) {

    return base64_decode( substr($str, 4) );
}

function timeFormatOnlineAcceptance($time = 0, $type = 0){

    if( $type == 1) {

        return date("M d, Y ", $time);
    }

    return date("d M Y H:i", $time);
}

function getStockTransactionLists ( $itemId = 0 ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Items_Model");

    return $CI->Items_Model->getStockTransactionLists( $itemId );
}


function renderOrderLineNo ( $details, $stockTypeId ) {

    $CI = &get_instance();

    if ( $stockTypeId == $CI->config->item("#CORE_MODULE_LOGISTICS_ITEM_RECIPT_ON_PO_TYPE_ID") ) {

        return $details["po_number_with_prefix"]." / ".$details["po_line_no"];
    }else if ( $stockTypeId == $CI->config->item("#CORE_MODULE_LOGISTICS_ITEM_DELIVER_ON_SO_TYPE_ID") ) {

        return $details["so_number_with_prefix"]." / ".$details["so_line_no"];
    }else {
        return "";
    }
    
}


function renderOrderStatus ( $details, $stockTypeId ) {

    $CI = &get_instance();

    if ( $stockTypeId == $CI->config->item("#CORE_MODULE_LOGISTICS_ITEM_RECIPT_ON_PO_TYPE_ID") ) {

        if ( strlen($details["po_status"]) > 0 ) {

            return dashboard_lang( $details["po_status"] );
        }else {
            return "";
        }
    }else if ( $stockTypeId == $CI->config->item("#CORE_MODULE_LOGISTICS_ITEM_DELIVER_ON_SO_TYPE_ID") ) {

        if ( strlen($details["so_status"]) > 0 ) {

            return dashboard_lang( $details["so_status"] );
        }else {
            return "";
        }
    }else {
        return "";
    }
}

function renderUser ( $details, $stockTypeId ) {

    $CI = &get_instance();

    if ( $stockTypeId == $CI->config->item("#CORE_MODULE_LOGISTICS_ITEM_RECIPT_ON_PO_TYPE_ID") ) {

        return $details["supplier"];
    }else if ( $stockTypeId == $CI->config->item("#CORE_MODULE_LOGISTICS_ITEM_DELIVER_ON_SO_TYPE_ID") ) {

        return $details["customer"];
    }else {
        return "";
    }
}


function render_docs_download_link( $file_name, $settingsKey ) {
    $ci = & get_instance();
    if ( strlen($file_name) == '0' ){

        return '';
    }

    $main_file_details = pathinfo( $file_name );
    $image_ext = array("jpg", "gif", "png", "jpeg", "JPG", "bitmap");
    $xlsx_ext = array("xls", "csv", "xlsx");
    $docs_ext = array("docs", "doc", "DOCX", "DOCS", "docx");
    $txt_ext = array("txt", "text");
    $pdf_ext = array( "pdf", "PDF" );

    if ( in_array(trim($main_file_details['extension']), $image_ext) ) {

        $fa_icon = "fa fa-file-image-o";
    }else if ( in_array(trim($main_file_details['extension']), $xlsx_ext)) {
        $fa_icon = "fa fa-file-excel-o";
    }else if (in_array(trim($main_file_details['extension']), $txt_ext)) {
        $fa_icon = "fa fa-file-text";
    }else if (in_array(trim($main_file_details['extension']), $docs_ext)){
        $fa_icon = "fa fa-file-word-o";
    }else if (in_array(trim($main_file_details['extension']), $pdf_ext)){
        $fa_icon = "fa fa-file-pdf-o";
    } else {
        $fa_icon = "fa fa-file";
    }
     
    $mainFileName = $main_file_details['basename'];
    $fileNameLength = $ci ->config->item( $settingsKey);       
   if ( strlen($mainFileName) > $fileNameLength ) {
       
       $mainFileName = substr($mainFileName, 0, $fileNameLength)." ...";
   }
    
   return "<a target='_blank' href='".$file_name."'> <i class='".$fa_icon." fa-2x' aria-hidden='true'></i> ".$mainFileName."</a>";

}    
     
function replaceBreakTag ( $content ) {

    $find = [
        "<br/>",
        "<br>",
        "</br>",
        "<br />"
    ];

    $replace = [
        "\n",
        "\n",
        "\n",
        "\n"
    ];

    return str_replace ( $find, $replace, $content );
}

function renderFieldValue ( $value, $field ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Receipts_Model");

    $lineId = $value;

    $numberFieldLists = [
        "qty_to_issue",
        "qty",
        "price",
        "amount",
        "vat_amount",
        "received",
        "still_to_receive",
        "subtotal",
        "vat_total",
        "total",
        "qty_at_stock"

    ];

    if ( $field == 'date' || $field == 'due_date' || $field == 'purchase_order_date' || $field == 'eta' || $field == 'purchase_order_lines_eta' || $field == 'stocktransaction_date') {

        if ( empty($value) || $value == '0' ) {
            return "<td data-field='".$field."' class='line-search'>&nbsp;</td>";
        }else {
            return "<td data-field='".$field."' class='line-search'>".date("d M Y", $value)."</td>";
        }
        
    }else if ( in_array( $field , $numberFieldLists ) ) {

        return "<td align='right' data-field='".$field."' class='issues-edit-line line-search'>".B_form_helper::customeMoneyFormate( $value, 2 )."</td>";
    }else if($field == 'status_purchase_order_lines_id'){

        $status = $CI->Receipts_Model->getStatusListById($value, 'status_purchase_order_lines');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
    
    }else if($field == 'status_purchase_orders_id'){

        $status = $CI->Receipts_Model->getStatusList($value, 'status_purchase_orders');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
    
    }else {
        return "<td data-field='".$field."' class='issues-edit-line line-search'>".$value."</td>";
    }
}

function renderIsseModalFieldValue ( $value, $field ) {

    $lineId = $value;
    if ( $field == 'qty_at_stock' ) {

        return "<td class='total-qty print-value' data-qty='".$value."'>".B_form_helper::customeMoneyFormate( $value, 2 )."</td>";
    }else {
        return "<td class='print-value'>".$value."</td>";
    }
}

function renderIsseModalEditFieldValue ( $value, $field, $totalQty, $qty ) {

    $lineId = $value;
    if ( $field == 'qty_at_stock' ) {

        $value = $totalQty + $qty ;
        
        return "<td class='total-qty' data-qty='".$value."'>".B_form_helper::customeMoneyFormate( $value, 2 )."</td>";
    }else {
        return "<td>".$value."</td>";
    }
}

function renderDeliveryStockFieldValue ( $value, $field ) {

    $lineId = $value;
    $CI = & get_instance();
    $CI->load->model("logistics/Receipts_Model");
    
    if ( $field == 'qty_at_stock' ) {

        return "<td class='total-qty' data-qty='".$value."'>".B_form_helper::customeMoneyFormate( $value, 2 )."</td>";
    }else if ( $field == 'batch_status' ) {

        $status = $CI->Receipts_Model->getStatusList($value, 'status_batches');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
    }else {
        return "<td>".$value."</td>";
    }
}

function renderPoLinesFieldValue ( $value, $field ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Receipts_Model");

    $lineId = $value;

    $numberFieldLists = [
        "qty_to_issue",
        "qty",
        "price",
        "amount",
        "vat_amount",
        "received",
        "still_to_receive",
        "subtotal",
        "vat_total",
        "total",
        "delivered",
        "still_to_deliver"
    ];

    if ( $field == 'purchase_order_date' || $field == 'sales_order_date' || $field == 'due_date' || $field == 'purchase_order_lines_eta' || $field == 'eta' || $field == 'date') {
        
        if(empty($value) || $value == '0' ){
            return "<td></td>";
        }else{
            return "<td>".date("d M Y", $value)."</td>";
        }
        
    }else if ( in_array ( $field, $numberFieldLists ) ) {

        return "<td align='right'>".B_form_helper::customeMoneyFormate( $value, 2 )."</td>";
    }else if($field == 'status_sales_orders_id'){

        $status = $CI->Receipts_Model->getStatusList($value, 'status_sales_orders');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
    }else if($field == 'status_purchase_order_lines_id'){

        $status = $CI->Receipts_Model->getStatusList($value, 'status_purchase_order_lines');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }

    }else if($field == 'status_purchase_orders_id'){

        $status = $CI->Receipts_Model->getStatusList($value, 'status_purchase_orders');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
        

    }else if($field == 'status_sales_order_lines_id'){

        $status = $CI->Receipts_Model->getStatusListById($value, 'status_sales_order_lines');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
        

    }else {
        return "<td>".$value."</td>";
    }
}

function logDateTime($time = 0){

    return date("d M Y", $time);
}


function getItemDetails( $id = 0 ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Items_Model");

    return $CI->Items_Model->getItemDetails( $id );
}

function renderUomDropdown( $uomLists,  $uomId, $receiptLineId ) {

    $selectField  = "<select data-id='".$receiptLineId."' name='uom[".$receiptLineId."]' class='form-control dashboard-dropdown receipt-line-uom'>";
    $selectField .= "<option value='0'>".dashboard_lang("_SELECT_UOM")."</option>";

    foreach ( $uomLists as $eachUom ) {

        if ( $eachUom["id"] == $uomId ) {

            $selected = "selected";
        }else {
            $selected = "";
        }

        $selectField .= "<option data-relation='".$eachUom["relation"]."' data-factor='".$eachUom["factor"]."' value='".$eachUom["id"]."' ".$selected.">".$eachUom["uom"]."</option>";
    }

    $selectField .= "</select>";

    return $selectField;
}


function renderStockLocationDropdown( $stockLocationLists,  $stockLocationId, $receiptLineId ) {

    $selectField  = "<select data-id='".$receiptLineId."' name='stock_locations[".$receiptLineId."]' class='form-control dashboard-dropdown receipt-line-stock-location'>";
    $selectField .= "<option value='0'>".dashboard_lang("_SELECT_STOCK_LOCATION")."</option>";

    foreach ( $stockLocationLists as $eachLocation ) {

        if ( $eachLocation["id"] == $stockLocationId ) {

            $selected = "selected";

            $showValue = [];

            if ( strlen($eachLocation["short_code_path"]) > 0 ) {
    
                $showValue[] = $eachLocation["short_code_path"];
            }
    
            if ( strlen($eachLocation["pathname"]) > 0 ) {
    
                $showValue[] = $eachLocation["pathname"];
            }

            $selectField .= "<option value='".$eachLocation["id"]."' ".$selected.">".implode(" - ", $showValue)."</option>";

        }else {
            $selected = "";
        }
       
    }

    $selectField .= "</select>";

    return $selectField;
}


function docNotesTimeFormate( $time ){
    
    return date("d M Y H:i", $time);
}

function getTransactionTypeId ( $type ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Issues_Model");

    return $CI->Issues_Model->getStockTransactionTypeId ( $type );
}


function formatTextForPrint ( $description ) {

    $allowLength = 21;
    $secondAllowLength = $allowLength * 2 +2;

    if ( strlen($description) <= $allowLength ) {

        return $description;
    }else {

        $text = "";

        $lineBreakAdded1 = false;
        $lineBreakAdded2 = false;

        for ( $count = 0; $count < strlen($description); $count++ ) {

            $truncateCount = $allowLength - 3;
            $eachChar = $description[$count];

            if ( $count > $truncateCount  && $lineBreakAdded1 == false && $count < $secondAllowLength) {

                if ( $eachChar == " " ) {

                    $text = $text."\\"."n";
                    $lineBreakAdded1 = true;
                }else {
                    $text = $text.$eachChar;
                }
            }else if ( $count > $secondAllowLength && $lineBreakAdded2 == false ) {

                if ( $eachChar == " " ) {

                    $text = $text."\\"."n";
                    $lineBreakAdded2 = true;
                }else {
                    $text = $text.$eachChar;
                }
            }else {
                $text = $text.$eachChar;
            }
        }

        return $text;
    }
}


function renderItemSalesOrderFieldValue ( $value, $field ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Receipts_Model");

    $lineId = $value;

    $numberFieldLists = [
        "qty_to_issue",
        "qty",
        "price",
        "amount",
        "vat_amount",
        "received",
        "still_to_receive",
        "subtotal",
        "vat_total",
        "total",
        "delivered",
        "still_to_deliver",

    ];

    $rightAlignFields = [
        "due_days"
    ];

    if ( $field == 'date' || $field == 'due_date' || $field == 'purchase_order_date' || $field == 'eta' || $field == 'purchase_order_lines_eta' || $field == 'sales_order_date' || $field == 'sales_order_lines_eta') {

        if ( empty($value) || $value == '0' ) {
            return "<td data-field='".$field."' class='line-search'>&nbsp;</td>";
        }else {
            return "<td data-field='".$field."' class='line-search'>".date("d M Y", $value)."</td>";
        }
        
    }else if ( in_array( $field , $numberFieldLists ) ) {

        return "<td align='right' data-field='".$field."' class='issues-edit-line line-search'>".B_form_helper::customeMoneyFormate( $value, 2 )."</td>";
    }else if ( in_array( $field , $rightAlignFields ) ) {

        return "<td align='right' data-field='".$field."' class='issues-edit-line line-search'>".$value."</td>";
    }else if($field == 'status_sales_orders_id'){

        $status = $CI->Receipts_Model->getStatusList($value, 'status_sales_orders');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
    }else if($field == 'status_purchase_orders_id'){

        $status = $CI->Receipts_Model->getStatusList($value, 'status_purchase_orders');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
    }else if($field == 'status_purchase_order_lines_id'){

        $status = $CI->Receipts_Model->getStatusList($value, 'status_purchase_order_lines');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
    }else if($field == 'status_sales_order_lines_id'){

        $status = $CI->Receipts_Model->getStatusList($value, 'status_sales_order_lines');
        
        if(!empty($status['status'])){

            return '<td><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
    }else if($field == 'item_type' && $value == 1){

        return "<td data-field='".$field."' >".dashboard_lang('_ITEM')."</td>";
    }else {
        return "<td data-field='".$field."' class='issues-edit-line line-search'>".$value."</td>";
    }
}

function renderIssueLinesLebel( $eachField ){

 
    if( $eachField == 'qty_to_issue'){

        return '<th class="right-align">'.dashboard_lang("_ISSUE_LINES_".strtoupper($eachField)).'</th>';
    }else{

        return '<th>'.dashboard_lang("_ISSUE_LINES_".strtoupper($eachField)).'</th>';
    }
    
}

function renderReceiptsLinesLebel( $eachField ){

 
    $numberFieldLists = [
        "qty_to_issue",
        "qty",
        "price",
        "amount",
        "vat_amount",
        "received",
        "still_to_receive",
        "subtotal",
        "vat_total",
        "total",
        "qty_at_stock"

    ];

    if(in_array( $eachField , $numberFieldLists )){

        return '<th class="right-align" width="10%">'.dashboard_lang("_CORE_MODULE_LOGISTICS_RECEIPTS_PO_LINES_".strtoupper($eachField)).'</th>';
    }else{

        return '<th width="10%">'.dashboard_lang("_CORE_MODULE_LOGISTICS_RECEIPTS_PO_LINES_".strtoupper($eachField)).'</th>';
    }
}


function renderDeliveryLinesLebel( $eachField ){

 
    $numberFieldLists = [
        "qty_to_issue",
        "qty",
        "price",
        "amount",
        "vat_amount",
        "received",
        "still_to_receive",
        "subtotal",
        "vat_total",
        "total",
        "delivered",
        "still_to_deliver"
    ];

    if( in_array( $eachField , $numberFieldLists )){

        return '<th class="right-align" width="10%">'.dashboard_lang("_CORE_MODULE_LOGISTICS_DELIVERIES_SO_LINES_".strtoupper($eachField)).'</th>';
    }else{

        return '<th width="10%">'.dashboard_lang("_CORE_MODULE_LOGISTICS_DELIVERIES_SO_LINES_".strtoupper($eachField)).'</th>';
    }
    
}

function render3plCustomers( $customerLists, $customerId = 0, $receiptLineId ) {

    $selectField  = "<select name='data3pl_customers_id[".$receiptLineId."]' class='form-control dashboard-dropdown receipt-line-3pl-customers'>";
    $selectField .= "<option value='0'>".dashboard_lang("_SELECT_3PL_CUSTOMER")."</option>";

    foreach ( $customerLists as $eachCustomer ) {

        if ( $eachCustomer["id"] == $customerId ) {

            $selected = "selected";
        }else {
            $selected = "";
        }

        $selectField .= "<option data-relation='".$eachCustomer["customer"]."' data-factor='".$eachCustomer["customer"]."' value='".$eachCustomer["id"]."' ".$selected.">".$eachCustomer["customer"]."</option>";
    }

    $selectField .= "</select>";

    return $selectField;
}

function setIncrementNumber( $incrementNumber, $offset ){

    $systemNumberLength = strlen($offset);
    $increment_number = sprintf("%0".$systemNumberLength."d", $incrementNumber);

    return $increment_number;

}

function getDefaultTenantStatus ( $tableName, $settingsValue ) {

    $CI = &get_instance();
    $CI->load->model("logistics/Doc_Type_Model");

    return $CI->Doc_Type_Model->getDefaultTenantStatus ( $tableName, $settingsValue );

}


function defaultHistoryLineLength ( ) {

    $CI = & get_instance();
    $showHistoryLength = intval($CI->config->item('#CORE_MODULE_LOGISTICS_DEFAULT_HISTORY_LINE_LENGTH'));

    if( $showHistoryLength > 0 ){

        return $showHistoryLength;
    }else{
        return 10;
    }
}

function renderQrCode( $code ){

    $qrCodeUrl = base_url().'qrcode.php?id='. str_replace(" ", "", $code);

    return $qrCodeUrl;
}

function getPurchaseOrderStatus ( ) {

    $CI = &get_instance();
    $CI->load->model("logistics/Doc_Type_Model");

    return $CI->Doc_Type_Model->getPurchaseOrderStatus ( );

}

function getSalesOrderStatus ( ) {

    $CI = &get_instance();
    $CI->load->model("logistics/Doc_Type_Model");

    return $CI->Doc_Type_Model->getSalesOrderStatus ( );

}

function renderTradeboardFileName( $fileName ){

    $CI = &get_instance();
    $fileName =  basename($fileName);
    $fileNameLength = $CI->config->item('#AMGEN_LOG_ATTACHMENTS_FILE_NAME_LENGTH');

    if(strlen($fileName) > $fileNameLength){
        $fileName = substr($fileName, 0, $fileNameLength)." ...";
    }
    
    return  $fileName;
}

function renderStockTransferLinesFieldValue ( $value, $field ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Receipts_Model");

    $lineId = $value;
    
    if($field == 'status_items_id'){

        $status = $CI->Receipts_Model->getStatusList( $value, 'status_items' );
        // return '<td>'. $field.'</td>';
        if(!empty($status['status'])){

            return '<td class="line-search print-value" data-field="<?php echo $field; ?>"><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }

    }else if($field == 'status_batches_id'){

        $status = $CI->Receipts_Model->getStatusList( $value, 'status_batches' );
        
        if(!empty($status['status'])){

            return '<td class="line-search print-value" data-field="<?php echo $field; ?>"><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
        

    }else {
        return '<td class="line-search print-value" data-field="<?php echo $field; ?>">'.$value.'</td>';
    }
}

function renderStockCorrectionsLinesFieldValue ( $value, $field ) {

    $CI = & get_instance();
    $CI->load->model("logistics/Receipts_Model");

    $lineId = $value;
    
    if($field == 'status_items_id'){

        $status = $CI->Receipts_Model->getStatusList( $value, 'status_items' );
        if(!empty($status['status'])){

            return '<td class="line-search print-value" data-field="<?php echo $field; ?>"><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }

    }else if($field == 'status_batches_id'){

        $status = $CI->Receipts_Model->getStatusList( $value, 'status_batches' );
        
        if(!empty($status['status'])){

            return '<td class="line-search print-value" data-field="<?php echo $field; ?>"><span class="m-badge m-badge--wide" style="color: '.$status['text_color'].'; background-color: '.$status['background_color'].';">'.dashboard_lang($status['status']).'</span></td>';
        }else{
            return '<td></td>';
        }
        
        

    }else {
        return '<td class="line-search print-value" data-field="<?php echo $field; ?>">'.$value.'</td>';
    }
}

function renderContacts ( $contacts ) {

    return $contacts["firstname"]." ".$contacts["lastname"]." ( ".$contacts["email"]." ) ";
}

function renderDeliveriesLineColumnForPdf ( $applySettings, $label = '' , $align = '') {

    $CI = &get_instance();
    
    $value =  strtolower ( $CI->config->item($applySettings) );
    if ( $value == 'true' ) {

        $html  =  '<th align="'.$align.'" style="border-bottom: #ccc 2px solid;vertical-align:top;">';
        $html .=      dashboard_lang($label);
        $html .=  "</th>";

        return $html;

    }else {
        return "";
    }
}

function renderDeliveriesLinRowForPdf ( $applySettings,  $fieldValue , $align = '') {

    $CI = &get_instance();

    $value =  strtolower ( $CI->config->item($applySettings) );
    if ( $value == 'true' ) {

        $html  =  '<td align="'.$align.'" style="border-bottom: #ccc 1px solid;vertical-align:top;">';
        $html .=      $fieldValue;
        $html .=  "</td>";

        return $html;

    }else {
        return "";
    }
}

function renderDeliveryListBranchName ( $field ) {

    if ( strlen($field) == '0' ) {

        return "";
    }else {
        return "<span class='doc-branch-name m--font-bolder'>".$field."</span> <br/>";
    }
}

function renderDeliveryListBranchLogo ( $logo = '') {

    $CI = & get_instance();
    $logoWidth = $CI->config->item("#CORE_MODULE_LOGISTICS_DELIVERY_LIST_LOGO_WIDTH");
    
    return "<img class='' width='".$logoWidth."' src='".$logo."' alt=''/>";
   
}

function salesOrderMultipleCreated ( $id ) {

    $CI = & get_instance();

    $CI->load->model("Quotations_Model");

    return $CI->Quotations_Model->salesOrderMultipleCreated ( $id );
}

function getSoStatusLists () {

    $CI = &get_instance();

    $CI->load->model("logistics/Status_Model");

    return $CI->Status_Model->getSalesOrderLinesStatues ();
}

function getQtyDecimal($data)
{
    $account_id = get_account_id();
    $CI = &get_instance();

    $CI->load->model("logistics/Items_Model");
    $query = $CI->Items_Model->getUomByItems ( @$data['items_id'], $account_id);
    
    // check if the uom_id matched from items, provided the item type is 1
    // otherwise, uom_id will be directly utilized for decimals value
    if(trim($data["items-type"]) == "1"){
        if($query->num_rows() && !empty(@$data['items_id'])){
            $found = false;
            foreach($query->result_array() as $result){
                if($result["select_key"] == @$data["uomId"]){
                    $found = true;
                }
            }
            if(!$found){
                @$data["uomId"] = 0;
            }
        }else{
            
            @$data["uomId"] = 0;
        }
    }

    $uom = $CI->db->get_where("uom", array(
        "id" => @$data["uomId"],
    ))->row_array();

    $currencyId = @$data["currencyId"];
    $currencyData = $CI->db->get_where("currencies", [
        "id" => $currencyId,
        "is_deleted" => 0
    ])->row_array();

    $iso2 = !empty($currencyData) ? $currencyData["ISO2"] : "";

    // if uom id is 0, then default settings value will be used as number of decimals value
    $settingsPrefix = $CI->config->item("settingsPrefix");
    $decimals = (int) strip_tags(trim($CI->config->item($settingsPrefix."_NUMBER_OF_DECIMALS")));

    if(!empty($uom) && strlen(trim(@$uom["decimals"])) > 0){
        $decimals = (int) trim(@$uom["decimals"]);
    }

    $numvalue = replaceComma(@$data["numvalue"]);
    $value = renderMoneyFormatForFields ( $iso2, $numvalue, '', $decimals );

    return array("value" => $value);
}
