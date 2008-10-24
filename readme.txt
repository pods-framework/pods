=== Pods ===
Contributors: logikal16
Donate link: http://wp-pods.googlecode.com
Tags: pods, wordpress, cms, plugin, module, datatype, posts, pages
Requires at least: 2.7
Tested up to: 2.7
Stable tag: trunk

Pods is a CMS (Content Management System) plugin for Wordpress.

== Description ==

Document library. Photo gallery. Event calendar. Newsroom. Pods is a CMS (Content Management System) plugin that allows you to create almost any imaginable structure in Wordpress using normal blog posts.

A **pod** is simply a container of fields. If you created a pod called **event**, it could contain fields like *date*, *address*, and *attendees*. With Pods, any field can be a relationship with another pod or category.

You also have full control over what gets displayed on the list and detail pages.

[View the Demo](http://pods.uproot.us)

== Installation ==

= Installation =
1. Unzip the `pods` folder into `wp-content/plugins/`
2. Move .htaccess from `wp-content/plugins/pods/` to the Wordpress root directory
3. In Settings > Permalinks, set the Custom Structure to `/%postname%/`
4. Enable the plugin through Plugins > Installed

= Adding a New Pod =
1. In Admin, click on Tools > Pods
2. Click on the add new link next to the "Manage Pods" header

= Adding a New Pod Column =
1. In Admin, click on Tools > Pods
2. On the left side, click on the pod you want to change (background turns blue)
3. Click on the "Add a Column" link

= Column Types =
 - **date** - builds a date picker
 - **number** - integer (1,2,3) or currency (12.50, 100.00)
 - **boolean** - generates a checkbox (True/False, Yes/No)
 - **text** - short text (name, caption, URL)
 - **desc** - long text (article body)
 - **file** - uses the default Wordpress file manager, simply saves the file URL
 - **pick** - relationship with a pod (or parent category having children)

= Relating Posts to Pods =
 - In the "Edit Post" page, you'll see a "Choose a Pod" tab

= List Template =
 - Determines how each item on a [list page](http://pods.uproot.us/list/?type=event) appears
 - Can be overridden by a custom template on a per-page basis (see the FAQ)
 - Supports full HTML, as well as magic tags (see below)
 - The `{@detail_url}` magic tag displays the detail page URL of any list item
 - Ex: `<p><a href="{@detail_url}">{@name}</a></p>`

= Detail Template =
 - Determines how an item's [detail page](http://pods.uproot.us/detail/?type=event&id=2) appears
 - Can be overridden by a custom template on a per-page basis (see the FAQ)
 - Supports full HTML, as well as magic tags (see below)

= Magic Tags =
 - Allows for column values to be inserted in List and Detail templates
 - Format: `{@column_name[,before][,after][,extras]}`
 - Ex: `{@start_date}` or `{@summary,Summary: }` or `{@start_date,<p>in ,</p>,m/d/Y}`
 - **column_name** - a column name in the current pod
 - **before** - text/html inserted before the column value (will **not** appear if the column is empty)
 - **after** - text/html inserted after the column value (will **not** appear if the column is empty)
 - **extras** - for DATE types, allows for custom PHP [date formats](http://us2.php.net/date) (e.g. m/d/Y)

= List Filter =
 - A comma-separated list of (PICK type) column names
 - Dropdown filters are generated on the pod's [list page](http://pods.uproot.us/list/?type=animal)
 - On custom list pages, the filters are optionally enabled by using `Record->getFilters()`

== Frequently Asked Questions ==

= How can I reach you? =
Feel free to email me at **logikal16@gmail.com** with improvement ideas, bug fix requests, and anything else that comes to mind.

= How do I see list views? =
1. You created a pod called **news** and your .htaccess is set properly
2. The list view is located at: **http://domain.com/list/?type=news**

= Can I add list views to any Wordpress page? =
Yes. Let's assume that you added page called "Latest News" at **http://domain.com/resources/latest**. To add a custom list view to that page, create a file at this path: **wp-content/plugins/pods/pages/resources/latest.tpl**. Note how the file path corresponds to the URL. In the latest.tpl file, enter the following code: 
`
<?php
$Record = new Pod('news'); // enter pod name
$Record->findRecords('id DESC', 6); // sort order and limit
echo $Record->getFilters(); // search box and filters
echo $Record->getPagination(); // pagination
echo $Record->showTemplate('list'); // build list or detail view
`

= Can I override Pod templates? =
Yes. You can specify template code as the 2nd `showTemplate()` parameter. In your custom `.tpl` file, you could have something like:
`
$override = '<p><a href="{@detail_url}">{@name}</a></p>';
$Record->showTemplate('list', $override);
`

== Screenshots ==

1. Manage all pods and views in the management panel

== Changelog ==

 - ***v1.1.0:* Added "edit_url" magic tag, better dropdown formatting
 - **v1.0.9:** Fixed column editing + dropdown filters
 - **v1.0.8:** Fixed bug when deleting pods
 - **v1.0.7:** Fixed post deletion hook, pagination when rpp <> 15
 - **v1.0.6:** AJAX file picker, improved magic tags, screenshots
 - **v1.0.5:** Allowed column type editing, removed excess icons
 - **v1.0.4:** Show/hide controls for management page options
 - **v1.0.3:** New pod tabs now clickable without refresh
 - **v1.0.2:** Added state & country tables to init.php
 - **v1.0.1:** Fixed boolean type, added readme.txt
