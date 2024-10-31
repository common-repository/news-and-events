<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

$settings_4c5c4291484df = ne_optionsHelper_mergeOptions(ne_Request::$args, 'headlines');

$news_4c5c429147dc8 = new ne_NewsItems();
$news_4c5c429147dc8->newer_than = $settings_4c5c4291484df['headlines_filter_date'];
$news_4c5c429147dc8->filter_tags = $settings_4c5c4291484df['headlines_filter_tags'];
$news_4c5c429147dc8->items_per_page = $settings_4c5c4291484df['headlines_filter_number'];
$news_4c5c429147dc8->current_page = 1;

$news_4c5c429147dc8->execute();

include 'views/newsCtrl/headlines.phtml';

?>
