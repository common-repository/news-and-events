<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$article_landing_page_4c5c4291497f5 = ne_optionsHelper_get('article_landing_page');

$newsItems_4c5c4291497f5 = new ne_NewsItems();
$newsItems_4c5c4291497f5->find();

$renderAs_4c5c4291497f5('application/rss+xml');

include 'views/newsCtrl/ajaxRSS.phtml';

?>
