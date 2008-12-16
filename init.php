<?php
/*
Plugin Name: Pods
Plugin URI: http://pods.uproot.us/
Description: The Wordpress CMS Plugin
Version: 1.2.9
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
    global $table_prefix;
    $dir = WP_PLUGIN_DIR . '/pods/sql';

    // Setup initial tables
    $result = mysql_query("SHOW TABLES LIKE '{$table_prefix}pod_widgets'");
    if (1 > mysql_num_rows($result))
    {
        $sql = file_get_contents("$dir/dump.sql");
        $sql = explode(";\n", str_replace('wp_', $table_prefix, $sql));
        for ($i = 0, $z = count($sql); $i < $z - 1; $i++)
        {
            mysql_query($sql[$i]) or die(mysql_error());
        }
    }

    // Check for .htaccess
    if (!file_exists(ABSPATH . '.htaccess'))
    {
        if (!copy(WP_PLUGIN_DIR . '/pods/.htaccess', ABSPATH . '.htaccess'))
        {
            echo 'Please copy the .htaccess file from plugins/pods/ to the Wordpress root folder!';
        }
    }

    // Update tables
    include("$dir/update.php");
}

function adminMenu()
{
    global $menu, $submenu, $table_prefix, $admin_page_hooks;

    $menu[30] = array('Pods', 8, 'pods', 'Pods', 'menu-top toplevel_page_pods', 'toplevel_page_pods', 'images/generic.png');
    add_submenu_page('pods', 'Setup', 'Setup', 8, 'pods', 'edit_options_page');
    add_submenu_page('pods', 'Browse Content', 'Browse Content', 8, 'pods-browse', 'edit_content_page');

    $result = mysql_query("SELECT name FROM {$table_prefix}pod_types ORDER BY name");
    if (0 < mysql_num_rows($result))
    {
        while ($row = mysql_fetch_array($result))
        {
            add_submenu_page('pods', "Add $row[0]", "Add $row[0]", 8, "pod-$row[0]", 'edit_content_page');
        }
    }
}

function edit_content_page()
{
    global $pods_url, $table_prefix;
    include WP_PLUGIN_DIR . '/pods/content.php';
}

function edit_options_page()
{
    global $pods_url, $table_prefix;
    include WP_PLUGIN_DIR . '/pods/options.php';
}

function redirect()
{
    global $table_prefix;

    if (is_page() || is_404())
    {
        $uri = explode('?', $_SERVER['REQUEST_URI']);
        $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
        $uri = empty($uri) ? '/' : "/$uri/";

        // See if the custom template exists
        $result = mysql_query("SELECT phpcode FROM {$table_prefix}pod_pages WHERE uri = '$uri' LIMIT 1");
        if (1 > mysql_num_rows($result))
        {
            // Find any wildcards
            $sql = "
            SELECT
                phpcode
            FROM
                {$table_prefix}pod_pages
            WHERE
                '$uri' LIKE REPLACE(uri, '*', '%')
            ORDER BY
                uri DESC
            LIMIT
                1
            ";
            $result = mysql_query($sql) or die(mysql_error());
        }

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

// Hook for admin menu
add_action('admin_menu', 'adminMenu');

// Hook for redirection
add_action('template_redirect', 'redirect');

