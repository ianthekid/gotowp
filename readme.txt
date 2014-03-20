=== GoToWP ===
Contributors: brandonmuth,pankajagrawal
Donate link: http://gotowp.com/
Tags: GoToWebinar, webinar registration, webinars, GoToMeeting wordpress plugin
Requires at least: 3.2
Tested up to: 3.8.1
Stable tag: 1.0.9
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
[register_free_webinar webid=xxxxxxxxxxx pageid=14]

where 
register_free_webinar - shortcode for registration form to appear on page or post
webid                  - webinar registration id for example if your registration URL is https://attendee.gotowebinar.com/register/xxxxxxxxxxxxxx
		         then webid will be xxxxxxxxxxxxxxxx
pageid                 - thank you page id for webinar 


= Admin Settings =
For setting admin Panel options go to wp-admin->Settings->GoToWebinar or Directly from Admin Menu

= Webinar Details =
Enter The Organizer Key and Access Token which can be obtained from our online app at http://app.gotowp.com/ 
Once there click the G2W OAuth Flow button, login to your account and allow the app to access details of your account. Citrix will then generate the necessary key and token on the screen.
Keys generated through app.gotowp.com are stable for 1 year

== Screenshots ==

1. screenshot1
2. screenshot2
3. screenshot3

== Changelog ==

= 1.0.9 =
* updated fixed "date format issue"

= 1.0.8 =
* updated fixed "date format issue"

= 1.0.5 =
* updated form to mirror the form in GoToWebinar settings for each webinar

= 1.0 =
* A change since the previous version.
* Another change.




== Upgrade Notice ==

= 1.0 =
Upgrade notices describe the reason a user should upgrade.  No more than 300 characters.
