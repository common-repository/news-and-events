<?php
	//If the uninstall is not being called from WordPress then exit
	if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit();
	}
	
    // delete the news and events tables from the database
    global $wpdb;
    $wpdb->query('DROP TABLE IF EXISTS ne_events');
    $wpdb->query('DROP TABLE IF EXISTS ne_news');
    $wpdb->query('DROP TABLE IF EXISTS ne_options');
    $wpdb->query('DROP TABLE IF EXISTS ne_tag_relationships');
    $wpdb->query('DROP TABLE IF EXISTS ne_tags');
 	
?>