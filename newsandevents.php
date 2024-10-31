<?php
/**
 * Plugin Name: News & Events
 * Plugin URI: http://knowledgetown.com/newsandevents
 * Description: Manage your News & Events
 * Author: The KnowledgeTown Crew
 * Author URI: http://knowledgetown.com
 * Version: 1.1
 */

require_once 'ne_includes.php';



//Set a special constant to indicate that no default was set in the short code
//This value lets the controller know to fill in the default
if(!defined('NE_FIND_DEFAULT')) {
	define('NE_FIND_DEFAULT', -1);
}

if(!defined('NE_PLUGIN')) define('NE_PLUGIN', 1);
if(!defined('NE_PLUGLET')) define('NE_PLUGLET', 2);

if(!defined('NE_SPACE')) {
	echo strpos('pluglets', __FILE__);
	if(false === strpos(__FILE__, 'pluglets')) {
		define('NE_SPACE', NE_PLUGIN);
	}
	else {
		define('NE_SPACE', NE_PLUGLET);
	}
}

//Get the directory and url of the plugin for later use
$ne_plugin_dir = trailingslashit(WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));
$ne_plugin_url = trailingslashit(plugins_url(dirname(plugin_basename(__FILE__))));
$ne_folder_name = dirname(plugin_basename(__FILE__));

/**
 * @brief Add a NE (news and events) button to TinyMCE.
 */
