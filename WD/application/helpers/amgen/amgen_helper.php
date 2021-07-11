<?php

function areaName( $slug ){

    $slug = str_replace( " ", "_", strtolower( trim($slug)) );
    $slug = str_replace( "/", "", $slug );
    
    return $slug;
}

?>