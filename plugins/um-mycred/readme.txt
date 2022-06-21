=== Ultimate Member - myCRED ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/mycred/
Contributors: ultimatemember, champsupertramp, nsinelnikov
Donate link:
Tags: myCRED, badges, points, user, community
Requires at least: 5.0
Tested up to: 5.7
Stable tag: 2.2.1
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.4

With our myCRED extension, reward or charge your users for using Ultimate Member features and doing profile updates and show their rank and badges beautifully in their user profile.

== Description ==

myCRED is an adaptive points management system for WordPress powered websites, giving you full control on how points are gained, used, traded, managed, logged or presented.

= Key Features: =

* Reward users for updating profile photo
* Reward users for updating profile
* Reward users for signing up or logging to your site
* Charge users for removing profile photo
* Limit the number of rewards/charges on specific action
* Credits and charges made via Ultimate Member are properly logged in myCRED
* Allow specific user roles to send balance to other users from UM account page (Transfer points to other members)
* Display user balance in their account page
* Display user balance, badges, or rank on their profile
* Display user progress bar beautifully on their profile
* Show all myCRED badges in profile tab (optional)

= Development * Translations =

Want to add a new language to Ultimate Member? Great! You can contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/ultimate-member).

If you are a developer and you need to know the list of UM Hooks, make this via our [Hooks Documentation](https://docs.ultimatemember.com/article/1324-hooks-list).

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/um-forumwp).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > Private Messaging to customize plugin options
3. For more details, please visit the official [Documentation](http://docs.ultimatemember.com/article/221-ultimate-member-mycred-extension) page.

== Changelog ==

= 2.2.1: March 11, 2021 =

* Fixed: Calculation of the rank progress

= 2.2.0: December 10, 2020 =

* Tweak: WordPress 5.6 compatibility (fixed issue with deprecated .load() function in JS)

= 2.1.9: August 11, 2020 =

* Added: Translation file *.pot
* Tweak: apply_shortcodes() function support
* Fixed: saving mycred_point_types when saving another setting tab

= 2.1.8: April 1, 2020 =

* Added: Wrappers for points in labels
* Added: Multi point types supporting
* Changed: Hooks' template wrapper
* Fixed: Transferring points without separator

= 2.1.7: January 13, 2020 =

* Tweak: Integration with Ultimate Member 2.1.3 and UM metadata table
* Added: Support different decimal separator from myCRED settings
* Added: Ability to change labels via points title
* Fixed: Sorting by points in Member Directory
* Fixed: Decimal transfers
* Fixed: Award points when use Member Directory search + filters
* Fixed: Award points hook on update user profile
* Fixed: myCRED badges displaying in tagline
* Fixed: Slider's range

= 2.1.6: November 11, 2019 =

* Tweak: Compatibility with 2.1.0 UM core
* Added: Sanitize functions for request variables
* Added: esc_attr functions to avoid XSS vulnerabilities
* Added: ability to change templates in theme via universal method UM()->get_template()

= 2.1.5: July 19, 2019 =

* Fixed: References for assign badges for the user

= 2.1.4: June 16, 2019 =

* New: added ability to show user badges in Member directory
* Fixed: Profile Tabs

= 2.1.3: February 8, 2019 =

* Fixed: Badges tab displaying

= 2.1.2: December 6, 2018 =

* Fixed: Translations for Badges template

= 2.1.1: November 12, 2018 =

* Fixed: myCRED field profile completeness

= 2.1.0: November 1, 2018 =

* Fixed: Transferring points in account page
* Optimized: JS/CSS enqueue

= 2.0.3: July 17, 2018 =

* Fixed: Removed duplicate award/deduct points on myCRED hooks actions

= 2.0.2: July 10, 2018 =

* Added: Loading translation from "wp-content/languages/plugins/" directory

= 2.0.1: July 10, 2018 =

* Fixed: badges on user profile

= 2.0: October 17, 2017 =

* Tweak: UM2.0 compatibility

= 1.2.3: July 26, 2017 =

* Fixed: badges and levels
* Fixed: translation strings
* Fixed: Remove notices
* Fixed: myCRED compatibility issue
* Tweak: Update EDD plugin updater class

= 1.2.2: February 7, 2016 =

* New: Order user by mycred points
* Tweak: Update EDD_SL_Plugin_Updater.php

= 1.2.1: December 8, 2015 =

* Initial release