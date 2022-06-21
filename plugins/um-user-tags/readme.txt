=== Ultimate Member - User Tags ===
Author URI: https://ultimatemember.com
Plugin URI: https://ultimatemember.com/extensions/user-tags/
Contributors: nsinelnikov
Tags: user tags, user-profile, user-registration
Requires at least: 5.0
Tested up to: 5.8
Stable tag: 2.1.8
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.0

Add a user tag system to your site and allow users to associate tags with their profile and enable users to find other users easily with the same tag.

== Description ==

Add a user tag system to your site and allow users to associate tags with their profile and enable users to find other users easily with the same tag.

= Features of the plugin include: =

* Admin can create user tags which users can select when registering or on their profile
* Tags can be separated into groups using parent/child tags
* Tags can be given an optional description which shows on front-end when the tag is hovered over.
* Tags can be displayed on profile form in main profile area or in profile header area
* Allows users to see all users on the member directory when they click on the tag e.g clicking on a tag called “HTML” will redirect the user to the directory page and display all users who have selected the HTML tag
* Widget that shows most popular tags (includes count next to each tag to show how many users have that tag)

Read about all of the plugin's features at [Ultimate Member - User Tags](https://ultimatemember.com/extensions/user-tags/)

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](https://docs.ultimatemember.com/article/186-user-tags-setup) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/ultimate-member).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > User Tags to customize plugin options
3. For more details, please visit the official [Documentation](https://docs.ultimatemember.com/article/186-user-tags-setup) page.

== Changelog ==

= Important: 2.0.9+ version is compatible with Ultimate Member 2.0.56+ version =

= 2.1.8: July 20, 2021 =

* Added: `ultimatemember_tags_calculate_count` filter for customizing user tags calculation
* Fixed: Compatibility with the Beaver Builder
* Tweak: WP5.8 widgets screen compatibility

= 2.1.7: December 24, 2020 =

* Fixed: Conditional logic for the User Tags field

= 2.1.6: December 8, 2020 =

* Fixed: WPML compatibility
* Fixed: User Tags field value when validation on form submission is triggered
* Fixed: Conditional logic for User Tags field type
* Fixed: User Tag's page if the members page has parent page
* Fixed: Restore new user tags after form validation fails

= 2.1.5: August 11, 2020 =

* Added: Translation file .pot
* Added: Translation for locales de_CH and de_DE
* Fixed: WPML integration
* Fixed: Members page content
* Fixed: Integration with UM metadata table
* Fixed: Restore new user tags after form validation fails
* Fixed: PHP notice on getting empty tags
* Tweak: apply_shortcodes() function support

= 2.1.4: February 25, 2020 =

* Tweak: Compatibility with 2.1.4 member directories JS
* Fixed: User Tags-type field validation and visibility
* Fixed: Using keys in User Tags-type field
* Fixed: Trimming options in User Tags filter

= 2.1.3: January 13, 2020 =

* Tweak: Integration with Ultimate Member 2.1.3 and UM metadata table
* Fixed: Additional slashes in individual tag page title
* Fixed: Ignore show after search option at the individual tag page
* Fixed: "Can add tags" capability for user roles
* Fixed: Updating count of the user tags after profile updating
* Fixed: Changed re-init select2 if "Can add tags" is turned on
* Fixed: Don't show the user tags links if base directory isn't selected

= 2.1.2: December 9, 2019 =

* Fixed: User Tags filters displaying on member directories
* Fixed: Notice on update profile

= 2.1.1: November 13, 2019 =

* Fixed: User Tags links from widget
* Fixed: WPML integration with new user tags links

= 2.1.0: November 11, 2019 =

* Added: Field to enable user added tags
* Added: New user tags permalinks and settings for integration with member directories
* Tweak: Integration with Ultimate Member 2.1.0

= 2.0.9: August 21, 2019 =

* Fixed: Integration with UM core and fields without metakeys in different places

= 2.0.8: May 2, 2019 =

* Fixed: Search by User Tags

= 2.0.7: February 8, 2019 =

* Fixed: Small CSS issues

= 2.0.6: October 19, 2018 =

* Fixed: CSS enqueue

= 2.0.5: October 15, 2018 =

* Fixed: WPML user tags compatibility
* Optimized: JS/CSS enqueue

= 2.0.4: August 9, 2018 =

* Fixed: Search in members directory

= 2.0.3: July 6, 2018 =

* Fixed: Search in members directory by User Tag

= 2.0.2: May 22, 2018 =

* Added: GDPR compatibility on delete users
* Fixed: Translations of the tags titles

= 2.0.1: May 22, 2018 =

* Added: Loading translation from "wp-content/languages/plugins/" directory
* Fixed: User Tags count displaying and order in widget
* Fixed: re-count user tags on user delete

= 2.0: April 1, 2018 =

* Added: UM2.0 compatibility
* Fixed: tags structure fixes

= 1.0.2: January 6, 2016 =

* Fixed: Fixed undefined function in admin

= 1.0.1: December 15, 2015 =

* Fixed: Popular user tags count in widget