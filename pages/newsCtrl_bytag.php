<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$settings_4c5c4291484df = ne_optionsHelper_mergeOptions(ne_Request::$args, 'newslist');

$tag = ne_Request::$args['t'];

$news_4c5c429147dc8 = new ne_NewsItems();
$news_4c5c429147dc8->query("	SELECT " . $news_4c5c429147dc8->includeFields() . " FROM ne_tag_relationships
			JOIN ne_tags ON ne_tag_relationships.tag_id = ne_tags.id
			JOIN ne_news ON ne_tag_relationships.entry_id = ne_news.id
			WHERE ne_tag_relationships.type = 'news'
			AND ne_tags.name = '$tag'");

include dirname(__FILE__) . '/views/newsCtrl/index.phtml';


?>
