<?php
class PodInit
{
    /**
     * Constructor - Pods Initialization
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     */
    function __construct() {
        global $pod_page_exists, $pods;

        // Activate and Install
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('wpmu_new_blog', array($this, 'new_blog'), 10, 6);

        $installed = get_option('pods_version');
        if (0 < strlen($installed) && false === strpos($installed, '.'))
            $installed = pods_version_to_point($installed);
        if (version_compare($installed, PODS_VERSION, '<'))
            $this->setup();
        elseif (version_compare($installed, PODS_VERSION, '>')) {
            delete_option('pods_version');
            add_option('pods_version', PODS_VERSION);
        }

        add_action('init', array($this, 'init'));
        add_action('after_setup_theme', array($this, 'deprecated'));
        add_action('admin_menu', array($this, 'admin_menu'), 99);
        add_action('template_redirect', array($this, 'template_redirect'));
        add_action('delete_attachment', array($this, 'delete_attachment'));
        add_shortcode('pods', 'pods_shortcode');

        if (!defined('PODS_DISABLE_POD_PAGE_CHECK')) {
            $pod_page_exists = pod_page_exists();

            if (false !== $pod_page_exists) {
                add_action('wp_head', array($this, 'wp_head'));
                add_filter('redirect_canonical', array($this, 'kill_redirect'));
                add_filter('wp_title', array($this, 'wp_title'), 0, 3);
                add_filter('body_class', array($this, 'body_class'), 0, 1);
                add_filter('status_header', array($this, 'status_header'));
                if (defined('PODS_PAGE_PRECODE_TIMING') && false !== PODS_PAGE_PRECODE_TIMING)
                    add_action('after_setup_theme', array($this, 'precode'));
                else
                    add_action('plugins_loaded', array($this, 'precode'));
                add_action('wp', array($this, 'silence_404'));
            }
        }
    }

