<?php
include dirname(dirname(dirname(dirname(__FILE__)))) . "/wp-load.php";
wp();

include_once 'ne_includes.php';

if(isset($_GET['r'])) {
	//Get the array of expected pages that could be included
	global $ne_pages;
	
	//Retrieve the passed-in file name and strip off the extension
	$page_index = basename($_GET['r'], '.php');
	
	//Make sure the requested file is one that is expected
	$page = $ne_pages[$page_index];
	include 'pages/' . $page;
}

?>
