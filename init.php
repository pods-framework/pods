<?php
/*
Plugin Name: Pods
Plugin URI: http://pods.uproot.us/
Description: The Wordpress CMS Plugin
Version: 1-1-8
Author: Matt Gibbs
Author URI: http://pods.uproot.us/

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
    $dir = realpath(dirname(__FILE__) . '/sql');

    // Add core tables
    multi_query("$dir/core.sql");

    // Add region tables
    $result = mysql_query("SHOW TABLES LIKE 'tbl_country'");
    if (1 > mysql_num_rows($result))
    {
        multi_query("$dir/regions.sql");
    }

    // Add defaults
    $result = mysql_query("SELECT id FROM wp_pod_pages LIMIT 1");
    if (1 > mysql_num_rows($result))
    {
        $list = mysql_real_escape_string(file_get_contents("$dir/list.sql"));
        $detail = mysql_real_escape_string(file_get_contents("$dir/detail.sql"));
        mysql_query("INSERT INTO wp_pod_pages (uri, phpcode) VALUES ('/list/','$list'),('/detail/','$detail')");

        // Add default pods
    }

    // Update tables
    include("$dir/update.php");
}

function multi_query($sql)
{
    $sql = explode(';', file_get_contents($sql));
    for ($i = 0, $cnt = count($sql) - 1; $i < $cnt; $i++)
    {
        mysql_query($sql[$i]) or trigger_error(mysql_error(), E_USER_ERROR);
    }
}

function adminMenu()
{
    // Add new box under Manage > Posts
    add_meta_box('pod', 'Choose a Pod', 'edit_post_page', 'post', 'normal', 'high');

    // Add new submenu under Tools
    add_management_page('Pods', 'Pods', 8, 'pods', 'edit_options_page');

    // Add new submenu under Tools
    add_management_page('Pods Pages', 'Pods Pages', 8, 'pods_pages', 'edit_custom_pages');
}

function edit_post_page()
{
    include realpath(dirname(__FILE__) . '/edit-post.php');
}

function edit_options_page()
{
    include realpath(dirname(__FILE__) . '/options.php');
}

function edit_custom_pages()
{
    include realpath(dirname(__FILE__) . '/pages.php');
}

function deletePost($post_ID)
{
    // Get the dtname and row_id from the post id
    $sql = "
    SELECT
        t.name, p.row_id
    FROM
        wp_pod p
    INNER JOIN
        wp_pod_types t ON t.id = p.datatype
    WHERE
        p.post_id = $post_ID
    LIMIT
        1
    ";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);

    mysql_query("DELETE FROM tbl_$row[0] WHERE id = $row[1] LIMIT 1");
    mysql_query("UPDATE wp_pod_rel SET sister_post_id = NULL WHERE sister_post_id = $post_ID");
    mysql_query("DELETE FROM wp_pod WHERE post_id = $post_ID LIMIT 1");
    mysql_query("DELETE FROM wp_pod_rel WHERE post_id = $post_ID");
}

function redirect()
{
    if (is_page() || is_404())
    {
        $uri = explode('?', $_SERVER['REQUEST_URI']);
        $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
        $uri = empty($uri) ? '/' : "/$uri/";

        // See if the custom template exists
        $result = mysql_query("SELECT phpcode FROM wp_pod_pages WHERE uri = '$uri' LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            $row = mysql_fetch_assoc($result);
            $phpcode = $row['phpcode'];

            include realpath(dirname(__FILE__) . '/router.php');
            return;
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

