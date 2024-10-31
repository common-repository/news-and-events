<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

for($i = 0; $i < 25; $i++) {
	$item = new ne_NewsItem();

	$item->headline = "Title $i";
	$item->author = "Author $i";
	$item->excerpt = "Excerpt $i";
	$item->date = rand(1, 12) . "/" . rand(1, 28) . "/" . rand(1990, 2015);
	$item->published = "on";
	$item->article = "Article $i";

	$item->save();
}

echo "Success!";


?>
