<?php

    function eventLogData($table, $rowId) {

        $CI = &get_instance();
        $CI->load->model('portal/Branches_Model');

        return $CI->Branches_Model->getEventLogData( $table, $rowId );

    }
    
    
    function getDashboardUserInfo($id) {

        $CI = &get_instance();
        $CI->load->model('portal/Branches_Model');

        return $CI->Branches_Model->getDashboardUserInfo($id);
    }

    function formatStatusNameInCustomer ( $statusName ) {

        return str_replace(" ", "-", strtolower( $statusName)) ;
    }


?>