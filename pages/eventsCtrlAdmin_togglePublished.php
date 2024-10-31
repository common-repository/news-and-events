<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$id = ne_Request::$args['id'];
$event = new ne_Events();
$event->find($id);

$event->published = ($event->fw_recordset->fields['publishd'] == 1 ? 0 : 1);

$event->save();

echo $event->fw_recordset->fields['id'];


?>
