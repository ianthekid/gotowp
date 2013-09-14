=== GoToWP ===
Contributors: brandonmuth,pankajagrawal
Tags: GoToWebinar, webinar registration, webinars, GoToMeeting wordpress plugin
Requires at least 3.2
Tested Up To  :3.4.2
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

GoToWP is a Wordpress plugin that allows users to register for your GoToWebinar webinars from any Wordpress post or page. 

== Description ==

Our plugin integrates with GoToWebinar's API to automatically register your customers for upcoming webinars and send them a confirmation email specifying the details (time, link, description, etc) of your webinar! You can even redirect to individual thank you pages for each webinar.

== Installation ==

You may directly upload the zip folder from admin or place the extracted files in wp-content/plugins directory

== Frequently Asked Questions ==

= How to use shortcode =

You may use a shortcode like this 
[register_free_webinar webid=7214273268860215552 pageid=14]

where 
register_free_webinar - shortcode for registration form to appear on page or post
webid                  - webinar registration id for example if your registration URL is https://attendee.gotowebinar.com/register/7214273268860215552
		         then webid will be 7214273268860215552
pageid                 - thank you page id for webinar 


= Admin Settings =
For setting admin Panel options go to wp-admin->Settings->GoToWebinar or Directly from Admin Menu

= Webinar Details =
Enter The Organizer Key  and Access Token  which can be obtained from online app here 
http://citrixonline-quick-oauth.herokuapp.com/   then choose the G2W OAuth Flow login to your account and allow the app to access details of your account


== Screenshots ==

1. screenshot1
2. screenshot2
3. screenshot3

== Support ==
For any assistance please visit http://wordpress.org/support/plugin/gotowp

== Changelog ==

== Upgrade Notice ==