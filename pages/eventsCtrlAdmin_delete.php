<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$id = ne_Request::$args['id'];

$event = new ne_Event();
$event->load($id);
$event->drop();

$rel = new ne_TagRelationships();
$rel->find("entry_id = $id AND type='event'");
while($r = $rel->next()) {
	$rel->drop();
}

ne_newsAndEventsHelper_removeUnusedTags();

ne_redirect("pages/eventsCtrlAdmin_index.php");



?>
