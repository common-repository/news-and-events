<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

header("content-type: text/html; charset=utf-8");
$alreadyInstalled_4c5c429146f18 = false;
if(ne_newsAndEventsHelper_isInstalled() == true) {
	$alreadyInstalled_4c5c429146f18 = true;
	include dirname(__FILE__) . '/views/newsAndEvents/install.phtml';
}
else {
	global $wpdb;

	$defs = array(array(
		"name" => "ne_news",
		"fields" => array(
			"id" 		=> array( "type" => "int", "auto_increment" => true ),
			"headline" 	=> array( "type" => "varchar", "length" => 255 ),
			"author" 	=> array( "type" => "varchar", "length" => 255),
			"date" 		=> array( "type" => "date" ),
			"sort_date" 	=> array( "type" => "date"),
			"excerpt" 	=> array( "type" => "text"),
			"article" 	=> array( "type" => "text" ),
			"image" 	=> array( "type" => "varchar", "length" => 255),
			"thumbnail" 	=> array( "type" => "varchar", "length" => 255),
			"published" 	=> array( "type" => "tinyint" )
		),
		"primary_key" => "id"
	), array(
		"name" => "ne_events",
		"fields" => array(
			"id" 			=> array( "type" => "int", "auto_increment" => true ),
			"title" 		=> array( "type" => "varchar", "length" => 255 ),
			"start_date"		=> array( "type" => "datetime" ),
			"end_date"		=> array( "type" => "datetime" ),
			"sort_date" 		=> array( "type" => "date"),
			"location" 		=> array( "type" => "varchar", "length" => 255 ),
			"date_location_text" 	=> array( "type" => "text" ),
			"short_description"	=> array( "type" => "text" ),
			"long_description"	=> array( "type" => "text" ),
			"image" 		=> array( "type" => "varchar", "length" => 255 ),
			"thumbnail" 		=> array( "type" => "varchar", "length" => 255 ),
			"published"		=> array( "type" => "tinyint" )
		),
		"primary_key" => "id"
	), array(
		"name" => "ne_options",
		"fields" => array(
			"name" 		=> array( "type" => "varchar", "length" => 255 ),
			"value" 	=> array( "type" => "varchar", "length" => 255 ),
			"type" 		=> array( "type" => "enum ('checkbox', 'radio', 'text', 'integer')" )
		),
		"primary_key" => "name"
	), array(
		"name" => "ne_tags",
		"fields" => array(
			"id" 		=> array( "type" => "int", "auto_increment" => true ),
			"name" 		=> array( "type" => "varchar", "length" => 255 )
		),
		"primary_key" => "id"
	), array(
		"name" => "ne_tag_relationships",
		"fields" => array(
			"id" 		=> array( "type" => "int", "auto_increment" => true ),
			"entry_id" 	=> array( "type" => "int" ),
			"tag_id" 	=> array( "type" => "int" ),
			"type" 		=> array( "type" => "enum ('news', 'event')" )
		),
		"primary_key" => "id"
	)
	);
	
	$warnings_4c5c429146f18 = array();
	$errors_4c5c429146f18 = array();
	global $wpdb;
	foreach ($defs as $table) {
		if(ne_DBHelper_tableExists($table['name']) == false) {
			$created = ne_DBHelper_createTable($table);

			if ($created === false) {
				$errors_4c5c429146f18[] = "There was an error creating the table <code>{$table['name']}</code> Error: <code>" . mysql_error() . "</code>";
			}
		}
		else {
			$warnings_4c5c429146f18[] = "Table `" . $table['name'] . "` already existed.";
		}
	}

	// TODO: Create default settings
	$ops = ne_Options::$optionsDef;

	foreach ($ops as $op) {
		//Don't use the model here. As the table was just created, the model won't have the correct field names registered
		//$wpdb->query("INSERT INTO ne_options (`name`, `value`, `type`) VALUES ('{$op['name']}', '{$op['value']}', '{$op['type']}')");
	}

	include dirname(__FILE__) . '/views/newsAndEvents/install.phtml';
}


?>
