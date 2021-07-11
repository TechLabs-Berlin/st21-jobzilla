<?php
    
    function renderItemHistoryLineWithBreak ( $time, $msg ) {

       $traslatedMsg = replaceItemFields($msg);
    
       $html =  '<li>'.date('d M Y H:i', $time).'&nbsp;&nbsp;&nbsp;&nbsp;'.$traslatedMsg.'</li>';
       return $html;

    }

    function replaceItemFields($message) {

        $message = str_replace("_CORE_MODULE_LOGISTICS_ITEMS_CREATED_BY", dashboard_lang("_CORE_MODULE_LOGISTICS_ITEMS_CREATED_BY"), $message);
        $message = str_replace("_DOCUMENT_DELETED_BY", dashboard_lang("_DOCUMENT_DELETED_BY"), $message);
        $message = str_replace("_PROFILE_IMAGE", dashboard_lang("_PROFILE_IMAGE"), $message);
        $message = str_replace("_STATUS_ITEMS_ID", dashboard_lang("_STATUS_ITEMS_ID"), $message);
        $message = str_replace("_MEDIA_FILE_UPLOADED_BY", dashboard_lang("_MEDIA_FILE_UPLOADED_BY"), $message);
        $message = str_replace("_CHANGED_TO", dashboard_lang("_CHANGED_TO"), $message);
        $message = str_replace("_CUSTOMER_SALES_INVOICE", dashboard_lang("_CUSTOMER_SALES_INVOICE"), $message);
        $message = str_replace("_CUSTOMER_SALES_ORDER", dashboard_lang("_CUSTOMER_SALES_ORDER"), $message);
        $message = str_replace("_CUSTOMER_QUOTATION", dashboard_lang("_CUSTOMER_QUOTATION"), $message);
        $message = str_replace("_CUSTOMER_CONTACT", dashboard_lang("_CUSTOMER_CONTACT"), $message);
        $message = str_replace("_CONTACT_CUSTOMER_LOCATION", dashboard_lang("_CONTACT_CUSTOMER_LOCATION"), $message);
        $message = str_replace("_ADDED_BY", dashboard_lang("_ADDED_BY"), $message);
        $message = str_replace("_DELETED_BY", dashboard_lang("_DELETED_BY"), $message);
        $message = str_replace("_IN_CONTACT_TAB", dashboard_lang("_IN_CONTACT_TAB"), $message);
        $message = str_replace("_CORE_MODULE_LOGISTICS_ITEMS", dashboard_lang("_CORE_MODULE_LOGISTICS_ITEMS"), $message);
        $message = str_replace("_PURCHASE_ORDER_LINES", dashboard_lang("_PURCHASE_ORDER_LINES"), $message);
        $message = str_replace("_RECEIPT_ON_PO_UOM", dashboard_lang("_RECEIPT_ON_PO_UOM"), $message);
        $message = str_replace("_RECEIPT_ON_PO_STOCKTRANSACTION", dashboard_lang("_RECEIPT_ON_PO_STOCKTRANSACTION"), $message);
        $message = str_replace("_RECEIPT_ON_PO_BATCH", dashboard_lang("_RECEIPT_ON_PO_BATCH"), $message);
        $message = str_replace("_DELIVERY_ON_SO_UOM", dashboard_lang("_DELIVERY_ON_SO_UOM"), $message);
        $message = str_replace("_DELIVERY_ON_SO_STOCKTRANSACTION", dashboard_lang("_DELIVERY_ON_SO_STOCKTRANSACTION"), $message);
        $message = str_replace("_DELIVERY_ON_SO_BATCH", dashboard_lang("_DELIVERY_ON_SO_BATCH"), $message);
        $message = str_replace("_ISSUE_UOM", dashboard_lang("_ISSUE_UOM"), $message);
        $message = str_replace("_ISSUE_STOCKTRANSACTION", dashboard_lang("_ISSUE_STOCKTRANSACTION"), $message);
        $message = str_replace("_ISSUE_BATCH", dashboard_lang("_ISSUE_BATCH"), $message);
        $message = str_replace("_IN_STOCK_ID", dashboard_lang("_IN_STOCK_ID"), $message);
        $message = str_replace("_STOCK_ID", dashboard_lang("_STOCK_ID"), $message);
        $message = str_replace("_LINES_NOTES", dashboard_lang("_LINES_NOTES"), $message);
        

        $message = str_replace("_CREATED_BY", dashboard_lang("_CREATED_BY"), $message);
        $message = str_replace("_ACCEPTED_BY", dashboard_lang("_ACCEPTED_BY"), $message);
        $message = str_replace("_DOCUMENT_UPLOADED_BY", dashboard_lang("_DOCUMENT_UPLOADED_BY"), $message);
        
        $message = str_replace("_BY", dashboard_lang("_BY"), $message);
        $message = str_replace("_CREATED", dashboard_lang("_CREATED"), $message);
        $message = str_replace("_DRAFT", dashboard_lang("_DRAFT"), $message);
        

        $fields = array(
            'long_description',
            'country_of_origin',
            'hscode',
            'item_id',
            'item_code',
            'item',
            'stocktransactions_id',
            'price',
            'stock_value',
            'description',
            'uom_id', 
            'stock_uom', 
            'factor', 
            'profile_image', 
            'status_items_id',  
            'categories_id', 
            'weight_uom', 
            'weight', 
            'width_uom', 
            'width', 
            'height_uom',  
            'height',  
            'depth_uom', 
            'depth',  
 
            
        );

        foreach ($fields as $eachField) {
            $message = str_replace('_'.strtoupper($eachField), '"'.dashboard_lang('_'.strtoupper($eachField)).'"', $message );
        }

        $message = str_replace("_ID", dashboard_lang("_ID"), $message);

        return $message;
    }


    function getTotalStockCount ( $itemId ) {

        $CI = & get_instance();

        $CI->load->model("logistics/Items_Model");

        return $CI->Items_Model->getTotalStockCount ( $itemId );
    }


    /*
 * renders image in a form
 */
