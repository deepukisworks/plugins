=== Ultimate Member - User Bookmarks ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/user-bookmarks/
Contributors: ultimatemember, champsupertramp, nsinelnikov
Donate link:
Tags: private messaging, email, user, community
Requires at least: 5.0
Tested up to: 5.7
Stable tag: 2.0.7
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.0

Allow users to bookmark content from your website.

== Description ==

Allow users to bookmark content from your website.

= Key Features: =

* Select which content bookmarks show on e.g pages, posts, CPTs
* Disable bookmarking for individual page/posts
* Bookmark link can appear at top or bottom of page/post content
* Bookmarks can be organized into different user created folders
* Folders can be made public or private by users
* Users can view and manage their bookmark folders and bookmarks from their profiles
* Users can view other users public bookmark folders

= Development * Translations =

Want to add a new language to Ultimate Member? Great! You can contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/ultimate-member).

If you are a developer and you need to know the list of UM Hooks, make this via our [Hooks Documentation](https://docs.ultimatemember.com/article/1324-hooks-list).

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/um-forumwp).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > User Bookmarks to customize plugin options
3. For more details, please visit the official [Documentation](https://docs.ultimatemember.com/article/1481-user-bookmarks-setup) page.

== Changelog ==

= 2.0.7: March 22, 2020 =

* Fixed: XSS vulnerabilities
* Tweak: UM Dropdown.js scripts updated
* Deprecated: `profile/edit-folder/form.php` template - it's unusable duplicate of `profile/edit-folder.php`

= 2.0.6: December 8, 2020 =

* Added: "Using Page Builder" option to a description if enabled
* Fixed: Issue with page builders showing shortcodes (strip_shortcodes added to bookmark description)
* Fixed: Bookmark issue on product and custom post types
* Fixed: Admin forms rendering in metaboxes

= 2.0.5: August 11, 2020 =

* Added: *.pot translations file
* Changed: The bookmark button templates
* Updated: CSS styles for the button to make it compatible with other UM extensions

= 2.0.4: March 18, 2020 =

* Added: Compatibility with Profile Tabs
* Fixed: Trimmed bookmarks button HTML

= 2.0.3: January 24, 2020 =

* Fixed: Displaying bookmark icon
* Fixed: Displaying bookmarks menu at the profile page when Bookmarks capability is turned off for the user role

= 2.0.2: November 11, 2019 =

* Added: Sanitize functions for request variables
* Added: esc_attr functions to avoid XSS vulnerabilities
* Fixed: [um_bookmarks_button] shortcodes
* Fixed: style dependencies
* Fixed: modal window z-index
* Fixed: privacy fields at account page

= 2.0.1: July 16, 2019 =

* Added: Bookmarks for archive
* Fixed: Texts spelling
* Fixed: Checking if bookmark or post exists
* Fixed: Add to bookmark button arguments

= 2.0: June 10, 2019 =

* Initial release