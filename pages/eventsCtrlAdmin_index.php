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

$events_4c5c42914e22d = new ne_Events();
$events_4c5c42914e22d->find("", array(
	'order' => 'start_date DESC'
));


include 'views/eventsCtrlAdmin/index.phtml';

?>
