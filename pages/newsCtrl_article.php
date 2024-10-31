<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$settings_4c5c4291484df = ne_optionsHelper_mergeOptions(ne_Request::$args, 'article');

$news_4c5c429147dc8 = new ne_NewsItem();
$news_4c5c429147dc8->load(ne_Request::$args['id']);
include dirname(__FILE__) . '/views/newsCtrl/article.phtml';


?>
