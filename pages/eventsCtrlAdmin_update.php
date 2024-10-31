<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

		//Check the security nonce to make sure this is OK
		check_admin_referer('eventEditForm');
		
		//Get the post data passed in from the form
		$post_data = $_POST['eventEditForm'];
		
		//Create a new instance of an event
		$event = new ne_Event();

		//Prepare the image text field for uploading by WordPress
		$event->fileArr = ne_newsAndEventsHelper_fileArray('eventEditForm', 'image');
		
		//Load the existing data from the database
		$event->load($post_data['id']);
		
		//Associate the post data with the event and run Validator functions
		$event->data = $post_data;
		$event->post_tags = $post_data['tags'];

		//If the 'Published' checkbox isn't checked set it to zero
		if(!isset($_POST['eventEditForm']['published'])) {
			$event->published = 0; 
		}
//echo '<pre>';
//print_r($newsItem);
		//If there are form errors, save the data for redisplay
		if(ne_sessionHelper_formHasErrors()) {
			ne_sessionHelper_saveForm('eventEditForm', $event);
			ne_redirect("pages/eventsCtrlAdmin_edit.php");

		}
		

		if($event->save()) {
			$event->set_tags(preg_split('/[\s,]+/', $post_data['tags']));
			
			//This is causing errors due to the one-to-many error model 2010-April
			//ne_newsAndEventsHelper_removeUnusedTags();

			//Go back to the list page
			ne_redirect("pages/eventsCtrlAdmin_index.php");

		} else {
			echo $event->last_error;
		}



?>
