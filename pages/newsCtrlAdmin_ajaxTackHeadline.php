<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$id = $_GET['id'];
$positionFromTop = $_GET['positionFromTop'];

$item = new ne_NewsItem();
$item->load($id);

$upper = new ne_NewsItem();
$upper->fromTop($positionFromTop);

$lower = new ne_NewsItem();
$lower->fromTop($positionFromTop + 1);

$item->sort_date = ($upper->dateAsTimestamp() + $lower->dateAsTimestamp()) / 2;

$item->save();


?>
