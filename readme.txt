=== Pods CMS Framework ===
Contributors: sc0ttkclark, logikal16, jchristopher
Donate link: http://podsfoundation.org/donate/
Tags: pods, cms, cck, pods ui, ui, content types, custom post types, relationships, database, framework, drupal, mysql, custom content, php
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 1.11

Pods is a CMS framework for creating, managing, and deploying customized content types.

== Description ==

Check out http://podscms.org/ for our User Guide and many other resources to help you develop with Pods.

= Create your own content types =
A Pod is a content type which contains a user-defined set of fields. Each content type is stored in it's own table, where as WordPress Custom Post Types are normally all stored in one single table for them all.

Create a variety of different fields including: text, paragraph text, date, number, file upload, and relationship (called "pick") fields.

Pick fields are useful if you want to create relationships between your content types. One example is if you want to relate an "event" with one or more "speaker".

= Easily display your content =
There are several ways to get Pods data to show up throughout your site:

* Add Pod Pages from within the admin area. Pod Pages support PHP and Wildcard URLs. For example, the Pod Page "events/*" will be the default handler for all pages beginning with "events/". This allows you to have a single page to handle a myriad of different items.
* Add PHP code directly into your WP template files, or wherever else PHP is supported.
* Use shortcode to display lists of Pod items or details of a Pod item within WP Pages or Posts.
* The Pods API allows you to retrieve raw data from and save data to the database.

= Customized Management Panels =
Utilize Pods UI (included in Pods 1.10+) to build your own Custom Management panels for your Pods.

= Migrate! =
Pods includes a Package Manager, which allows you to import/export Pods (structure-only, no data yet), Pod Templates, Pod Pages, and/or Pod Helpers. You can select which features you want to "package up" and export it for easy migration to other sites or to share your code with other users in our Package Directory.

Pods also includes an easy to use PHP API to allow you to import and export your data via CSV, and other more complex operations.

= Introduction to the Pods CMS Framework =
[vimeo http://vimeo.com/15086927]

= Stay tuned for Pods 2.0 =
Pods 2.0 is around the corner, so keep up-to-date by following our @podscms twitter account or checking out our Pods Development blog at http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

Features coming in Pods 2.0 include:

* Completely revamped UI
* Pods UI refactoring / revamp
* Create and Manage Custom Post Types
* Create and Manage Custom Taxonomy
* Easy migration between Custom Post Types and Custom Content Types (Standalone Pods)
* Many more field types and advanced options (less code for you to do!)
* Many MySQL optimizations and performance tweaks
* Full i18n support
* and more features which can be found at: http://dev.podscms.org/pods-2-0/
* Pods 2.0 and How You Can Help: http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP plugin page)
1. Activate this plugin

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

== Changelog ==

