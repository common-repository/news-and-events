=== News & Events ===
Contributors: smohlke, lclarkberg
Donate: http://www.knowledgetown.com
Tags: news, events
Requires at least: 2.0.2
Tested up to: 3.0.1
Stable Tag: 1.0

This plugin allows you to manage news & events and display them on your Wordpress website.

== Description ==
This News & Events plugin is intended for organizations whose time-related information fits more into the category of news articles and event items rather than blog posts. An administrator can enter news articles and event items using an admin form. The administrator can then display the news and/or events on any page or post. The items can be displayed in three ways: as a short list of headlines and event titles; as a complete list of all news articles or event items (with pagination as necessary); or as a "detail page" of a single news article or event item. Plugin settings allow the administrator to control how many items and what type of information is shown for each view, and how long event items continue to be shown after their event date has passed.  

== Installation ==

1. Download the plugin from WordPress' website (http://wordpress.org/extend/plugins/news-and-events/). 

2. Install the plugin in Wordpress by going to Plugins -> Add New -> Upload, and uploading the zip file you got here.

3. Active "news-and-events" through the "Plugins" menu in Wordpress.

4. Click on "News & Events" in the left hand Wordpress navigation menu. The first time you click this button you will need to click "Install News & Events" to install the News & Events database tables

5. You will now see a form where you will be able to enter some news articles and event items. Add a few test items.

6. Next create a new page to list your news or event items, or choose an existing page to put them on. When you activate the News and Events plugin a "clocktower" button appears in the editing toolbar in the post and page editors. Use this button to insert the news or events displays: a short headlines view, a full archive view of all items, or a detail view of one item.

7. Don't like the view? Release your inner control freak by playing with the settings found in the News and Events section at the bottom of the admin menu.

== Changelog ==

= Version 1.1 =
* Fixed a security problem that could have allowed an attacker to view the contents of any system file
* Fixed bugs that were preventing events from displaying
* Fixed a bug that prevented the style sheet from being included
* Fixed a broken reference to an error file
* Removed the link to "Customize Headlines View" because the view isn't working properly and isn't a critical feature
* Fixed a bug that prevented null end dates for events
* Added an uninstall file so that news and events database tables are deleted when deleting the plugin

= Version 1.0 =
* Initial release

== Frequently Asked Questions ==

= Why can't I see the clock tower button? =
You may have the visual editor turned off.  Go to Users -> Your Profile and make sure the checkbox "Disable the visual editor when writing" is cleared.


== Upgrade Notice ==

= 1.1 =
This version fixes a security related bug as well as a few other functional bugs.  Upgrade immediately.