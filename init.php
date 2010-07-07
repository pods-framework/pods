<?php
/*
Plugin Name: Pods CMS
Plugin URI: http://podscms.org/
Description: Create custom content types in WordPress.
Version: 1.8.9
Author: Matt Gibbs
Author URI: http://podscms.org/about/

Copyright 2010  Matt Gibbs  (email : contact@podscms.org)

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
define('PODS_VERSION', 189);
define('PODS_VERSION_FULL', implode('.', str_split(PODS_VERSION)));
define('PODS_URL', WP_PLUGIN_URL . '/pods');
define('PODS_DIR', WP_PLUGIN_DIR . '/pods');
define('WP_INC_URL', get_bloginfo('wpurl') . '/' . WPINC);

$pods_roles = unserialize(get_option('pods_roles'));

require_once(PODS_DIR . '/functions.php');
require_once(PODS_DIR . '/deprecated.php');
require_once(PODS_DIR . '/classes/Pod.php');
require_once(PODS_DIR . '/classes/PodAPI.php');
require_once(PODS_DIR . '/classes/PodCache.php');
require_once(PODS_DIR . '/classes/PodUI.php');

// JSON support
if (!function_exists('json_encode')) {
    require_once(ABSPATH . '/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php');

    function json_encode($str) {
        $json = new Moxiecode_JSON();
        return $json->encode($str);
    }

    function json_decode($str) {
        $json = new Moxiecode_JSON();
        return $json->decode($str);
    }
}

function pods_content() {
    global $pod_page_exists;

    ob_start();
    eval('?>' . $pod_page_exists['phpcode']);
    echo apply_filters('pods_content', ob_get_clean());
}

class PodInit
{
    function __construct() {
        global $pod_page_exists;

        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_head', array($this, 'wp_head'));
        add_action('template_redirect', array($this, 'template_redirect'));
        add_action('delete_attachment', array($this, 'delete_attachment'));
        add_shortcode('pods', 'pods_shortcode');

        $pod_page_exists = pod_page_exists();

        if (false !== $pod_page_exists) {
            if (empty($pods) || 404 != $pods) {
                add_filter('redirect_canonical', array($this, 'kill_redirect'));
                add_filter('wp_title', array($this, 'wp_title'), 0, 3);
                add_filter('status_header', array($this, 'status_header'));
                add_action('plugins_loaded', array($this, 'precode'));
                add_action('wp', array($this, 'silence_404'));
            }
        }
    }

    function init() {
        // Setup DB tables
        if ($installed = (int) get_option('pods_version')) {
            if ($installed < PODS_VERSION) {
                include(PODS_DIR . '/sql/update.php');
            }
        }
        else {
            $sql = file_get_contents(PODS_DIR . '/sql/dump.sql');
            $sql = explode(";\n", str_replace('wp_', '@wp_', $sql));
            for ($i = 0, $z = count($sql); $i < $z; $i++) {
                pod_query($sql[$i], 'Cannot setup SQL tables');
            }
            delete_option('pods_version');
            add_option('pods_version', PODS_VERSION);
        }

        // Check for .htaccess
        if (!file_exists(ABSPATH . '.htaccess')) {
            if (!copy(PODS_DIR . '/htaccess.txt', ABSPATH . '.htaccess')) {
                echo 'Please copy "htaccess.txt" to "' . ABSPATH . '.htaccess"';
            }
        }

        // Session start
        if (false === headers_sent() && '' == session_id()) {
            session_start();
        }

        // Load necessary JS
        wp_enqueue_script('jquery');
        wp_enqueue_script('pods-ui', PODS_URL . '/ui/js/pods.ui.js');
    }

    function precode() {
        global $pods, $pod_page_exists;
        eval('?>' . $pod_page_exists['precode']);
    }

    function admin_menu() {
        $submenu = array();
        $result = pod_query("SELECT name, label, is_toplevel FROM @wp_pod_types ORDER BY label, name");
        while ($row = mysql_fetch_array($result)) {
            $name = $row['name'];
            $label = trim($row['label']);
            $label = ('' != $label) ? $label : $name;
            if (pods_access("pod_$name")) {
                if (1 == $row['is_toplevel']) {
                    add_object_page($label, $label, 'read', "pods-manage-$name");
                    add_submenu_page("pods-manage-$name", 'Edit', 'Edit', 'read', "pods-manage-$name", array($this, 'pods_content_page'));
                    add_submenu_page("pods-manage-$name", 'Add New', 'Add New', 'read', "pod-$name", array($this, 'pods_content_page'));
                }
                else {
                    $submenu[] = $row;
                }
            }
        }

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        add_object_page('Pods', 'Pods', 'read', 'pods');
        add_submenu_page('pods', 'Setup', 'Setup', 'read', 'pods', array($this, 'pods_setup_page'));
        if (pods_access('manage_content')) {
            add_submenu_page('pods', 'Manage Content', 'Manage Content', 'read', 'pods-manage', array($this, 'pods_content_page'));
        }
        if (pods_access('manage_packages')) {
            add_submenu_page('pods', 'Package Manager', 'Package Manager', 'read', 'pods-package', array($this, 'pods_package_page'));
        }
        if (pods_access('manage_menu')) {
            add_submenu_page('pods', 'Menu Editor', 'Menu Editor', 'read', 'pods-menu', array($this, 'pods_menu_page'));
        }
        foreach ($submenu as $item) {
            $name = $item['name'];
            $label = trim($item['label']);
            $label = ('' != $label) ? $label : $name;
            add_submenu_page('pods', "Add $label", "Add $label", 'read', "pod-$name", array($this, 'pods_content_page'));
        }
    }

    function wp_head() {
?>
<!-- Pods CMS <?php echo PODS_VERSION_FULL; ?> -->
<?php
    }

    function kill_redirect() {
        return false;
    }

    function wp_title($title, $sep, $seplocation) {
        global $pods, $pod_page_exists;

        $page_title = trim($pod_page_exists['title']);

        if (0 < strlen($page_title)) {
            if (is_object($pods)) {
                $page_title = preg_replace_callback("/({@(.*?)})/m", array($pods, "parse_magic_tags"), $page_title);
            }
            $title = $page_title;
        }
        else {
            $uri = explode('?', $_SERVER['REQUEST_URI']);
            $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
            $uri = preg_replace("@(-|_)@", " ", $uri);
            $uri = explode('/', $uri);

            $title = '';
            foreach ($uri as $key => $page_title) {
                $title .= ('right' == $seplocation) ? ucwords($page_title) . " $sep " : " $sep " . ucwords($page_title);
            }
        }
        return apply_filters('pods_title', $title);
    }

    function status_header() {
        return $_SERVER['SERVER_PROTOCOL'] . ' 200 OK';
    }

    function silence_404() {
        global $wp_query;
        $wp_query->query_vars['error'] = '';
        $wp_query->is_404 = false;
    }

    function template_redirect() {
        global $pod_page_exists;

        if ($row = $pod_page_exists) {
            /*
             * Create pods.php in your theme directory, and
             * style it to suit your needs. Some helpful functions:
             *
             * get_header()
             * pods_content()
             * get_sidebar()
             * get_footer()
             */
            $phpcode = $row['phpcode'];
            $page_template = $row['page_template'];
            $pods_theme_path = STYLESHEETPATH . '/pods.php';

            if (!empty($page_template) && file_exists(STYLESHEETPATH . '/' . $page_template)) {
                include STYLESHEETPATH . '/' . $page_template;
            }
            elseif (file_exists($pods_theme_path)) {
                include $pods_theme_path;
            }
            else {
                get_header();
                pods_content();
                get_sidebar();
                get_footer();
            }
            exit;
        }
    }

    function delete_attachment($postid) {
        $result = pod_query("SELECT id FROM @wp_pod_fields WHERE coltype = 'file'");
        if (0 < mysql_num_rows($result)) {
            while ($row = mysql_fetch_assoc($result)) {
                $field_ids[] = $row['id'];
            }
            $field_ids = implode(',', $field_ids);

            // Remove all references to the deleted attachment
            pod_query("DELETE FROM @wp_pod_rel WHERE field_id IN ($field_ids) AND tbl_row_id = $postid");
        }
    }

    function pods_setup_page() {
        include PODS_DIR . '/ui/manage.php';
    }

    function pods_content_page() {
        include PODS_DIR . '/ui/manage_content.php';
    }

    function pods_package_page() {
        include PODS_DIR . '/ui/manage_packages.php';
    }

    function pods_menu_page() {
        include PODS_DIR . '/ui/manage_menu.php';
    }
}

$cache = PodCache::instance();
$init = new PodInit();
