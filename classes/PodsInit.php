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
        global $pod_page_exists, $pods, $pods_admin;

        add_action('init', array($this, 'init'));
    }

    function activate () {
        global $wpdb;
        if (function_exists('is_multisite') && is_multisite() && isset($_GET['networkwide']) && 1 == $_GET['networkwide']) {
            $blogids = $wpdb->get_col($wpdb->prepare("SELECT `blog_id` FROM {$wpdb->blogs}"));
            foreach ($blogids as $blogid)
                $this->setup($_blog_id);
        }
        else
            $this->setup();
    }

    function new_blog ($blogid, $user_id, $domain, $path, $site_id, $meta) {
        if (function_exists('is_multisite') && is_multisite() && is_plugin_active_for_network('pods/init.php'))
            $this->setup($blogid);
    }

    function setup ($_blog_id = null) {
        global $wpdb;

        // Switch DB table prefixes
        if (null !== $_blog_id && $_blog_id != $wpdb->blogid)
            PodsData::switch_site($_blog_id);
        else
            $_blog_id = null;

        // Setup DB tables
        $pods_version = get_option('pods_version');
        if (0 < strlen($pods_version) && false === strpos($pods_version, '.'))
            $pods_version = pods_version_to_point($pods_version);
        if (0 < strlen($pods_version)) {
            if (version_compare($pods_version, PODS_VERSION, '<')) {
                do_action('pods_update', PODS_VERSION, $pods_version, $_blog_id);
                if (false !== apply_filters('pods_update_run', null, PODS_VERSION, $pods_version, $_blog_id) && !isset($_GET['pods_bypass_update']))
                    include(PODS_DIR . '/sql/update.php');
                do_action('pods_update_post', PODS_VERSION, $pods_version, $_blog_id);
            }
        }
        else {
            do_action('pods_install', PODS_VERSION, $pods_version, $_blog_id);
            if (false !== apply_filters('pods_install_run', null, PODS_VERSION, $pods_version, $_blog_id) && !isset($_GET['pods_bypass_install'])) {
                $sql = file_get_contents(PODS_DIR . '/sql/dump.sql');
                $sql = apply_filters('pods_install_sql', $sql, PODS_VERSION, $pods_version, $_blog_id);
                $charset_collate = 'DEFAULT CHARSET utf8';
                if (!empty($wpdb->charset))
                    $charset_collate = "DEFAULT CHARSET {$wpdb->charset}";
                if (!empty($wpdb->collate))
                    $charset_collate .= " COLLATE {$wpdb->collate}";
                if ('DEFAULT CHARSET utf8' != $charset_collate)
                    $sql = str_replace('DEFAULT CHARSET utf8', $charset_collate, $sql);
                $sql = explode(";\n", str_replace('wp_', $wpdb->prefix, $sql));
                for ($i = 0, $z = count($sql); $i < $z; $i++) {
                    pods_query($sql[$i], 'Cannot setup SQL tables');
                }
            }
            delete_option('pods_version');
            add_option('pods_version', PODS_VERSION);
            do_action('pods_install_post', PODS_VERSION, $pods_version, $_blog_id);
        }
	
        // Restore DB table prefix (if switched)
        if (null !== $_blog_id)
            PodsData::restore_site();
    }

    function init () {
        global $pod_page_exists, $pods, $pods_admin;
	
        // Session start
        if (((defined('WP_DEBUG') && WP_DEBUG) || false === headers_sent()) && '' == session_id())
            @session_start();

        // Activate and Install (@to-do: don't install, display notice if not 'installed' with a link for user to run install)
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('wpmu_new_blog', array($this, 'new_blog'), 10, 6);
        $pods_version = get_option('pods_version');
        if (empty($pods_version) || false === strpos($pods_version, '.') || version_compare($pods_version, PODS_VERSION, '<'))
            $this->setup();
        
        $pods_admin = pods_admin_ui();
        
        add_shortcode('pods', 'pods_shortcode');
        add_action('delete_attachment', array($this, 'delete_attachment'));
        
        if (!defined('PODS_DISABLE_POD_PAGE_CHECK')) {
            $pod_page_exists = pod_page_exists();

            if (false !== $pod_page_exists) {
                if (404 != $pods && (!is_object($pods) || !is_wp_error($pods))) {
                    add_action('template_redirect', array($this, 'template_redirect'));
                    add_filter('redirect_canonical', '__return_false');
		    add_action('wp_head', array($this, 'wp_head'));
                    add_filter('wp_title', array($this, 'wp_title'), 0, 3);
                    add_filter('body_class', array($this, 'body_class'), 0, 1);
                    add_filter('status_header', array($this, 'status_header'));
                    add_action('after_setup_theme', array($this, 'precode'));
                    add_action('wp', array($this, 'silence_404'));
                }
            }
        }

        // Load necessary JS
        wp_register_script('jqmodal', PODS_URL . '/ui/js/jqmodal.js', array('jquery'));
        wp_register_script('pods-ui', PODS_URL . '/ui/js/pods.ui.js', array('jquery', 'jqmodal'));

        $security_settings = array('pods_disable_file_browser' => 0,
                                   'pods_files_require_login' => 0,
                                   'pods_files_require_login_cap' => '',
                                   'pods_disable_file_upload' => 0,
                                   'pods_upload_require_login' => 0,
                                   'pods_upload_require_login_cap' => '');
        foreach ($security_settings as $security_setting => $setting) {
            $setting = get_option($security_setting);
            if (!empty($setting))
                $security_settings[$security_setting] = $setting;
        }
        foreach ($security_settings as $security_setting => $setting) {
            if (0 == $setting)
                $setting = false;
            elseif (1 == $setting)
                $setting = true;
            if (in_array($security_setting, array('pods_files_require_login', 'pods_upload_require_login'))) {
                if (0 < strlen($security_settings[$security_setting . '_cap']))
                    $setting = $security_settings[$security_setting . '_cap'];
            }
            elseif (in_array($security_setting, array('pods_files_require_login_cap', 'pods_upload_require_login_cap')))
                continue;
            if (!defined(strtoupper($security_setting)))
                define(strtoupper($security_setting), $setting);
        }
    }

    function delete_attachment ($_ID) {
        $result = pods_query("SELECT `id` FROM `@wp_pods_fields` WHERE `type` = 'file'");
        if (!empty($results)) {
            $field_ids = array();
            foreach ($results as $row) {
                $field_ids[] = $row->id;
            }
            $field_ids = implode(',', $field_ids);

            if (!empty($field_ids)) {
                // Remove all references to the deleted attachment
                do_action('pods_delete_attachment', $_ID, $field_ids);
                pods_query("DELETE FROM `@wp_pods_rel` WHERE `field_id` IN ({$field_ids}) AND `item_id` = {$_ID}");
	    }
        }
    }
    
    
    // Pod Page Code

    function precode () {
        global $pods, $pod_page_exists;

        $function_or_file = str_replace('*', 'w', $pod_page_exists['uri']);
        $check_file = 'precode-' . $function_or_file;
        if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_PAGE_FILES') || !PODS_PAGE_FILES))
            $check_file = false;
        if (false !== $check_function && false !== $check_file)
            $function_or_file = pods_function_or_file($function_or_file, false, 'page', $check_file);
        else
            $function_or_file = false;

        $content = false;
        if (!$function_or_file && 0 < strlen(trim($pod_page_exists['precode'])))
            $content = $pod_page_exists['precode'];

        if (false === $content && false !== $function_or_file && isset($function_or_file['file']))
            locate_template($function_or_file['file'], true, true);
        elseif (false !== $content) {
            if (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                eval("?>$content");
        }

        do_action('pods_page_precode', $pod_page_exists, $pods);
        if (!is_object($pods) && (404 == $pods || is_wp_error($pods))) {
            remove_action('template_redirect', array($this, 'template_redirect'));
            remove_action('wp_head', array($this, 'wp_head'));
            remove_filter('redirect_canonical', '__return_false');
            remove_filter('wp_title', array($this, 'wp_title'));
            remove_filter('body_class', array($this, 'body_class'));
            remove_filter('status_header', array($this, 'status_header'));
            remove_action('wp', array($this, 'silence_404'));
        }
    }


    function wp_head() {
        global $pods;
        do_action('pods_wp_head');
        if (!defined('PODS_DISABLE_VERSION_OUTPUT') || !PODS_DISABLE_VERSION_OUTPUT) {
?>
<!-- Pods CMS <?php echo PODS_VERSION; ?> -->
<?php
        }
        if ((!defined('PODS_DISABLE_META') || !PODS_DISABLE_META) && is_object($pods) && !is_wp_error($pods)) {
            if (isset($pods->meta) && is_array($pods->meta)) {
                foreach ($pods->meta as $name => $content) {
                    if ('title' == $name)
                        continue;
    ?>
    <meta name="<?php echo esc_attr($name); ?>" content="<?php echo esc_attr($content); ?>" />
    <?php
                }
            }
            if (isset($pods->meta_properties) && is_array($pods->meta_properties)) {
                foreach ($pods->meta_properties as $property => $content) {
    ?>
    <meta property="<?php echo esc_attr($property); ?>" content="<?php echo esc_attr($content); ?>" />
    <?php
                }
            }
            if (isset($pods->meta_extra) && 0 < strlen($pods->meta_extra))
                echo $pods->meta_extra;
        }
    }

    function wp_title ($title, $sep, $seplocation) {
        global $pods, $pod_page_exists;

        $page_title = $pod_page_exists['title'];

        if (0 < strlen(trim($page_title))) {
            if (is_object($pods) && !is_wp_error($pods))
                $page_title = preg_replace_callback("/({@(.*?)})/m", array($pods, "parse_magic_tags"), $page_title);
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
        if ((!defined('PODS_DISABLE_META') || !PODS_DISABLE_META) && is_object($pods) && !is_wp_error($pods) && isset($pods->meta) && is_array($pods->meta) && isset($pods->meta['title']))
            $title = $pods->meta['title'];
        return apply_filters('pods_title', $title, $sep, $seplocation);
    }

    function body_class ($classes) {
        global $pods, $pod_page_exists;
        $classes[] = 'pods';
        $uri = explode('?', $pod_page_exists['uri']);
        $uri = explode('#', $uri[0]);
        $class = str_replace(array('*', '/'), array('_w_', '-'), $uri[0]);
	$class = sanitize_title($class);
	$class = str_replace(array('_', '--', '--'), '-', $class);
	$class = trim($class, '-');
        $classes[] = 'pod-page-' . $class);
        if (is_object($pods) && !is_wp_error($pods)) {
	    $class = sanitize_title($pods->pod);
	    $class = str_replace(array('_', '--', '--'), '-', $class);
	    $class = trim($class, '-');
            $classes[] = 'pod-' . $class;
        }
        if ((!defined('PODS_DISABLE_BODY_CLASSES') || !PODS_DISABLE_BODY_CLASSES) && is_object($pods) && !is_wp_error($pods) && isset($pods->body_classes))
            $classes[] = $pods->body_classes;
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
        global $pods, $pod_page_exists;

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
            if ((!defined('PODS_DISABLE_DYNAMIC_TEMPLATE') || !PODS_DISABLE_DYNAMIC_TEMPLATE) && is_object($pods) && !is_wp_error($pods) && isset($pods->page_template) && !empty($pods->page_template) && '' != locate_template(array($pods->page_template), true)) {
                $template = $pods->page_template;
                // found the template and included it, we're good to go!
            }
            elseif (!empty($pod_page_exists['page_template']) && '' != locate_template(array($pod_page_exists['page_template']), true)) {
                $template = $pod_page_exists['page_template'];
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