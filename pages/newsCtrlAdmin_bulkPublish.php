<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

if(!isset($_GET['ids'])) ne_redirect("pages/newsCtrlAdmin_index.php");


//Check for just one singular ID
if(is_numeric($_GET['ids']))
	$ids = array($_GET['ids']);
else
	$ids = explode(',', $_GET['ids']);

foreach($ids as $id) {
	$item = new ne_NewsItem();
	$item->load($id);

	$item->published = 1;
	$item->save();
}

ne_redirect("pages/newsCtrlAdmin_index.php");



?>
