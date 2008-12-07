<?php
/*
Plugin Name: Pods
Plugin URI: http://pods.uproot.us/
Description: The Wordpress CMS Plugin
Version: 1.2.4
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
    $dir = WP_PLUGIN_DIR . '/pods/sql';

    // Setup initial tables
    $result = mysql_query("SHOW TABLES LIKE 'wp_pod_widgets'");
    if (1 > mysql_num_rows($result))
    {
        $sql = explode(";\n", file_get_contents("$dir/dump.sql"));
        for ($i = 0, $z = count($sql); $i < $z - 1; $i++)
        {
            mysql_query($sql[$i]) or die(mysql_error());
        }
    }

    // Update tables
    include("$dir/update.php");
}

function adminMenu()
{
    // Add panel under Manage > Posts
    add_meta_box('pod', 'Choose a Pod', 'edit_post_page', 'post', 'normal', 'high');

    // Add submenu under Tools
    add_management_page('Pods', 'Pods', 8, 'pods', 'edit_options_page');
}

function edit_post_page()
{
    global $pods_url;
    include WP_PLUGIN_DIR . '/pods/edit-post.php';
}

function edit_options_page()
{
    global $pods_url;
    include WP_PLUGIN_DIR . '/pods/options.php';
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

            include WP_PLUGIN_DIR . '/pods/router.php';
            return;
        }
    }
}

// Setup DB tables, get the gears turning
initialize();

$pods_url = WP_PLUGIN_URL . '/pods';

// Hook for adding admin menus
add_action('admin_menu', 'adminMenu');

// Hook for post deletion
add_action('delete_post', 'deletePost');

// Hook for redirection
add_action('template_redirect', 'redirect');