function items_render_file($src_file, $file_upload_path, $name, $field_status, $ajaxSavingClass="", $validation = "")
{
    $html = '<div>';
    if ((stripos($src_file, "http://") !== false) || (stripos($src_file, "https://") !== false)) {
        $ext = explode('/', $src_file);
        $file_name = end($ext);
        $html .= '<a href="' . $src_file . '">' . $file_name . '</a>';

        if( strpos ( $validation, "required" ) === false){

            $html .= '<input type="checkbox" class="delete-checkbox '. $ajaxSavingClass .'" name="' . $name . '" value="1" ' . $field_status . '/>' . dashboard_lang('_DELETE_THIS_FILE');
        }
        
    } else {
        if (! empty($src_file)) {
            $html .= '<span style="width: 100px !important; overflow: hidden; height: 25px; float: left;">';
            $html .= '<a href="' . base_url() . $file_upload_path . $src_file . '" target="_blank">' . $src_file . '</a>';
            $html .= '</span>';

            if( strpos ( $validation, "required" ) === false){
                $html .= '<input type="checkbox" class="delete-checkbox '. $ajaxSavingClass .'" name="' . $name . '" value="1" ' . $field_status . '/>' . dashboard_lang('_DELETE_THIS_FILE');
            }
        }
    }
    
    $html .= '</div>';
    return $html;
}


function getItemsCategoriesLists () {

    $CI = & get_instance();
    $CI->load->model("logistics/Category_items_Model");
    
    return $CI->Category_items_Model->getCategoryLists( 0 );
}


function getSavedPrinter() {
        
    $CI = & get_instance();
    $CI->load->model("logistics/Items_Model");
    
    return $CI->Items_Model->getSavedPrinters ( );
    
}
?>