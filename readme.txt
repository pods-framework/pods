=== Pods CMS Framework ===
Contributors: sc0ttkclark, logikal16, jchristopher
Donate link: http://podsfoundation.org/donate/
Tags: pods, cms, cck, pods ui, ui, content types, custom post types, relationships, database, framework, drupal, mysql, custom content, php
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: 1.12.3

Pods is a CMS framework for creating, managing, and deploying customized content types.

== Description ==

Check out http://podsframework.org/ for our User Guide and many other resources to help you develop with Pods.

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
Pods 2.0 is around the corner, so keep up-to-date by following our @podsframework twitter account or checking out our Pods Development blog at http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

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

= 1.12.3 - February 19, 2012 =
* Added: Pods UI findRecords parameters array now goes through a new filter called "pods_ui_findrecords"
* Fixed: Forcing boolean check if true in bypass_helpers in PodAPI
* Fixed: Pod Page automatic title generation now removed WP home path (for WP sites in sub-directories); Props @chrisbliss18
* Fixed: nicEdit JS "A.createRange()||document.createRange()" fix for JS errors on certain browsers / uses
* Fixed: Pagination page_var usage (was forcing 'pg' var name no matter what), and fixed query array handling; Props to @thangaswamyarun for finding this one so we could fix it!
* Fixed: Pods UI search settings set correctly now (had to flip a true/false check and not have search_across take the bool value of search)
* Fixed: Pods UI filtering sets emptied values now, wasn't setting right if you had emptied a filter value when submitting (unselected drop-down)

= 1.12.2 - December 14, 2011 =
* Fixed: WP 3.3 TinyMCE Editor bug with HTML tab (wouldn't save if HTML tab was active during initial form load) and other minor fixes

= 1.12.1 - December 12, 2011 =
* Fixed: findRecords Order bug

= 1.12 - December 12, 2011 =
* Important: As with all upgrades, we take them seriously. If you experience any major issues when upgrading to this version from a previous version, immediately contact uhoh@podsframework.org and we'll help get your upgrade issue figured out (critical bugs only please)
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
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.4 - August 1, 2011 =
* Fixed: Pods UI was breaking 'view' links
* Fixed: Pods UI reordering fixed
* Fixed: Better errors for when a Pod doesn't exist to replace SQL errors
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.3 - July 30, 2011 =
* Fixed: Shortcode 'where' parameter fixed
* Fixed: Body Class for Pod Pages not replacing / with - correctly and leaving an extra - at the end with wildcards
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.10.2 - July 29, 2011 =
* Added: Moved the demo.php file from the Pods UI plugin over as pods-ui-demo.php and can now be found distributed with this plugin in the /demo/ plugin.
* Fixed: PHP error with new Version to Point function
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
* Fixed: Pod :: getFilters now using the correct 'label' for the search button
* Fixed: On uninstall, now deleting options WHERE option_name LIKE 'pods_%'
* Fixed: Various minor bug fixes
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

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
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Reminder: Pods 2.0 and How You Can Help - http://dev.podscms.org/2011/06/16/pods-2-0-and-how-you-can-help/

= 1.9.8 - July 24, 2011 =
* Added: New Option to override existing packages during package import
* Added: Pods and additional database calls are not initiated (but you can run the code as the files are included) when SHORTINIT is defined and set to true (also does not load much of WP itself)
* Added: Pods will now check the version of Pods a package was exported from and display notices if it 'might' be incompatible (based on the minor version in major.minor.patch), and an additional two variables (compatible_from and compatible_to) are available within the 'meta' array which will get utilized in the new Pods site revamp within the Package Directory
* Improved: Enhanced display / error information and implementation for package import
* Fixed: Package export bug that generated an 'empty' package when you click 'Export' without anything selected
* Fixed: No longer calling $pods_roles immediately, only used when needed in the code
* Fixed: &$referenced the $pods_cache variable to $cache for backwards compatibility - use $pods_cache going forward
* Fixed: Minor PHP warnings/notices that come up when WP_DEBUG is defined and set to true
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

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
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.3 - June 24, 2011 =
* Fixed: JS optimization and fixes for nicEdit (also now no longer outputting pods-ui.js on every page)
* Fixed: Non Top-level menu Pods now appearing in alphabetical order under Pods menu
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.2 - June 23, 2011 =
* Fixed: TinyMCE API update from @azaozz with additional WP 3.2 support
* Fixed: Pod Page Precode $pods = 404; bug that wouldn't produce the default WordPress 404 error page
* Fixed: Fix for nicEdit JS error during init that breaks forms (when on a non top-level menu Pod AJAX-loaded form)
* Fixed: Fix for PICK error during save that errors out trying to save selections as 'undefined' (when on a non top-level menu Pod AJAX-loaded form)
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6.1 - June 23, 2011 =
* Fixed: Fix for nicEdit JS error during init that breaks forms
* Reminder: 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6 - June 22, 2011 =
* Full Details can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/
* Security Update: New security settings section in the Pods >> Setup >> Settings tab to restrict access to the File Browser / Uploader used in publicForm, adjust the settings to fit your site if you experience any problems
* Added: New TinyMCE API for use with the new TinyMCE package update at http://podsframework.org/packages/tinymce-for-pods/
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