if(!function_exists('ne_addTinyButton')) {
	function ne_addTinyButton() {
		//If the user doesn't have permissions to ever look at a WP editor, don't bother setting all this up
		if( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

		//Make sure rich editing is enabled, otherwise the user will never see the WP RTF editor (where our button goes)
		if( get_user_option('rich_editing') == 'true' ) {
			add_filter('mce_external_plugins', 'ne_addTinyPlugin');
			add_filter('mce_buttons', 'ne_registerTinyButton');
		}
	}
}

if(!function_exists('ne_addTinyPlugin')) {
	function ne_addTinyPlugin($plugin_array) {
		//Register our TinyMCE plugin with TinyMCE. We have two different versions of this file, depending on where NE is running (pluglet vs plugin)
		$src = '';

		if(NE_SPACE == NE_PLUGLET) {
			$src = plugins_url('ktframework/pluglets/newsandevents/js/ne_tiny.js');
		}
		else if(NE_SPACE == NE_PLUGIN) {
			global $ne_plugin_url;
			$src = $ne_plugin_url . 'assets/js_ne_tiny_plugin.js';
		}
		
		//Register our TinyMCE plugin with TinyMCE.
		$plugin_array['NEPlugin'] = $src;

		return $plugin_array;
	}
}


if(!function_exists('ne_registerTinyButton')) {
	function ne_registerTinyButton($buttons) {
		//Puts out button into the TinyMCE button list.
		array_push($buttons, "sepasrator", "newsandevents");
		return $buttons;
	}

	add_action('init', 'ne_addTinyButton', 5);
}


/**
 * @brief Handle WordPress short codes for news
 * 
 * When WordPress sees [news *] on a page, it will call this function This converts
 * the tag=value short code pairs into an appropriate request to pluglet/plugin
 */
if(!function_exists('news_shortcode')) {
	function news_shortcode($atts) {
		if(NE_SPACE == NE_PLUGLET) {
			//Initialize state of the union so that we can access Options model
			KTF_Request::set('newsandevents', 'newsCtrl', 'list'); //This doesn't actually matter, it will be redefined in the front controller. We set it here so that we can initialize SOTU early
			ktf_sotu(null, true);
			
			$options = Options::$optionsDef;
		}
		else {
			$options = ne_Options::$optionsDef;
		}
		
		//Construct the list of short tags that can be used
		$shortcodes = array('view' => 'list', 'id' => 0);
		
		//Add in all the defined options and settings
		foreach($options as $option) {
			//Set a generic default value for each possible tag/code
			if(isset($option['short_tag'])) {
				$shortcodes[$option['short_tag']] = NE_FIND_DEFAULT;
			}
			else {
				//If a short tag isn't explicitly set, make an educated guess
				if(false !== strpos($option, 'show')) {
					if(NE_SPACE == NE_PLUGLET) {
						$shortcodes[$option['short_tag']] = optionsHelper::guessShortTag($option);
					}
					else {
						$shortcodes[$option['short_tag']] = ne_optionsHelper_guessShortTag($option);
					}
				}
			}
		}

		//Override the default for any shortcodes that were set on the page
		$params = shortcode_atts($shortcodes, $atts);
		
		//Special case:  If the view is 'list' call the 'index' function
		if($params['view'] == 'list') {
			$params['view'] = 'index';
		}
		
		//The first part of the request has to have a path to the controller and function name
		$request = "newsandevents/newsCtrl/" . $params['view'];

		//Check to see if the action (function) exists

		$actionExists = true;

		if(NE_SPACE == NE_PLUGLET) {
			$actionExists = ktf_sotu()->actionExists($request);
		}
		else {
			//TODO: Check if action exists on exported version
		}

		if($actionExists) {
			//Start buffering the output to capture the framework/plugin/pluglet output
			ob_start();
			
			if(NE_SPACE == NE_PLUGLET) {
				//This is operating as a pluglet within the Knowledge Town Framework
				foreach($params as $tag => $value) {
					$request .= "/$tag=" . $value;
				}

				ktframework($request);
			}
			else {
				//This is operating as a regular WordPress plugin
				$ne_args = $params;
				
				ne_Request::set('newsandevents', 'newsCtrl', $params['view']);
				ne_Request::$args = $ne_args;

				include "pages/newsCtrl_" . $params['view'] . ".php";
			}

			//Read the buffered output into a variable to return to WordPress
			$ret = ob_get_contents();
			
			//Clean up the output buffer
			ob_end_clean();
			
			return $ret;
		}
		else {
			//The requested action/function doesn't exist
			if(NE_SPACE == NE_PLUGLET) {
				ktframework("newsandevents/newsAndEvents/error_404");
			}
			else {
				include "pages/newsAndEvents_error_404.php";
			}
		}
	}

	add_shortcode('news', 'news_shortcode');
}

/**
 * @brief Handle WordPress short codes for events
 * 
 * When WordPress sees [events *] on a page, it will call this function.  This converts
 * the tag=value short code pairs into an appropriate request to pluglet/plugin
 */
if(!function_exists('events_shortcode')) {
	function events_shortcode($atts) {
		if(NE_SPACE == NE_PLUGLET) {
			//Initialize state of the union so that we can access Options model
			KTF_Request::set('newsandevents', 'eventsCtrl', 'listView'); //This doesn't actually matter, it will be redefined in the front controller. We set it here so that we can initialize SOTU early
			$options = Options::$optionsDef;
		}
		else {
			$options = ne_Options::$optionsDef;
		}
		
		//Construct the list of short tags that can be used
		//There must be a tag for view that determines which controller function to call
		$shortcodes = array('view' => 'listView', 'id' => 0);
		
		//Add in all the defined options and settings
		foreach($options as $option) {
			//Set a generic default value for each possible tag/code
			if(isset($option['short_tag'])) {
				$shortcodes[$option['short_tag']] = NE_FIND_DEFAULT;
			}
			//else {
				//If a short tag isn't explicitly set, make an educated guess
				//if(false !== strpos($option, 'show')) {
					//$shortcodes[$option['short_tag']] = optionsHelper::guessShortTag($option);
				//}
			//}
		}
			
		//Override the default for any shortcodes that were set on the page
		$params = shortcode_atts($shortcodes, $atts);
		
		//The first part of the request has to have a path to the controller and function name
		$request = "newsandevents/eventsCtrl/" . $params['view'];

		$actionExists = true;
		if(NE_SPACE == NE_PLUGLET) {
			$actionExists = ktf_sotu()->actionExists($request);
		}
		else {
			//TODO
		}

		if($actionExists) {
			//Start buffering the output to capture the framework/plugin/pluglet output
			ob_start();
			
			if(NE_SPACE == NE_PLUGLET) {
				//This is operating as a pluglet within the Knowledge Town Framework
				foreach($params as $tag => $value) {
					$request .= "/$tag=" . $value;
				}

				ktframework($request);
			}
			else {
				//This is operating as a regular WordPress plugin
				$ne_args = $params;
				
				//
				ne_Request::set('newsandevents', 'eventsCtrl', $params['view']);

				include "pages/eventsCtrl_" . $params['view'] . ".php";
			}
			
			//Read the buffered output into a variable to return to WordPress
			$ret = ob_get_contents();

			//Clean up the output buffer
			ob_end_clean();
			return $ret;
		}
		else {
			//The action/function doesn't exist
			if(NE_SPACE == NE_PLUGLET) {
				ktframework("newsandevents/newsAndEvents/error_404");
			}
			else {
				include "pages/newsAndEvents_error_404.php";
			}
		}
	}

	add_shortcode('events', 'events_shortcode');
}


if(!function_exists('ne_public_stylesheet')) {
	function ne_public_stylesheet() {
		if(!is_admin()) {
			if(NE_SPACE == NE_PLUGLET) {
				$url = WP_PLUGIN_URL . '/ktframework/pluglets/newsandevents/css/newsandevents.css';
				
				$customRelative = '/ktframework/pluglets/newsandevents/css/newsandevents-custom.css';
			}
			else {
				$pluginName = str_replace(dirname(dirname(__FILE__)), '', dirname(__FILE__));

				$url = WP_PLUGIN_URL . '/ '. $pluginName . '/assets/css_newsandevents.css';
				$customRelative = $pluginName . '/assets/css_newsandevents-custom.css';
			}

			wp_enqueue_style('ne_public_stylesheet', $url);

			if(file_exists(ABSPATH . 'wp-content/plugins/' . $customRelative)) {
				$customurl = WP_PLUGIN_URL . '/' . $customRelative;
				wp_enqueue_style('ne_custom_stylesheet', $customurl);
			}	
		}
	}

	add_action('wp_print_styles', 'ne_public_stylesheet');
}




function ne_adminMenu() {
	global $ne_folder_name;
	
	list($plugin) = explode('/', $_GET['page']);
	if($plugin == $ne_folder_name) {
		global $_registered_pages;
		$_registered_pages[get_plugin_page_hookname($_GET['page'], '')] = true;
	}
	
	$parent = $ne_folder_name . '/pages/newsCtrlAdmin_index.php';
	
	//Add the admin menu group and the three menu items
	add_menu_page('News & Events', 'News & Events', 0, $parent);
	add_submenu_page($parent, 'News', 'News', 0, $ne_folder_name . '/pages/newsCtrlAdmin_index.php');
	add_submenu_page($parent, 'Events', 'Events', 0, $ne_folder_name . '/pages/eventsCtrlAdmin_index.php');
	add_submenu_page($parent, 'Settings', 'Settings', 0, $ne_folder_name . '/pages/optionsCtrlAdmin_index.php');
}

add_action('admin_menu', 'ne_adminMenu');

function ne_adminInit() {
	wp_enqueue_script( array("jquery", "jquery-ui-core", "interface", "jquery-ui-sortable", "wp-lists", "jquery-ui-sortable") );
	ob_start();
}

add_action('admin_init', 'ne_adminInit');

function ne_adminFooter() {
	ob_end_flush();
}

add_action('admin_footer', 'ne_adminFooter');

function ne_adminHeader() {
	$request = $_GET['page'];
	list($pluglet, , $page) = explode('/', $request);

	if($page == 'newsAndEvents_ajaxEditorButtonDialog.php') {
		ne_includeAssets(array(), array('_home_herb_Web_knowledgetown_ktfdev_wp-content_plugins_ktframework_pluglets_js_dialogScript.js'), array(), array());
	}
	if($page == 'newsCtrlAdmin_index.php') {
		ne_includeAssets(array('css_newsandevents.css'), array(), array(), array('views_newsCtrlAdmin_index.pjs'));
	}
	if($page == 'newsCtrlAdmin_add.php') {
		ne_includeAssets(array(), array('js_form.js'), array(), array());
	}
	if($page == 'newsCtrlAdmin_edit.php') {
		ne_includeAssets(array(), array('js_form.js'), array(), array());
	}
	if($page == 'newsCtrlAdmin_previewHeadlines.php') {
		ne_includeAssets(array(), array(), array(), array('views_newsCtrlAdmin_previewHeadlines.pjs'));
	}
	if($page == 'eventsCtrlAdmin_index.php') {
		ne_includeAssets(array('css_newsandevents.css'), array(), array(), array('views_eventsCtrlAdmin_index.pjs'));
	}
	if($page == 'eventsCtrlAdmin_add.php') {
		ne_includeAssets(array(), array('js_form.js'), array(), array());
	}
	if($page == 'eventsCtrlAdmin_edit.php') {
		ne_includeAssets(array(), array('js_form.js'), array(), array());
	}
	if($page == 'optionsCtrlAdmin_index.php') {
		ne_includeAssets(array(), array('views_optionsCtrlAdmin_index.js'), array(), array());
	}

}

add_action('admin_head', 'ne_adminHeader');

function ne_includeAssets($cssArr, $jsArr, $pcssArr, $pjsArr) {
	//$url = get_bloginfo('wpurl');
	//$url = substr($url, -1) == '/' ? $url : $url . '/'; //Make sure there is a slash at the end

	//$assetPath = $url . 'wp-content/plugins/newsandevents/assets/';
	
	global $ne_plugin_url;
	$assetPath = $ne_plugin_url . 'assets/';
	
	foreach($cssArr as $css) {
		echo "<link rel='stylesheet' href='$assetPath$css' />\n";
	}

	foreach($jsArr as $js) {
		echo "<script src='$assetPath$js' type='text/javascript'></script>\n";
	}

	foreach($pcssArr as $pcss) {
		echo "<style type='text/css'>\n";
		include("assets/$pcss");
		echo "</style>";
	}

	foreach($pjsArr as $pjs) {
		echo "<script type='text/javascript'>\n";
		include("assets/$pjs");
		echo "</script>";
	}
}

add_action('admin_head', 'ne_adminHeader');

function ne_filter($content = '') {
	if($_GET['ktf'] != '') {
		//Request should be in the following form: ?ktf=ne_$controller_$action

		$prefixLen = strlen('ne_');

		//If it isn't KTF content
		if(substr($_GET['ktf'], 0, $prefixLen) !== 'ne_') return;
		
		//If it is an admin page, don't let it be embedded
		if(substr($_GET['ktf'], -5) == 'Admin') return;

		$pageRequest = substr($_GET['ktf'], $prefixLen);
		list($controller, $action) = explode('_', $pageRequest);

		ne_Request::$namespace = 'News & Events';
		ne_Request::$controller = $controller;
		ne_Request::$action = $action;
		
		//Get the array of expected pages that could be included
		global $ne_pages;
		
		//Assemble the index for the array of expected pages
		$page_index = $controller . '_' . $action;
		
		//Try to get the page to be included
		$page = $ne_pages[$page_index];
		
		//Check to see if the requested page was one of the ones expected
		if (strlen($page) > 0) {
			ob_start();
			//Include the page/function/view with the requested content
			include 'pages/' . $page;
			$content = ob_get_clean();
		}
	}

	return $content;
}

add_action('the_content', 'ne_filter');

function ne_posts($posts) { 
	if(isset($_GET['ktf'])) {
		return array(array());
	}

	return $posts;
}

add_action('the_posts', 'ne_posts');

function ne_add_wysiwyg() {
	// activate these includes if the user chooses tinyMCE on the settings page
	echo '<script type="text/javascript" src="../wp-includes/js/tinymce/tiny_mce.js"></script>';
	echo '<script type="text/javascript">
	<!--
	tinyMCE.init({
		theme : "advanced",
		mode : "none",
		width : "565",
		height : "200",
		theme_advanced_toolbar_align : "left",
		theme_advanced_toolbar_location : "top",
		skin: "default",
		theme_advanced_buttons1: "bold, italic, underline, seperator, bullist, numlist, seperator, forecolor, backcolor, seperator, undo, redo",
		theme_advanced_buttons2: "", 
		theme_advanced_buttons3: ""
	});
	-->
	</script>';
}

add_action('admin_head', 'ne_add_wysiwyg');


?>
