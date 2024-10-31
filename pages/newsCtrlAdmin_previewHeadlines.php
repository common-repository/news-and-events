<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

wp_enqueue_script( array("jquery", "jquery-ui-core", "interface", "jquery-ui-sortable", "wp-lists", "jquery-ui-sortable") );


ne_Options::load();
//Get the option for how many news items are in the headline view
$numHeadlines_4c5c42914c63e = ne_optionsHelper_get("headlines_filter_number");

//Pull all the news items by date
$newsItems_4c5c4291497f5 = new ne_NewsItems();
$newsItems_4c5c4291497f5->allByDate();

include dirname(__FILE__) . '/views/newsCtrlAdmin/previewHeadlines.phtml';


?>
