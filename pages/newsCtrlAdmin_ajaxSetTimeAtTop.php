<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

//Grab the top item
$item = new ne_NewsItem();
$item->fromTop(0);

$item->sort_date = mktime(0, 0, 0, $_GET['month'], $_GET['day'], $_GET['year']);

$item->save();


?>
