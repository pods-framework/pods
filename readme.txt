=== Pods CMS ===
Contributors: logikal16, sc0ttkclark
Donate link: http://podscms.org
Tags: pods, cms, cck, custom post types, content types, relationships, database, framework, drupal
Requires at least: 2.8
Tested up to: 3.0
Stable tag: trunk

Pods is a CMS framework for managing your own content types.

== Description ==

Pods is a CMS framework for managing your own content types.

**Create your own content types.**

A pod, or content type, is a named group of input fields. The Pods plugin lets you create your own content types. Instead of with custom post types, each content type gets its own table.

Create a variety of different input fields, including text, paragraph text, date, number, file upload, and relationship (called "pick") fields. Pick fields are extremely useful if you want to create relationships among your data. One example is if you want to relate an "event" item with one or more "speaker" items.

**Easily display your content.**

There are several ways to get Pods data to show up throughout your site:

* Add Pod Pages from within the admin area. Pod Pages support PHP and Wildcard URLs. For example, the Pod Page "events/*" will be the default handler for all pages beginning with "events/". This allows you to have a single page to handle a myriad of different items.
* Add PHP code directly into your WP template files, or wherever else PHP is supported.
* Use shortcode to display lists of Pod items within WP Pages or Posts.
* The Pods API allows you to retrieve raw data from the database.

**Migrate!**

Pods includes a Package Manager, which allows you to import/export your database structure. You can select which features you want to "package up" and export it for easy migration.

== Changelog ==

= 1.8.9 - July 7, 2010 =
* Changed: Minor UI changes
* Changed: author_id now getting stored
* Bugfix: Add / Edit javascript fix

= 1.8.8 - May 23, 2010 =
* Bugfix: bi-directional relationships

= 1.8.7 - Apr 16, 2010 =
* Bugfix: error when editing a unique field
* Bugfix: API handling for drop_pod_item

= 1.8.6 - Apr 14, 2010 =
* Bugfix: saving an empty pick column throws an error

= 1.8.5 - Apr 13, 2010 =
* Changed: save_pod_item improvements, see http://bit.ly/d4EWDM
* Changed: proper PHPdoc commenting
* Bugfix: timezone issues
* Added: ability to override pager var ($this->page_var)
* Added: load_helper, load_pod, load_template, drop_helper, drop_pod, drop_template methods support the "name" field as well as the id
* Added: load_page, drop_page methods support the "uri" field as well as the id