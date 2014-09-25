=== GoToWP ===
Contributors: brandonmuth,pankajagrawal
Donate link: http://gotowp.com/
Tags: GoToWebinar, Gototraining, Training registration,webinar registration, webinars, trainings, GoToMeeting wordpress plugin,Gototraining wordpress plugin
Requires at least: 3.2
Tested up to: 4.0
Stable tag: 1.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

GoToWP is a Wordpress plugin that allows users to register for your GoToWebinar webinars and/or your GoToTraining trainings from any Wordpress post or page. 

== Description ==

Our plugin integrates with GoToWebinar's and GoToTraining's API to automatically register your customers for upcoming webinars or trainings and send them a confirmation email specifying the details (time, link, description, etc) of your webinar or training! You can even redirect to individual thank you pages for each webinar or training.

== Installation ==

You may directly upload the zip folder from admin or place the extracted files in wp-content/plugins directory

== Frequently Asked Questions ==

Please visit our support and user forum at http://www.gotowp.com/support

= How to use shortcode =

GOTOWEBINAR
You may use a shortcode like this 
[register_free_webinar webid=7214273268860215552 pageid=14]

where 
register_free_webinar - shortcode for registration form to appear on page or post
webid                  - webinar registration id for example if your registration URL is https://attendee.gotowebinar.com/register/7214273268860215552
           then webid will be 7214273268860215552
pageid                 - thank you page id for webinar 

GOTOTRAINING
You may use a shortcode like this 
[register_free_training id=7214273268860215552 pageid=14]

where 
register_free_training - shortcode for registration form to appear on page or post
id                  - training registration id for example if your registration URL is https://attendee.gototraining.com/register/7214273268860215552
           then webid will be 7214273268860215552
pageid                 - thank you page id for training 


= Admin Settings =
For setting admin Panel options go to wp-admin->GoToWP Personal (in left hand column, near bottom of menu)

= Webinar & Training Details =
Enter The Organizer Key and Access Token which can be obtained from our online app at http://app.gotowp.com/ 
Once there click the G2W OAuth Flow button (for GoToWebinar) or the G2T OAuth Flow (for GoToTraining), login to your account and allow the app to access details of your account. Citrix will then generate the necessary key and token on the screen.
Keys generated through app.gotowp.com are stable for 1 year

== Screenshots ==

1. screenshot1
2. screenshot2
3. screenshot3

== Changelog ==

= 1.1.3 =
* added new api "Now includes integration with GoToTraining as well as GoToWebinar"

= 1.1.2 =
* updated fixed "fix to form mirroring bug"

= 1.1.1 =
* updated fixed "fixed some code issue"

= 1.1.0 =
* updated fixed "update to plugin settings page display"

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
