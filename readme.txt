=== Get All Comments Widget ===
Contributors: kanedo
Donate link: http://kanedo.net
Tags: comment, multi site, widget, display
Requires at least: 3.7
Tested up to: 3.9.1
Stable tag: 1.3

Creates a widget which lists all comments across all sites of a multi site wordpress installation

== Description ==
This widget displays **all** comments on **all** blogs on your multi site wordpress installation. You can define how many comments should be displayed.

== Installation ==
1. Upload "getRecentCommentsFromAllSites.php"
2. Activate Plugin through the 'Plugins' Menu in Wordpress
3. Place the Widget trough the 'Widgets' Menu in Wordpress
4. Enjoy!

== Changelog ==

= 1.3 =
released 2014-07-12
use wp_get_sites instead of custom SQL (requires at least wordpress 3.7)
refactor class structure for better readability
fixed warning due to deprecated use of wpdb::prepare
fixed warning due to use of deprecated function parameters in load_plugin_textdomain

= 1.2 =
Added localization for german language

= 1.2.1 =
Fixed the path to language files