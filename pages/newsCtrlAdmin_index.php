<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';




if( ne_newsAndEventsHelper_isInstalled() === false ) {
	ne_redirect("pages/newsAndEvents_confirmInstall.php");

	exit();
}

$news_4c5c429147dc8 = new ne_NewsItems();
$news_4c5c429147dc8->find("", array(
	'order' => 'date DESC'
));

include 'views/newsCtrlAdmin/index.phtml';

?>
