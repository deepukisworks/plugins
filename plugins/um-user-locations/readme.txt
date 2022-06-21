=== Ultimate Member - User Locations ===
Author URI: https://ultimatemember.com/
Plugin URI: https://ultimatemember.com/extensions/user-locations
Contributors: ultimatemember, champsupertramp, nsinelnikov
Donate link: 
Tags: user location, member, membership, user-profile, user-registration
Requires at least: 5.0
Tested up to: 5.7
Stable tag: 1.0.5
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Requires UM core at least: 2.1.6

Using the Google Maps API, display users on a map on the member directory page and allow users to add their location via their profile.

== Description ==

= Important =

User Locations extension is integrated with the [Google Maps Platform](https://developers.google.com/maps). The extension requires creating an app within the platform and activating the API. The Google Maps Platform is a paid service that comes with $200 in free usage each month. To view the Google Maps usage pricing please click [here](https://cloud.google.com/maps-platform/pricing/).

= Key Features: =

* Add user location field to registration and profile forms to allow users to add their location.
* Ability to auto-detect user location (requires users to click the pin icon on the location field and give permission to share location)
* Adds a user location search field to member directory to allow people to search for users by location
* Adds a map to member directory
* User Clustering for users in close proximity on member directory map
* User avatars appear on the map to show the location of users
* When avatar is clicked a small profile card will appear on the map which shows the avatar, display name and information you choose in member directory settings
* Allow people to find users by dragging the map

= Development * Translations =

Want to add a new language to Ultimate Member? Great! You can contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/ultimate-member).

If you are a developer and you need to know the list of UM Hooks, make this via our [Hooks Documentation](https://docs.ultimatemember.com/article/1324-hooks-list).

= Documentation & Support =

Got a problem or need help with Ultimate Member? Head over to our [documentation](http://docs.ultimatemember.com/) and perform a search of the knowledge base. If you can’t find a solution to your issue then you can create a topic on the [support forum](https://wordpress.org/support/plugin/um-forumwp).

== Installation ==

1. Activate the plugin
2. That's it. Go to Ultimate Member > Settings > Extensions > User Locations to customize plugin options
3. For more details, please visit the official [Documentation](https://docs.ultimatemember.com/article/1545-user-location-setup) page.

== Changelog ==

= 1.0.5: April 28, 2021 =

* Fixed: Registration and Profile forms handler
* Fixed: Integration with Profile Completeness extension

= 1.0.4: April 14, 2021 =

* Added: Search by radius functionality on the member directory
* Added: Ability to turn off clustering and OMS( Overlapping Marker Spiderfier ) by the JS hook
* Added: Ability to show the nearest members if the search results are empty
* Added: Location filter / location admin filter
* Added: Ability to select the location by the clicking on the map
* Fixed: OMS( Overlapping Marker Spiderfier ) library using on the map shortcode
* Fixed: Issue with the first map's idle
* Fixed: Distance sorting calculation

= 1.0.3: December 11, 2020 =

* Added: OMS( Overlapping Marker Spiderfier ) library for getting visible the clustering markers in the same place
* Added: Error/notices blocks for the location field
* Added: Ability to select map marker icon for role
* Added: Ability to select what markers type to use in member directory
* Added: Distance field based on User Location fields
* Added: Ability to add the Distance field to User Profile meta section
* Added: Ability to show the Distance field on the Member Directory
* Added: Ability to sort by nearby members based on distance and current user geolocation
* Added: [um_user_locations_map] shortcode
* Fixed: Getting bounds on the first member directory page loading (not default starting lat/lng/zoom)
* Fixed: Getting bounds on the first member directory page loading (Show results after search and searched by location. Added timeout for setting bound for invisible map)
* Fixed: Uninstall process

= 1.0.2: August 11, 2020 =

* Added: JS filter 'um_user_locations_marker_data' to customize the markers' data
* Added: JS filter 'um_user_locations_marker_clustering_options' to customize the markers' clustering options
* Added: Integration with Profile Completeness
* Added: Starting coordinates/zoom for the member directory
* Fixed: Google Maps init when Internet connection is slow
* Fixed: Keypress "Enter" on user location field autocomplete
* Fixed: Locations fields add/remove and option 'um_map_user_fields'
* Fixed: Map localization
* Fixed: Location field title in the markers' title attribute
* Fixed: Mobile device styles for the member directory map
* Fixed: Getting map bounds
* Fixed: Integration with Social Login form in popup and user Location field

= 1.0.1: April 1, 2020 =

* Added: Ability to edit member directory map via a template in theme
* Added: JS hooks for 3-rd party integrations

= 1.0.0: March 03, 2020 =

- Initial release