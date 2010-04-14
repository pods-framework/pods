=== Pods CMS ===
Contributors: logikal16, sc0ttkclark
Donate link: http://podscms.org
Tags: pods, cms, table, database, relational database, custom, data, framework, drupal, cck, joomla
Requires at least: 2.8
Tested up to: 3.0
Stable tag: trunk

Pods is a full-featured yet lightweight CMS framework for WordPress.

== Description ==

Pods is a CMS framework for WordPress.
It sits on top of WordPress, allowing you to add and display your own content types. 

**Relate content to other content.**

When creating a new content type, you can add relationship, or PICK, columns. This column will build a dropdown containing items from the related table, allowing you to select one or more.
In other words, you can relate an item from one pod to items in another pod, or even relate to WordPress items (users, pages, posts).

**Show your new content with style.**

Pod Pages are similar to WP Pages, but they include PHP and wildcard URL support. For example, the Pod Page events/* will handle all URLs beginning with "events/", unless a more specific Pod Page is found.
Templates are used within Pod Pages, and are meant for separating presentation from logic as much as possible. Magic tags are used within templates to dynamically pull in a column's value: {@column_name}

**It's all in the box.**

* Package Manager - import & export pieces of your site
* Menu Editor - organize your pages to generate sitemaps, navigation menus, and breadcrumbs
* Roles - lightweight permissions system
* API - access and modify the data programatically

== Changelog ==

= 1.8.6 - Apr 14, 2010 =
* Bugfix: saving an empty pick column throws an error

= 1.8.5 - Apr 13, 2010 =
* Changed: save_pod_item improvements, see http://bit.ly/d4EWDM
* Changed: proper PHPdoc commenting
* Bugfix: timezone issues
* Added: ability to override pager var ($this->page_var)
* Added: load_helper, load_pod, load_template, drop_helper, drop_pod, drop_template methods support the "name" field as well as the id
* Added: load_page, drop_page methods support the "uri" field as well as the id