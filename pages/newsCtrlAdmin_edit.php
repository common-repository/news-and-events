<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';



$newsItem_4c5c42914ad5f = new ne_NewsItem();
$newsItem_4c5c42914ad5f->load(ne_Request::$args['id']);

include dirname(__FILE__) . '/views/newsCtrlAdmin/edit.phtml';

if(ne_sessionHelper_formHasErrors()) ne_sessionHelper_clearErrors();


?>
