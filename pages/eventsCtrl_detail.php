<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

//Get the option values from the options/settings table but override 
//them with any options set in the short code that called for this view
$settings_4c5c4291484df = ne_optionsHelper_mergeOptions(ne_Request::$args, 'eventDetail');

$event_4c5c42914efb8 = new ne_Event();
$event_4c5c42914efb8->load(ne_Request::$args['id']);
include dirname(__FILE__) . '/views/eventsCtrl/detail.phtml';


?>
