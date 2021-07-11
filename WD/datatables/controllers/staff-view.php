<?php

// DataTables PHP library
include( "../lib/DataTables.php" );

// Alias Editor classes so they are easy to use
use
	DataTables\Editor,
	DataTables\Editor\Field,
	DataTables\Editor\Format,
	DataTables\Editor\Mjoin,
	DataTables\Editor\Options,
	DataTables\Editor\Upload,
	DataTables\Editor\Validate,
	DataTables\Editor\ValidateOptions;

// Build our Editor instance and process the data coming from _POST
Editor::inst( $db, 'users' )
	->readTable('staff_newyork') // The VIEW to read data from
	->field( 
		Field::inst( 'first_name' ),
		Field::inst( 'last_name' ),
		Field::inst( 'phone' ),
		Field::inst( 'city' ),
		Field::inst( 'site' )
			->get(false)
			->setValue(4) // New York, for the VIEW condition
	)
	->tryCatch(false)
	->debug(true)
	->process($_POST)
	->json();
