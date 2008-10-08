<?php
/*
Plugin Name: Pods
Plugin URI: http://wp-pods.googlecode.com
Description: Allows posts to be treated like CMS modules.
Version: 1.0.0
Author: Matt Gibbs
Author URI: http://uproot.us/

Copyright 2008  Matt Gibbs  (email : logikal16@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function initialize()
{
    global $table_prefix;

    $sql = "CREATE TABLE IF NOT EXISTS {$table_prefix}pod (
        id INT unsigned auto_increment primary key,
        row_id INT unsigned,
        post_id INT unsigned,
        datatype TINYINT unsigned
    )";
    mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);

    $sql = "CREATE TABLE IF NOT EXISTS {$table_prefix}pod_types (
        id INT unsigned auto_increment primary key,
        name VARCHAR(32),
        description VARCHAR(128),
        list_filters TEXT,
        tpl_detail TEXT,
        tpl_list TEXT
    )";
    mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);

    $sql = "CREATE TABLE IF NOT EXISTS {$table_prefix}pod_fields (
        id INT unsigned auto_increment primary key,
        datatype TINYINT unsigned,
        name VARCHAR(32),
        coltype VARCHAR(4),
        pickval VARCHAR(32),
        sister_field_id INT unsigned,
        weight TINYINT
    )";
    mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);

    $sql = "CREATE TABLE IF NOT EXISTS {$table_prefix}pod_rel (
        id INT unsigned auto_increment primary key,
        post_id INT unsigned,
        sister_post_id INT unsigned,
        field_id INT unsigned,
        term_id INT unsigned
    )";
    mysql_query($sql) or trigger_error(mysql_error(), E_USER_ERROR);
}

function adminMenu()
{
    // Add new box under Manage > Posts
    add_meta_box('pod', 'Choose a Pod', 'edit_post_page', 'post', 'normal', 'high');

    // Add a new submenu under Manage
    add_management_page('Pods', 'Pods', 8, 'pods', 'edit_options_page');
}

function edit_post_page()
{
    include realpath(dirname(__FILE__) . '/edit-post.php');
}

function edit_options_page()
{
    include realpath(dirname(__FILE__) . '/options.php');
}

function deletePost($post_ID)
{
    mysql_query("DELETE FROM wp_pod WHERE post_id = $post_ID LIMIT 1");
    mysql_query("DELETE FROM wp_pod_rel WHERE post_id = $post_ID");
}

function redirect()
{
    if (is_page() || is_404())
    {
        $uri = explode('?', $_SERVER['REQUEST_URI']);
        $uri = ('/' == substr($uri[0], 0, 1)) ? substr($uri[0], 1) : $uri[0];
        $uri = ('/' == substr($uri, -1)) ? substr($uri, 0, -1) : $uri;
        $uri = empty($uri) ? array('home') : explode('/', $uri);

        // See if the hierarchical template exists
        for ($i = count($uri); $i > 0; $i--)
        {
            $uri_string = implode('/', $uri);
            $tpl_path = realpath(dirname(__FILE__) . "/pages/$uri_string.tpl");
            if (file_exists($tpl_path))
            {
                include realpath(dirname(__FILE__) . '/router.php');
                return;
            }
            array_pop($uri);
        }
    }
}

// Create the DB tables, get the gears turning
initialize();

// Hook for adding admin menus
add_action('admin_menu', 'adminMenu');

// Hook for post deletion
add_action('delete_post', 'deletePost');

// Hook for redirection
add_action('template_redirect', 'redirect');

