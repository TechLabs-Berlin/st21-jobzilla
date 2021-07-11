<?php

    function eventLogData($table, $rowId) {

        $CI = &get_instance();
        $CI->load->model('portal/Customers_Model');

        return $CI->Customers_Model->getEventLogData( $table, $rowId );

    }
    
    
    function getDashboardUserInfo($id) {

        $CI = &get_instance();
        $CI->load->model('portal/Customers_Model');

        return $CI->Customers_Model->getDashboardUserInfo($id);
    }

    function formatStatusNameInCustomer ( $statusName ) {

        return str_replace(" ", "-", strtolower( $statusName)) ;
    }

    function renderSingleLineWithBreak ( $field ) {

        if ( strlen($field) == '0' ) {
    
            return "";
        }else {
            return "<span>".$field."</span> <br/>";
        }
    }

    function renderLineWithBreak ( $field ) {

        if ( strlen($field) == '0' ) {
    
            return "";
        }else {
            return $field."<br/>";
        }
    }
    
    function renderSingleLineWithOutBreak ( $field ) {
        
        if ( strlen($field) == '0' ) {
            
            return "";
        }else {
            return "<span>".$field."</span> ";
        }
    }
    
    function formatKeyCustomer( $fieldName, $prefix ) {
        
        $langKey = $prefix.strtoupper("_".$fieldName);
        
        return $langKey;
    }
    
    function renderHistoryLineWithBreak ( $time, $msg ) {

       $traslatedMsg = replaceFields($msg);
    
       $html =  '<li>'.date('d M Y H:i', $time).'&nbsp;&nbsp;&nbsp;&nbsp;'.$traslatedMsg.'</li>';
       return $html;

    }

    function replaceFields($message) {

        $message = str_replace("_CUSTOMER_CREATED_BY", dashboard_lang("_CUSTOMER_CREATED_BY"), $message);
        $message = str_replace("_DOCUMENT_UPLOADED_BY", dashboard_lang("_DOCUMENT_UPLOADED_BY"), $message);
        $message = str_replace("_DOCUMENT_DELETED_BY", dashboard_lang("_DOCUMENT_DELETED_BY"), $message);
        $message = str_replace("_CHANGED_TO", dashboard_lang("_CHANGED_TO"), $message);
        $message = str_replace("_CUSTOMER_SALES_INVOICE", dashboard_lang("_CUSTOMER_SALES_INVOICE"), $message);
        $message = str_replace("_CUSTOMER_SALES_ORDER", dashboard_lang("_CUSTOMER_SALES_ORDER"), $message);
        $message = str_replace("_CUSTOMER_QUOTATION", dashboard_lang("_CUSTOMER_QUOTATION"), $message);
        $message = str_replace("_CUSTOMER_CONTACT", dashboard_lang("_CUSTOMER_CONTACT"), $message);
        $message = str_replace("_CONTACT_CUSTOMER_LOCATION", dashboard_lang("_CONTACT_CUSTOMER_LOCATION"), $message);
        $message = str_replace("_ADDED_BY", dashboard_lang("_ADDED_BY"), $message);
        $message = str_replace("_DELETED_BY", dashboard_lang("_DELETED_BY"), $message);
        $message = str_replace("_IN_CONTACT_TAB", dashboard_lang("_IN_CONTACT_TAB"), $message);
        $message = str_replace("_IN_LOCATION_TAB", dashboard_lang("_IN_LOCATION_TAB"), $message);
        $message = str_replace("_QUOTATIONS_ID", dashboard_lang("_QUOTATIONS_ID"), $message);
        $message = str_replace("_SALES_INVOICES_ID", dashboard_lang("_SALES_INVOICES_ID"), $message);
        $message = str_replace("_CREATED_BY", dashboard_lang("_CREATED_BY"), $message);
        $message = str_replace("_BY", dashboard_lang("_BY"), $message);

        $fields = array(
            'contact_customers_firstname',
            'contact_customers_lastname',
            'contact_customers_phone',
            'contact_customers_email',
            'contact_customers_custom_roles_id',
            'contact_customers_contact_customers_notes',
            'location_customers_location', 
            'location_customers_addres1', 
            'location_customers_addres2', 
            'location_customers_zipcode', 
            'location_customers_city',  
            'location_customers_country',
            'customers_status_customers_id', 
            'customers_currencies_id', 
            'customers_contact_customers_id', 
            'customers_location_customers_id', 
            'customers_mobile_number', 
            'customers_customer',  
            'customers_alternative_code',  
            'customers_email',  
            'customers_phone',  
            'customers_website',  
            'customers_coc',  
            'customers_vats_id',  
            'customers_vat',  
            'customers_bank',  
            'customers_doctext', 
            'customers_location_types_id', 
            'customers_invoice_custom_roles_id', 
            'customers_category_customers_id', 
            'customers_sales_invoices_due_days', 
        );

        foreach ($fields as $eachField) {
            $message = str_replace('_'.strtoupper($eachField), '"'.dashboard_lang('_'.strtoupper($eachField)).'"', $message );
        }

        return $message;
    }

    function render_with_download_link( $filePath, $settingsPrefix="", $settingsPostfix="_MAX_UPLOADED_FILE_NAME_LENGTH" ) {
        $ci = & get_instance();
        if ( strlen($filePath) == '0' ){
    
            return '';
        }
    
        $fileDetails = pathinfo( $filePath );
        $image_ext = array("jpg", "gif", "png", "jpeg", "JPG", "bitmap");
        $xlsx_ext = array("xls", "csv", "xlsx");
        $docs_ext = array("docs", "doc", "DOCX", "DOCS", "docx");
        $txt_ext = array("txt", "text");
        $pdf_ext = array( "pdf", "PDF" );
 
        if ( in_array(trim($fileDetails['extension']), $image_ext) ) {
    
            $fa_icon = "fa fa-file-image-o";
        }else if ( in_array(trim($fileDetails['extension']), $xlsx_ext)) {
            $fa_icon = "fa fa-file-excel-o";
        }else if (in_array(trim($fileDetails['extension']), $txt_ext)) {
            $fa_icon = "fa fa-file-text";
        }else if (in_array(trim($fileDetails['extension']), $docs_ext)){
            $fa_icon = "fa fa-file-word-o";
        }else if (in_array(trim($fileDetails['extension']), $pdf_ext)){
            $fa_icon = "fa fa-file-pdf-o";
        } else {
            $fa_icon = "fa fa-file";
        }
         
        $fileName = $fileDetails['basename'];
        $fileNameLength = $ci ->config->item( $settingsPrefix . $settingsPostfix );       
       if ( strlen($fileName) > $fileNameLength ) {
           
           $fileName = substr($fileName, 0, $fileNameLength)." ...";
       }
        
       return "<a target='_blank' class='file-with-icon' href='".$filePath."'> <i class='".$fa_icon." fa-2x' aria-hidden='true'></i> ".$fileName."</a>";       
         
    }

    function getShortContent($long_text = '', $show = 60)
    {

        $filtered_text = strip_tags($long_text);
        if ($show < strlen($filtered_text)) {
            return substr($filtered_text, 0, $show) . '...';
        } else {
            return $filtered_text;
        }
    }

    function renderCustomerInternalNotes($tableName, $selectWhere, $tableId){

        $CI = &get_instance();
        $CI->load->model('portal/Customers_Model');

        $notes = $CI->Customers_Model->renderInternalNotes( $tableName, $selectWhere, $tableId );
        $characterLength = $CI->config->item('#TECHNATION_CUSTOMER_INTERNAL_NOTES_CHARACTER_SHOW');
        $allNotes['notes'] = $notes['notes'];
        
        if ($characterLength < strlen($notes['subNotes'])) {
            $allNotes['subNotes'] = substr($notes['subNotes'], 0, $characterLength) . '...';
        } else {
            $allNotes['subNotes'] = $notes['subNotes'];
        }

        return $allNotes;

    }

    function renderLocationDropdown( $locationLists,  $locationCustomersId ) {

        $selectField  = "<select  class='form-control dashboard-dropdown on-change-location'>";
        $selectField .= "<option value='0'>".dashboard_lang("_PLEASE_SELECT")."</option>";
    
        foreach ( $locationLists as $eachLocation ) {
    
            if ( $eachLocation["id"] == $locationCustomersId ) {
    
                $selected = "selected";
            }else {
                $selected = "";
            }
    
            $selectField .= "<option value='".$eachLocation["id"]."' ".$selected.">".$eachLocation["location"]."</option>";
        }
    
        $selectField .= "</select>";
    
        return $selectField;
    }

    function renderContactDropdown( $contactLists,  $contactCustomersId ) {

        $selectField  = "<select  class='form-control dashboard-dropdown on-change-contact on-dropdown-contact'>";
        $selectField .= "<option value='0'>".dashboard_lang("_PLEASE_SELECT")."</option>";
    
        foreach ( $contactLists as $eachContact ) {
    
            if ( $eachContact["id"] == $contactCustomersId ) {
    
                $selected = "selected";
            }else {
                $selected = "";
            }
    
            $selectField .= "<option value='".$eachContact["id"]."' ".$selected.">".$eachContact["firstname"]." ".$eachContact["lastname"]."</option>";
        }
    
        $selectField .= "</select>";
    
        return $selectField;
    }

?>