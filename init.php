<?php
/*
Plugin Name: Pods
Plugin URI: http://pods.uproot.us/
Description: The Wordpress CMS Plugin
Version: 1.4.0
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
$latest = 140;

function pods_init()
{
    global $table_prefix, $latest;
    $dir = WP_PLUGIN_DIR . '/pods/sql';

    // Get the installed version
    if ($installed = (int) get_option('pods_version'))
    {
        if ($installed < $latest)
        {
            include("$dir/update.php");
        }
    }
    // Setup initial tables
    else
    {
        $sql = file_get_contents("$dir/dump.sql");
        $sql = explode(";\n", str_replace('wp_', $table_prefix, $sql));
        for ($i = 0, $z = count($sql) - 1; $i < $z; $i++)
        {
            pod_query($sql[$i]);
        }
        delete_option('pods_version');
        add_option('pods_version', $latest);
    }

    // Check for .htaccess
    if (!file_exists(ABSPATH . '.htaccess'))
    {
        if (!copy(WP_PLUGIN_DIR . '/pods/.htaccess', ABSPATH . '.htaccess'))
        {
            echo 'Please copy the .htaccess file from plugins/pods/ to the Wordpress root folder!';
        }
    }
}

function pods_menu()
{
    global $menu, $table_prefix;

    $menu[30] = array('Pods', 8, 'pods', 'Pods', 'menu-top toplevel_page_pods', 'toplevel_page_pods', 'images/generic.png');
    add_submenu_page('pods', 'Setup', 'Setup', 8, 'pods', 'pods_options_page');
    add_submenu_page('pods', 'Browse Content', 'Browse Content', 8, 'pods-browse', 'pods_content_page');

    $result = pod_query("SELECT name FROM {$table_prefix}pod_types ORDER BY name");
    if (0 < mysql_num_rows($result))
    {
        while ($row = mysql_fetch_array($result))
        {
            add_submenu_page('pods', "Add $row[0]", "Add $row[0]", 8, "pod-$row[0]", 'pods_content_page');
        }
    }
}

function pods_options_page()
{
    global $pods_url, $table_prefix;
    include WP_PLUGIN_DIR . '/pods/options.php';
}

function pods_layout_page()
{
    global $pods_url, $table_prefix;
    include WP_PLUGIN_DIR . '/pods/layout.php';
}

function pods_content_page()
{
    global $pods_url, $table_prefix;
    include WP_PLUGIN_DIR . '/pods/content.php';
}

function pods_meta()
{
    global $latest;

    $latest = "$latest";
    $latest = $latest[0] . '.' . $latest[1] . '.' . $latest[2];
?>
<meta name="CMS" content="Pods <?php echo $latest; ?>" />
<?php
}

function pods_title($title, $sep, $seplocation)
{
    $pieces = explode('?', $_SERVER['REQUEST_URI']);
    $pieces = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $pieces[0]);
    $pieces = preg_replace("(-|_)", "", $pieces);
    $pieces = explode('/', $pieces);
    $title = str_replace(" $sep Page not found", '', $title);

    foreach ($pieces as $key => $page_title)
    {
        $title .= " $sep " . ucwords($page_title);
    }
    return $title;
}

function get_content()
{
    global $phpcode, $post;

    require_once WP_PLUGIN_DIR . '/pods/Pod.class.php';

    // Cleanse the GET variables
    foreach ($_GET as $key => $val)
    {
        ${$key} = mysql_real_escape_string($val);
    }

    if (!empty($phpcode))
    {
        eval("?>$phpcode");
    }
    elseif (!empty($post))
    {
        echo $post->post_content;
    }
}

function pods_redirect()
{
    global $phpcode, $podpage_exists;

    if ($result = $podpage_exists)
    {
        $row = mysql_fetch_assoc($result);
        $phpcode = $row['phpcode'];

        include WP_PLUGIN_DIR . '/pods/router.php';
        die();
    }
}

function pods_404($vars = false)
{
    return false;
}

function podpage_exists()
{
    global $table_prefix;

    $uri = explode('?', $_SERVER['REQUEST_URI']);
    $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
    $uri = empty($uri) ? '/' : "/$uri/";

    // Handle subdirectory installations
    $baseuri = get_bloginfo('url');
    $baseuri = substr($baseuri, strpos($baseuri, '//') + 2);
    $baseuri = str_replace($_SERVER['HTTP_HOST'], '', $baseuri);
    $baseuri = str_replace($baseuri, '', $uri);

    // See if the custom template exists
    $result = pod_query("SELECT phpcode FROM {$table_prefix}pod_pages WHERE uri IN('$uri', '$baseuri') LIMIT 1");
    if (1 > mysql_num_rows($result))
    {
        // Find any wildcards
        $sql = "
        SELECT
            phpcode
        FROM
            {$table_prefix}pod_pages
        WHERE
            '$uri' LIKE REPLACE(uri, '*', '%') OR '$baseuri' LIKE REPLACE(uri, '*', '%')
        ORDER BY
            uri DESC
        LIMIT
            1
        ";
        $result = pod_query($sql);
    }

    if (0 < mysql_num_rows($result))
    {
        return $result;
    }
    return false;
}

// Setup DB tables, get the gears turning
require_once WP_PLUGIN_DIR . '/pods/functions.php';

pods_init();

$podpage_exists = podpage_exists();
$pods_url = WP_PLUGIN_URL . '/pods';

// Hook for admin menu
add_action('admin_menu', 'pods_menu');

// Hook for Pods branding
add_action('wp_head', 'pods_meta');

// Hook for redirection
add_action('template_redirect', 'pods_redirect');

// Filters for 404 handling
if (false !== $podpage_exists)
{
    add_filter('query_vars', 'pods_404');
    add_filter('wp_title', 'pods_title', 8, 3);
}

