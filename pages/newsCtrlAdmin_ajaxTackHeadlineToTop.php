<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$id = $_GET['id'];

$item = new ne_NewsItem();
$item->load($id);

//Default to one day ahead

$lower = new ne_NewsItem();
$lower->fromTop(0);
$dayLength = 60*60*24;

if($lower->sortDateSet()) {
	$item->sort_date = $lower->sortDateAsTimestamp() + $dayLength;
}
else {
	$item->sort_date = $lower->dateAsTimestamp() + $dayLength;
}

$item->save();

echo $item->sortYear() . " " . $item->sortMonth() . " " . $item->sortDay();


?>
