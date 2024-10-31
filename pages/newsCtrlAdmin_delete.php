<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$newsItem = new ne_NewsItem();
$newsItem->load(ne_Request::$args['id']);
$newsItem->drop();
ne_redirect("pages/newsCtrlAdmin_index.php");



?>
