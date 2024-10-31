<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$settings_4c5c4291484df = ne_optionsHelper_mergeOptions(ne_Request::$args, 'newslist');

$news_4c5c429147dc8 = new ne_NewsItems();
$news_4c5c429147dc8->show_unpublished = $settings_4c5c4291484df['newslist_show_unpublished'];
$news_4c5c429147dc8->filter_tags = $settings_4c5c4291484df['newslist_filter_tags'];
$news_4c5c429147dc8->items_per_page = $settings_4c5c4291484df['newslist_items_per_page'];
$news_4c5c429147dc8->sortby = $settings_4c5c4291484df['newslist_sort']; //How to sort (e.g. by date or by headline)
$news_4c5c429147dc8->current_page = $_GET['page'] or $news_4c5c429147dc8->current_page = 1;

$news_4c5c429147dc8->execute();

//Calculate paging
//Get the current page
$pageNumber_4c5c4291484df = $news_4c5c429147dc8->current_page;

//If the curren page is anything other than 1 then there is a page below
if($news_4c5c429147dc8->current_page != 1) {
	$pageBelow_4c5c4291484df = true;
}
else {
	$pageBelow_4c5c4291484df = false;
}

//Get the pageAbove status from the model
$pageAbove_4c5c4291484df = $news_4c5c429147dc8->page_above;

include 'views/newsCtrl/index.phtml';

?>
