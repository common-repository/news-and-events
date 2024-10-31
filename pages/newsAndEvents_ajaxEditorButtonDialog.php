<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';



$newsListNumPerPage_4c5c429147dc8 = ne_optionsHelper_get('newslist_items_per_page');

$article_image_display_4c5c429147dc8 = ne_optionsHelper_get('article_image_display');
$headlines_filter_tags_4c5c429147dc8 = ne_optionsHelper_get('headlines_filter_tags');
$headlines_filter_number_4c5c429147dc8 = ne_optionsHelper_get('headlines_filter_number');

$news_4c5c429147dc8 = new ne_NewsItems();
$news_4c5c429147dc8->find();

include dirname(__FILE__) . '/views/newsAndEvents/ajaxEditorButtonDialog.phtml';


?>
