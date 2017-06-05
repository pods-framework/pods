=== Pods - Custom Content Types and Fields ===
Contributors: sc0ttkclark, pglewis, jimtrue, Shelob9, jamesgol, clubduece, dan.stefan, Desertsnowman, curtismchale, logikal16, mikedamage, jchristopher, keraweb, ramoonus, pcfreak30
Donate link: http://podsfoundation.org/donate/
Tags: pods, custom post types, custom taxonomies, content types, custom fields, cck, database, user fields, comment fields, media fields, relationships, drupal
Requires at least: 3.8
Tested up to: 4.8
Stable tag: 2.6.10-a-1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pods is a framework for creating, managing, and deploying customized content types and fields.

== Description ==
Manage all your custom content needs in ONE location with the Pods Framework. You can:

* Create and edit custom post types, taxonomy, fields and extend existing WordPress objects like users, media, posts and pages or extend other plugins' custom post types -- all from Pods.
* Easily display your custom content, whether you want to use shortcodes, widgets, the code-free Pods Template approach, or use standard PHP in WordPress Theme templates and functions.
* Create connections between any of your content to help organize it in logical and useful ways with relationship fields.

Let Pods help you grow your development skills and manage content beyond the standard WordPress Posts & Pages. Check out [pods.io](http://pods.io/) for our User Guide, [Support Forum](https://pods.io/forums/), and our [Slack Chat](https://pods.io/chat/) to help you develop with Pods.

= Introduction =
[youtube http://www.youtube.com/watch?v=bYEE2i3nPOM]

= Content types that evolve with your needs =
Create any type of content that you want -- small or large -- we've got you covered. Every content type created with Pods gets all the love it needs to grow up big and strong. You'll get an easy to use interface that lets you manage custom fields and how your content type will function.

We now give you the power you've never before had with a single plugin because we've re-imagined how to manage content types from the ground up.

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
 * [Conductor](https://conductorplugin.com/)
 * [Timber](http://upstatement.com/timber/)
 * [Gravity Forms](http://www.gravityforms.com/) Using the [Pods Gravity Forms Add-on](https://wordpress.org/plugins/pods-gravity-forms/)
 * [Caldera Forms](http://calderaforms.com) Using the [Pods Caldera Forms Add-on](https://github.com/pods-framework/pods-caldera-forms)
* Themes we've integrated with
 * [Beaver Builder](https://beaverbuilder.com) (BeaverBuilder)
 * [Builder](http://www.ithemes.com/) (iThemes)
 * [Genesis](http://www.studiopress.com/) (StudioPress)

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP Admin plugin page)
1. Activate this plugin

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

== Frequently Asked Questions ==

= Where do we go for Support on your plugin? =

Our primary Support is handled through our Support Forums at [https://pods.io/forums/](https://pods.io/forums/). For the fastest support, you can contact us on our Slack Chat at [https://pods.io/chat/](https://pods.io/chat/) in the #support channel. We do not staff our Slack channel 24 hours, but we do check any questions that come through daily and reply to any unanswered questions.

We do have a community of Pods users and developers that hang out on Slack so you're sure to get an answer quickly. We answer our Forum questions once a week with follow-up during the week as we're prioritizing resources towards restructuring and improving our documentation.

= I've found a Bug or I have a Feature Request =

If you’ve uncovered a Bug or have a Feature Request, we kindly request you to create an Issue on our GitHub Repository at [https://github.com/pods-framework/pods/issues/new](https://github.com/pods-framework/pods/issues/new). Please be very specific about what steps you did to create the issue you’re having and include any screenshots or other configuration parameters to help us recreate or isolate the issue.

= Will Pods work with my Theme? =

We don't provide any special CSS or display attributes with your custom content so as long as your theme works with WordPress standard functions and the [WordPress Template Hierarchy](https://wphierarchy.com), you should be fine. You may need to create special PHP WordPress Theme Templates for your content, or you can use our Pods Templates and the Auto Template option to display your 'template' containing your custom content where your theme normally outputs `the_content` filter.

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

Join us in further translating the Pods interface at: [https://translate.wordpress.org/projects/wp-plugins/pods](https://translate.wordpress.org/projects/wp-plugins/pods)

We also have a dedicated [Slack Chat](https://pods.io/chat/) channel to help our translators get started and to support them on the process.

== Changelog ==

= 2.6.9 - May 30th 2017 =
* Added: Pods Template Component is now automatically active on initial installation or reinstallation of Pods. Fixes (#3446). (#4060,#4180). [@pglewis,@sc0ttkclark]
* Added: Auto Template Fix: Add configurations setting to override and allow Auto Templates to run against the_content outside of the WordPress_Loop. By default now, it will only run inside the WP Loop. (#4088). [@jamesgol]
* Added: Allow raw value in PodsUI rows. New type "raw" that can output HTML form elements; used in i18n component. Fixes (#3959). (#3960). [@JoryHogeveen]
* Fixed: Template Reference in Template Editor now properly displays without running out of memory. Fixes (#3370,#3992). (#4088,#4000). [@jamesgol]
* Fixed: post_author querying now works through traversal of related post type. Fixes (#3931). (#3953,#4065). [@sc0ttkclark,@pglewis]
* Fixed: Search the proper SQL column with "search" and meta based CPT. Fixes (#3858). (#4007). [@jamesgol]
* Fixed: Ensure call to pods_view returns shortcode generated content, instead of echo'ing. Fixes (#3433). (#4010) [@dom111]
* Fixed: Additional CSS Classes were not saved (#4003) so new Duplicating Pod now gives preference to existing field options values on duplication (#4028). [@pglewis]
* Fixed: duplicate_pod_item now works for WP objects. Fixes (#3238,#4025). (#4070). [@pglewis]
* Fixed: Hidden field did not save Default Values on fields with both Visibility hidden and a Default Value set. Fixes (#3996). (#4061). [@pglewis]
* Fixed: Use register_taxonomy_for_object_type for post types that support post formats. Was not originally being registered for Pods CPT. Fixes (#2165). (#4076,#4084). [@sc0ttkclark]
* Fixed: Help Text for HTML fields wpautop reference. Fixes (#4090,#4091). (#4089). [@JoryHogeveen]
* Fixed: Corrected Pods overriding preview link for post updated messages. Fixes (#4092). (#4093). [@tuanmh]
* Fixed: When using shortcodes/magic tags with PDF attachments, ._src returns an image since WP 4.7. This will now output the URL of the file. You can still get PDF generated images using ._src.image_size or ._img. Fixes (#4040). (#4111). [@JoryHogeveen]
* Fixed: Audio attachments will now work properly with pods_attachment_import. (#4155) [@sc0ttkclark]
* Fixed: Handling of Single & Double Quotes in post_thumbnail interpolation. Fixes (#4166). (#4167). [@quasel]
* Fixed: Adding back_link to wp_die() usage which allows Modal Add/Edit to give you a way to go back in the edit screen. Fixes (#4168). (#4169). [@sc0ttkclark]
* Fixed: Conflict with The Event Calendar issue with Handlebars (as we're using an older implementation). Temporary hack until 2.7 with the correct fix. (#4173). [@sc0ttkclark]
* Fixed: Missing images in unit test (#4177). [@sc0ttkclark]
* Fixed: Invalid AJAX error with frontend forms and Settings Pods; $id will always return for AJAX requests. Fixes (#4181). (#4184). [@JoryHogeveen]
* Fixed: Allow float values for menu positions and the option to remove trailing decimals from number field. Fixes issue where Pods Converted menu positions with decimals to INT on save. Fixes (#2839). (#4192). [@JoryHogeveen]
* Fixed: Composer: composer installers v1.2 to v1.3. (#4239) [@Ramoonus]
* Fixed: Editable Titles in Multiple File Upload fields are 'editable' again (broke in 2.6.8) without breaking bidirectional relationship saving. Fixes (#4112) and resolves (#3477,#3720). (#4264). [@sc0ttkclark]
* Fixed: Spelling error in UI. Fixes (#4266). (#4267). [@danmaby]
* Updated: Brand assets for Pods (icons and banners) for WordPress Plugin Directory. Fixes (#3948). (#4268) [@jimtrue]

= 2.6.8 - January 17th 2017 =
* Added: WP Gallery display options for image fields. Fixes (#3905). (#3910). [@JoryHogeveen]
* Added: Add action after successful AJAX in admin_ajax. This allows other scripts to hook in the ajax handlers from Pods. Fixes (#3839). (#3840). [@JoryHogeveen]
* Added: Keep Plupload instances in `windows.pods_uploader[id]`. This makes it possible to bind event listeners to the file uploader. Fixes (#3763). (#3768). [@thirdender]
* Added: New singular capabilities for Taxonomies for Compatibility with WP 4.7. Fixes (#3896). (#3946). [@JoryHogeveen]
* Added: Enhance Currency Field storage. Fixes Adds new format as arrays for multiple values (label, name, sign) and decimal handling options. Fixes(#1915,#3453). (#3949). [@JoryHogeveen]
* Fixed: Number/Currency format validation error with French formatting. Fixes (#3842). (#3950). [@JoryHogeveen]
* Fixed: Additional save_user/save_post handling problems corrected and addition of Unit Tests. Fixes (#3918,#3801). (#3945). [sc0ttkclark]
* Fixed: Double qtip/tooltip when using single checkboxes (boolean type). Fixes (#3940). (#3943) [@JoryHogeveen]
* Fixed: Undefined Index Notice in (#3936). (#3941). [@sc0ttkclark]
* Fixed: Properly clear cache before running post-save actions in PodsAPI::save_pod_item. Prevents double saves being necessary to use the `pods_api_post_save_pod_item` filters to update WordPress Object fields. Fixes (#3917). (#3938). [@sc0ttkclark]
* Fixed: Revamp pods_error to handle multiple modes, PodsMeta now returns false instead of die/exception. Fixes (#3930). (#3937). [@sc0ttkclark]
* Fixed: Update save_post / save_user handling with better fallbacks when nonce not active. Fixes an issue where the $is_new_item was not set as expected on post saves and user saves. Fixes (#3801,#3918). (#3936). [@sc0ttkclark]
* Fixed: Add `pods_ui_get_find_params` filter for PodsUI to extend default `find()`. Fixes (#3925). (#3926). [@sc0ttkclark]
* Fixed: Compatibility additions for WP 4.7, Taxonomy Class (#3895,#3894)
* Fixed: Proper reset of the local var cache in Pods::$row when using add_to/remove_from/save. Fixes (#3784). (#3923). [@sc0ttkclark]
* Fixed: get_meta() $single usage to ensure it's always a boolean. Fixes (#3742). (#3921). [@sc0ttkclark,@JoryHogeveen]
* Fixed: Multiple Travis and Unit Test fixes and build functions. (#3942,#3913,#3911,#3907,#3903,#3888,#3887,#3886,#3885,#3883,#3882,#3881,#3880,#3869,#3868,#3819,#3770,#3750,#3745,#3743) [@Ramoonus,@sc0ttkclark]
* Fixed: Removing a Bad Init Call generated by a fix to correct plupload file field listings. Fixes (#3900,#3731). (#3901). [@pcfreak30]
* Fixed: Pass audio_args to the Audio Shortcode, src wasn't being passed when multiple audio files were on the same page using the same shortcode. Fixes (#3891). [@jamesgol]
* Fixed: Corrected non-printable character added after $api->cache_flush_pods() to settings-tools.php. Fixes (#3876). [@szepeviktor]
* Fixed: opcache names and add OPcache. Fixes (#3864). (#3875). [@szepeviktor]
* Fixed: Make sure self::$field_data is set in all cases. Corrects issue where relationship to predefined list was not working in AutoComplete/Select2 fields.  Fixes (#3862). (#3863). [@jamesgol]
* Fixed: Unchecking Show in Menu in the Admin UI selection for Custom Taxonomies will now properly not show the Taxonomy. `show_in_menu` option for for Taxonomies. Fixes (#3848). (#3852). [@JoryHogeveen]
* Fixed: Make field type labels translatable. Fixes (#3849). (#3850). [@JoryHogeveen]
* Fixed: Store the old field name in meta. Pods already stored the old 'pod' name, but didn't do the same for fields after updating. Added for (#3841). (#3842). [@JoryHogeveen]
* Fixed: Fix error with PODS_LIGHT and components. Fixes (#2301,#3811,#2293). (#3837). [@JoryHogeveen]
* Fixed: Update the attachment parent if updating a post type. Only updates if the parent isn't set but allows file fields/upload fields to now properly show parent post. Fixes (#3808). (#3834). [@JoryHogeveen]
* Fixed: CSS fixes (remove old images + linter), fixing issues with gradient button not in WP Core. Fixes (#3812). (#3833). [@JoryHogeveen]
* Fixed: Improve CSS for Code Field (CodeMirror). Fixes (#3818). (#3832). [@JoryHogeveen]
* Fixed: Set Start of Week Day from General Settings; fixes issue where Calendar of datetime field in admin UI didn't follow the first day of week settings from Setting, General. Fixes (#3826). (#3831). [@JoryHogeveen]
* Fixed: PHP7 Compatibility issues, a few deprecated constructors and deprecated mysql_ with the use of $wpdb. Fixes (#3828). (#3830). [@JoryHogeveen]
* Fixed: Update postmeta cache before get_post_meta calls when bypassing cache, ensuring the meta is "fresh". (#3807). [@sc0ttkclark]
* Fixed: When preloading config after flushing cache, bypass cache. solves the issue when running multisite and you’ve got an object cache drop-in that won’t flush cache for multisite when wp_cache_flush is called. (#3806). [@sc0ttkclark]
* Fixed: Fix error exporting taxonomy relationship over REST API. Fixes (#3606). (#3794). [@pcfreak30]
* Fixed: Use taxonomy capabilities in custom locations for taxonomy edit pages. Fixes an issue where Taxonomies assigned as Top Level Menu Items are not usable by Editors (only by Administrators). Fixes (#3569). (#3780). [@JoryHogeveen]
* Fixed: Correcting a bug in adding Taxonomy REST support. Fixes (#3777). (#3778). [@pcfreak30]
* Fixed: Clear `$related_item_cache` when saving a relationship. Fixes an issue where the $PodsAPI::save_relationships was not clearing the cache. Fixes (#3775). (#3776). [@pcfreak30]
* Fixed: jQuery fix to change from deprecated .live() to .on(). Fixes (#3771). (#3772). [@mikeschinkel]
* Fixed: Basic included post types from WP-API are no longer having their REST base overridden by Pods. Fixes (#3759). (#3760). [@sc0ttkclark]
* Fixed: Fix SQL for multilingual taxonomies for compatibility with PolyLang. Fixes (#3728). (#3754). [@JoryHogeveen]
* Fixed: Fix plupload file field listings, specifically fixing some issues in the CSS and jQuery. Fixes (#3731). (#3732). [@pcfreak30]
* Fixed: Removed max-length for default field value for Text & WYSIWYG fields. Fixes (#3729). (#3730). [@JoryHogeveen]
* Fixed: Updated URL for translation contributions. Fixes (#3725). (#3726). [@JoryHogeveen]
* Fixed: Validate field option dependencies based on location within tabs. Corrects and issue with compatibility between Pods SEO. Fixes (#3707). (#3723). [@JoryHogeveen]
* Fixed: Properly update bidirectional taggable relations. Corrects the issue where bidirectional relationships were creating new entries from the taggable choice in AutoComplete fields, but not saving the relationship. Fixes (#3477). (#3720). [@caleb]
* Fixed: Allow the entry of negative numbers in Currency fields. Fixes (#3708). (#3709). [@pcfreak30]

= 2.6.7 - August 15th 2016 =
* Fixed: Magic Tag {@permalink} fixes for taxonomy / user / comment detail URL Mapping. Fixes (#3339). [@sc0ttkclark]
* Fixed: Pods Wizard for Forms now properly uses the `[podsform]` shortcode. Fixes (#3251). [@sc0ttkclark]
* Fixed: Issue with pll_get_post returning false instead of null. Fixes (#3596). (#3599) [@JoryHogeveen]
* Fixed: WYSIWYG editor type option is used as dependency by the editor options. Fixes (#3549). (#3610) [@JoryHogeveen]
* Fixed: Do not display metagroup if all fields are set to hidden. Fixes (#1614). (#3615) [@JoryHogeveen]
* Fixed: Allow post_status filter to be set for related post_type objects in the edit field UI (#3626). Fixes (#3594). [@JoryHogeveen]
* Fixed: Refactor object type checking in PodsRESTHandlers::get_handler (#3630). Fixes (#3629). [@pcfreak30]
* Fixed: Added PODS_DIR to directories that are checked by field_loader() (#3644). Fixes (#3643). [@jamesgol]
* Fixed: Improved field alignment on setting pages (#3649). Fixes (#3648). [@JoryHogeveen]
* Fixed: Check for PodsInit in general.php (#3665). Fixes (#3473,#2803,#3353). [@JoryHogeveen]
* Fixed: Taxonomy capabilities + No more hardcoded tax settings (#3678). Fixes (#3676,#3677). [@JoryHogeveen]
* Fixed: Allow field options to be filtered (UI). Also allows for il8n module to improve translation handling. (#3683). Fixes (#3682). [@JoryHogeveen]
* Fixed: WPML Compatibility (#3691). Related to (#142). [@srdjan-jcc]
* Fixed: Pods field() now properly handles user and media when the output type is pod/pods. Original issue resulted in `$object` being empty as `user` and `media` do not have a `pick_val` (#3694). Fixes (#3693). [@pcfreak30]
* Fixed: travis-ci: test with PHP 7.1 (#3702). [@Ramoonus]

= 2.6.6 - June 23rd 2016 =
* Added: Polylang compatibility with latest versions along with fixes to longstanding issues with editing and displaying content, relationships, and taxonomy (#3574). Fixes (#3572, #3506) [@JoryHogeveen]
* Added: REST API v2 Compatibility (#3584). Switches `register_api_field` to `register_rest_field`. Fixes (#3581) [@sc0ttkclark]
* Added: Allow changing the Auto Templates Filter. This adds a new section in the Auto Templates tab that allows overriding the default `the_content` filter (#3542). Fixes (#3540) [@Shelob9]
* Added: Polylang support to pods_v post_id (#3562). Allows Pods templates that are translated to be properly selected. Fixes (#3561,#3537) [@jamesgol]
* Added: Create new 'post_id' method for pods_v (#3537). Provides a method to allow i18n plugins to return a different post id. Related to (#3542,#3526) [@jamesgol]
* Added: Add filter to PodsMeta->groups_get() allowing adjusting the groups via filter (#3548). Related to (#3547) [@jamesgol]
* Added: Use form_counter in field name to be unique and prevent conflicts. (#3535) Fixes (#3533) [@pcfreak30]
* Added: Add user, media and comment support to REST API (#3516). Related to (#3418,#3419) [@pcfreak30]
* Added: Filter the Pods Metas to Display (#3544). Fixes (#3520). [@coding-panda]
* Fixed: REST API cleanup for pick field handling. (#3560) Fixes (#3559) [@sc0ttkclark]
* Fixed: Exclude Unique Post fields from duplication during `$pods->save`. (#3564). Includes `ID`, `post_name`, `post_date`, `post_date_gmt`, `post_modified`, `post_modified_gmt` and `guid`. Fixes (#3563) [@pcfreak30]
* Fixed: Allow midnight (00:00) as valid time (#3555). If "Allow empty value" is unchecked and a value is not passed it will default to the current time, but it will still accept 00:00:00 as a valid value. Related to (#3488) [@jamesgol]
* Fixed: Pass $strict = false to load_pod (#3554). This will keep the "Pod not found" message from being displayed during register of other post types. Related to (#3416) [@jamesgol]
* Fixed: Don't add space to currency names that use HTML encoding (#3553). Fixes British pound currency symbols and others. Resolves (#3498) [@jamesgol]
* Fixed: Removed extra setting showing up in Auto Templates settings for Taxonomies (#3543). Fixes (#3541) [@Shelob9]
* Fixed: Use html_entity_decode to convert separator as it is an html entity. (#3536) Fixes (#3527) [@pcfreak30]
* Fixed: PodsRESTHandlers::write_handler needs to be static (#3511). Fixes (#3510) [@pcfreak30]

= 2.6.5.2 - May 4th 2016 =
* Fixed: Typo in PLL Compatibility check corrected. (#3504) Fixes (#3503). Thank you @JoryHogeveen and @fmommeja for tracking down, fixing and validating this fix. [@JoryHogeveen]

= 2.6.5.1 - May 4th, 2016 =
* Fixed: Additional Field Options tab disappears from field admin view. Fixes (#3501). [@sc0ttkclark]

= 2.6.5 - May 3rd, 2016 =
* Fixed: Renaming of Pods with underscores to hyphenated names that was introduced in 2.6.3. Hyphenated Pods names will remain hyphenated and Underscored Pods names will remain underscored. Fixes (#3499). [@sc0ttkclark]
* Fixed: Support for new Polylang Versions with much kudos to @JoryHogeveen for tackling this (#3491). Fixes (#3490,#3223) [@JoryHogeveen]

= 2.6.4 - April 25th, 2016 =
* Fixed: Modified Run activation/install priority to fire before plugins loaded. Fix for the Clearing Pods Cache automatically after Pods Upgrade (#3487). Fixes (#2558,#3348) [@sc0ttkclark]

= 2.6.3.1 - April 21st, 2016 =
* Fixed: An Git / SVN deploy bug caused some files to not be properly pushed to WordPress.org SVN, this release is just to ensure everyone who may have updated to 2.6.3 during the period which we were fixing it will be able to still get the proper file updates

= 2.6.3 - April 21st, 2016 =
* Fixed: Fix forcing underscores when loading Edit Pod Form (#3483). Fixes (#3095) [@sc0ttkclark] Kudos to @lkraav for helping us pin this particular issue down and bring it to resolution.
* Fixed: Clearing Pods Cache automatically after Pods Upgrade "Salt n'Pepa"'ing the cache keys (#3401). Fixes (#2558,#3348) [@sc0ttkclark]

= 2.6.2 - March 24th, 2016 =
* Added: Support for object fields when using Pods::field() with a specific $field and $option. This was also used to correct a problem with "fetching" Custom Taxonomy's Term List when using Pods Feeds in Pods Gravity Forms Plugin. (#3437) [@sc0ttkclark]
* Fixed: Correcting CSS used for Dashicon to remove conflict with icon usage in Divi. (#3404,#3406) [@jimtrue]
* Fixed: Currency/Number Validation used to correct issue with Currency Usage in the Pods Gravity Forms plugin (#3436) [@sc0ttkclark]

= 2.6.1 - February 15th, 2016 =
* Added: Additional Label support for Post Type / Taxonomy register functions (#3275) [@pcfreak30]
* Added: Add use_current option for Widget Single (#3393,#3394) [@sc0ttkclark]
* Added: Add option to website fields to open links in new window (#3388,#3387) [@sc0ttkclark]
* Fixed: 'type' not 'object_type' (#3378,#3351) [@pglewis]
* Fixed: Update Select2 to v3.2.0, should resolve #3344 (#3377,#344) [@pglewis]
* Fixed: Change Markup to Support CSS in WP 4.4 (Thanks to @nicdford we missed mentioning in 2.6 Change log) (#3277,#3270,#3279)
* Fixed: Non-Zero Array Keys here in PHP7 cause odd behaviour so just strip the keys (#3294,#3299) [@pglewis]
* Fixed: Corrected Dashicons Link in the Menu Options panel of Edit Pods (#3287,#3271) [@benbrandt]
* Fixed: Update Version number on 2.x (#3282,#3281) [@pglewis]
* Fixed: Typo's Rest into REST (#3303) [@Ramoonus]
* Fixed: Disable xdebug on Travis (#3284,#3283) [@pglewis]
* Fixed: Remove dockunit leftovers (#3307) [@Ramoonus]
* Fixed: Do not use Hashtag as name (#3316) [@Ramoonus]
* Fixed: Over-escaping strikes again (file upload, restrict file types with more than one mime type) (#3083,#3328) [@pglewis]
* Fixed: Refresh #3388 with 2.x (#3388,#3389) [@sc0ttkclark]
* Fixed: Replace usage of get_currentuserinfo with wp_get_current_user (preparation for WP 4.5) (#3399,#3398) [@sc0ttkclark]
* Fixed: Taxonomy custom meta fields returning false from REST API (#3365,#3369) [@anandamd]

= 2.6 - December 9th, 2015 =
* Added: Support for Term Meta in WP 4.4 - Now create meta-based taxonomies and Pods just magically works! (#3169,#3163) [@sc0ttkclark]
* Added: Add REST API Support to Post Types, Taxonomies, Users. Read the update in https://github.com/pods-framework/pods/pull/3184 for step by step details. (#3184,#3182) [@Shelob9]
* Added: Added compatibility with the latest Polylang version, using $polylang-model to get the current language and version. (#3223) [@JoryHogeveen]
* Added: Inline hook docs in PodsAdmin class (#3180,#3179) [@Shelob9]
* Added: Fixes to REST API Admin Tab (thanks @nicdford) to display always but also explain why it won't work if not able to work. (#3246,#3259) [@Shelob9,@nicdford]
* Added: PHPunit support for clover-coverage FN (#3176) [@Ramoonus]
* Added: Travis do not allow PHP7 to fail (#3235) [@Ramoonus]
* Added: Tests for Mariadb and mysql 5.6+7 with PHP 5.6 Travis (#3212,#3208) [@Ramoonus]
* Added: Nonce and text translation to delete link in pod edit sidebar. Fixes issue where attempted to delete pod from edit page results in fatal error. (#3203,#3194) [@cpruitt]
* Added: Use phpcs standard wordpress in scrutinizer (#3166) [@Ramoonus]
* Added: phpunit support for clover-coverage (#3161) [@Ramoonus]
* Added: Travis allow PHP7 to fail (#3153) [@Ramoonus]
* Added: Travis include WordPress 4.3 in test matrix (#3152) [@Ramoonus]
* Added: Travis cache composer (#3151) [@Ramoonus]
* Added: Grunt ignore dockunit.json (#3150) [@Ramoonus]
* Updated: Dockunit - replace PHP 7 rc1 with rc4 (#3201) [@Ramoonus]
* Updated: Improve Contributing guidelines correcting wrong pull location and fixing correct release branch. (#3149,#3147) [@quasel]
* Fixed: Scheduled post preview message/URL. When a post was scheduled, the status message displayed at the top of the edit post page was malformed where the string placeholders were numbered. (#3234) [@sparkdevelopment]
* Fixed: Merged #3205 to fix install-wp-tests.sh (#3211,#3205) [@Ramoonus]
* Fixed: Add pods_auto_template_name filter, by context to change auto template (#3199,#3200,#3198) [@Shelob9]
* Fixed: Revert scrutinizer less is more (#3172,#3170) [@sc0ttkclark,@Ramoonus]
* Fixed: Remove limit of 5 in get_template_titles Auto Template (#3157,#3160) [@jimtrue]
* Fixed: Related_act.permalink calls to fix permalink/slug traversal in ACTs and related taxonomies (#3156,#3155,#2779) [@sc0ttkclark]
* Fixed: Added option to deselect Auto Template for Archive views. There needed an 'empty' selection to correct issue where Template error wouldn't go away. (#3148,#3146,#3142,#3247) [@Sgillessen]
* Fixed: Added Dockunit Badge (#3145) [@tlovett1]
* Removed: Double exclude path in scrutinizer (#3228) [@Ramoonus]
* Removed: Readme removed code coverage badge (#3220) [@Ramoonus]
* Removed: Dump composer in Scrutinizer (#3204,#3167) [@Ramoonus]
* Removed: Composer remove coveralls. Was not being used and needs phpunit support. Could also be replaced by php codesniffer or scrutinizer. (#3174) [@Ramoonus]

= 2.5.5 - September 16th, 2015 =
* Added: Unit testing for PHPUnit 4.8 support. (#3090, #3069) Kudos to @Ramoonus
* Fixed: Drop External code coverage - timeout in Scrutinizer.  (#3091) Kudos to @Ramoonus
* Fixed: Changed Content property to fix spacing issues with AutoComplete Field where the formatted selection fields have awkward spacing between the selection box and the selection list. (#3098, #3097, #3099) Kudos to @nicdford
* Fixed: Issue where [each] tag traversal did not work with Taxonomy in Pods Templates. Related notes regarding pod->object_fields for taxonomy added for 3.0 notes. (#3106, #3018, #3107, #3111) Major thanks to @pglewis
* Fixed: `permalink` field traversal has only been working for post types, not for related taxonomies. (#2779, #3114, #3115) Kudos to @pglewis
* Added: Support for CPT UI 1.0.x in CPT UI migration component by adding support for multiple possible option names for post types and taxonomies. (#3112, #3109, #3113, #3116, #3117) Kudos to @pglewis
* Added: Merged Auto Template into Pods Template Component.  (#3125, #3105) Major thanks to @Shelob9 both for the original plugin and for incorporating this into Pods Templates.
* Added: License.txt changes to sync with GPL v2 (#3130, #3133) Kudos to @Ramoonus

= 2.5.4 - August 10th, 2015 =
* Added: Support for Compare ALL with meta_query syntax. Kudos to @pcfreak30. (#3037, #3038)
* Added: Query_field tests (meta_query syntax for where) (#3033, #3032, #1662, #2689)
* Added: Support for autoCAST()ing meta_value orderby for dates and numbers (#3043, #3041, #3058)
* Added: Feature/pods page export support. Added 'pods_page_exists' filter to allow Pods Page content to be supplied from another source (eg exported files) (#3049, #3054)
* Added: Copy of EDDs scrutinizer (#2917, #3072)
* Removed: PHP4-style constructor removed in Pods Widgets (#3055, #3056, #3057)
* Fixed: PHP Doc Improvement (#3039, #3040)
* Fixed: Style escaping which created a quote encoding bug in PodsMeta.php. (#3053, #3032)

= 2.5.3 - June 11th, 2015 =
* Added: Support for Term Splitting in WP 4.2 (#2856, #2660)
* Added: Support for Pod and Field names with dashes and prefixes with underscores (#3012, #3021, #3022)
* Added: Add git workflow and a link to it from contributing.md (#2490, #2496)
* Added: Unit tests for PodsField_Boolean (#2473, #2474)
* Added: Unit test to create pod factory object and moves fixture set up from traversal tests to test case. (#2445)
* Added: Additional Pods_Field_Text tests added to incorrect text dependencies. (#2388)
* Fixed: Fixes for Drag and Drop Reorder Action not working in ACT's (#3015, #3016)
* Fixed: Fix for pagination handling in shortcodes. Shortcodes currently use 'pagination' for two contexts (display and data) but if page or offset is supplied, it's only meant for one context (display). (#2807, #3004)
* Fixed: Update post field in pod instance before saving, related to MetaData (post field) not flushing after saving (#3000, #3002, #3003)
* Fixed: Corrects Delete not working for Edit Items (#2752, #2991)
* Fixed: Corrects ACT - Admin Order DESC not working && SQL error if order by an relationship field (#2843, #2989)
* Fixed: Composer: updated for phpunit 4.7 (#2987, #2988, #2783)
* Fixed: ui/js/jquery.pods.js fixes (#2971, #2972)
* Fixed: Remove `@internal` phpDoc for pods_query() (#2970, #2969, #2975)
* Fixed: Fix for ACT editor not staying on current item after saving (#2968, #2942, #2974)
* Fixed: Fix for over escaping icon URL in file fields previewer (#2957, #2956, #2955, #2978)
* Fixed: Fix for symlinked pods in local deve environment (#2946, #2945, #2949)
* Fixed: Removed Vestiges of Old Updater (#2940, #2983)
* Fixed: Clarify help text as to what does and doesn't get deleted on reset (#2792, #2778)
* Fixed: Missing $ in PodsInit line 494 (#2475, #2476)
* Fixed: Trim off whitespace when saving custom fields; code in classes/fields/pick.php already does this. (#2386, #2343)
* Fixed: Updated Taxonomy to get called after cache is flushed (#2264, #2375, #2382)
* Fixed: Cleared old unit tests from EDD (#2380)
* Fixed: Allow fields to be sorted by orderby; Two separate but connected issues. First if orderby is passed then the $data array is never populated. Then looping through $ids will always give it results sorted by priority in the relationships field (data returned by lookup_related_items) (#2350, #2277)

= 2.5.2 - May 14th, 2015 =
* Fixed: Issues with default values for number and other types of fields.
* Fixed: Issue where Pods update was causing WP-API endpoints to 404. Rewirte rules now flush on wp_loaded.
* Fixed: Issue preventing proper display of fields in a related CPT via Pods::field()
* Fixed: Issue preventing codemirror from being enqueued in Pods templates and therefore breaking Pods tempaltes editor in certain configurations.
* Added: Added caching info to debug info.
* Fixed: Bug that was causing Pods to overwrite admin menus.
* Fixed: Issue preventing ongoing compatibility with Admin Columns.
* Improved: Style of components filter bar.
* Improved: Proper sanitization/ escaping of URLs.
* Fixed: Shortcode button was outputted in post editor when shortcodes were disabled. This will no longer happen.
* Improved: Translation strings in ui/admin/help
* Improved: Gradients in Pods wizard.
* Fixed: Issue preventing associated taxonomies to be fetched via Pods::field() and therefore magic tags.
* Improved: Icon font used for Pods admin icon.
* Improved: Elaborated on what data is and isn't deleted when Pods data is reset.
* Added: Compatibility with Github updater plugin.
* Updated: New youtube video in readme.
* Added: Support for term splitting in WordPress 4.2.
* Removed: Extra meta data with _pods_ prefix
* Fixed: Issue where multiple post type Pods objects called in same session were treated as the same in cache.
* Fixed: Double slashing in PodsView class.
* Improved: URL escaping in PodsUI

= 2.5.1.2 - March 16th, 2015 =
* Security Update: We recommend all Pods 2.x installations be updated to the latest version of Pods
* or replace your plugin files with the download of your version from http://wordpress.org/plugins/pods/developers/
* Fixed: Pods UI orderby now strictly enforces Database column format

= 2.5.1.1 - January 22nd, 2015 =
* Fixed missing files for font icon.

= 2.5.1 - January 22nd, 2015 =
* Fixed: Issue preventing fields from being sorted by weight or by orderby, that was affecting image multi-select image field ordering.
* Fixed: Missing gradients in UI.
* Fixed: Use of anonymous function in PodsMeta.php causing issues with old versions of PHP.
* Fixed: Issue where hidden fields were being shown for admin users, when they should have been hidden.
* Fixed: Issue where PodsAPI::delete_field() was unable to delete fields in certain situations.
* Fixed: Issue with pods_version_check() usage that was causing a deprecated core function to run, when it was supposed to prevent it from running.
* Fixed: Issue with pods_error() that was causing it to display AJAX errors improperly.
* Fixed: Issue preventing public, publicly queryable & rewrite with front from saving choices in advanced options.
* Fixed: Magic tag for custom taxonomy, which was showing no content in Pods Templates in 2.5.
* Fixed: If block in Frontier.
* Fixed: Issue with custom taxonomy joins preventing "custom_tax.d.custom_field" type where clauses from working.

= 2.5 - December 30th, 2014 =
* Major performance enhancements can now make things run up to 400% faster (props to @jamesgol!)
* More unit tests -- now 1,858 tests with a total of 13,420 assertions covering all content type, storage type, and field variations (props to @sc0ttkclark, @clubduece, and @mordauk! it was a group effort)
* Added Travis-CI / Scrutinizer-CI for all pushes and pull requests to unit test and check for other issues
* Upgraded Code Mirror library
* Upgraded qTip library
* Updated translations -- Add your translations at http://wp-translate.org/projects/pods
* Fixed: Added nonces for multiple actions in the admin area to avoid accidental / unwanted results
* Fixed: Issue causing issues in admin with CodePress admin columns.
* Fixed: Issue preventing Pods Template editor from working with certain xcache configurations.
* Added: 'join' to the accepted tags for Pods Shortcode.
* Added: 'pods_data_pre_select_params' filter.
* Improve: PodsAPI::export_pod_item_lvl(), adding item ID to all steps.
* Simplify logic when creating new PodsAPI singleton internally.
* Switch from Pods::do_hook() to apply_filters() or do_action() for 'pods_pods_fields', 'pods_pods_field_related_output_type', 'pods_pods_field_', 'pods_pods_field', 'pods_pods_fetch', 'pods_pods_reset', 'pods_pods_total_found', 'pods_pods_filters'
* Fixed: YARRP support.
* Ensure that pods_v_sanitized() passes the $strict argument to pods_v().
* Prevent use of date_i18n() in PodsData when not needed.
* Fixed: Issue where updating relationship to users in pods editor  threw an erroneous error.
* Fixed: Hiding of text in title-prompt-text
* Updated design of new Pod wizard to match MP6 (props to @nikv!)
* Fixed: Inline docs for pods_api_get_table_info_default_post_status filter
* Fixed: Issue where Pods::field() showed cached data after saving via Pods::save(), without re-building the Pods Object.
* Allowed PodsField_Pick to save names
* Switched pods_v() to use switch/case instead of if/else where possible.
* Prevented Pods::id() from calling the field method unless it has to.
* In PodsData::select(), allow proper use of cache expiration time.
* Fixed: Issue in currency fields to ensure proper handling of decimals.
* Added a "pre_select" hook in PodsData.
* Improved traversal regex in Pods::find() to have better handling for variation of backticks.
* Removed usages of the deprecated WordPress function like_escape().
* Remove redundant file/class checks for Pods Templates.
* Implement glotpress-grunt for manging translations.
* Fixed: Issue where get_current_screen(), in some contexts was used as an object, when it was null.
* Improved: Styling of shortcode insert button.
* Prevented string replace and trim from running on a form field default when default value is not a string
* Fixed: Issue preventing color pickers from working in front-end form.
* Switched from using $wpdb->prefix to $wpdb->base_prefix in pick field class.
* Fixed: Default avatars on the Discussion settings page replaced by user's custom avatar.
* When saving custom fields, whitespace is now trimmed.
* Better validation of custom fields when saving custom post types.
* Improved: Handling of required fields.
* Changed the default of $display_errors in Pods class to true.
* Allowed save_post_meta to delete single meta elements instead of update.
* Fixed: An issue preventing fields from being sorted by orderby.
* Fixed: Issue where fields, storing one value, returned arrays, instead of strings.
* Allowed extending the link category taxonomy, if in use.
* Added join as an acceptable tag for Pods shortcodes.
* Fixed pods_error(): reversed logic that was emitting an error instead of throwing an exception when $display_errors is false
* Fixed issue where user_url was created as a required field when extending users.
* Add ability to use pods_group_add() in the ACT editor.
* Security Update Reminder: As of Pods 2.4.2, we recommend all Pods 2.x installations be updated to the latest version, or replace your plugin files with the download of your version from http://wordpress.org/plugins/pods/developers/
* If you need assistance in upgrading your Pods 2.x site to the latest version of Pods, please don't hesitate to contact us at http://pods.io/help/

= 2.4.3 - June 23rd, 2014 =
* Fixed: Pods Templates component now has better handling of the new shortcodes
* Fixed: PodsUI data issue with Custom DB Table support
* Fixed: Readonly fields and noncing now works properly, Pods 2.4.2 caused all forms with readonly fields to fail submission
* Hardened: Further security hardening of the `[pods]` shortcode, added PODS_DISABLE_SHORTCODE constant to allow sites to disable the Pods shortcode altogether

= 2.4.2 - June 22nd, 2014 =
* Security Update: We recommend all Pods 2.x installations be updated to the latest version of Pods to fix a noncing issue with form saving, or replace your plugin files with the download of your version from http://wordpress.org/plugins/pods/developers/

= 2.4.1 - June 19th, 2014 =
* Fixed: Display of of hidden fields in Pods Forms
* Fixed: Reordering fields in PodsUI
* Fixed: PodsUI Admin Icon Display
* Add new filter: ‘pods_pod_form_success_message’ for changing the message when Pods Forms are successfully submitted.
* Fixed: Issues in Packages component when importing existing fields.
* Added new filter: ‘pods_view_alt_view’ for overriding normal Pods Views to be loaded in via AJAX inline from Pods AJAX Views plugin.
* Fixed: PHP error in Pods Template reference.
* New Constant: PODS_PRELOAD_CONFIG_AFTER_FLUSH check to allow for preloading $api->load_pods() after a Pods Cache flush.
* Fixed: Issue with tabled-based SQL delete actions.
* Fixed: PodsUI SQL table-based lookups
* Added: New Hooks In ui/admin/form, which generates ACT editor, for adding additional forms or other content to editor.
* Added: Inline docs for 'pods_meta_default_box_title' filter and normalized args across each usage.
* Added: Item ID to pods_api::export() item array.
* Fixed: Update from GitHub functionality.
* Fixed: Issue where extended custom post types had diffrent names then original post type due to use of dashes in names.
* Improved UX for select2 field adding new items.
* Fixed: $params with unslashed data in Pods_Admin::admin_ajax()
* Fixed: Unwarranted base_dir warnings.
* Fixed: Pagination/search boolean checks.
* Fixed: Issue when mbstring module is not active.
* Fixed: Issue with markdown module header causing activation errors.
* New Filter: 'pods_admin_components_menu' to add/edit components submenu items.
* Added: Ability to use pods() without any parameters. Will pull the pod object based off of the current WP_Query queried object / object id

= 2.4 - April 16th, 2014 =
* After a long road, we've got a new minor release out that fixes a large number of outstanding bugs and adds a few improvements that were within reach right away.
* In Pods 3.0 we're focusing on finishing some overarching performance improvements that are necessary to support large installs with the new Loop and Repeatable fields features.
* Added: Tagging feature for Relationship fields with Autocomplete (Select2) which lets you add new items on-demand when saving
* Added: PodsAPI::get_changed_fields() that can be used when in a pre-save hook to return array of changed values or used in PodsAPI::save_pods_item() to track changes to fields
* Added: _pods_location to $params for PodsAPI::save_pod_item which will contain the URL of the form it was submitted from
* Added: New Pods Template editor revamp to include auto-complete for magic tags and field reference, which can be further extended by installing Pods Frontier
* Added: An optional download link to File Upload field type
* Added: Additional Currency formats to Currency field type
* Added: created/modified functionality (see Advanced Content Types) to other Pod types, as long as they are date/datetime fields
* Added: Support for JetPack Publicize and Markdown modules
* Added: Max character length option for paragraph fields
* Added: Actions before and after Pods Form all and individual form fields are outputted
* Added: New constant PODS_ALLOW_FULL_META for for enabling/disabling get_post_meta( $id ) interaction with Pods (on by default)
* Added: New constant PODS_DISABLE_SHORTCODE_SQL to disable SQL-related parameters in shortcode
* Added: 'pods_admin_media_button' filter to disable the Pods shortcode button in post editor
* Added: 'pods_api_save_pod_item_track_changed_fields_{POD_NAME}' filter for tracking changes to fields
* Added: 'pods_pick_ignore_internal' filter to enable/disable Relationships with core Pods types (_pods_pod, etc)
* Added: 'pods_image_default' filter to allow for placekitten.com or other image placeholder scripts for testing
* Added: Improved Pods Template code sanitization
* Added: Better names for many fields in Pods Editor
* Added: New and improved help bubbles in Pods Editor
* Added: Instructions about using Pods Templates in Pods Widgets
* Added: New descriptions for Pods Pages and Pods Advanced Content Types component descriptions
* Added: Support links in Pods Admin -> Help
* Added: Currently active theme to Pods Debug info list
* Added: Inline docs for 'pods_api_get_table_info_default_post_status' filter
* Added: Inline docs for 'pods_admin_menu' filter
* Added: Inline docs for 'pods_admin_setup_edit_options' (and related) filters
* Added: Inline docs for 'pods_admin_setup_edit_tabs' (and related) filters
* Fixed: Issues with user tables in multisite
* Fixed: Issue with PodsForm::default_value
* Fixed: With Pods UI. Keep view when generating pagination links
* Fixed: Bug with custom extensions for allowed file types in upload fields
* Fixed: Compatibility problem with changes to plupload in WordPress 3.9 that prevented upload pop-up from loading
* Fixed: Array to string conversion error for CSS fields in Pods UI
* Fixed: Magic tags for taxonomies in Pods Templates
* Fixed: Fixed jQuery scope in Pods Form inline JavaScript
* Fixed: Added 'output' to reserved content types names and reserved query vars
* Fixed: Issue where required currency and number fields could be saved at default value
* Fixed: Undefined method error in WP 3.4 due to use of WP_User::to_array() which was added in WP 3.5
* Fixed: Issue with ability to use filters on reorder page with Pods UI
* Fixed: Pre-save enforcing of max length for meta-based values
* Fixed: Extra spaces in custom defined list labels
* Fixed: Pagination default value for Pods shortcode
* Fixed: PodsForm::submit_button() method that had been lost from previous versions
* Fixed: Usage of pods_v in currency.php for optimzation purposes
* Fixed: Correct parent_file to highlight the correct top level menu
* Fixed: Improper wording for text at top of settings page field

Found a bug? Have a great feature idea? Get on GitHub and tell us about it and we'll get right on it: https://pods.io/submit/

Our GitHub has the full list of all prior releases of Pods: https://github.com/pods-framework/pods/releases
