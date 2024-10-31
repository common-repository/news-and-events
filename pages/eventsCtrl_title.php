<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';

		
		//Get the option values from the options/settings table but override 
		//them with any options set in the short code that called for this view
		$settings_4c5c4291484df = ne_optionsHelper_mergeOptions(ne_Request::$args, 'eventTitle');
		
		$events_4c5c42914e22d = new ne_Events();
		//Filter by date
		$events_4c5c42914e22d->newer_than = $settings_4c5c4291484df['eventTitle_filter_date'];
		//Filter by tag
		$events_4c5c42914e22d->filter_tags = $settings_4c5c4291484df['eventTitle_filter_tags'];
		//Set number of items to display
		$events_4c5c42914e22d->items_per_page = $settings_4c5c4291484df['eventTitle_filter_number'];
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


include 'views/eventsCtrl/title.phtml';

?>
