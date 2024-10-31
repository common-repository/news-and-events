<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

//Check the nonce
check_admin_referer('newsitemEditForm');

$post_data = $_POST['newsitemEditForm'];
$newsItem = new ne_NewsItem();
$newsItem->load($post_data['id']);

$newsItem->data = $post_data;

//This should stay in the controller rather than in the onSave event in the model because this code is specific to the form that was submitted ('newsitemEditForm')
if(!isset($_POST['newsitemEditForm']['published'])) {
	$newsItem->published = 0; //If the checkbox was unchecked, set the value to 0
}

if(ne_sessionHelper_formHasErrors()) {
	ne_sessionHelper_saveForm("newsitemEditForm", $newsItem);
	ne_redirect("pages/newsCtrlAdmin_edit.php");

}

if($_POST['newsitemEditForm']['remove_image'] == 'on') {
	$newsItem->image = "";
	$newsItem->thumbnail = "";
}

$newsItem->fileArr = ne_newsAndEventsHelper_fileArray('newsitemEditForm', 'image');

if($newsItem->save()) {
	$newsItem->set_tags(preg_split('/[\s,]+/', $post_data['tags']));

	ne_redirect("pages/newsCtrlAdmin_index.php");

}
else {
	echo $newsItem->last_error;
}


?>
