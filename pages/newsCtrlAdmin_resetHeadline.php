<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$id = $_GET['id'];

$item = new ne_NewsItem();
$item->load($id);

$item->sort_date = '0000-00-00';

$item->save();

ne_redirect("pages/newsCtrlAdmin_previewHeadlines.php");



?>
