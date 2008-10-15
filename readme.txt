=== Pods ===
Contributors: logikal16
Donate link: http://wp-pods.googlecode.com
Tags: pods, wordpress, cms, plugin, module, datatype
Requires at least: 2.7
Tested up to: 2.7
Stable tag: trunk

Pods is a Wordpress plugin that adds CMS abilities to blog posts.

== Description ==

Pods is a CMS (Content Management System) plugin for Wordpress. It allows users to add **content types**, associate content types with posts, and form relationships among blog posts.

A content type, or **pod**, is a simply a container of information. It allows items to be categorized in a much more structured and flexible manner than through tagging alone.

For example, we'll create 2 pods: **person** and **automobile**. The **automobile** pod could contain relevant fields (*make*, *model*, *year*, *mpg*, and *owner*). The cool part about Pods is that we could set the *owner* field as a relationship with the **person** content type.

Posts can be interrelated with other posts of that same content type, as well as with any other content type(s). This aspect of a "web of related content" is the heart of any modern CMS.

== Installation ==

= Installation =
1. Unzip the `pods` folder into `/wp-content/plugins/`
2. Move .htaccess from `/wp-content/plugins/pods/` to the Wordpress root directory
3. In Settings > Permalinks, set the Custom Structure to `/%postname%/`
4. Enable the plugin through Plugins > Installed

= Adding a New Pod =
1. In Admin, click on Tools > Pods
2. Click on the add new link next to the "Manage Pods" header
3. Enter a valid name (underscores, not spaces), then click Save

= Adding a New Pod Column =
1. In Admin, click on Tools > Pods
2. On the left side, click on the Pod you want to change (the background will turn blue)
3. In the text box above the "Detail Template" header, enter the column name
4. Select the appropriate column type from the dropdown
5. Click Add Column

= Column Types =
1. **date** - builds a date picker
2. **number** - integer (1,2,3) or currency (12.50, 100.00)
3. **boolean** - generates a checkbox (True/False, Yes/No)
4. **text** - short text (name, caption, URL)
5. **desc** - long text (article body)
6. **file** - uses the default Wordpress file manager, simply saves the file URL
7. **pick** - relationship with a Pod (or parent category having children)

= Relating Posts to Pods =
* When editing a post, you'll see the **Choose a Pod** tab

= List Template =
* Determines how each item on a [list page](http://pods.uproot.us/list/?type=animal) appears
* Supports full HTML, as well as magic tags
* If the pod has a column called address, then entering the magic tag **{@address}** will display the value
* The **{@detail_url}** magic tag will display the detail page URL of any list item
* Ex: **`<p><a href="{@detail_url}">{@name}</a> - {@date}</p>`**

= Detail Template =
* Determines how an item's [detail page](http://pods.uproot.us/detail/?type=animal&id=4) appears
* Supports full HTML, as well as magic tags
* If the pod has a column called summary, then entering the magic tag **{@summary}** will display the value

= List Filter =
* Enter a comma-separated list of (PICK type) column names
* Dropdown filters are generated on the pod's [list page](http://pods.uproot.us/list/?type=animal)
* On custom list pages, the filters are optionally enabled by using **Record->getFilters()**

== Frequently Asked Questions ==

= How can I reach you? =
Feel free to email me at **logikal16@gmail.com** with improvement ideas, bug fix requests, and anything else that comes to mind.

= How do I see list views? =
1. You created a pod called **news** and your .htaccess is set properly
2. The list view is located at: **http://domain.com/list/?type=news**

= Can I add list views to any Wordpress page? =
Yes. Let's start with an example. On your blog, you have a page called "Latest News" at **http://domain.com/resources/latest**. To add a custom list view to that page, create a file at this path: **wp-content/plugins/pods/pages/resources/latest.tpl**. Note how the file path corresponds to the URL. In the latest.tpl file, enter the following code: 
`
<?php
$Record = new Pod('news'); // change "news" with any pod name
$Record->findRecords('id DESC'); // change the sort order if needed
echo $Record->getFilters(); // show the search box and any available filters
echo $Record->getPagination(); // show the pagination controls
echo $Record->showTemplate('list'); // build the list view`

= Can I have more than 1 list or detail template for each Pod? =
Yes. You can specify template code as the 2nd parameter in **showTemplate()**.
`
$custom_tpl = '<p><a href="{@detail_url}">{@name}</a></p>';
$Record->showTemplate('list', $custom_tpl);`

== History ==

**1.0.5**
* ADDED: allow editing of column types
* DROPPED: unnecessary icons

**1.0.4**
* ADDED: panel toggles on management page

**1.0.3**
* FIXED: after adding a pod, the new tab is now clickable without refresh

**1.0.2**
* ADDED: state & country tables to init.php

**1.0.1**
* ADDED: readme.txt
* FIXED: boolean type

**1.0.0**
* Initial import

