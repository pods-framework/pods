<?php
class PodsInit
{

    /**
     * Setup and Initiate Pods
     * 
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.8.9
     */
    function __construct () {
        global $pod_page_exists, $pods_admin;

        add_action('init', array(&$this, 'init'));

        // Activate and Install
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('wpmu_new_blog', array(&$this, 'new_blog'), 10, 6);
        if (version_compare(get_option('pods_version'), PODS_VERSION, '<'))
            $this->setup();
        
        $pods_admin = pods_admin_ui();
        
        add_shortcode('pods', 'pods_shortcode');
        add_action('delete_attachment', array(&$this, 'delete_attachment'));
        
        if (!defined('PODS_DISABLE_POD_PAGE_CHECK')) {
            $pod_page_exists = pod_page_exists();

            if (false !== $pod_page_exists) {
                if (empty($pods) || 404 != $pods) {
                    add_action('template_redirect', array(&$this, 'template_redirect'));
                    add_filter('redirect_canonical', array(&$this, 'kill_redirect'));
                    add_filter('wp_title', array(&$this, 'wp_title'), 0, 3);
                    add_filter('body_class', array(&$this, 'body_class'), 0, 1);
                    add_filter('status_header', array(&$this, 'status_header'));
                    add_action('plugins_loaded', array(&$this, 'precode'));
                    add_action('wp', array(&$this, 'silence_404'));
                }
            }
        }
    }

    function activate () {
        global $wpdb;
        if (function_exists('is_multisite') && is_multisite() && isset($_GET['networkwide']) && 1 == $_GET['networkwide']) {
            $blogids = $wpdb->get_col($wpdb->prepare("SELECT `blog_id` FROM {$wpdb->blogs}"));
            foreach ($blogids as $blogid)
                $this->setup($blogid);
        }
        else
            $this->setup();
    }

    function new_blog ($blogid, $user_id, $domain, $path, $site_id, $meta) {
        if (function_exists('is_multisite') && is_multisite() && is_plugin_active_for_network('pods/init.php'))
            $this->setup($blogid);
    }

    function setup ($blog_id = null) {
        global $wpdb;

        // Switch DB table prefixes
        if (null !== $blog_id && $blog_id != $wpdb->blogid)
            PodsData::switch_site($blog_id);
        else
            $blog_id = null;

        // Setup DB tables
        $pods_version = get_option('pods_version');
        if (!empty($pods_version) && false === strpos($pods_version, '.'))
            $pods_version = implode('.', str_split($pods_version));
        if (!empty($pods_version)) {
            if (version_compare($pods_version, PODS_VERSION, '<'))
                include(PODS_DIR . '/sql/update.php');
        }
        else {
            $sql = file_get_contents(PODS_DIR . '/sql/dump.sql');
            $sql = explode(";\n", str_replace('wp_', '@wp_', $sql));
            for ($i = 0, $z = count($sql); $i < $z; $i++) {
                pods_query($sql[$i], 'Cannot setup SQL tables');
            }
        }
        delete_option('pods_version');
        add_option('pods_version', PODS_VERSION, '', 'yes');

        // Restore DB table prefix (if switched)
        if (null !== $blog_id)
            PodsData::restore_site();
    }

    function init () {
        // Session start
        if (false === headers_sent() && '' == session_id())
            @session_start();
    }

    function delete_attachment ($postid) {
        $result = pods_query("SELECT `id` FROM `@wp_pods_fields` WHERE `type` = 'file'");
        if (!empty($results)) {
            foreach ($results as $row) {
                $field_ids[] = $row->id;
            }
            $field_ids = implode(',', $field_ids);

            // Remove all references to the deleted attachment
            pods_query("DELETE FROM `@wp_pods_rel` WHERE `field_id` IN ({$field_ids}) AND `item_id` = {$postid}");
        }
    }
    
    
    // Pod Page Code

    function precode () {
        global $pods, $pod_page_exists;
        eval('?>' . $pod_page_exists['precode']);
    }

    function kill_redirect () {
        return false;
    }

    function wp_title ($title, $sep, $seplocation) {
        global $pods, $pod_page_exists;

        $page_title = $pod_page_exists['title'];

        if (0 < strlen(trim($page_title))) {
            if (is_object($pods)) {
                $page_title = preg_replace_callback("/({@(.*?)})/m", array($pods, "parse_magic_tags"), $page_title);
            }
            $title = ('right' == $seplocation) ? $page_title . " $sep " : " $sep " . $page_title;
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

    function body_class ($classes) {
        global $pods;
        $classes[] = 'pods';
        $uri = explode('?',$_SERVER['REQUEST_URI']);
        $uri = explode('#',$uri[0]);
        $uri = $uri[0];
        $classes[] = 'pod-page-' . str_replace('--', '-', str_replace('--', '-', str_replace('_', '-', str_replace('/', '-', sanitize_title($uri, '', 'save')))));
        if (is_object($pods))
            $classes[] = 'pod-' . str_replace('--', '-', str_replace('_', '-', $pods->pod));
        return apply_filters('pods_body_class', $classes, $uri);
    }

    function status_header () {
        return $_SERVER['SERVER_PROTOCOL'] . ' 200 OK';
    }

    function silence_404 () {
        global $wp_query;
        $wp_query->query_vars['error'] = '';
        $wp_query->is_404 = false;
    }

    function template_redirect () {
        global $pod_page_exists;

        if (false !== $pod_page_exists) {
            /*
             * Create pods.php in your theme directory, and
             * style it to suit your needs. Some helpful functions:
             *
             * get_header()
             * pods_content()
             * get_sidebar()
             * get_footer()
             */

            do_action('pods_page_start', $pod_page_exists);
            $template = false;
            if (!empty($pod_page_exists['page_template']) && '' != locate_template(array($pod_page_exists['page_template']), true)) {
                $template = $pod_page_exists['page_template'];
                // found the template and included it, we're good to go!
            }
            elseif ('' != locate_template(array('page-pods.php'), true)) {
                $template = 'page-pods.php';
                // found the template and included it, we're good to go!
            }
            elseif ('' != locate_template(array('pods.php'), true)) {
                $template = 'pods.php';
                // found the template and included it, we're good to go!
            }
            else {
                // templates not found in theme, default output
                get_header();
                pods_content();
                get_sidebar();
                get_footer();
            }
            do_action('pods_page_end', $template, $pod_page_exists);
            exit;
        }
    }
}