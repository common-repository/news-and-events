<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

//Check the security nonce to make sure this is OK
check_admin_referer('eventAddForm');

//Create a new instance of an event
$event = new ne_Event();

//Get the post data passed in from the form
$post_data = $_POST['eventAddForm'];

//Prepare the image text field for uploading by WordPress
$event->fileArr = ne_newsAndEventsHelper_fileArray('eventAddForm', 'image');

//Associate the post data with the event
$event->data = $post_data;
$event->post_tags = $post_data['tags'];

//If the 'Published' checkbox isn't checked set it to zero
if(!isset($_POST['eventAddForm']['published'])) {
	$event->published = 0; 
}

//If there are form errors, save the data for redisplay
if(ne_sessionHelper_formHasErrors()) {
	ne_sessionHelper_saveForm("eventAddForm", $event);
	ne_redirect("pages/eventsCtrlAdmin_add.php");

}

if($event->save()) {
	$event->set_tags(preg_split('/[\s,]+/', $post_data['tags']));

	ne_redirect("pages/eventsCtrlAdmin_index.php");

}
else {
	echo $event->last_error;
}


?>