= 1.11 - August 12, 2011 =
* Improved: MySQL performance enhanced with a number of MySQL indexes and column type tweaks, your DB will be automatically upgraded for you
* Added: PodInit :: setup now has filters / actions that run before / after install and updates
* Added: PodInit :: setup now explicitly sets CHARSET / COLLATE options as defined in wp-config ($wpdb->charset / $wpdb->collate)
* Added: PodInit :: precode now runs action 'pods_page_precode' after a Pod Page's precode is run (if any) and allows you to intercept the global $pods variable to force a Pod Page to stop running (issue a 404 by $pods = 404;) and other modifications to $pods global
* Added: PodInit :: admin_menu now checks if PODS_DISABLE_ADMIN_MENU is defined and set to true, which will hide all of the Pods menus (except for top-level Pod menus)
* Added: PodInit :: wp_head now checks if PODS_DISABLE_VERSION_OUTPUT is defined and set to true, which will hide the Pods version from Pod Pages
* Added: Set Meta Property Tags in your Pod Page precode, just setup $pods on your Pod object, and assign $pods->meta_properties as an array with any other meta property tags you want put on that page (useful for quick and dynamic meta tags dependant on Pod information)
* Bugfix: Pods UI bug with filters / searches not working fixed, added a $strict variable to pods_ui_var and pods_var for clearer values (strict mode returns default if value found is empty)
* Bugfix: Fixed pods_var bug with strtolower PHP warning when managing content
* Bugfix: PodAPI :: export_package now removes sister_field_id from field data being exported as it could cause issues with incorrect bi-directional mapping to fields on reimport (expect to rebuild bi-directional field relationships upon import of packages going forward)
* Bugfix: PodAPI :: save_column now reserves the field names 't' and 'p' for internal use as aliases
* Bugfix: Pod :: lookup_row_ids now forces (int) on values given, also allows $tbl_row_ids to be array instead of only just comma-separate string as before
* Bugfix: Pod :: findRecords now looks in 'select' parameter to find fields that need to be included
* Bugfix: Pod :: showform now forces (int) on explicitly values that hit the DB
* Bugfix: Various PHP notice fixes and query updates to improve performance (to maximize performance on custom queries, update wp_pod queries to use datatype first then tbl_row_id if it's not already in WHERE statements, do same for wp_pod_rel on field_id and pod_id)

= 1.10.7 - August 9, 2011 =
* Bugfix: Fix for /ui/ajax/api.php which added extra slashes unnecessarily

= 1.10.6 - August 9, 2011 =
* Added: pods_var function to replace pods_url_variable (better name, more functionality), now handles URL segments, $_GET, $_POST, $_SESSION, $_COOKIE, $_SERVER, CONSTANT, User meta, custom arrays, and custom objects - also added a $default option to set what it should default to if not found (default: null) - also added a $allowed option to set what values are allowed to be returned, if $output is not $allowed ($allowed is array and not in $allowed OR $allowed is not array and does not equal $allowed) then $default is returned
* Added: pods_var_set function to set variables, operates similar to pods_var, has three variables ($value, $key, $type) and returns $value on success (if $type is an array or object it will return the updated $type, if $type is 'url' it will return the full updated $url)
* Bugfix: Now using get_current_url() and parse_url to get path versus $_SERVER['REQUEST_URI']
* Bugfix: Replaced mysql_real_escape_string usage with esc_sql
* Bugfix: Fixed backslashes being automatically added and causing issues with additional urlencoding and esc_attr usage

= 1.10.5 - August 9, 2011 =
* Added: $pods->meta_extra now outputs after the meta tags when wp_head runs in case you want to output one-off meta tags for a specific page using pre-code without extra WP functions
* Bugfix: When adding a helper, it will now be added to the 'input helper' drop-down too
* Bugfix: Pods non-top-level management has session filters turned off now by default
* Bugfix: Taxonomy PICK unique values handler fixed to reference 't.term_id' instead of just 'id'
* Bugfix: Pagination now using esc_url correctly, which wasn't being used right in 1.10.4
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.4 - August 1, 2011 =
* Bugfix: Pods UI was breaking 'view' links
* Bugfix: Pods UI reordering fixed
* Bugfix: Better errors for when a Pod doesn't exist to replace SQL errors
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.3 - July 30, 2011 =
* Bugfix: Shortcode 'where' parameter fixed
* Bugfix: Body Class for Pod Pages not replacing / with - correctly and leaving an extra - at the end with wildcards
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.2 - July 29, 2011 =
* Added: Moved the demo.php file from the Pods UI plugin over as pods-ui-demo.php and can now be found distributed with this plugin in the /demo/ plugin.
* Bugfix: Fixed PHP error with new Version to Point function
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.1 - July 28, 2011 =
* Added: New wp_pod and wp_pod_tbl_* table fix tool under Pods >> Setup >> Settings tab to resync your wp_pod table (clear orphans in wp_pod, and sync from wp_pod_tbl_* to wp_pod what doesn't already exist) - Useful for those who imported directly to wp_pod_tbl_* but forgot to import into wp_pod
* Added: Set Meta Tags in your Pod Page precode, just setup $pods on your Pod object, and assign $pods->meta as an array with 'description', 'keywords', or any other meta tags you want put on that page
* Added: Set Title Tag via $pods->meta['title'] (see Meta Tags feature listed directly above) which overrides what you might have in your Pod Page Title field
* Added: Set Body Classes via $pods->body_classes (as a string, like $pods->body_classes = 'one-class another-class')
* Added: Dynamically set your Pod Page template via $pods->page_template to set the filename, compatible with parent / child themes (fallback on currently selected Pod Page template, pods.php or default output)
* Improved: Added many new filters / actions to PodInit functions for advanced customization via other plugins
* Improved: On duplicate, Pods UI will now show the 'Add Another' and 'Add another based on this item' links again (instead of only on first add but not after duplicating)
* Improved: PodAPI :: save_pod now returns the $pod data (via PodAPI :: load_pod) if $params->return_pod is set to true
* Bugfix: Pod :: getFilters now using the correct 'label' for the search button
* Bugfix: On uninstall, now deleting options WHERE option_name LIKE 'pods_%'
* Bugfix: Various minor bug fixes
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10 - July 28, 2011 =
* Added: Full revamped UI for Content Management via Pods UI (you no longer need two separate plugins - Pods and Pods UI)
* Added: TinyMCE is now the default visual editor in WP Admin pages (nicEdit remains default for frontend forms), you can still use the newly updated TinyMCE for Pods package to customize this
* Improved: Added many new filters across functions.php for advanced customization via other plugins
* Improved: Admin Notices are now shown if you aren't running the minimum version of WordPress, PHP, and/or MySQL
* Improved: Updated version handling of PODS_VERSION
* Bugfix: Updated Pod :: get_dropdown_values to work off of $params object instead of the old ${$key} = $value
* Bugfix: Updated Pod :: findRecords to check if 'select' from $params is empty (and only to set it if it's not empty)
* Bugfix: Updated Pod :: findRecords to cast (int) on certain values set via $params
* Bugfix: Updated Pod :: findRecords to INNER JOIN the wp_pod_tbl_podname table prior to other joins (now you can reference t.field_name in your custom 'join' from $params)
* Bugfix: Updated Pod :: getFilters to use the field label (if set), falling back on name (used to only be based on name)
* Bugfix: publicForm now sets columns explicitly to those that exist instead of passing null if fields not set
* Bugfix: PodAPI :: __construct now uses PodAPI :: load_pod to setup the pod and it's fields instead of doing the calls itself
* Bugfix: PodAPI :: load_pod simplified
* Bugfix: PodInit :: init now checks if WP_DEBUG is on in addition to if headers_sent to set session_start()
* Bugfix: Moved Package Manager to below Setup and above Manage Content to keep Manage Content next to the Add podname sub-menu items
* Bugfix: Pods >> Setup >> Pods tab updated so when you add/remove Pods it will adjust the related to drop-down in field settings (previously it didn't show it until you refreshed the page)
* Bugfix: Pods >> Setup >> Pods tab under Field settings section updated to show/hide relevant fields if a pick (or not)
* Bugfix: Pods >> Setup >> Pods tab to reset the Pod settings fields correctly when switching to different Pods or adding / removing them
* Bugfix: Pods >> Setup >> Helpers tab updated so when you add/remove Helpers it will adjust the pre/post save/drop helpers drop-downs in the Pods tab (previously it didn't show it until you refreshed the page)
* Bugfix: Pods >> Setup >> Helpers tab updated so when you add Helpers it will set the helper_type in parentheses correctly (previously it didn't show it until you refreshed the page)
* Bugfix: misc.php updated to work off of $params object instead of the old ${$key} = $value
* Bugfix: Updated TinyMCE $wp_editor (developed by @azaozz) to hide WP 3.2 fullscreen buttons and only show native TinyMCE fullscreen button
* Bugfix: Various PHP notice fixes and escape/sanitization on output across Pods
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.9.8 - July 24, 2011 =
* Added: New Option to override existing packages during package import
* Added: Pods and additional database calls are not initiated (but you can run the code as the files are included) when SHORTINIT is defined and set to true (also does not load much of WP itself)
* Added: Pods will now check the version of Pods a package was exported from and display notices if it 'might' be incompatible (based on the minor version in major.minor.patch), and an additional two variables (compatible_from and compatible_to) are available within the 'meta' array which will get utilized in the new Pods site revamp within the Package Directory
* Improved: Enhanced display / error information and implementation for package import
* Bugfix: Fixed package export bug that generated an 'empty' package when you click 'Export' without anything selected
* Bugfix: No longer calling $pods_roles immediately, only used when needed in the code
* Bugfix: &$referenced the $pods_cache variable to $cache for backwards compatibility - use $pods_cache going forward
* Bugfix: Fixed minor PHP warnings/notices that come up when WP_DEBUG is defined and set to true
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.7.4 - July 14, 2011 =
* Bugfix: esc_html replaced with esc_textarea for Package Export and textarea usages to prevent breaking html entities
* Bugfix: Fixed errors when you enter a field that doesn't exist, for inclusion in a publicForm

= 1.9.7.3 - July 6, 2011 =
* Bugfix: Fixed Uploader cookie-handling for advanced usage (1.9.7.2 wouldn't appear on wp.org)

= 1.9.7.1 - July 6, 2011 =
* Bugfix: Fix for relationships / file saving (error 500 fix)

= 1.9.7 - July 5, 2011 =
* Added: 'having' parameter to Pod :: findRecords
* Added: #spacer_$name ID is now set on the spacer (div.pods_form div.clear) directly after a field) for clean UI when utilizing advanced CSS / jQuery usage; Also increased spacing by 5px
* Improved: Increased integer limits on IDs throughout the database to allow for more (or just higher ID #'s)
* Improved: File Uploader now links after upload, instead of only on loading a form with existing files (or after saving)
* Bugfix: Now looking at 'groupby' parameter for any additional PICK fields to be JOINed
* Bugfix: PodAPI :: fields now gets label in addition to name
* Bugfix: Sometimes when a non integer is sent, SQL errors show up (but not a sanitization issue, it was a casting issue)
* Bugfix: Using esc_html in place of htmlentities (out with the old, in with the standards, more in 2.0)
* Bugfix: Now explicitly sending content encoding type (based on WP settings) in AJAX returns
* Bugfix: TinyMCE API update from @azaozz with additional WP 3.2 support
* Bugfix: File Upload field now checks if user has access to upload and/or browse before showing the UI for those (regardless, access when trying to actually use the UI before was still closed off)
* Bugfix: Removed htaccess.txt which was no longer referenced or used
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.3 - June 24, 2011 =
* Bugfix: JS optimization and fixes for nicEdit (also now no longer outputting pods-ui.js on every page)
* Bugfix: Non Top-level menu Pods now appearing in alphabetical order under Pods menu
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.2 - June 23, 2011 =
* Bugfix: TinyMCE API update from @azaozz with additional WP 3.2 support
* Bugfix: Fixed Pod Page Precode $pods = 404; bug that wouldn't produce the default WordPress 404 error page
* Bugfix: Fix for nicEdit JS error during init that breaks forms (when on a non top-level menu Pod AJAX-loaded form)
* Bugfix: Fix for PICK error during save that errors out trying to save selections as 'undefined' (when on a non top-level menu Pod AJAX-loaded form)
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.1 - June 23, 2011 =
* Bugfix: Fix for nicEdit JS error during init that breaks forms
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6 - June 22, 2011 =
* Full Details can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Security Update: New security settings section in the Pods >> Setup >> Settings tab to restrict access to the File Browser / Uploader used in publicForm, adjust the settings to fit your site if you experience any problems
* Added: New TinyMCE API for use with the new TinyMCE package update at http://podscms.org/packages/tinymce-for-pods/
* Added: New get_current_url() function to get current page URL
* Bugfix: Fixed pod_page_exists() bug with $home path
* Bugfix: Fixed publicForm bug with $css_id always using form_count 1 (now uses correct $form_count)
* Bugfix: Fixed access to Pod Pages tab in Pods >> Setup menu (manage_pages >> manage_pod_pages)
* Bugfix: Added $params and $columns to actions for pods_pre_save_pod_item and pods_post_save_pod_item in PodAPI :: save_pod_item
* Bugfix: Moved $params->pod_id and $params->tbl_row_id setup to above pre_save_helpers run in PodAPI :: save_pod_item
* Bugfix: tbl_row_id now sent in publicForm (not just pod_id)
* Bugfix: Fixed WP 3.2 bugs regarding jQuery upgrade to 1.6.1
* Bugfix: Fixed some PHP warnings dependant on error_reporting level and PHP version

= 1.9.5.1 - April 7, 2011 =
* Bugfix: Fixed dot-traversal in Pod :: get_field

= 1.9.5 - April 7, 2011 =
* Added: Multisite (3.0+) Network Activation support - Now when you click Network Activate, Pods will install itself correctly across all sites (and new sites as they are added)
* Added: Third option "$thankyou_url" in publicForm($columns, $label, $thankyou_url) which changes what happens when a form is submitted
* Added: Pod :: findRecords - New 'total' variable separate from getTotalRows(), call $record->total to get the number of results in _current_ query
* Added: For sites that don't use Pod Pages, there is now a new check if defined('PODS_DISABLE_POD_PAGE_CHECK') to disable the Pod Page check on each page load
* Improved: Drop / Reset Pod now does a little validations on ID / Name of Pod
* Bugfix: File Uploads failed to save to the Pod item when themes / plugins output erroneous whitespace during their init
* Bugfix: Various PHP warnings cleaned up (was showing when WP_DEBUG and other debugging is turned on)

= 1.9.4 - October 20, 2010 =
* Bugfix: Pod Pages - Only match Pod Page URIs that match current depth (specifically when using wildcards)
* Bugfix: $groupby referenced but not used - and $orderby should be allowed to be empty (gives error if it is)
* Bugfix: Allow Pod Pages on domains other than contained in wpurl
* Bugfix: Pod :: get_dropdown_values wp_taxonomy Filter Query fix

= 1.9.3.1 - October 4, 2010 =
* Bugfix / Added: Pod :: findRecords - Add param for groupby since where is now surrounded in ( ) - resolving the issue introduced in 1.9.3
* Bugfix: Pod :: findRecords - Filtering should run through $search variable instead of $where

= 1.9.3 - October 1, 2010 =
* Bugfix: PodAPI :: csv_to_php - Field Name not un-escaped like Field Values (quotes)
* Bugfix: Pod :: findRecords - $limit / $where / etc should only run if $sql is empty
* Bugfix: Pod :: findRecords - $where (if not empty) should be surrounded in parethesis
* Bugfix: mysql_real_escape_string - Needs an identifier to avoid PHP warnings
* Bugfix: $this->page should be no lower than 1
* Bugfix: PodAPI :: load_pod_item - Undefined Property fix
* Bugfix: Manage Pods - JS Error with .length on null var
* Bugfix: Manage Content - Browse / Edit tabs + Filtering fixes
* Bugfix: Pod :: publicForm - CSS .hidden not targeted in stylesheet
* Bugfix: PodInit :: body_class - Pulling REQUEST_URI instead of Pod Page URI
* Bugfix: PodInit :: init - htaccess check not necessary, not all users will use Pod Pages

= 1.9.2.2 - September 23, 2010 =
* Bugfix: Older method of array('datatype'=>'x','columns'=>array('name','other_col'),'name'=>$name,'other_col'=>$other_col) with save_pod_item now work when saving (to allow an easier upgrade path for those using already built code that utilize it)

= 1.9.2.1 - September 23, 2010 =
* Bugfix: Adding / Editing items weren't saving properly

= 1.9.2 - September 23, 2010 =
* Added: Ability to use filters / actions to add new Column Types to Pods
* Added: Filters - pods_admin_menu_name / pods_admin_menu_label / pods_admin_submenu_name / pods_admin_submenu_label / pods_rel_lookup / pods_get_dropdown_values / pods_findrecords_the_join / pods_findrecords_join / pods_showform_save_button_atts / pods_showform_save_button / pods_column_dbtypes / pods_column_types
* Added: Actions - pods_pre_pod_helper / pods_pre_pod_helper_$helper / pods_post_pod_helper / pods_post_pod_helper_$helper / pods_pre_showtemplate / pods_pre_showtemplate_$tpl / pods_post_showtemplate / pods_post_showtemplate_$tpl / pods_pre_input_field / pods_pre_input_field_$name / pods_pre_input_field_type_$coltype / pods_input_field_type_$coltype / pods_post_input_field / pods_post_input_field_$name / pods_post_input_field_type_$coltype / pods_pre_form / pods_pre_form_{Pod :: datatype} / pods_post_form / pods_post_form_{Pod :: datatype}
* Added: Automatic File Column Upgrade during DB Update from Pods < 1.7.6
* Added: Pod :: findRecords($params) can now be used where $params is an key/value array containing 'select' (t.*, p.id AS pod_id, p.created, p.modified), 'where' (null), 'join' (empty), 'orderby' (t.id DESC), 'limit' (15), 'page' (Pod :: page), 'search' (Pod :: search), and 'sql' (null) for future proofing variable expansion
* Added: save_pod_item has a new var in $params to be used - bypass_helpers (default: true) which can be set to false to not run any pre/post save helpers
* Improved: Parent / Child Theme integration uses core WP functions to lookup templates
* Improved: pods_access now uses current_user_can for 'administrator' role check, converts $method to upper case, also looks for a capability of pods_administrator for full access
* Improved: DB Update code revised
* Improved: Using $wpdb->tablename format for WP Core table names in all code
* Improved: PodAPI :: import now checks if the $data is an array of items or if it's a single-item array
* Improved: Input fields have name attribute * Added to them (except multi-select pick field which works off of a div and the file upload field)
* Bugfix: File Upload field checks version of WP to get correct button height
* Bugfix: PodAPI :: import and pick values work correctly now
* Bugfix: PodAPI :: save_pod_item works with tbl_row_id parameter correctly now
* Bugfix: PodAPI :: reset_pod works correctly now
* Bugfix: PodAPI :: drop_pod_item works with tbl_row_id parameter correctly now
* Bugfix: pods_url_variable now removes the hash (#) part of the url - On a side note, avoid use of pods_url_variable(-1) and other negative numbers as it is not always the level you expect in wildcard Pod Pages
* Bugfix: Revised AJAX-based drop_pod_item access check, you can now drop an item if a user has pod_$podname access but NOT manage_content access (previously denied)
* Bugfix: Date Input offset uses this.input.position() instead of this.input.offset() now
* Bugfix: Pod Page Template select gets/saves page.php correctly now when page.php doesn't have a Template Name
* Bugfix: File Browser display CSS fix
* Deprecated: Instead of using wp_users you should use $wpdb->users (along with other Core WP table names)

= 1.9.1 - August 13, 2010 =
* Added: Support for Multisite Environment URLs and Super Admin role
* Added: Filters for Manage Tabs (to allow Pods UI to enhance these areas)
* Added: page.php now appears as "Page (WP Default)" in the Page Template list if page.php has no "Template Name" and exists in the theme (previously did not show up)
* Added: $is_new_item to save_pod_item() in PodAPI for use in Pre-save and Post-save Helpers -- $is_new_item = true if adding an item, $is_new_item = false if editing an item
* Bugfix: drop_pod() in PodAPI function reference fix
* Bugfix: validate_package() in PodAPI assumed array, now it checks if the $data is an array

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
* Bugfix: load_pod_item() in PodAPI class no longer interferes with input helpers access to the data of a Pod Item as the Pod class is now initiated with an $id
* Bugfix: api.php now requires manage_pods priv to run load_sister_fields action
* Bugfix: Menu now runs after most plugins to avoid conflicts
* Bugfix: Menu no longer shows to any user, checks access via Pod roles
* Bugfix: pod_query() now checks against FOUND_ROWS() instead of FOUND ROWS() to cache or not
* Bugfix: style.css now uses the .pods_admin and .pods_form class selectors for each style defined to avoid overwriting other element styles on a page
* Removed: package.php has been removed from AJAX operations as code has been moved into PodAPI class

= 1.8.9 - July 7, 2010 =
* Changed: Minor UI changes
* Changed: author_id now getting stored
* Bugfix: Add / Edit javascript fix

= 1.8.8 - May 23, 2010 =
* Bugfix: bi-directional relationships

= 1.8.7 - April 16, 2010 =
* Bugfix: error when editing a unique field
* Bugfix: API handling for drop_pod_item

= 1.8.6 - April 14, 2010 =
* Bugfix: saving an empty pick column throws an error

= 1.8.5 - April 13, 2010 =
* Changed: save_pod_item improvements, see http://bit.ly/d4EWDM
* Changed: proper PHPdoc commenting
* Bugfix: timezone issues
* Added: ability to override pager var ($this->page_var)
* Added: load_helper, load_pod, load_template, drop_helper, drop_pod, drop_template methods support the "name" field as well as the id
* Added: load_page, drop_page methods support the "uri" field as well as the id