<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

if( ne_DBHelper_tableExists('ne_news') ) {
	$newsDeleted_4c5c42914768c = ne_DBHelper_dropTable('ne_news');
}

if( ne_DBHelper_tableExists('ne_events') ) {
	$eventsDeleted_4c5c42914768c = ne_DBHelper_dropTable('ne_events');
}

include dirname(__FILE__) . '/views/newsAndEvents/uninstall.phtml';


?>
