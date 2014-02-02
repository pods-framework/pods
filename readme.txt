=== Pods - Custom Content Types and Fields ===
Contributors: sc0ttkclark, pglewis, curtismchale, Desertsnowman, dan.stefan, mikedamage, logikal16, jchristopher
Donate link: http://podsfoundation.org/donate/
Tags: pods, custom post types, custom taxonomies, user fields, custom fields, cck, cms, content types, database, framework, drupal, post types, avatars, comment fields, media fields
Requires at least: 3.4
Tested up to: 3.7.1
Stable tag: 2.3.18
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pods is a framework for creating, managing, and deploying customized content types and fields.

== Description ==

Check out http://pods.io/ for our User Guide, Forums, and other resources to help you develop with Pods.

= Introduction =
[youtube http://www.youtube.com/watch?v=tkBDNOAQJxg]

= Content types that evolve with your needs =
Create any type of content that you want -- small or large -- we've got you covered. Every content type created with Pods gets all the love it needs to grow up big and strong. You'll get an easy to use interface that lets you manage custom fields and how your content type will function.

We now give you the power you've never before had with a single plugin because we've reimagined how to manage content types from the ground up.

= Create new content types =
With Pods, you can create entirely new content types:

* Custom Post Types - Content types that look and function like Posts and Pages, but in their own separate areas
* Custom Taxonomies - Content types that look and function like Categories and Tags, but in their own separate areas
* Custom Settings Pages - Create custom admin forms under Settings to help organize your site's custom global settings
* Advanced Content Types - These are entirely separate from WordPress and function off their own database tables

= Extend existing content types =
Not satisfied? How about the power of being able to extend existing content types? We've got you covered with extending these major WordPress objects:

* Post Types - Create and manage fields for any existing Post Type (Posts, Pages, etc), even those created by plugins or themes
* Taxonomies - Create and manage fields for any existing Taxonomies (Categories, Tags, etc), even those created by plugins or themes
* Media - Create and manage fields for your media uploads, easily add additional information and context to any file you want
* Users - Create and manage fields for your user profiles, this is truly the bees knees!
* Comments - Create and manage fields for your visitor comments, easily add fields to fit the way you use comments as reviews and more

= Use our field types, or make your own =
We have a lot of common field types available for you to use, or you can build your own with our extensible field type classes.

Each of these field type have their own set of options, if those aren't enough they are also easily extended:

* Date / Time - Date, Time, or both
* Number - Plain Number or Currency
* Text - Plain Text, Website, Phone, E-mail, or Password
* Paragraph Text - Plain Paragraph, WYSIWYG (TinyMCE or CLEditor, or add your own), or Code (Syntax Highlighting)
* Color Picker - Choose colors, because colors are great
* Yes / No - You can't really go wrong with a checkbox, but we've added a few charms to make it stand out
* File / Image / Video - Upload new media or select from existing ones with our Media Library integration, or use a simple uploader, your choice
* Avatars - Upload new media or select from existing ones, automatically integrates with get_avatar calls for Users extended by Pods
* Relationships - Relate any item, to any item of any WP object type, another Pod, or a custom user-defined list -- with bidirectional relationships

= Relationships to rule the world with =

* Custom defined list
* Post Types
* Taxonomies
* Users
* User Roles
* User Capabilities
* Media
* Comments

And many other relationships are also available including:

* Image Sizes
* Navigation Menus
* Post Formats
* Post Status
* Sidebars
* Countries (predefined)
* US States (predefined)
* Days of Week (predefined)
* Months of Year (predefined)

= Easily display your content =
There are several ways to get Pods data to show up throughout your site, but with any WP object type you create or extend with Pods, you can use all of the functions and methods you're already used to with the core WordPress API / Loop -- out of the box!

Additionally, we have a United Theming API that lets you theme your content types across every type of Pod, regardless if it's a post type or taxonomy or user, or.. you get the picture.

= Customized Management Panels =
Utilize Pods UI (included in Pods 1.10+) to build your own Custom Management panels for your Pods.

= Optional Components to do even more =
You can enable some of our included components to extend your WordPress site even further:

* Roles and Capabilities - Create or edit Roles for your site, and customize their corresponding capabilities
* Pages - Create custom pages that function off of your site's path, with wildcard support, and choose the Page Template to use
* Templates - Use our template engine to create templates that can be handed off to clients for carefree management
* Helpers - Customize how Pods works right from the admin area with simple to advanced reusable code snippets
* Advanced Content Types - These types of content were built into Pods prior to 2.3, but are now optionally enabled
* Table Storage - Enable table-based storage for custom fields on Post Types, Media, Users, and Comments. Also adds the ability to add custom fields to Taxonomies
* Advanced Relationships - Add advanced relationship objects for relating to including Database Tables, Multisite Networks, Multisite Sites, Themes, Page Templates, Sidebars, Post Type Objects, and Taxonomy Objects
* Markdown Syntax - Parses Markdown Syntax for Paragraph Text / WYSIWYG fields
* Builder theme integration - Use our tightly integrated modules for Builder in your layouts

= Migrate to Pods, find out what you've been missing =
Using another solution? We've built additional components to help you transition:

* Import from Custom Post Type UI
* More imports coming soon including Importing from Custom Field Suite, Advanced Custom Fields, and Custom Tables

= Plays well with others =
We also do our best to integrate and play nicely with other projects:

* Plugins we've integrated with
 * [Tabify Edit Screen](http://wordpress.org/plugins/tabify-edit-screen/)
 * [Codepress Admin Columns](http://wordpress.org/plugins/codepress-admin-columns/)
 * [Polylang](http://wordpress.org/plugins/polylang/)
 * [YARPP](http://wordpress.org/plugins/yet-another-related-posts-plugin/)
 * [WPML](http://wpml.org/)
* Themes we've integrated with
 * [Builder](http://www.ithemes.com/) (iThemes)
 * [Genesis](http://www.studiopress.com/) (StudioPress)

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP Admin plugin page)
1. Activate this plugin

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

== Screenshots ==

1. Create new content types or extend existing ones
2. Add fields of many different types, with individual options for each so you can define your content type to be what you need it to be
3. Post Type pods will add fields to the Post editor
4. Taxonomy pods will add fields to the Taxonomy forms
5. User pods will add fields to the User forms
6. Comment pods will add fields to the Comment forms
7. Media pods will add fields to the Media forms
8. Create Advanced Content Types that exist only as you define them, outside of the normal WP object structure

== Contributors ==

Pods really wouldn't be where it is without all of the contributions both financially and through code / time. Check out our GitHub for a list of contributors, or search our GitHub issues to see everyone involved in adding features, fixing bugs, or reporting issues/testing.

[github.com/pods-framework/pods/graphs/contributors](https://github.com/pods-framework/pods/graphs/contributors)

== Translations ==

Many thanks go out to the fine folks who have helped us translate Pods into other languages other than English!

* sk_SK (Slovak) - Branco Radenovich ([WebHostingGeeks.com](http://webhostinggeeks.com/blog/))
* nl_NL (Dutch) - [Ramon van Belzen](http://www.ramoonus.nl/)
* pt_BR (Portuguese) - [Luciana](https://github.com/yammye)
* And more in progress! Join us in further translating the Pods interface at: http://wp-translate.org/projects/pods/dev

== Changelog ==

= 2.3.18 - November 4th, 2013 =
* Be on the look out for Pods 2.4, officially in development and in Beta soon! It will include our new Loop and Repeatable fields
* Fixed: PodsData row handling during fetch loop, thanks to a number of users who helped find this one
* Found a bug? Have a great feature idea? Get on GitHub and tell us about it and we'll get right on it: https://pods.io/submit/
* Our GitHub also has a full list of issues closed for this release and all previous 2.x releases, you can even browse our code or contribute notes and patches all from the web at: http://pods.io/github/

= 2.3.17 - November 4th, 2013 =
* Fixed: PodsData item caching now disabled for WP objects, relying on core WP caching entirely
* Fixed: PodsAPI::save_pod_item default value handling for new items no goes through all fields, even if not included in form

= 2.3.16 - November 4th, 2013 =
* Fixed: PodsMeta pod caching is now different between meta calls and the form methods, avoiding potential issues with functions used that call their own meta (TinyMCE)
* Fixed: Properly add/drop column for table-based Pods when switching between a custom simple relationship and a normal relationship
* Fixed: Session starting for memcache-based sessions and other tcp:// configs improved
* Fixed: Media saving bug, where the custom fields were not saving when going to Media Library > Edit

= 2.3.15 - October 31st, 2013 =
* Added: New 'calc_rows' option in Pods::find, this allows for SQL_CALC_FOUND_ROWS to be run selectively (default is off, since we run a separate count query on demand by default)
* Added: You can now override the 'manage' action link in PodsUI 'action_links'
* Added: `shortcodes="1"` attribute for the Pods shortcode will allow for running of shortcodes output through templates or fields included
* Fixed: PHP warnings with role restriction when limited to one role
* Fixed: 2.3.14 introduced a regression bug that would not save fields in the user profile, so values never changed
* Fixed: Quick Edit on terms could potentially save empty values for the custom fields
* Fixed: Traversal handling of Pods::field for related_item.ID would cache into object as related_item, so a subsequent lookup of related_item would come back as the ID and return the wrong value

= 2.3.14 - October 29th, 2013 =
* Fixed: Some users experienced and issue with user registration when there were required fields

= 2.3.13 - N/A =

= 2.3.12 - October 15th, 2013 =
* Improved: Meta object caching improved
* Fixed: Some users experienced an issue with a reference error

= 2.3.11 - October 12th, 2013 =
* Fixed: User / Post field value saving with better nonce handling
* Fixed: pods_v_set saving for user meta

= 2.3.10 - October 11th, 2013 =
* Added: Ability to set 'output' type in Pods::field() to 'pods' for Relationship fields related to a Pod, which will return an array of fully functional 'pods' objects for further advanced code
* Added: Pod Pages now have an option to redirect to the login page or a custom URL if the user does not have permission to view it (based on restrict settings on the Pod Page itself)
* Added: Ability to set Taxonomy terms for a Post Type item through the normal Pods 'add' / 'save' / etc methods
* Added: Ability to set User 'role' for a User through the normal Pods 'add' / 'save' methods
* Added: Taxonomy-specific capabilities added to the Pods Roles component
* Added: New Days of Week and Months in Year pre-defined relationships added for simplistic date-oriented fields
* Added: Support for $offset handling in Pods::pagination()
* Added: YARPP integration for Post Types
* Added: Default Select Text customization for Relationship fields that are set to a Dropdown input
* Added: Default Post Status to use for Custom Post Types created by Pods, when utilizing the Pods 'add' method
* Added: mu-plugins support for Pods as a Must-Use plugin on WordPress Multisite installations (props @studioanino)
* Improved: Smarter handling of post_status for Post Types, easier to override to show other post statuses, and if you don't provide it in the 'where', it will fall back to the default(s)
* Improved: Pods::remove_from() now removes all values if you provide no 'value' for a specific field
* Fixed: Comment queries using comment_type should allow for a blank string (props @sirbeagle)
* Fixed: Date / Time saving for 24 hour formats
* Fixed: Timezone notices on certain configurations

= 2.3.9 - August 5th, 2013 =
* A big welcome to the newest contributor to our team, David Cramer (@desertsnowman)!
* Added: Theme-based Pod Templates now available, when using $pod->template( 'your-template' ) or other places a template can be used (shortcode, widget, etc), with $obj variable available for use like in a normal template -- this will automatically include your template file from the following locations, child-theme aware: pods/your-template.php, pods-your-template.php, or your-template.php -- Get the code out of the database and get rid of the need for the Templates component!
* Added: When saving items via the API, relationship fields now accept slugs (previously only IDs)
* Added: When saving items via the API, file fields now accept URLs or GUIDs (previously only IDs), if you provide a URL and it isn't already in WordPress, it will automatically import as a new WP attachment
* Added: Read Only option for fields, works like Hidden option, under Advanced tab of field editor
* Added: New '_src_relative' and '_src_schemeless' field options for returning an attachment field's URL that's schemeless (// instead of http://)
* Added: New 'list' option for pagination, a clone of the 'paginate' option that's Bootstrap compatible
* Added: Added Chinese translations
* Fixed: Updated compatibility for WordPress 3.6 slashing changes while maintaining compatibility for WP 3.4+
* Fixed: Custom Taxonomies now have their menu icon option available, previously hidden due to a bug
* Fixed: Various PHP notices/warnings
* Fixed: Translation tweaks and fixes

= 2.3.8 - June 8th, 2013 =
* Fixed: Hide field from UI option now works properly for admins
* Fixed: User data handling for `pods( 'user' )`
* Fixed: jpeg extension now included in built-in 'images' option for File field type
* Fixed: iThemes Builder / Markdown components weren't loading properly (no errors, just didn't load)

= 2.3.7 - June 7th, 2013 =
* Added: New filter to allow searching across different fields in autocomplete relationship fields: https://github.com/pods-framework/pods/issues/1464
* Improved: JS performance used for the field manager drastically improved (props @pglewis)
* Improved: PHP optimization tweaks for how we handle $_POST sanitization
* Fixed: Parent Menu ID handling for the Pods that support it
* Fixed: E_STRICT PHP notices
* Fixed: Shortcode popup JS building logic
* Fixed: Issue with find() queries using number decimals matching the relationship traversal regex rules

= 2.3.6 - May 24th, 2013 =
* Fixed: Issue with the Pod list when you delete or empty a Pod, it would repeat the same row in the list until you went back to the Edit Pods screen without the id=X in the URL
* Fixed: Issue with renaming a field to another name would rename the field name and then delete it due to a missing ID validation check

= 2.3.5.1 - May 20th, 2013 =
* Fixed: Issue with the Upgrade wizard from 1.x to 2.x showing up properly

= 2.3.5 - May 19th, 2013 =
* Added: Ability to add new global field options (separate from field types) and new field editor tabs
* Various fixes that can be found on GitHub

= 2.3.4 - April 29th, 2013 =
* Added: Ability to iterate through the Pods object with `foreach ( $pod as $item ) { echo $item->display( 'name' ); }`
* Added: Ability to override serial array parameters in Pods::display() `$pod->display( array( 'name' => 'field_name', 'serial_params' => array( 'and' => '' ) ) )`
* Added: Ability to override related field parameters in Pods::field() to further filter related field arrays beyond the defaults `$pod->field( array( 'name' => 'related_field', 'params' => array( 'where' => 't.active = 1' ) ) )`
* Added: Ability to use RegEx in Pod Page URI's, just filter 'pods_page_regex_matching' and return true (default is false, normal wildcard * handling)
* Improved: Pod Page detection on URLs is cleaner and more performant, the tricky MySQL query from the days of Pods 1.x has been completely replaced with a process similar to WP Rewrites
* And 15 other bug fixes that can be found on GitHub

= 2.3.3.1 - April 21st, 2013 =
* Fixed: Advanced Content Types were missing their 'Advanced' tab
* Fixed: IE 8-10 issue with plupload implementation for the 'Add File' button

= 2.3.3 - April 21st, 2013 =
* Added: Ability to change the output type of relationship fields with pods_field_related_output_type filter - Options are arrays (default), objects, ids, or names
* Added: Traversal for detail_url (related_post.detail_url maps to get_permalink, same for Taxonomies, Users, or Comments)
* Added: Pods::is( $field, $value ) to check if a field is a specific value
* Added: Pods::has( $field, $value ) to check if a field has a specific value in it - Check for value(s) in related/file fields, get stripos for text-based fields, uses Pods::is for all other fields
* Added: Pods::remove_from( $field, $value ) to remove a value for relationship (remove ID), file (remove ID), and number (subtract) and saves (see Pods::add_to for the reverse of this)
* Added: Ability to change the default file upload type (default images) with the pods_form_ui_field_file_type_default filter
* Improved: Pods class caching now better and utilized object caching for primary object init
* Translated: Full pt_BR translation provided by [Luciana](https://github.com/yammye)
* And 40+ other enhancements and bug fixes that can be found on GitHub

= 2.3.2 - April 11th, 2013 =
* Added: You can now select 'ID' from the list of available columns to show in Admin UI for Advanced Content Types
* Various fixes that can be found on GitHub

= 2.3.1 - April 9th, 2013 =
* Added: New ability to set the menu location of Custom Taxonomies (expose a Custom Taxonomy that isn't associated to a Post Type)
* Various fixes that can be found on GitHub

= 2.3 - April 7th, 2013 =
* Added: Custom Settings Pages - now you can add new settings pages with their own custom fields!
* Added: Pods find() 'where' / 'having' parameters now accepts the standard WP_Query meta_query format! With the added ability to nest AND/OR 'relation' too!
* Added: When using pods() function and `[pods]` shortcode, Pod and ID will be auto-detected from current post type and ID if on singular post page or in the loop
* Added: Pods fields() method now takes two new arguments, $field and $option to get an option from a specific field
* Added: `{@detail_url}` handling for taxonomies, users, and comments
* Added: New find() traversal capabilities https://github.com/pods-framework/pods/issues/972
* Added: New field() value and traversal capabilities https://github.com/pods-framework/pods/issues/971
* Added: When saving a relationship field that's bidirectional, and the related field is required - if the save would cause that field to be empty a warning will now be shown on save
* Added: New Pods first_id/last_id methods for getting the first/last ID of find()
* Added: New Pods nth( $pos ) method for when in a fetch() loop, works like CSS nth-child and accepts the same format `5`, `3n+3`, etc: http://css-tricks.com/how-nth-child-works/
* Added: New Pods position() method for when in a fetch() loop, returns current row number (1+)
* Added: New Pods add_to() method to add a value to relationship (add ID), file (add ID), number (add/subtract), and text (append) fields to their existing values and saves
* Added: New Pods import() method maps to PodsAPI import() method
* Added: New Pods export() method maps to PodsAPI export() and accepts find $params and the ability to choose depth level
* Added: Advanced Content Types now have Admin UI settings available which expose the most popular PodsUI options
* Added: Advanced Content Types now have the ability to be Hierarchical, by selecting a relationship field to itself
* Added: Now you can Duplicate Pods themselves!
* Added: Pods now automatically adds Post Type capabilities (based on the Post Type options) for each Custom Post Type you create in Pods, works with Members capabilities filter
* Added: Additional support in the Pods API for (eventually) extending WP Multisite Sites / Networks, and Custom Tables
* Added: New shortcode / widget / Builder module for including a file from the theme (using PodsView)
* Added: New shortcode option for including a field value from the current post/page
* Added: New WordPress 3.5 Media Library integration, more on the way soon!
* Added: New shortcode option for including Pod Page content
* Added: New Pod Page option to associate a Pod and choose the slug {@url.2} to use for populating the pod
* Added: New translations! Join us in further translating the Pods interface at: http://translate.rocksta.rs/projects/pods-framework
* Revamped: Admin interface for editing Pods has been updated with tabs and better organization, includes the new ability to add your own tabs and options using the pods_admin_setup_edit_tabs and pods_admin_setup_edit_options filters
* Revamped: Relationships saving has been revamped to provide better abstraction (less code, more reusable)
* Updated: Additional Polylang and WPML support throughout the Pods API
* Updated: Pods Edit list now separated by Pod Type for easier management on large sites
* Updated: Pods Components list now separated by Category, getting us ready for many new components that will be separately available soon
* Updated: Pods export() method now exports to JSON and you can choose the depth of the export (whether to include relationships and their related items, etc)
* Updated: Better handling for Pods prev/next methods, detecting if there's a find() already on that page
* Updated: More phpDoc updates
* Updated: More refined caching and optimization of specific calls to get only what they need
* Updated: Now enforcing maximum post type (20 chars) / taxonomy (32 chars) naming
* Changed: Advanced Content Types have been split off into their own component which you can enable to be able to add new Advanced Content Types
* Changed: Table-based storage for WordPress objects (Post Types, Taxonomies, Media, Users, and Comments) has been split off into it's own component which you can enable to add the table-based storage option to the Pods Add New interface
* Various fixes that can be found on GitHub

= 2.2 - January 5th, 2013 =
* Added: New 'Duplicate Field' option, that lets you copy a field's settings into a new field in the Pod editor
* Added: New iThemes Builder component - Adds four new modules available for use in Builder Layouts -- Field Value, Form, List Items, and Single Item
* Updated: Split up the old Pods Admin > Setup menu into two separate items -- Edit Pods and Add New
* Fixed: Upgrade from Pods 1.x to Pods 2.x now fixed, in Pods 2.1 the upgrade wizard was not shown
* Various fixes that can be found on GitHub

= 2.1 - December 7th, 2012 =
* Pods is now WordPress 3.5 compatible as we've added a number of fixes for all the 3.5 media goodness! We're also working on some tighter integration with the new 3.5 media popups (thanks to the awesome work of @jchristopher) - watch for that in Pods 2.2 soon
* Added: New Tableless mode (for WordPress VIP compatibility!) lets Pods run on any site w/ table-based storage turned off and wp_podsrel won't be utilized (or even created if tableless mode is on during activation) - define( 'PODS_TABLELESS', true )
* Added: New Light mode disables all Components - define( 'PODS_LIGHT', true )
* Added: New Avatar field type available for when you extend the Users object with Pods - Automatically takes over get_avatar calls!
* Added: New Relate to options available for relationships fields for Post Formats and WP Nav Menus
* Added: API to register pods and fields from a theme or another plugin (doesn't save into the DB): pods_register_type and pods_register_field - See https://github.com/pods-framework/pods/issues/700
* Added: Now you can look up meta field values within find() calls, just use the field_name.meta_value syntax (instead of t.field_name) and Pods will auto-join the table needed
* Updated: Relationship 'where' option in Field editor now more robust and has all fields (including relationships, or meta like above) can be referenced
* Updated: Relationship 'where' option in Field editor now supports {@user.ID} lookups which maps to pods_var( 'ID', 'user' ) to sanitize (ex. user.ID != '{@user.ID}' in the Pick WHERE will return all users not the current user); You can use any pods_var enabled option, documentation coming this month
* Updated: Relationship saving has been optimized for both bi-directional relationships and regular relationships
* Various fixes that can be found on GitHub

= 2.0.5.1 - November 25th, 2012 =
* Fixed: 'Edit' link wasn't appearing for Pod Pages / Templates / Helpers (you could click the title though)

= 2.0.5 - November 24th, 2012 =
* Another big set of stability fixes to improve performance and functionality
* Added: Migrate Packages component - Our Package manager makes a return! You may remember it from Pods 1.x, but we've cleaned it up and improved the interface to make it easier to migrate your settings between sites or share them with others

= 2.0.4.1 - October 17th, 2012 =
* Updated: Pods UI duplicate method labels were confusing
* Fixed: Simple Relationships were returning raw data for table-based Pods
* Fixed: Specify specific content types to import in Migrate Custom Post Types UI component
* Fixed: Add Custom Capabilities bug with first text box wouldn't save in Roles component
* Fixed: Various Widget fixes to Widget UI
* Fixed: XHTML balance tags option in Writing settings was adding a space in <?php tags for Pod Pages / Helpers / Templates
* Fixed: Date / Time field now allows an empty value to be saved rather than setting the current date / time, this is an option that can be turned off
* Fixed: WP Rewrites are properly flushed upon adding / editing / deleting Pods

= 2.0.4 - October 15th, 2012 =
* Big bug fix release, we've fixed tons of bugs and improved backwards compatibility even further - stability, stability, stability!

= 2.0.3.1 - October 5th, 2012 =
* Fixed an upgrade issue a few users were reporting where the upgrade wouldn't start
* Fixed reserved post_name issues with our internal post types for Pods and Fields (rss, date, and any other feeds)

= 2.0.3 - October 4th, 2012 =
* We've fixed many more bugs, that means even more stability and backwards compatibility for those who have been holding off on upgrading
* Added: 'expires' parameter to find() / findRecords() calls, defaults to null, but set it to 0 or above (in seconds) and it will cache the results for as long as you'd like.
* Added: 'cache_mode' parameter to find() / findRecords() calls, defaults to 'cache', additional options are 'transient' and 'site-transient' and it kicks in when 'expires' is 0 or above
* Added: 'search_across' parameter to find() / findRecords() calls, defaults to false, set it to true to have your searches search across all of the fields on your pod (excluding relationship / files)
* Added: 'search_across_pick' parameter to find() / findRecords() calls, defaults to false, set it to true to have your searches search across all of the relationship fields on your pod
* Added: 'search_across_file' parameter to find() / findRecords() calls, defaults to false, set it to true to have your searches search across all of the file fields on your pod
* Added: Bidirectional fields are now available again in Pods 2.0, our new fully revamped functionality takes care of the headaches and will keep your relationships in sync with each other. As a result of the revamp, any previous bidirectional fields will need to be set again. Those now upgrading from Pods 1.x will have their existing bidirectional fields upgraded automatically and won't have to worry about setting them up again.
* Added: Widgets are now available to use to List Pod items, Show a specific Pod item, or to Show a specific field from a Pod item much like our TinyMCE shortcode popup -- enjoy!
* Improved: Search handling has been improved along with the above tweaks
* Check out the new screenshots we added to our plugin page if you're new to the plugin
* While we have been working on our new Pods 2.0 documentation for our site, we went ahead and synced all of that good stuff over into the code comments along with links back to the documentation.

= 2.0.2 - September 27th, 2012 =
* Even more bugs have now been fixed including additional backwards compatibility fixes
* Caching improvements and fixes, things should be even more responsive, try enabling object caching to see super speed!

= 2.0.1 - September 25th, 2012 =
* With the help of our awesome users, we've been able to quickly fix 14 bugs
* Improved backwards compatibility
* Fixed Pods UI reordering saving bug

= 2.0 - September 21st, 2012 =
* An all new, fully revamped Pods has arrived! Check our plugin page for all the details
* Please backup your site database before upgrading, even though we've tested migration it's never a bad idea to be safe
* Create and extend WP objects like Post Types, Taxonomies, Media, Users, and Comments, plus everything you love about Pods from before

= 1.14.4 - September 16th, 2012 =
* Security Update Reminder: As of Pods 1.12+, AJAX API calls all utilize _wpnonce hashes, update your customized publicForm / input helper code AJAX (api.php and misc.php expect `wp_create_nonce('pods-' . $action)` usage)
* Note: Oh hey, Pods 2.0 is coming out September 21st! Please help us continue to test the beta this week: http://dev.pods.io/tag/pods2/
* Changed: get_current_url was an older function added by Pods a while back, pods_get_current_url is the new function name which is future-proof (get_current_url will point at the new one)
* Added: A new check will deactivate the plugin if you happen to have another version of the plugin activated for testing purposes
* Added: A quick enhancement for all to enjoy as a final farewell to Pods 1.x, File Browser now has a mouse-over image enlarge function (props @WallabyKid), see: http://pods.io/forums/topic/add-thumbnail-preview-to-jqmwindow-file-browser-for-image-files/
* Fixed: Some plugins/themes use the wp_title filter incorrectly and do not pass the $sep and $seplocation variables, we now set defaults in those cases
* Fixed: Some sites experienced PHP notices from the way we've been using parse_url, we now have a fallback for that handling which clears those up
* Q & A: What's going to happen to Pods 1.x when Pods 2.0 comes out? We're going to release maintenence updates to Pods 1.14.x for a period of time, but there will be no further features added

= 1.14.3 - September 6th, 2012 =
* Added: Reordering a Pod (using pods_ui_manage) now has a new capability check for pods_reorder_pod_podname (Custom WP capability you can add to the user's role if they don't have pod_podname access already)
* Added: New pods_page_precode_X action, where X is the Pod Page URI
* Fixed: get_field from returning values if an id is not set (no data found)
* Fixed: Moved wp_editor support into an if/else statement to avoid potential overlaps
* Fixed: Pods UI pagination and search parameter naming for 'num' usage

= 1.14.2 - June 8th, 2012 =
* Changed: More strictness to the above security update, also setting tighter defaults for security access w/ uploader

= 1.14.1 - May 31st, 2012 =
* Changed: Uploaded files now uses data-post-id attribute in file row div to avoid issues with IDs, backwards compatibility maintained for old input helpers using IDs
* Fixed: Uploaded files not showing in form in the order of upload on subsequent edits
* Fixed: Fixed an issue with adding / editing fields where "Related to" dropdown would not show

= 1.14 - May 21, 2012 =
* Important Change / Addition: For installations using WordPress 3.3+, we have switched the default uploader to Plupload from SWFUpload due to incompatibilities introduced in WP 3.3.2 that effect all plugins and themes using the styled button. Be sure to update your file upload helpers using our examples at http://pods.io/packages/file-uploader-input-helpers/
* Added: edit_where_any option in Pods UI now lets you set (true/false) whether for edit_where to be an ANY or ALL match (default false = ALL)
* Fixed: Date Input field was throwing a JS error if you used YYYY-mm-dd format without the time included
* Fixed: parse_url fixes for when path isn't set (localhost or custom ports usually causes this)
* Fixed: When there was extra output above or below JSON strings like errors from other plugins, whitespace, or anything else - we now explicitly match the JSON {...} string before using it in JS to avoid confusing errors for the user
* Fixed: .pods_form style tweaks to help cover themes which display the form incorrectly
* Fixed: Forcing (int) on getRecordById when is_numeric( $id )
* Fixed: Resolved incompatibility issues with certain MySQL configurations which were throwing errors when saving a Pod

= 1.12.4 - April 5, 2012 =
* Added: 'offset' parameter to Pod::findRecords, allows you to offset what results to start with, which is added to the offset calculated based on current page number and limit
* Added: 'page_var' parameter to Pod::findRecords, allows you to set a custom page_var (default is 'pg'), setting it will reset the current page number, set during Pod::construct()
* Added: New 'pods_rel_lookup_data' filter to filter the data array itself (not just the MySQL resource given in 'pods_rel_lookup' filter above), great for customizing drop-downs for PICK fields
* Fixed: 'page' parameter in Pod::findRecords wasn't being validated as a number greater than 0, now forces a minimum of 1; Anything less will also reset the current page number, set during Pod::construct()
* Fixed: Pods UI 'label_add' wasn't being used on button at top of manage table list
* Fixed: Pagination bug with custom page_var set in Pod object, would add the custom page_var to the URL over and over
* Fixed: Upgrade script updated to include all upgrades prior to 1.6, which had been left out in a previous release
* Fixed: More strict matching in Pod::findRecords for field names, instead of just removing '(' and ')', it now removes 'function_name(' first, so that fields with the same name as function names won't be pulled

= 1.12.3 - February 19, 2012 =
* Added: Pods UI findRecords parameters array now goes through a new filter called "pods_ui_findrecords"
* Fixed: Forcing boolean check if true in bypass_helpers in PodAPI
* Fixed: Pod Page automatic title generation now removed WP home path (for WP sites in sub-directories) (props @chrisbliss18)
* Fixed: nicEdit JS "A.createRange()||document.createRange()" fix for JS errors on certain browsers / uses
* Fixed: Pagination page_var usage (was forcing 'pg' var name no matter what), and fixed query array handling; Props to @thangaswamyarun for finding this one so we could fix it!
* Fixed: Pods UI search settings set correctly now (had to flip a true/false check and not have search_across take the bool value of search)
* Fixed: Pods UI filtering sets emptied values now, wasn't setting right if you had emptied a filter value when submitting (unselected drop-down)

= 1.12.2 - December 14, 2011 =
* Fixed: WP 3.3 TinyMCE Editor bug with HTML tab (wouldn't save if HTML tab was active during initial form load) and other minor fixes

= 1.12.1 - December 12, 2011 =
* Fixed: findRecords Order bug

= 1.12 - December 12, 2011 =
* Important: As with all upgrades, we take them seriously. If you experience any major issues when upgrading to this version from a previous version, immediately contact uhoh@pods.io and we'll help get your upgrade issue figured out (critical bugs only please)
* Security Update: AJAX API calls all utilize _wpnonce hashes, update your customized publicForm / input helper code AJAX (api.php and misc.php expect `wp_create_nonce('pods-' . $action)` usage)
* Added: Multi-level references in field names when referenced in Pod :: findRecords $params (`select, where, groupby, having, orderby`) - ex. `'where' =&gt; 'pick_field.another_pick_field.another_pick.field = "Example"'` **(donation-funded by @chriscarvache)**
* Added: Multi-level references in search filters when referenced in Pod :: findRecords $params (`select, where, groupby, having, orderby`) or Pod :: traverse variable (when not mentioned in params but you want it filterable) **(donation-funded by @chriscarvache)**
* Added: Lightweight Relationship (PICK) field support in Pod :: findRecords (2.0 full support in UI) **(donation-funded by @chriscarvache)**
* Added: Fully revamped JOINs based on field names when referenced in Pod :: findRecords $params (`select, where, groupby, having, orderby`) **(donation-funded by @chriscarvache)**
* Added: RegEx auto-sanitizing of field names when referenced in Pod :: findRecords $params (`select, where, groupby, having, orderby`) **(donation-funded by @chriscarvache)**
* Added: PodAPI :: duplicate_pod_item with $params as `'datatype' =&gt; 'podname', 'tbl_row_id' =&gt; $item_id_to_duplicate` (returns new id) **(donation-funded by @gr0b1)**
* Added: PodAPI :: export_pod_item with $params as `'datatype' =&gt; 'podname', 'tbl_row_id' =&gt; $item_id_to_export` (returns array of data - pick/file columns are arrays of their data) **(donation-funded by @gr0b1)**
* Added: PODS_STRICT_MODE constant to enable many features at once which are common settings for advanced developers including - Pagination defaults to off; Search defaults to off; PodAPI $params are auto-sanitized (stripslashes_deep if you already sanitized or are dealing with sanitized values in your $params)
* Added: Pod('pod_name', $params) ability to run findRecords straight away with one single line that also sets up the Pod object, $params must be an array
* Added: Option to use SQL_CALC_FOUND_ROWS or COUNT(*) for getting the total rows available (for use with pagination / Pod :: getTotalRows) setting 'calc_found_rows' or 'count_found_rows' to true in Pod :: findRecords $params (useful for complex queries on data)
* Added: Option to disable pagination altogether (separate from setting page to 1, but also forces page to be 1) in findRecords $params `'pagination' =&gt; false`
* Added: PODS_GLOBAL_POD_PAGINATION constant to globally disable pagination by setting the constant to false (can be renabled as needed in findRecords $params `'pagination' =&gt; true`)
* Added: PODS_GLOBAL_POD_SEARCH constant to globally disable search by setting the constant to false (can be renabled as needed in findRecords $params `'search' =&gt; true`)
* Added: PODS_GLOBAL_POD_SEARCH_MODE constant to globally set the search_mode to 'text', 'int', or 'text_like' (default 'int' which references field IDs) - can be overrided as needed in findRecords $params `'search_mode' =&gt; 'text'`)
* Added: PODS_DISABLE_EVAL constant to globally disable PHP eval() on PHP-enabled areas of Pods (Templates, Helpers, Pod Pages)
* Added: PODS_WP_VERSION_MINIMUM constant to disable WP minimum version requirement
* Added: PODS_PHP_VERSION_MINIMUM constant to disable PHP minimum version requirement
* Added: PODS_MYSQL_VERSION_MINIMUM constant to disable MySQL minimum version requirement
* Added: Pod :: getRowNumber() to get current row number and Pod :: row_number variable to internally be used to keep track of which row_number you're on in findRecords loop (incremented in fetchRecord)
* Added: Pod :: raw_sql contains SQL without @ table references replaced and Pod :: sql now should reflect the same query as hits the DB (@ table references replaced)
* Added: Pod :: getZebra() which uses a switch (Pod :: zebra) that goes from false to true during fetchRecord loops (initial value is false, first fetch switches it to true and reverses each additional fetch)
* Added: PodAPI :: save_template / save_page / save_helper now allow 'name' / 'uri' to be renamed on save (only in API, not UI)
* Added: PodAPI :: save_pod_item now accepts an array for $params-&gt;tbl_row_id which will let you save multiple items at a time using the rest of the $params
* Added: PodAPI :: delete_pod_item now accepts an array for $params-&gt;tbl_row_id which will let you delete multiple items at a time
* Added: Having trouble updating Pods but you know things should be OK (advanced users)? Try adding ?pods_bypass_update=1 to the page URL you're on to bypass the update
* Added: Pagination / Filters to pods_shortcode (ex. `[pods name="mypod" limit="15" pagination="1" pagination_label="Go to page:" pagination_location="after" filters="status,category" filters_label="Filter:" filters_location="before"]`)
* Added: pods_page_templates filter to get $page_templates for use in Pod Page editor, which allows support for Pods built into **iThemes Builder** coming soon (to select layouts)
* Added: When using pods_query and setting $error to false, will bypass die on MySQL error
* Added: When using Pods UI as an admin (manage_options capability), add 'debug=1' to the URL to see the currently used SQL query for a manage screen
* Added: pods_manage now returns $object
* Added: Sort classes now used to show current sort direction
* Added: PodAPI :: load_column now accepts 'name' and 'datatype' (id) parameters for lookup instead of only just 'id' of field
* Added: PodAPI :: load_helper now accepts 'type' parameter for lookup instead of only just 'id' and 'name' of helper
* Added: New function 'pods_function_or_file' that checks if a function or file exists based on a number of locations, used for Helpers / Templates / Pod Pages, filter available called 'pods_function_or_file' if you want to customize further
* Changed: Pod Page Precode now runs on 'after_setup_theme' action instead of 'plugins_loaded'
* Changed: pods_generate_key / pods_validate_key revamped to work off of wpnonce, though $_SESSION is still used for holding the columns from that form usage
* Changed: pods_sanitize now sanitizes keys (previously only values)
* Changed: Now using wp_hash instead of md5 to get the hash of a value
* Changed: PODS_VERSION_FULL removed and PODS_VERSION now set as real point version (ex. `1.12`), updated all checks for version to use PHP version_compare
* Changed: input_helper in column options returns only value instead of the actual 'phpcode' now during publicForm, which then enables file-based / function-based checks during input_field.php loop
* Changed: pods_unique_slug to work more efficiently
* Removed: $this-&gt;wpdb from Pod class (just a vestige of the past, now using global $wpdb)
* Removed: PodAPI / UI References to old Pods Menu functionality
* Fixed: jQuery Sortable include fix
* Fixed: WP 3.3 errors fixed in regards to new WP Editor API for TinyMCE (via @azzozz)
* Fixed: Tightened up uninstall.php and when it can be run to avoid accidental uninstalls (Reminder: When you delete Pods (and other plugins) within WP, you'll delete the files AND your data as we follow the WP Plugin data standard for uninstalling)
* Fixed: Pods &gt;&gt; Setup UI updated with lots of fixes when editing Pods / Columns, and Helpers (no more refreshes needed where they may have been needed before)
* Fixed: PodAPI setting of defaults for $params to avoid isset checks
* Fixed: PodAPI :: save_column now sets pick-related extra data to empty if not a pick column
* Fixed: Pod :: getRecordById() now gets all of the same data as findRecords pulls in (pod_id, created, modified)
* Fixed: pods_url_variable references updated to pods_var
* Fixed: SQL cleaned up (extra line breaks removed so it's not as ugly) and standardized to escape field names in SQL references

= 1.11 - August 12, 2011 =
* Improved: MySQL performance enhanced with a number of MySQL indexes and column type tweaks, your DB will be automatically upgraded for you
* Added: PodInit :: setup now has filters / actions that run before / after install and updates
* Added: PodInit :: setup now explicitly sets CHARSET / COLLATE options as defined in wp-config ($wpdb->charset / $wpdb->collate)
* Added: PodInit :: precode now runs action 'pods_page_precode' after a Pod Page's precode is run (if any) and allows you to intercept the global $pods variable to force a Pod Page to stop running (issue a 404 by $pods = 404;) and other modifications to $pods global
* Added: PodInit :: admin_menu now checks if PODS_DISABLE_ADMIN_MENU is defined and set to true, which will hide all of the Pods menus (except for top-level Pod menus)
* Added: PodInit :: wp_head now checks if PODS_DISABLE_VERSION_OUTPUT is defined and set to true, which will hide the Pods version from Pod Pages
* Added: Set Meta Property Tags in your Pod Page precode, just setup $pods on your Pod object, and assign $pods->meta_properties as an array with any other meta property tags you want put on that page (useful for quick and dynamic meta tags dependant on Pod information)
* Fixed: Pods UI bug with filters / searches not working fixed, added a $strict variable to pods_ui_var and pods_var for clearer values (strict mode returns default if value found is empty)
* Fixed: pods_var bug with strtolower PHP warning when managing content
* Fixed: PodAPI :: export_package now removes sister_field_id from field data being exported as it could cause issues with incorrect bi-directional mapping to fields on reimport (expect to rebuild bi-directional field relationships upon import of packages going forward)
* Fixed: PodAPI :: save_column now reserves the field names 't' and 'p' for internal use as aliases
* Fixed: Pod :: lookup_row_ids now forces (int) on values given, also allows $tbl_row_ids to be array instead of only just comma-separate string as before
* Fixed: Pod :: findRecords now looks in 'select' parameter to find fields that need to be included
* Fixed: Pod :: showform now forces (int) on explicitly values that hit the DB
* Fixed: Various PHP notice fixes and query updates to improve performance (to maximize performance on custom queries, update wp_pod queries to use datatype first then tbl_row_id if it's not already in WHERE statements, do same for wp_pod_rel on field_id and pod_id)

= 1.10.7 - August 9, 2011 =
* Fixed: /ui/ajax/api.php which added extra slashes unnecessarily

= 1.10.6 - August 9, 2011 =
* Added: pods_var function to replace pods_url_variable (better name, more functionality), now handles URL segments, $_GET, $_POST, $_SESSION, $_COOKIE, $_SERVER, CONSTANT, User meta, custom arrays, and custom objects - also added a $default option to set what it should default to if not found (default: null) - also added a $allowed option to set what values are allowed to be returned, if $output is not $allowed ($allowed is array and not in $allowed OR $allowed is not array and does not equal $allowed) then $default is returned
* Added: pods_var_set function to set variables, operates similar to pods_var, has three variables ($value, $key, $type) and returns $value on success (if $type is an array or object it will return the updated $type, if $type is 'url' it will return the full updated $url)
* Fixed: Now using get_current_url() and parse_url to get path versus $_SERVER['REQUEST_URI']
* Fixed: Replaced mysql_real_escape_string usage with esc_sql
* Fixed: Backslashes being automatically added and causing issues with additional urlencoding and esc_attr usage

= 1.10.5 - August 9, 2011 =
* Added: $pods->meta_extra now outputs after the meta tags when wp_head runs in case you want to output one-off meta tags for a specific page using pre-code without extra WP functions
* Fixed: When adding a helper, it will now be added to the 'input helper' drop-down too
* Fixed: Pods non-top-level management has session filters turned off now by default
* Fixed: Taxonomy PICK unique values handler fixed to reference 't.term_id' instead of just 'id'
* Fixed: Pagination now using esc_url correctly, which wasn't being used right in 1.10.4
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.pods.io/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.4 - August 1, 2011 =
* Fixed: Pods UI was breaking 'view' links
* Fixed: Pods UI reordering fixed
* Fixed: Better errors for when a Pod doesn't exist to replace SQL errors
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.pods.io/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.3 - July 30, 2011 =
* Fixed: Shortcode 'where' parameter fixed
* Fixed: Body Class for Pod Pages not replacing / with - correctly and leaving an extra - at the end with wildcards
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.pods.io/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.2 - July 29, 2011 =
* Added: Moved the demo.php file from the Pods UI plugin over as pods-ui-demo.php and can now be found distributed with this plugin in the /demo/ plugin.
* Fixed: PHP error with new Version to Point function
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.pods.io/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.1 - July 28, 2011 =
* Added: New wp_pod and wp_pod_tbl_* table fix tool under Pods >> Setup >> Settings tab to resync your wp_pod table (clear orphans in wp_pod, and sync from wp_pod_tbl_* to wp_pod what doesn't already exist) - Useful for those who imported directly to wp_pod_tbl_* but forgot to import into wp_pod
* Added: Set Meta Tags in your Pod Page precode, just setup $pods on your Pod object, and assign $pods->meta as an array with 'description', 'keywords', or any other meta tags you want put on that page
* Added: Set Title Tag via $pods->meta['title'] (see Meta Tags feature listed directly above) which overrides what you might have in your Pod Page Title field
* Added: Set Body Classes via $pods->body_classes (as a string, like $pods->body_classes = 'one-class another-class')
* Added: Dynamically set your Pod Page template via $pods->page_template to set the filename, compatible with parent / child themes (fallback on currently selected Pod Page template, pods.php or default output)
* Improved: Added many new filters / actions to PodInit functions for advanced customization via other plugins
* Improved: On duplicate, Pods UI will now show the 'Add Another' and 'Add another based on this item' links again (instead of only on first add but not after duplicating)
* Improved: PodAPI :: save_pod now returns the $pod data (via PodAPI :: load_pod) if $params->return_pod is set to true
* Fixed: Pod :: getFilters now using the correct 'label' for the search button
* Fixed: On uninstall, now deleting options WHERE option_name LIKE 'pods_%'
* Fixed: Various minor bug fixes
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.pods.io/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10 - July 28, 2011 =
* Added: Full revamped UI for Content Management via Pods UI (you no longer need two separate plugins - Pods and Pods UI)
* Added: TinyMCE is now the default visual editor in WP Admin pages (nicEdit remains default for frontend forms), you can still use the newly updated TinyMCE for Pods package to customize this
* Improved: Added many new filters across functions.php for advanced customization via other plugins
* Improved: Admin Notices are now shown if you aren't running the minimum version of WordPress, PHP, and/or MySQL
* Improved: Updated version handling of PODS_VERSION
* Fixed: Updated Pod :: get_dropdown_values to work off of $params object instead of the old ${$key} = $value
* Fixed: Updated Pod :: findRecords to check if 'select' from $params is empty (and only to set it if it's not empty)
* Fixed: Updated Pod :: findRecords to cast (int) on certain values set via $params
* Fixed: Updated Pod :: findRecords to INNER JOIN the wp_pod_tbl_podname table prior to other joins (now you can reference t.field_name in your custom 'join' from $params)
* Fixed: Updated Pod :: getFilters to use the field label (if set), falling back on name (used to only be based on name)
* Fixed: publicForm now sets columns explicitly to those that exist instead of passing null if fields not set
* Fixed: PodAPI :: __construct now uses PodAPI :: load_pod to setup the pod and it's fields instead of doing the calls itself
* Fixed: PodAPI :: load_pod simplified
* Fixed: PodInit :: init now checks if WP_DEBUG is on in addition to if headers_sent to set session_start()
* Fixed: Moved Package Manager to below Setup and above Manage Content to keep Manage Content next to the Add podname sub-menu items
* Fixed: Pods >> Setup >> Pods tab updated so when you add/remove Pods it will adjust the related to drop-down in field settings (previously it didn't show it until you refreshed the page)
* Fixed: Pods >> Setup >> Pods tab under Field settings section updated to show/hide relevant fields if a pick (or not)
* Fixed: Pods >> Setup >> Pods tab to reset the Pod settings fields correctly when switching to different Pods or adding / removing them
* Fixed: Pods >> Setup >> Helpers tab updated so when you add/remove Helpers it will adjust the pre/post save/drop helpers drop-downs in the Pods tab (previously it didn't show it until you refreshed the page)
* Fixed: Pods >> Setup >> Helpers tab updated so when you add Helpers it will set the helper_type in parentheses correctly (previously it didn't show it until you refreshed the page)
* Fixed: misc.php updated to work off of $params object instead of the old ${$key} = $value
* Fixed: Updated TinyMCE $wp_editor (developed by @azaozz) to hide WP 3.2 fullscreen buttons and only show native TinyMCE fullscreen button
* Fixed: Various PHP notice fixes and escape/sanitization on output across Pods
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.pods.io/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.9.8 - July 24, 2011 =
* Added: New Option to override existing packages during package import
* Added: Pods and additional database calls are not initiated (but you can run the code as the files are included) when SHORTINIT is defined and set to true (also does not load much of WP itself)
* Added: Pods will now check the version of Pods a package was exported from and display notices if it 'might' be incompatible (based on the minor version in major.minor.patch), and an additional two variables (compatible_from and compatible_to) are available within the 'meta' array which will get utilized in the new Pods site revamp within the Package Directory
* Improved: Enhanced display / error information and implementation for package import
* Fixed: Package export bug that generated an 'empty' package when you click 'Export' without anything selected
* Fixed: No longer calling $pods_roles immediately, only used when needed in the code
* Fixed: &$referenced the $pods_cache variable to $cache for backwards compatibility - use $pods_cache going forward
* Fixed: Minor PHP warnings/notices that come up when WP_DEBUG is defined and set to true
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.7.4 - July 14, 2011 =
* Fixed: esc_html replaced with esc_textarea for Package Export and textarea usages to prevent breaking html entities
* Fixed: Errors when you enter a field that doesn't exist, for inclusion in a publicForm

= 1.9.7.3 - July 6, 2011 =
* Fixed: Uploader cookie-handling for advanced usage (1.9.7.2 wouldn't appear on wp.org)

= 1.9.7.1 - July 6, 2011 =
* Fixed: Fix for relationships / file saving (error 500 fix)

= 1.9.7 - July 5, 2011 =
* Added: 'having' parameter to Pod :: findRecords
* Added: #spacer_$name ID is now set on the spacer (div.pods_form div.clear) directly after a field) for clean UI when utilizing advanced CSS / jQuery usage; Also increased spacing by 5px
* Improved: Increased integer limits on IDs throughout the database to allow for more (or just higher ID #'s)
* Improved: File Uploader now links after upload, instead of only on loading a form with existing files (or after saving)
* Fixed: Now looking at 'groupby' parameter for any additional PICK fields to be JOINed
* Fixed: PodAPI :: fields now gets label in addition to name
* Fixed: Sometimes when a non integer is sent, SQL errors show up (but not a sanitization issue, it was a casting issue)
* Fixed: Using esc_html in place of htmlentities (out with the old, in with the standards, more in 2.0)
* Fixed: Now explicitly sending content encoding type (based on WP settings) in AJAX returns
* Fixed: TinyMCE API update from @azaozz with additional WP 3.2 support
* Fixed: File Upload field now checks if user has access to upload and/or browse before showing the UI for those (regardless, access when trying to actually use the UI before was still closed off)
* Fixed: Removed htaccess.txt which was no longer referenced or used
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.3 - June 24, 2011 =
* Fixed: JS optimization and fixes for nicEdit (also now no longer outputting pods-ui.js on every page)
* Fixed: Non Top-level menu Pods now appearing in alphabetical order under Pods menu
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.2 - June 23, 2011 =
* Fixed: TinyMCE API update from @azaozz with additional WP 3.2 support
* Fixed: Pod Page Precode $pods = 404; bug that wouldn't produce the default WordPress 404 error page
* Fixed: Fix for nicEdit JS error during init that breaks forms (when on a non top-level menu Pod AJAX-loaded form)
* Fixed: Fix for PICK error during save that errors out trying to save selections as 'undefined' (when on a non top-level menu Pod AJAX-loaded form)
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.1 - June 23, 2011 =
* Fixed: Fix for nicEdit JS error during init that breaks forms
* Reminder: 1.9.6 Security Update information can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6 - June 22, 2011 =
* Full Details can be found at: http://dev.pods.io/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Security Update: New security settings section in the Pods >> Setup >> Settings tab to restrict access to the File Browser / Uploader used in publicForm, adjust the settings to fit your site if you experience any problems
* Added: New TinyMCE API for use with the new TinyMCE package update at http://pods.io/packages/tinymce-for-pods/
* Added: New get_current_url() function to get current page URL
* Fixed: pod_page_exists() bug with $home path
* Fixed: publicForm bug with $css_id always using form_count 1 (now uses correct $form_count)
* Fixed: Access to Pod Pages tab in Pods >> Setup menu (manage_pages >> manage_pod_pages)
* Fixed: Added $params and $columns to actions for pods_pre_save_pod_item and pods_post_save_pod_item in PodAPI :: save_pod_item
* Fixed: Moved $params->pod_id and $params->tbl_row_id setup to above pre_save_helpers run in PodAPI :: save_pod_item
* Fixed: tbl_row_id now sent in publicForm (not just pod_id)
* Fixed: WP 3.2 bugs regarding jQuery upgrade to 1.6.1
* Fixed: PHP warnings dependant on error_reporting level and PHP version

= 1.9.5.1 - April 7, 2011 =
* Fixed: dot-traversal in Pod :: get_field

= 1.9.5 - April 7, 2011 =
* Added: Multisite (3.0+) Network Activation support - Now when you click Network Activate, Pods will install itself correctly across all sites (and new sites as they are added)
* Added: Third option "$thankyou_url" in publicForm($columns, $label, $thankyou_url) which changes what happens when a form is submitted
* Added: Pod :: findRecords - New 'total' variable separate from getTotalRows(), call $record->total to get the number of results in _current_ query
* Added: For sites that don't use Pod Pages, there is now a new check if defined('PODS_DISABLE_POD_PAGE_CHECK') to disable the Pod Page check on each page load
* Improved: Drop / Reset Pod now does a little validations on ID / Name of Pod
* Fixed: File Uploads failed to save to the Pod item when themes / plugins output erroneous whitespace during their init
* Fixed: Various PHP warnings cleaned up (was showing when WP_DEBUG and other debugging is turned on)

= 1.9.4 - October 20, 2010 =
* Fixed: Pod Pages - Only match Pod Page URIs that match current depth (specifically when using wildcards)
* Fixed: $groupby referenced but not used - and $orderby should be allowed to be empty (gives error if it is)
* Fixed: Allow Pod Pages on domains other than contained in wpurl
* Fixed: Pod :: get_dropdown_values wp_taxonomy Filter Query fix

= 1.9.3.1 - October 4, 2010 =
* Fixed / Added: Pod :: findRecords - Add param for groupby since where is now surrounded in ( ) - resolving the issue introduced in 1.9.3
* Fixed: Pod :: findRecords - Filtering should run through $search variable instead of $where

= 1.9.3 - October 1, 2010 =
* Fixed: PodAPI :: csv_to_php - Field Name not un-escaped like Field Values (quotes)
* Fixed: Pod :: findRecords - $limit / $where / etc should only run if $sql is empty
* Fixed: Pod :: findRecords - $where (if not empty) should be surrounded in parethesis
* Fixed: mysql_real_escape_string - Needs an identifier to avoid PHP warnings
* Fixed: $this->page should be no lower than 1
* Fixed: PodAPI :: load_pod_item - Undefined Property fix
* Fixed: Manage Pods - JS Error with .length on null var
* Fixed: Manage Content - Browse / Edit tabs + Filtering fixes
* Fixed: Pod :: publicForm - CSS .hidden not targeted in stylesheet
* Fixed: PodInit :: body_class - Pulling REQUEST_URI instead of Pod Page URI
* Fixed: PodInit :: init - htaccess check not necessary, not all users will use Pod Pages

= 1.9.2.2 - September 23, 2010 =
* Fixed: Older method of array('datatype'=>'x','columns'=>array('name','other_col'),'name'=>$name,'other_col'=>$other_col) with save_pod_item now work when saving (to allow an easier upgrade path for those using already built code that utilize it)

= 1.9.2.1 - September 23, 2010 =
* Fixed: Adding / Editing items weren't saving properly

= 1.9.2 - September 23, 2010 =
* Added: Ability to use filters / actions to add new Column Types to Pods
* Added: Filters - pods_admin_menu_name / pods_admin_menu_label / pods_admin_submenu_name / pods_admin_submenu_label / pods_rel_lookup / pods_get_dropdown_values / pods_findrecords_the_join / pods_findrecords_join / pods_showform_save_button_atts / pods_showform_save_button / pods_column_dbtypes / pods_column_types
* Added: Actions - pods_pre_pod_helper / pods_pre_pod_helper_$helper / pods_post_pod_helper / pods_post_pod_helper_$helper / pods_pre_showtemplate / pods_pre_showtemplate_$tpl / pods_post_showtemplate / pods_post_showtemplate_$tpl / pods_pre_input_field / pods_pre_input_field_$name / pods_pre_input_field_type_$coltype / pods_input_field_type_$coltype / pods_post_input_field / pods_post_input_field_$name / pods_post_input_field_type_$coltype / pods_pre_form / pods_pre_form_{Pod :: datatype} / pods_post_form / pods_post_form_{Pod :: datatype}
* Added: Automatic File Column Upgrade during DB Update from Pods below version 1.7.6
* Added: Pod :: findRecords($params) can now be used where $params is an key/value array containing 'select' (t.*, p.id AS pod_id, p.created, p.modified), 'where' (null), 'join' (empty), 'orderby' (t.id DESC), 'limit' (15), 'page' (Pod :: page), 'search' (Pod :: search), and 'sql' (null) for future proofing variable expansion
* Added: save_pod_item has a new var in $params to be used - bypass_helpers (default: true) which can be set to false to not run any pre/post save helpers
* Improved: Parent / Child Theme integration uses core WP functions to lookup templates
* Improved: pods_access now uses current_user_can for 'administrator' role check, converts $method to upper case, also looks for a capability of pods_administrator for full access
* Improved: DB Update code revised
* Improved: Using $wpdb->tablename format for WP Core table names in all code
* Improved: PodAPI :: import now checks if the $data is an array of items or if it's a single-item array
* Improved: Input fields have name attribute * Added to them (except multi-select pick field which works off of a div and the file upload field)
* Fixed: File Upload field checks version of WP to get correct button height
* Fixed: PodAPI :: import and pick values work correctly now
* Fixed: PodAPI :: save_pod_item works with tbl_row_id parameter correctly now
* Fixed: PodAPI :: reset_pod works correctly now
* Fixed: PodAPI :: drop_pod_item works with tbl_row_id parameter correctly now
* Fixed: pods_url_variable now removes the hash (#) part of the url - On a side note, avoid use of pods_url_variable(-1) and other negative numbers as it is not always the level you expect in wildcard Pod Pages
* Fixed: Revised AJAX-based drop_pod_item access check, you can now drop an item if a user has pod_$podname access but NOT manage_content access (previously denied)
* Fixed: Date Input offset uses this.input.position() instead of this.input.offset() now
* Fixed: Pod Page Template select gets/saves page.php correctly now when page.php doesn't have a Template Name
* Fixed: File Browser display CSS fix
* Deprecated: Instead of using wp_users you should use $wpdb->users (along with other Core WP table names)

= 1.9.1 - August 13, 2010 =
* Added: Support for Multisite Environment URLs and Super Admin role
* Added: Filters for Manage Tabs (to allow Pods UI to enhance these areas)
* Added: page.php now appears as "Page (WP Default)" in the Page Template list if page.php has no "Template Name" and exists in the theme (previously did not show up)
* Added: $is_new_item to save_pod_item() in PodAPI for use in Pre-save and Post-save Helpers -- $is_new_item = true if adding an item, $is_new_item = false if editing an item
* Fixed: drop_pod() in PodAPI function reference fix
* Fixed: validate_package() in PodAPI assumed array, now it checks if the $data is an array

= 1.9.0 - July 29, 2010 =
* Added: Integration with body_class() - When on a Pod Page, two classes are added: pods, pod-page-URI-GOES-HERE; and if $pods is defined as a Pod another is added: pod-POD-NAME-GOES-HERE
* Added: pods_admin css class to wrap divs in Admin UI
* Added: New Pods Icon set for primary Pods menu and Pods Setup heading
* Added: pods_api_$action filter runs before $action runs in AJAX API operations
* Added: Support for tbl_row_id in save_pod_item, drop_pod_item, and load_pod_item params as alternative to pod_id (to eventually fully replace pod_id support)
* Added: reset_pod() added to PodAPI class to delete all Pod Items from a Pod without deleting the Pod itself
* Added: reorder_pod_item() added to PodAPI class to quickly and easily mass edit a number field for reordering purpose
* Added: Bulk save_pod_item() operations added in PodAPI class with new 'data' parameter ('data' should contain an array of 'columns' arrays)
* Added: Files previously uploaded will now be linked to the location in the file list for a column
* Improved: New $api->snap variable can be set to true in PodAPI class to silence all die() functions and throw them as Exceptions to improve API-based operations in advanced setups
* Improved: pod_query() now trims $sql once instead of three times
* Improved: pod_page_exists() now has a $uri parameter to pull data on a Pod Page at another URI than REQUEST_URI gives
* Improved: pods_access() now supports checking multiple at a time with addition of second parameter $method (AND/OR) and accepting an array for $priv
* Improved: Admin UI / Form Fields now have maxlength attributes on input fields with length restrictions
* Improved: Extended maximum length for Helper names, Template names, and and Field Comments to 255 characters
* Improved: Made Debug Information on Settings tab easier to read
* Improved: drop_pod() in PodAPI class now clears Pod items in a more efficient way
* Changed: DB update trims all Pod Pages of their beginning and trailing slashes "/" which previously were allowed but are now stripped during the saving process (normalization)
* Changed: save_page() in PodAPI class now strips beginning and trailing slashes "/" from URI before save
* Changed: Moved Package operations into PodAPI class
* Changed: Moved jqmWindow in Admin UI into wrap div and the pods_form div in the content form
* Changed: PodAPI class now returns all IDs instead of die("$id")
* Changed: import() in PodAPI class now uses save_pod_item() which gives it full support for bi-directional relationships
* Fixed: load_pod_item() in PodAPI class no longer interferes with input helpers access to the data of a Pod Item as the Pod class is now initiated with an $id
* Fixed: api.php now requires manage_pods priv to run load_sister_fields action
* Fixed: Menu now runs after most plugins to avoid conflicts
* Fixed: Menu no longer shows to any user, checks access via Pod roles
* Fixed: pod_query() now checks against FOUND_ROWS() instead of FOUND ROWS() to cache or not
* Fixed: style.css now uses the .pods_admin and .pods_form class selectors for each style defined to avoid overwriting other element styles on a page
* Removed: package.php has been removed from AJAX operations as code has been moved into PodAPI class

= 1.8.9 - July 7, 2010 =
* Changed: Minor UI changes
* Changed: author_id now getting stored
* Fixed: Add / Edit javascript fix

= 1.8.8 - May 23, 2010 =
* Fixed: bi-directional relationships

= 1.8.7 - April 16, 2010 =
* Fixed: error when editing a unique field
* Fixed: API handling for drop_pod_item

= 1.8.6 - April 14, 2010 =
* Fixed: saving an empty pick column throws an error

= 1.8.5 - April 13, 2010 =
* Changed: save_pod_item improvements, see http://bit.ly/d4EWDM
* Changed: proper PHPdoc commenting
* Fixed: timezone issues
* Added: ability to override pager var ($this->page_var)
* Added: load_helper, load_pod, load_template, drop_helper, drop_pod, drop_template methods support the "name" field as well as the id
* Added: load_page, drop_page methods support the "uri" field as well as the id