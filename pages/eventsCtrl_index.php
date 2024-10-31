<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$eventsList_4c5c4291513a7 = new ne_Events();
$eventsList_4c5c4291513a7->find("", 0, 0, "date DESC");
include dirname(__FILE__) . '/views/eventsCtrl/index.phtml';


?>
