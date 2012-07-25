# Pods Framework (2.0 Alpha) for WordPress
![PodsCMS icon](http://podsframework.org/wp-content/themes/pods/images/logo-pods-header.png)

**Pods is a development framework for creating, managing, and deploying customized content types in WordPress.**

## Description

Check out <http://podsframework.org/> for our User Guide and many other resources to help you develop with Pods.

### Create your own content types

A Pod is a content type which contains a user-defined set of fields. Each content type is stored in it's own table, where as WordPress Custom Post Types are normally all stored in one single table for them all.

Create a variety of different fields including: text, paragraph text, date, number, file upload, and relationship (called "pick") fields.

Pick fields are useful if you want to create relationships between your content types. One example is if you want to relate an "event" with one or more "speaker".

### Easily display your content

There are several ways to get Pods data to show up throughout your site:

* Add Pod Pages from within the admin area. Pod Pages support PHP and Wildcard URLs. For example, the Pod Page "events/*" will be the default handler for all pages beginning with "events/". This allows you to have a single page to handle a myriad of different items.
* Add PHP code directly into your WP template files, or wherever else PHP is supported.
* Use shortcode to display lists of Pod items or details of a Pod item within WP Pages or Posts.
* The Pods API allows you to retrieve raw data from and save data to the database.

### Customized Management Panels

Utilize Pods UI (included in Pods 1.10+) to build your own Custom Management panels for your Pods.

### Migrate!

Pods includes a Package Manager, which allows you to import/export Pods (structure-only, no data yet), Pod Templates, Pod Pages, and/or Pod Helpers. You can select which features you want to "package up" and export it for easy migration to other sites or to share your code with other users in our Package Directory.

Pods also includes an easy to use PHP API to allow you to import and export your data via CSV, and other more complex operations.

## Introduction to the Pods CMS Framework

<http://vimeo.com/15086927>