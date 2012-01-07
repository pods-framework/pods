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
        add_action('init', array($this, 'init'));
        add_action('init', array($this, 'activate_install'), 9);
        add_action('init', array($this, 'admin_init'));
        add_action('init', array($this, 'setup_content_types'));
        add_action('init', array($this, 'page_check'), 11);
        add_action('add_meta_boxes', array($this,'add_meta_boxes'));
        add_action('save_post',array($this,'save_post'));
        //add_action('init', array($this, 'jquery_ui'), 11); // WP 3.1 + 3.2 support
        add_action('delete_attachment', array($this, 'delete_attachment'));
    }

    function init () {
        // Session start
        if (((defined('WP_DEBUG') && WP_DEBUG) || false === headers_sent()) && '' == session_id())
            @session_start();

        load_plugin_textdomain('pods', false, basename(plugin_basename(__FILE__)) . '/languages/');

        add_shortcode('pods', 'pods_shortcode');

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

    function admin_init () {
        global $pods_admin;
        $pods_admin = pods_admin();
    }

    function setup_content_types () {
        $api = pods_api();

        $taxonomies = (array) $api->load_pods(array('orderby' => '`weight`, `name`', 'type' => 'taxonomy'));
        foreach ($taxonomies as $taxonomy) {
            if (!empty($taxonomy['object']))
                continue;

            $taxonomy['options']['name'] = $taxonomy['name'];
            $taxonomy = (array) $taxonomy['options'];
            $taxonomy = array_filter($taxonomy);

            // Post Types
            $ct_post_types = array();
            $post_types = get_post_types();
            $ignore = array('attachment', 'revision', 'nav_menu_item');
            foreach ($post_types as $post_type => $label) {
                if (in_array($post_type, $ignore)) {
                    unset($post_types[$post_type]);
                    continue;
                }
                if (false !== (boolean) pods_var('ct_built_in_post_types_' . $post_type, $taxonomy, false))
                    $ct_post_types[] = $post_type;
            }
            if (empty($ct_post_types))
                $ct_post_types = null;

            // Labels
            $ct_label = esc_html(pods_var('ct_label', $taxonomy, pods_var('name', $taxonomy)));
            $ct_singular_label = esc_html(pods_var('ct_singular_label', $taxonomy, pods_var('ct_label', $taxonomy, pods_var('name', $taxonomy))));
            $ct_labels['name'] = $ct_label;
            $ct_labels['singular_name'] = $ct_singular_label;
            $ct_labels['search_items'] = pods_var('ct_search_items', $taxonomy, 'Search ' . $ct_label);
            $ct_labels['popular_items'] = pods_var('ct_popular_items', $taxonomy, 'Popular ' . $ct_label);
            $ct_labels['all_items'] = pods_var('ct_all_items', $taxonomy, 'All ' . $ct_label);
            $ct_labels['parent_item'] = pods_var('ct_parent_item', $taxonomy, 'Parent ' . $ct_singular_label);
            $ct_labels['parent_item_colon'] = pods_var('ct_parent_item_colon', $taxonomy, 'Parent ' . $ct_singular_label. ':');
            $ct_labels['edit_item'] = pods_var('ct_edit_item', $taxonomy, 'Edit ' . $ct_singular_label);
            $ct_labels['update_item'] = pods_var('ct_update_item', $taxonomy, 'Update ' . $ct_singular_label);
            $ct_labels['add_new_item'] = pods_var('ct_add_new_item', $taxonomy, 'Add New ' . $ct_singular_label);
            $ct_labels['new_item_name'] = pods_var('ct_new_item_name', $taxonomy, 'New ' . $ct_singular_label. ' Name');
            $ct_labels['separate_items_with_commas'] = pods_var('ct_separate_items_with_commas', $taxonomy, 'Separate ' . $ct_label. ' with commas');
            $ct_labels['add_or_remove_items'] = pods_var('ct_add_or_remove_items', $taxonomy, 'Add or remove ' . $ct_label);
            $ct_labels['choose_from_most_used'] = pods_var('ct_choose_from_most_used', $taxonomy, 'Choose from the most used ' . $ct_label);
            $ct_labels['menu_name'] = pods_var('ct_menu_name', $taxonomy, $ct_label);

            // Rewrite
            $ct_rewrite = pods_var('ct_rewrite', $taxonomy, true);
            $ct_rewrite_array = array('slug' => pods_var('ct_custom_rewrite_slug', $taxonomy, pods_var('name', $taxonomy)),
                                      'with_front' => (boolean) pods_var('ct_rewrite_with_front', $taxonomy, true),
                                      'hierarchical' => pods_var('ct_rewrite_hierarchical', $taxonomy, pods_var('ct_hierarchical', $taxonomy, false)));
            if (false !== $ct_rewrite)
                $ct_rewrite = $ct_rewrite_array;

            // Register Taxonomy
            register_taxonomy(pods_var('name', $taxonomy),
                              $ct_post_types,
                              array('label' => $ct_label,
                                    'labels' => $ct_labels,
                                    'public' => (boolean) pods_var('ct_public', $taxonomy, true),
                                    'show_in_nav_menus' => (boolean) pods_var('ct_show_in_nav_menus', $taxonomy, pods_var('ct_public', $taxonomy, true)),
                                    'show_ui' => (boolean) pods_var('ct_show_ui', $taxonomy, pods_var('ct_public', $taxonomy, true)),
                                    'show_tagcloud' => (boolean) pods_var('ct_show_tagcloud', $taxonomy, pods_var('ct_show_ui', $taxonomy, pods_var('ct_public', $taxonomy, true))),
                                    'hierarchical' => (boolean) pods_var('ct_hierarchical', $taxonomy),
                                    //'update_count_callback' => pods_var('update_count_callback', $taxonomy),
                                    'query_var' => (false !== pods_var('ct_query_var', $taxonomy, true) ? pods_var('ct_query_var_string', $taxonomy, pods_var('name', $taxonomy)) : false),
                                    'rewrite' => $ct_rewrite));
            //'capabilities' => $ct_capabilities
        }

        $post_types = (array) $api->load_pods(array('orderby' => '`weight`, `name`', 'type' => 'post_type'));
        foreach ($post_types as $post_type) {
            if (!empty($post_type['object']))
                continue;

            $post_type['options']['name'] = $post_type['name'];
            $post_type = (array) $post_type['options'];
            $post_type = array_filter($post_type);

            // Labels
            $cpt_label = esc_html(pods_var('cpt_label', $post_type, ucwords(str_replace('_', ' ', pods_var('name', $post_type)))));
            $cpt_singular = esc_html(pods_var('cpt_singular_label', $post_type, ucwords(str_replace('_', ' ', pods_var('cpt_label', $post_type, pods_var('name', $post_type))))));
            $cpt_labels['name'] = $cpt_label;
            $cpt_labels['singular_name'] = $cpt_singular;
            $cpt_labels['menu_name'] = pods_var('cpt_menu_name', $post_type, $cpt_label);
            $cpt_labels['add_new'] = pods_var('cpt_add_new', $post_type, 'Add ' . $cpt_singular);
            $cpt_labels['add_new_item'] = pods_var('cpt_add_new_item', $post_type, 'Add New ' . $cpt_singular);
            $cpt_labels['new_item'] = pods_var('cpt_new_item', $post_type, 'New ' . $cpt_singular);
            $cpt_labels['edit'] = pods_var('cpt_edit', $post_type, 'Edit');
            $cpt_labels['edit_item'] = pods_var('cpt_edit_item', $post_type, 'Edit ' . $cpt_singular);
            $cpt_labels['view'] = pods_var('cpt_view', $post_type, 'View ' . $cpt_singular);
            $cpt_labels['view_item'] = pods_var('cpt_view_item', $post_type, 'View ' . $cpt_singular);
            $cpt_labels['all_items'] = pods_var('cpt_all_items', $post_type, 'All ' . $cpt_singular);
            $cpt_labels['search_items'] = pods_var('cpt_search_items', $post_type, 'Search ' . $cpt_label);
            $cpt_labels['not_found'] = pods_var('cpt_not_found', $post_type, 'No ' . $cpt_label. ' Found');
            $cpt_labels['not_found_in_trash'] = pods_var('cpt_not_found_in_trash', $post_type, 'No ' . $cpt_label. ' Found in Trash');
            $cpt_labels['parent'] = pods_var('cpt_parent', $post_type, 'Parent ' . $cpt_singular);
            $cpt_labels['parent_item_colon'] = pods_var('cpt_parent_item_colon', $post_type, 'Parent ' . $cpt_singular . ':');

            // Supported
            $cpt_supported = array('title' => (boolean) pods_var('cpt_supports_title', $post_type, true),
                                   'editor' => (boolean) pods_var('cpt_supports_editor', $post_type, true),
                                   'author' => (boolean) pods_var('cpt_supports_author', $post_type, false),
                                   'thumbnail' => (boolean) pods_var('cpt_supports_thumbnail', $post_type, false),
                                   'excerpt' => (boolean) pods_var('cpt_supports_excerpt', $post_type, false),
                                   'trackbacks' => (boolean) pods_var('cpt_supports_trackbacks', $post_type, false),
                                   'custom-fields' => (boolean) pods_var('cpt_supports_custom_fields', $post_type, false),
                                   'comments' => (boolean) pods_var('cpt_supports_comments', $post_type, false),
                                   'revisions' => (boolean) pods_var('cpt_supports_revisions', $post_type, false),
                                   'page-attributes' => (boolean) pods_var('cpt_supports_page_attributes', $post_type, false),
                                   'post-formats' => (boolean) pods_var('cpt_supports_post_formats', $post_type, false));
            $cpt_supports = array();
            foreach ($cpt_supported as $cpt_support => $cpt_supported) {
                if (false !== $cpt_supported)
                    $cpt_supports[] = $cpt_support;
            }

            // Taxonomies
            $cpt_taxonomies = array();
            $taxonomies = get_taxonomies();
            $ignore = array('nav_menu', 'link_category', 'post_format');
            foreach ($taxonomies as $taxonomy => $label) {
                if (in_array($taxonomy, $ignore)) {
                    unset($taxonomies[$taxonomy]);
                    continue;
                }
                if (false !== (boolean) pods_var('cpt_built_in_taxonomies_' . $taxonomy, $post_type, false))
                    $cpt_taxonomies[] = $taxonomy;
            }

            // Rewrite
            $cpt_rewrite = pods_var('cpt_rewrite', $post_type, true);
            $cpt_rewrite_array = array('slug' => pods_var('cpt_custom_rewrite_slug', $post_type, pods_var('name', $post_type)),
                                       'with_front' => (boolean) pods_var('cpt_rewrite_with_front', $post_type, true),
                                       'feeds' => pods_var('cpt_rewrite_feeds', $post_type, pods_var('cpt_has_archive', $post_type, false)),
                                       'pages' => (boolean) pods_var('cpt_rewrite_pages', $post_type, true));
            if (false !== $cpt_rewrite)
                $cpt_rewrite = $cpt_rewrite_array;
                
            // Register Post Type
            register_post_type(pods_var('name', $post_type),
                               array('label' => $cpt_label,
                                     'labels' => $cpt_labels,
                                     'description' => esc_html(pods_var('cpt_description', $post_type)),
                                     'public' => (boolean) pods_var('cpt_public', $post_type, false),
                                     'publicly_queryable' => (boolean) pods_var('cpt_publicly_queryable', $post_type, (boolean) pods_var('cpt_public', $post_type, false)),
                                     'exclude_from_search' => (boolean) pods_var('cpt_exclude_from_search', $post_type, (pods_var('cpt_public', $post_type, false) ? false : true)),
                                     'show_ui' => (boolean) pods_var('cpt_show_ui', $post_type, (boolean) pods_var('cpt_public', $post_type, false)),
                                     'show_in_menu' => (boolean) pods_var('cpt_show_in_menu', $post_type, true),
                                     'show_in_admin_bar' => (boolean) pods_var('cpt_show_in_admin_bar', $post_type, (boolean) pods_var('cpt_show_in_menu', $post_type, true)),
                                     'menu_position' => (int) pods_var('cpt_menu_position', $post_type, 20),
                                     'menu_icon' => pods_var('cpt_menu_icon', $post_type),
                                     'capability_type' => pods_var('cpt_capability_type', $post_type, 'post'),
                                     //'capabilities' => $cpt_capabilities,
                                     'map_meta_cap' => (boolean) pods_var('cpt_map_meta_cap', $post_type, true),
                                     'hierarchical' => (boolean) pods_var('cpt_hierarchical', $post_type, false),
                                     'supports' => $cpt_supports,
                                     //'register_meta_box_cb' => array($this, 'manage_meta_box'),
                                     'taxonomies' => $cpt_taxonomies,
                                     //'permalink_epmask' => EP_PERMALINK,
                                     'has_archive' => (boolean) pods_var('cpt_has_archive', $post_type, false),
                                     'rewrite' => $cpt_rewrite,
                                     'query_var' => (false !== pods_var('cpt_query_var', $post_type, true) ? pods_var('cpt_query_var_string', $post_type, pods_var('name', $post_type)) : false),
                                     'can_export' => (boolean) pods_var('cpt_can_export', $post_type, true),
                                     'show_in_nav_menus' => (boolean) pods_var('cpt_show_in_nav_menus', $post_type, (boolean) pods_var('cpt_public', $post_type, false))));
        }
    }

    function page_check () {
        global $pod_page_exists, $pods;

        if (!defined('PODS_DISABLE_POD_PAGE_CHECK')) {
            if (null === $pod_page_exists)
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
    }

    function jquery_ui () {
        global $wp_version;
        if (version_compare($wp_version, '3.3', '<')) {
            $jquery_ui = array('effects.blind' => array('effects-core'),
                               'effects.bounce' => array('effects-core'),
                               'effects.clip' => array('effects-core'),
                               'effects.core' => array(),
                               'effects.drop' => array('effects-core'),
                               'effects.explode' => array('effects-core'),
                               'effects.fade' => array('effects-core'),
                               'effects.fold' => array('effects-core'),
                               'effects.highlight' => array('effects-core'),
                               'effects.pulsate' => array('effects-core'),
                               'effects.scale' => array('effects-core'),
                               'effects.shake' => array('effects-core'),
                               'effects.slide' => array('effects-core'),
                               'effects.transfer' => array('effects-core'),
                               'ui.accordion' => array('jquery-ui-core',
                                                       'jquery-ui-widget'),
                               'ui.autocomplete' => array('jquery-ui-core',
                                                          'jquery-ui-widget',
                                                          'jquery-ui-position'),
                               'ui.datepicker' => array('jquery-ui-core'),
                               'ui.progressbar' => array('jquery-ui-core',
                                                         'jquery-ui-widget'),
                               'ui.slider' => array('jquery',
                                                    'jquery-ui-core',
                                                    'jquery-ui-widget',
                                                    'jquery-ui-mouse'));
            foreach ($jquery_ui as $script => $dependencies) {
                $handle = 'jquery-' . str_replace('.', '-', $script);
                if (!wp_script_is($handle, 'registered'))
                    wp_register_script($handle, PODS_URL . 'ui/js/jquery-ui/jquery.' . $script . '.js', $dependencies, '1.8.12');
            }
        }
    }

    function activate_install () {
        // Activate and Install (@to-do: don't install, display notice if not 'installed' with a link for user to run install)
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('wpmu_new_blog', array($this, 'new_blog'), 10, 6);
        $pods_version = get_option('pods_framework_version');
        if (empty($pods_version) || false === strpos($pods_version, '.') || version_compare($pods_version, PODS_VERSION, '<'))
            $this->setup();
    }

    function activate () {
        global $wpdb;
        if (function_exists('is_multisite') && is_multisite() && isset($_GET['networkwide']) && 1 == $_GET['networkwide']) {
            $_blog_ids = $wpdb->get_col($wpdb->prepare("SELECT `blog_id` FROM {$wpdb->blogs}"));
            foreach ($_blog_ids as $_blog_id)
                $this->setup($_blog_id);
        }
        else
            $this->setup();
    }

    function new_blog ($_blog_id, $user_id, $domain, $path, $site_id, $meta) {
        if (function_exists('is_multisite') && is_multisite() && is_plugin_active_for_network('pods/init.php'))
            $this->setup($_blog_id);
    }

    function setup ($_blog_id = null) {
        global $wpdb;

        // Switch DB table prefixes
        if (null !== $_blog_id && $_blog_id != $wpdb->blogid)
            switch_to_blog(pods_absint($_blog_id));
        else
            $_blog_id = null;

        // Setup DB tables
        $pods_version = get_option('pods_framework_version');
        if (0 < strlen($pods_version) && false === strpos($pods_version, '.'))
            $pods_version = pods_version_to_point($pods_version);
        if (0 < strlen($pods_version)) {
            if (version_compare($pods_version, PODS_VERSION, '<')) {
                do_action('pods_update', PODS_VERSION, $pods_version, $_blog_id);
                if (false !== apply_filters('pods_update_run', null, PODS_VERSION, $pods_version, $_blog_id) && !isset($_GET['pods_bypass_update']))
                    include(PODS_DIR . 'sql/update.php');
                do_action('pods_update_post', PODS_VERSION, $pods_version, $_blog_id);
            }
        }
        else {
            do_action('pods_install', PODS_VERSION, $pods_version, $_blog_id);
            if (false !== apply_filters('pods_install_run', null, PODS_VERSION, $pods_version, $_blog_id) && !isset($_GET['pods_bypass_install'])) {
                $sql = file_get_contents(PODS_DIR . 'sql/dump.sql');
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
            delete_option('pods_framework_version');
            add_option('pods_framework_version', PODS_VERSION);
            do_action('pods_install_post', PODS_VERSION, $pods_version, $_blog_id);
        }

        // Restore DB table prefix (if switched)
        if (null !== $_blog_id)
            restore_current_blog();
    }

    // Delete Attachments from relationships
    // @to-do: remove select and run DELETE with the JOIN on field.type='file'
    function delete_attachment ($_ID) {
        $results = pods_query("SELECT `id` FROM `@wp_pods_fields` WHERE `type` = 'file'");
        if (!empty($results)) {
            $field_ids = array();
            foreach ($results as $row) {
                $field_ids[] = (int) $row->id;
            }
            $field_ids = implode(',', $field_ids);

            if (!empty($field_ids)) {
                // Remove all references to the deleted attachment
                do_action('pods_delete_attachment', $_ID, $field_ids);
                $sql = "DELETE FROM `@wp_pods_rel` WHERE `field_id` IN ({$field_ids}) AND `item_id` = %d";
                $sql = array($sql, array($_ID));
                pods_query($sql);
            }
        }
    }


    // Pod Page Code
    function precode () {
        global $pods, $pod_page_exists;

        if (false !== $pod_page_exists) {
            $content = false;
            if (0 < strlen(trim($pod_page_exists['precode'])))
                $content = $pod_page_exists['precode'];

            if (false !== $content && !defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
                eval("?>$content");

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
    }


    function wp_head () {
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
        $classes[] = 'pod-page-' . $class;
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
    
    /*
     * Buld meta boxes for custom fields
     */
   	function add_meta_boxes() {
   		global $wpdb,$post;
   		$pod_fields = array(); 
   		wp_enqueue_style('mb-style',PODS_URL.'/ui/css/meta-boxes.css');
   		//query pod fields for post_type
   		$select = $wpdb->prepare("SELECT p.name as pod_name, f.* FROM {$wpdb->prefix}pods p INNER JOIN {$wpdb->prefix}pods_fields AS f ON f.pod_id = p.id WHERE p.type = 'post_type' AND p.name = %s ORDER BY f.weight ASC",$post->post_type);
   		$pts = $wpdb->get_results($select);
   		if(!empty($pts)) {
	   		foreach($pts as $pt) {
	   			$pod_fields[$pt->pod_name][$pt->weight] = $pt;
	   		}
	   		if(!empty($pod_fields)) {
				$post_type = get_post_type_object($post->post_type);
				add_meta_box($post->post_type.'-pods-fields', __($post_type->labels->singular_name,'pods'), array($this,'mb_callback'), $post->post_type, 'normal', 'high', $pod_fields );  
			}
			
		}
   	}
   	
   	function mb_callback($post,$args) {
   		$fields = $args['args'][$post->post_type];
   		foreach($fields as $field) {
   			
   			$options = (array) json_decode($field->options);
   			
   			$input_field = array(
   				'id' 	=> $field->name,
   				'type'	=> $field->type,
   				'desc' 	=> $options['description'],
   				'std'	=> @$options['default'],
   			); 
   			
   			if($field->type == 'pick') {
   				$obj = explode('-',$field->pick_object); 
   				switch($obj[0]) {
   					case 'taxonomy':
   						if('single' == $options['pick_type']) {
   							$input_field['type'] = 'taxonomy-single';
   							$input_field['taxonomy'] = $obj[1];
   						}
   					break;
   				}
   			}
   			
   			echo '<input type="hidden" name="wp_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';			
   			echo '<table class="form-table DMB_metabox">';
   			echo '<tr class="'.$field->type.'">';
   			echo '<th style="width:18%"><label for="', $field->name.'">'.$field->label.'</label></th>';
   			echo '<td>';
   			mpv_field_input($input_field,get_post_meta($post->ID,$field->name,true),$post);
   			echo '</td>';
   			echo '</tr>';
   			echo '</table>';
   		}
   	}
   	
   	function save_post($post_id) {
   		global $post,$wpdb;
   		//look for the nonce
		if ( ! isset( $_POST['wp_meta_box_nonce'] ) || !wp_verify_nonce($_POST['wp_meta_box_nonce'], basename(__FILE__))) {
			return $post_id;
		}
		
		// check autosave
		if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
			return $post_id;
		}

		// check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
   		
   		//get fields
   		$select = $wpdb->prepare("SELECT p.name as pod_name, f.* FROM {$wpdb->prefix}pods p INNER JOIN {$wpdb->prefix}pods_fields AS f ON f.pod_id = p.id WHERE p.type = 'post_type' AND p.name = %s ORDER BY f.weight ASC",$post->post_type);
   		$pts = $wpdb->get_results($select);
   		if(!empty($pts)) {
   			foreach($pts as $field) {
   				if($field->type != 'pick') {
   					update_post_meta($post_id,$field->name, $_POST[$field->name]);
   				} elseif($field->type == 'pick' AND strstr($field->pick_object,'taxonomy') ) {
   					$taxonomy = str_replace('taxonomy-','',$field->pick_object);
   					wp_set_object_terms($post_id,intval($_POST[$field->name]),$taxonomy);
   				}
   			}
   		}
   		
   		return $post_id;
   	}
}

function mpv_field_input($field,$meta,$post) {
			switch ( $field['type'] ) {
					case 'text':
						echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" style="width:97%" />',
							'<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'text_small':
						echo '<input class="DMB_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'text_medium':
						echo '<input class="DMB_text_medium" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'text_date':
						echo '<input class="DMB_text_small DMB_datepicker" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'text_money':
						echo '$ <input class="DMB_text_money" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'textarea':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="10" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
							'<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'textarea_small':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
							'<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'select':
						echo '<select name="', $field['id'], '" id="', $field['id'], '">';
						foreach ($field['options'] as $option) {
							echo '<option value="', $option['value'], '"', $meta == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
						}
						echo '</select>';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'radio_inline':
						echo '<div class="DMB_radio_inline">';
						foreach ($field['options'] as $option) {
							echo '<div class="DMB_radio_inline_option"><input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'], '</div>';
						}
						echo '</div>';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'radio':
						foreach ($field['options'] as $option) {
							echo '<p><input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'].'</p>';
						}
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'checkbox':
						echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
						echo '<span class="DMB_metabox_description">', $field['desc'], '</span>';
						break;
					case 'multicheck':
						echo '<ul>';
						foreach ( $field['options'] as $value => $name ) {
							// Append `[]` to the name to get multiple values
							// Use in_array() to check whether the current option should be checked
							echo '<li><input type="checkbox" name="', $field['id'], '[]" id="', $field['id'], '" value="', $value, '"', in_array( $value, $meta ) ? ' checked="checked"' : '', ' /><label>', $name, '</label></li>';
						}
						echo '</ul>';
						echo '<span class="DMB_metabox_description">', $field['desc'], '</span>';					
						break;		
					case 'title':
						echo '<h5 class="DMB_metabox_title">', $field['name'], '</h5>';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						break;
					case 'paragraph':
						echo '<div id="poststuff" class="meta_mce">';
						echo '<div class="customEditor"><textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="7" style="width:97%">', $meta ? $meta : '', '</textarea></div>';
	                    echo '</div>';
				        echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
					break;
	/*
					case 'wysiwyg':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" class="theEditor" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';	
						break;
	*/
					case 'file_list':
						if($field['mode'] == 'all' || !isset($field['mode'])) {
							echo '<input id="upload_file" type="text" size="36" name="', $field['id'], '" value="" />';
							echo '<input class="upload_button button" type="button" value="Upload File" />';
							echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
								$args = array(
										'post_type' => 'attachment',
										'numberposts' => null,
										'post_status' => null,
										'post_parent' => $post->ID
									);
									$attachments = get_posts($args);
									if ($attachments) {
										echo '<ul class="attach_list">';
										foreach ($attachments as $attachment) {
											echo '<li>'.wp_get_attachment_link($attachment->ID, 'thumbnail', 0, 0, 'Download');
											echo '<span>';
											echo apply_filters('the_title', '&nbsp;'.$attachment->post_title);
											echo '</span>';
											echo ' / <span><a href="" id="remove-attach" rel="'.$attachment->ID.'">Remove</a></li>';
										}
										echo '</ul>';
									}
						} elseif($field['mode'] == 'only') { 
								echo '<input id="upload_file" type="text" size="36" name="', $field['id'], '" value="" />';
								echo '<input class="upload_button button" type="button" value="Upload File" />';
								echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
							$files = get_post_meta($post->ID,$field['id'],false);
							if(!empty($files)) {
										echo '<ul class="attach_list">';
									if(is_array($files)) {
											foreach ($files as $file) {
												echo '<li>'.wp_get_attachment_link($file, 'thumbnail', 0, 0, 'Download');
												echo '<span>';
												echo apply_filters('the_title', '&nbsp;'.get_the_title($file));
												echo '</span></li>';	
											}
									} else {
												echo '<li>'.wp_get_attachment_link($files, 'thumbnail', 0, 0, 'Download');
												echo '<span>';
												echo apply_filters('the_title', '&nbsp;'.get_the_title($file));
												echo '</span></li>';	
									}
								}
							}
							echo '<div id="', $field['id'], '_status" class="DMB_upload_status">';	
							echo '</div>';
							break;
					case 'file':
						echo '<input id="upload_file" type="text" size="45" class="', $field['id'], '" name="', $field['id'], '" value="', $meta, '" />';
						echo '<input class="upload_button button" type="button" value="Upload File" />';
						echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
						echo '<div id="', $field['id'], '_status" class="DMB_upload_status">';	
							if ( $meta != '' ) { 
								$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $meta );
								if ( $check_image ) {
									echo '<div class="img_status">';
									echo '<a href="#" class="remove_file_button" rel="', $field['id'], '">Remove Image</a><br>';
									echo '<img src="', $meta, '" alt="" />';
									echo '</div>';
								} else {
									$parts = explode( "/", $meta );
									for( $i = 0; $i < sizeof( $parts ); ++$i ) {
										$title = $parts[$i];
									} 
									echo 'File: <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta, '" target="_blank" rel="external">Download</a> / <a href="# class="remove_file_button" rel="', $field['id'], '">Remove</a>)';
								}	
							}
						echo '</div>'; 
					break;
					case 'pick':
					case 'taxonomy-single':
					$vals = wp_get_object_terms($post->ID,$field['taxonomy'],array('fields'=>'ids'));
						wp_dropdown_categories(array(
						'name' => $field['id'], 
						'id'=> $field['taxonomy'], 
						'hide_empty'=> 0,
						'show_count'=>0,
						'selected' =>($vals)?$vals[0]:'',
						'taxonomy' => $field['taxonomy'])
						); 
					echo '<p class="DMB_metabox_description">', $field['desc'], '</p>';
					break;
					
					case 'taxonomy-text':
					$vals = wp_get_object_terms($post->ID,$field['taxonomy'],array('fields'=>'all'));
					echo '<input class="DMB_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="'.@$vals[0]->name.'" /><span class="DMB_metabox_description">', $field['desc'], '</span>';
					break;
					
					} // switch
}