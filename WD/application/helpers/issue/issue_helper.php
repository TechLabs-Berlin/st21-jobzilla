<?php

    

    function statusName ( $statusName ) {

        return str_replace(" ", "-", strtolower( $statusName)) ;
    }

    function formatKeyIssues( $fieldName, $prefix ) {
            
        $langKey = $prefix.strtoupper("_".$fieldName);
        
        return $langKey;
    }

    function renderIssueHistoryLineWithBreak ( $time, $msg ) {

       $traslatedMsg = replaceIssueFields($msg);
    
       $html =  '<li>'.date('d M Y H:i', $time).'&nbsp;&nbsp;&nbsp;&nbsp;'.$traslatedMsg.'</li>';
       return $html;

    }

    function replaceIssueFields($message) {

        $message = str_replace("_CORE_MODULE_LOGISTICS_ISSUES_CREATED_BY", dashboard_lang("_CORE_MODULE_LOGISTICS_ISSUES_CREATED_BY"), $message);
        $message = str_replace("_CREATED_BY", dashboard_lang("_CREATED_BY"), $message);
        $message = str_replace("_DOCUMENT_DELETED_BY", dashboard_lang("_DOCUMENT_DELETED_BY"), $message);
        $message = str_replace("_DOCUMENT_UPLOADED_BY", dashboard_lang("_DOCUMENT_UPLOADED_BY"), $message);
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
        $message = str_replace("_CORE_MODULE_LOGISTICS_ISSUES", dashboard_lang("_CORE_MODULE_LOGISTICS_ISSUES"), $message);
        $message = str_replace("_LINES_TAB", dashboard_lang("_LINES_TAB"), $message);
        $message = str_replace("_ATTACHMENTS_TAB", dashboard_lang("_ATTACHMENTS_TAB"), $message);
        $message = str_replace("_MESSAGE_TAB", dashboard_lang("_MESSAGE_TAB"), $message);
        $message = str_replace("_QTY_TO_ISSUE", dashboard_lang("_QTY_TO_ISSUE"), $message);
        $message = str_replace("_PROJECT_TO_ISSUE", dashboard_lang("_PROJECT_TO_ISSUE"), $message);
        $message = str_replace("_ISSUE_LINES", dashboard_lang("_ISSUE_LINES"), $message);
        $message = str_replace("_ISSUES_STATUS", dashboard_lang("_ISSUES_STATUS"), $message);
        $message = str_replace("_STATUS_ISSUES_ID", dashboard_lang("_STATUS_ISSUES_ID"), $message);
        $message = str_replace("_ISSUE_LINES_NOTES", dashboard_lang("_ISSUE_LINES_NOTES"), $message);
        $message = str_replace("_BY", dashboard_lang("_BY"), $message);
        



        $fields = array(
            'issue_id',
            'item_code',
            'date',
            'note',
            'status_issues_id',
            '3pl_customers_id',
        );

        foreach ($fields as $eachField) {
            $message = str_replace('_'.strtoupper($eachField), '"'.dashboard_lang('_'.strtoupper($eachField)).'"', $message );
        }

        $message = str_replace("_ID", dashboard_lang("_ID"), $message);

        return $message;
    }

   

?>