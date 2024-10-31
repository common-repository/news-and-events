<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';



$event_4c5c42914efb8 = new ne_Event();
$event_4c5c42914efb8->load(ne_Request::$args['id']);

include dirname(__FILE__) . '/views/eventsCtrlAdmin/edit.phtml';

if(ne_sessionHelper_formHasErrors()) ne_sessionHelper_clearErrors();


?>