    function activate () {
        global $wpdb;
        if (function_exists('is_multisite') && is_multisite() && isset($_GET['networkwide']) && 1 == $_GET['networkwide']) {
            $blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
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

    function setup ($blogid = null) {
        global $wpdb;
        if (null !== $blogid && $blogid != $wpdb->blogid) {
            $old_blogid = $wpdb->blogid;
            switch_to_blog($blogid);
        }
        // Setup DB tables
        $installed = get_option('pods_version');
        if (0 < strlen($installed) && false === strpos($installed, '.'))
            $installed = pods_version_to_point($installed);
        if (0 < strlen($installed)) {
            if (version_compare($installed, PODS_VERSION, '<')) {
                do_action('pods_update', PODS_VERSION, $installed, $blogid);
                if (null === apply_filters('pods_update_run', null, PODS_VERSION, $installed, $blogid) && !isset($_GET['pods_bypass_update']))
                    include(PODS_DIR . '/sql/update.php');
                do_action('pods_update_post', PODS_VERSION, $installed, $blogid);
            }
        }
        else {
            do_action('pods_install', PODS_VERSION, $installed, $blogid);
            if (null === apply_filters('pods_install_run', null, PODS_VERSION, $installed, $blogid) && !isset($_GET['pods_bypass_install'])) {
                $sql = file_get_contents(PODS_DIR . '/sql/dump.sql');
                $sql = str_replace("\r", "\n", $sql);
                $sql = str_replace("\n\n", "\n", $sql);
                $sql = apply_filters('pods_install_sql', $sql, PODS_VERSION, $installed, $blogid);
                $charset_collate = 'DEFAULT CHARSET utf8';
                if (!empty($wpdb->charset))
                    $charset_collate = "DEFAULT CHARSET {$wpdb->charset}";
                if (!empty($wpdb->collate))
                    $charset_collate .= " COLLATE {$wpdb->collate}";
                if ('DEFAULT CHARSET utf8' != $charset_collate)
                    $sql = str_replace('DEFAULT CHARSET utf8', $charset_collate, $sql);
                $sql = explode(";\n", str_replace('wp_', '@wp_', $sql));
                for ($i = 0, $z = count($sql); $i < $z; $i++) {
                    $sql[$i] = trim($sql[$i]);
                    if (empty($sql[$i]))
                        continue;
                    pod_query($sql[$i], 'Cannot setup SQL tables');
                }
            }
            delete_option('pods_version');
            add_option('pods_version', PODS_VERSION);
            do_action('pods_install_post', PODS_VERSION, $installed, $blogid);
        }
        if (null !== $blogid && $blogid != $wpdb->blogid)
            switch_to_blog($old_blogid);
    }

    function init() {
        // Session start
        if (((defined('WP_DEBUG') && WP_DEBUG) || false === headers_sent()) && '' == session_id())
            @session_start();

        // Load necessary JS
        wp_register_script('jqmodal', PODS_URL . '/ui/js/jqmodal.js', array('jquery'));
        wp_register_script('pods-ui', PODS_URL . '/ui/js/pods.ui.js', array('jquery', 'jqmodal'));

        $additional_settings = array('pods_disable_file_browser' => 0,
                                     'pods_files_require_login' => 0,
                                     'pods_files_require_login_cap' => '',
                                     'pods_disable_file_upload' => 0,
                                     'pods_upload_require_login' => 0,
                                     'pods_upload_require_login_cap' => '',
                                     'pods_page_precode_timing' => 0);
        foreach ($additional_settings as $additional_setting => $setting) {
            $setting = get_option($additional_setting);
            if (!empty($setting))
                $additional_settings[$additional_setting] = $setting;
        }
        foreach ($additional_settings as $additional_setting => $setting) {
            if (0 == $setting)
                $setting = false;
            elseif (1 == $setting)
                $setting = true;
            if (in_array($additional_setting, array('pods_files_require_login', 'pods_upload_require_login'))) {
                if (0 < strlen($additional_settings[$additional_setting.'_cap']))
                    $setting = $additional_settings[$additional_setting.'_cap'];
            }
            elseif (in_array($additional_setting, array('pods_files_require_login_cap', 'pods_upload_require_login_cap')))
                continue;
            if (!defined(strtoupper($additional_setting)))
                define(strtoupper($additional_setting), $setting);
        }
    }

    function precode() {
        global $pods, $pod_page_exists;

        $function_or_file = str_replace('*', 'w', $pod_page_exists['uri']);
        $check_function = false;
        $check_file = 'precode-' . $function_or_file;
        if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_PAGE_FILES') || !PODS_PAGE_FILES))
            $check_file = false;
        if (false !== $check_function && false !== $check_file)
            $function_or_file = pods_function_or_file($function_or_file, $check_function, 'page', $check_file);
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
            remove_filter('redirect_canonical', array($this, 'kill_redirect'));
            remove_filter('wp_title', array($this, 'wp_title'));
            remove_filter('body_class', array($this, 'body_class'));
            remove_filter('status_header', array($this, 'status_header'));
            remove_action('wp', array($this, 'silence_404'));
        }
    }

    function admin_menu() {
        $submenu = array();
        $result = pod_query("SELECT name, label, is_toplevel FROM @wp_pod_types ORDER BY label, name");
        while ($row = mysql_fetch_array($result)) {
            $name = apply_filters('pods_admin_menu_name', $row['name'], $row);
            $label = trim($row['label']);
            $label = ('' != $label) ? $label : $name;
            $label = apply_filters('pods_admin_menu_label', $label, $row);
            $row['name'] = $name;
            $row['label'] = $label;
            if (pods_access("pod_{$name}")) {
                if (1 == $row['is_toplevel']) {
                    add_object_page($label, $label, 'read', "pods-manage-$name");
                    add_submenu_page("pods-manage-$name", 'Edit', 'Edit', 'read', "pods-manage-$name", array($this, 'pods_content_page'));
                    add_submenu_page("pods-manage-$name", 'Add New', 'Add New', 'read', "pods-add-$name", array($this, 'pods_content_page'));
                }
                else {
                    $submenu[trim($row['label'].$row['name'])] = $row;
                }
            }
        }
        $priv_check = array('manage_pods','manage_templates','manage_pod_pages','manage_helpers','manage_roles','manage_settings','manage_content','manage_packages');
        if ((!defined('PODS_DISABLE_ADMIN_MENU') || !PODS_DISABLE_ADMIN_MENU) && (!empty($submenu) || pods_access($priv_check))) {
            add_object_page('Pods', 'Pods', 'read', 'pods', null, PODS_URL.'/ui/images/icon16.png');
            if (pods_access(array('manage_pods','manage_templates','manage_pod_pages','manage_helpers','manage_roles','manage_settings'))) {
                add_submenu_page('pods', 'Setup', 'Setup', 'read', 'pods', array($this, 'pods_setup_page'));
            }
            if (pods_access('manage_packages')) {
                add_submenu_page('pods', 'Package Manager', 'Package Manager', 'read', 'pods-package', array($this, 'pods_packages_page'));
            }
            if (pods_access('manage_content')) {
                add_submenu_page('pods', 'Manage Content', 'Manage Content', 'read', 'pods-manage', array($this, 'pods_content_page'));
            }
            ksort($submenu);
            foreach ($submenu as $item) {
                $name = apply_filters('pods_admin_submenu_name', $item['name'], $item);
                $label = trim($item['label']);
                $label = ('' != $label) ? $label : $name;
                $label = apply_filters('pods_admin_submenu_label', $label, $item);
                add_submenu_page('pods', "Add $label", "Add $label", 'read', "pod-$name", array($this, 'pods_content_page'));
            }
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

    function kill_redirect() {
        return false;
    }

    function wp_title($title, $sep, $seplocation) {
        global $pods, $pod_page_exists;

        $page_title = $pod_page_exists['title'];

        if (0 < strlen(trim($page_title))) {
            if (is_object($pods) && !is_wp_error($pods))
                $page_title = preg_replace_callback("/({@(.*?)})/m", array($pods, "parse_magic_tags"), $page_title);
            $title = $page_title . " " . $sep . " ";
            if ('right' == $seplocation)
                $title = " " . $sep . " " . $page_title;
        }
        else {
            $home_path = parse_url(home_url());
            $uri = preg_replace('|^' . preg_quote($home_path['path'], '|') . '|', '', $_SERVER['REQUEST_URI']);
            $uri = explode('?', $uri);
            $uri = preg_replace("@^([/]?)(.*?)([/]?)$@", "$2", $uri[0]);
            $uri = preg_replace("@(-|_)@", " ", $uri);
            $uri = explode('/', $uri);

            $title = '';
            foreach ($uri as $page_title) {
                $title .= ('right' == $seplocation) ? ucwords($page_title) . " $sep " : " $sep " . ucwords($page_title);
            }
        }
        if ((!defined('PODS_DISABLE_META') || !PODS_DISABLE_META) && is_object($pods) && !is_wp_error($pods) && isset($pods->meta) && is_array($pods->meta) && isset($pods->meta['title']))
            $title = $pods->meta['title'];
        return apply_filters('pods_title', $title, $sep, $seplocation);
    }

    function body_class($classes) {
        global $pods, $pod_page_exists;
        $classes[] = 'pods';
        $uri = explode('?',$pod_page_exists['uri']);
        $uri = explode('#',$uri[0]);
        $classes[] = 'pod-page-'.trim(str_replace('--','-',str_replace('--','-',str_replace('_','-',sanitize_title(str_replace('/','-',str_replace('*','_w_',$uri[0])))))), '-');
        if (is_object($pods) && !is_wp_error($pods)) {
            $classes[] = 'pod-'.trim(str_replace('--','-',str_replace('_','-',$pods->datatype)), '-');
        }
        if ((!defined('PODS_DISABLE_BODY_CLASSES') || !PODS_DISABLE_BODY_CLASSES) && is_object($pods) && !is_wp_error($pods) && isset($pods->body_classes))
            $classes[] = $pods->body_classes;
        return apply_filters('pods_body_class', $classes, $uri);
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
            $template = $pod_page_exists['page_template'];
            $template = apply_filters('pods_page_template', $template, $pod_page_exists);

            $render_function = apply_filters('pods_template_redirect', 'false', $template, $pod_page_exists);

            do_action('pods_page', $template, $pod_page_exists);
            if (is_callable($render_function))
                call_user_func($render_function);
            elseif ((!defined('PODS_DISABLE_DYNAMIC_TEMPLATE') || !PODS_DISABLE_DYNAMIC_TEMPLATE) && is_object($pods) && !is_wp_error($pods) && isset($pods->page_template) && !empty($pods->page_template) && '' != locate_template(array($pods->page_template), true)) {
                $template = $pods->page_template;
                // found the template and included it, we're good to go!
            }
            elseif (!empty($pod_page_exists['page_template']) && '' != locate_template(array($pod_page_exists['page_template']), true)) {
                $template = $pod_page_exists['page_template'];
                // found the template and included it, we're good to go!
            }
            elseif ('' != locate_template(apply_filters('pods_page_default_templates', array('pods.php')), true)) {
                $template = 'pods.php';
                // found the template and included it, we're good to go!
            }
            else {
                // templates not found in theme, default output
                do_action('pods_page_default', $template, $pod_page_exists);
                get_header();
                pods_content();
                get_sidebar();
                get_footer();
            }
            do_action('pods_page_end', $template, $pod_page_exists);
            exit;
        }
    }

    function delete_attachment($attachment_id) {
        $result = pod_query("SELECT id FROM @wp_pod_fields WHERE coltype = 'file'");
        if (0 < mysql_num_rows($result)) {
            while ($row = mysql_fetch_assoc($result)) {
                $field_ids[] = $row['id'];
            }
            $field_ids = implode(',', $field_ids);

            // Remove all references to the deleted attachment
            do_action('pods_delete_attachment', $attachment_id, $field_ids);
            pod_query("DELETE FROM @wp_pod_rel WHERE field_id IN ({$field_ids}) AND tbl_row_id = {$attachment_id}");
        }
    }

    function pods_setup_page() {
        if (!wp_script_is('jquery-ui-core', 'queue') && !wp_script_is('jquery-ui-core', 'to_do') && !wp_script_is('jquery-ui-core', 'done'))
            wp_print_scripts('jquery-ui-core');
        if (!wp_script_is('jquery-ui-sortable', 'queue') && !wp_script_is('jquery-ui-sortable', 'to_do') && !wp_script_is('jquery-ui-sortable', 'done'))
            wp_print_scripts('jquery-ui-sortable');
        if (null === apply_filters('pods_admin_setup', null))
            include PODS_DIR . '/ui/manage.php';
    }

    function pods_packages_page() {
        if (null === apply_filters('pods_admin_packages', null))
            include PODS_DIR . '/ui/manage_packages.php';
    }

    function pods_content_page() {
        if (!wp_script_is('jquery-ui-core', 'queue') && !wp_script_is('jquery-ui-core', 'to_do') && !wp_script_is('jquery-ui-core', 'done'))
            wp_print_scripts('jquery-ui-core');
        if (!wp_script_is('jquery-ui-sortable', 'queue') && !wp_script_is('jquery-ui-sortable', 'to_do') && !wp_script_is('jquery-ui-sortable', 'done'))
            wp_print_scripts('jquery-ui-sortable');
        if (null === apply_filters('pods_admin_content', null))
            include PODS_DIR . '/ui/manage_content.php';
    }

    function deprecated() {
        require_once(PODS_DIR . '/deprecated.php'); // DEPRECATED IN 2.0
    }
}