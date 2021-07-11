<?php

function renderHistoryLineWithBreakSupplier ( $time, $msg ) {

    $traslatedMsg = replaceFieldsSupplier($msg);
 
    $html =  '<li>'.date('d M Y H:i', $time).'&nbsp;&nbsp;&nbsp;&nbsp;'.$traslatedMsg.'</li>';
    return $html;

 }

 function replaceFieldsSupplier($message) {

     $message = str_replace("_SUPPLIER_CREATED_BY", dashboard_lang("_SUPPLIER_CREATED_BY"), $message);
     $message = str_replace("_DOCUMENT_UPLOADED_BY", dashboard_lang("_DOCUMENT_UPLOADED_BY"), $message);
     $message = str_replace("_DOCUMENT_DELETED_BY", dashboard_lang("_DOCUMENT_DELETED_BY"), $message);
     $message = str_replace("_CHANGED_TO", dashboard_lang("_CHANGED_TO"), $message);
     $message = str_replace("_SUPPLIER_PURCHASE_ORDER", dashboard_lang("_SUPPLIER_PURCHASE_ORDER"), $message);
     $message = str_replace("_SUPPLIER_REQUEST", dashboard_lang("_SUPPLIER_REQUEST"), $message);
     $message = str_replace("_SUPPLIER_CONTACT", dashboard_lang("_SUPPLIER_CONTACT"), $message);
     $message = str_replace("_CONTACT_SUPPLIER_LOCATION", dashboard_lang("_CONTACT_SUPPLIER_LOCATION"), $message);
     $message = str_replace("_ADDED_BY", dashboard_lang("_ADDED_BY"), $message);
     $message = str_replace("_DELETED_BY", dashboard_lang("_DELETED_BY"), $message);
     $message = str_replace("_IN_CONTACT_TAB", dashboard_lang("_IN_CONTACT_TAB"), $message);
     $message = str_replace("_IN_LOCATION_TAB", dashboard_lang("_IN_LOCATION_TAB"), $message);
     $message = str_replace("_CREATED_BY", dashboard_lang("_CREATED_BY"), $message);
     $message = str_replace("_REQUESTS_ID", dashboard_lang("_REQUESTS_ID"), $message);
     $message = str_replace("_PURCHASE_ORDERS_ID", dashboard_lang("_PURCHASE_ORDERS_ID"), $message);
     $message = str_replace("_BY", dashboard_lang("_BY"), $message);

     $fields = array(
         'contact_suppliers_firstname',
         'contact_suppliers_lastname',
         'contact_suppliers_phone',
         'contact_suppliers_email',
         'contact_suppliers_custom_roles_id',
         'contact_suppliers_contact_suppliers_notes',
         'location_suppliers_location', 
         'location_suppliers_addres1', 
         'location_suppliers_addres2', 
         'location_suppliers_zipcode', 
         'location_suppliers_city',  
         'location_suppliers_country',
         'suppliers_status_suppliers_id', 
         'suppliers_currencies_id', 
         'suppliers_contact_suppliers_id', 
         'suppliers_location_suppliers_id', 
         'suppliers_languages_id', 
         'suppliers_supplier_types_id',  
         'suppliers_supplier',  
         'suppliers_alternative_code',  
         'suppliers_email',  
         'suppliers_phone',  
         'suppliers_website',  
         'suppliers_coc',  
         'suppliers_vats_id',  
         'suppliers_vat',  
         'suppliers_bank',  
         'suppliers_doctext',
         'suppliers_mobile_number', 
     );

     foreach ($fields as $eachField) {
         $message = str_replace('_'.strtoupper($eachField), '"'.dashboard_lang('_'.strtoupper($eachField)).'"', $message );
     }

     return $message;
 }


 function renderSupplierInternalNotes($tableName, $selectWhere, $tableId){

    $CI = &get_instance();
    $CI->load->model('portal/Suppliers_Model');

    $notes = $CI->Suppliers_Model->renderInternalNotes( $tableName, $selectWhere, $tableId );
    $characterLength = $CI->config->item('#TECHNATION_CUSTOMER_INTERNAL_NOTES_CHARACTER_SHOW');
    $allNotes['notes'] = $notes['notes'];
    
    if ($characterLength < strlen($notes['subNotes'])) {
        $allNotes['subNotes'] = substr($notes['subNotes'], 0, $characterLength) . '...';
    } else {
        $allNotes['subNotes'] = $notes['subNotes'];
    }

    return $allNotes;

}



?>