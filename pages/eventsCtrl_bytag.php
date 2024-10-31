<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

		$tag = ne_Request::$args['tag'];
		
		//Get the option values from the options/settings table but override 
		//them with any options set in the short code that called for this view
		$settings_4c5c4291484df = ne_optionsHelper_mergeOptions(ne_Request::$args, 'eventList');
		
		$events_4c5c42914e22d = new ne_Events();
		$events_4c5c42914e22d->show_unpublished = $settings_4c5c4291484df['eventList_show_unpublished'];
		$events_4c5c42914e22d->items_per_page = $settings_4c5c4291484df['eventList_items_per_page'];
		$events_4c5c42914e22d->filter_tags = $tag;
		
		if(strlen($_GET['page']) > 0 ) {
			$events_4c5c42914e22d->current_page = $_GET['page'];
		}
		$events_4c5c42914e22d->where = "ne_tag_relationships.type = 'event'
					AND ne_tags.name = '$tag'";


		$events_4c5c42914e22d->execute();

		//Calculate the Paging
		//Get the current page 
		$pageNumber_4c5c4291484df = $events_4c5c42914e22d->current_page;
		
		//If the current page is anything other than 1 then there is a page below
		if($events_4c5c42914e22d->current_page != 1) {
			$pageBelow_4c5c4291484df = true;
		} else {
			$pageBelow_4c5c4291484df = false;
		} 
		
		//Get the pageAbove status from the model
		$pageAbove_4c5c4291484df = $events_4c5c42914e22d->page_above;
		
		//Calculate number of pages
		//$totalPages = ceil(record_count/records_perpage);

		//This should really have its own phtml with a tag header or an option to show
			//the tag in the listView.phtml
		include dirname(__FILE__) . '/views/eventsCtrl/listView.phtml';


?>
