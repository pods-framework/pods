=== Pods CMS Framework ===
Contributors: logikal16, sc0ttkclark, jchristopher
Donate link: http://podsfoundation.org/donate/
Tags: pods, cms, cck, custom post types, content types, relationships, database, framework, drupal, mysql, custom content, php
Requires at least: 2.8
Tested up to: 3.2
Stable tag: 1.9.6

Pods is a CMS framework for creating and managing your own content types.

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
Utilize the Pods UI plugin (included in the upcoming Pods 2.0) to build your own Custom Management panels for your Pods. Get it at: http://wordpress.org/extend/plugins/pods-ui/

= Migrate! =
Pods includes a Package Manager, which allows you to import/export your database structure. You can select which features you want to "package up" and export it for easy migration.

Pods also includes an easy to use PHP API to allow you to import and export your data via CSV, and other more complex operations.

= Introduction to the Pods CMS Framework =
[vimeo http://vimeo.com/15086927]

= Stay tuned for Pods 2.0 =
Pods 2.0 is around the corner, so keep up-to-date by following our @podscms twitter account or checking out our Pods Development blog at http://dev.podscms.org/

Features coming in Pods 2.0 include:

* Completely revamped UI
* Pods UI will become part of Pods core
* Create and Manage Custom Post Types
* Create and Manage Custom Taxonomy
* Easy migration between Custom Post Types and Custom Content Types (Standalone Pods)
* Many more field types and advanced options (less code for you to do!)
* Many MySQL optimizations and performance tweaks
* Full i18n support
* and more features which can be found at: http://dev.podscms.org/pods-2-0/

== Changelog ==

= 1.9.6.1 - June 25, 2011 =
* Fix for nicEdit JS error during init that breaks forms
* 1.9.6 Security Update information can be found at: http://dev.podscms.org/2011/06/22/pods-1-9-6-security-update-new-features-bug-fixes/

= 1.9.6 - June 24, 2011 =
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
This will be the last Feature release for Pods 1.9.x -- All future releases of 1.9.x will be strictly Bug Fix only until Pods 2.0

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