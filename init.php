<?php
/*
Plugin Name: Pods
Plugin URI: http://pods.uproot.us/
Description: The WordPress CMS Plugin
Version: 1.6.1
Author: Matt Gibbs
Author URI: http://pods.uproot.us/

Copyright 2009  Matt Gibbs  (email : logikal16@gmail.com)

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
$pods_latest = 161;

function pods_init()
{
    global $table_prefix, $pods_latest;
    $dir = WP_PLUGIN_DIR . '/pods/sql';

    // Check for PHP 5.2+
    if (!function_exists('json_encode'))
    {
        die('Please upgrade to PHP 5.2+ before installing Pods!');
    }

    // Get the installed version
    if ($installed = (int) get_option('pods_version'))
    {
        if ($installed < $pods_latest)
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
            pod_query($sql[$i], 'Cannot setup SQL tables');
        }
        delete_option('pods_version');
        add_option('pods_version', $pods_latest);
    }

    // Check for .htaccess
    if (!file_exists(ABSPATH . '.htaccess'))
    {
        if (!copy(WP_PLUGIN_DIR . '/pods/htaccess.txt', ABSPATH . '.htaccess'))
        {
            echo 'Please copy "htaccess.txt" to "' . ABSPATH . '.htaccess"';
        }
    }
}

function pods_menu()
{
    $submenu = array();
    $result = pod_query("SELECT name, label, is_toplevel FROM @wp_pod_types ORDER BY name");
    if (0 < mysql_num_rows($result))
    {
        while ($row = mysql_fetch_array($result))
        {
            $name = $row['name'];
            $label = trim($row['label']);
            $label = ('' != $label) ? $label : $name;

            if (pods_access("pod_$name"))
            {
                if (1 != $row['is_toplevel'])
                {
                    $submenu[] = $row;
                }
                else
                {
                    add_object_page($label, $label, 0, "pods-browse-$name");
                    add_submenu_page("pods-browse-$name", 'Edit', 'Edit', 0, "pods-browse-$name", 'pods_content_page');
                    add_submenu_page("pods-browse-$name", 'Add New', 'Add New', 0, "pod-$name", 'pods_content_page');
                }
            }
        }
    }

    add_object_page('Pods', 'Pods', 0, 'pods');
    add_submenu_page('pods', 'Setup', 'Setup', 0, 'pods', 'pods_options_page');

    if (pods_access('manage_content'))
    {
        add_submenu_page('pods', 'Browse Content', 'Browse Content', 0, 'pods-browse', 'pods_content_page');
    }
    if (pods_access('manage_packages'))
    {
        add_submenu_page('pods', 'Package Manager', 'Package Manager', 0, 'pods-package', 'pods_package_page');
    }
    if (pods_access('manage_menu'))
    {
        add_submenu_page('pods', 'Menu Editor', 'Menu Editor', 0, 'pods-menu', 'pods_menu_page');
    }

    foreach ($submenu as $item)
    {
        $name = $item['name'];

        if (pods_access("pod_$name"))
        {
            add_submenu_page('pods', "Add $name", "Add $name", 0, "pod-$name", 'pods_content_page');
        }
    }
}

function pods_options_page()
{
    global $pods_url, $pods_latest, $pods_roles, $table_prefix;
    include WP_PLUGIN_DIR . '/pods/manage.php';
}

function pods_content_page()
{
    global $pods_url;
    include WP_PLUGIN_DIR . '/pods/manage/manage_content.php';
}

function pods_package_page()
{
    global $pods_url;
    include WP_PLUGIN_DIR . '/pods/packages.php';
}

function pods_menu_page()
{
    global $pods_url;

    define('WP_INC_URL', str_replace('wp-content', 'wp-includes', WP_CONTENT_URL));
    include WP_PLUGIN_DIR . '/pods/manage/manage_menu.php';
}

function pods_meta()
{
    global $pods_latest;

    $pods_latest = implode('.', str_split($pods_latest));
?>
<meta name="CMS" content="Pods <?php echo $pods_latest; ?>" />
<?php
}

function pods_title($title, $sep, $seplocation)
{
    if (false !== strpos($title, 'Page not found'))
    {
        global $podpage_exists;

        $page_title = trim($podpage_exists['page_title']);

        if (0 < strlen($page_title))
        {
            $title = str_replace('Page not found', $page_title, $title);
        }
        else
        {
            $uri = explode('?', $_SERVER['REQUEST_URI']);
            $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
            $uri = preg_replace("@(-|_)@", " ", $uri);
            $uri = explode('/', $uri);

            $title = '';
            foreach ($uri as $key => $page_title)
            {
                $title .= ('right' == $seplocation) ? ucwords($page_title) . " $sep " : " $sep " . ucwords($page_title);
            }
        }
    }
    return $title;
}

function get_content()
{
    global $phpcode, $post;

    if (!empty($phpcode))
    {
        ob_start();
        eval("?>$phpcode");
        $content = ob_get_clean();
        remove_filter('the_content', 'st_add_widget');
        remove_filter('the_content', 'prepend_attachment');
        remove_filter('the_content', 'wpautop');
        remove_filter('the_content', 'convert_chars');
        remove_filter('the_content', 'wptexturize');
    }
    elseif (!empty($post))
    {
        $content = $post->post_content;
    }
    echo apply_filters('the_content', $content);
}

function pods_redirect()
{
    global $phpcode, $podpage_exists;

    if ($row = $podpage_exists)
    {
        $phpcode = $row['phpcode'];
        $page_template = $row['page_template'];

        include WP_PLUGIN_DIR . '/pods/router.php';
        die();
    }
}

function pods_404()
{
    return 'HTTP/1.1 200 OK';
}

function kill_redirect()
{
    return false;
}

function podpage_exists()
{
    $uri = explode('?', $_SERVER['REQUEST_URI']);
    $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
    $uri = empty($uri) ? '/' : "/$uri/";

    if (false !== strpos($uri, 'wp-admin'))
    {
        return false;
    }

    // Handle subdirectory installations
    $baseuri = get_bloginfo('url');
    $baseuri = substr($baseuri, strpos($baseuri, '//') + 2);
    $baseuri = str_replace($_SERVER['HTTP_HOST'], '', $baseuri);
    $baseuri = str_replace($baseuri, '', $uri);

    // See if the custom template exists
    $result = pod_query("SELECT * FROM @wp_pod_pages WHERE uri IN('$uri', '$baseuri') LIMIT 1");
    if (1 > mysql_num_rows($result))
    {
        // Find any wildcards
        $sql = "
        SELECT
            *
        FROM
            @wp_pod_pages
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
        return mysql_fetch_assoc($result);
    }
    return false;
}

// Setup DB tables, get the gears turning
require_once WP_PLUGIN_DIR . '/pods/functions.php';
require_once WP_PLUGIN_DIR . '/pods/Pod.class.php';

pods_init();

$pods_url = WP_PLUGIN_URL . '/pods';
$pods_roles = unserialize(get_option('pods_roles'));
$podpage_exists = podpage_exists();

// Hook for admin menu
add_action('admin_menu', 'pods_menu', 9999);

// Hook for Pods branding
add_action('wp_head', 'pods_meta', 0);

// Hook for redirection
add_action('template_redirect', 'pods_redirect');

// Filters for 404 handling
if (false !== $podpage_exists)
{
    add_filter('redirect_canonical', 'kill_redirect');
    add_filter('wp_title', 'pods_title', 8, 3);
    add_filter('status_header', 'pods_404');
}

