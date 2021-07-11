<?php

function formatStatusNameInProjects ( $statusName ) {

    return str_replace(" ", "-", strtolower( $statusName)) ;
}